<?php
session_start();
require_once "../../models/Category.php";
require_once "../../models/Product.php";
require_once "../../models/User.php";
require_once "../../models/Orders.php";

class AdminController
{
    private $categoryModel;
    private $productModel;
    private $userModel;
    private $ordersModel;

    public function __construct()
    {
        $this->categoryModel = new Category();
        $this->productModel = new Product();
        $this->userModel = new User();
        $this->ordersModel = new Orders();
    }

    public function fetchCounts()
    {
        $response = array('success' => 0);
        if (isset($_SESSION['user_id'])) {
            $response['success'] = -1;
            $stats = array();
            $params = array('countOnly'=>true);
            $stats['categories'] = $this->categoryModel->getAllCategories($params);
            $stats['products'] = $this->productModel->getAllProducts($params);
            $stats['users'] = $this->userModel->getAllUsers($params);
            $stats['orders'] = $this->ordersModel->getAllOrders($params);
            $response['success'] = 1;
            $response['data'] = $stats;
        }
        header('Content-Type: application/json');
        echo json_encode($response);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $categoryController = new AdminController();
    $data = json_decode(file_get_contents("php://input"), true);
    if (!empty($data) && !empty($data['action']) && method_exists($categoryController, $data['action'])) {
        $categoryController->{$data['action']}();
    }
}
