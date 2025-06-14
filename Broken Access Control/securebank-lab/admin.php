<?php
require_once 'includes/config.php';

// VULNERABILITY: Weak authorization check that can be bypassed
$bypass_header = $_SERVER['HTTP_X_ORIGINAL_URL'] ?? '';
$is_admin_request = isAdmin() || !empty($bypass_header);

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

// VULNERABILITY: Simple role check that can be manipulated
if (!$is_admin_request && !isset($_GET['admin_override'])) {
    http_response_code(403);
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Access Denied - SecureBank</title>
        <script src="https://cdn.tailwindcss.com"></script>
    </head>
    <body class="bg-gray-50 min-h-screen flex items-center justify-center">
        <div class="bg-white rounded-xl shadow-lg p-8 max-w-md text-center">
            <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                </svg>
            </div>
            <h2 class="text-2xl font-bold text-gray-800 mb-4">Access Denied</h2>
            <p class="text-gray-600 mb-6">You don't have permission to access the admin panel.</p>
            <a href="dashboard.php" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg transition duration-200">
                Back to Dashboard
            </a>
            
            <!-- Hint for bypass -->
            <div class="mt-6 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                <p class="text-sm text-yellow-700">
                    <strong>Hint:</strong> Try using HTTP headers or URL parameters to bypass this restriction.
                </p>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// If we reach here, access is granted (either legitimately or through bypass)
$all_users = $pdo->query("SELECT * FROM users")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - SecureBank</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen">
    <nav class="bg-white shadow-lg">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center py-4">
                <div class="flex items-center space-x-2">
                    <div class="w-8 h-8 bg-red-600 rounded-lg flex items-center justify-center">
                        <span class="text-white font-bold">A</span>
                    </div>
                    <h1 class="text-xl font-bold text-gray-800">Admin Panel</h1>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="dashboard.php" class="text-blue-600 hover:text-blue-800">Dashboard</a>
                    <a href="logout.php" class="text-red-600 hover:text-red-800">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="container mx-auto px-4 py-8">
        <!-- Success Message with Flag -->
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-8">
            <strong>🎉 Congratulations!</strong> You've successfully accessed the admin panel!<br>
            <strong>FLAG{v3rt1c4l_4cc3ss_c0ntr0l_byp4ss3d}</strong>
        </div>

        <div class="grid md:grid-cols-2 gap-8">
            <!-- User Management -->
            <div class="bg-white rounded-xl shadow-lg p-6">
                <h3 class="text-xl font-bold text-gray-800 mb-6">User Management</h3>
                <div class="overflow-x-auto">
                    <table class="w-full table-auto">
                        <thead>
                            <tr class="bg-gray-50">
                                <th class="px-4 py-2 text-left text-sm font-semibold">ID</th>
                                <th class="px-4 py-2 text-left text-sm font-semibold">Username</th>
                                <th class="px-4 py-2 text-left text-sm font-semibold">Role</th>
                                <th class="px-4 py-2 text-left text-sm font-semibold">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php foreach ($all_users as $user): ?>
                            <tr>
                                <td class="px-4 py-2 text-sm"><?php echo $user['id']; ?></td>
                                <td class="px-4 py-2 text-sm"><?php echo htmlspecialchars($user['username']); ?></td>
                                <td class="px-4 py-2 text-sm">
                                    <span class="px-2 py-1 rounded-full text-xs <?php echo $user['role'] === 'admin' ? 'bg-red-100 text-red-800' : 'bg-blue-100 text-blue-800'; ?>">
                                        <?php echo ucfirst($user['role']); ?>
                                    </span>
                                </td>
                                <td class="px-4 py-2 text-sm">
                                    <button class="text-blue-600 hover:text-blue-800 text-xs">Edit</button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- System Stats -->
            <div class="bg-white rounded-xl shadow-lg p-6">
                <h3 class="text-xl font-bold text-gray-800 mb-6">System Statistics</h3>
                <div class="space-y-4">
                    <div class="flex justify-between items-center p-4 bg-blue-50 rounded-lg">
                        <span class="text-blue-700 font-medium">Total Users</span>
                        <span class="text-2xl font-bold text-blue-600"><?php echo count($all_users); ?></span>
                    </div>
                    <div class="flex justify-between items-center p-4 bg-green-50 rounded-lg">
                        <span class="text-green-700 font-medium">Active Sessions</span>
                        <span class="text-2xl font-bold text-green-600">12</span>
                    </div>
                    <div class="flex justify-between items-center p-4 bg-yellow-50 rounded-lg">
                        <span class="text-yellow-700 font-medium">Security Alerts</span>
                        <span class="text-2xl font-bold text-yellow-600">3</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
