<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | eVitalRx</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="../../assets/css/main.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>

<body class="min-h-screen">
    <div class="flex flex-col">
        <div class="flex justify-between items-center p-5 bg-gray-800">
            <a href="dashboard.php" class="text-xl px-3 text-white font-semibold focus:outline-none">Admin Dashboard</a>
            <div class="flex justify-between px-4 py-2 gap-2">
                <button class="block w-full px-4 py-2 text-left text-white rounded bg-gray-700 hover:bg-gray-600" id="logoutBtn">Logout <i class="fa fa-fw fa-power-off ml-2"></i></button>
                <span class="flex items-center w-fit whitespace-nowrap px-4 py-2 text-left text-white rounded bg-gray-700 hover:bg-gray-600 focus:outline-none">
                    <i class="fa fa-user mr-2"></i> <?php echo ($_SESSION['user_name']) ?>
                </span>
            </div>
        </div>
        <div class="flex">
            <?php 
                include('./sidebar.php');
                if (isset($_GET['view_products'])) {
                    include '../admin/products/list.php';
                }
                else if (isset($_GET['insert_product'])) {
                    include '../admin/products/create.php';
                }
                else if (isset($_GET['edit_product'])) {
                    include '../admin/products/edit.php';
                }
                else if (isset($_GET['insert_category'])) {
                    include '../admin/categories/create.php';
                }
                else if (isset($_GET['view_categories'])) {
                    include '../admin/categories/list.php';
                }
                else if (isset($_GET['edit_category'])) {
                    include '../admin/categories/edit.php';
                }
                else if (isset($_GET['view_users'])) {
                    include '../admin/users/list.php';
                }
                else if (isset($_GET['insert_user'])) {
                    include '../admin/users/create.php';
                }
                else if (isset($_GET['edit_user'])) {
                    include '../admin/users/edit.php';
                }
                else if (isset($_GET['view_orders'])) {
                    include '../admin/orders/list.php';
                }
                else if (isset($_GET['insert_order'])) {
                    include '../admin/orders/create.php';
                }
                else if (isset($_GET['edit_order'])) {
                    include '../admin/orders/edit.php';
                }
                else {
                    include '../admin/statistics.php';
                }
            ?>
        </div>
    </div>
</body>

<script>
    $(document).ready(function() {
        $('#logoutBtn').click(function() {
            $.ajax({
                url: '../../controllers/AuthController.php',
                type: 'POST',
                data: JSON.stringify({
                    action: 'logout'
                }),
                success: function(response) {
                    if (response.success == 1) {
                        window.location.href = '../auth/login.php';
                    } else {
                        alert('Logout failed. Please try again.');
                    }
                },
                error: function(xhr, status, error) {
                    alert('An error occurred while logging out. Please try again.');
                }
            });
        });
    });

    function myFunction() {
        document.getElementById("myDropdown").classList.toggle("hidden");
    }
</script>

</html>