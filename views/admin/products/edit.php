<?php
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

$product_id = isset($_GET['edit_product']) ? $_GET['edit_product'] : null;
if ($product_id === null) {
    header("Location: ../admin/dashboard.php?view_products");
    exit();
}
?>

<div class="p-6 bg-white shadow-md rounded-lg mt-3 w-1/2">
    <h2 class="text-2xl font-bold text-center mb-6">Edit Product</h2>

    <form id="editProductForm">
        <div class="mb-4">
            <label class="block text-gray-700">Product Name</label>
            <input type="text" id="product_name" value="<?php echo htmlspecialchars($productDetails['product_name']); ?>" class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-teal-400" required>
            <span id="product_name_error" class="text-red-500 text-sm hidden">Product name is required.</span>
        </div>

        <div class="mb-4">
            <label class="block text-gray-700">Category</label>
            <select id="category" class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-teal-400" required>
                <option value="">Select Category</option>
                <!-- Categories will be loaded here -->
            </select>
            <span id="category_error" class="text-red-500 text-sm hidden">Please select a category.</span>
        </div>

        <div class="mb-4">
            <label class="block text-gray-700">Description</label>
            <textarea id="description" rows="3" class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-teal-400 resize-none" required><?php echo htmlspecialchars($productDetails['description']); ?></textarea>
            <span id="description_error" class="text-red-500 text-sm hidden">Description is required.</span>
        </div>

        <div class="mb-4">
            <label class="block text-gray-700">Price</label>
            <input type="text" id="price" value="<?php echo htmlspecialchars($productDetails['price']); ?>" class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-teal-400" required>
            <span id="price_error" class="text-red-500 text-sm hidden">Please enter a valid price.</span>
        </div>

        <div class="mb-4">
            <label class="block text-gray-700">Stock Quantity</label>
            <input type="number" id="stock_quantity" value="<?php echo htmlspecialchars($productDetails['stock_quantity']); ?>" class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-teal-400" required>
            <span id="stock_quantity_error" class="text-red-500 text-sm hidden">Stock quantity is required and must be a number.</span>
        </div>

        <button type="button" id="editProductBtn" class="w-full bg-teal-500 hover:bg-teal-700 text-white font-bold py-2 rounded-md">Update Product</button>
    </form>
</div>

<script>
    $(document).ready(function() {
        const productId = <?php echo $product_id; ?>;
        
        $.ajax({
            url: '../../controllers/admin/ProductController.php',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({ action: 'getProductDetails', product_id: productId }),
            success: function(response) {
                if (response.success === 1) {
                    const productDetails = response.data;
                    $('#product_name').val(productDetails.product_name);
                    $('#description').val(productDetails.description);
                    $('#price').val(productDetails.price);
                    $('#stock_quantity').val(productDetails.stock_quantity);
                    $('#category').val(productDetails.category_id);
                    $.ajax({
                        url: '../../controllers/admin/CategoryController.php',
                        type: 'POST',
                        contentType: 'application/json',
                        data: JSON.stringify({ action: 'getCategories' }),
                        success: function(response) {
                            if (response.success === 1) {
                                const categorySelect = $('#category');
                                response.data.forEach(category => {
                                    const selected = ((category.id == productDetails.category_id) ? 'selected' : '');
                                    categorySelect.append(`<option value="${category.id}" ${selected}>${category.category_name}</option>`);
                                });
                            } else {
                                alert("Failed to load categories.");
                            }
                        },
                        error: function() {
                            alert("An error occurred while fetching categories.");
                        }
                    });
                } else {
                    alert("Failed to fetch product details.");
                }
            },
            error: function() {
                alert("An error occurred while fetching product details.");
            }
        });

        $('#editProductBtn').click(function() {
            $('.text-red-500').addClass('hidden');

            let isValid = true;

            const productName = $('#product_name').val().trim();
            const categoryId = $('#category').val();
            const description = $('#description').val().trim();
            const price = $('#price').val().trim();
            const stockQuantity = $('#stock_quantity').val().trim();

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

            if (!isValid) {
                return;
            }

            const productData = {
                product_id: productId,
                product_name: productName,
                category_id: categoryId,
                description: description,
                price: price,
                stock_quantity: stockQuantity,
                action: 'updateProduct'
            };

            $.ajax({
                url: '../../controllers/admin/ProductController.php',
                type: 'POST',
                contentType: 'application/json',
                data: JSON.stringify(productData),
                success: function(response) {
                    if (response.success === 1) {
                        window.location.href = "../admin/dashboard.php?view_products";
                    } else {
                        alert("Failed to update product. " + response.message);
                    }
                },
                error: function() {
                    alert("An error occurred while updating the product.");
                }
            });
        });
    });
</script>
