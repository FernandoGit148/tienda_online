<?php

class OrderRepository {

    // Obtener un pedido por su ID
    public static function getOrderById($id) {

        $db = DB::connect();

        $query = "SELECT * FROM orders WHERE id = ?";

        $stmt = $db->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();

        $result = $stmt->get_result();
        $order = $result->fetch_assoc();

        $stmt->close();
        $db->close();

        if (!$order) {
            return null;
        }

        return new Order(
            $order['id'],
            $order['buyer_id'],
            $order['total_price'],
            $order['order_date'],
            $order['status']
        );
    }


    // Obtener todos los pedidos de un usuario
    public static function getOrdersByUserId($userId) {

        $db = DB::connect();

        $query = "SELECT *
                  FROM orders
                  WHERE buyer_id = ?
                  ORDER BY order_date DESC";

        $stmt = $db->prepare($query);
        $stmt->bind_param("i", $userId);
        $stmt->execute();

        $result = $stmt->get_result();

        $orders = [];

        while ($order = $result->fetch_assoc()) {

            $orders[] = new Order(
                $order['id'],
                $order['buyer_id'],
                $order['total_price'],
                $order['order_date'],
                $order['status']
            );
        }

        $stmt->close();
        $db->close();

        return $orders;
    }
}