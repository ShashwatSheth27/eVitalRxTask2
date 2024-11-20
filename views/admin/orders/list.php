<div class="container p-5">
    <h3 class="text-2xl font-semibold text-gray-700 mb-4">Order List</h3>

    <!-- Loader -->
    <div class="loader" id="loader"></div>

    <!-- Order Table -->
    <div class="overflow-x-auto bg-white shadow-md rounded-lg">
        <table class="min-w-full bg-white divide-y divide-gray-200" id="orderTable">
            <thead class="bg-red-500 text-white">
                <tr>
                    <th class="py-3 px-6 text-left text-xs font-medium uppercase tracking-wider">Order Id</th>
                    <th class="py-3 px-6 text-left text-xs font-medium uppercase tracking-wider">Order Number</th>
                    <th class="py-3 px-6 text-left text-xs font-medium uppercase tracking-wider">User name</th>
                    <th class="py-3 px-6 text-left text-xs font-medium uppercase tracking-wider">Shiping Address</th>
                    <th class="py-3 px-6 text-left text-xs font-medium uppercase tracking-wider">Shiping City</th>
                    <th class="py-3 px-6 text-left text-xs font-medium uppercase tracking-wider">Shiping State</th>
                    <th class="py-3 px-6 text-left text-xs font-medium uppercase tracking-wider">Shiping Zip Code</th>
                    <th class="py-3 px-6 text-left text-xs font-medium uppercase tracking-wider">Order Status</th>
                    <th class="py-3 px-6 text-left text-xs font-medium uppercase tracking-wider">Total Amount</th>
                    <th class="py-3 px-6 text-left text-xs font-medium uppercase tracking-wider">Payment Status</th>
                    <th class="py-3 px-6 text-left text-xs font-medium uppercase tracking-wider">Order Created</th>
                    <th class="py-3 px-6 text-left text-xs font-medium uppercase tracking-wider">Order Completed</th>
                    <th class="py-3 px-6 text-center text-xs font-medium uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <!-- Dynamic rows will be appended here -->
            </tbody>
        </table>
    </div>
</div>

<script>
    let deleteOrderId = null;

    function fetchOrders() {
        $('#loader').show();

        $.ajax({
            url: '../../controllers/admin/OrdersController.php',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({
                action: 'fetchOrders'
            }),
            success: function(response) {
                $('#loader').hide();

                let orderTable = $('#orderTable tbody');
                orderTable.empty();

                if (response.success === 1 && response.data.length > 0) {

                    response.data.forEach(function(order) {
                        let orderRow = `
                                <tr class="hover:bg-gray-100">
                                    <td class="py-3 px-6 text-gray-700">${order.id}</td>
                                    <td class="py-3 px-6 text-gray-700">${order.order_number}</td>
                                    <td class="py-3 px-6 text-gray-700">${order.user_name}</td>
                                    <td class="py-3 px-6 text-gray-700">${order.shipping_address}</td>
                                    <td class="py-3 px-6 text-gray-700">${order.shipping_city}</td>
                                    <td class="py-3 px-6 text-gray-700">${order.shipping_state}</td>
                                    <td class="py-3 px-6 text-gray-700">${order.shipping_zipcode}</td>
                                    <td class="py-3 px-6 text-gray-700">${order.order_status}</td>
                                    <td class="py-3 px-6 text-gray-700">${order.net_amount}</td>
                                    <td class="py-3 px-6 text-gray-700">${order.payment_status}</td>
                                    <td class="py-3 px-6 text-gray-700">${new Date(order.created_at).toLocaleString()}</td>
                                    <td class="py-3 px-6 text-gray-700">${order.delivered_at ? new Date(order.delivered_at).toLocaleString() : ''}</td>
                                    <td class="py-3 px-6 text-center flex gap-3">
                                        ${order.order_status === 'placed' ? `
                                            <a href="dashboard.php?edit_order=${order.id}" class="text-blue-500 hover:text-blue-700">
                                                <i class="fa fa-edit"></i>
                                            </a>
                                        ` : ''}
                                    </td>
                                </tr>
                            `;
                        orderTable.append(orderRow);
                    });
                } else {
                    let noDataRow = `
                        <tr>
                            <td colspan="13" class="py-3 px-6 text-center text-gray-700">No orders found.</td>
                        </tr>
                    `;
                    orderTable.append(noDataRow);
                }
            },
            error: function(xhr, status, error) {
                $('#loader').hide();
                alert('An error occurred while fetching orders. Please try again.');
            }
        });
    }

    $(document).ready(function() {
        fetchOrders();
    });
</script>