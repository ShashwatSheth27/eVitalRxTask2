<?php
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

$order_id = isset($_GET['edit_order']) ? $_GET['edit_order'] : null;
if ($order_id == null) {
    header("Location: ../admin/dashboard.php?view_orders");
    exit();
}
?>
<div class="p-6 bg-white shadow-md rounded-lg mt-3 w-1/2">
    <h2 class="text-2xl font-bold text-center mb-6">Edit Order</h2>

    <form id="editOrderForm">
        <div class="mb-4">
            <label class="block text-gray-700">Select User</label>
            <input type="text" id="user" class="w-full px-4 py-2 border rounded-md" disabled>
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
                <select id="products_dropdown" class="p-1 border rounded-full focus:outline-none cursor-pointer">
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

        <button type="button" id="updateOrderBtn" class="w-full bg-teal-500 hover:bg-teal-700 text-white font-bold py-2 rounded-md">Update Order</button>
    </form>
</div>

<script>
    const selectedProducts = [];
    const removedProducts = [];
    let totalAmount = 0;
    let editProductsDisabled = false;

    function fetchUserAddresses(userId) {
        return $.ajax({
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
                        addressSelect.append(`
                            <option value="${address.id}">
                                ${address.full_address}, ${address.city}, ${address.state}, ${address.zip_code}
                            </option>
                        `);
                    });
                } else {
                    alert("Failed to load shipping addresses.");
                }
            },
            error: function() {
                alert("An error occurred while fetching user addresses.");
            }
        });
    }

    function fetchProducts() {
        return $.ajax({
            url: '../../controllers/admin/ProductController.php',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({
                action: 'getProducts'
            }),
            success: function(response) {
                if (response.success === 1) {
                    const productSelect = $('#products_dropdown');
                    Object.values(response.data).forEach(product => {
                        productSelect.append(`
                            <option value="${product.id}" data-price="${product.price}" data-name="${product.product_name}">
                                ${product.product_name} - ${product.price} INR (${product.stock_quantity} in stock)
                            </option>
                        `);
                    });
                } else {
                    alert("Failed to load products.");
                }
            },
            error: function() {
                alert("An error occurred while fetching products.");
            }
        });
    }

    function addProduct(productDetails, updateAmount = false) {
        if (!selectedProducts.includes(productDetails['productId'])) {
            selectedProducts.push(String(productDetails['productId']));
            const index = removedProducts.indexOf(String(productDetails['productId']));
            if(index > -1) removedProducts.splice(index, 1);
            if (updateAmount) {
                totalAmount += productDetails['productPrice'];
                $('#total_amount').val(totalAmount.toFixed(2));
            }
            $('#selectedProducts').append(`
                <div class="flex items-center justify-center bg-slate-300 rounded-full py-1 px-3">
                    <span class="text-slate-800">${productDetails['productName']}</span>
                    <button class="ml-2 text-slate-800" onclick="removeProduct(event, '${productDetails['productId']}', '${productDetails['productName']}', ${productDetails['productPrice']})">
                        <i class="fa fa-times"></i>
                    </button>
                </div>
            `);
        }
    }

    function removeProduct(event, productId, productName, productPrice) {
        event.preventDefault();
        if(!editProductsDisabled) {
            const index = selectedProducts.indexOf(productId);
            if (index > -1) {
                if(!removedProducts.includes(productId)) removedProducts.push(productId);
                selectedProducts.splice(index, 1);
                totalAmount -= productPrice;
                $('#total_amount').val(totalAmount.toFixed(2));
                $('#selectedProducts').find(`span:contains('${productName}')`).parent().remove();
            }
        }
    };

    $(document).ready(function() {
        const orderId = <?= json_encode($order_id); ?>;
        $.ajax({
            url: '../../controllers/admin/OrdersController.php',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({
                action: 'fetchOrderById',
                order_id: orderId
            }),
            success: function(response) {
                if (response.success === 1) {
                    const order = response.data;
                    if (order.order_status != 'placed') {
                        alert('Order cannot be changed');
                        window.location.href = "../admin/dashboard.php?view_orders";
                        return;
                    }
                    $('#user').val(order.user_name);
                    totalAmount = parseFloat(order.net_amount);
                    $('#total_amount').val(totalAmount);
                    // fetch user address
                    fetchUserAddresses(order.user_id).then(() => {
                        $('#shipping_address').val(order.shipping_address_id);
                    });
                    // fetch all the products
                    fetchProducts().then(() => {
                        if (order.payment_status !== 'not paid') {
                            editProductsDisabled = true;
                            $('#products_dropdown').prop('disabled', true);
                        }
                        order.productIds.forEach(productId => {
                            const productOption = $(`#products_dropdown option[value="${productId}"]`);
                            if (productOption.length) {
                                const productName = productOption.data('name');
                                const productPrice = parseFloat(productOption.data('price'));
                                const productDetails = {
                                    'productId': productId,
                                    'productName': productName,
                                    'productPrice': productPrice
                                };
                                addProduct(productDetails);
                            }
                        });
                    });
                } else {
                    alert("Failed to load order details.");
                }
            },
            error: function() {
                alert("An error occurred while fetching order details.");
            }
        });

        $('#products_dropdown').change(function() {
            const selectedOption = $(this).find('option:selected');
            const productDetails = {
                'productId': selectedOption.val(),
                'productName': selectedOption.data('name'),
                'productPrice': parseFloat(selectedOption.data('price'))
            }
            addProduct(productDetails, true);
            $(this).val('');
        });

        $('#updateOrderBtn').click(function() {
            $('.text-red-500').addClass('hidden');

            let isValid = true;
            const shippingAddressId = $('#shipping_address').val();
            const productIds = selectedProducts;
            if (!shippingAddressId) {
                $('#shipping_address_error').removeClass('hidden');
                isValid = false;
            }
            if (!editProductsDisabled && !productIds.length) {
                $('#products_error').removeClass('hidden');
                isValid = false;
            }
            if (!editProductsDisabled && !parseFloat(totalAmount) > 0) {
                $('#amount_error').removeClass('hidden');
                isValid = false;
            }

            if (!isValid) {
                return;
            }

            const orderData = {
                order_id: orderId,
                shipping_address_id: shippingAddressId,
                action: 'updateOrder'
            };

            if(!editProductsDisabled) {
                orderData['product_ids'] = productIds;
                orderData['net_amount'] = totalAmount;
                if(removedProducts.length) orderData['remove_product_ids'] = removedProducts;
            }

            $.ajax({
                url: '../../controllers/admin/OrdersController.php',
                type: 'POST',
                contentType: 'application/json',
                data: JSON.stringify(orderData),
                success: function(response) {
                    if (response.success === 1) {
                        alert("Order updated successfully.");
                        window.location.href = "../admin/dashboard.php?view_orders";
                    } else {
                        alert("Failed to update order. " + response.message);
                    }
                },
                error: function() {
                    alert("An error occurred while updating the order.");
                }
            });
        });
    });
</script>