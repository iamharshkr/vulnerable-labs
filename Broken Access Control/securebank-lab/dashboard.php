<?php
require_once 'includes/config.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$user = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - SecureBank</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen">
    <!-- Navigation -->
    <nav class="bg-white shadow-lg">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center py-4">
                <div class="flex items-center space-x-2">
                    <div class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center">
                        <span class="text-white font-bold">SB</span>
                    </div>
                    <h1 class="text-xl font-bold text-gray-800">SecureBank</h1>
                </div>
                <div class="flex items-center space-x-4">
                    <span class="text-gray-600">Welcome, <?php echo htmlspecialchars($user['username']); ?></span>
                    <?php if (isAdmin()): ?>
                        <span class="bg-red-100 text-red-800 px-2 py-1 rounded-full text-sm">Admin</span>
                    <?php endif; ?>
                    <a href="logout.php" class="text-red-600 hover:text-red-800">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="container mx-auto px-4 py-8">
        <div class="grid md:grid-cols-3 gap-6 mb-8">
            <!-- Account Overview -->
            <div class="bg-white rounded-xl shadow-lg p-6">
                <h3 class="text-lg font-semibold mb-4">Quick Actions</h3>
                <div class="space-y-3">
                    <a href="account.php?user_id=<?php echo $user['id']; ?>" 
                       class="block w-full bg-blue-600 hover:bg-blue-700 text-white text-center py-2 px-4 rounded-lg transition duration-200">
                        View Account Details
                    </a>
                    <a href="transactions.php" 
                       class="block w-full bg-green-600 hover:bg-green-700 text-white text-center py-2 px-4 rounded-lg transition duration-200">
                        View Transactions
                    </a>
                    
                    <!-- VULNERABILITY 1: Admin panel link visible to all users -->
                    <a href="admin.php" 
                       class="block w-full bg-red-600 hover:bg-red-700 text-white text-center py-2 px-4 rounded-lg transition duration-200">
                        Admin Panel
                    </a>
                </div>
            </div>

            <!-- Balance Card -->
            <div class="bg-gradient-to-r from-blue-500 to-purple-600 rounded-xl shadow-lg p-6 text-white">
                <h3 class="text-lg font-semibold mb-2">Account Balance</h3>
                <p class="text-3xl font-bold">$<?php echo number_format(rand(1000, 50000), 2); ?></p>
                <p class="text-blue-100 mt-2">Available Balance</p>
            </div>

            <!-- Recent Activity -->
            <div class="bg-white rounded-xl shadow-lg p-6">
                <h3 class="text-lg font-semibold mb-4">Recent Activity</h3>
                <div class="space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">Transfer to John</span>
                        <span class="text-red-600 font-medium">-$500</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">Deposit</span>
                        <span class="text-green-600 font-medium">+$1,200</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Vulnerability Testing Panel
        <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-6 mb-8">
            <h3 class="text-xl font-bold text-yellow-800 mb-4">🎯 Penetration Testing Challenges</h3>
            <div class="grid md:grid-cols-2 gap-4">
                <div class="bg-white p-4 rounded-lg">
                    <h4 class="font-semibold text-gray-800 mb-2">Challenge 1: Horizontal Privilege Escalation</h4>
                    <p class="text-sm text-gray-600 mb-3">Try to access other users' account information</p>
                    <div class="flex space-x-2">
                        <input type="text" id="user_id_input" placeholder="User ID" 
                               class="flex-1 px-3 py-2 border border-gray-300 rounded text-sm">
                        <button onclick="testUserAccess()" 
                                class="bg-blue-600 text-white px-4 py-2 rounded text-sm hover:bg-blue-700">
                            Test Access
                        </button>
                    </div>
                </div>
                
                <div class="bg-white p-4 rounded-lg">
                    <h4 class="font-semibold text-gray-800 mb-2">Challenge 2: Transaction IDOR</h4>
                    <p class="text-sm text-gray-600 mb-3">Find hidden transactions by manipulating IDs</p>
                    <a href="transactions.php?id=12340" 
                       class="inline-block bg-green-600 text-white px-4 py-2 rounded text-sm hover:bg-green-700">
                        View Transaction
                    </a>
                </div>
            </div>
        </div>
    </div> -->

    <script>
        function testUserAccess() {
            const userId = document.getElementById('user_id_input').value;
            if (userId) {
                window.location.href = `account.php?user_id=${userId}`;
            }
        }
    </script>
</body>
</html>
