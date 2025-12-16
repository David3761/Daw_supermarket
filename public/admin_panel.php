<?php
session_start();
if (($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: admin_login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">
    <nav class="bg-gray-800 text-white p-4 shadow-md">
        <div class="container mx-auto flex justify-between items-center">
            <h1 class="text-xl font-bold">Admin Dashboard</h1>
            <div class="flex gap-4">
                <a href="index.php" class="hover:text-gray-300">Go to Store</a>
                <a href="login.php" class="hover:text-gray-300">Logout</a>
            </div>
        </div>
    </nav>
    <div class="container mx-auto p-6 grid grid-cols-1 md:grid-cols-2 gap-8">
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h2 class="text-2xl font-bold mb-4 text-gray-800 border-b pb-2">Manage Products</h2>
            <form onsubmit="addProduct(event)" class="mb-8 bg-gray-50 p-4 rounded border">
                <h3 class="font-semibold mb-2">Add New Product</h3>
                <div class="grid grid-cols-1 gap-3">
                    <input type="text" id="p-name" placeholder="Product Name" required class="p-2 border rounded">
                    <input type="text" id="p-price" placeholder="Price (e.g. 10.50 Lei)" required class="p-2 border rounded">
                    <input type="text" id="p-image" placeholder="Image URL" required class="p-2 border rounded">
                    <button type="submit" class="bg-green-600 text-white py-2 rounded hover:bg-green-700">Add Product</button>
                </div>
            </form>
            <div class="overflow-y-auto h-96">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-200 text-gray-600 text-sm">
                            <th class="p-2">Name</th>
                            <th class="p-2">Price</th>
                            <th class="p-2">Action</th>
                        </tr>
                    </thead>
                    <tbody id="product-list" class="text-sm"></tbody>
                </table>
            </div>
        </div>
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h2 class="text-2xl font-bold mb-4 text-gray-800 border-b pb-2">Manage Users</h2>
            <div class="overflow-y-auto h-[600px]">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-200 text-gray-600 text-sm">
                            <th class="p-2">Email</th>
                            <th class="p-2">Role</th>
                            <th class="p-2">Status</th>
                            <th class="p-2">Action</th>
                        </tr>
                    </thead>
                    <tbody id="user-list" class="text-sm"></tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        async function loadData() {
            const pRes = await fetch('api.php?action=products');
            const products = await pRes.json();
            const pList = document.getElementById('product-list');
            pList.innerHTML = products.map(p => `
                <tr class="border-b hover:bg-gray-50">
                    <td class="p-2">${p.name}</td>
                    <td class="p-2">${p.price}</td>
                    <td class="p-2">
                        <button onclick="deleteProduct(${p.id})" class="text-red-500 hover:text-red-700 font-bold">Delete</button>
                    </td>
                </tr>
            `).join('');
            
            const uRes = await fetch('api.php?action=users');
            const users = await uRes.json();
            const uList = document.getElementById('user-list');
            uList.innerHTML = users.map(u => `
                <tr class="border-b hover:bg-gray-50">
                    <td class="p-2">${u.email}</td>
                    <td class="p-2 capitalize">${u.role || 'user'}</td>
                    <td class="p-2">
                        <span class="${u.is_banned == 1 ? 'text-red-600 font-bold' : 'text-green-600'}">
                            ${u.is_banned == 1 ? 'Banned' : 'Active'}
                        </span>
                    </td>
                    <td class="p-2">
                        ${u.role !== 'admin' ? `
                            <button onclick="toggleBan(${u.id}, ${u.is_banned})" 
                                class="${u.is_banned == 1 ? 'text-green-600' : 'text-red-600'} font-bold hover:underline">
                                ${u.is_banned == 1 ? 'Unban' : 'Ban'}
                            </button>
                        ` : '<span class="text-gray-400">N/A</span>'}
                    </td>
                </tr>
            `).join('');
        }

        async function addProduct(e) {
            e.preventDefault();
            const name = document.getElementById('p-name').value;
            const price = document.getElementById('p-price').value;
            const image = document.getElementById('p-image').value;

            await fetch('api.php?action=add_product', {
                method: 'POST',
                body: JSON.stringify({ name, price, image })
            });
            
            document.getElementById('p-name').value = '';
            document.getElementById('p-price').value = '';
            document.getElementById('p-image').value = '';
            loadData();
        }

        async function deleteProduct(id) {
            if(!confirm('Are you sure you want to delete this product?')) return;
            await fetch('api.php?action=delete_product', {
                method: 'POST',
                body: JSON.stringify({ id })
            });
            loadData();
        }

        async function toggleBan(id, currentStatus) {
            const newStatus = currentStatus == 1 ? 0 : 1;
            await fetch('api.php?action=ban_user', {
                method: 'POST',
                body: JSON.stringify({ id, is_banned: newStatus })
            });
            loadData();
        }

        loadData();
    </script>
</body>
</html>
