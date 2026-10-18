<?php

class OrderLine{

    private $id;
    private $product;
    private $quantity;
    private $price;

    public function __construct($id, $product_id, $quantity, $price, $order_id) {
        $this->id = $id;
        $this->product = ProductRepository::getProductById($product_id);
        $this->quantity = $quantity;
        $this->price = $price;
        $this->order = OrderRepository::getOrderById($order_id);
    }

    public function getId() {
        return $this->order_id;
    }

    public function getProductId() {
        return $this->product_id;
    }

    public function getQuantity() {
        return $this->quantity;
    }

    public function getPrice() {
        return $this->price;
    }

    public function getOrder() {
        return $this->order;
    }
}