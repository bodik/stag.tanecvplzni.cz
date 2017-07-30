<?php

namespace GuserBundle\Controller;

use GuserBundle\CryptPasswordEncoder;
use GuserBundle\Entity\User;
use GuserBundle\Form\DeleteButtonType;
use GuserBundle\Form\UserChangePasswordType;
use GuserBundle\Form\UserType;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class UserController extends Controller {
	private $em;

	public function __construct(EntityManagerInterface $em) {
		$this->em = $em;
	}
	
	
	
	/**
	 * @Route("/user/list", name="user_list")
         * @Security("has_role('ROLE_ADMIN')")
	 */
	public function listAction(Request $request) {
		$users = $this->em->getRepository("GuserBundle:User")->findAll();
		return $this->render("GuserBundle:User:list.html.twig", array("users" => $users) );
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

			$this->addFlash("success","User {$user->getUsername()} was created");
			return $this->redirectToRoute("user_index");
		}

		return $this->render("GuserBundle:User:/addedit.html.twig", array("form" => $form->createView(),));
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


			$this->addFlash("success","User {$user->getUsername()} was saved");
            		return $this->redirectToRoute("user_index");
		}

		return $this->render("GuserBundle:User:addedit.html.twig", array("form" => $form->createView(),));
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
			if (!empty($user)) {
				$this->em->remove($user);
				$this->em->flush();

				$this->addFlash("success","User {$user->getUsername()} was deleted");
			} else {
				$this->addFlash("error","User with ID {$id} does not exits");
			}
			return $this->redirectToRoute("user_index");
		}

		return $this->render("GuserBundle::deletebutton.html.twig", array("form" => $form->createView(),));
	}



	/**
	 * @Route("/user/changepassword", name="user_changepassword")
	 * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
	 */
	public function changePasswordAction(Request $request) {
		$passwordValid = False;
		$passwordStrength = False;
		$form = $this->createForm(UserChangePasswordType::class);
		
		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
			$data = $form->getData();
			$user = $this->em->getRepository("GuserBundle:User")->findOneByUsername(
					$this->container->get("security.token_storage")->getToken()->getUser()->getUsername()
				);
        		$encoder = $this->container->get("security.encoder_factory")->getEncoder($user);
			
			# reauth
			$reauth = $encoder->isPasswordValid($user->getPassword(), $data["current_password"], $user->getSalt());
			#check typos
			$newPasswordsMatch = ($data["new_password1"] == $data["new_password2"] ? True : False);
			# strength
			$passwordStrength = $encoder->isPasswordStrength($user->getUsername(), $data["new_password2"]);
			
			if ($reauth) {
				if ($newPasswordsMatch) {
					switch ($passwordStrength) {
						case CryptPasswordEncoder::PASSWORD_RET_LENGTH:	$this->addFlash("error","Password minimal length is " . CryptPasswordEncoder::PASSWORD_MIN_LENGTH); break;
						case CryptPasswordEncoder::PASSWORD_RET_CLASSES: $this->addFlash("error","Password must contain at least " . CryptPasswordEncoder::PASSWORD_MIN_CLASSES . "character classes"); break;
						case CryptPasswordEncoder::PASSWORD_RET_INCLUDESUSERNAME: $this->addFlash("error","Password must not be based on username"); break;

						case CryptPasswordEncoder::PASSWORD_RET_OK:
							$user->setPassword($data["new_password2"]);
							$this->em->persist($user);
							$this->em->flush();
							$this->addFlash("success","Password changed.");
							break;
					}
				} else {
					$this->addFlash("error","New passwords does not match. Password not changed.");
				}
			} else {
				$this->addFlash("error","Reauthentication failed. Password not changed.");
			}
		}

		return $this->render("GuserBundle:User:changepassword.html.twig", array("form" => $form->createView(),));
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

		return $this->render("GuserBundle:User:login.html.twig", array(
			"last_username" => $lastUsername,
			"error" => $error,
		));
	}
	/**
	 * @Route("/logout", name="logout")
	 */
    	public function logoutAction() {
        	return $this->redirectToRoute('/');
	}
}
