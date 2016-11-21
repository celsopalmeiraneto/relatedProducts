<?php
namespace RelatedProducts;

class Product
{
  public $productId;
  public $name;
  function __construct($productId, $name)
  {
    $this->productId = $productId;
    $this->name      = $name;
  }
}
?>
