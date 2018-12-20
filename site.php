<?php 

use Hcode\Page;
use Hcode\Model\Product;
use Hcode\Model\Category;

$app->get('/', function() {

	$product = Product::listAll();

	$page = new Page();

	$page -> setTpl("index", ["product" => Product::checkList($product) ]);

});


$app -> get("/categories/:idcategory", function($idcategory){

	$category = new Category();

	$category->get((int)$idcategory );

	$page = new Page();
	$page -> setTpl("category", ["category" => $category -> getValues(), "products" => Product::checkList( $category -> getProducts() ) ]);
});

 ?>