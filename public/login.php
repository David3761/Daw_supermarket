<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supermarket</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-white flex items-center justify-center h-screen">
    <div class="bg-white p-8 rounded-xl shadow-lg w-full max-w-md">
        <div class="text-center mb-6">
            <h1 class="text-3xl font-bold text-green-600">Supermarket</h1>
            <p class="text-gray-500">Sign in to continue shopping</p>
        </div>
        <form id="loginForm" onsubmit="handleLogin(event)">
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="email">
                    Email Address
                </label>
                <input type="email" id="email" required 
                       placeholder="Enter your email"
                       class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none ">
            </div>
            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="password">
                    Password
                </label>
                <input type="password" id="password" required 
                       placeholder="Enter your password"
                       class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none">
            </div>
            <button type="submit" 
                    class="w-full bg-green-600 text-white font-bold py-3 rounded-lg hover:bg-green-700 transition shadow-md">
                Login
            </button>
            <div id="error-msg" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mt-4 hidden" role="alert">
                <span class="block sm:inline" id="error-text"></span>
            </div>
        </form>
        <div class="mt-6 pt-6 border-t border-gray-100 text-center space-y-2">
            <a href="register.php" class="text-green-600 font-bold ">I don't have an account</a>
            <p class="text-sm">
                <a href="index.php" class="text-gray-500 hover:text-gray-700">← Back to Store</a>
            </p>
        </div>
    </div>

    <script>
        async function handleLogin(e) {
            e.preventDefault();
            
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            const errorBox = document.getElementById('error-msg');
            const errorText = document.getElementById('error-text');

            errorBox.classList.add('hidden');

            try {
                const res = await fetch('api.php?action=login', {
                    method: 'POST',
                    body: JSON.stringify({ email, password })
                });
                
                const data = await res.json();
                
                if (data.success) {
                    window.location.href = 'index.php';
                } else {
                    errorText.innerText = data.message || "Login failed. Please try again.";
                    errorBox.classList.remove('hidden');
                }
            } catch (err) {
                console.error(err);
                errorText.innerText = "A server error occurred.";
                errorBox.classList.remove('hidden');
            }
        }
    </script>
</body>
</html>