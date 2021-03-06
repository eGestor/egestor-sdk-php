<?php

namespace Zipline\eGestor;

use GuzzleHttp\Exception\ClientException;


/**
 * Configurações gerais para acesso da API
 */
class Config {

  const VERSION = 'v1';
  public static $API_URI = 'https://v4.egestor.com.br/api/';

  public static function getApiUri() {
      return self::$API_URI . self::VERSION . '/';
  }

  public static function setApiUri($uri) {
    self::$API_URI = $uri;
  }

  public static function getAuthUri() {
      return self::$API_URI . 'oauth/';
  }

  public static function getApiVersion() {
      return self::VERSION;
  }


}
