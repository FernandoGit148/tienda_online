<?php

class OrderLineRepository{
    public static function getOrderLinesByOrderId($order_id){
        $db=DB::connect();
        $query="SELECT * FROM order_lines WHERE order_id=$order_id";
        $result=$db->query($query);
        $orderLines=[];
        while($orderLine=$result->fetch_assoc()){
            $orderLines[]=$orderLine;
        }
        return $orderLines;
    }

    public static function getOrderLineById($id){
        $db=DB::connect();
        $query="SELECT * FROM order_lines WHERE id=$id";
        $result=$db->query($query);
        $orderLine=$result->fetch_assoc();
        return new OrderLine($orderLine['id'], $orderLine['product_id'], $orderLine['quantity'], $orderLine['price']);
    }
}