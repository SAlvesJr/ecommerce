<?php 
namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\Model;
use \Hcode\Mailer;

class User extends Model {

	const SESSION = "User";
	const SECRET = "senhaContem16Car";


	static function getFromSession(){
		$user =  new User();

		if (isset($_SESSION[User::SESSION]) && (int)$_SESSION[User::SESSION]["iduser"] > 0 ) {
			$user -> setData($_SESSION[User::SESSION]);
		}
		return $user;
	}

	static function checkLogin($inadmin = true){
		if (!isset($_SESSION[User::SESSION] ) ||
			!$_SESSION[User::SESSION] ||
			!(int)$_SESSION[User::SESSION]["iduser"] > 0 ){

			return false;
		}else{
			if ($inadmin === true && (bool)$_SESSION[User::SESSION]["inadmin"] === true ) {
				return true;
			}else if($inadmin === false){
				return true;
			}else{
				return false;
			}
		}
	}

	static function login($login, $password)	{
		$sql = new Sql();

		$result = $sql -> select("SELECT * FROM tb_users WHERE deslogin = :LOGIN", array(":LOGIN" => $login ) );

		if (count($result ) === 0 ) {
			throw new \Exception("Usuário ou Senha incoreto");
		}

		$data = $result[0];

		if (password_verify($password, $data["despassword"]) === true ) {
			$user = new User();

			$user -> setData( $data );
			
			$_SESSION[User::SESSION] = $user -> getValues();

			return $user;

		}else{
			throw new \Exception("Usuário inexistente ou senha inválida");
			
		}
	}

	static function verifyLogin ($inadmin = true){

		if (User::checkLogin($inadmin)	) {

			header("Location: /admin/login");
			exit;
		}
	}

	static function logout(){
		$_SESSION[User::SESSION] = NULL;
	}

	static function listAll(){

		$sql = new Sql();
		
		return $sql->select("SELECT * FROM tb_users a INNER JOIN tb_persons b USING(idperson) ORDER BY b.desperson");
	}

	function save(){

		$sql = new Sql();

		$result = $sql ->select("CALL sp_users_save(:desperson, :deslogin, :despassword, :desemail, :nrphone, :inadmin)", array(
			":desperson"  => $this-> getdesperson(),
			":deslogin"   => $this->getdeslogin(),
			":despassword"=> $this->getdespassword(),
			":desemail"   => $this->getdesemail(),
			":nrphone"    => $this->getnrphone(),
			":inadmin"    => $this->getinadmin()
		) );

		$this -> setData($result[0]);
	}

	function get($iduser){
		$sql = new Sql();

		$results = $sql->select("SELECT * FROM tb_users a INNER JOIN tb_persons b USING(idperson) WHERE a.iduser = :iduser;", array( ":iduser" => $iduser) );

		$data = $results[0];

		$this->setData($data);

	}

	function update(){
		$sql = new Sql();

		$result = $sql ->select("CALL sp_usersupdate_save(:iduser, :desperson, :deslogin, :despassword, :desemail, :nrphone, :inadmin)", array(
			":iduser"	  => $this->getiduser(),
			":desperson"  => $this-> getdesperson(),
			":deslogin"   => $this->getdeslogin(),
			":despassword"=> $this->getdespassword(),
			":desemail"   => $this->getdesemail(),
			":nrphone"    => $this->getnrphone(),
			":inadmin"    => $this->getinadmin()
		) );

		$this -> setData($result[0]);
	}


	function delete(){
		$sql = new Sql();
		$sql -> query("CALL sp_users_delete(:iduser)", array( ":iduser" => $this -> getiduser() ));
	}

	static function getForgot($email, $inadmin = true){
		$sql = new Sql();

		$result = $sql -> select("SELECT * FROM tb_persons a INNER JOIN tb_users b USING(idperson) WHERE a.desemail = :email;", array( ":email" => $email ) );

		if (count($result) === 0) {
			throw new \Exception("Não foi possível recuperar a senha.");
			
		}else{
			$data = $result[0];

			$chamadaFuncao =  $sql -> select("CALL sp_userspasswordsrecoveries_create(:iduser, :desip)", array(":iduser" => $data["iduser"], ":desip" => $_SERVER["REMOTE_ADDR"] ) );

			if (count($chamadaFuncao) === 0) {
				throw new \Exception("Não foi possível recuperar a senha.");
				
			}else{
				$dataRecovery = $chamadaFuncao[0];
				$iv = random_bytes(openssl_cipher_iv_length('aes-256-cbc'));
				$code = openssl_encrypt($dataRecovery['idrecovery'], 'aes-256-cbc', User::SECRET, 0, $iv);
				$result = base64_encode($iv.$code);
				if ($inadmin === true) {
				 $link = "http://www.hcodecommerce.com.br/admin/forgot/reset?code=$result";
				} else {
				 $link = "http://www.hcodecommerce.com.br/forgot/reset?code=$result";
				}
				$mailer = new Mailer($data["desemail"], $data["desperson"], "Redefinir senha ", "forgot", array("name" => $data["desperson"], "link" => $link) );

				$mailer -> send();

				return $link;

			}
		}
	}

	static function validForgotDecrypt($result){
		$result = base64_decode($result);

		$code = mb_substr($result, openssl_cipher_iv_length('aes-256-cbc'), null, '8bit');

		$iv = mb_substr($result, 0, openssl_cipher_iv_length('aes-256-cbc'), '8bit');

		$idrecovery = openssl_decrypt($code, 'aes-256-cbc', User::SECRET, 0, $iv);

		$sql = new Sql();

		$results = $sql->select("
			SELECT * FROM tb_userspasswordsrecoveries a
			INNER JOIN tb_users b USING(iduser)
			INNER JOIN tb_persons c USING(idperson) WHERE a.idrecovery = :idrecovery
			AND a.dtrecovery IS NULL AND
			DATE_ADD(a.dtregister, INTERVAL 1 HOUR) >= NOW();
			", array(
			":idrecovery"=>$idrecovery
		));
		if (count($results) === 0){
			throw new \Exception("Não foi possível recuperar a senha.");
		}
		else{
			return $results[0];
		}
	}

	function setForgotUsed($idrecovery){
		$sql = new Sql();

		$sql -> query("UPDATE tb_userspasswordsrecoveries SET dtrecovery = NOW() WHERE idrecovery = :idrecovery", array( ":idrecovery" => $idrecovery ) );
	}

	function setPassword($password ){

		$sql = new Sql();

		$sql -> query("UPDATE tb_users SET despassword = :password WHERE iduser = :iduser", array( ":password" => $password, ":iduser" => $this->getiduser() ) );
	}

}


 ?>