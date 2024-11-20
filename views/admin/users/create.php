<?php
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}
?>
<div class="p-6 bg-white shadow-md rounded-lg mt-3 w-1/2">
    <h2 class="text-2xl font-bold text-center mb-6">Create User</h2>

    <form id="createUserForm">
        
        <div class="mb-4">
            <label class="block text-gray-700">Full Name</label>
            <input type="text" id="full_name" class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-teal-400" required>
            <span id="full_name_error" class="text-red-500 text-sm hidden">Full name is required.</span>
        </div>
        
        <div class="mb-4">
            <label class="block text-gray-700">Email</label>
            <input type="email" id="email" class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-teal-400" required>
            <span id="email_error" class="text-red-500 text-sm hidden">Valid email is required.</span>
        </div>
        
        <div class="mb-4">
            <label class="block text-gray-700">Password</label>
            <input type="password" id="password" class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-teal-400" required>
            <span id="password_error" class="text-red-500 text-sm hidden">Password is required.</span>
        </div>
        
        <div class="mb-4">
            <label class="block text-gray-700">Phone Number</label>
            <input type="text" id="phone_number" class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-teal-400" required>
            <span id="phone_number_error" class="text-red-500 text-sm hidden">Phone number is required.</span>
        </div>
        
        <div class="mb-4">
            <label class="block text-gray-700">Role</label>
            <select id="role_id" class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-teal-400" required>
                <option value="">Select Role</option>
            </select>
            <span id="role_id_error" class="text-red-500 text-sm hidden">Please select a role.</span>
        </div>

        <button type="button" id="createUserBtn" class="w-full bg-teal-500 hover:bg-teal-700 text-white font-bold py-2 rounded-md">Create User</button>
    </form>
</div>

<script>
    $(document).ready(function() {
        $.ajax({
            url: '../../controllers/admin/UserController.php',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({ action: 'getRoles' }),
            success: function(response) {
                if (response.success === 1) {
                    const roleSelect = $('#role_id');
                    response.data.forEach(role => {
                        roleSelect.append(`<option value="${role.id}">${role.role_name}</option>`);
                    });
                } else {
                    alert("Failed to load roles.");
                }
            },
            error: function() {
                alert("An error occurred while fetching roles.");
            }
        });

        $('#createUserBtn').click(function() {
            $('.text-red-500').addClass('hidden');

            let isValid = true;

            const roleId = $('#role_id').val();
            const fullName = $('#full_name').val().trim();
            const email = $('#email').val().trim();
            const password = $('#password').val().trim();
            const phoneNumber = $('#phone_number').val().trim();

            if (roleId === '') {
                $('#role_id_error').removeClass('hidden');
                isValid = false;
            }
            if (fullName === '') {
                $('#full_name_error').removeClass('hidden');
                isValid = false;
            }
            if (email === '' || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                $('#email_error').removeClass('hidden');
                isValid = false;
            }
            if (password === '') {
                $('#password_error').removeClass('hidden');
                isValid = false;
            }
            if (phoneNumber === '' || isNaN(phoneNumber)) {
                $('#phone_number_error').removeClass('hidden');
                isValid = false;
            }

            if (!isValid) {
                return;
            }

            const userData = {
                role_id: roleId,
                full_name: fullName,
                email: email,
                password: password,
                phone_number: phoneNumber,
                action: 'createUser'
            };

            $.ajax({
                url: '../../controllers/admin/UserController.php',
                type: 'POST',
                contentType: 'application/json',
                data: JSON.stringify(userData),
                success: function(response) {
                    if (response.success === 1) {
                        window.location.href = "../admin/dashboard.php?view_users";
                    } else {
                        alert("Failed to create user. " + response.message);
                    }
                },
                error: function() {
                    alert("An error occurred while creating the user.");
                }
            });
        });
    });
</script>
