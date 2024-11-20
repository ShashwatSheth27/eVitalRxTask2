<?php
    session_start();
    if (isset($_SESSION['user_id'])) {
        header("Location: ../admin/dashboard.php");
        exit();
    }
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | eVitalRx</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.carousel.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.2/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/main.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>

<body class="flex items-center justify-center min-h-screen bg-gray-100 p-0">
    <div class="w-full max-w-md p-8 bg-white rounded-lg shadow-md">
        <form id="loginForm" class="form-login flex flex-col gap-2">
            <h2 class="form-login-heading">User login</h2>
            
            <!-- Email input field -->
            <input type="text" class="form-control m-0 p-2 border border-gray-300 rounded w-full" name="email" id="email" placeholder="Email Address">
            <div id="emailError" class="text-red-500 text-sm mt-1"></div>

            <!-- Password input field -->
            <input type="password" class="form-control m-0 p-2 border border-gray-300 rounded w-full" name="password" id="password" placeholder="Password">
            <div id="passwordError" class="text-red-500 text-sm mt-1"></div>
            
            <button class="btn btn-lg btn-primary btn-block m-0 p-2 bg-blue-500 text-white rounded w-full" type="submit">Log In</button>
            <div id="error" class="text-red-500 text-sm mt-1"></div>
        </form>
    </div>
</body>

<script>
    $(document).ready(function() {
        $('#loginForm').on('submit', function(event) {
            event.preventDefault();
            $('#error').text('');
            $('#emailError').text('');
            $('#passwordError').text('');

            const email = $("input[name='email']").val();
            const password = $("input[name='password']").val();

            const emailRegex = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$/;
            let emailValid = true;
            if (!email) {
                emailValid = false;
                $('#emailError').text('Email is required.');
            } else if (!emailRegex.test(email)) {
                emailValid = false;
                $('#emailError').text('Please enter a valid email address.');
            }

            let passwordValid = true;
            if (!password) {
                passwordValid = false;
                $('#passwordError').text('Password is required.');
            } else if (password.length < 6) {
                passwordValid = false;
                $('#passwordError').text('Password must be at least 6 characters long.');
            }

            if (!emailValid || !passwordValid) {
                return;
            }

            $.ajax({
                url: '../../controllers/AuthController.php',
                type: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({
                    action: 'login',
                    email: email,
                    password: password
                }),
                success: function(response) {
                    if (response.success == 1) {
                        if(response.data.role_name == 'admin'){
                            window.location.href = '../admin/dashboard.php';
                        } else {
                            $('#error').text('Unauthenticated access');
                        }
                    } else if (response.success == -1) {
                        $('#error').text(response.message || 'Something went wrong');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error:', error);
                    alert('An error occurred. Please try again.');
                }
            });
        });
    });
</script>

</html>
