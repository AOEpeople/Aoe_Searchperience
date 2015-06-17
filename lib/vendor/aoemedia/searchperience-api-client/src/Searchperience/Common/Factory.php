<?php

namespace Searchperience\Common;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;

/**
 * @author Michael Klapper <michael.klapper@aoemedia.de>
 * @date 18.11.12
 */
class Factory {

	/**
	 * @var bool
	 */
	public static $HTTP_DEBUG = FALSE;

	/**
	 * Create a new instance of DocumentRepository
	 *
	 * @param string $baseUrl Example: http://api.searchperience.com/
	 * @param string $customerKey Example: qvc
	 * @param string $username
	 * @param string $password
	 *
	 * @throws \Searchperience\Common\Exception\RuntimeException
	 * @internal param string $customerkey
	 * @return \Searchperience\Api\Client\Domain\DocumentRepository
	 */
	public static function getDocumentRepository($baseUrl, $customerKey, $username, $password) {
		// TODO resolve this "autoloading" in a right way
		class_exists('\Symfony\Component\Validator\Constraints\Url');
		class_exists('\Symfony\Component\Validator\Constraints\NotBlank');
		class_exists('\Symfony\Component\Validator\Constraints\Length');

		$guzzle = new \Guzzle\Http\Client();
		$guzzle->setConfig(array(
			'customerKey' => $customerKey,
			'redirect.disable' => true
		));

		if (self::$HTTP_DEBUG === TRUE) {
			if (class_exists('\Guzzle\Plugin\Log\LogPlugin')) {
				$guzzle->addSubscriber(\Guzzle\Plugin\Log\LogPlugin::getDebugPlugin());
			} else {
				throw new \Searchperience\Common\Exception\RuntimeException('Please run "composer install --dev" to install "guzzle/plugin-log"');
			}
		}


		$dateTimeService = new \Searchperience\Api\Client\System\DateTime\DateTimeService();

		$documentStorage = new \Searchperience\Api\Client\System\Storage\RestDocumentBackend();
		$documentStorage->injectRestClient($guzzle);
		$documentStorage->injectDateTimeService($dateTimeService);
		$documentStorage->setBaseUrl($baseUrl);
		$documentStorage->setUsername($username);
		$documentStorage->setPassword($password);

		$documentRepository = new \Searchperience\Api\Client\Domain\DocumentRepository();
		$documentRepository->injectStorageBackend($documentStorage);
		$documentRepository->injectFilterCollectionFactory(new \Searchperience\Api\Client\Domain\Filters\FilterCollectionFactory());
		$documentRepository->injectValidator(\Symfony\Component\Validator\Validation::createValidatorBuilder()->enableAnnotationMapping()->getValidator());

		return $documentRepository;
	}
}
