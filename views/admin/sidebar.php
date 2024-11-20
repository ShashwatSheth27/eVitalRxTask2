<div class="bg-gray-800 text-white min-h-screen max-w-xs w-1/6">

    <ul class="mt-4">
        <li>
            <a href="dashboard.php" class="flex items-center px-4 py-2 hover:bg-gray-700 rounded">
                <i class="fa fa-bar-chart mr-2"></i> Dashboard
            </a>
        </li>
        <li class="relative">
            <button class="flex items-center w-full px-4 py-2 text-left hover:bg-gray-700 rounded" data-toggle="collapse" data-target="#products">
                <i class="fa fa-cube mr-2"></i> Products <i class="fa fa-caret-down ml-auto"></i>
            </button>
            <ul id="products" class="hidden ml-4 space-y-1">
                <li>
                    <a href="dashboard.php?insert_product" class="block px-4 py-2 hover:bg-gray-700 rounded">Insert Product</a>
                </li>
                <li>
                    <a href="dashboard.php?view_products" class="block px-4 py-2 hover:bg-gray-700 rounded">View Products</a>
                </li>
            </ul>
        </li>
        <li class="relative">
            <button class="flex items-center w-full px-4 py-2 text-left hover:bg-gray-700 rounded" data-toggle="collapse" data-target="#products_cat">
                <i class="fa fa-list mr-2"></i> Categories <i class="fa fa-caret-down ml-auto"></i>
            </button>
            <ul id="products_cat" class="hidden ml-4 space-y-1">
                <li>
                    <a href="dashboard.php?insert_category" class="block px-4 py-2 hover:bg-gray-700 rounded">Create Category</a>
                </li>
                <li>
                    <a href="dashboard.php?view_categories" class="block px-4 py-2 hover:bg-gray-700 rounded">View Categories</a>
                </li>
            </ul>
        </li>
        <li class="relative">
            <button class="flex items-center w-full px-4 py-2 text-left hover:bg-gray-700 rounded" data-toggle="collapse" data-target="#users">
                <i class="fa fa-users mr-2"></i> Users <i class="fa fa-caret-down ml-auto"></i>
            </button>
            <ul id="users" class="hidden ml-4 space-y-1">
                <li>
                    <a href="dashboard.php?insert_user" class="block px-4 py-2 hover:bg-gray-700 rounded">Create User</a>
                </li>
                <li>
                    <a href="dashboard.php?view_users" class="block px-4 py-2 hover:bg-gray-700 rounded">View Users</a>
                </li>
            </ul>
        </li>
        <li class="relative">
            <button class="flex items-center w-full px-4 py-2 text-left hover:bg-gray-700 rounded" data-toggle="collapse" data-target="#orders">
                <i class="fa fa-shopping-cart mr-2"></i> Orders <i class="fa fa-caret-down ml-auto"></i>
            </button>
            <ul id="orders" class="hidden ml-4 space-y-1">
                <li>
                    <a href="dashboard.php?insert_order" class="block px-4 py-2 hover:bg-gray-700 rounded">Create Order</a>
                </li>
                <li>
                    <a href="dashboard.php?view_orders" class="block px-4 py-2 hover:bg-gray-700 rounded">View Orders</a>
                </li>
            </ul>
        </li>
    </ul>
</div>

<script>
    function myFunction() {
        document.getElementById("myDropdown").classList.toggle("hidden");
    }
    window.onclick = function(event) {
        if (!event.target.matches('.dropbtn')) {
            var dropdowns = document.getElementsByClassName("dropdown-content");
            for (var i = 0; i < dropdowns.length; i++) {
                dropdowns[i].classList.add("hidden");
            }
        }
    }
    document.querySelectorAll('[data-toggle="collapse"]').forEach(button => {
        button.addEventListener('click', function() {
            const target = document.querySelector(this.getAttribute('data-target'));
            target.classList.toggle('hidden');
        });
    });
</script>