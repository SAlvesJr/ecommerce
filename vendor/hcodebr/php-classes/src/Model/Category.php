<?php 
namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\Model;
use \Hcode\Mailer;

class Category extends Model {

	static function listAll(){

		$sql = new Sql();

		return $sql->select("SELECT * FROM tb_categories ORDER BY descategory");
	}

	function save(){
		$sql = new Sql();

		$result = $sql ->select("CALL sp_categories_save(:idcategory, :descategory)", array(
			":idcategory"  => $this-> getidcategory(),
			":descategory"  => $this->getdescategory()
		) );

		$this -> setData($result[0]);

		Category::updateFile();
	}

	function get($idcategory){

		$sql = new Sql();

		$result = $sql ->select("SELECT * FROM tb_categories WHERE idcategory = :idcategory", [":idcategory" => $idcategory ]);

		$this -> setData($result[0]);
	}

	function delete(){

		$sql = new Sql();

		$sql ->query("DELETE FROM tb_categories WHERE idcategory = :idcategory", [":idcategory"  => $this-> getidcategory() ]);

		Category::updateFile();
	}

	static function updateFile(){
		$categories = Category::listAll();

		$html = [];

		foreach ($categories as $key) {
			array_push($html, "<li> <a href = '/categories/".$key["idcategory"]."'>". $key["descategory"]. "</a> </li>");
		}

		file_put_contents($_SERVER["DOCUMENT_ROOT"] . DIRECTORY_SEPARATOR . "views" . DIRECTORY_SEPARATOR . "categories-menu.html", implode("", $html ) );
	}

	function getProducts($related = true){
		$sql = new Sql();

		if ($related === true) {
			return $sql -> select("
				SELECT * FROM tb_products WHERE idproduct IN(
					SELECT a.idproduct
					FROM tb_products a
					INNER JOIN tb_productscategories b ON a.idproduct = b.idproduct
					WHERE b.idcategory = :idcategory

			);", [":idcategory" => $this->getidcategory()] );
		}else{
			return $sql -> select("
				SELECT * FROM tb_products WHERE idproduct NOT IN(
					SELECT a.idproduct
					FROM tb_products a
					INNER JOIN tb_productscategories b ON a.idproduct = b.idproduct
					WHERE b.idcategory = :idcategory

			);", [":idcategory" => $this->getidcategory() ] );
		}
	}

	function addProduct(Product $product){
		$sql = new Sql();

		$sql -> query("INSERT INTO tb_productscategories (idcategory, idproduct) VALUES (:idcategory, :idproduct)", [":idcategory"  => $this->getidcategory(), ":idproduct" => $product->getidproduct() ] );
	}

	function removeProduct(Product $product){
		$sql = new Sql();

		$sql -> query("DELETE FROM tb_productscategories WHERE idcategory = :idcategory AND idproduct = :idproduct ", [":idcategory"  => $this->getidcategory(), ":idproduct" => $product->getidproduct() ] );
	}

}


 ?>