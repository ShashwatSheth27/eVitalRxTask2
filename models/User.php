<?php

require_once __DIR__ . '/../config/dbconfig.php';

class User
{
    private $conn;

    public function __construct()
    {
        $this->conn = (new Database())->connect();
    }

    public function getAllUsers($params = array())
    {
        $query = "SELECT u.id, u.full_name, u.email, u.phone_number, ur.role_name FROM users u 
            left join user_roles ur on ur.id = u.role_id WHERE u.is_deleted = false";
        if(!empty($params['role_id'])) $query .= " AND u.role_id = ".$params['role_id'];
        $query .= " ORDER BY u.id DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $resData = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if(!empty($params) && !empty($params['countOnly'])) return count($resData);
        return $resData;
    }

    public function getUserByEmail($email)
    {
        $query = "SELECT u.id, u.full_name, u.password, ur.role_name FROM users u LEFT JOIN user_roles ur ON ur.id = u.role_id WHERE u.email = :email LIMIT 1;";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":email", $email);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getUserById($user_id)
    {
        if (empty($user_id)) return false;
        $query = "SELECT id, full_name, email, phone_number, role_id FROM users WHERE id = :user_id LIMIT 1;";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getAllRoles()
    {
        $query = "SELECT id, role_name FROM user_roles";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createUser($role_id, $full_name, $email, $hashed_password, $phone_number)
    {
        if (empty($role_id) || empty($full_name) || empty($email) || empty($hashed_password) || empty($phone_number)) return false;

        $query = "INSERT INTO users (role_id, full_name, email, password, phone_number) VALUES (:role_id, :full_name, :email, :password, :phone_number)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':role_id', $role_id, PDO::PARAM_INT);
        $stmt->bindParam(':full_name', $full_name, PDO::PARAM_STR);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->bindParam(':password', $hashed_password, PDO::PARAM_STR);
        $stmt->bindParam(':phone_number', $phone_number, PDO::PARAM_STR);

        return $stmt->execute();
    }

    public function updateUserDetails($user_id, $data)
    {
        if (empty($user_id)) return false;
        $query = "UPDATE users SET ";
        $fields = [];
        $params = [];
        foreach ($data as $field => $value) {
            if (!empty($value) || $value === "0") {
                $fields[] = "$field = :$field";
                $params[":$field"] = $value;
            }
        }
        if (empty($fields)) {
            return false;
        }
        $query .= implode(", ", $fields) . " WHERE id = :user_id";
        $params[':user_id'] = $user_id;
        $stmt = $this->conn->prepare($query);
        return $stmt->execute($params);
    }

    public function deleteUserById($user_id)
    {
        if (empty($user_id)) return false;
        $query = "UPDATE users SET is_deleted = true WHERE id = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function getUserAddressesById($user_id)
    {
        if (empty($user_id)) return false;
        $query = "SELECT sa.id, sa.full_address, sa.city, sa.state, sa.zip_code FROM shipping_addresses sa WHERE sa.user_id = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

}
