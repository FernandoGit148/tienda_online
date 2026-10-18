<?php

//cargar modelos
require_once("models/User.php");
require_once("models/Product.php");
require_once("models/Order.php");
require_once("models/OrderLine.php");
require_once("models/ProductRepository.php");
require_once("models/OrderRepository.php");
require_once("models/OrderLineRepository.php");
require_once("models/UserRepository.php");

session_start();

if (isset($_GET['c'])) {
    $controllers = ['user', 'product', 'cart', 'order'];
    $controller = $_GET['c'];

    if (in_array($controller, $controllers, true)) {
        require_once("controllers/" . $controller . "Controller.php");
    }
}

//acciones

//listar productos

//ver login
if(isset($_GET['login'])){
    require_once('views/login.phtml');
    exit;
}
//hacer login

//logout

//register
if(isset($_GET['register'])){
    require_once('views/register.phtml');
    exit;
}

//añadir al carrito

//terminar pedido


// vista por defecto

$products=ProductRepository::getProducts();

require_once("views/mainView.phtml");

?>