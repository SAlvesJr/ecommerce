<?php 

use Hcode\PageAdmin;

use Hcode\Page;

use Hcode\Model\User;

use Hcode\Model\Category;

$app -> get("/admin/categories", function(){

	User::verifyLogin();

	$categories = Category::listAll();

	$page = new PageAdmin();

	$page ->setTpl("categories", ["categories" => $categories ]);
});

$app -> get("/admin/categories/create", function(){

	User::verifyLogin();

	$page = new PageAdmin();

	$page ->setTpl("categories-create");
});

$app -> post("/admin/categories/create", function(){

	User::verifyLogin();

	$categories =  new Category();
	$categories -> setData($_POST);

	$categories -> save();

	header("Location: /admin/categories");
	exit;
});

$app -> get("/admin/categories/:idcategory/delete", function($idcategory){

	User::verifyLogin();

	$categories = new Category();

	$categories -> get( (int)$idcategory );

	$categories -> delete();

	header("Location: /admin/categories");
	exit;

});

$app -> get("/admin/categories/:idcategory", function($idcategory){

	User::verifyLogin();

	$categories = new Category();

	$categories -> get( (int)$idcategory );

	$page = new PageAdmin();

	$page ->setTpl("categories-update", ["category" => $categories-> getValues() ]);

});

$app -> post("/admin/categories/:idcategory", function($idcategory){

	User::verifyLogin();

	$categories = new Category();

	$categories -> get( (int)$idcategory );
	$categories -> setData($_POST);

	$categories -> save();

	header("Location: /admin/categories");
	exit;

});

$app -> get("/categories/:idrecovery", function($idrecovery){
	$category = new Category();

	$category->get((int)$idrecovery );

	$page = new Page();
	$page -> setTpl("category", ["category" => $category -> getValues(), "products" => [] ]);
});

 ?>