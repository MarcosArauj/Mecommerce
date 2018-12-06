<?php 

namespace projectbr\Model;

use \projectbr\DB\Sql;
use \projectbr\Model\Model;
use \projectbr\Model\Products;


class Cart extends Model {

    const SESSION = "Cart";

    public function get(int $idacart) {
        $sql = new Sql();

        $results = $sql->select("SELECT * FROM tb_carts WHERE idcart",array(
            ':idcart'=>$idacart
        ));

        if (count($results) > 0) {
            $this->setData($results[0]);

        }
    }

    public function getFromSessionID() {
        $sql = new Sql();

        $results = $sql->select("SELECT * FROM tb_carts WHERE dessessionid = :dessessionid",array(
            ':dessessionid'=>session_id()
        ));

        if (count($results) > 0) {
            $this->setData($results[0]);

        }
    }

    public function setToSession(){
        $_SESSION[Cart::SESSION] = $this->getValues();
    }

    public function save() {
        $sql = new Sql();

        $results = $sql->select("CALL sp_carts_save(:idcart,:dessessionid,:iduser,:deszipcode,:vlfreight,:nrdays)",array(
            ':idcart'=>$this->getidcar(),
            ':dessessionid'=>$this->getdessessionid(),
            ':iduser'=>$this->getiduser(),
            ':deszipcode'=>$this->getdeszipcode(),
            ':vlfreight'=>$this->getvlfreight(),
            ':nrdays'=>$this->getnrdays()
        ));

        $this->setData($results[0]);
    }


    public static function getFromSession(){
        $cart = new Cart();

        if(isset($_SESSION[Cart::SESSION]) && (int)$_SESSION[Cart::SESSION]['idcart'] > 0 ) {
            $cart->get((int)$_SESSION[Cart::SESSION]['idcart']);
        } else {
            $cart->getFromSessionID();

            if (!(int)$cart->geticart() > 0) {
                $data = array(
                  'dessessionid'=>session_id()
                );

                if (User::checkLogin(false)) {
                    $user = User::getFromSession();

                    $data['iduser'] = $user->getiduser();

                }

                $cart->setData($data);

                $cart->save();

                $cart->setToSession();

            }
        }

        return $cart;
    }

    public function addProduct(Products $products) {
        $sql = new Sql();

        $sql->query("INSERT INTO tb_cartsproducts(idcart,idproduct) VALUES(:idcart,:idproduct)", array(
            ':idcart'=>$this->getidcart(),
            ':idproduct'=>$products->getidproduct()
        ));
    }

    public function removeProduct(Products $products, $all = false) {

        $sql = new Sql();

        if ($all) {
            //remove todos os produtos do site
            $sql->query("UPDATE tb_cartsproducts SET dtremoved = NOW() WHERE idcart = :idcart AND 
                idproduct = :idproduct AND dtremoved IS NULL", array(
                    ':idcart'=>$this->getidcart(),
                    ':idproduct'=>$products->getidproduct()
            ));
        } else {
            //remove apenas um produto do carrinho
            $sql->query("UPDATE tb_cartsproducts SET dtremoved = NOW() WHERE idcart = :idcart AND 
                idproduct = :idproduct AND dtremoved IS NULL LIMIT 1", array(
                ':idcart'=>$this->getidcart(),
                ':idproduct'=>$products->getidproduct()
            ));
        }
    }

    ////----- Listar todos os produtos que foram adicionados no carrinho
    public function getProducts(){

        $sql = new Sql();

        $rows = $sql->select("SELECT b.idproduct, b.desproduct, b.vlprice, b.vlwidth, b.vlheight, b.vllength, b.vlweight, b.desurl,
              COUNT(*) AS nrqtd, SUM(b.vlprice) AS vltotal
               FROM tb_cartsproducts a 
               INNER JOIN tb_products b ON a.idproduct = b.idproduct
               WHERE a.idcart = 1 AND a.dtremoved IS NULL 
               GROUP BY b.idproduct, b.desproduct, b.vlprice, b.vlwidth, b.vlheight, b.vllength, b.vlweight, b.desurl
               ORDER BY b.desproduct",
            array(
            ':idcart'=>$this->getidcart()
        ));

        return Products::checkList($rows);

    }






}
