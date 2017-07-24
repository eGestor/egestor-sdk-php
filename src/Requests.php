<?php

namespace Zipline\eGestor;

use GuzzleHttp\Exception\ClientException;

use Zipline\eGestor\Auth;
use Zipline\eGestor\Config;


/**
 * Responsável por realizar os acessos na API
 */
class Requests {

  public $http, $response;

  private $_API_URI;

  public function __construct(Auth $auth, $httpClient) {
    $this->auth = $auth;
    $this->http = $httpClient;

    $this->_API_URI = Config::getApiUri();
  }

  public function __call($name, $arguments) {
    if (!empty($this->response)) {
      return call_user_func_array(
        array($this->response, $name), $arguments
      );
    }
  }

  private function requestAccessToken() {
    return $this->auth->requestAccessToken();
  }

  private function beforeRequest() {
    $accessToken = $this->auth->getAccessToken();

    if (empty($accessToken)) {
      $this->auth->requestAccessToken();
    }
  }

  private function request($type, $endpoint, $options = []) {
    $this->beforeRequest();

    $uri = $this->_API_URI . trim($endpoint, '/' );

    $options['headers']['Authorization'] = 'Bearer ' . $this->auth->getAccessToken();
    $options['headers']['Content-Type'] = 'application/json';

    try {
      return $this->http->request($type, $uri, $options);
    } catch (ClientException $e) {

      $resp = json_decode($e->getResponse()->getBody());

      if ($e->getCode() == 401 && $this->retry) {
        if (isset($resp->errObs) && $resp->errObs == 'access_denied') {
          $this->requestAccessToken();
          $this->retry = false;

          return $this->request($type, $endpoint, $options);
        }
      }

      if (isset($resp->errMsg)) {
        throw new \Exception($resp->errMsg, $e->getCode());
      }

      return $e->getResponse();
    }

  }

  public function get($endpoint, array $data = []) {
    $this->retry = true;

    $options = [
      'query' => $data
    ];

    if (empty($data)) {
      $options = [];
    }

    $this->response = $this->request('GET', $endpoint, $options);

    return json_decode($this->response->getBody(), true);
  }

  public function post($endpoint, array $data) {
    $this->retry = true;
    $options = [
      'json' => $data
    ];
    $this->response = $this->request('POST', $endpoint, $options);
    return json_decode($this->response->getBody(), true);
  }

  public function put($endpoint, array $data) {
    $this->retry = true;
    $options = [
      'json' => $data
    ];
    $this->response = $this->request('PUT', $endpoint, $options);
    return json_decode($this->response->getBody(), true);
  }

  public function delete($endpoint) {
    $this->retry = true;
    $this->response = $this->request('DELETE', $endpoint);

    return json_decode($this->response->getBody(), true);
  }

}
