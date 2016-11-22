<?php
namespace RelatedProducts;

class ProductDAL
{
  private $mysqlConnection;
  private $neo4jConnection;

  function __construct($mysql, $neo)
  {
    $this->mysqlConnection = $mysql;
    $this->neo4jConnection = $neo;
  }

  public function listAllProducts()
  {
    $res   = $this->mysqlConnection->query("select productId, name from d_product");
    $table = [];
    if($res){
      while ($row = $res->fetch_assoc()) {
        $row['name'] = mb_convert_encoding($row['name'], 'ASCII');
        $product = new Product($row['productId'], $row['name']);
        $table[] = $product;
      }
    }
    return $table;
  }

  public function searchRelatedProductsMySQL($mainProduct){
    $res   = $this->mysqlConnection->query("select s2.d_product_productid as productId, count(*) as ocorrencias
              from f_sale s1
              inner join f_sale s2 on s2.externalid = s1.externalid
              where s1.d_product_productid =  '$mainProduct'
                and s2.d_product_productid <> '$mainProduct'
              group by s2.d_product_productid
              having ocorrencias > 5
              order by ocorrencias desc");
    $table = [];
    if($res){
      while ($row = $res->fetch_assoc()) {
        $row['name'] = mb_convert_encoding($row['name'], 'ASCII');
        $product = new Product($row['productId'], '');
        $table[] = $product;
      }
    }
    return $table;

  }
  public function searchRelatedProductsNeo4j($mainProduct){
    $query = "match (sa:Sale)-[r:CONTAINS]->(prod:Product{productId:'$mainProduct'})
              match (sa)-[r2:CONTAINS]->(relacionados:Product)
              with relacionados, count(relacionados) as ocorrencias
              where ocorrencias > 5 and relacionados.productId <> '$mainProduct'
              return relacionados, ocorrencias order by ocorrencias desc";

    $res = $this->neo4jConnection->run($query);

    $buildTable = function($item){
      $internalArray = $item->values();
      return ['productId'   => $internalArray[0]->value("productId"),
              'name'        => $internalArray[0]->value("name"),
              'occurrences' => $internalArray[1]];
    };

    return ['query' => $query, 'res' => array_map($buildTable, $res->getRecords())];
  }



}

?>
