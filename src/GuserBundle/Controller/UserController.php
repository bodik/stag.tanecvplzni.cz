<?php

namespace GuserBundle\Controller;

use GuserBundle\CryptPasswordEncoder;
use GuserBundle\Entity\User;
use GuserBundle\Form\DeleteButtonType;
use GuserBundle\Form\LostPasswordType;
use GuserBundle\Form\SetPasswordType;
use GuserBundle\Form\ChangePasswordType;
use GuserBundle\Form\UserType;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;


class UserController extends Controller {
	private $log;
	private $em;
	private $appName;

	public function __construct(LoggerInterface $log, EntityManagerInterface $em) {
		$this->log = $log;
		$this->em = $em;
		$this->appName = (array_key_exists("SERVER_NAME", $_SERVER) ? $_SERVER["SERVER_NAME"] : "localhost");
	}
	
	
	
	/**
	 * @Route("/user/list", name="user_list")
         * @Security("has_role('ROLE_ADMIN')")
	 */
	public function listAction(Request $request) {
		$users = $this->em->getRepository("GuserBundle:User")->findAll();
		return $this->render("GuserBundle:User:list.html.twig", ["users" => $users] );
	}



	/**
	 * @Route("/user/add", name="user_add")
	 * @Security("has_role('ROLE_ADMIN')")
	 */
	public function addAction(Request $request) {
		$user = new User();
		$form = $this->createForm(UserType::class, $user);
		
		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
			
			//TODO: test to inject false created, modified ?
			$user = $form->getData();
			$this->em->persist($user);
			$this->em->flush();
			/* security audit */
			list($username, $remote_addr) = $this->_getAuthenticationInfo();
			$this->log->info("GUSER USER ADD {$user->getUsername()} from $username $remote_addr");
			
			$this->addFlash("success","User {$user->getUsername()} was created");
			return $this->redirectToRoute("user_list");
		}

		return $this->render("GuserBundle:User:addedit.html.twig", ["form" => $form->createView()]);
	}
	
	
	
	/**
	 * @Route("/user/edit/{id}", name="user_edit")
	 * @Security("has_role('ROLE_ADMIN')")
	 */
	public function editAction(Request $request, $id) {
		$user = $this->em->getRepository("GuserBundle:User")->find($id);
		$form = $this->createForm(UserType::class, $user);

		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
			
			$user = $form->getData();
			$this->em->flush();
			/* security audit */
			list($username, $remote_addr) = $this->_getAuthenticationInfo();
			$this->log->info("GUSER USER EDIT {$user->getUsername()} from $username $remote_addr");

			$this->addFlash("success","User {$user->getUsername()} was saved");
            		return $this->redirectToRoute("user_list");
		}

		return $this->render("GuserBundle:User:addedit.html.twig", ["form" => $form->createView()]);
	}	
	
	
		
	/**
	 * @Route("/user/delete/{id}", name="user_delete")
	 * @Security("has_role('ROLE_ADMIN')")
	 */
	public function deleteAction(Request $request, $id) {
		$user = $this->em->getRepository("GuserBundle:User")->find($id);
		$form = $this->createForm(DeleteButtonType::class, $user,
			array("action" => $this->generateUrl("user_delete", ["id" => $id]))
		);

		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
			if ($user) {
				
				$this->em->remove($user);
				$this->em->flush();
				/* security audit */
				list($username, $remote_addr) = $this->_getAuthenticationInfo();
				$this->log->info("GUSER USER DELETE {$user->getUsername()} from $username $remote_addr");

				$this->addFlash("success","User {$user->getUsername()} was deleted");
			} else {
				$this->addFlash("error","User with ID {$id} does not exits");
			}
			return $this->redirectToRoute("user_list");
		}

		return $this->render("GuserBundle::deletebutton.html.twig", ["form" => $form->createView()]);
	}






	/**
	 * @Route("/user/changepassword", name="user_changepassword")
	 * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
	 */
	public function changePasswordAction(Request $request) {
		$form = $this->createForm(ChangePasswordType::class);
		
		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
			$data = $form->getData();
			$user = $this->em->getRepository("GuserBundle:User")->findOneByUsername(
					$this->container->get("security.token_storage")->getToken()->getUser()->getUsername()
				);

			# reauth
			$encoder = $this->container->get("security.encoder_factory")->getEncoder($user);
			$reauth = $encoder->isPasswordValid($user->getPassword(), $data["current_password"], $user->getSalt());
			if ($reauth) {
				$this->_changePassword($user, $data["new_password1"], $data["new_password2"]);
			} else {
				$this->addFlash("error","Reauthentication failed. Password not changed.");
			}
		}

		return $this->render("GuserBundle:User:changepassword.html.twig", ["form" => $form->createView()]);
	}
	
	
	
	public function _changePassword($user, $new_password1, $new_password2) {
		#check typos
		$newPasswordsMatch = ($new_password1 == $new_password2 ? True : False);
		# strength
		$encoder = $this->container->get("security.encoder_factory")->getEncoder($user);
		$passwordStrength = $encoder->isPasswordStrength($user->getUsername(), $new_password2);
		
		if ($newPasswordsMatch) {
			switch ($passwordStrength) {
				case CryptPasswordEncoder::PASSWORD_RET_LENGTH:	$this->addFlash("error","Password minimal length is " . CryptPasswordEncoder::PASSWORD_MIN_LENGTH); break;
				case CryptPasswordEncoder::PASSWORD_RET_CLASSES: $this->addFlash("error","Password must contain at least " . CryptPasswordEncoder::PASSWORD_MIN_CLASSES . "character classes"); break;
				case CryptPasswordEncoder::PASSWORD_RET_INCLUDESUSERNAME: $this->addFlash("error","Password must not be based on username"); break;

				case CryptPasswordEncoder::PASSWORD_RET_OK:
					$user->setPassword($new_password2);
					$this->em->persist($user);
					$this->em->flush();
					/* security audit */
					list($username, $remote_addr) = $this->_getAuthenticationInfo();
					$this->log->info("GUSER USER CHANGEPASSWORD {$user->getUsername()} from $username $remote_addr");
					
					$this->_sendPasswordChangedEmail($user);
					$this->addFlash("success","Password changed.");
					break;
			}
		} else {
			$this->addFlash("error","New passwords does not match. Password not changed.");
		}
		
		return $passwordStrength;
	}
	
	
	
	public function _sendPasswordChangedEmail($user) {
		# send email
		$message = (new \Swift_Message("{$this->appName} password changed"));
		$message->setFrom( ($this->container->getParameter('mailer_user') ? $this->container->getParameter('mailer_user') : "noreply@{$this->appName}") );
		$message->setTo($user->getEmail());
		$message->setBody(
			$this->renderView("GuserBundle:User:passwordchangedemail.html.twig", [
				"appname" => $this->appName,
				"username" => $user->getUsername(),
				"email" => $user->getEmail(),
				"url" => $this->generateUrl("user_lostpassword", [], UrlGeneratorInterface::ABSOLUTE_URL)
				]),
			"text/plain"
		);
		
		$this->get("mailer")->send($message);

		return;
	}






	/**
	 * @Route("/user/lostpassword/{token}", name="user_lostpassword", defaults={"token" = null})
	 */
	public function lostPasswordAction(Request $request, $token) {
		if ($token == null) {
			
			# lost password form
			$form = $this->createForm(LostPasswordType::class);
			$form->handleRequest($request);
			if ($form->isSubmitted() && $form->isValid()) {
				$data = $form->getData();
				$user = $this->em->getRepository("GuserBundle:User")->findOneByEmail($data["email"]);
				
				if ($user) {
						$this->_sendLostPasswordEmail($user);
						/* security audit */
						list($username, $remote_addr) = $this->_getAuthenticationInfo();
						$this->log->info("GUSER USER LOSTPASSWORD {$user->getUsername()} from $username $remote_addr");

				}
				sleep(rand(1,5)); # anti information gathering time gap
				
				$this->addFlash("info","Lost password reset information was sent on registered email.");
			}
			return $this->render("GuserBundle:User:lostpassword.html.twig", ["form" => $form->createView()]);
			
		} else {
			
			# set password form
			$user = $this->em->getRepository("GuserBundle:User")->findOneByLostPasswordToken($token);
			$now = new \Datetime();
			if ( ($user) && ($now < $user->getLostPasswordTokenExpiration())) {
				$form = $this->createForm(SetPasswordType::class);
				$form->handleRequest($request);
				if ($form->isSubmitted() && $form->isValid()) {
					$data = $form->getData(); 
					$ret = $this->_changePassword($user, $data["new_password1"], $data["new_password2"]);
					if ( $ret == CryptPasswordEncoder::PASSWORD_RET_OK) {
						$user->setLostPasswordToken(null);
						$user->setLostPasswordTokenExpiration(null);
						$user->setLocked(false);
						$this->em->flush();
						/* security audit */
						list($username, $remote_addr) = $this->_getAuthenticationInfo();
						$this->log->info("GUSER USER LOSTPASSWORDCHANGE {$user->getUsername()} from $username $remote_addr");
						return $this->redirectToRoute("login");
					} else {
						return $this->redirectToRoute("user_lostpassword", ["token" => $token]);
					}
				}
				return $this->render("GuserBundle:User:setpassword.html.twig", ["form" => $form->createView()]);
			} else {
				$this->addFlash("error","Invalid lost password token.");
				return $this->redirectToRoute("user_lostpassword");
			}
		}
	}
	
	
	
	public function _sendLostPasswordEmail($user){
		# gen token
		$user->setLostPasswordToken(hash('sha256', random_bytes(100)));
		$expire = new \Datetime();
		$expire->setTimestamp( $expire->getTimestamp() + User::LOST_PASSWORD_TOKEN_EXPIRATION );
		$user->setLostPasswordTokenExpiration($expire);
		$this->em->persist($user);
		$this->em->flush();		

		# send email
		$message = (new \Swift_Message("{$this->appName} password reset"));
		$message->setFrom( ($this->container->getParameter('mailer_user') ? $this->container->getParameter('mailer_user') : "noreply@{$this->appName}") );
		$message->setTo($user->getEmail());
		$message->setBody(
			$this->renderView("GuserBundle:User:lostpasswordemail.html.twig", [
				"appname" => $this->appName,
				"url" => $this->generateUrl("user_lostpassword", ["token" => $user->getLostPasswordToken()], UrlGeneratorInterface::ABSOLUTE_URL)
				]),
			"text/plain"
		);
		$res = $this->get("mailer")->send($message);
		return;
	}







	/**
         * only used to render loginform
	 *
	 * @Route("/login", name="login")
	 */
	public function loginAction(Request $request, AuthenticationUtils $authUtils) {
		// get the login error if there is one
		$error = $authUtils->getLastAuthenticationError();
		
		// last username entered by the user
		$lastUsername = $authUtils->getLastUsername();

		return $this->render("GuserBundle:User:login.html.twig", [
			"last_username" => $lastUsername,
			"error" => $error,
		]);
	}

	/**
	 * @Route("/logout", name="logout")
	 */
    	public function logoutAction() {
        	return $this->redirectToRoute("/");
	}
	
	public function _getAuthenticationInfo() {
		$tmp = $this->container->get('security.token_storage')->getToken()->getUser();
		if(is_object($tmp)) { 
			$username = $tmp->getUsername(); 
		} else {
			$username = $tmp;
		}
		$remote_addr = $this->container->get('request_stack')->getCurrentRequest()->getClientIp();
		return [$username, $remote_addr];
	}
}
