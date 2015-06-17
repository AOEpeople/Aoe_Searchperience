++++++++++++++++++++++++
Searchperience Api Client
++++++++++++++++++++++++

:Author: Michael Klapper <michael.klapper@aoemedia.de>
:Author: AOE media <dev@aoemedia.com>
:Description: PHP Library to communicate with the searchperience RestFul API
:Homepage: http://www.searchperience.com
:Build status: |buildStatusIcon|


Installing via Composer
========================

The recommended way to install Searchperience API client is through [Composer](http://getcomposer.org).

1. Add ``aoemedia/searchperience-api-client`` as a dependency in your project's ``composer.json`` file:

::

	{
		"require": {
			"aoemedia/searchperience-api-client": "*"
		},
		"require-dev": {
			"guzzle/plugin-log": "*"
		}
	}

Consider tightening your dependencies to a known version when deploying mission critical applications (e.g. ``1.0.*``).

2. Download and install Composer:

::

	curl -s http://getcomposer.org/installer | php

3. Install your dependencies:

::

	php composer.phar install

4. Require Composer's autoloader

Composer also prepares an autoload file that's capable of autoloading all of the classes in any of the libraries that it downloads. To use it, just add the following line to your code's bootstrap process:

::

	require 'vendor/autoload.php';

You can find out more on how to install Composer, configure autoloading, and other best-practices for defining dependencies at http://getcomposer.org.

Searchperience API Client basics
========================

Add or update documents
-----------

::

	$document = new \Searchperience\Api\Client\Domain\Document();
	$document->setContent('some content');
	$document->setForeignId(12);
	$document->setUrl('http://www.some.test/product/detail');

	$documentRepository = \Searchperience\Common\Factory::getDocumentRepository('http://api.searchperience.com/', 'customerKey', 'username', 'password');
	$documentRepository->add($document);

Get document from indexer
-----------

Get documents by foreign id

::

	$documentRepository = \Searchperience\Common\Factory::getDocumentRepository('http://api.searchperience.com/', 'customerKey', 'username', 'password');
	$document = $documentRepository->getByForeignId(12);


Get documents by query and filters

::

	$documentRepository = \Searchperience\Common\Factory::getDocumentRepository('http://api.searchperience.com/', 'customerKey', 'username', 'password');
	$document = $documentRepository->getAllByFilters(
		0,
		10,
		array(
			'crawl' => array(
				'crawlStart' => new DateTime(),
				'crawlEnd' =>  new DateTime()
			),
			'source' => array(
				'source' => 'magento'
			),
			'query' => array(
				'queryString' => 'test',
				'queryFields' => 'id,url'
			),
			'boostFactor' => array(
				'boostFactorEnd' => 123.00
			),
			'pageRank' => array(
				'pageRankStart' => 0.00,
				'pageRankEnd' => 123.00
			),
			'lastProcessed' => array(
				'processStart' =>  new DateTime(),
				'processEnd' =>  new DateTime()
			),
			'notifications' => array(
				'isduplicateof' => false,
				'lasterror' => true,
				'processingthreadid' => true
			)
		)
	);


::

Delete document from indexer
-----------

::

	$documentRepository = \Searchperience\Common\Factory::getDocumentRepository('http://api.searchperience.com/', 'customerKey', 'username', 'password');
	$documentRepository->deleteByForeignId(12);

Trouble shooting
----------------
There is a HTTP_DEBUG mode which can be easy enabled.

::

	\Searchperience\Common\Factory::$HTTP_DEBUG = TRUE;



.. |buildStatusIcon| image:: https://secure.travis-ci.org/AOEmedia/searchperience-api-client.png?branch=master
   :alt: Build Status
   :target: http://travis-ci.org/AOEmedia/searchperience-api-client