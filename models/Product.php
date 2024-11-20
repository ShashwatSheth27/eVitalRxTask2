<?php
require_once "../../config/dbconfig.php";

class Product
{
    private $conn;

    public function __construct()
    {
        $this->conn = (new Database())->connect();
    }

    public function getAllProducts($params = array())
    {
        $query = "SELECT p.id, p.product_name, p.description, p.price, p.stock_quantity, c.category_name FROM products p 
                    left join categories c on c.id = p.category_id 
                    -- left join product_media pm on pm.product_id = p.id 
                    WHERE p.is_deleted = false 
                    ORDER BY p.id DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $resData = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (!empty($params) && !empty($params['countOnly'])) return count($resData);
        return $resData;
    }

    public function getProductById($product_id)
    {
        if (empty($product_id)) return false;
        $query = "SELECT p.id, p.product_name, p.description, p.price, p.stock_quantity, c.id as category_id FROM products p LEFT JOIN categories c ON c.id = p.category_id WHERE p.id = :product_id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
        $stmt->execute();
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!empty($product) && is_array($product) && count($product) > 0) {
            return $product;
        }
        return false;
    }

    public function createProduct($product_name, $category_id, $description, $price, $stock_quantity)
    {
        if (empty($product_name) || empty($category_id) || empty($description) || empty($price) || empty($stock_quantity)) return false;

        $query = "INSERT INTO products (product_name, category_id, description, price, stock_quantity)
              VALUES (:product_name, :category_id, :description, :price, :stock_quantity)";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':product_name', $product_name);
        $stmt->bindParam(':category_id', $category_id);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':price', $price);
        $stmt->bindParam(':stock_quantity', $stock_quantity);
        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    public function saveProductMedia($productId, $mediaDetails)
    {
        if (empty($mediaDetails)) return false;
        $placeholders = [];
        $values = [];
        foreach ($mediaDetails as $media) {
            $placeholders[] = "(?, ?, ?)";
            $values[] = $productId;
            $values[] = $media['file_path'];
            $values[] = $media['file_type'];
        }
        $placeholders = implode(", ", $placeholders);
        $sql = "INSERT INTO product_media (product_id, path, type) VALUES $placeholders";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute($values);
    }

    public function updateProduct($product_id, $data)
    {
        if (empty($product_id)) return false;

        $query = "UPDATE products SET ";
        $fields = [];
        $params = [];

        foreach ($data as $field => $value) {
            if (!empty($value) || $value === "0") {
                $fields[] = "$field = :$field";
                $params[":$field"] = $value;
            }
        }
        if (empty($fields)) return false;
        $query .= implode(", ", $fields) . " WHERE id = :product_id";
        $params[':product_id'] = $product_id;
        $stmt = $this->conn->prepare($query);
        return $stmt->execute($params);
    }

    public function deleteProductById($product_id)
    {
        if (empty($product_id)) return false;
        $query = "UPDATE products SET is_deleted = true WHERE id = :product_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function fetchProductsByCategoryId($category_id)
    {
        if (empty($category_id)) return false;
        $query = "SELECT p.id FROM products p JOIN categories c ON c.id = p.category_id WHERE c.id = :category_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':category_id', $category_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
