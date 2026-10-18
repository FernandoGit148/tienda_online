<?php
if (isset($_GET['detail'])) {
    $id = filter_input(INPUT_GET, 'detail', FILTER_VALIDATE_INT);

    if (!$id || $id < 1) {
        header('Location: index.php');
        exit;
    }

    $product = ProductRepository::getProductById($id);

    if ($product === null) {
        header('Location: index.php');
        exit;
    }

    require_once("views/productDetail.phtml");
    exit;
}

if (isset($_GET['new'])) {
    require_once("views/newProduct.phtml");
    exit;
}
?>