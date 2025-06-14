<?php require_once 'includes/config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SecureBank - Online Banking</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body class="bg-gradient-to-br from-blue-50 to-indigo-100 min-h-screen">
    <div class="container mx-auto px-4 py-8">
        <!-- Header -->
        <nav class="bg-white rounded-xl shadow-lg mb-8 p-4">
            <div class="flex justify-between items-center">
                <div class="flex items-center space-x-2">
                    <div class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center">
                        <span class="text-white font-bold">SB</span>
                    </div>
                    <h1 class="text-2xl font-bold text-gray-800">SecureBank</h1>
                </div>
                <div class="space-x-4">
                    <?php if (isLoggedIn()): ?>
                        <a href="dashboard.php" class="btn-primary">Dashboard</a>
                        <a href="logout.php" class="text-gray-600 hover:text-gray-800">Logout</a>
                    <?php else: ?>
                        <a href="login.php" class="btn-primary">Login</a>
                    <?php endif; ?>
                </div>
            </div>
        </nav>

        <!-- Hero Section -->
        <div class="text-center mb-12">
            <h2 class="text-5xl font-bold text-gray-800 mb-4">Welcome to SecureBank</h2>
            <p class="text-xl text-gray-600 mb-8">Your trusted partner in secure online banking</p>
            
            <!-- Vulnerability Hint Banner -->
            <div class="bg-yellow-100 border-l-4 border-yellow-500 p-4 rounded-lg mb-8 max-w-2xl mx-auto">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-yellow-700">
                            <strong>Penetration Testing Lab:</strong> This is a deliberately vulnerable application. Find the security flaws!
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Features Grid -->
        <div class="grid md:grid-cols-3 gap-8 mb-12">
            <div class="card text-center">
                <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-semibold mb-2">Secure Transactions</h3>
                <p class="text-gray-600">Bank-grade security for all your transactions</p>
            </div>
            
            <div class="card text-center">
                <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-semibold mb-2">Real-time Analytics</h3>
                <p class="text-gray-600">Track your spending and savings in real-time</p>
            </div>
            
            <div class="card text-center">
                <div class="w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192L5.636 18.364M12 2.25a9.75 9.75 0 1 0 0 19.5 9.75 9.75 0 0 0 0-19.5Z"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-semibold mb-2">24/7 Support</h3>
                <p class="text-gray-600">Round-the-clock customer support</p>
            </div>
        </div>

        <!-- Test Accounts Info -->
        <div class="card max-w-4xl mx-auto">
            <h3 class="text-2xl font-bold mb-6 text-center">Test Accounts Available</h3>
            <div class="overflow-x-auto">
                <table class="w-full table-auto">
                    <thead>
                        <tr class="bg-gray-50">
                            <th class="px-4 py-3 text-left font-semibold">Username</th>
                            <th class="px-4 py-3 text-left font-semibold">Password</th>
                            <th class="px-4 py-3 text-left font-semibold">Role</th>
                            <th class="px-4 py-3 text-left font-semibold">Description</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <!-- <tr>
                            <td class="px-4 py-3 font-mono">admin</td>
                            <td class="px-4 py-3 font-mono">password</td>
                            <td class="px-4 py-3"><span class="bg-red-100 text-red-800 px-2 py-1 rounded-full text-sm">Admin</span></td>
                            <td class="px-4 py-3">System administrator account</td>
                        </tr> -->
                        <tr>
                            <td class="px-4 py-3 font-mono">john_doe</td>
                            <td class="px-4 py-3 font-mono">password</td>
                            <td class="px-4 py-3"><span class="bg-purple-100 text-purple-800 px-2 py-1 rounded-full text-sm">Premium</span></td>
                            <td class="px-4 py-3">Premium customer with high balance</td>
                        </tr>
                        <!-- <tr>
                            <td class="px-4 py-3 font-mono">jane_smith</td>
                            <td class="px-4 py-3 font-mono">password</td>
                            <td class="px-4 py-3"><span class="bg-blue-100 text-blue-800 px-2 py-1 rounded-full text-sm">Regular</span></td>
                            <td class="px-4 py-3">Regular customer account</td>
                        </tr> -->
                        <!-- <tr>
                            <td class="px-4 py-3 font-mono">bob_wilson</td>
                            <td class="px-4 py-3 font-mono">password</td>
                            <td class="px-4 py-3"><span class="bg-blue-100 text-blue-800 px-2 py-1 rounded-full text-sm">Regular</span></td>
                            <td class="px-4 py-3">Regular customer account</td>
                        </tr> -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
