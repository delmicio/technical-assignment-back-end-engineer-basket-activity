<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Basket Demo</title>
    <!-- Tailwind CSS via CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 text-gray-800">
<!-- Top Bar for CSV Download -->
<div class="bg-blue-600 text-white p-4 flex justify-between">
    <h1 class="text-2xl">Basket Demo</h1>
    <div>
        <label for="fromDate" class="mr-2">From:</label>
        <input type="date" id="fromDate" class="bg-white text-black p-1 rounded mr-4">
        <label for="toDate" class="mr-2">To:</label>
        <input type="date" id="toDate" class="bg-white text-black p-1 rounded mr-4">
        <button onclick="downloadCsv()" class="bg-green-500 hover:bg-green-600 px-4 py-2 rounded">Download CSV</button>
    </div>
</div>

<!-- Main Content Section -->
<div class="flex justify-between p-6 space-x-4">
    <!-- Left Section: List of Users -->
    <div class="w-1/4 bg-white p-4 rounded shadow">
        <h2 class="text-xl font-bold mb-4">Users</h2>
        <ul id="user-list" class="space-y-2">
            <!-- Users will be rendered here by JavaScript -->
        </ul>
    </div>

    <!-- Middle Section: Basket Items for Selected User -->
    <div class="w-1/2 bg-white p-4 rounded shadow">
        <h2 class="text-xl font-bold mb-4">Basket Items</h2>
        <ul id="basket-list" class="space-y-2">
            <!-- Basket items will be rendered here by JavaScript -->
        </ul>
        <hr>

        <h2 class="text-xl font-bold mb-4">Available Products</h2>
        <ul id="product-list" class="space-y-2">
            <!-- Products will be rendered here by JavaScript -->
        </ul>
    </div>

    <!-- Right Section: Removed Items for All Users -->
    <div class="w-1/4 bg-white p-4 rounded shadow">
        <h2 class="text-xl font-bold mb-4">Removed Items</h2>
        <ul id="removed-items-list" class="space-y-2">
            <!-- Removed items will be rendered here by JavaScript -->
        </ul>
    </div>
</div>

<script>
    const apiUrl = '/api';
    let selectedUserId = null;

    // Fetch users, products, and removed items on page load
    document.addEventListener('DOMContentLoaded', function () {
        fetchUsers();
        fetchProducts();
        fetchRemovedItems();
    });

    // Fetch users and set the first user as the default selected user
    function fetchUsers() {
        fetch('/api/users')
            .then(response => response.json())
            .then(users => {
                const userList = document.getElementById('user-list');
                userList.innerHTML = ''; // Clear previous users

                users.forEach((user, index) => {
                    const listItem = document.createElement('li');
                    listItem.innerHTML = `
                        <button class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded"
                                onclick="selectUser(${user.id})">${user.name}</button>`;
                    userList.appendChild(listItem);

                    // Auto-select the first user
                    if (index === 0) {
                        selectedUserId = user.id;
                        selectUser(user.id);
                    }
                });
            });
    }

    // Select user and fetch their basket
    function selectUser(userId) {
        selectedUserId = userId;
        fetchBasket(userId);
        fetchProducts();
    }

    // Fetch basket items for the selected user
    function fetchBasket(userId) {
        fetch(`${apiUrl}/basket?user_id=${userId}`)
            .then(response => response.json())
            .then(data => {
                const basketList = document.getElementById('basket-list');
                basketList.innerHTML = ''; // Clear previous basket items

                if (data.items && data.items.length > 0) {
                    data.items.forEach(item => {
                        const listItem = document.createElement('li');
                        listItem.innerHTML = `
                            <div class="bg-gray-200 p-2 rounded">
                                Product ID: ${item.product_id}
                                <button class="ml-4 bg-red-500 hover:bg-red-600 text-white px-2 py-1 rounded"
                                        onclick="removeFromBasket(${item.product_id}, ${userId})">Remove</button>
                            </div>`;
                        basketList.appendChild(listItem);
                    });
                } else {
                    basketList.innerHTML = '<p>No items in the basket.</p>';
                }
            });
    }

    // Fetch available products to add to the basket
    function fetchProducts() {
        fetch(`${apiUrl}/products`)
            .then(response => response.json())
            .then(products => {
                const productList = document.getElementById('product-list');
                productList.innerHTML = ''; // Clear previous products

                products.forEach(product => {
                    const listItem = document.createElement('li');
                    listItem.innerHTML = `
                        <div class="bg-gray-200 p-2 rounded">
                            Product ID: ${product.id} - ${product.name}
                            <button class="ml-4 bg-green-500 hover:bg-green-600 text-white px-2 py-1 rounded"
                                    onclick="addToBasket(${product.id}, ${selectedUserId})">Add to Basket</button>
                        </div>`;
                    productList.appendChild(listItem);
                });
            });
    }

    // Add a product to the selected user's basket (using user_id)
    function addToBasket(productId, userId) {
        fetch(`${apiUrl}/basket`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({product_id: productId, user_id: userId})
        })
            .then(() => {
                fetchBasket(userId); // Refresh basket after adding item
            });
    }

    // Remove a product from the selected user's basket (using user_id)
    function removeFromBasket(productId, userId) {
        fetch(`${apiUrl}/basket/${userId}/${productId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
            .then(() => {
                fetchBasket(userId); // Refresh basket after removing item
                fetchRemovedItems(); // Refresh removed items
            });
    }

    // Fetch removed items for all users
    function fetchRemovedItems() {
        fetch(`${apiUrl}/basket/removed-items`)
            .then(response => response.json())
            .then(data => {
                const removedItemsList = document.getElementById('removed-items-list');
                removedItemsList.innerHTML = ''; // Clear previous removed items

                data.removed_items.forEach(item => {
                    const listItem = document.createElement('li');
                    listItem.innerHTML = `User ID: ${item.user_id} - Product ID: ${item.product_id}`;
                    removedItemsList.appendChild(listItem);
                });
            });
    }

    // Download CSV of removed items with date filter
    function downloadCsv() {
        const fromDate = document.getElementById('fromDate').value;
        const toDate = document.getElementById('toDate').value;
        let url = `${apiUrl}/sales/removed-items-csv`;

        if (fromDate || toDate) {
            url += `?from=${fromDate}&to=${toDate}`;
        }

        window.location.href = url; // Trigger CSV download
    }
</script>
</body>
</html>
