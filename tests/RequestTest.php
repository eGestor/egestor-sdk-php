<?php

class RequestTest extends PHPUnit_Framework_TestCase {

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


  public function mockAuth() {
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

    $auth->byCode($code);

    return $auth;
  }

  public function testGet() {
    $http = Mockery::mock('GuzzleHttp\Client');
    $auth = $this->mockAuth();
    $response = Mockery::mock('Response');

    $body = '{"codigo":145,"codPlanoContas":11,"codFormaPgto":0,"numDoc":"","descricao":"Venda a prazo, c\u00f3digo 119, parcela 1\/1","valor":505,"dtVenc":"2017-06-05","dtPgto":"0000-00-00","dtCad":"2017-06-05 10:59:16","situacao":20,"codContato":7,"origem":"vendas","obs":"","tags":[]}';

    $response->shouldReceive('getBody')->andReturn($body);

    $http->shouldReceive('request')->andReturn($response);

    $request = new Zipline\eGestor\Requests(
      $auth,
      $http
    );

    $resposta = $request->get('contatos/1');

    $this->assertEquals(json_encode($resposta), $body);

  }


}
