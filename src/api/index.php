<?php
namespace RelatedProducts;

require '../vendor/autoload.php';
require 'Product.php';
require 'ProductDAL.php';

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface      as Response;
use GraphAware\Neo4j\Client\ClientBuilder;


$config['displayErrorDetails'] = true;


$app = new \Slim\App(["setting" => $config]);

$container = $app->getContainer();

$container['mysql'] = function($c){
  $connString = $c['settings']['mysql'];
  $mysql = new \mysqli('127.0.0.1', 'root', 'root', 'bi_kamar');
  if ($mysql->connect_error) {
    return false;
  }else{
    return $mysql;
  }
};

$container['neo4j'] = function($c){
  $neo4j = ClientBuilder::create()
    ->addConnection('default', 'http://neo4j:root@localhost:7474')
    ->addConnection('bolt'   , 'http://neo4j:root@localhost:7687')
    ->build();
  return $neo4j;
};


$app->get('/this', function(Request $request, Response $response){
  $newResponse = $response->withJson(var_dump($this));
  return $newResponse;
});

$app->get('/Product', function( Request $request, Response $response){
  $pdal = new ProductDAL($this->mysql, $this->neo4j);
  $newResponse = $response->withJson($pdal->listAllProducts());
  return $newResponse;
});

$app->get('/SearchRelated/{productId}/{technology}', function(Request $request, Response $response, $args){
  $pdal = new ProductDAL($this->mysql, $this->neo4j);

  if($args['technology']=='neo')
    $newResponse = $response->withJson($pdal->searchRelatedProductsNeo4j($args['productId']));
  else
    $newResponse = $response->withJson($pdal->searchRelatedProductsMySQL($args['productId']));

  return $newResponse;
});


$app->run();
?>
