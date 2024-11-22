<div class="container p-5">
    <h3 class="text-2xl font-semibold text-gray-700 mb-4">Product List</h3>

    <div class="loader" id="loader"></div>

    <div class="overflow-x-auto bg-white shadow-md rounded-lg">
        <table class="min-w-full bg-white divide-y divide-gray-200" id="productTable">
            <thead class="bg-blue-500 text-white">
                <tr>
                    <th class="py-3 px-6 text-left text-xs font-medium uppercase tracking-wider">Product Id</th>
                    <th class="py-3 px-6 text-left text-xs font-medium uppercase tracking-wider">Product Name</th>
                    <th class="py-3 px-6 text-left text-xs font-medium uppercase tracking-wider">Product Price</th>
                    <th class="py-3 px-6 text-left text-xs font-medium uppercase tracking-wider">Product Description</th>
                    <th class="py-3 px-6 text-left text-xs font-medium uppercase tracking-wider">Product Category</th>
                    <th class="py-3 px-6 text-left text-xs font-medium uppercase tracking-wider">Product Quantity</th>
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
    let deleteProductId = null;

    function fetchProducts() {
        $('#loader').show();

        $.ajax({
            url: '../../controllers/admin/ProductController.php',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({
                action: 'getProducts'
            }),
            success: function(response) {
                $('#loader').hide();

                let productTable = $('#productTable tbody');
                productTable.empty();

                if (response.success === 1) {

                    Object.values(response.data).forEach(function(product) {
                        let productRow = `
                                <tr class="hover:bg-gray-100">
                                    <td class="py-3 px-6 text-gray-700">${product.id}</td>
                                    <td class="py-3 px-6 text-gray-700">${product.product_name}</td>
                                    
                                    <td class="py-3 px-6 text-gray-700">${product.price}</td>
                                    <td class="py-3 px-6 text-gray-700">${product.description}</td>
                                    <td class="py-3 px-6 text-gray-700">${product.category_name}</td>
                                    <td class="py-3 px-6 text-gray-700">${product.stock_quantity}</td>
                                    <td class="py-3 px-6 text-center flex gap-3">
                                        <button class="deleteBtn text-red-500 hover:text-red-700" data-id="${product.id}">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                        <a href="dashboard.php?edit_product=${product.id}" class="text-blue-500 hover:text-blue-700">
                                            <i class="fa fa-edit"></i>
                                        </a>
                                    </td>
                                </tr>
                            `;
                        productTable.append(productRow);
                    });

                    $('.deleteBtn').on('click', function() {
                        deleteProductId = $(this).data('id');
                        $('#deleteConfirmationModal').fadeIn();
                    });

                } else {
                    let noDataRow = `
                        <tr>
                            <td colspan="8" class="py-3 px-6 text-center text-gray-700">No products found.</td>
                        </tr>
                    `;
                    productTable.append(noDataRow);
                }
            },
            error: function(xhr, status, error) {
                $('#loader').hide();
                alert('An error occurred while fetching products. Please try again.');
            }
        });
    }

    $(document).ready(function() {
        fetchProducts();

        $('#cancelDelete').on('click', function() {
            $('#deleteConfirmationModal').fadeOut();
            deleteProductId = null;
        });

        $('#confirmDelete').on('click', function() {
            if (deleteProductId) {
                $.ajax({
                    url: '../../controllers/admin/ProductController.php',
                    type: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify({
                        action: 'deleteProduct',
                        product_id: deleteProductId
                    }),
                    success: function(response) {
                        if (response.success === 1) {
                            alert('Product deleted successfully.');
                            $('#deleteConfirmationModal').fadeOut();
                            fetchProducts();
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