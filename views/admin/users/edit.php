<?php
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = isset($_GET['edit_user']) ? $_GET['edit_user'] : null;
if ($user_id === null) {
    header("Location: ../admin/dashboard.php?view_users");
    exit();
}
?>

<div class="p-6 bg-white shadow-md rounded-lg mt-3 w-1/2">
    <h2 class="text-2xl font-bold text-center mb-6">Edit User</h2>

    <form id="editUserForm">
        <div class="mb-4">
            <label class="block text-gray-700">Full Name</label>
            <input type="text" id="full_name" value="" class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-teal-400">
            <span id="full_name_error" class="text-red-500 text-sm hidden">Full name is required.</span>
        </div>

        <div class="mb-4">
            <label class="block text-gray-700">Email</label>
            <input type="email" id="email" value="" class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-teal-400">
            <span id="email_error" class="text-red-500 text-sm hidden">Valid email is required.</span>
        </div>

        <div class="mb-4">
            <label class="block text-gray-700">Phone Number</label>
            <input type="text" id="phone_number" value="" class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-teal-400">
            <span id="phone_number_error" class="text-red-500 text-sm hidden">Phone number is required.</span>
        </div>

        <div class="mb-4">
            <label class="block text-gray-700">Role</label>
            <select id="role_id" class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-teal-400">
                <option value="">Select Role</option>
            </select>
            <span id="role_id_error" class="text-red-500 text-sm hidden">Please select a role.</span>
        </div>

        <button type="button" id="editUserBtn" class="w-full bg-teal-500 hover:bg-teal-700 text-white font-bold py-2 rounded-md">Update User</button>
    </form>
</div>

<script>
    $(document).ready(function() {
        const userId = <?php echo $user_id; ?>;

        const disableRole = (userId == <?php echo $_SESSION['user_id']?>);
        if(disableRole == true){
            $('#role_id').prop('disabled', true);
        }

        $.ajax({
            url: '../../controllers/admin/UserController.php',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({ action: 'getUserDetails', user_id: userId }),
            success: function(response) {
                if (response.success === 1) {
                    const userDetails = response.data;
                    $('#full_name').val(userDetails.full_name);
                    $('#email').val(userDetails.email);
                    $('#phone_number').val(userDetails.phone_number);
                    $('#role_id').val(userDetails.role_id);

                    $.ajax({
                        url: '../../controllers/admin/UserController.php',
                        type: 'POST',
                        contentType: 'application/json',
                        data: JSON.stringify({ action: 'getRoles' }),
                        success: function(response) {
                            if (response.success === 1) {
                                const roleSelect = $('#role_id');
                                response.data.forEach(role => {
                                    const selected = (role.id == userDetails.role_id) ? 'selected' : '';
                                    roleSelect.append(`<option value="${role.id}" ${selected}>${role.role_name}</option>`);
                                });
                            } else {
                                alert("Failed to load roles.");
                            }
                        },
                        error: function() {
                            alert("An error occurred while fetching roles.");
                        }
                    });
                } else {
                    alert("Failed to fetch user details.");
                }
            },
            error: function() {
                alert("An error occurred while fetching user details.");
            }
        });

        $('#editUserBtn').click(function() {
            $('.text-red-500').addClass('hidden');

            let isValid = true;

            const fullName = $('#full_name').val().trim();
            const email = $('#email').val().trim();
            const phoneNumber = $('#phone_number').val().trim();
            const roleId = $('#role_id').val();

            if (fullName === '') {
                $('#full_name_error').removeClass('hidden');
                isValid = false;
            }
            if (email === '' || !/^\S+@\S+\.\S+$/.test(email)) {
                $('#email_error').removeClass('hidden');
                isValid = false;
            }
            if (phoneNumber === '') {
                $('#phone_number_error').removeClass('hidden');
                isValid = false;
            }

            if (!isValid) {
                return;
            }

            const userData = {
                user_id: userId,
                full_name: fullName,
                email: email,
                phone_number: phoneNumber,
                role_id: roleId,
                action: 'updateUser'
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
                        alert("Failed to update user. " + response.message);
                    }
                },
                error: function() {
                    alert("An error occurred while updating the user.");
                }
            });
        });
    });
</script>
