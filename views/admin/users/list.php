<div class="container p-5">
    <h3 class="text-2xl font-semibold text-gray-700 mb-4">User List</h3>

    <!-- Loader -->
    <div class="loader" id="loader"></div>

    <div class="overflow-x-auto bg-white shadow-md rounded-lg">
        <table class="min-w-full bg-white divide-y divide-gray-200" id="userTable">
            <thead class="bg-green-500 text-white">
                <tr>
                    <th class="py-3 px-6 text-left text-xs font-medium uppercase tracking-wider">User Id</th>
                    <th class="py-3 px-6 text-left text-xs font-medium uppercase tracking-wider">User Name</th>
                    <th class="py-3 px-6 text-left text-xs font-medium uppercase tracking-wider">User Email</th>
                    <th class="py-3 px-6 text-left text-xs font-medium uppercase tracking-wider">User Phone Number</th>
                    <th class="py-3 px-6 text-left text-xs font-medium uppercase tracking-wider">User Role</th>
                    <th class="py-3 px-6 text-center text-xs font-medium uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <!-- Dynamic rows will be appended here -->
            </tbody>
        </table>
    </div>
</div>

<?php require_once('../components/deleteConfirmationModal.php'); ?>

<script>
    let deleteUserId = null;
    const sessionUserId = <?php echo $_SESSION['user_id']?>;
    function fetchUsers() {
        $('#loader').show();

        $.ajax({
            url: '../../controllers/admin/UserController.php',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({
                action: 'listUsers'
            }),
            success: function(response) {
                $('#loader').hide();
                let userTable = $('#userTable tbody');
                userTable.empty();

                if (response.success === 1 && response.data.length > 0) {
                    response.data.forEach(function(user) {
                        let deleteButton = '';
                        if (sessionUserId !== user.id) {
                            deleteButton = `
                                <button class="deleteBtn text-red-500 hover:text-red-700" data-id="${user.id}">
                                    <i class="fa fa-trash"></i>
                                </button>
                            `;
                        }
                        let userRow = `
                                <tr class="hover:bg-gray-100">
                                    <td class="py-3 px-6 text-gray-700">${user.id}</td>
                                    <td class="py-3 px-6 text-gray-700">${user.full_name}</td>
                                    <td class="py-3 px-6 text-gray-700">${user.email}</td>
                                    <td class="py-3 px-6 text-gray-700">${user.phone_number}</td>
                                    <td class="py-3 px-6 text-gray-700">${user.role_name}</td>
                                    <td class="py-3 px-6 text-center flex gap-3">
                                        ${deleteButton}
                                        <a href="dashboard.php?edit_user=${user.id}" class="text-blue-500 hover:text-blue-700">
                                            <i class="fa fa-edit"></i>
                                        </a>
                                    </td>
                                </tr>
                            `;
                        userTable.append(userRow);
                    });

                    $('.deleteBtn').on('click', function() {
                        deleteUserId = $(this).data('id');
                        $('#deleteConfirmationModal').fadeIn();
                    });

                } else {
                    let noDataRow = `
                        <tr>
                            <td colspan="7" class="py-3 px-6 text-center text-gray-700">No users found.</td>
                        </tr>
                    `;
                    userTable.append(noDataRow);
                }
            },
            error: function(xhr, status, error) {
                $('#loader').hide();
                alert('An error occurred while fetching users. Please try again.');
            }
        });
    }

    $(document).ready(function() {
        fetchUsers();

        $('#cancelDelete').on('click', function() {
            $('#deleteConfirmationModal').fadeOut();
            deleteUserId = null;
        });

        $('#confirmDelete').on('click', function() {
            if (deleteUserId) {
                $.ajax({
                    url: '../../controllers/admin/UserController.php',
                    type: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify({
                        action: 'deleteUser',
                        user_id: deleteUserId
                    }),
                    success: function(response) {
                        if (response.success === 1) {
                            alert('User deleted successfully.');
                            $('#deleteConfirmationModal').fadeOut();
                            fetchUsers();
                        } else {
                            alert(response.message || 'Failed to delete the product.');
                        }
                    },
                    error: function(xhr, status, error) {
                        alert('An error occurred while deleting the product.');
                    }
                });
            }
        });
    });
</script>