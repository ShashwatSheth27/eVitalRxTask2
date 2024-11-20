<?php
require_once "../../config/dbconfig.php";

class Category
{
    private $conn;

    public function __construct()
    {
        $this->conn = (new Database())->connect();
    }

    public function getAllCategories($params = array())
    {
        $query = "SELECT c.id, c.category_name, c.description, count(p.id) as number_of_products FROM categories c left join products p on p.category_id = c.id GROUP BY c.id ORDER BY c.id DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $resData = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if(!empty($params) && !empty($params['countOnly'])) return count($resData);
        return $resData;
    }

    public function getCategoryById($id)
    {
        $query = "SELECT id, category_name, description FROM categories WHERE id = :id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getCategoryByName($name)
    {
        $query = "SELECT id FROM categories WHERE category_name = :name LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':name', $name);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function createCategory($category_name, $category_description = null)
    {
        if(empty($category_name)) return false;
        $query = "INSERT INTO categories (category_name, description) VALUES (:name, :description)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':name', $category_name);
        $stmt->bindParam(':description', $category_description);
        return $stmt->execute();
    }

    public function updateCategory($category_id, $data)
    {
        if (empty($category_id)) return false;

        $query = "UPDATE categories SET ";
        $fields = [];
        $params = [];

        foreach ($data as $field => $value) {
            if (!empty($value) || $value === "0") {
                $fields[] = "$field = :$field";
                $params[":$field"] = $value;
            }
        }
        if (empty($fields)) return false;
        $query .= implode(", ", $fields) . " WHERE id = :category_id";
        $params[':category_id'] = $category_id;
        $stmt = $this->conn->prepare($query);
        return $stmt->execute($params);
    }

    public function deleteCategoryById($id)
    {
        $query = "DELETE FROM categories WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

}
