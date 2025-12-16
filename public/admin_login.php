<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-900 flex items-center justify-center h-screen">
    <div class="bg-white p-8 rounded-xl shadow-2xl w-full max-w-md">
        <div class="text-center mb-6">
            <h1 class="text-3xl font-bold text-gray-800">Admin Panel</h1>
            <p class="text-gray-500">Restricted Access</p>
        </div>
        <form onsubmit="handleAdminLogin(event)">
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">Email</label>
                <input type="email" id="email" required class="w-full p-3 border rounded focus:outline-none focus:border-gray-500">
            </div>
            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-bold mb-2">Password</label>
                <input type="password" id="password" required class="w-full p-3 border rounded focus:outline-none focus:border-gray-500">
            </div>
            <button type="submit" class="w-full bg-gray-800 text-white font-bold py-3 rounded hover:bg-gray-700 transition">
                Login to Dashboard
            </button>
            <p id="error-msg" class="text-red-500 text-center mt-4 hidden"></p>
        </form>
        <div class="mt-6 text-center">
            <a href="index.php" class="text-gray-500 hover:text-gray-800 text-sm">← Back to Store</a>
        </div>
    </div>

    <script>
        async function handleAdminLogin(e) {
            e.preventDefault();
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            const errorMsg = document.getElementById('error-msg');

            try {
                const res = await fetch('api.php?action=login', {
                    method: 'POST',
                    body: JSON.stringify({ email, password })
                });
                const data = await res.json();

                if (data.success) {
                    if (data.role === 'admin') {
                        window.location.href = 'admin_panel.php';
                    } else {
                        errorMsg.innerText = "Access Denied: You are not an administrator.";
                        errorMsg.classList.remove('hidden');
                    }
                } else {
                    errorMsg.innerText = data.message;
                    errorMsg.classList.remove('hidden');
                }
            } catch (err) {
                console.error(err);
            }
        }
    </script>
</body>
</html>
