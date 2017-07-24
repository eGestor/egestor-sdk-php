<?php

class AuthTest extends PHPUnit_Framework_TestCase {

  public $client = [
    'id'     => '154d6edd8bd3010c4860d7a9c9573fbefe524def',
    'secret' => '4955793a84726f1613d2b9415e282bb13db7cbd4',
    'uri'    => 'redirect_uri'
  ];

  public $tokens = [
    'access_token'   => '2ff2dfe36322448c6953616740a910be57bbd4ca',
    'refresh_token'  => '4c82f23d91a75961f4d08134fc5ad0dfe6a4c36a',
    'personal_token' => 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJhcHAiOiIzNDhlNTM4MzQ2M2Y3MzZjMGExZTJhNTFmNjYwZjA5NCIsInN1YmRvbWluaW8iOiJleGVtcGxvIiwiY2xpZW50IjoiMTU0ZDZlZGQ4YmQzMDEwYzQ4NjBkN2E5Yzk1NzNmYmVmZTUyNGRlZiJ9.JJNs0bFtGOtwyJy_r-eefsvkd387M_x7zpucE1m4WIw'
  ];

  public function testSetGetRefreshToken() {
    $auth = new Zipline\eGestor\Auth(
      $this->client['id'],
      $this->client['secret']
    );

    $auth->setRefreshToken($this->tokens['refresh_token']);

    $refreshToken = $auth->getRefreshToken();

    $this->assertEquals($refreshToken, $this->tokens['refresh_token']);

  }


  public function testGetters() {
    $http = Mockery::mock('GuzzleHttp\Client');
    $response = Mockery::mock('Response');

    $body = [
      'access_token'   => '2ff2dfe36322448c6953616740a910be57bbd4ca',
      'refresh_token'  => '4c82f23d91a75961f4d08134fc5ad0dfe6a4c36a',
      'personal_token' => 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJhcHAiOiIzNDhlNTM4MzQ2M2Y3MzZjMGExZTJhNTFmNjYwZjA5NCIsInN1YmRvbWluaW8iOiJleGVtcGxvIiwiY2xpZW50IjoiMTU0ZDZlZGQ4YmQzMDEwYzQ4NjBkN2E5Yzk1NzNmYmVmZTUyNGRlZiJ9.JJNs0bFtGOtwyJy_r-eefsvkd387M_x7zpucE1m4WIw'
    ];

    $response->shouldReceive('getBody')->andReturn(json_encode($body));

    $http->shouldReceive('request')->andReturn($response);

    $auth = new Zipline\eGestor\Auth(
      $this->client['id'],
      $this->client['secret'],
      $this->client['uri'],
      $http
    );

    $code = 'e6fb06210fafc02fd7479ddbed2d042cc3a5155e';

    $tokens = $auth->byCode($code);

    $this->assertEquals($auth->getPersonalToken(), $body['personal_token']);
    $this->assertEquals($auth->getAccessToken(),   $body['access_token']);

    $uri = 'oauth/authorize?response_type=code&client_id=154d6edd8bd3010c4860d7a9c9573fbefe524def&redirect_uri=redirect_uri';

    $this->assertStringEndsWith($uri, $auth->getAuthUri());

  }

  public function testGrantByCode() {
    $http = Mockery::mock('GuzzleHttp\Client');
    $response = Mockery::mock('Response');

    $body = [
      'access_token'   => '2ff2dfe36322448c6953616740a910be57bbd4ca',
      'refresh_token'  => '4c82f23d91a75961f4d08134fc5ad0dfe6a4c36a',
      'personal_token' => 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJhcHAiOiIzNDhlNTM4MzQ2M2Y3MzZjMGExZTJhNTFmNjYwZjA5NCIsInN1YmRvbWluaW8iOiJleGVtcGxvIiwiY2xpZW50IjoiMTU0ZDZlZGQ4YmQzMDEwYzQ4NjBkN2E5Yzk1NzNmYmVmZTUyNGRlZiJ9.JJNs0bFtGOtwyJy_r-eefsvkd387M_x7zpucE1m4WIw'
    ];

    $response->shouldReceive('getBody')->andReturn(json_encode($body));

    $http->shouldReceive('request')->andReturn($response);

    $auth = new Zipline\eGestor\Auth(
      $this->client['id'],
      $this->client['secret'],
      $this->client['uri'],
      $http
    );

    $code = 'e6fb06210fafc02fd7479ddbed2d042cc3a5155e';

    $tokens = $auth->byCode($code);

    $this->assertEquals($tokens, $body);
  }

  public function testGrantByPersonal() {
    $http = Mockery::mock('GuzzleHttp\Client');
    $response = Mockery::mock('Response');

    $body = [
      'access_token'   => '2ff2dfe36322448c6953616740a910be57bbd4ca',
      'refresh_token'  => '4c82f23d91a75961f4d08134fc5ad0dfe6a4c36a'
    ];

    $response->shouldReceive('getBody')->andReturn(json_encode($body));

    $http->shouldReceive('request')->andReturn($response);

    $auth = new Zipline\eGestor\Auth(
      $this->client['id'],
      $this->client['secret']
    );

    $auth->http = $http;

    $token = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJhcHAiOiIzNDhlNTM4MzQ2M2Y3MzZjMGExZTJhNTFmNjYwZjA5NCIsInN1YmRvbWluaW8iOiJleGVtcGxvIiwiY2xpZW50IjoiMTU0ZDZlZGQ4YmQzMDEwYzQ4NjBkN2E5Yzk1NzNmYmVmZTUyNGRlZiJ9.JJNs0bFtGOtwyJy_r-eefsvkd387M_x7zpucE1m4WIw';

    $tokens = $auth->byToken($token);

    $this->assertEquals($tokens, $body);
  }

  public function testRefreshToken() {
    $http = Mockery::mock('GuzzleHttp\Client');
    $response = Mockery::mock('Response');

    $body = [
      'access_token'   => '2ff2dfe36322448c6953616740a910be57bbd4ca',
      'refresh_token'  => '4c82f23d91a75961f4d08134fc5ad0dfe6a4c36a'
    ];

    $response->shouldReceive('getBody')->andReturn(json_encode($body));

    $http->shouldReceive('request')->andReturn($response);

    $auth = new Zipline\eGestor\Auth(
      $this->client['id'],
      $this->client['secret']
    );

    $auth->http = $http;

    $token = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJhcHAiOiIzNDhlNTM4MzQ2M2Y3MzZjMGExZTJhNTFmNjYwZjA5NCIsInN1YmRvbWluaW8iOiJleGVtcGxvIiwiY2xpZW50IjoiMTU0ZDZlZGQ4YmQzMDEwYzQ4NjBkN2E5Yzk1NzNmYmVmZTUyNGRlZiJ9.JJNs0bFtGOtwyJy_r-eefsvkd387M_x7zpucE1m4WIw';

    $auth->byToken($token);

    $tokens = $auth->requestAccessToken();

    $this->assertEquals($tokens, $body);
  }
}
