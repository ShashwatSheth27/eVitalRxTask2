<div class="container p-5">
    <h3 class="text-2xl font-semibold text-gray-700 mb-4">Categories List</h3>

    <div class="loader" id="loader"></div>

    <div class="overflow-x-auto bg-white shadow-md rounded-lg">
        <table class="min-w-full bg-white divide-y divide-gray-200" id="categoryTable">
            <thead class="bg-yellow-500 text-white">
                <tr>
                    <th class="py-3 px-6 text-left text-xs font-medium uppercase tracking-wider">Category Id</th>
                    <th class="py-3 px-6 text-left text-xs font-medium uppercase tracking-wider">Category Name</th>
                    <th class="py-3 px-6 text-left text-xs font-medium uppercase tracking-wider">Category Description</th>
                    <th class="py-3 px-6 text-left text-xs font-medium uppercase tracking-wider">Products</th>
                    <th class="py-3 px-6 text-left text-xs font-medium uppercase tracking-wider">Actions</th>
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
    let deleteCategoryId = null;

    function fetchCategories() {
        $('#loader').show();

        $.ajax({
            url: '../../controllers/admin/CategoryController.php',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({
                action: 'getCategories'
            }),
            success: function(response) {
                $('#loader').hide();

                let categoryTable = $('#categoryTable tbody');
                categoryTable.empty();

                if (response.success === 1 && response.data.length > 0) {

                    response.data.forEach(function(category) {
                        let deleteOption = '';
                        if(category.number_of_products == 0){
                            deleteOption = `
                                <button class="deleteBtn text-red-500 hover:text-red-700" data-id="${category.id}">
                                    <i class="fa fa-trash"></i>
                                </button>
                            `;
                        }
                        let categoryRow = `
                                <tr class="hover:bg-gray-100">
                                    <td class="py-3 px-6 text-gray-700">${category.id}</td>
                                    <td class="py-3 px-6 text-gray-700">${category.category_name}</td>
                                    <td class="py-3 px-6 text-gray-700">${category.description || ''}</td>
                                    <td class="py-3 px-6 text-gray-700">${category.number_of_products}</td>
                                    <td class="py-3 px-6 text-center flex gap-3">
                                        ${deleteOption}
                                        <a href="dashboard.php?edit_category=${category.id}" class="text-blue-500 hover:text-blue-700">
                                            <i class="fa fa-edit"></i>
                                        </a>
                                    </td>
                                </tr>
                            `;
                        categoryTable.append(categoryRow);
                    });

                    $('.deleteBtn').on('click', function() {
                        deleteCategoryId = $(this).data('id');
                        $('#deleteConfirmationModal').fadeIn();
                    });

                } else {
                    let noDataRow = `
                        <tr>
                            <td colspan="8" class="py-3 px-6 text-center text-gray-700">No categories found.</td>
                        </tr>
                    `;
                    categoryTable.append(noDataRow);
                }
            },
            error: function(xhr, status, error) {
                $('#loader').hide();
                alert('An error occurred while fetching categoryies. Please try again.');
            }
        });
    }

    $(document).ready(function() {
        fetchCategories();

        $('#cancelDelete').on('click', function() {
            $('#deleteConfirmationModal').fadeOut();
            deleteCategoryId = null;
        });

        $('#confirmDelete').on('click', function() {
            if (deleteCategoryId) {
                $.ajax({
                    url: '../../controllers/admin/CategoryController.php',
                    type: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify({
                        action: 'deleteCategory',
                        category_id: deleteCategoryId
                    }),
                    success: function(response) {
                        if (response.success === 1) {
                            $('#deleteConfirmationModal').fadeOut();
                            fetchCategories();
                        } else {
                            alert(response.message || 'Failed to delete the category.');
                        }
                    },
                    error: function(xhr, status, error) {
                        alert('An error occurred while deleting the category.');
                    }
                });
            }
        });
    });
</script>