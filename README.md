eGestor SDK para PHP
==================================

SDK para simplificar o acesso da API do [eGestor](https://www.egestor.com.br/)

Instalação
------------
Utilize o [Composer](https://getcomposer.org) para instalar:

```bash
# Instalando o Composer
curl -sS https://getcomposer.org/installer | php

# Instalando o zipline/egestor-sdk como uma dependência do projeto
php composer.phar require zipline/egestor-sdk
```

Quando o SDK estiver instalado, será necessário carregar o `autoloader` gerado pelo Composer  (arquivo responsável por carregar todos os *namespaces* ).

Adicione no seu arquivo:
```php
require 'vendor/autoload.php';
```

Autenticando seu aplicativo
-----------------------

Existem duas formas principais para realizar a autenticação:

### Código de Acesso
O usuário será redirecionado para a url da sua aplicação com o parâmetro `code` que deverá ser utilizado para iniciar a autenticação com a API.

```php
$code = $_GET['code'];

$api = new Zipline\eGestor\API([
  'clientId' => 'id_da_aplicacao',
  'clientSecret' => 'segredo',
  'redirectUri' => 'uri_cadastrada'
]);

$tokens = $api->authByCode($code);
```

Como resposta a API irá enviar três tokens `access_token`, `refresh_token` e `personal_token`.

Para os próximos acessos acesso utilize o `personal_token` para solicitar o conjunto de `access_token` e `refresh_token`.

O SDK possui um método para retornar o link de solicitação de acesso para sua aplicação:
```php

$api = new Zipline\eGestor\API([
  'clientId' => 'id_da_aplicacao',
  'clientSecret' => 'segredo',
  'redirectUri' => 'uri_cadastrada'
]);

$url = $api->getAuthUri();

```

Sempre que o usuário acessar sua aplicação pelo link de redirecionamento, o sistema irá gerar um novo `code`;

### Personal Token
Depois de realizada a primeira autenticação é possível utilizar o `personalToken` recebido para solicitar um novo `access_token`.

O `personalToken` identifica sua aplicação com o usuário do eGestor.

```php

$api = new Zipline\eGestor\API(['personalToken' => PERSONAL_TOKEN]);

```


Acessando a API
----------------
Depois de feita a autenticação será possível acessar a API. Os métodos acesitos são `get`, `post`, `put` e `delete`.
Sempre que possível o retorno será em formato `Array`.


```php
require 'vendor/autoload.php';

$api = new Zipline\eGestor\API(['personalToken' => PERSONAL_TOKEN]);

$contatos = $api->get('contatos');
var_dump($contatos);
// Resposta:
// array(10) {
//   ["total"] => int(1)
//   ["per_page"]=> int(50)
//   ["current_page"]=>  int(1)
//   ["last_page"]=>  int(1)
//   ["next_page_url"]=>  NULL
//   ["prev_page_url"]=>  NULL
//   ["from"]=>   int(1)
//   ["to"]=>  int(50)
//   ["data"]=> array(1) {
//     [0]=> array(8) {
//       ["codigo"] =>  int(1)
//       ["nome"]   =>  string(5) "Paulo"
//       ["tipo"]   =>  array(1) {
//           [0]=>  string(7) "cliente"
//       }
//       ["emails"] =>  array(1) {
//         [0]=> string(17) "paulo@example.com"
//       }
//       ["fones"]  =>  array(0) {}
//       ["cidade"] =>  string(0) ""
//       ["uf"]     =>  string(0) ""
//       ["tags"]   =>  array(1) {
//         [0]=> string(11) "cliente-vip"
//       }
//     }
//   }
//   ["next_page"]=> NULL
// }

```



O objeto da API permite acessar dados da última requisição:

```php

$remaining = $api->getRemaining();//número que requests até atingir o limite

$body = $api->getBody();//corpo da resposta sem o parser

```

Exemplos:

```php
require 'vendor/autoload.php';

$api = new Zipline\eGestor\API(['personalToken' => PERSONAL_TOKEN]);

//Novo contato
$contato = $api->post("contatos", [
    'nome' => 'Zipline',
    'tipo' => ['fornecedor']
]);
$codContato = $contato['codigo'];

//Editando
$response = $api->put("contatos/$codContato", [
    'nome' => 'eGestor',
]);

//Removendo
$response = $api->delete("contatos/$codContato");

//Pesquisando
$response = $api->get("contatos", [
    'filtro' => 'eGestor',
]);
```

```php
require 'vendor/autoload.php';
try {
  $api = new Zipline\eGestor\API(['personalToken' => PERSONAL_TOKEN]);

  $contatos = $api->get("contatos");

  foreach($contato as $contatos['data']) {
    echo $contato['codigo'] . ' - ' . $contato['nome'] . "\n";
  }

} catch(Zipline\Exception\InvalidTokenException $e) {
  echo "Não foi possível autenticar! Motivo:" . $e->getMessage() . "\n";
} catch(Exception $e) {
  echo "Requisição não poder realizada! Motivo:" . $e->getMessage() . "\n";
}


```

```php
require 'vendor/autoload.php';
try {
  $api = new Zipline\eGestor\API([
  'app_id' => MEU_APP_ID,
  'app_secret' => MEU_APP_SECRET
  'personalToken' => PERSONAL_TOKEN)
  ]);

  $contatos = $api->get("contatos");

  foreach($contato as $contatos['data']) {
    echo $contato['codigo'] . ' - ' . $contato['nome'] . "\n";
  }

} catch(Zipline\Exception\InvalidTokenException $e) {
  echo "Não foi possível autenticar! Motivo:" . $e->getMessage() . "\n";
} catch(Exception $e) {
  echo "Requisição não poder realizada! Motivo:" . $e->getMessage() . "\n";
}


```

Exceptions
----------

* `Zipline\Exception\InvalidTokenException`: credenciais inválidas;
