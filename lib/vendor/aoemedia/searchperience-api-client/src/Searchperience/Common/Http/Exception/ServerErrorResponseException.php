<?php

namespace Searchperience\Common\Http\Exception;

/**
 * Exception when a server error is encountered (5xx codes)
 */
class ServerErrorResponseException extends \Searchperience\Common\Exception\RuntimeException implements \Searchperience\Common\Exception\SearchperienceException {
}
