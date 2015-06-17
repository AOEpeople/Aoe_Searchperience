<?php

namespace Searchperience\Tests\Api\Client;

/**
 * @author Michael Klapper <michael.klapper@aoemedia.de>
 * @date 14.11.12
 * @time 17:50
 */
class DocumentTestCase extends \Searchperience\Tests\BaseTestCase {

	/**
	 * @var \Searchperience\Api\Client\Domain\Document
	 */
	protected $document;

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
		$this->document = null;
	}

	/**
	 * @test
	 */
	public function verifyGetterAndSetter() {
		$this->document = new \Searchperience\Api\Client\Domain\Document();

		$this->document->setId(12);
		$this->document->setForeignId(312);
		$this->document->setMimeType('application/json');
		$this->document->setContent('<document></document>');
		$this->document->setGeneralPriority(0);
		$this->document->setTemporaryPriority(2);
		$this->document->setSource('someSourceString');
		$this->document->setUrl('https://api.searchperience.com/endpoint');
		$this->document->setBoostFactor(2);
		$this->document->setIsProminent(1);
		$this->document->setIsMarkedForProcessing(1);
		$this->document->setIsMarkedForDeletion(1);
		$this->document->setNoIndex(1);

		$this->assertEquals($this->document->getId(), 12);
		$this->assertEquals($this->document->getForeignId(), 312);
		$this->assertEquals($this->document->getMimeType(), 'application/json');
		$this->assertEquals($this->document->getContent(), '<document></document>');
		$this->assertEquals($this->document->getGeneralPriority(), 0);
		$this->assertEquals($this->document->getTemporaryPriority(), 2);
		$this->assertEquals($this->document->getSource(), 'someSourceString');
		$this->assertEquals($this->document->getUrl(), 'https://api.searchperience.com/endpoint');
		$this->assertEquals($this->document->getBoostFactor(), 2);
		$this->assertEquals($this->document->getIsProminent(), 1);
		$this->assertEquals($this->document->getIsMarkedForProcessing(), 1);
		$this->assertEquals($this->document->getIsMarkedForDeletion(), 1);
		$this->assertEquals($this->document->getNoIndex(), 1);
	}
}
