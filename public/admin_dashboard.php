<?php
session_start();
if (($_SESSION['role'] ?? 'user') !== 'admin') {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-gray-100">
    <nav class="bg-gray-800 p-4 text-white mb-6">
        <div class="container mx-auto flex flex-col sm:flex-row justify-between items-center gap-4">
            <h1 class="text-xl font-bold">Admin Dashboard</h1>
            <a href="index.php" class="hover:text-gray-300">Back to Store</a>
        </div>
    </nav>

    <div class="container mx-auto p-4 space-y-6">
        
        <div class="bg-white p-6 rounded shadow">
            <h2 class="text-xl font-bold mb-4 text-blue-600">External Data (BNR)</h2>
            <div id="bnr-rate" class="text-2xl font-mono">Loading Exchange Rate...</div>
        </div>

        <div class="bg-white p-6 rounded shadow">
            <h2 class="text-xl font-bold mb-4 text-purple-600">Statistics (Top 5 Expensive Products)</h2>
            <div class="h-64">
                <canvas id="myChart"></canvas>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white p-6 rounded shadow">
                <h2 class="text-xl font-bold mb-4 text-green-600">Export Data</h2>
                <p class="mb-4 text-gray-600">Download product list as CSV file.</p>
                <a href="api.php?action=export_products" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 inline-block">
                    Export to CSV
                </a>
            </div>

            <div class="bg-white p-6 rounded shadow">
                <h2 class="text-xl font-bold mb-4 text-orange-600">Import Data</h2>
                <p class="mb-4 text-gray-600">Upload CSV (ID, Name, Price, ImageURL).</p>
                <form id="importForm" class="flex flex-col sm:flex-row gap-2">
                    <input type="file" id="csvFile" accept=".csv" class="border p-2 rounded w-full">
                    <button type="submit" class="bg-orange-500 text-white px-4 py-2 rounded hover:bg-orange-600 w-full sm:w-auto">Import</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        fetch('api.php?action=exchange_rate')
            .then(r => r.json())
            .then(data => {
                const el = document.getElementById('bnr-rate');
                if(data.eur) el.innerText = `1 EUR = ${data.eur} RON`;
                else el.innerText = "Rate unavailable";
            });

        fetch('api.php?action=stats')
            .then(r => r.json())
            .then(data => {
                const ctx = document.getElementById('myChart').getContext('2d');
                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: data.map(d => d.name),
                        datasets: [{
                            label: 'Price (RON)',
                            data: data.map(d => parseFloat(d.price.replace(/[^0-9.]/g, ''))),
                            backgroundColor: 'rgba(75, 192, 192, 0.2)',
                            borderColor: 'rgba(75, 192, 192, 1)',
                            borderWidth: 1
                        }]
                    },
                    options: { maintainAspectRatio: false, scales: { y: { beginAtZero: true } } }
                });
            });

        document.getElementById('importForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const file = document.getElementById('csvFile').files[0];
            if(!file) return alert("Select a file");
            
            const formData = new FormData();
            formData.append('file', file);

            const res = await fetch('api.php?action=import_products', {
                method: 'POST',
                body: formData
            });
            const data = await res.json();
            if(data.success) alert("Import successful!");
            else alert("Import failed");
        });
    </script>
</body>
</html>
