<?php
namespace Zipline\eGestor\Exception;

/**
 *
 */
class InvalidRequestException extends \Exception {

  public $response;

  public function __construct($message, $code = 0, Exception $previous = null, $response = null) {
    parent::__construct($message, $code, $previous);

    $this->response = $response;
  }

  public function getResponse() {
    return json_decode($this->response, true);
  }

  public function getRawResponse() {
    return $this->response;
  }
}
