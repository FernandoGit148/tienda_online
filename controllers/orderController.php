<?php
/*
 * Mostrar el historial de pedidos del usuario
 * URL: index.php?c=order&history
 */
if (isset($_GET['history'])) {

    // Comprobar que el usuario ha iniciado sesión
    if (!isset($_SESSION['user'])) {
        header('Location: index.php?login');
        exit;
    }

    // Obtener el ID del usuario conectado
    $userId = $_SESSION['user']->getId();

    // Consultar sus pedidos
    $orders = OrderRepository::getOrdersByUserId($userId);

    // Mostrar la vista del historial
    require_once __DIR__ . '/../views/orders.phtml';
    exit;
}
// =====================================================
// 1. MOSTRAR LA PANTALLA DE CONFIRMACIÓN
// URL: index.php?c=order&checkout
// =====================================================

if (isset($_GET['checkout'])) {

    if (!isset($_SESSION['user'])) {
        header('Location: index.php?login');
        exit;
    }

    if (empty($_SESSION['cart'])) {
        header('Location: index.php?c=cart&show');
        exit;
    }

    require_once __DIR__ . '/../views/checkout.phtml';
    exit;
}


// =====================================================
// 2. CONFIRMAR Y GUARDAR EL PEDIDO
// =====================================================

if (isset($_POST['checkout'])) {

    // Comprobar que hay un usuario conectado
    if (!isset($_SESSION['user'])) {
        header('Location: index.php?login');
        exit;
    }

    // Comprobar que el carrito no está vacío
    if (empty($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
        header('Location: index.php?c=cart&show');
        exit;
    }

    $db = null;
    $transactionStarted = false;
    $orderId = null;

    try {

        // Conectar con MySQL e iniciar transacción
        $db = DB::connect();
        $db->begin_transaction();
        $transactionStarted = true;

        $buyerId = (int) $_SESSION['user']->getId();
        $total = 0;
        $items = [];

        // ---------------------------------------------
        // 2.1. Comprobar productos, cantidades y stock
        // ---------------------------------------------

        foreach ($_SESSION['cart'] as $id => $quantity) {

            $id = filter_var($id, FILTER_VALIDATE_INT);
            $quantity = filter_var($quantity, FILTER_VALIDATE_INT);

            if (
                $id === false || $id <= 0 ||
                $quantity === false || $quantity <= 0
            ) {
                throw new Exception(
                    'El producto o la cantidad no son válidos.'
                );
            }

            $stmt = $db->prepare(
                'SELECT name, price, stock
                 FROM products
                 WHERE id = ?
                 FOR UPDATE'
            );

            $stmt->bind_param('i', $id);
            $stmt->execute();

            $result = $stmt->get_result();
            $product = $result->fetch_assoc();

            $stmt->close();

            if (!$product) {
                throw new Exception(
                    'Uno de los productos ya no existe.'
                );
            }

            if ($quantity > (int) $product['stock']) {
                throw new Exception(
                    'No hay stock suficiente para: ' .
                    $product['name']
                );
            }

            $price = (float) $product['price'];

            $total += $price * $quantity;

            $items[] = [
                'id' => $id,
                'quantity' => $quantity,
                'price' => $price
            ];
        }

        if (empty($items)) {
            throw new Exception('El carrito está vacío.');
        }

        // ---------------------------------------------
        // 2.2. Crear el pedido en la tabla orders
        // ---------------------------------------------

        $stmt = $db->prepare(
            "INSERT INTO orders
                (buyer_id, total_price, status)
             VALUES (?, ?, 'pending')"
        );

        $stmt->bind_param('id', $buyerId, $total);
        $stmt->execute();

        $orderId = $db->insert_id;

        $stmt->close();

        // ---------------------------------------------
        // 2.3. Guardar las líneas del pedido
        //      y actualizar el stock
        // ---------------------------------------------

        foreach ($items as $item) {

            // Insertar línea del pedido
            $stmt = $db->prepare(
                'INSERT INTO order_lines
                    (order_id, product_id, quantity, price)
                 VALUES (?, ?, ?, ?)'
            );

            $stmt->bind_param(
                'iiid',
                $orderId,
                $item['id'],
                $item['quantity'],
                $item['price']
            );

            $stmt->execute();
            $stmt->close();

            // Descontar las unidades compradas
            $stmt = $db->prepare(
                'UPDATE products
                 SET stock = stock - ?
                 WHERE id = ? AND stock >= ?'
            );

            $stmt->bind_param(
                'iii',
                $item['quantity'],
                $item['id'],
                $item['quantity']
            );

            $stmt->execute();

            if ($stmt->affected_rows !== 1) {
                $stmt->close();

                throw new Exception(
                    'No se pudo actualizar el stock.'
                );
            }

            $stmt->close();
        }

        // ---------------------------------------------
        // 2.4. Confirmar definitivamente la transacción
        // ---------------------------------------------

        $db->commit();
        $transactionStarted = false;

        // Vaciar el carrito tras guardar correctamente
        $_SESSION['cart'] = [];

        // Guardar el identificador para la vista de éxito
        $_SESSION['order_success'] = $orderId;

        // Cerrar la conexión antes de mostrar la vista
        $db->close();
        $db = null;

        // Mostrar confirmación del pedido
        require __DIR__ . '/../views/orderSuccess.phtml';
        exit;

    } catch (Throwable $e) {

        // Si el pedido no se ha confirmado,
        // intentar deshacer la transacción
        if (
            $transactionStarted &&
            $db instanceof mysqli
        ) {
            try {
                $db->rollback();
            } catch (Throwable $rollbackError) {
                error_log(
                    'Error al revertir el pedido: ' .
                    $rollbackError->getMessage()
                );
            }
        }

        // Cerrar la conexión si sigue abierta
        if ($db instanceof mysqli) {
            try {
                $db->close();
            } catch (Throwable $closeError) {
                error_log($closeError->getMessage());
            }
        }

        error_log(
            'Error al confirmar el pedido: ' .
            $e->getMessage()
        );

        // No vaciar el carrito si la compra falla
        $_SESSION['order_error'] =
            'No se ha podido confirmar el pedido. ' .
            'Comprueba el stock e inténtalo de nuevo.';

        header('Location: index.php?c=cart&show');
        exit;
    }
}