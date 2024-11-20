<?php
require_once "../../config/dbconfig.php";

class Orders
{
    private $conn;

    public function __construct()
    {
        $this->conn = (new Database())->connect();
    }

    public function beginTransaction()
    {
        return $this->conn->beginTransaction();
    }

    public function commit()
    {
        return $this->conn->commit();
    }

    public function rollback()
    {
        return $this->conn->rollBack();
    }

    public function getAllOrders($params = array())
    {
        $query = "SELECT o.id, o.order_number, u.full_name as user_name, sa.full_address as shipping_address, sa.state as shipping_state, sa.city as shipping_city, sa.zip_code as shipping_zipcode, o.order_status, o.net_amount, o.payment_status, o.created_at, o.delivered_at FROM orders o
            LEFT JOIN users u on u.id = o.user_id
            LEFT JOIN shipping_addresses sa on sa.id = o.shipping_address_id
            ORDER BY o.created_at DESC
        ";
        if(!empty($params['limit'])) $query .= " LIMIT ".$params['limit'];
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $resData = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if(!empty($params) && !empty($params['countOnly'])) return count($resData);
        return $resData;
    }

    public function checkIfProductExists($product_id)
    {
        $query = "SELECT o.id FROM orders o join order_products op on op.order_id = o.id where op.product_id = :product_id AND o.order_status != 'delivered' AND op.is_deleted = FALSE";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createOrder($orderData)
    {
        if (empty($orderData['order_number']) || empty($orderData['user_id']) || empty($orderData['net_amount']) || empty($orderData['shipping_address_id'])) return false;
        $query = "INSERT INTO orders (order_number, user_id, shipping_address_id, net_amount) 
                  VALUES (:order_number, :user_id, :shipping_address_id, :net_amount)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':order_number', $orderData['order_number']);
        $stmt->bindParam(':user_id', $orderData['user_id']);
        $stmt->bindParam(':shipping_address_id', $orderData['shipping_address_id']);
        $stmt->bindParam(':net_amount', $orderData['net_amount']);
        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    public function addOrUpdateOrderProducts($orderId, $productIds)
    {
        if (empty($orderId) || empty($productIds) || !count($productIds) > 0) return false;
        $placeholders = rtrim(str_repeat('(?, ?, 1), ', count($productIds)), ', ');
        // hard coded quantity to 1 for each product, needs to be added to the frontend
        $query = "INSERT INTO order_products (order_id, product_id, quantity) VALUES $placeholders ON CONFLICT (order_id, product_id) DO UPDATE SET is_deleted = FALSE;";
        $values = [];
        foreach ($productIds as $productId) {
            $values[] = $orderId;
            $values[] = $productId;
        }
        $stmt = $this->conn->prepare($query);
        return $stmt->execute($values);
    }

    public function getOrderDetails($order_id)
    {
        if (empty($order_id)) return false;

        $query = "SELECT o.id, o.user_id, u.full_name as user_name, o.shipping_address_id, o.order_status, o.net_amount, o.payment_status, op.product_id, op.quantity 
                  FROM orders o
                  LEFT JOIN users u on u.id = o.user_id
                  LEFT JOIN order_products op on op.order_id = o.id
                  WHERE o.id = :order_id AND op.is_deleted = false";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':order_id', $order_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateOrderDetails($order_id, $data)
    {
        if (empty($order_id) || empty($data)) return false;

        $fields = [];
        $values = [];
        $values[':order_id'] = $data['order_id'];

        if (isset($data['shipping_address_id'])) {
            $fields[] = "shipping_address_id = :shipping_address_id";
            $values[':shipping_address_id'] = $data['shipping_address_id'];
        }
        if (isset($data['net_amount'])) {
            $fields[] = "net_amount = :net_amount";
            $values[':net_amount'] = $data['net_amount'];
        }

        $query = "UPDATE orders SET " . implode(", ", $fields) . " WHERE id = :order_id";
        $stmt = $this->conn->prepare($query);
        $stmt->execute($values);
        return $stmt->rowCount() > 0;
    }

    public function removeOrderProducts($orderId, $removeProductIds)
    {
        if (empty($removeProductIds)) return false;
        $query = "
            UPDATE order_products
            SET is_deleted = TRUE
            WHERE order_id = :order_id
            AND product_id IN (" . implode(", ", $removeProductIds) . ");
        ";
        $values = array_merge([$orderId]);
        $stmt = $this->conn->prepare($query);
        return $stmt->execute($values);
    }

}
