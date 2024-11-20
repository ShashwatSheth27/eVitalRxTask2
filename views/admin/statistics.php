<?php
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}
?>

<div class="container mx-auto px-4 py-6">
    <div class="flex items-center justify-between">
        <h1 class="text-3xl font-bold text-gray-800">Dashboard</h1>
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mt-6">
        <a href="dashboard.php?view_products" class="block">
            <div class="bg-blue-500 text-white rounded-lg shadow-md p-6 hover:shadow-lg transition duration-300">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-blue-700 rounded-full flex items-center justify-center">
                        <i class="fa fa-cube fa-lg"></i>
                    </div>
                    <div class="ml-4">
                        <h2 class="text-xl font-semibold" id="productsCount">0</h2>
                        <p>Products</p>
                    </div>
                </div>
            </div>
        </a>

        <a href="dashboard.php?view_users" class="block">
            <div class="bg-green-500 text-white rounded-lg shadow-md p-6 hover:shadow-lg transition duration-300">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-green-700 rounded-full flex items-center justify-center">
                        <i class="fa fa-users fa-lg"></i>
                    </div>
                    <div class="ml-4">
                        <h2 class="text-xl font-semibold" id="customersCount">0</h2>
                        <p>Users</p>
                    </div>
                </div>
            </div>
        </a>

        <a href="dashboard.php?view_categories" class="block">
            <div class="bg-yellow-500 text-white rounded-lg shadow-md p-6 hover:shadow-lg transition duration-300">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-yellow-700 rounded-full flex items-center justify-center">
                        <i class="fa fa-list fa-lg"></i>
                    </div>
                    <div class="ml-4">
                        <h2 class="text-xl font-semibold" id="categoriesCount">0</h2>
                        <p>Categories</p>
                    </div>
                </div>
            </div>
        </a>

        <a href="dashboard.php?view_orders" class="block">
            <div class="bg-red-500 text-white rounded-lg shadow-md p-6 hover:shadow-lg transition duration-300">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-red-700 rounded-full flex items-center justify-center">
                        <i class="fa fa-shopping-cart fa-lg"></i>
                    </div>
                    <div class="ml-4">
                        <h2 class="text-xl font-semibold" id="ordersCount">0</h2>
                        <p>Orders</p>
                    </div>
                </div>
            </div>
        </a>
    </div>


    <div class="mt-8 bg-white rounded-lg shadow-md p-6">
        <h2 class="text-xl font-bold text-gray-800 mb-4">New Orders</h2>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr>
                        <th class="border-b p-2">Order Id</th>
                        <th class="border-b p-2">Order number</th>
                        <th class="border-b p-2">Customer name</th>
                        <th class="border-b p-2">Date created</th>
                        <th class="border-b p-2">Status</th>
                    </tr>
                </thead>
                <tbody id="topOrders">
                    <!-- Dynamic rows will be inserted here -->
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        $.ajax({
            url: '../../controllers/admin/AdminController.php',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({
                action: 'fetchCounts'
            }),
            success: function(response) {
                if (response.success == 1 && response.data) {
                    $('#productsCount').text(response.data.products);
                    $('#customersCount').text(response.data.users);
                    $('#categoriesCount').text(response.data.categories);
                    $('#ordersCount').text(response.data.orders);
                }
            },
            error: function(error) {
                console.error('Error fetching dashboard data:', error);
            }
        });

        $.ajax({
            url: '../../controllers/admin/OrdersController.php',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({
                action: 'fetchOrders',
                limit: 5
            }),
            success: function(response) {
                const topOrdersTable = $('#topOrders');
                if (response.success == 1 && response.data && response.data.length > 0) {
                    response.data.forEach(function(order) {
                        const row = `
                                <tr>
                                <td class="border-b p-2">${order.id}</td>
                                <td class="border-b p-2">${order.order_number}</td>
                                <td class="border-b p-2">${order.user_name}</td>
                                <td class="border-b p-2">${new Date(order.created_at).toLocaleString()}</td>
                                <td class="border-b p-2">${order.order_status}</td>
                                </tr>
                            `;
                        topOrdersTable.append(row);
                    });
                } else {
                    topOrdersTable.append(`<tr><td class="border-b p-2 text-center" colspan="5">No records found.</td></tr>`);
                }
            },
            error: function(error) {
                console.error('Error fetching dashboard data:', error);
            }
        });
    });
</script>