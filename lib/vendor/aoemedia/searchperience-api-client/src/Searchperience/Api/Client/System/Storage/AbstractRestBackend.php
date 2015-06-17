<?php

namespace Searchperience\Api\Client\System\Storage;

/**
 * User: Michael Klapper
 * Date: 16.11.12
 * Time: 21:19
 */
abstract class AbstractRestBackend {

	/**
	 * @var string
	 */
	protected $username;

	/**
	 * @var
	 */
	protected $password;

	/**
	 * Default is set to "http://api.searchperience.me/"
	 *
	 * @string
	 */
	protected $baseUrl = 'http://api.searchperience.me/';

	/**
	 * Set the username to access the api.
	 *
	 * @param string $username
	 * @throws \Searchperience\Common\Exception\InvalidArgumentException
	 * @return void
	 */
	public function setUsername($username) {
		if (!is_string($username) || $username === '') {
			throw new \Searchperience\Common\Exception\InvalidArgumentException('username cannot be empty string.');
		}
		$this->username = $username;
	}

	/**
	 * Set the password to access the api.
	 *
	 * @param string $password
	 * @throws \Searchperience\Common\Exception\InvalidArgumentException
	 * @return void
	 */
	public function setPassword($password) {
		if (!is_string($password) || $password === '') {
			throw new \Searchperience\Common\Exception\InvalidArgumentException('password cannot be empty string.');
		}
		$this->password = $password;
	}

	/**
	 * Set the api base url including the customer path key.
	 *
	 * @param string $baseUrl Example: http://api.searchperience.com/bosch/
	 * @throws \Searchperience\Common\Exception\InvalidArgumentException
	 * @return void
	 */
	public function setBaseUrl($baseUrl) {
		if (!is_string($baseUrl) || $baseUrl === '') {
			throw new \Searchperience\Common\Exception\InvalidArgumentException('baseUrl cannot be empty string.');
		}
		$this->baseUrl = $baseUrl;
	}

	/**
	 * @param \Guzzle\Http\Exception\ClientErrorResponseException $exception
	 *
	 * @throws \Searchperience\Common\Http\Exception\InternalServerErrorException
	 * @throws \Searchperience\Common\Http\Exception\ForbiddenException
	 * @throws \Searchperience\Common\Http\Exception\ClientErrorResponseException
	 * @throws \Searchperience\Common\Http\Exception\DocumentNotFoundException
	 * @throws \Searchperience\Common\Http\Exception\UnauthorizedException
	 * @throws \Searchperience\Common\Http\Exception\MethodNotAllowedException
	 * @throws \Searchperience\Common\Http\Exception\RequestEntityTooLargeException
	 * @return void
	 */
	protected function transformStatusCodeToClientErrorResponseException(\Guzzle\Http\Exception\ClientErrorResponseException $exception) {

		switch ($exception->getResponse()->getStatusCode()) {
			case 401:

				throw new \Searchperience\Common\Http\Exception\UnauthorizedException($exception->getMessage(), 1353574907, $exception);
				break;

			case 403:
				throw new \Searchperience\Common\Http\Exception\ForbiddenException($exception->getMessage(), 1353574915, $exception);
				break;

			case 404:
				throw new \Searchperience\Common\Http\Exception\DocumentNotFoundException($exception->getMessage(), 1353574919, $exception);
				break;

			case 405:
				throw new \Searchperience\Common\Http\Exception\MethodNotAllowedException($exception->getMessage(), 1353574923, $exception);
				break;

			case 413:
				throw new \Searchperience\Common\Http\Exception\RequestEntityTooLargeException($exception->getMessage(), 1353574956, $exception);
				break;

			default:
				throw new \Searchperience\Common\Http\Exception\ClientErrorResponseException($exception->getMessage(), 1353574962, $exception);
		}
	}

	/**
	 * @param \Guzzle\Http\Exception\ServerErrorResponseException $exception
	 *
	 * @throws \Searchperience\Common\Http\Exception\ServerErrorResponseException
	 * @throws \Searchperience\Common\Http\Exception\InternalServerErrorException
	 * @return void
	 */
	protected function transformStatusCodeToServerErrorResponseException(\Guzzle\Http\Exception\ServerErrorResponseException $exception) {

		switch ($exception->getResponse()->getStatusCode()) {
			case 500:
				throw new \Searchperience\Common\Http\Exception\InternalServerErrorException($exception->getMessage(), 1353574974, $exception);
				break;

			default:
				throw new \Searchperience\Common\Http\Exception\ServerErrorResponseException($exception->getMessage(), 1353574979, $exception);
		}
	}
}
