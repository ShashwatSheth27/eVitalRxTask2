<?php
session_start();
require_once '../../config/dbconfig.php';
require_once '../../models/User.php';

class AdminUserController
{
    private $db;
    private $userModel;

    public function __construct()
    {
        $this->db = (new Database())->connect();
        $this->userModel = new User($this->db);
    }

    public function listUsers()
    {
        $response = array('success' => 0);
        if (isset($_SESSION['user_id'])) {
            $response['success'] = -1;
            $data = json_decode(file_get_contents("php://input"), true);
            $params = array();
            if(!empty($data['role_id'])) $params['role_id'] = $data['role_id'];
            $users = $this->userModel->getAllUsers($params);
            if (!empty($users) && is_array($users) && count($users) > 0) {
                $response['success'] = 1;
                $response['data'] = $users;
            }
        }
        header('Content-Type: application/json');
        echo json_encode($response);
    }

    public function getRoles()
    {
        $response = array('success' => 0);
        if (isset($_SESSION['user_id'])) {
            $response['success'] = -1;
            $user_roles = $this->userModel->getAllRoles();
            if (!empty($user_roles) && is_array($user_roles) && count($user_roles) > 0) {
                $response['success'] = 1;
                $response['data'] = $user_roles;
            }
        }
        header('Content-Type: application/json');
        echo json_encode($response);
    }

    public function createUser()
    {
        $response = array('success' => 0);
        if (isset($_SESSION['user_id'])) {
            $response['success'] = -1;
            $data = json_decode(file_get_contents("php://input"), true);
            $requiredFields = ['role_id', 'full_name', 'email', 'password', 'phone_number'];
            foreach ($requiredFields as $field) {
                if (empty($data[$field])) {
                    $response['message'] = "Field '$field' is required.";
                    header('Content-Type: application/json');
                    echo json_encode($response);
                    exit();
                }
            }

            $role_id = intval($data['role_id']);
            $full_name = htmlspecialchars(trim($data['full_name']));
            $email = filter_var(trim($data['email']), FILTER_VALIDATE_EMAIL);
            $password = htmlspecialchars(trim($data['password']));
            $phone_number = htmlspecialchars(trim($data['phone_number']));

            if (!$email) {
                $response['message'] = "Invalid email format.";
                header('Content-Type: application/json');
                echo json_encode($response);
                exit();
            }

            $hashed_password = password_hash($password, PASSWORD_BCRYPT);

            try {
                $userCreated = $this->userModel->createUser($role_id, $full_name, $email, $hashed_password, $phone_number);
                if (!empty($userCreated)) {
                    $response['success'] = 1;
                    $response['message'] = 'User created successfully';
                }
            }
            catch(Exception $e) {
                $response['message'] = $e->getMessage();
            }
        }
        header('Content-Type: application/json');
        echo json_encode($response);
    }

    public function getUserDetails()
    {
        $response = array('success' => 0);
        if (isset($_SESSION['user_id'])) {
            $response['success'] = -1;
            $data = json_decode(file_get_contents("php://input"), true);
            if (!empty($data['user_id'])) {
                $user_id = $data['user_id'];
                $user = $this->userModel->getUserById($user_id);
                if (!empty($user)) {
                    $response['success'] = 1;
                    $response['data'] = $user;
                }
            }
        }
        header('Content-Type: application/json');
        echo json_encode($response);
    }

    public function updateUser()
    {
        $response = array('success' => 0);

        if (isset($_SESSION['user_id'])) {
            $response['success'] = -1;
            $data = json_decode(file_get_contents("php://input"), true);

            if (!empty($data['user_id'])) {
                $user_id = $data['user_id'];
                $user_details = array();
                if (!empty($data['full_name'])) $user_details['full_name'] = htmlspecialchars(trim($data['full_name']));
                if (!empty($data['email'])) $user_details['email'] = htmlspecialchars(trim($data['email']));
                if (!empty($data['phone_number'])) $user_details['phone_number'] = htmlspecialchars(trim($data['phone_number']));
                if (!empty($data['role_id'])) $user_details['role_id'] = intval($data['role_id']);

                $userUpdated = $this->userModel->updateUserDetails($user_id, $user_details);

                if (!empty($userUpdated)) {
                    $response['success'] = 1;
                    $response['message'] = 'User details updated successfully.';
                }
            }
        }

        header('Content-Type: application/json');
        echo json_encode($response);
    }

    public function deleteUser()
    {
        $response = array('success' => 0);
        if (isset($_SESSION['user_id'])) {
            $response['success'] = -1;
            $data = json_decode(file_get_contents("php://input"), true);
            if (!empty($data['user_id'])) {
                $user_id = intval($data['user_id']);
                $isDeleted = $this->userModel->deleteUserById($user_id);
                if (!empty($isDeleted)) {
                    $response['success'] = 1;
                    $response['message'] = "User deleted successfully.";
                }
            }
        }
        header('Content-Type: application/json');
        echo json_encode($response);
    }

    public function getUserAddresses()
    {
        $response = array('success' => 0);
        if (isset($_SESSION['user_id'])) {
            $response['success'] = -1;
            $data = json_decode(file_get_contents("php://input"), true);
            if (!empty($data['user_id'])) {
                $user_id = $data['user_id'];
                $addresses = $this->userModel->getUserAddressesById($user_id);
                if (!empty($addresses)) {
                    $response['success'] = 1;
                    $response['data'] = $addresses;
                }
            }
        }
        header('Content-Type: application/json');
        echo json_encode($response);
    }

}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $productController = new AdminUserController();
    $data = json_decode(file_get_contents("php://input"), true);
    if (!empty($data) && !empty($data['action']) && method_exists($productController, $data['action'])) {
        $productController->{$data['action']}();
    }
}
