<?php
session_start();
require_once "../../models/Product.php";
require_once "../../models/Orders.php";

class ProductController
{
    private $productModel;

    public function __construct()
    {
        $this->productModel = new Product();
    }

    public function getProducts()
    {
        $response = array('success' => 0);
        if (isset($_SESSION['user_id'])) {
            $response['success'] = -1;
            $products = $this->productModel->getAllProducts();
            if (!empty($products) && is_array($products) && count($products) > 0) {
                $response['success'] = 1;
                $response['data'] = $products;
            }
        }
        header('Content-Type: application/json');
        echo json_encode($response);
    }

    public function getProductDetails()
    {
        $response = array('success' => 0);
        if (isset($_SESSION['user_id'])) {
            $response['success'] = -1;
            $data = json_decode(file_get_contents("php://input"), true);
            if (!empty($data['product_id'])) {
                $product_id = $data['product_id'];
                $product = $this->productModel->getProductById($product_id);
                if (!empty($product)) {
                    $response['success'] = 1;
                    $response['data'] = $product;
                }
            }
        }
        header('Content-Type: application/json');
        echo json_encode($response);
    }


    public function createProduct()
    {
        $response = array('success' => 0);

        if (isset($_SESSION['user_id'])) {
            $response['success'] = -1;
            $requiredFields = ['product_name', 'category_id', 'description', 'price', 'stock_quantity'];
            foreach ($requiredFields as $field) {
                if (empty($_POST[$field])) {
                    $response['message'] = "Field '$field' is required.";
                    header('Content-Type: application/json');
                    echo json_encode($response);
                    exit();
                }
            }

            $product_name = htmlspecialchars(trim($_POST['product_name']));
            $category_id = intval($_POST['category_id']);
            $description = htmlspecialchars(trim($_POST['description']));
            $price = floatval($_POST['price']);
            $stock_quantity = intval($_POST['stock_quantity']);

            $productCreatedId = $this->productModel->createProduct($product_name, $category_id, $description, $price, $stock_quantity);

            if (!empty($productCreatedId)) {
                $productMedia = array();
                if (isset($_FILES['product_media']) && count($_FILES['product_media']['name']) > 0) {
                    $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/eVitalRxTask2/assets/product_media/';
                    $maxFileSize = 5 * 1024 * 1024;
                    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'video/mp4', 'audio/mpeg', 'audio/wav'];
                    foreach ($_FILES['product_media']['name'] as $key => $fileName) {
                        $fileTmpName = $_FILES['product_media']['tmp_name'][$key];
                        $fileType = $_FILES['product_media']['type'][$key];
                        $fileSize = $_FILES['product_media']['size'][$key];
                        $fileExt = pathinfo($fileName, PATHINFO_EXTENSION);
                        $newFileName = uniqid('media_', true) . '.' . $fileExt;
                        $uploadPath = $uploadDir . $newFileName;

                        if ($fileSize > $maxFileSize) {
                            $response['message'] = "File '{$fileName}' exceeds the maximum size limit of 10 MB.";
                            continue;
                        }
                        if (!in_array($fileType, $allowedTypes)) {
                            $response['message'] =  "File '{$fileName}' is not an allowed type. Only images, videos, and audio files are allowed.";
                            continue;
                        }
                        if (move_uploaded_file($fileTmpName, $uploadPath)) {
                            $productMedia[] = [
                                'file_path' => $uploadPath,
                                'file_type' => $fileType,
                            ];
                        }
                    }

                    if (!empty($productMedia)) {
                        $this->productModel->saveProductMedia($productCreatedId, $productMedia);
                    }
                }

                $response['success'] = 1;
            }
        }

        header('Content-Type: application/json');
        echo json_encode($response);
    }

    public function updateProduct()
    {
        $response = array('success' => 0);

        if (isset($_SESSION['user_id'])) {
            $response['success'] = -1;
            $data = json_decode(file_get_contents("php://input"), true);
            $product_id = intval($data['product_id']);
            $new_data = array();
            if (!empty($data['product_name'])) $new_data['product_name'] = htmlspecialchars(trim($data['product_name']));
            if (!empty($data['category_id'])) $new_data['category_id'] = intval($data['category_id']);
            if (!empty($data['description'])) $new_data['description'] = htmlspecialchars(trim($data['description']));
            if (!empty($data['price'])) $new_data['price'] = floatval($data['price']);
            if (!empty($data['stock_quantity'])) $new_data['stock_quantity'] = intval($data['stock_quantity']);
            $productUpdated = $this->productModel->updateProduct($product_id, $new_data);
            if (!empty($productUpdated)) {
                $response['success'] = 1;
                $response['message'] = 'Product updated successfully.';
            }
        }

        header('Content-Type: application/json');
        echo json_encode($response);
    }

    public function deleteProduct()
    {
        $response = array('success' => 0);
        if (isset($_SESSION['user_id'])) {
            $response['success'] = -1;
            $data = json_decode(file_get_contents("php://input"), true);
            if (!empty($data['product_id'])) {
                $response['success'] = -2;
                $response['message'] = 'Product is associated with order';
                $product_id = intval($data['product_id']);
                $orders = new Orders();
                $productsInOrder = $orders->checkIfProductExists($product_id);
                if (empty($productsInOrder)) {
                    $isDeleted = $this->productModel->deleteProductById($product_id);
                    if (!empty($isDeleted)) {
                        $response['success'] = 1;
                        $response['message'] = "Product deleted successfully.";
                    }
                }
            }
        }
        header('Content-Type: application/json');
        echo json_encode($response);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $productController = new ProductController();
    $data = json_decode(file_get_contents("php://input"), true);
    if (empty($data)) $data = $_POST;
    if (!empty($data) && !empty($data['action']) && method_exists($productController, $data['action'])) {
        $productController->{$data['action']}();
    }
}
