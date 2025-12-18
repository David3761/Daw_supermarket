<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Supermarket</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-white flex items-center justify-center h-screen">
    <div class="bg-white p-8 rounded-xl shadow-lg w-96">
        <h2 class="text-2xl font-bold text-center text-green-600 mb-2">Create Account</h2>
        <p class="text-gray-500 text-center mb-6 text-sm">Join us today</p>
        <form id="registerForm" onsubmit="handleRegister(event)">
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">Email Address</label>
                <input type="email" id="email" required placeholder="you@example.com"
                       class="w-full p-3 border rounded focus:outline-none">
            </div>
            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-bold mb-2">Password</label>
                <input type="password" id="password" required placeholder="Choose a password"
                       class="w-full p-3 border rounded focus:outline-none">
            </div>
            <button type="submit" 
                    class="w-full bg-green-600 text-white font-bold py-3 rounded hover:bg-green-700 transition shadow-md">
                Sign Up
            </button>
            <p id="message" class="text-center mt-4 text-sm font-semibold hidden"></p>
        </form>
        <div class="mt-6 pt-4 border-t border-gray-100 text-center">
            <p class="text-sm text-gray-600">Already have an account?</p>
            <a href="login.php" class="text-green-600 font-bold">Login here</a>
        </div>
    </div>

    <script>
        async function handleRegister(e) {
            e.preventDefault();
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            const msg = document.getElementById('message');

            if(password.length < 4) {
                showMsg("Password must be at least 4 characters", "red");
                return;
            }

            const res = await fetch('api.php?action=register', {
                method: 'POST',
                body: JSON.stringify({ email, password })
            });
            
            const data = await res.json();
            
            if (data.success) {
                showMsg("Account created! You need to log in. Redirecting...", "green");
                setTimeout(() => {
                    window.location.href = 'login.php';
                }, 1500);
            } else {
                showMsg(data.message, "red");
            }
        }

        function showMsg(text, color) {
            const msg = document.getElementById('message');
            msg.innerText = text;
            msg.className = `text-center mt-4 text-sm font-semibold text-${color}-600`;
            msg.classList.remove('hidden');
        }
    </script>
</body>
</html>