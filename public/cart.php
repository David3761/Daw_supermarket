<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Cart</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-white">
    <nav class="bg-green-600 p-4 text-white flex justify-between items-center shadow-md">
        <a href="index.php" class="font-bold text-xl flex items-center gap-2">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Back to Store
        </a>
        <h1 class="text-xl font-semibold">Shopping Cart</h1>
    </nav>

    <div class="container mx-auto p-4 max-w-3xl">
        <div id="cart-items" class="space-y-4">
            </div>
        <div id="cart-summary" class="mt-8 bg-white p-6 rounded-lg shadow-md hidden">
            <div class="mb-6 border-b border-gray-200 pb-6">
                <label for="payment-method" class="block text-gray-700 text-sm font-bold mb-2">
                    Select Payment Method
                </label>
                <div class="relative">
                    <select id="payment-method" class="block appearance-none w-full bg-gray-50 border border-gray-300 text-gray-700 py-3 px-4 pr-8 rounded leading-tight focus:outline-none focus:bg-white focus:border-green-500">
                        <option value="Card">Credit Card</option>
                        <option value="Cash">Cash on delivery</option>
                        <option value="PayPal">PayPal</option>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
                        <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/></svg>
                    </div>
                </div>
            </div>
            <div class="flex justify-between text-xl font-bold text-gray-800 mb-4">
                <span>Total:</span>
                <span id="total-price">0.00 Lei</span>
            </div>
            <button onclick="placeOrder()" class="w-full bg-green-600 text-white py-3 rounded-lg hover:bg-green-700 font-semibold shadow-lg transition">
                Place Order
            </button>
        </div>
        <div id="empty-msg" class="text-center text-gray-500 mt-10 hidden">
            Your cart is currently empty.
        </div>
    </div>

    <script>
        const parsePrice = (str) => parseFloat(str.replace(/[^0-9.]/g, ''));

        async function loadCart() {
            const res = await fetch('api.php?action=cart');
            const cart = await res.json();
            const container = document.getElementById('cart-items');
            container.innerHTML = '';
            
            let total = 0;
            const items = Object.values(cart);

            if (items.length === 0) {
                document.getElementById('empty-msg').classList.remove('hidden');
                document.getElementById('cart-summary').classList.add('hidden');
                return;
            }

            document.getElementById('empty-msg').classList.add('hidden');
            document.getElementById('cart-summary').classList.remove('hidden');

            items.forEach(item => {
                const itemTotal = parsePrice(item.price) * item.qty;
                total += itemTotal;

                container.innerHTML += `
                    <div class="bg-white p-4 rounded-lg shadow flex items-center gap-4">
                        <img src="${item.image}" class="w-20 h-20 object-cover rounded">
                        <div class="flex-1">
                            <h3 class="font-bold text-gray-800">${item.name}</h3>
                            <p class="text-gray-500">${item.price}</p>
                        </div>
                        <div class="flex items-center gap-3">
                            <button onclick="updateQty(${item.id}, ${item.qty - 1})" class="bg-gray-200 w-8 h-8 rounded-full hover:bg-gray-300">-</button>
                            <span class="font-semibold w-6 text-center">${item.qty}</span>
                            <button onclick="updateQty(${item.id}, ${item.qty + 1})" class="bg-gray-200 w-8 h-8 rounded-full hover:bg-gray-300">+</button>
                        </div>
                        <button onclick="updateQty(${item.id}, 0)" class="text-red-500 hover:text-red-700">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        </button>
                    </div>
                `;
            });

            document.getElementById('total-price').innerText = total.toFixed(2) + " Lei";
        }

        async function updateQty(id, qty) {
            await fetch('api.php?action=update_cart', {
                method: 'POST',
                body: JSON.stringify({ id, qty })
            });
            loadCart();
        }

        async function placeOrder() {
            const paymentMethod = document.getElementById('payment-method').value;
            const res = await fetch('api.php?action=place_order', { method: 'POST', body: JSON.stringify({ 
                        payment_method: paymentMethod 
                    })});
            const data = await res.json();
            if (data.success) {
                alert('Order placed successfully!');
                window.location.href = 'index.php';
            } else {
                alert('Error: ' + data.message);
            }
        }

        loadCart();
    </script>
</body>
</html>