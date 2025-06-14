<?php
require_once 'includes/config.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

// VULNERABILITY: No authorization check for user_id parameter
$user_id = $_GET['user_id'] ?? $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT u.*, a.* FROM users u 
                       LEFT JOIN accounts a ON u.id = a.user_id 
                       WHERE u.id = ?");
$stmt->execute([$user_id]);
$account_data = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$account_data) {
    $error = "Account not found";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Details - SecureBank</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen">
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
                    <a href="dashboard.php" class="text-blue-600 hover:text-blue-800">Dashboard</a>
                    <a href="logout.php" class="text-red-600 hover:text-red-800">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="container mx-auto px-4 py-8">
        <?php if (isset($error)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php else: ?>
            <!-- Account Information -->
            <div class="bg-white rounded-xl shadow-lg p-8 mb-8">
                <h2 class="text-2xl font-bold text-gray-800 mb-6">Account Information</h2>
                
                <div class="grid md:grid-cols-2 gap-8">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-700 mb-4">Personal Details</h3>
                        <div class="space-y-3">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Name:</span>
                                <span class="font-medium"><?php echo htmlspecialchars($account_data['username']); ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Email:</span>
                                <span class="font-medium"><?php echo htmlspecialchars($account_data['email']); ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Role:</span>
                                <span class="font-medium capitalize"><?php echo htmlspecialchars($account_data['role']); ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">User ID:</span>
                                <span class="font-medium"><?php echo htmlspecialchars($account_data['id']); ?></span>
                            </div>
                        </div>
                    </div>
                    
                    <div>
                        <h3 class="text-lg font-semibold text-gray-700 mb-4">Account Details</h3>
                        <div class="space-y-3">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Account Number:</span>
                                <span class="font-medium"><?php echo htmlspecialchars($account_data['account_number'] ?? 'N/A'); ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Balance:</span>
                                <span class="font-medium text-green-600">$<?php echo number_format($account_data['balance'] ?? 0, 2); ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Account Type:</span>
                                <span class="font-medium capitalize"><?php echo htmlspecialchars($account_data['account_type'] ?? 'N/A'); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <?php if (!empty($account_data['notes'])): ?>
                    <div class="mt-8 p-4 bg-gray-50 rounded-lg">
                        <h4 class="font-semibold text-gray-700 mb-2">Account Notes:</h4>
                        <p class="text-gray-600"><?php echo htmlspecialchars($account_data['notes']); ?></p>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Vulnerability Indicator -->
            <?php if ($user_id != $_SESSION['user_id']): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                    <strong>⚠️ Security Alert:</strong> You are viewing another user's account information! This is a Horizontal Privilege Escalation vulnerability.
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</body>
</html>
