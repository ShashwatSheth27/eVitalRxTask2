<?php
session_start();
require_once "../../models/Orders.php";

class CategoryController
{
    private $ordersModel;

    public function __construct()
    {
        $this->ordersModel = new Orders();
    }

    public function fetchOrders()
    {
        $response = array('success' => 0);
        if (isset($_SESSION['user_id'])) {
            $response['success'] = -1;
            $data = json_decode(file_get_contents("php://input"), true);
            $params = array();
            if(!empty($data['limit'])) $params['limit'] = $data['limit'];
            $orders = $this->ordersModel->getAllOrders($params);
            if (!empty($orders) && is_array($orders) && count($orders) > 0) {
                $response['success'] = 1;
                $response['data'] = $orders;
            }
        }
        header('Content-Type: application/json');
        echo json_encode($response);
    }

    public function createOrder()
    {
        $response = array('success' => 0);
        if (isset($_SESSION['user_id'])) {
            $response['success'] = -1;
            $data = json_decode(file_get_contents("php://input"), true);
            if (!empty($data['order_number']) && !empty($data['user_id']) && !empty($data['net_amount']) && !empty($data['shipping_address_id'])) {
                $response['success'] = -2;
                $this->ordersModel->beginTransaction();
                try {
                    $orderCreatedId = $this->ordersModel->createOrder($data);
                    if (!empty($orderCreatedId)) {
                        $productsAdded = $this->ordersModel->addOrUpdateOrderProducts($orderCreatedId, $data['product_ids']);
                        if (!empty($productsAdded)) {
                            $response['success'] = 1;
                            $this->ordersModel->commit();
                        } else {
                            throw new Exception("Failed to add products.");
                        }
                    } else {
                        throw new Exception("Failed to create order.");
                    }
                } catch (Exception $e) {
                    $this->ordersModel->rollback();
                }
            }
        }
        header('Content-Type: application/json');
        echo json_encode($response);
    }

    public function fetchOrderById()
    {
        $response = array('success' => 0);
        if (isset($_SESSION['user_id'])) {
            $response['success'] = -1;
            $data = json_decode(file_get_contents("php://input"), true);
            if (!empty($data['order_id'])) {
                $order = $this->ordersModel->getOrderDetails($data['order_id']);
                if (!empty($order) && count($order) > 0) {
                    $orderDetails = $order[0];
                    $orderDetails['productIds'] = array();
                    unset($orderDetails['product_id']);
                    foreach ($order as $product) array_push($orderDetails['productIds'], $product['product_id']);
                    $response['success'] = 1;
                    $response['data'] = $orderDetails;
                }
            }
        }
        header('Content-Type: application/json');
        echo json_encode($response);
    }

    public function updateOrder()
    {
        $response = array('success' => 0);
        if (isset($_SESSION['user_id'])) {
            $response['success'] = -1;
            $data = json_decode(file_get_contents("php://input"), true);
            if (!empty($data['order_id'])) {
                $response['success'] = -2;
                $this->ordersModel->beginTransaction();
                try {
                    $orderUpdated = $this->ordersModel->updateOrderDetails($data['order_id'], $data);
                    if (empty($orderUpdated)) throw new Exception("Failed to update order details.");
                    if(!empty($data['product_ids'])) {
                        $productsUpdated = $this->ordersModel->addOrUpdateOrderProducts($data['order_id'], $data['product_ids']);
                        if (empty($productsUpdated)) throw new Exception("Failed to add or restore products.");
                    }
                    if(!empty($data['remove_product_ids'])) {
                        $productsRemoved = $this->ordersModel->removeOrderProducts($data['order_id'], $data['remove_product_ids']);
                        if (empty($productsRemoved)) throw new Exception("Failed to remove products.");
                    }
                    $response['success'] = 1;
                    $this->ordersModel->commit();
                } catch (Exception $e) {
                    $response['message'] = $e->getMessage();
                    $this->ordersModel->rollback();
                }
            }
        }
        header('Content-Type: application/json');
        echo json_encode($response);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $categoryController = new CategoryController();
    $data = json_decode(file_get_contents("php://input"), true);
    if (!empty($data) && !empty($data['action']) && method_exists($categoryController, $data['action'])) {
        $categoryController->{$data['action']}();
    }
}
