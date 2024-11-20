<?php
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}
?>
<div class="p-6 bg-white shadow-md rounded-lg mt-3 w-1/2">
    <h2 class="text-2xl font-bold text-center mb-6">Create Order</h2>

    <form id="createOrderForm">
        <div class="mb-4">
            <label class="block text-gray-700">Select User</label>
            <select id="user" class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-teal-400 cursor-pointer" required>
                <option value="">Select User</option>
            </select>
            <span id="user_error" class="text-red-500 text-sm hidden">Please select a user.</span>
        </div>

        <div class="mb-4">
            <label class="block text-gray-700">Shipping Address</label>
            <select id="shipping_address" class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-teal-400 cursor-pointer" required>
                <option value="">Select Shipping Address</option>
            </select>
            <span id="shipping_address_error" class="text-red-500 text-sm hidden">Please select a shipping address.</span>
        </div>

        <div class="mb-4">
            <label class="block text-gray-700">Select Products</label>
            <div class="flex flex-wrap items-center gap-1 border rounded-md p-2">
                <div id="selectedProducts" class="flex flex-wrap gap-1"></div>    
                <select id="products_dropdown" class="p-1 border rounded-full focus:outline-none cursor-pointer" required>
                    <option value="">Select Product</option>
                </select>
            </div>
            <span id="products_error" class="text-red-500 text-sm hidden">Please select at least one product.</span>
        </div>

        <div class="mb-4">
            <label class="block text-gray-700">Total Amount</label>
            <input type="text" id="total_amount" class="w-full px-4 py-2 border rounded-md" value="0.00" disabled>
            <span id="amount_error" class="text-red-500 text-sm hidden">Invalid amount, remove and select products again.</span>
        </div>

        <button type="button" id="createOrderBtn" class="w-full bg-teal-500 hover:bg-teal-700 text-white font-bold py-2 rounded-md">Create Order</button>
    </form>
</div>

<script>
    const selectedProducts = [];
    let totalAmount = 0;

    function removeProduct(productId, productName, productPrice) {
        const index = selectedProducts.indexOf(productId);
        if (index > -1) {
            selectedProducts.splice(index, 1);
            totalAmount -= productPrice;
            $('#total_amount').val(totalAmount.toFixed(2));
            $('#selectedProducts').find(`span:contains('${productName}')`).parent().remove();
        }
    };

    $(document).ready(function() {
        $.ajax({
            url: '../../controllers/admin/UserController.php',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({
                role_id: '2',
                action: 'listUsers'
            }),
            success: function(response) {
                if (response.success === 1) {
                    const userSelect = $('#user');
                    response.data.forEach(user => {
                        userSelect.append(`<option value="${user.id}">${user.full_name}</option>`);
                    });
                } else {
                    alert("Failed to load users.");
                }
            },
            error: function() {
                alert("An error occurred while fetching users.");
            }
        });

        $.ajax({
            url: '../../controllers/admin/ProductController.php',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({
                action: 'getProducts'
            }),
            success: function(response) {
                if (response.success === 1) {
                    const productSelect = $('#products_dropdown');
                    response.data.forEach(product => {
                        const productOption = `<option value="${product.id}" data-price="${product.price}" data-name="${product.product_name}" ${!product.stock_quantity > 0 ? 'disabled' : ''}>${product.product_name} - ${product.price} INR (${product.stock_quantity} in stock)</option>`;
                        productSelect.append(productOption);
                    });
                } else {
                    alert("Failed to load products.");
                }
            },
            error: function() {
                alert("An error occurred while fetching products.");
            }
        });

        $('#user').change(function() {
            const userId = $(this).val();
            if (userId) {
                $.ajax({
                    url: '../../controllers/admin/UserController.php',
                    type: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify({
                        action: 'getUserAddresses',
                        user_id: userId
                    }),
                    success: function(response) {
                        if (response.success === 1) {
                            const addressSelect = $('#shipping_address');
                            addressSelect.empty().append('<option value="">Select Shipping Address</option>');
                            response.data.forEach(address => {
                                addressSelect.append(`<option value="${address.id}">${address.full_address}, ${address.city}, ${address.state}, ${address.zip_code}</option>`);
                            });
                        } else {
                            alert("Failed to load shipping addresses.");
                        }
                    },
                    error: function() {
                        alert("An error occurred while fetching shipping addresses.");
                    }
                });
            } else {
                $('#shipping_address').empty().append('<option value="">Select Shipping Address</option>');
            }
        });

        $('#products_dropdown').change(function() {
            const selectedOption = $(this).find('option:selected');
            const productId = selectedOption.val();
            const productName = selectedOption.data('name');
            const productPrice = parseFloat(selectedOption.data('price'));

            if (productId && !selectedProducts.includes(productId)) {
                selectedProducts.push(productId);
                totalAmount += productPrice;
                $('#total_amount').val(totalAmount.toFixed(2));

                $('#selectedProducts').append(`
                    <div class="flex items-center justify-center bg-slate-300 rounded-full py-1 px-3">
                        <span class="text-slate-800">${productName}</span>
                        <button class="ml-2 text-slate-800" onclick="removeProduct('${productId}', '${productName}', ${productPrice})"><i class="fa fa-times"></i></button>
                    </div>
                `);
            }
            $(this).val('');
        });

        $('#createOrderBtn').click(function() {
            $('.text-red-500').addClass('hidden');

            let isValid = true;

            const userId = $('#user').val();
            const shippingAddressId = $('#shipping_address').val();
            const productIds = selectedProducts;

            if (!userId) {
                $('#user_error').removeClass('hidden');
                isValid = false;
            }
            if (!shippingAddressId) {
                $('#shipping_address_error').removeClass('hidden');
                isValid = false;
            }
            if (!productIds.length) {
                $('#products_error').removeClass('hidden');
                isValid = false;
            }
            if(!parseFloat(totalAmount) > 0) {
                $('#amount_error').removeClass('hidden');
                isValid = false;
            }

            if (!isValid) {
                return;
            }

            const order_number = Math.floor(10000000 + Math.random() * 90000000).toString();

            const orderData = {
                order_number: order_number,
                user_id: userId,
                shipping_address_id: shippingAddressId,
                product_ids: productIds,
                net_amount: totalAmount,
                action: 'createOrder'
            };

            $.ajax({
                url: '../../controllers/admin/OrdersController.php',
                type: 'POST',
                contentType: 'application/json',
                data: JSON.stringify(orderData),
                success: function(response) {
                    if (response.success === 1) {
                        alert("Order created successfully.");
                        window.location.href = "../admin/dashboard.php?view_orders";
                    } else {
                        alert("Failed to create order. " + response.message);
                    }
                },
                error: function() {
                    alert("An error occurred while creating the order.");
                }
            });
        });
    });
</script>