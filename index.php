<?php
require "vendor/autoload.php";

use Phalcon\Loader;
use Phalcon\Mvc\Micro;
use Phalcon\Di\FactoryDefault;
use Phalcon\Db\Adapter\Pdo\Mysql as PdoMysql;
use Phalcon\Http\Response;
use Dmkit\Phalcon\Auth\Middleware\Micro as AuthMicro;

// Use Loader() to autoload our model
$loader = new Loader();
$loader->registerDirs(
  [
    __DIR__ . '/models/'
  ]
)->register();

$di = new FactoryDefault();

// Set up the database service
$di->set(
  'db',
  function () {
    return new PdoMysql(
      [
        'host'     => '',
        'username' => '',
        'password' => '',
        'dbname'   => '',
 	"options" => array( // this is your important part
 	  PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'
 	)
      ]
    );
  }
);

$app = new Micro($di);

// SETUP THE CONFIG
$authConfig = [
  'secretKey' => '923753F2317FC1EE5B52DF23951B1',
  'payload' => [
    'exp' => 1440,
    'iss' => 'phalcon-jwt-auth'
  ],
  'ignoreUri' => [
    '/',
    'regex:/application/',
    'regex:/users/:POST,PUT',
    '/auth/user:POST,PUT',
    '/auth/application',
    '/login',
    '/version',
  ]
];

$auth = new AuthMicro($app, $authConfig);

$auth->onUnauthorized(function($authMicro, $app) {

  $response = $app["response"];
  $response->setStatusCode(401, 'Unauthorized tavu');
  $response->setContentType("application/json");

  // to get the error messages
  $response->setContent(json_encode([$authMicro->getMessages()[0]]));
  $response->send();

  // return false to stop the execution
  return false;
});

$app->get(
  '/version',
  function () use ($app) {
    $version = file_get_contents("version.txt");
    echo $version;
  }
);

function returnError($app, $statusCode, $statusMessage, $errorMessage) {
  $app->response->setStatusCode($statusCode, $statusMessage)->sendHeaders();
  echo $errorMessage;
}

$app->notFound(function () use ($app) {
  $app->response->setStatusCode(404, "Not Found")->sendHeaders();
  echo 'Page not found!';
});

$app->handle();
