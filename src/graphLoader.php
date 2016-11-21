<?php

  $loader = require __DIR__.'/vendor/autoload.php';

  use GraphAware\Neo4j\Client\ClientBuilder;

  $mysqlConnection = new mysqli('127.0.0.1', 'root', 'root', 'bi_kamar');
  if ($mysqlConnection->connect_error){
    die("falha ao conectar ao banco");
  }else{
    echo "Conexão ao mysql bem sucedida.\n";
  }


  $neo4j = ClientBuilder::create()
    ->addConnection('default', 'http://neo4j:root@localhost:7474')
    ->addConnection('bolt'   , 'http://neo4j:root@localhost:7687')
    ->build();

  if(array_search("product", $argv)){
    //Apagando todos os produtos...
    echo "Apagando todos os produtos...\n";
    $neo4j->run("MATCH (p:Product) DELETE p");

    //Buscando todos os produtos...
    $response = $mysqlConnection->query('select productId, name from d_product');
    if ($response) {
      echo "Inserindo produtos...\n";
      $contador = 0;
      while ($row = $response->fetch_assoc()) {
        $contador++;
        if (($contador % 100) == 0) {
          echo "$contador / $response->num_rows\n";
        }

        $row['name'] = mb_convert_encoding($row['name'], 'ASCII');
        $neo4j->run("CREATE (p:Product) SET p += {info}",
          ['info' => ['productId' => $row['productId'], 'name' => $row['name']]]
        );
      }
      $response->free();
    }else{
      echo $mysqlConnection->error;
      die("\nquery de produtos mal sucedida\n");
    }
  }

  if(array_search("sale", $argv)){
    //Apagando todas as vendas...
    echo "Apagando todos os produtos...\n";
    $neo4j->run("MATCH (p:Sale) DELETE p");

    //Buscando todas as vendas...
    $response = $mysqlConnection->query('select externalid from f_sale group by externalid');
    if ($response) {
      echo "Inserindo produtos...\n";
      $contador = 0;
      $sales = [];
      while ($row = $response->fetch_assoc()) {
        $contador++;

        $sales[] = ['saleId' => $row['externalid']];

        if (($contador % 1000) == 0 ) {
          echo "$contador / $response->num_rows\n";
          $neo4j->run("UNWIND {props} AS properties CREATE (n:Sale) SET n = properties",
            ["props" => $sales]
          );
          $sales = [];
        }
      }
      $neo4j->run("UNWIND {props} AS properties CREATE (n:Sale) SET n = properties",
        ["props" => $sales]
      );
      $response->free();
    }else{
      echo $mysqlConnection->error;
      die("\nquery de produtos mal sucedida\n");
    }
  }

  if(array_search("relationships", $argv)){
    $response = $mysqlConnection->query('select externalid, d_product_productid from f_sale');
    $contador = 0;
    while ($row = $response->fetch_assoc()){
      $contador++;
      $res_neo = $neo4j->run("MATCH (s:Sale), (p:Product) where s.saleId = '$row[externalid]' AND p.productId= '$row[d_product_productid]'
                  CREATE (s)-[r:CONTAINS]->(p)
                  RETURN r");
      if ( ($contador % 1000) == 0) {
        echo "$contador / $response->num_rows \n";
      }
    }
  }


  $mysqlConnection->close();
  echo "Conexões fechadas!\n"

?>
