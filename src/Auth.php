<?php

namespace Zipline\eGestor;

use GuzzleHttp\Exception\ClientException;

use Zipline\eGestor\Config;


/**
 * Responsável por realizar as requisições de autenticação com a API;
 */
class Auth {

  private $_URI_ACCESS_TOKEN = 'access_token';

  private $_URI_AUTHORIZE = 'authorize';

  private $_API_URI;

  private $accessToken, $refreshToken, $personalToken;

  private $clientApp;

  public $http;


  public function __construct($clientId, $clientSecret, $redirectUri = '', $httpClient = null) {
    $this->clientApp = [
      'id'     => $clientId,
      'secret' => $clientSecret,
      'redirectUri' => $redirectUri
    ];

    if ($this->isJWToken($clientId)) {
      throw new Exception\InvalidTokenException('JWT informado no `clientId`. Token JWT deve ser o `personalToken`!', 400);
    }

    $this->_URI_API = Config::getAuthUri();
    $this->_URI_AUTHORIZE    = $this->_URI_API . $this->_URI_AUTHORIZE;
    $this->_URI_ACCESS_TOKEN = $this->_URI_API . $this->_URI_ACCESS_TOKEN;

    if ($httpClient) {
      $this->http = $httpClient;
    } else {
      $this->http = new \GuzzleHttp\Client;
    }
  }

  public function setRefreshToken($token) {
    $this->refreshToken = $token;
  }

  public function getAccessToken() {
    return $this->accessToken;
  }

  public function setAccessToken($accessToken) {
    $this->accessToken = $accessToken;
  }

  public function getRefreshToken() {
    return $this->refreshToken;
  }

  public function setPersonalToken($personalToken) {
    if ($this->isJWToken($personalToken)) {
      throw new Exception\InvalidTokenException('O `personalToken` não é um JWT válido!', 400);
    }
    $this->personalToken = $personalToken;
  }

  public function getPersonalToken() {
    return $this->personalToken;
  }

  public function getAuthUri() {
    if (empty($this->clientApp['redirectUri'])) {
      return '';
    }

    $authPath    = $this->_URI_AUTHORIZE . '?response_type=code&client_id={{clientId}}&redirect_uri={{redirectUri}}';

    return str_replace(
      [ '{{clientId}}',      '{{redirectUri}}'],
      [ $this->clientApp['id'], $this->clientApp['redirectUri']],
      $authPath
    );
  }

  /**
  * Solicita para API os tokens de acesso utilizando a autenticação via código
  *
  * @param  string $code Código enviado pelo sistema depois que usuário libera o acesso para a aplicação
  * @return array        tokens de acesso
  */
  public function byCode($code) {
    try {
      $response = $this->http->request('POST', $this->_URI_ACCESS_TOKEN , [
        'json' => [
          'grant_type' => 'authorization_code',
          'client_id'  => $this->clientApp['id'],
          'client_secret' => $this->clientApp['secret'],
          'redirect_uri'  => $this->clientApp['redirectUri'],
          'code' => $code
        ]
      ]);
      $body = json_decode($response->getBody(), true);

      $this->accessToken   = $body['access_token'];
      $this->refreshToken  = $body['refresh_token'];
      $this->personalToken = $body['personal_token'];

      return $body;
    } catch (ClientException $e) {
      $respBody = $e->getResponse()->getBody();
      $bodyObj = json_decode($respBody);

      if (empty($body)) {
        throw new \Exception($e->getMessage(), $e->getCode());
      }

      throw new Exception\InvalidRequestException(
        $body->errMsg,
        $body->errCode,
        $e,
        $respBody
      );
    }
  }

  /**
  * Solicita para API os tokens de acesso utilizando o 'personal_token'
  *
  * @param  string $token Token JWT informado pelo usuário
  * @return array        tokens de acesso
  */
  public function byToken($token) {
    $url = $this->_URI_ACCESS_TOKEN;
    $this->personalToken = $token;

    try {
      $response = $this->http->request('POST', $url , [
        'json' => [
          'grant_type' => 'personal',
          'personal_token' => $token
        ]
      ]);

      $body = json_decode($response->getBody(), true);

      $this->accessToken  = $body['access_token'];
      $this->refreshToken = $body['refresh_token'];

      return $body;
    } catch (ClientException $e) {
      $body = json_decode($e->getResponse()->getBody());

      if (empty($body)) {
        throw new \Exception($e->getMessage(), $e->getCode());
      }

      if ($e->getCode() == 400 && isset($body->errMsg)) {
        throw new Exception\InvalidTokenException($body->errMsg, 400);
      }

      if (isset($body->errMsg)) {
        throw new \Exception($body->errMsg, $e->getCode());
      }

      return $body;
    }
  }


  public function requestAccessToken() {
    if (!empty($this->refreshToken) && !empty($this->clientApp['id'])) {
      return $this->refreshToken();
    } else if (!empty($this->personalToken)) {
      return $this->byToken($this->personalToken);
    }

    throw new \Exception("Não foi possível solicitar um novo 'access_token'. Informe um token de acesso antes de continuar. (API->authByCode ou API->authByPersonal) ", 1);

  }


  private function refreshToken() {
    try {
      $url = $this->_URI_ACCESS_TOKEN;

      $response = $this->http->request('POST', $url , [
        'json' => [
          'grant_type' => 'refresh_token',
          'client_id'  => $this->clientApp['id'],
          'client_secret' => $this->clientApp['secret'],
          'refresh_token' => $this->refreshToken
        ]
      ]);

      $body = json_decode($response->getBody(), true);
      $this->accessToken  = $body['access_token'];
      $this->refreshToken = $body['refresh_token'];

      return $body;
    } catch (ClientException $e) {
      $body = json_decode($e->getResponse()->getBody());

      if (empty($body)) {
        throw new \Exception($e->getMessage(), $e->getCode());
      }

      if ($e->getCode() == 400 && isset($body->errMsg)) {
        throw new Exception\InvalidTokenException($body->errMsg, 400);
      }
    }
  }

  /**
   * Verificação simplificada se token é JWT
   * @param  string $token
   * @return boolean
   */
  public function isJWToken($token) {
      $data = explode('.', $token);
      $dots = count(array_filter($data));
      if ($dots != 3) {
        return false;
      }

      foreach ($data as $value) {
        $recoded = base64_encode(base64_decode($value));
        if ($value !== $recoded ) {
          return false;
        }
      }

      return true;
  }

}
