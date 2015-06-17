<?php

namespace Searchperience\Tests\Api\Client\System\Storage;

/**
 * @author Michael Klapper <michael.klapper@aoemedia.de>
 * @date 14.11.12
 * @time 15:17
 */
class RestDocumentBackendTestCase extends \Searchperience\Tests\BaseTestCase {

	/**
	 * @var \Searchperience\Api\Client\System\Storage\RestDocumentBackend
	 */
	protected $documentBackend;

	/**
	 * Initialize test environment
	 *
	 * @return void
	 */
	public function setUp() {
		$this->documentBackend = new \Searchperience\Api\Client\System\Storage\RestDocumentBackend();
		$this->documentBackend->injectDateTimeService(new \Searchperience\Api\Client\System\DateTime\DateTimeService());
	}

	/**
	 * Cleanup test environment
	 *
	 * @return void
	 */
	public function tearDown() {
		$this->documentBackend = NULL;
	}

	/**
	 * Get a dummy Document
	 *
	 * @param array $default
	 * @return \Searchperience\Api\Client\Domain\Document
	 */
	protected function getDocument($default = array()) {
		$document = new \Searchperience\Api\Client\Domain\Document();

		if (count($default) > 0) {
			foreach ($default as $key => $value) {
				$method = 'set' . ucfirst($key);
				$document->$method($value);
			}
		}

		return $document;
	}

	/**
	 * @test
	 */
	public function canGetDocumentByDocumentForeignId() {
		$restClient = new \Guzzle\Http\Client('http://api.searchperience.com/');
		$mock = new \Guzzle\Plugin\Mock\MockPlugin();
		$mock->addResponse(new \Guzzle\Http\Message\Response(201, NULL, $this->getFixtureContent('Api/Client/System/Storage/Fixture/Qvc_foreignId_12.xml')));
		$restClient->addSubscriber($mock);

		$this->documentBackend->injectRestClient($restClient);
		$document = $this->documentBackend->getByForeignId(13211);

		$expectedDocument = $this->getDocument(array(
			'id' => 12,
			'foreignId' => '13211',
			'source' => 'magento',
			'content' => '<xml>some value</xml>',
			'url' => 'http://www.dummy.tld/some/product',
			'generalPriority' => 0,
			'temporaryPriority' => 2,
			'lastProcessing' => '2012-11-14 17:35:03',
			'boostFactor' => 1,
			'noIndex' => 0,
			'isProminent' => 1,
			'isMarkedForProcessing' => 0,
			'mimeType' => 'text/xml'
		));


		$this->assertEquals('2012-11-14 17:35:03',$expectedDocument->getLastProcessing());

		/**
		 * @var $lastProcessingDate \DateTime
		 */
		$lastProcessingDate = $document->getLastProcessingDate();
		$this->assertEquals($lastProcessingDate->format('Y'),'2012');

		$this->assertInstanceOf('\Searchperience\Api\Client\Domain\Document', $document);
		$this->assertEquals($expectedDocument, $document);
	}

	/**
	 * @test
	 */
	public function canGetDocumentByDocumentUrl() {
		$restClient = new \Guzzle\Http\Client('http://api.searchperience.com/');
		$mock = new \Guzzle\Plugin\Mock\MockPlugin();
		$mock->addResponse(new \Guzzle\Http\Message\Response(201, NULL, $this->getFixtureContent('Api/Client/System/Storage/Fixture/Qvc_foreignId_12.xml')));
		$restClient->addSubscriber($mock);

		$this->documentBackend->injectRestClient($restClient);
		$document = $this->documentBackend->getByUrl('http://www.dummy.tld/some/product');

		$expectedDocument = $this->getDocument(array(
			'id' => 12,
			'foreignId' => '13211',
			'source' => 'magento',
			'content' => '<xml>some value</xml>',
			'url' => 'http://www.dummy.tld/some/product',
			'generalPriority' => 0,
			'temporaryPriority' => 2,
			'lastProcessing' => '2012-11-14 17:35:03',
			'boostFactor' => 1,
			'noIndex' => 0,
			'isProminent' => 1,
			'isMarkedForProcessing' => 0,
			'mimeType' => 'text/xml'
		));

		$this->assertInstanceOf('\Searchperience\Api\Client\Domain\Document', $document);
		$this->assertEquals($expectedDocument, $document);
	}

	/**
	 * @test
	 */
	public function canGetAllDocuments() {
		$restClient = new \Guzzle\Http\Client('http://api.searchperience.com/');
		$mock = new \Guzzle\Plugin\Mock\MockPlugin();
		$mock->addResponse(new \Guzzle\Http\Message\Response(201, NULL, $this->getFixtureContent('Api/Client/System/Storage/Fixture/Qvc_foreignId_12.xml')));
		$restClient->addSubscriber($mock);

		$this->documentBackend->injectRestClient($restClient);

		$filtersCollection = new \Searchperience\Api\Client\Domain\Filters\FilterCollection();
		$documents = $this->documentBackend->getAllByFilterCollection(0, 10, $filtersCollection);

		$expectedDocument = $this->getDocument(array(
			'id' => 12,
			'foreignId' => '13211',
			'source' => 'magento',
			'content' => '<xml>some value</xml>',
			'url' => 'http://www.dummy.tld/some/product',
			'generalPriority' => 0,
			'temporaryPriority' => 2,
			'lastProcessing' => '2012-11-14 17:35:03',
			'boostFactor' => 1,
			'noIndex' => 0,
			'isProminent' => 1,
			'isMarkedForProcessing' => 0,
			'mimeType' => 'text/xml'
		));

		$this->assertInstanceOf('\Searchperience\Api\Client\Domain\DocumentCollection', $documents);
		$this->assertInstanceOf('\Searchperience\Api\Client\Domain\Document', $documents[0]);

		$this->assertEquals($expectedDocument, $documents[0]);
	}

	/**
	 * @test
	 */
	public function canDeleteDocumentByForeignId() {
		$restClient = new \Guzzle\Http\Client('http://api.searchperience.com/');
		$mock = new \Guzzle\Plugin\Mock\MockPlugin();
		$mock->addResponse(new \Guzzle\Http\Message\Response(200));
		$restClient->addSubscriber($mock);

		$this->documentBackend->injectRestClient($restClient);
		$this->assertEquals(200, $this->documentBackend->deleteByForeignId(13211));
	}

	/**
	 * @test
	 */
	public function canDeleteDocumentBySource() {
		$restClient = new \Guzzle\Http\Client('http://api.searchperience.com/');
		$mock = new \Guzzle\Plugin\Mock\MockPlugin();
		$mock->addResponse(new \Guzzle\Http\Message\Response(200));
		$restClient->addSubscriber($mock);

		$this->documentBackend->injectRestClient($restClient);
		$this->assertEquals(200, $this->documentBackend->deleteBySource("magento"));
	}

	/**
	 * @test
	 * @expectedException \Searchperience\Common\Http\Exception\ClientErrorResponseException
	 */
	public function verifyPostDocumentThrowsClientErrorResponseExceptionWhileInvalidAuthenticationGiven() {
		$restClient = new \Guzzle\Http\Client('http://api.searchperience.com/');
		$mock = new \Guzzle\Plugin\Mock\MockPlugin();
		$mock->addResponse(new \Guzzle\Http\Message\Response(403));
		$restClient->addSubscriber($mock);
		$this->documentBackend->injectRestClient($restClient);
		$this->documentBackend->post($this->getDocument());
	}

	/**
	 * @test
	 */
	public function verifyPostCreateNewDocument() {
		$restClient = new \Guzzle\Http\Client('http://api.searchperience.com/');
		$mock = new \Guzzle\Plugin\Mock\MockPlugin();
		$mock->addResponse(new \Guzzle\Http\Message\Response(201));
		$restClient->addSubscriber($mock);
		$this->documentBackend->injectRestClient($restClient);
		$statusCode = $this->documentBackend->post($this->getDocument());
		$this->assertEquals($statusCode, 201);
	}

	/**
	 * @test
	 */
	public function canGetAllByFiltersTriggersExpectedBackendUrl() {
		$filters = array(
						'crawl' => array(
							'crawlStart' => $this->getUTCDateTimeObject('2014-01-03 10:00:00'),
							'crawlEnd' => $this->getUTCDateTimeObject('2014-01-03 10:00:00')
						),
						'source' => array('source' => 'magento'),
						'query' => array('queryString' => 'test', 'queryFields' => 'id,url'),
						'boostFactor' => array('boostFactorEnd' => 123.00),
						'pageRank' => array('pageRankStart' => 0.00, 'pageRankEnd' => 123.00),
						'lastProcessed' => array(
							'processStart' => $this->getUTCDateTimeObject('2014-01-01 10:00:00'),
							'processEnd' => $this->getUTCDateTimeObject('2014-01-03 10:00:00')
						),
						'notifications' => array('isduplicateof' => false, 'lasterror' => true, 'processingthreadid' => true),
		);

		$expectedUrl = '/{customerKey}/documents?start=0&limit=10&'.
						'crawlStart=2014-01-03%2010%3A00%3A00&'.
						'crawlEnd=2014-01-03%2010%3A00%3A00&'.
						'source=magento&query=test&'.
						'queryFields=id%2Curl&'.
						'boostFactorEnd=123&'.
						'pageRankEnd=123&'.
						'processStart=2014-01-01%2010%3A00%3A00&'.
						'processEnd=2014-01-03%2010%3A00%3A00&'.
						'lasterror=1&'.
						'processingthreadid=1';

		//$this->markTestSkipped('The test is not valid anymore.');
		$responsetMock = $this->getMock('\Guzzle\Http\Message\Response', array('xml'), array(), '', false);
		$responsetMock->expects($this->once())->method('xml')->will($this->returnValue(new \SimpleXMLElement('<?xml version="1.0"?><documents></documents>')));

		$resquestMock = $this->getMock('\Guzzle\Http\Message\Request',array('setAuth','send'),array(),'',false);
		$resquestMock->expects($this->once())->method('setAuth')->will($this->returnCallback(function () use ($resquestMock) {
			return $resquestMock;
		}));
		$resquestMock->expects($this->once())->method('send')->will($this->returnCallback(function () use ($responsetMock) {
			return $responsetMock;
		}));

		$restClient = $this->getMock('\Guzzle\Http\Client',array('get','setAuth','send'),array('http://api.searcperience.com/'));
		$restClient->expects($this->once())->method('get')->with($expectedUrl)->will($this->returnCallback(function() use ($resquestMock) {
			return $resquestMock;
		}));


		$mock = new \Guzzle\Plugin\Mock\MockPlugin();
		$mock->addResponse(new \Guzzle\Http\Message\Response(201, NULL, $this->getFixtureContent('Api/Client/System/Storage/Fixture/Qvc_foreignId_12.xml')));
		$restClient->addSubscriber($mock);

		$this->documentBackend->injectRestClient($restClient);

		$filterCollectionFactory = new \Searchperience\Api\Client\Domain\Filters\FilterCollectionFactory();
		$filterCollection = $filterCollectionFactory->createFromFilterArguments($filters);
		$this->documentBackend->getAllByFilterCollection(0, 10, $filterCollection);
	}
}
