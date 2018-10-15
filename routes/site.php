<?php
use \projectbr\Page;
use \projectbr\Model\Products;
use \projectbr\Model\Category;
use \projectbr\Model\Cart;

define("DS", DIRECTORY_SEPARATOR);

$app->get('/', function() {

    $products = Products::listAll();

    $page = new Page();

    Category::updeteFile();

    $page->setTpl("index", array(
        'products'=>Products::checkList($products)
    ));

});

// ------------- Categorias de Produtos----------------
$app->get('/categories/:idcategory', function ($idcategory){

    $pageAtual = (isset($_GET['page'])) ? (int)$_GET['page'] : 1;

    $category = new Category();

    $category->get((int)$idcategory);

    $pagination = $category->getProductPage($pageAtual);

    $pages = [];

    for ($i = 1;$i <= $pagination['pages'];$i++) {
        array_push($pages, array(
           'link'=>'/categories/'.$category->getidcategory().'?page='.$i,
            'page'=>$i
        ));
    }

    $page = new Page();

    $page->setTpl("category", array(
        "category"=>$category->getValues(),
        "products"=>$pagination["data"],
        "pages"=>$pages
    ));

});

// ---------- Detalhar produto ------------
$app->get('/products/:desurl', function ($desurl){

    $products = new Products();

    $products->getFromURL($desurl);

    $page = new Page();

    $page->setTpl("product-detail", array(
        'product'=>$products->getValues(),
        'categories'=>$products->getCategories()
    ));

});


//-------- Carrinho de Compras----------------
$app->get('/cart', function (){

    $cart = Cart::getFromSession();

    $page = new Page();

    $page->setTpl("cart", array(
        'cart'=>$cart->getValues(),
        'products'=>$cart->getProducts()
    ));

});

///--------------Adiciona produto no carrinho---------
$app->get('/cart/:idproduct/add', function ($idproduct){

    $product = new Products();

    $product->get((int)$idproduct);

    $cart = Cart::getFromSession();


    $qtd = (isset($_GET['qtd'])) ? (int)$_GET['qtd'] : 1;

    for ($i = 0; $i < $qtd; $i++) {
        $cart->addProduct($product);
    }

    header("Location: /cart");
    exit;
});

///--------------Remove 1 produto do carrinho---------
$app->get('/cart/:idproduct/minus', function ($idproduct){

    $product = new Products();

    $product->get((int)$idproduct);

    $cart = Cart::getFromSession();

    $cart->removeProduct($product);

    header("Location: /cart");
    exit;
});

///--------------Remove todos produtos do carrinho---------
$app->get('/cart/:idproduct/remove', function ($idproduct){

    $product = new Products();

    $product->get((int)$idproduct);

    $cart = Cart::getFromSession();

    $cart->removeProduct($product, true);

    header("Location: /cart");
    exit;
});

  