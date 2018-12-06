<?php 

namespace projectbr\Model;

use \projectbr\DB\Sql;
use projectbr\Mailer;
use \projectbr\Model\Model;

class User extends Model {

	const SESSION = "User";
	const SECRET = "HcodePhp7_Secret";

	public static function getFromSession(){

	    $user = new User();

	    if (isset($_SESSION[User::SESSION]) && (int)$_SESSION[User::SESSION]['iduser'] >0) {
	        $user->setData($_SESSION[User::SESSION]);
        }
 
        return $user;

    }  

//	protected $fields = [
//		"iduser", "idperson", "desperson" ,"nrphone","desemail","deslogin", "despassword", "inadmin", "dtergister","deip"
//	];

    public static function checkLogin($inadmin = true){
        if (!isset($_SESSION[User::SESSION])
            ||
            !$_SESSION[User::SESSION]
            ||
            !(int)$_SESSION[User::SESSION]["iduser"] > 0) {
            //Não esta logado
            return false;
        } else {
            if($inadmin === true && (bool)$_SESSION[User::SESSION]["inadmin"] === true) {
                return true;

            } else if($inadmin === false){
                return true;

            } else {
                return false;
            }
        }

    }

	public static function login($login, $password){

		$db = new Sql();

		$results = $db->select("SELECT * FROM tb_users WHERE deslogin = :LOGIN", array(
			":LOGIN"=>$login
		));

		if (count($results) === 0) {
			throw new \Exception("Não foi possível fazer login.");
		}

		$data = $results[0];

		if (password_verify($password, $data["despassword"])) {

			$user = new User();
			$user->setData($data);

			$_SESSION[User::SESSION] = $user->getValues();

			return $user;

		} else {

             header("Location: /admin/login");
            exit;
		//	throw new \Exception("Não foi possível fazer login.");

		}

	}

	public static function logout()
	{

		$_SESSION[User::SESSION] = NULL;

	}

	public static function verifyLogin($inadmin = true) {

		if (User::checkLogin($inadmin)) {
			
			return true;

		} else {
            header("Location: /admin/login");
            exit;
        }

	}

	public static function listAll() {
	    $sql = new Sql();

	   return $sql->select("SELECT * FROM tb_users a INNER JOIN tb_persons b USING(idperson) ORDER BY b.desperson");
    }

    public function save(){
	    $sql = new Sql();

	   $results =  $sql->select("CALL sp_users_save(:desperson,:deslogin,:despassword,:desemail,:nrphone,:inadmin)",array(
	        ":desperson"=>$this->getdesperson(),
            ":deslogin"=>$this->getdeslogin(),
            ":despassword"=>password_hash($this->getdespassword(), PASSWORD_DEFAULT,["cost"=>12]),
            ":desemail"=>$this->getdesemail(),
            ":nrphone"=>$this->getnrphone(),
            ":inadmin"=>$this->getinadmin()
        ));

	   $this->setData($results[0]);

    }

    public function get($iduser) {
        $sql = new Sql();

        $results =  $sql->select("SELECT * FROM tb_users a INNER JOIN tb_persons b USING(idperson) WHERE a.iduser = :iduser", array(
            ":iduser"=>$iduser
        ));

        $this->setData($results[0]);
    }

    public function update() {
        $sql = new Sql();

        $results =  $sql->select("CALL sp_usersupdate_save(:iduser,:desperson,:deslogin,:despassword,:desemail,:nrphone,:inadmin)",array(
            ":iduser"=>$this->getiduser(),
            ":desperson"=>$this->getdesperson(),
            ":deslogin"=>$this->getdeslogin(),
            ":despassword"=>$this->getdespassword(),
            ":desemail"=>$this->getdesemail(),
            ":nrphone"=>$this->getnrphone(),
            ":inadmin"=>$this->getinadmin()
        ));

        $this->setData($results[0]);
    }

    public function delete(){
        $sql = new Sql();

        $sql->query("CALL sp_users_delete(:iduser)", array(
                ":iduser"=>$this->getiduser()
            ));


    }

    public static function getForgot($email) {


	    $sql = new Sql();

	    $results = $sql->select(
	        "SELECT * FROM tb_persons a INNER JOIN tb_users b USING(idperson) WHERE a.desemail = :email", array(
              ":email"=>$email
        ));

	    if (count($results) === 0) {
	        throw \Exception("Não foi possivel recuperar a senha.");
        } else {
	        $data = $results[0];

	        $results_recovery = $sql->select("CALL sp_userspasswordsrecoveries_create(:iduser,:desip)",array(
	           ":iduser"=>$data["iduser"],
               ":desip"=>$_SERVER["REMOTE_ADDR"]
            ));

	        if(count($results_recovery) === 0 ) {
                throw \Exception("Não foi possivel recuperar a senha.");
            } else {
	            $data_recovery = $results_recovery[0];


	          $code = base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, User::SECRET, $data_recovery["idrecovery"], MCRYPT_MODE_ECB));
             //   $code = $data_recovery["idrecovery"];



                $link = "http://tecommerce.hol.es/admin/forgot/reset?code=$code";

	        //   $link = "http://www.mecommerce.com.br/admin/forgot/reset?code=$code";
 


	            $mailer = new Mailer(
	                $data["desemail"],
                    $data["desperson"],
                    "Redefinir senha do Mecommerce",
                    "forgot",
                    array(
                       "name"=>$data["desperson"],
                        "link"=>$link
                    ));
	            $mailer->send();

	            return $data;

            }

        }

    }

    public static function validForgotDecrypt($code) {

	  $disrecovery = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, User::SECRET, base64_decode($code), MCRYPT_MODE_ECB);

	  $sql = new Sql();

	  $results = $sql->select("SELECT * FROM
	  tb_userspasswordsrecoveries a 
	  INNER JOIN tb_users b USING(iduser)
	  INNER JOIN tb_persons c USING(idperson) WHERE
	  a.idrecovery = :idrecovery AND 
	  a.dtrecovery IS NULL AND 
	  DATE_ADD(a.dtregister, INTERVAL 1 HOUR) >= NOW()", array(
	      ":idrecovery"=>$disrecovery
      ));

    //  var_dump($results);

	  if(count($results) === 0) {
          throw \Exception("Não foi possivel recuperar a senha.");
      } else {
	      return $results[0];
      }
	}

	public static function setForgotUsed($idrecovery){
        $sql = new Sql();

        $sql->query("UPDATE tb_userspasswordsrecoveries SET 
        dtrecovery = NOW() WHERE idrecovery = :idrecovery", array(
            ":idrecovery"=>$idrecovery
        ));

    }

    public function setPassword($password) {
        $sql = new Sql();

        $sql->query("UPDATE tb_users SET 
        despassword = :password WHERE iduser = :iduser", array(
           ":password"=>$password,
           "iduser"=>$this->getiduser()
        ));
    }


}

 ?>