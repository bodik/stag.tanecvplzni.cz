<?php

namespace StagBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

/**
  * Blob
  *
  * @ORM\Table(name="blobx")
  * @ORM\Entity(repositoryClass="StagBundle\Repository\BlobRepository")
  * @ORM\HasLifecycleCallbacks
  */
class Blob {
	const DATADIR = "../var/data-stagbundle-blob";
	
	/**
	 * @ORM\Column(name="id", type="integer")
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	private $id;
	
	/**
	 * @ORM\Column(name="file_name", type="string", length=255, unique=true)
	 */
	private $fileName;
	
	/**
	 * @ORM\Column(name="data_path", type="string", length=1024)
	 */
	private $dataPath;

	/**
	 * Virtual field used for handling the file
	 * @Assert\File(maxSize = "20M")
	 */
	private $fileHandler;
	
	

	public function getId() { return $this->id; }

	public function getFileName() { return $this->fileName; }
	public function setFileName($fileName) { $this->fileName = $fileName; return $this; }
	
	public function getDataPath() { return $this->dataPath; }
	public function setDataPath($dataPath) { $this->dataPath = $dataPath; return $this; }
	
	public function getFileHandler() { return $this->fileHandler; }
	public function setFileHandler($fileHandler) { $this->fileHandler = $fileHandler; return $this; }
	
	public function getData() { return file_get_contents($this->dataPath); }
	public function setData($data) { file_put_contents($this->dataPath, $data); return $this; }


	/* lifecycle hooks */
	
	/**
	 * @ORM\PostRemove()
	 */
	public function removeData() {
		unlink($this->getDataPath());
	}
	
	 
	 
	/* db storage implementation; works fine but wont scale to 200MB+ files die to php max mem limit */
	/*
	@XXXORM\Column(name="data", type="blob")
	private $data;

	@XXXORM\Column(name="mime_type", type="string", length=1024)
	private $mimeType;

	public function getData() { return $this->data; }
	public function setData($data) { $this->data = $data; return $this; }
	 * @XXXORM\PrePersist()
	 * @XXXORM\PreUpdate()
	public function saveData() {
		if($this->fileHandler) {
			$this->setFileName($this->fileHandler->getClientOriginalName());
			$this->setMimeType($this->fileHandler->getClientMimeType());
			
			$tmpName = md5(uniqid(mt_rand(), true)) . '.' . $this->fileHandler->guessExtension();
			try {
				$this->fileHandler->move(sys_get_temp_dir()."/", $tmpName);
			} catch (\Exception $e) {}

			$this->setData(file_get_contents(sys_get_temp_dir()."/".$tmpName));
			unlink(sys_get_temp_dir()."/".$tmpName);
		}
	}*/
}