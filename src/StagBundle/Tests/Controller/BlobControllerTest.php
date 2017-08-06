<?php

namespace StagBundle\Tests\Controller;

use StagBundle\Entity\Blob;
use StagBundle\Tests\StagWebTestCase;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class BlobControllerTest extends StagWebTestCase {
	protected $client;
	protected $em;
	protected $blobRepo;

	protected $testBlobDataFile = "src/StagBundle/bin/testblob.jpg";
	public function createTestBlob($dataPath="/tmp/testblob.jpg") {
		copy($this->testBlobDataFile, $dataPath);
		
        	$tmp = new Blob();
        	$tmp->setFileName(basename($dataPath));
		$tmp->setDataPath($dataPath);
		return $tmp;
	}




	protected function setUp() {
		parent::setUp();
		if(!$this->client) { $this->client = static::createClient(); }
		if(!$this->em) { $this->em = static::$kernel->getContainer()->get("doctrine")->getManager(); }
		
		$this->blobRepo = $this->em->getRepository("StagBundle:Blob");
	}
	protected function tearDown() {
		parent::tearDown();
	}






	public function testList() {
		$this->logIn();
		
        	$crawler = $this->client->request('GET', '/blob/list');
	        $this->assertGreaterThan(0, $crawler->filter('html:contains("Blobs")')->count());
	}


	public function testAddAction() {
		$this->logIn();	
		
		$testBlob = $this->createTestBlob("/tmp/testblob_add_".mt_rand().".jpg");
						
		$crawler = $this->client->request("GET", "/blob/add");
		$form = $crawler->filter('button[type="submit"]')->form();
		$form['blob[fileHandler]']->upload($testBlob->getDataPath());
        	$this->client->submit($form);
        	$this->assertSame(Response::HTTP_FOUND, $this->client->getResponse()->getStatusCode());
        	
        	$blob = $this->blobRepo->findOneByFileName($testBlob->getFileName());
        	$this->assertNotNull($blob);
        	$this->assertSame($blob->getData(), $testBlob->getData());

		$this->em->remove($blob);
		$this->em->flush();
		$testBlob->removeData();
    	}
    	
    	
    	
    	public function testGetAction() {
		
		$testBlob = $this->createTestBlob(getcwd()."/var/data-stagbundle-blob/testblob_get_".mt_rand().".jpg");
		$this->em->persist($testBlob);
		$this->em->flush();
						
		$crawler = $this->client->request("GET", "/blob/get/{$testBlob->getId()}");
        	$this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

		$crawler = $this->client->request("GET", "/blob/get/{$testBlob->getFileName()}");
        	$this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        	
        	$blob = $this->blobRepo->findOneByFileName($testBlob->getFileName());
		$this->em->remove($blob);
		$this->em->flush();
    	}
    	
    	
	public function testDeleteAction() {
		$this->logIn();
		
		$testBlob = $this->createTestBlob(getcwd()."/var/data-stagbundle-blob/testblob_delete_".mt_rand().".jpg");
		$this->em->persist($testBlob);
		$this->em->flush();


		$crawler = $this->client->request("GET", "/blob/delete/{$testBlob->getId()}");
		$form = $crawler->filter('button[type="submit"]')->form();
		$this->client->submit($form);
		$this->assertSame(Response::HTTP_FOUND, $this->client->getResponse()->getStatusCode());

		$blob = $this->blobRepo->findOneByFileName($testBlob->getFileName());
		$this->assertNull($blob);
    	}

}

?>

