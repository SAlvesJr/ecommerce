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
	}

	function get($idcategory){

		$sql = new Sql();

		$result = $sql ->select("SELECT * FROM tb_categories WHERE idcategory = :idcategory", [":idcategory" => $idcategory ]);

		$this -> setData($result[0]);
	}

	function delete(){

		$sql = new Sql();

		$sql ->query("DELETE FROM tb_categories WHERE idcategory = :idcategory", [":idcategory"  => $this-> getidcategory() ]);
	}


}


 ?>