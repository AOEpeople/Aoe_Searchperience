<?php

namespace Searchperience\Api\Client\Domain;

use Symfony\Component\Validator\Constraints as Assert;

/**
 *
 * @author Michael Klapper <michael.klapper@aoemedia.de>
 */
class Document {

	/**
	 * @var integer
	 */
	protected $id;

	/**
	 * @var \DateTime
	 */
	protected $lastProcessingDate = null;

	/**
	 * @var int
	 */
	protected $boostFactor;

	/**
	 * @var int
	 */
	protected $isProminent;

	/**
	 * Schedule a document for (re-)processing
	 *
	 * @var int
	 */
	protected $isMarkedForProcessing;

	/**
	 * Schedule a document for deleting
	 *
	 * @var int
	 */
	protected $isMarkedForDeletion;

	/**
	 * Can be used to remove a document from the index
	 *
	 * @var int
	 */
	protected $noIndex;

	/**
	 * @var string
	 * @Assert\Length(min = 1, max = 255)
	 */
	protected $foreignId;

	/**
	 * @var string
	 * @Assert\Url
	 * @Assert\Length(max = 1000)
	 */
	protected $url;

	/**
	 * @var string
	 * @Assert\Length(min = 3, max = 40)
	 * @Assert\NotBlank
	 */
	protected $source;

	/**
	 * @var string
	 * @Assert\Length(min = 3, max = 128)
	 * @Assert\NotBlank
	 */
	protected $mimeType;

	/**
	 * Content can be set up to 3MB
	 *
	 * @var string
	 * @Assert\Length(max = 3145728)
	 */
	protected $content;

	/**
	 * @var string
	 */
	protected $generalPriority;

	/**
	 * @var string
	 */
	protected $temporaryPriority;

	/**
	 * @param string $lastProcessing
	 * @deprecated Please now use setLastProcessingDate
	 */
	public function setLastProcessing($lastProcessing) {
		$this->lastProcessingDate  = \DateTime::createFromFormat('Y-m-d H:i:s',$lastProcessing,new \DateTimeZone('UTC'));
	}

	/**
	 * @return string
	 * @deprecated Please now use getLastProcessingDate
	 */
	public function getLastProcessing() {
		return ($this->lastProcessingDate instanceof \DateTime) ?  $this->lastProcessingDate->format('Y-m-d H:i:s') : '';
	}

	/**
	 * @param \DateTime $lastProcessingDate
	 * @return void
	 */
	public function setLastProcessingDate(\DateTime $lastProcessingDate) {
		return $this->lastProcessingDate = $lastProcessingDate;
	}

	/**
	 * @return \DateTime
	 */
	public function getLastProcessingDate() {
		return $this->lastProcessingDate;
	}

	/**
	 * @param int $boostFactor
	 */
	public function setBoostFactor($boostFactor) {
		$this->boostFactor = $boostFactor;
	}

	/**
	 * @return int
	 */
	public function getBoostFactor() {
		return $this->boostFactor;
	}

	/**
	 * @param int $id
	 */
	public function setId($id) {
		$this->id = $id;
	}

	/**
	 * @return int
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * @param int $isMarkedForProcessing
	 */
	public function setIsMarkedForProcessing($isMarkedForProcessing) {
		$this->isMarkedForProcessing = $isMarkedForProcessing;
	}

	/**
	 * @return int
	 */
	public function getIsMarkedForProcessing() {
		return $this->isMarkedForProcessing;
	}


	/**
	 * @param int $isMarkedForDeletion
	 */
	public function setIsMarkedForDeletion($isMarkedForDeletion) {
		$this->isMarkedForDeletion = $isMarkedForDeletion;
	}

	/**
	 * @return int
	 */
	public function getIsMarkedForDeletion() {
		return $this->isMarkedForDeletion;
	}

	/**
	 * @param int $isProminent
	 */
	public function setIsProminent($isProminent) {
		$this->isProminent = $isProminent;
	}

	/**
	 * @return int
	 */
	public function getIsProminent() {
		return $this->isProminent;
	}

	/**
	 * @param int $noIndex
	 */
	public function setNoIndex($noIndex) {
		$this->noIndex = $noIndex;
	}

	/**
	 * @return int
	 */
	public function getNoIndex() {
		return $this->noIndex;
	}

	/**
	 * Maximum content size is 3MB
	 *
	 * @param string $content
	 */
	public function setContent($content) {
		$this->content = $content;
	}

	/**
	 * @return string
	 */
	public function getContent() {
		return $this->content;
	}

	/**
	 * The foreignId can be a string of:
	 * 0-9a-zA-Z_-.:
	 *
	 * @param string $foreignId
	 */
	public function setForeignId($foreignId) {
		$this->foreignId = $foreignId;
	}

	/**
	 * @return string
	 */
	public function getForeignId() {
		return $this->foreignId;
	}

	/**
	 * @param string $generalPriority
	 */
	public function setGeneralPriority($generalPriority) {
		$this->generalPriority = $generalPriority;
	}

	/**
	 * @return string
	 */
	public function getGeneralPriority() {
		return $this->generalPriority;
	}

	/**
	 * @param string $mimeType
	 */
	public function setMimeType($mimeType) {
		$this->mimeType = $mimeType;
	}

	/**
	 * @return string
	 */
	public function getMimeType() {
		return $this->mimeType;
	}

	/**
	 * @param string $source
	 */
	public function setSource($source) {
		$this->source = $source;
	}

	/**
	 * @return string
	 */
	public function getSource() {
		return $this->source;
	}

	/**
	 * @param string $temporaryPriority
	 */
	public function setTemporaryPriority($temporaryPriority) {
		$this->temporaryPriority = $temporaryPriority;
	}

	/**
	 * @return string
	 */
	public function getTemporaryPriority() {
		return $this->temporaryPriority;
	}

	/**
	 * @param string $url
	 */
	public function setUrl($url) {
		$this->url = $url;
	}

	/**
	 * @return string
	 */
	public function getUrl() {
		return $this->url;
	}
}
