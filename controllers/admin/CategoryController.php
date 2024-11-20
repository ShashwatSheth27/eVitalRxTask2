<?php
session_start();
require_once "../../models/Category.php";
require_once "../../models/Product.php";

class CategoryController
{
    private $categoryModel;

    public function __construct()
    {
        $this->categoryModel = new Category();
    }

    public function getCategories()
    {
        $response = array('success'=>0);
        if (isset($_SESSION['user_id'])) {
            $response['success'] = -1;
            $categories = $this->categoryModel->getAllCategories();
            if (!empty($categories) && is_array($categories) && count($categories) > 0) {
                $response['success'] = 1;
                $response['data'] = $categories;
            }
        }
        header('Content-Type: application/json');
        echo json_encode($response);
    }

    

    public function createCategory()
    {
        $response = array('success' => 0);
        if (isset($_SESSION['user_id'])) {
            $response['success'] = -1;
            $data = json_decode(file_get_contents("php://input"), true);
            if (!empty($data['category_name'])) {
                $category_name = trim($data['category_name']);
                $category_description = null;
                if(!empty($data['description'])) $category_description = trim($data['description']);
                $categoryExists = $this->categoryModel->getCategoryByName($category_name);
                $response['message'] = 'Category with this name exists';
                if(empty($categoryExists)){
                    $isCreated = $this->categoryModel->createCategory($category_name, $category_description);
                    if (!empty($isCreated)) {
                        $response['success'] = 1;
                        $response['message'] = "Category created successfully.";
                    }
                }
            }
        }
        header('Content-Type: application/json');
        echo json_encode($response);
    }

    public function updateCategory()
    {
        $response = array('success' => 0);
        if (isset($_SESSION['user_id'])) {
            $response['success'] = -1;
            $data = json_decode(file_get_contents("php://input"), true);
            if (!empty($data['category_id'])) {
                $category_id = intval($data['category_id']);
                $category_details = array();
                if(!empty($data['category_name'])) $category_details['category_name'] = trim($data['category_name']);
                if(!empty($data['description'])) $category_details['description'] = !empty($data['description']) ? trim($data['description']) : null;
                $categoryExists = false;
                if(!empty($category_name)) $categoryExists = $this->categoryModel->getCategoryByName($category_details['category_name']);
                $response['message'] = 'Category with this name exists';
                if (empty($categoryExists)) {
                    $isUpdated = $this->categoryModel->updateCategory($category_id, $category_details);
                    if (!empty($isUpdated)) {
                        $response['success'] = 1;
                        $response['message'] = "Category updated successfully.";
                    } else {
                        $response['message'] = "Failed to update category.";
                    }
                } else {
                    $response['message'] = "Category not found.";
                }
            }
        }
        header('Content-Type: application/json');
        echo json_encode($response);
    }

    public function deleteCategory()
    {
        $response = array('success' => 0);
        if (isset($_SESSION['user_id'])) {
            $response['success'] = -1;
            $data = json_decode(file_get_contents("php://input"), true);
            if (!empty($data['category_id'])) {
                $category_id = intval($data['category_id']);
                $productModel = new Product();
                $products = $productModel->fetchProductsByCategoryId($category_id);
                if (empty($products)) {
                    $isDeleted = $this->categoryModel->deleteCategoryById($category_id);
                    if (!empty($isDeleted)) {
                        $response['success'] = 1;
                        $response['message'] = "Category deleted successfully.";
                    }
                }
            }
        }
        header('Content-Type: application/json');
        echo json_encode($response);
    }

    public function getCategoryDetails()
    {
        $response = array('success' => 0);
        if (isset($_SESSION['user_id'])) {
            $response['success'] = -1;
            $data = json_decode(file_get_contents("php://input"), true);
            if (!empty($data['category_id'])) {
                $category_id = intval($data['category_id']);
                $category = $this->categoryModel->getCategoryById($category_id);
                if (!empty($category)) {
                    $response['success'] = 1;
                    $response['data'] = $category;
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
