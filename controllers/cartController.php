
<?php

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Añadir un producto al carrito
if (isset($_GET['add'])) {
    $id = filter_input(INPUT_GET, 'add', FILTER_VALIDATE_INT);

    if ($id && $id > 0) {
        $product = ProductRepository::getProductById($id);

        if ($product !== null && $product->getStock() > 0) {
            if (isset($_SESSION['cart'][$id])) {
                if ($_SESSION['cart'][$id] < $product->getStock()) {
                    $_SESSION['cart'][$id]++;
                }
            } else {
                $_SESSION['cart'][$id] = 1;
            }
        }
    }

    header('Location: index.php?c=cart&show');
    exit;
}

// Cambiar la cantidad de un producto
if (isset($_POST['update'])) {
    $id = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
    $quantity = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT);

    if ($id && $id > 0 && $quantity !== false && $quantity !== null) {
        if ($quantity <= 0) {
            unset($_SESSION['cart'][$id]);
        } else {
            $product = ProductRepository::getProductById($id);

            if ($product !== null && $quantity <= $product->getStock()) {
                $_SESSION['cart'][$id] = $quantity;
            }
        }
    }

    header('Location: index.php?c=cart&show');
    exit;
}

// Eliminar un producto del carrito
if (isset($_GET['remove'])) {
    $id = filter_input(INPUT_GET, 'remove', FILTER_VALIDATE_INT);

    if ($id && $id > 0) {
        unset($_SESSION['cart'][$id]);
    }

    header('Location: index.php?c=cart&show');
    exit;
}

// Mostrar el carrito
if (isset($_GET['show'])) {
    $cartProducts = [];

    foreach ($_SESSION['cart'] as $id => $quantity) {
        $product = ProductRepository::getProductById((int) $id);

        if ($product !== null) {
            $cartProducts[] = [
                'product' => $product,
                'quantity' => $quantity
            ];
        } else {
            unset($_SESSION['cart'][$id]);
        }
    }

    require_once('views/cart.phtml');
    exit;
}
