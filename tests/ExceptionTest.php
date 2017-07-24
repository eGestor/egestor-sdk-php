<?php

class ExceptionTest extends PHPUnit_Framework_TestCase {

  /**
    * @expectedException Zipline\eGestor\Exception\InvalidRequestException
    */
  public function testInvalidRequestException() {
    throw new Zipline\eGestor\Exception\InvalidRequestException("Requisição inválida!");
  }


  /**
    * @expectedException Zipline\eGestor\Exception\InvalidTokenException
    */
  public function testInvalidTokenException() {
    throw new Zipline\eGestor\Exception\InvalidTokenException("Token informado é inválido!");
  }



}
