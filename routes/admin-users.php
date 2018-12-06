<?php

use \projectbr\PageAdmin;
use \projectbr\Model\User;

// tela de Usuarios
$app->get('/admin/users', function() {

    User::verifyLogin();

    $users = User::listAll();

    $page = new PageAdmin();

    $page->setTpl("users", array(
        "users"=>$users
    ));

});

//tela create users
$app->get('/admin/users/create', function() {

    User::verifyLogin();

    $page = new PageAdmin();
    $page->setTpl("users-create");

});

// excluir usuario
$app->get('/admin/users/:iduser/delete', function($iduser) {

    User::verifyLogin();

    $user = new User();

    $user->get((int)$iduser);

    $user->delete();

    header("Location: /admin/users");
    exit;
});

// tela update users
$app->get('/admin/users/:iduser', function($iduser) {

    User::verifyLogin();

    $user = new User();

    $user->get((int)$iduser);

    $page = new PageAdmin();
    $page->setTpl("users-update", array(
        "user"=>$user->getValues()
    ));

});

// salvar users
$app->post('/admin/users/create', function() {

    User::verifyLogin();

    $user = new User();

    $_POST["inadmin"] = (isset($_POST["inadmin"]))?1:0;

    $user->setData($_POST);

    $user->save();

    header("Location: /admin/users");
    exit;
});

// salvar a edicção do usuario

$app->post('/admin/users/:iduser', function($iduser) {

    User::verifyLogin();

    $user = new User();

    $_POST["inadmin"] = (isset($_POST["inadmin"]))?1:0;

    $user->get((int)$iduser);

    $user->setData($_POST);

    $user->update();

    header("Location: /admin/users");
    exit;

});

//Tela de Login

$app->get('/admin/login', function() {

    $page = new PageAdmin([
        "header"=> false,
        "footer"=>false
    ]);

    $page->setTpl("login");

});

// Rota para logar
$app->post('/admin/login', function() {

    User::login($_POST["login"],$_POST["password"]);

    header("Location: /admin");
    exit;
});

// Rota paa deslogar
$app->get('/admin/logout', function() { 

    User::logout();

    header("Location: /admin/login");
    exit;
}); 