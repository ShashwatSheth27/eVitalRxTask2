<?php
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

$category_id = isset($_GET['edit_category']) ? $_GET['edit_category'] : null;
if ($category_id === null) {
    header("Location: ../admin/dashboard.php?view_categories");
    exit();
}
?>

<div class="p-6 bg-white shadow-md rounded-lg mt-3 w-1/2">
    <h2 class="text-2xl font-bold text-center mb-6">Edit Category</h2>

    <form id="editCategoryForm">
        <div class="mb-4">
            <label class="block text-gray-700">Category Name</label>
            <input type="text" id="category_name" value="<?php echo htmlspecialchars($categoryDetails['category_name']); ?>" class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-teal-400">
            <span id="category_name_error" class="text-red-500 text-sm hidden">Category name is required.</span>
        </div>

        <div class="mb-4">
            <label class="block text-gray-700">Description (optional)</label>
            <textarea id="description" rows="3" class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-teal-400 resize-none"><?php echo htmlspecialchars($categoryDetails['description']); ?></textarea>
            <span id="description_error" class="text-red-500 text-sm hidden">Invalid Description.</span>
        </div>

        <button type="button" id="editCategoryBtn" class="w-full bg-teal-500 hover:bg-teal-700 text-white font-bold py-2 rounded-md">Update Category</button>
    </form>
</div>

<script>
    $(document).ready(function() {
        const categoryId = <?php echo $category_id; ?>;
        
        $.ajax({
            url: '../../controllers/admin/CategoryController.php',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({ action: 'getCategoryDetails', category_id: categoryId }),
            success: function(response) {
                if (response.success === 1) {
                    const categoryDetails = response.data;
                    $('#category_name').val(categoryDetails.category_name);
                    $('#description').val(categoryDetails.description);
                } else {
                    alert("Failed to fetch category details.");
                }
            },
            error: function() {
                alert("An error occurred while fetching category details.");
            }
        });

        $('#editCategoryBtn').click(function() {
            $('.text-red-500').addClass('hidden');

            let isValid = true;

            const categoryName = $('#category_name').val().trim();
            const description = $('#description').val().trim();

            if (categoryName === '') {
                $('#category_name_error').removeClass('hidden');
                isValid = false;
            }
            if (categoryId === '') {
                $('#category_error').removeClass('hidden');
                isValid = false;
            }

            if (!isValid) {
                return;
            }

            const categoryData = {
                category_id: categoryId,
                category_name: categoryName,
                description: description,
                action: 'updateCategory'
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
                        alert("Failed to update category. " + response.message);
                    }
                },
                error: function() {
                    alert("An error occurred while updating the category.");
                }
            });
        });
    });
</script>
