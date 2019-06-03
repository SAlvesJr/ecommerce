<?php 

use Hcode\PageAdmin;

use Hcode\Model\User;

use Hcode\Model\Product;

$app -> get("/admin/products", function(){
	User::verifyLogin();

	$search = (isset( $_GET["search"])) ? $_GET["search"] : "";	
	$page = (isset( $_GET["page"])) ? (int)$_GET["page"] : 1;

	
	if ($search != "") {
		$pagination = Product::getPageSearch($search, $page);
	} else {
		$pagination = Product::getPage($page);
	}

	$pages = [];

	for ($i=1; $i <= $pagination["page"]; $i++) { 
		array_push(
			$pages, [
				"href" => "/admin/products?". http_build_query([
					"page" => $i,
					"search" => $search
				]),
				"text" => $i
			]);
	}

	$page = new PageAdmin();

	$page -> setTpl("products", ["products" => $pagination["data"], "search" => $search, "pages" => $pages ] );
});

$app -> get("/admin/products/create", function(){
	User::verifyLogin();

	$page = new PageAdmin();

	$page -> setTpl("products-create");
});

$app -> post("/admin/products/create", function(){
	User::verifyLogin();
	$products = new Product();

	$products -> setData($_POST);

	$products -> save();
	
	if($_FILES["file"]["name"] !== "") $product->setPhoto($_FILES['file']);

	header("Location: /admin/products");
	exit;
});

$app -> get("/admin/products/:idproduct", function($idproduct){
	User::verifyLogin();

	$page = new PageAdmin();

	$product = new Product();

	$product -> get((int)$idproduct );

	$page -> setTpl("products-update", ["product" => $product -> getValues() ]);
});

$app -> post("/admin/products/:idproduct", function($idproduct){
	User::verifyLogin();

	$product = new Product();

	$product -> get((int)$idproduct );

	$product -> setData($product);

	if($_FILES["file"]["name"] !== "") $product->setPhoto($_FILES["file"]);

	$product ->  save();

	//$product -> setPhoto($_FILES["file"]);

	header("Location: /admin/products");
	exit;
});

$app -> get("/admin/products/:idproduct/delete", function($idproduct){
	User::verifyLogin();

	$product = new Product();

	$product -> get((int)$idproduct );

	$product -> delete();

	header("Location: /admin/products");
	exit;	
});

 ?>