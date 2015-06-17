<?php

namespace Searchperience\Tests\Api\Client\System\Storage;

/**
 * @author Michael Klapper <michael.klapper@aoemedia.de>
 * @date 17.11.12
 * @time 09:37
 */
class AbstractRestBackendTestCase extends \Searchperience\Tests\BaseTestCase {

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
	}

	/**
	 * @test
	 */
	public function verifyAbstractRestBackendSetter() {
		$baseUrl = 'http://www.searchperience.com/';
		$username = 'user';
		$password = 'pass';
		/** @var $restBackend \Searchperience\Api\Client\System\Storage\AbstractRestBackend */
		$restBackend = $this->getAccessibleMockForAbstractClass('\Searchperience\Api\Client\System\Storage\AbstractRestBackend');
		$restBackend->setBaseUrl($baseUrl);
		$restBackend->setPassword($password);
		$restBackend->setUsername($username);

		$this->assertEquals($baseUrl, $restBackend->_get('baseUrl'));
		$this->assertEquals($username, $restBackend->_get('username'));
		$this->assertEquals($password, $restBackend->_get('password'));
	}

	/**
	 * Provides some invalid value for authentication
	 *
	 * @return array
	 */
	public function verifySetBaseUrlThrowsExceptionOnInvalidArgumentDataProvider() {
		return array(
			array(NULL),
			array(''),
			array(array()),
			array(new \stdClass()),
		);
	}

	/**
	 * @test
	 * @param mixed $invalidValue
	 * @expectedException \Searchperience\Common\Exception\InvalidArgumentException
	 * @dataProvider verifySetBaseUrlThrowsExceptionOnInvalidArgumentDataProvider
	 */
	public function verifySetBaseUrlThrowsExceptionOnInvalidArgument($invalidValue) {
		/** @var $restBackend \Searchperience\Api\Client\System\Storage\AbstractRestBackend */
		$restBackend = $this->getAccessibleMockForAbstractClass('\Searchperience\Api\Client\System\Storage\AbstractRestBackend');
		$restBackend->setBaseUrl($invalidValue);
	}

	/**
	 * @test
	 * @param mixed $invalidValue
	 * @expectedException \Searchperience\Common\Exception\InvalidArgumentException
	 * @dataProvider verifySetBaseUrlThrowsExceptionOnInvalidArgumentDataProvider
	 */
	public function verifySetUsernameThrowsExceptionOnInvalidArgument($invalidValue) {
		/** @var $restBackend \Searchperience\Api\Client\System\Storage\AbstractRestBackend */
		$restBackend = $this->getAccessibleMockForAbstractClass('\Searchperience\Api\Client\System\Storage\AbstractRestBackend');
		$restBackend->setUsername($invalidValue);
	}

	/**
	 * @test
	 * @param mixed $invalidValue
	 * @expectedException \Searchperience\Common\Exception\InvalidArgumentException
	 * @dataProvider verifySetBaseUrlThrowsExceptionOnInvalidArgumentDataProvider
	 */
	public function verifySetPasswordThrowsExceptionOnInvalidArgument($invalidValue) {
		/** @var $restBackend \Searchperience\Api\Client\System\Storage\AbstractRestBackend */
		$restBackend = $this->getAccessibleMockForAbstractClass('\Searchperience\Api\Client\System\Storage\AbstractRestBackend');
		$restBackend->setPassword($invalidValue);
	}

	/**
	 * Provides Status codes and their expected Exception class names
	 *
	 * @return array
	 */
	public function verifyTransformStatusCodeToClientErrorResponseExceptionDataProvider() {
		return array(
			array('401', '\Searchperience\Common\Http\Exception\UnauthorizedException'),
			array('403', '\Searchperience\Common\Http\Exception\ForbiddenException'),
			array('404', '\Searchperience\Common\Http\Exception\DocumentNotFoundException'),
			array('405', '\Searchperience\Common\Http\Exception\MethodNotAllowedException'),
			array('413', '\Searchperience\Common\Http\Exception\RequestEntityTooLargeException'),
			array('414', '\Searchperience\Common\Http\Exception\ClientErrorResponseException'),
			array('499', '\Searchperience\Common\Http\Exception\ClientErrorResponseException'),
		);
	}

	/**
	 * @test
	 * @param string $statusCode
	 * @param string $exceptionClassName
	 * @dataProvider verifyTransformStatusCodeToClientErrorResponseExceptionDataProvider
	 */
	public function verifyTransformStatusCodeToClientErrorResponseException($statusCode, $exceptionClassName) {
		$this->setExpectedException($exceptionClassName);
		$response = $this->getMock('\Guzzle\Http\Message\Response', array('getStatusCode'), array(), '', FALSE);
		$response->expects($this->once())
			->method('getStatusCode')
			->will($this->returnValue($statusCode));
		$clientException = $this->getMock('\Guzzle\Http\Exception\ClientErrorResponseException', array('getResponse'));
		$clientException->expects($this->once())
			->method('getResponse')
			->will($this->returnValue($response));
		$restBackend = $this->getAccessibleMockForAbstractClass('\Searchperience\Api\Client\System\Storage\AbstractRestBackend');

		$restBackend->_call('transformStatusCodeToClientErrorResponseException', $clientException);
	}

	/**
	 * Provides Status codes and their expected Exception class names
	 *
	 * @return array
	 */
	public function verifyTransformStatusCodeToServerErrorResponseExceptionDataProvider() {
		return array(
			array('500', '\Searchperience\Common\Http\Exception\InternalServerErrorException'),
			array('501', '\Searchperience\Common\Http\Exception\ServerErrorResponseException'),
			array('555', '\Searchperience\Common\Http\Exception\ServerErrorResponseException'),
		);
	}

	/**
	 * @test
	 * @param string $statusCode
	 * @param string $exceptionClassName
	 * @dataProvider verifyTransformStatusCodeToServerErrorResponseExceptionDataProvider
	 */
	public function verifyTransformStatusCodeToServerErrorResponseException($statusCode, $exceptionClassName) {
		$this->setExpectedException($exceptionClassName);
		$response = $this->getMock('\Guzzle\Http\Message\Response', array('getStatusCode'), array(), '', FALSE);
		$response->expects($this->once())
			->method('getStatusCode')
			->will($this->returnValue($statusCode));
		$serverException = $this->getMock('\Guzzle\Http\Exception\ServerErrorResponseException', array('getResponse'));
		$serverException->expects($this->once())
			->method('getResponse')
			->will($this->returnValue($response));
		$restBackend = $this->getAccessibleMockForAbstractClass('\Searchperience\Api\Client\System\Storage\AbstractRestBackend');

		$restBackend->_call('transformStatusCodeToServerErrorResponseException', $serverException);
	}
}
