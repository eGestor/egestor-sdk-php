<?php

namespace Zipline\eGestor;

use GuzzleHttp\Client as GuzzleClient;

use Zipline\eGestor\Auth;
use Zipline\eGestor\Requests;

/**
 * Core do sdk do egestor
 */
class API {

  private $config = [
    'clientId'     => '',
    'clientSecret' => '',
    'redirectUri'  => '',
    'authToken'    => '',
    'accessToken'  => '',
    'refreshToken' => ''
  ];

  public $auth, $requests;

  public function __construct(array $config = []) {
    $this->config = array_merge($this->config, $config);

    $httpClient = new GuzzleClient();

    $this->auth = new Auth(
      $this->config['clientId'],
      $this->config['clientSecret'],
      $this->config['redirectUri'],
      $httpClient
    );

    $this->requests = new Requests($this->auth, $httpClient);

    if (!empty($this->config['accessToken'])) {
      $this->requests->setAccessToken($this->config['accessToken']);
    }

    if (!empty($this->config['refreshToken'])) {
      $this->auth->setRefreshToken($this->config['refreshToken']);
    }

    if (!empty($this->config['personalToken'])) {
      $this->urlEncodeToken();
      $this->auth->setPersonalToken($this->config['personalToken']);
    }
  }

  public function getAuthUri() {
    return $this->auth->getAuthUri();
  }

  public function authByCode($code) {
    return $this->auth->byCode($code);
  }

  public function authByPersonal($token) {
    return $this->auth->byToken($token);
  }

  public function authByRefresh($token) {
    $this->auth->setRefreshToken($token);

    return $this->auth->requestAccessToken();
  }

  public function getConnection() {
    return $this->requests;
  }

  public function getRemaining() {
    return $this->requests->getHeader('X-RateLimit-Remaining');
  }

  public function __call($name, $arguments) {
      return call_user_func_array(
        array($this->requests, $name), $arguments
      );
  }

  public function urlEncodeToken() {
    $decoded = urlencode(urldecode($this->config['personalToken']));
    if ($decoded != $this->config['personalToken']) {
      $this->config['personalToken'] = urlencode($this->config['personalToken']);
    }
  }
}
