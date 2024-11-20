<?php
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}
?>
<div class="p-6 bg-white shadow-md rounded-lg mt-3 w-1/2">
    <h2 class="text-2xl font-bold text-center mb-6">Create Product</h2>

    <form id="createProductForm" enctype="multipart/form-data">
        <div class="mb-4">
            <label class="block text-gray-700">Product Name</label>
            <input type="text" id="product_name" class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-teal-400" required>
            <span id="product_name_error" class="text-red-500 text-sm hidden">Product name is required.</span>
        </div>

        <div class="mb-4">
            <label class="block text-gray-700">Category</label>
            <select id="category" class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-teal-400" required>
                <option value="">Select Category</option>
            </select>
            <span id="category_error" class="text-red-500 text-sm hidden">Please select a category.</span>
        </div>

        <div class="mb-4">
            <label class="block text-gray-700">Description</label>
            <textarea id="description" rows="3" class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-teal-400 resize-none" required></textarea>
            <span id="description_error" class="text-red-500 text-sm hidden">Description is required.</span>
        </div>

        <div class="mb-4">
            <label class="block text-gray-700">Price</label>
            <input type="text" id="price" class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-teal-400" required>
            <span id="price_error" class="text-red-500 text-sm hidden">Please enter a valid price.</span>
        </div>

        <div class="mb-4">
            <label class="block text-gray-700">Product Media</label>
            <input type="file" id="product_media" name="product_media[]" class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-teal-400" multiple>
            <span id="media_error" class="text-red-500 text-sm hidden">Please upload at least one file.</span>
        </div>


        <div class="mb-4">
            <label class="block text-gray-700">Stock Quantity</label>
            <input type="number" id="stock_quantity" class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-teal-400" required>
            <span id="stock_quantity_error" class="text-red-500 text-sm hidden">Stock quantity is required and must be a number.</span>
        </div>

        <button type="button" id="createProductBtn" class="w-full bg-teal-500 hover:bg-teal-700 text-white font-bold py-2 rounded-md">Create Product</button>
    </form>
</div>

<script>
    $(document).ready(function() {
        $.ajax({
            url: '../../controllers/admin/CategoryController.php',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({
                action: 'getCategories'
            }),
            success: function(response) {
                if (response.success === 1) {
                    const categorySelect = $('#category');
                    response.data.forEach(category => {
                        categorySelect.append(`<option value="${category.id}">${category.category_name}</option>`);
                    });
                } else {
                    alert("Failed to load categories.");
                }
            },
            error: function() {
                alert("An error occurred while fetching categories.");
            }
        });

        $('#createProductBtn').click(function() {
            $('.text-red-500').addClass('hidden');

            let isValid = true;

            const productName = $('#product_name').val().trim();
            const categoryId = $('#category').val();
            const description = $('#description').val().trim();
            const price = $('#price').val().trim();
            const stockQuantity = $('#stock_quantity').val().trim();
            const productMedia = $('#product_media')[0].files;
            const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'audio/mpeg', 'audio/wav', 'video/mp4'];
            const maxSize = 5 * 1024 * 1024;

            if (productName === '') {
                $('#product_name_error').removeClass('hidden');
                isValid = false;
            }
            if (categoryId === '') {
                $('#category_error').removeClass('hidden');
                isValid = false;
            }
            if (description === '') {
                $('#description_error').removeClass('hidden');
                isValid = false;
            }
            if (price === '' || isNaN(price) || parseFloat(price) <= 0) {
                $('#price_error').removeClass('hidden').text('Please enter a valid price greater than 0.');
                isValid = false;
            }
            if (stockQuantity === '' || isNaN(stockQuantity) || parseInt(stockQuantity) < 0) {
                $('#stock_quantity_error').removeClass('hidden').text('Stock quantity is required and must be a non-negative number.');
                isValid = false;
            }
            if (productMedia.length === 0) {
                $('#media_error').removeClass('hidden');
                isValid = false;
            }

            for (let i = 0; i < productMedia.length; i++) {
                const file = productMedia[i];
                if (!allowedTypes.includes(file.type)) {
                    isValid = false;
                    $('#media_error').text('Invalid file type. Allowed types: images, audio, video.').removeClass('hidden');
                    break;
                }
                if (file.size > maxSize) {
                    isValid = false;
                    $('#media_error').text('File size exceeds 5 MB limit.').removeClass('hidden');
                    break;
                }
            }

            if (!isValid) {
                return;
            }

            const productData = new FormData();
            productData.append('product_name', productName);
            productData.append('category_id', categoryId);
            productData.append('description', description);
            productData.append('price', price);
            productData.append('stock_quantity', stockQuantity);
            for (let i = 0; i < productMedia.length; i++) {
                productData.append('product_media[]', productMedia[i]);
            }
            productData.append('action', 'createProduct');

            $.ajax({
                url: '../../controllers/admin/ProductController.php',
                type: 'POST',
                processData: false,
                contentType: false,
                data: productData,
                success: function(response) {
                    if (response.success === 1) {
                        if(response.message) alert(response.message);
                        window.location.href = "../admin/dashboard.php?view_products";
                    } else {
                        alert("Failed to create product. " + response.message);
                    }
                },
                error: function() {
                    alert("An error occurred while creating the product.");
                }
            });
        });
    });
</script>