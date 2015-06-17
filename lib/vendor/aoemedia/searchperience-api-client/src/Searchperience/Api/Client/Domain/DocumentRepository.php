<?php

namespace Searchperience\Api\Client\Domain;

use Searchperience\Api\Client\Domain\Filters\FiltersCollection;
use Symfony\Component\Validator\Validation;

/**
 * @author Michael Klapper <michael.klapper@aoemedia.de>
 * @date 14.11.12
 * @time 15:13
 */
class DocumentRepository {

	/**
	 * @var \Searchperience\Api\Client\System\Storage\DocumentBackendInterface
	 */
	protected $storageBackend;

	/**
	 * @var \Symfony\Component\Validator\ValidatorInterface
	 */
	protected $documentValidator;

	/**
	 * @var \Searchperience\Api\Client\Domain\Filters\FilterCollectionFactory
	 */
	protected $filterCollectionFactory;

	/**
	 * Injects the storage backend.
	 *
	 * @param \Searchperience\Api\Client\System\Storage\DocumentBackendInterface $storageBackend
	 * @return void
	 */
	public function injectStorageBackend(\Searchperience\Api\Client\System\Storage\DocumentBackendInterface $storageBackend) {
		$this->storageBackend = $storageBackend;
	}

	/**
	 * Injects the validation service
	 *
	 * @param \Symfony\Component\Validator\ValidatorInterface $documentValidator
	 * @return void
	 */
	public function injectValidator(\Symfony\Component\Validator\ValidatorInterface $documentValidator) {
		$this->documentValidator = $documentValidator;
	}

	/**
	 * Injects the filter collection factory
	 *
	 * @param \Searchperience\Api\Client\Domain\Filters\FilterCollectionFactory $filterCollectionFactory
	 * @return void
	 */
	public function injectFilterCollectionFactory(\Searchperience\Api\Client\Domain\Filters\FilterCollectionFactory $filterCollectionFactory) {
		$this->filterCollectionFactory = $filterCollectionFactory;
	}

	/**
	 * Add a new Document to the index
	 *
	 * @param \Searchperience\Api\Client\Domain\Document $document
	 * @throws \Searchperience\Common\Exception\InvalidArgumentException
	 * @return integer HTTP Status code
	 */
	public function add(\Searchperience\Api\Client\Domain\Document $document) {
		$violations = $this->documentValidator->validate($document);

		if ($violations->count() > 0) {
			throw new \Searchperience\Common\Exception\InvalidArgumentException('Given object of type "' . get_class($document) . '" is not valid: ' . PHP_EOL . $violations);
		}

		$status = $this->storageBackend->post($document);
		return $status;
	}

	/**
	 * Get a Document by foreignId
	 *
	 * The foreignId can be a string of:
	 * 0-9a-zA-Z_-.:
	 * Is valid if it is an alphanumeric string, which is defined as [[:alnum:]]
	 *
	 * @param string $foreignId
	 *
	 * @throws \Searchperience\Common\Exception\InvalidArgumentException
	 * @throws \Searchperience\Common\Http\Exception\DocumentNotFoundException
	 * @return \Searchperience\Api\Client\Domain\Document $document
	 */
	public function getByForeignId($foreignId) {
		if (!is_string($foreignId) && !is_integer($foreignId) || preg_match('/^[a-zA-Z0-9_-]*$/u', $foreignId) !== 1) {
			throw new \Searchperience\Common\Exception\InvalidArgumentException('Method "' . __METHOD__ . '" accepts only strings values as $foreignId. Input was: ' . serialize($foreignId));
		}

		$document = $this->storageBackend->getByForeignId($foreignId);
		return $document;
	}

	/**
	 * Get a Document by url
	 *
	 * The url can be a string of:
	 * 0-9a-zA-Z_-.:
	 * Is valid if it is an alphanumeric string, which is defined as [[:alnum:]]
	 *
	 * @param string $url
	 *
	 * @throws \Searchperience\Common\Exception\InvalidArgumentException
	 * @throws \Searchperience\Common\Http\Exception\DocumentNotFoundException
	 * @return \Searchperience\Api\Client\Domain\Document $document
	 */
	public function getByUrl($url) {
		if (!is_string($url) ) {
			throw new \Searchperience\Common\Exception\InvalidArgumentException('Method "' . __METHOD__ . '" accepts only strings values as $url. Input was: ' . serialize($url));
		}

		$document = $this->storageBackend->getByUrl($url);
		return $document;
	}

	/**
	 * Get all documents by source
	 *
	 * The source can be a string of:
	 * 0-9a-zA-Z_-.:
	 * Is valid if it is an alphanumeric string, which is defined as [[:alnum:]]
	 *
	 * @param int $start
	 * @param int $limit
	 * @param string $source
	 *
	 * @throws \Searchperience\Common\Exception\InvalidArgumentException
	 * @throws \Searchperience\Common\Http\Exception\DocumentNotFoundException
	 * @return \Searchperience\Api\Client\Domain\DocumentCollection
	 * @deprecated Please now use getAllByFilters with a filter arguments array or getAllByFilterCollection with a proper FilterCollection
	 */
	public function getAll($start = 0, $limit = 10, $source = '') {
		if (isset($source) && (!is_string($source) && !is_integer($source) || preg_match('/^[a-zA-Z0-9_-]*$/u', $source) !== 1)) {
			throw new \Searchperience\Common\Exception\InvalidArgumentException('Method "' . __METHOD__ . '" accepts only strings values as $url. Input was: ' . serialize($source));
		}
		if ( !is_integer($start) ) {
			throw new \Searchperience\Common\Exception\InvalidArgumentException('Method "' . __METHOD__ . '" accepts only integer values as $start. Input was: ' . serialize($start));
		}
		if (!is_integer($limit)) {
			throw new \Searchperience\Common\Exception\InvalidArgumentException('Method "' . __METHOD__ . '" accepts only integer values as $limit. Input was: ' . serialize($limit));
		}

		$filterCollection = $this->filterCollectionFactory->createFromFilterArguments(
				array('source' => array('source' => $source))
		);

		return $this->getAllByFilterCollection($start, $limit, $filterCollection);
	}


	/**
	 * Method to retrieve all documents by filters
	 *
	 * @param int $start
	 * @param int $limit
	 * @param array $filterArguments
	 *
	 * @throws \Searchperience\Common\Exception\InvalidArgumentException
	 * @throws \Searchperience\Common\Http\Exception\DocumentNotFoundException
	 * @return \Searchperience\Api\Client\Domain\DocumentCollection
	 */
	public function getAllByFilters($start = 0, $limit = 10, array $filterArguments = array()){
		if ( !is_integer($start) ) {
			throw new \Searchperience\Common\Exception\InvalidArgumentException('Method "' . __METHOD__ . '" accepts only integer values as $start. Input was: ' . serialize($start));
		}
		if (!is_integer($limit)) {
			throw new \Searchperience\Common\Exception\InvalidArgumentException('Method "' . __METHOD__ . '" accepts only integer values as $limit. Input was: ' . serialize($limit));
		}
		if (!is_array($filterArguments)) {
			throw new \Searchperience\Common\Exception\InvalidArgumentException('Method "' . __METHOD__ . '" accepts only integer values as $filterArguments. Input was: ' . serialize($filterArguments));
		}

		$filterCollection = $this->filterCollectionFactory->createFromFilterArguments($filterArguments);
		$documents = $this->getAllByFilterCollection($start, $limit, $filterCollection);

		return $documents;
	}

	/**
	 * @param int $start
	 * @param int $limit
	 * @param Filters\FilterCollection $filtersCollection
	 * @return DocumentCollection
	 * @throws \Searchperience\Common\Exception\InvalidArgumentException
	 */
	public function getAllByFilterCollection($start, $limit, \Searchperience\Api\Client\Domain\Filters\FilterCollection $filtersCollection= null) {
		if (!is_integer($start)) {
			throw new \Searchperience\Common\Exception\InvalidArgumentException('Method "' . __METHOD__ . '" accepts only integer values as $start. Input was: ' . serialize($start));
		}
		if (!is_integer($limit)) {
			throw new \Searchperience\Common\Exception\InvalidArgumentException('Method "' . __METHOD__ . '" accepts only integer values as $limit. Input was: ' . serialize($limit));
		}

		$documents = $this->storageBackend->getAllByFilterCollection($start, $limit, $filtersCollection);
		return $documents;
	}

	/**
	 * Delete a Document by foreignId
	 *
	 * The foreignId can be a string of:
	 * 0-9a-zA-Z_-.:
	 * Is valid if it is an alphanumeric string, which is defined as [[:alnum:]]
	 *
	 * @param string $foreignId
	 *
	 * @throws \Searchperience\Common\Exception\InvalidArgumentException
	 * @throws \Searchperience\Common\Http\Exception\DocumentNotFoundException
	 * @return integer HTTP status code
	 */
	public function deleteByForeignId($foreignId) {
		if (!is_string($foreignId) && !is_integer($foreignId) || preg_match('/^[a-zA-Z0-9_-]*$/u', $foreignId) !== 1) {
			throw new \Searchperience\Common\Exception\InvalidArgumentException('Method "' . __METHOD__ . '" accepts only strings values as $foreignId. Input was: ' . serialize($foreignId));
		}

		$statusCode = $this->storageBackend->deleteByForeignId($foreignId);
		return $statusCode;
	}

	/**
	 * Delete Document by source
	 *
	 * The source can be a string of:
	 * 0-9a-zA-Z_-.:
	 *
	 * @param string $source
	 *
	 * @throws \Searchperience\Common\Exception\InvalidArgumentException
	 * @throws \Searchperience\Common\Exception\DocumentNotFoundException
	 * @return \Searchperience\Api\Client\Domain\Document $document
	 */
	public function deleteBySource($source) {
		if (!is_string($source)) {
			throw new \Searchperience\Common\Exception\InvalidArgumentException('Method "' . __METHOD__ . '" accepts only strings values as $source. Input was: ' . serialize($source));
		}

		$document = $this->storageBackend->deleteBySource($source);
		return $document;
	}
}
