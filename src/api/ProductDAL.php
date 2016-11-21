<?php
namespace RelatedProducts;

class ProductDAL
{
  private $dbConnection;
  function __construct($db)
  {
    $this->dbConnection = $db;
  }

  public function listAllProducts()
  {
    $res   = $this->dbConnection->query("select productId, name from d_product");
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
}

?>
