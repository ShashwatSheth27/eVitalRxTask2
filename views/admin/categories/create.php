<?php
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}
?>
<div class="p-6 bg-white shadow-md rounded-lg mt-3 w-1/2">
    <h2 class="text-2xl font-bold text-center mb-6">Create Category</h2>

    <form id="createCategoryForm">
        <div class="mb-4">
            <label class="block text-gray-700">Category Name</label>
            <input type="text" id="category_name" class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-teal-400" required>
            <span id="category_name_error" class="text-red-500 text-sm hidden">Category name is required.</span>
        </div>

        <div class="mb-4">
            <label class="block text-gray-700">Description (optional)</label>
            <textarea id="description" rows="3" class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-teal-400 resize-none" required></textarea>
            <span id="description_error" class="text-red-500 text-sm hidden">Invalid dscription text</span>
        </div>

        <button type="button" id="createCategoryBtn" class="w-full bg-teal-500 hover:bg-teal-700 text-white font-bold py-2 rounded-md">Create Category</button>
    </form>
</div>

<script>
    $(document).ready(function() {
        $('#createCategoryBtn').click(function() {
            $('.text-red-500').addClass('hidden');

            let isValid = true;

            const categoryName = $('#category_name').val().trim();
            const description = $('#description').val().trim();

            if (categoryName === '') {
                $('#category_name_error').removeClass('hidden');
                isValid = false;
            }

            if (!isValid) {
                return;
            }

            const categoryData = {
                category_name: categoryName,
                description: description,
                action: 'createCategory'
            };

            $.ajax({
                url: '../../controllers/admin/CategoryController.php',
                type: 'POST',
                contentType: 'application/json',
                data: JSON.stringify(categoryData),
                success: function(response) {
                    if (response.success === 1) {
                        window.location.href = "../admin/dashboard.php?view_categories";
                    } else {
                        alert("Failed to create category. " + response.message);
                    }
                },
                error: function() {
                    alert("An error occurred while creating the category.");
                }
            });
        });
    });
</script>
