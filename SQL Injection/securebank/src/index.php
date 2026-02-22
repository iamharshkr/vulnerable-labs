<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // VULNERABLE CONNECTION (for demo)
    $conn = new mysqli(
        getenv('DB_HOST') ?: 'db',
        getenv('DB_USER') ?: 'root',
        getenv('DB_PASS') ?: 'rootpassword',
        getenv('DB_NAME') ?: 'bank'
    );
    if ($conn->connect_error) {
        die('Connection failed: ' . $conn->connect_error);
    }
    // 🔓 VULNERABLE QUERY – SQL INJECTION POSSIBLE
    $sql = "SELECT * FROM users WHERE username = '$username' AND password = '$password'";
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        header('Location: dashboard.php');
        exit;
    } else {
        $error = 'Invalid username or password.';
    }
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SecureBank · Login</title>
    <!-- Tailwind CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Extra custom font/icon (optional) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>

<body class="bg-gradient-to-br from-blue-900 to-blue-700 min-h-screen flex items-center justify-center p-4">

    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-8">
        <div class="text-center mb-8">
            <i class="fas fa-university text-5xl text-blue-700 mb-2"></i>
            <h1 class="text-3xl font-bold text-gray-800">SecureBank</h1>
            <p class="text-gray-500">Online Banking Portal</p>
        </div>

        <form method="POST" class="space-y-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                <input type="text" name="username" required
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                <input type="password" name="password" required
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition">
            </div>

            <?php if ($error): ?>
                <div class="bg-red-50 text-red-600 p-3 rounded-lg text-sm text-center">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <button type="submit"
                class="w-full bg-blue-700 hover:bg-blue-800 text-white font-semibold py-3 rounded-lg transition duration-200 transform hover:scale-[1.02]">
                <i class="fas fa-sign-in-alt mr-2"></i>Login
            </button>
        </form>

        <div class="mt-6 text-xs text-center text-gray-400">
            <i class="fas fa-shield-alt mr-1"></i> SSL Encrypted · Demo Purpose Only
        </div>

        <!-- SQL Injection hint (remove in production demo) -->
        <div class="mt-4 p-3 bg-yellow-50 border border-yellow-200 rounded text-yellow-700 text-xs">
            <strong>Demo:</strong> Try <span class="font-mono">admin' -- </span> (any password) or <span class="font-mono">' OR '1'='1</span>
        </div>
    </div>

</body>

</html>