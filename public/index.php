<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supermarket</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/heroicons@1.0.6/outline.js"></script> 
</head>
<body class="bg-white flex flex-col min-h-screen">
    <nav class="bg-green-600 p-4 sticky top-0 z-10 shadow-md">
        <div class="container mx-auto flex justify-between items-center">
            <h1 class="text-white text-2xl font-bold">Supermarket</h1>
            <div class="flex-1 mx-4">
                <input type="text" id="searchInput" placeholder="Search products..." 
                       class="w-full p-2 rounded-lg focus:outline"
                       onkeyup="fetchProducts()">
            </div>
            <div class="flex gap-4">
                <a href="admin_dashboard.php" id="admin-link" class="text-white hover:text-green-200 hidden">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                </a>
                <a href="cart.php" class="text-white hover:text-green-200">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                </a>
                <a href="login.php" class="text-white hover:text-green-200">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path></svg>
                </a>
            </div>
        </div>
    </nav>

    <main class="container mx-auto p-4 grow">
        <div id="product-grid" class="grow grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6 p-6 mt-16 bg-white">
            </div>
    </main>

    <footer class="bg-gray-800 text-white p-6 mt-auto text-center">
        <p>&copy; 2025 Supermarket de top</p>
    </footer>

    <script>
        let allProducts = [];

        (async () => {
            const check = await fetch('api.php?action=users');
            const data = await check.json();
            if (Array.isArray(data)) { document.getElementById('admin-link').classList.remove('hidden'); }
        })();

        async function fetchProducts() {
            const search = document.getElementById('searchInput').value;
            try {
                const response = await fetch(`api.php?action=products&search=${search}`);
                
                allProducts = await response.json();
                
                const grid = document.getElementById('product-grid');
                grid.innerHTML = '';

                allProducts.forEach(p => {
                    const card = `
                        <div class="bg-white rounded-xl shadow-lg overflow-hidden flex flex-col hover:shadow-xl transition">
                            <div class="h-48 overflow-hidden flex items-center justify-center bg-white p-4">
                                <img src="${p.image_urls[0]}" alt="${p.name}" class="object-contain h-48 w-full" onerror="this.style.display='none'">
                            </div>
                            <div class="p-4 flex flex-col flex-grow border-t border-gray-100">
                                <h3 class="text-lg font-semibold text-gray-800 mb-2 leading-tight">${p.name}</h3>
                                <div class="mt-auto flex justify-between items-center pt-2">
                                    <span class="text-green-600 font-bold text-xl">${p.price}</span>
                                    <button onclick="addToCart(${p.id})" 
                                        class="bg-green-500 hover:bg-green-600 text-white p-2 rounded-full shadow-md transition transform hover:scale-105 active:scale-95">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    `;
                    grid.innerHTML += card;
                });
            } catch (error) {
                console.error("Failed to fetch products:", error);
            }
        }

        async function addToCart(id) {
            const product = allProducts.find(p => p.id == id);

            if (!product) {
                alert("Error: Product not found");
                return;
            }

            try {
                const res = await fetch('api.php?action=add_cart', {
                    method: 'POST',
                    body: JSON.stringify({ product: product })
                });
            } catch (error) {
                console.error("Error adding to cart:", error);
            }
        }

        fetchProducts();
    </script>
</body>
</html>