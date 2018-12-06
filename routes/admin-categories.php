<?php

use \projectbr\Model\Category;
use \projectbr\Model\User;
use \projectbr\PageAdmin;

//-------------------Categorias dos Produtos-----------------//
$app->get('/admin/categories', function (){

    User::verifyLogin();

    $categories = Category::listAll();
    $page = new PageAdmin();

    $page->setTpl("categories", array(
        "categories"=>$categories
    ));
});

$app->get('/admin/categories/create', function (){

    User::verifyLogin();

    $page = new PageAdmin();

    $page->setTpl("categories-create");
});

$app->post('/admin/categories/create', function (){

    User::verifyLogin();

    $category = new Category();

    $category->setData($_POST);

    $category->save();

    header("Location: /admin/categories");
    exit;

});

$app->get('/admin/categories/:idcategory/delete', function ($idcategory){

    User::verifyLogin();

    $category = new Category();

    $category->get((int)$idcategory);

    $category->delete();

    header("Location: /admin/categories");
    exit;
});

$app->get('/admin/categories/:idcategory', function ($idcategory){

    User::verifyLogin();

    $category = new Category();

    $category->get((int)$idcategory);

    $page = new PageAdmin();

    $page->setTpl("categories-update", array(
        "category"=>$category->getValues()
    ));
});

$app->post('/admin/categories/:idcategory', function ($idcategory){

    User::verifyLogin();

    $category = new Category();

    $category->get((int)$idcategory);

    $category->setData($_POST);

    $category->save();

    header("Location: /admin/categories");
    exit;
});


/////------------------- Categorias e Produtos relacionados--------

$app->get('/admin/categories/:idcategory/products', function ($idcategory){

    User::verifyLogin();

    $category = new Category();

    $category->get((int)$idcategory); 

    $page = new PageAdmin();

    $page->setTpl("categories-products", array(
       'category'=>$category->getValues(),
       'productsRelated'=>$category->getProducts(),
       'productsNotRelated'=>$category->getProducts(false)
    ));

});

// ------------------------ Relacionar produtos a categorias --------------
$app->get('/admin/categories/:idcategory/products/:idproduct/add', function ($idcategory,$idproduct){

    User::verifyLogin();

    $category = new Category();

    $category->get((int)$idcategory);

    $product = new \projectbr\Model\Products();

    $product->get((int)$idproduct);

    $category->addProduct($product);

    header("Location: /admin/categories/".$idcategory."/products");
    exit;
});

// ------------------------ Remover relação entre produtos e categorias --------------
$app->get('/admin/categories/:idcategory/products/:idproduct/remove', function ($idcategory,$idproduct){

    User::verifyLogin();

    $category = new Category();

    $category->get((int)$idcategory);

    $product = new \projectbr\Model\Products();

    $product->get((int)$idproduct);

    $category->removeProduct($product);

    header("Location: /admin/categories/". $idcategory."/products");
    exit;
});
