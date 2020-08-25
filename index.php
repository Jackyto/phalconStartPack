<?php
require "vendor/autoload.php";

use Phalcon\Loader;
use Phalcon\Mvc\Micro;
use Phalcon\Di\FactoryDefault;
use Phalcon\Db\Adapter\Pdo\Mysql as PdoMysql;
use Phalcon\Http\Response;

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
