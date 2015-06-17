<?php

namespace Searchperience\Tests\Api\Client;
use Searchperience\Api\Client\Domain\Filters\FilterCollection;
use Searchperience\Api\Client\Domain\Filters\FilterCollectionFactory;
use Searchperience\Api\Client\Domain\Filters\SourceFilter;

/**
 * @author Michael Klapper <michael.klapper@aoemedia.de>
 * @date 14.11.12
 * @time 15:13
 */
class DocumentRepositoryTestCase extends \Searchperience\Tests\BaseTestCase {

	/**
	 * @var \Searchperience\Api\Client\Domain\DocumentRepository
	 */
	protected $documentRepository;

	/**
	 * Initialize test environment
	 *
	 * @return void
	 */
	public function setUp() {

	}

	/**
	 * Cleanup test environment
	 *
	 * @return void
	 */
	public function tearDown() {
		$this->documentRepository = NULL;
	}

	/**
	 * Provides some valid fixtures
	 *
	 * @return array
	 */
	public function verifyGetByForeignIdReturnsValidDomainDocumentDataProvider() {
		return array(
			array(312),
			array(0),
			array('asd'),
			array('158977_1'),
			array('158977-1'),
		);
	}

	/**
	 * @test
	 * @params mixed $foreignId
	 * @dataProvider verifyGetByForeignIdReturnsValidDomainDocumentDataProvider
	 */
	public function verifyGetByForeignIdReturnsValidDomainDocument($foreignId) {
		$this->documentRepository = new \Searchperience\Api\Client\Domain\DocumentRepository();
		$storageBackend = $this->getMock('\Searchperience\Api\Client\System\Storage\RestDocumentBackend', array('getByForeignId'));
		$storageBackend->expects($this->once())
			->method('getByForeignId')
			->will($this->returnValue(new \Searchperience\Api\Client\Domain\Document()));

		$this->documentRepository->injectStorageBackend($storageBackend);
		$document = $this->documentRepository->getByForeignId($foreignId);
		$this->assertInstanceOf('\Searchperience\Api\Client\Domain\Document', $document);
	}

	/**
	 * Provides some valid fixtures
	 *
	 * @return array
	 */
	public function verifyDeleteByForeignIdReturnsValidDomainDocumentDataProvider() {
		return array(
			array(312),
			array(0),
			array('asd'),
			array('158977_1'),
			array('158977-1'),
		);
	}

	/**
	 * @test
	 * @params mixed $foreignId
	 * @dataProvider verifyDeleteByForeignIdReturnsValidDomainDocumentDataProvider
	 */
	public function verifyDeleteByForeignIdReturnsValidDomainDocument($foreignId) {
		$this->documentRepository = new \Searchperience\Api\Client\Domain\DocumentRepository();
		$storageBackend = $this->getMock('\Searchperience\Api\Client\System\Storage\RestDocumentBackend', array('deleteByForeignId'));
		$storageBackend->expects($this->once())
			->method('deleteByForeignId')
			->with($foreignId)
			->will($this->returnValue(200));

		$this->documentRepository->injectStorageBackend($storageBackend);
		$statusCode = $this->documentRepository->deleteByForeignId($foreignId);
		$this->assertEquals(200, $statusCode);
	}

	/**
	 * @test
	 */
	public function verifyGetByUrlReturnsValidDomainDocument() {
		$url = 'http://www.qvc.it';
		$this->documentRepository = new \Searchperience\Api\Client\Domain\DocumentRepository();
		$storageBackend = $this->getMock('\Searchperience\Api\Client\System\Storage\RestDocumentBackend', array('getByUrl'));
		$storageBackend->expects($this->once())
				->method('getByUrl')
				->with($url)
				->will($this->returnValue(new \Searchperience\Api\Client\Domain\Document()));

		$this->documentRepository->injectStorageBackend($storageBackend);
		$document = $this->documentRepository->getByUrl($url);
		$this->assertInstanceOf('\Searchperience\Api\Client\Domain\Document', $document);
	}

	/**
	 * @test
	 */
	public function verifyGetAllReturnsValidDomainDocument() {
		$this->documentRepository = new \Searchperience\Api\Client\Domain\DocumentRepository();

		$filterCollection = new FilterCollection();
		$sourceFilter = new SourceFilter();
		$sourceFilter->setSource('magento');
		$filterCollection->addFilter($sourceFilter);

		$storageBackend = $this->getMock('\Searchperience\Api\Client\System\Storage\RestDocumentBackend', array('getAllByFilterCollection'));
		$storageBackend->expects($this->once())
				->method('getAllByFilterCollection')
				->with(1,11, $filterCollection)
				->will($this->returnValue(new \Searchperience\Api\Client\Domain\Document()));


		$this->documentRepository->injectStorageBackend($storageBackend);
		$this->documentRepository->injectFilterCollectionFactory(new FilterCollectionFactory());
		$document = $this->documentRepository->getAll(1,11,'magento');

		$this->assertInstanceOf('\Searchperience\Api\Client\Domain\Document', $document);
	}

	/**
	 * Provides some valid fixtures
	 *
	 * @return array
	 */
	public function verifyDeleteBySourceReturnsValidDomainDocumentDataProvider() {
		return array(
			array('magento')
		);
	}

	/**
	 * @test
	 * @param mixed $source
	 * @dataProvider verifyDeleteBySourceReturnsValidDomainDocumentDataProvider
	 */
	public function verifyDeleteBySourceReturnsValidDomainDocument($source) {
		$this->documentRepository = new \Searchperience\Api\Client\Domain\DocumentRepository();
		$storageBackend = $this->getMock('\Searchperience\Api\Client\System\Storage\RestDocumentBackend', array('deleteBySource'));
		$storageBackend->expects($this->once())
			->method('deleteBySource')
			->will($this->returnValue(200));

		$this->documentRepository->injectStorageBackend($storageBackend);
		$statusCode = $this->documentRepository->deleteBySource($source);
		$this->assertEquals(200, $statusCode);
	}

	/**
	 * @test
	 * @expectedException \Searchperience\Common\Exception\InvalidArgumentException
	 */
	public function getByForeignIdThrowsInvalidArgumentExceptionOnInvalidArgument() {
		$this->documentRepository = new \Searchperience\Api\Client\Domain\DocumentRepository();
		$this->documentRepository->getByForeignId(NULL);
	}

	/**
	 * @test
	 * @expectedException \Searchperience\Common\Exception\InvalidArgumentException
	 */
	public function addThrowsInvalidArgumentExceptionOnInvalidArgument() {
		$violationList = $this->getMock('\Symfony\Component\Validator\ConstraintViolationList', array('count'), array(), '', FALSE);
		$violationList->expects($this->once())
			->method('count')
			->will($this->returnValue(1));
		$validator = $this->getMock('\Symfony\Component\Validator\Validator', array('validate'), array(), '', FALSE);
		$validator->expects($this->once())
			->method('validate')
			->will($this->returnValue($violationList));
		$this->documentRepository = new \Searchperience\Api\Client\Domain\DocumentRepository();
		$this->documentRepository->injectValidator($validator);
		$this->documentRepository->add(new \Searchperience\Api\Client\Domain\Document());
	}
}
