<?php
session_start();
require_once "../models/User.php";

class AuthController
{
    private $userModel;

    public function __construct()
    {
        $this->userModel = new User();
    }

    public function login()
    {
        $response = array('success' => 0);
        $data = json_decode(file_get_contents("php://input"), true);
        if (!empty($data['email']) && !empty($data['password'])) {
            $response['success'] = -1;
            $response['message'] = 'Invalid email or password';
            $user = $this->userModel->getUserByEmail($data['email']);
            if (!empty($user) && (password_verify($data['password'], $user['password']))) {
                $response['success'] = 1;
                $response['message'] = 'User Authenticated';
                $response['data'] = $user;
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['full_name'];
                $_SESSION['role'] = $user['role_name'];
            }
        }
        header('Content-Type: application/json');
        echo json_encode($response);
    }

    public function logout()
    {
        session_unset();
        session_destroy();
        $response = ['success' => 1, 'message' => 'Logout successful'];
        header('Content-Type: application/json');
        echo json_encode($response);
    }
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $auth = new AuthController();
    $data = json_decode(file_get_contents("php://input"), true);
    if (!empty($data) && !empty($data['action']) && method_exists($auth, $data['action'])) {
        $auth->{$data['action']}();
    }
}
