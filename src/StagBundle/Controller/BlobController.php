<?php

namespace StagBundle\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use StagBundle\Entity\Blob;
use StagBundle\Form\BlobType;
use StagBundle\Form\DeleteButtonType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class BlobController extends Controller {
	private $em;


	public function __construct(EntityManagerInterface $em) {
		$this->em = $em;
	}



	/**
	 * @Route("/blob/list", name="blob_list")
	 * @Security("has_role('ROLE_ADMIN')")
	 */
	public function listAction(Request $request) {
        	$blobs = $this->em->getRepository("StagBundle:Blob")->findAll();
		return $this->render("StagBundle:Blob:list.html.twig", [ "blobs" => $blobs ] );
	}


	
	/**
	 * @Route("/blob/add", name="blob_add")
	 * @Security("has_role('ROLE_ADMIN')")
	 */
	public function addAction(Request $request) {
		$blob = new Blob();
		$form = $this->createForm(BlobType::class, $blob);
		
		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
			$blob = $form->getData();
			
			# this might go to entity itself, but i had a hard times access right 
			# directory in production and unit tests, since they had different cwd
			$datadir = realpath($this->get('kernel')->getRootDir()."/../var/data-stagbundle-blob");
			if(!is_dir($datadir)) {mkdir($datadir, 0755);}
			
			$tmpName = md5(uniqid(mt_rand(), true));
			$blob->getFileHandler()->move($datadir, $tmpName);
				
			$blob->setFileName($blob->getFileHandler()->getClientOriginalName());
			$blob->setDataPath("{$datadir}/{$tmpName}");
			$this->em->persist($blob);
			$this->em->flush();
			
			$this->addFlash("success","Blob {$blob->getFileName()} was created");
			return $this->redirectToRoute("blob_list");
		}

		return $this->render("StagBundle:Blob:addedit.html.twig", ["form" => $form->createView()]);
	}



	/**
	 * @Route("/blob/delete/{id}", name="blob_delete")
	 * @Security("has_role('ROLE_ADMIN')")
	 */
	public function deleteAction(Request $request, $id) {
		$blob = $this->em->getRepository("StagBundle:Blob")->find($id);
		$form = $this->createForm(DeleteButtonType::class, $blob,
			["action" => $this->generateUrl("blob_delete", ["id" => $id])]
		);

		$form->handleRequest($request);
		if ($form->isSubmitted() && $form->isValid()) {
			if ($blob) {
				$this->em->remove($blob);
				$this->em->flush();

				$this->addFlash("success","Blob {$blob->getFileName()} was deleted");
			} else {
				$this->addFlash("error","Blob with ID {$id} does not exits");
			}
			return $this->redirectToRoute("blob_list");
		}

		return $this->render("StagBundle::deletebutton.html.twig", array("form" => $form->createView(),));
	}
	
	
	
	/**
	 * @Route("/blob/get/{id}", name="blob_get")
	 */
	public function getAction(Request $request, $id) {
		if(is_numeric($id)) {
			$blob = $this->em->getRepository("StagBundle:Blob")->find($id);
		} else {
			$blob = $this->em->getRepository("StagBundle:Blob")->findOneByFileName($id);
		}
		return new Response($blob->getData(), Response::HTTP_OK, ["content-type" => mime_content_type($blob->getDataPath())]);
	}
}
