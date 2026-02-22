<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit;
}

$admin_id = $_SESSION['user_id'];
$message = '';
$error = '';

$conn = new mysqli(
    getenv('DB_HOST') ?: 'db',
    getenv('DB_USER') ?: 'root',
    getenv('DB_PASS') ?: 'rootpassword',
    getenv('DB_NAME') ?: 'bank'
);
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

// Handle admin actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Deposit
    if (isset($_POST['deposit'])) {
        $to_username = $_POST['to_username'];
        $amount = floatval($_POST['amount']);
        // VULNERABLE: find user
        $user_res = $conn->query("SELECT id FROM users WHERE username = '$to_username'");
        if ($user_res && $user_res->num_rows > 0) {
            $to_user = $user_res->fetch_assoc();
            $conn->query("UPDATE users SET balance = balance + $amount WHERE id = " . $to_user['id']);
            $conn->query("INSERT INTO transactions (to_account, amount, type) VALUES (" . $to_user['id'] . ", $amount, 'deposit')");
            $message = "Deposited $$amount to $to_username.";
        } else {
            $error = "User not found.";
        }
    }
    // Withdraw
    elseif (isset($_POST['withdraw'])) {
        $from_username = $_POST['from_username'];
        $amount = floatval($_POST['amount']);
        $user_res = $conn->query("SELECT id, balance FROM users WHERE username = '$from_username'");
        if ($user_res && $user_res->num_rows > 0) {
            $from_user = $user_res->fetch_assoc();
            if ($from_user['balance'] >= $amount) {
                $conn->query("UPDATE users SET balance = balance - $amount WHERE id = " . $from_user['id']);
                $conn->query("INSERT INTO transactions (from_account, amount, type) VALUES (" . $from_user['id'] . ", $amount, 'withdrawal')");
                $message = "Withdrew $$amount from $from_username.";
            } else {
                $error = "Insufficient balance.";
            }
        } else {
            $error = "User not found.";
        }
    }
    // Transfer (admin can transfer from any to any)
    elseif (isset($_POST['transfer'])) {
        $from_username = $_POST['from_username'];
        $to_username = $_POST['to_username'];
        $amount = floatval($_POST['amount']);
        // Find both users (vulnerable)
        $from_res = $conn->query("SELECT id, balance FROM users WHERE username = '$from_username'");
        $to_res = $conn->query("SELECT id FROM users WHERE username = '$to_username'");
        if ($from_res && $from_res->num_rows > 0 && $to_res && $to_res->num_rows > 0) {
            $from_user = $from_res->fetch_assoc();
            $to_user = $to_res->fetch_assoc();
            if ($from_user['balance'] >= $amount) {
                $conn->query("UPDATE users SET balance = balance - $amount WHERE id = " . $from_user['id']);
                $conn->query("UPDATE users SET balance = balance + $amount WHERE id = " . $to_user['id']);
                $conn->query("INSERT INTO transactions (from_account, to_account, amount, type) VALUES (" . $from_user['id'] . ", " . $to_user['id'] . ", $amount, 'transfer')");
                $message = "Transferred $$amount from $from_username to $to_username.";
            } else {
                $error = "Insufficient balance in source account.";
            }
        } else {
            $error = "One or both usernames not found.";
        }
    }
}

// Get admin balance
$balance_res = $conn->query("SELECT balance FROM users WHERE id = $admin_id");
$admin_balance = $balance_res->fetch_assoc()['balance'];

// Get all users for dropdowns (vulnerable)
$users = $conn->query("SELECT id, username FROM users");

// Get all transactions
$all_transactions = $conn->query("
    SELECT t.*,
           u_from.username as from_name,
           u_to.username as to_name
    FROM transactions t
    LEFT JOIN users u_from ON t.from_account = u_from.id
    LEFT JOIN users u_to ON t.to_account = u_to.id
    ORDER BY t.transaction_date DESC
");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard · SecureBank</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>

<body class="bg-gray-100">

    <nav class="bg-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <i class="fas fa-university text-2xl text-blue-700 mr-2"></i>
                    <span class="font-bold text-xl text-gray-800">SecureBank Admin</span>
                    <span class="ml-4 text-sm bg-purple-100 text-purple-800 px-2 py-1 rounded">Admin: <?= htmlspecialchars($_SESSION['username']) ?></span>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="logout.php" class="text-gray-600 hover:text-gray-900">
                        <i class="fas fa-sign-out-alt mr-1"></i>Logout
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <!-- Admin Balance -->
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg mb-6">
            <div class="p-6 bg-gradient-to-r from-purple-500 to-purple-600">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-purple-100 text-sm font-medium">Your Admin Balance</p>
                        <p class="text-4xl font-bold text-white">$<?= number_format($admin_balance, 2) ?></p>
                    </div>
                    <i class="fas fa-user-tie text-6xl text-white opacity-30"></i>
                </div>
            </div>
        </div>

        <!-- Messages -->
        <?php if ($message): ?>
            <div class="bg-green-50 border-l-4 border-green-400 p-4 mb-6"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="bg-red-50 border-l-4 border-red-400 p-4 mb-6"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <!-- Admin Action Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            <!-- Deposit Card -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold text-gray-700 mb-4 flex items-center">
                    <i class="fas fa-plus-circle text-green-600 mr-2"></i>Deposit
                </h2>
                <form method="POST" class="space-y-3">
                    <input type="text" name="to_username" placeholder="Username" required
                        class="w-full border rounded p-2">
                    <input type="number" step="0.01" name="amount" placeholder="Amount" required
                        class="w-full border rounded p-2">
                    <button type="submit" name="deposit"
                        class="w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-2 rounded">
                        Deposit
                    </button>
                </form>
            </div>
            <!-- Withdraw Card -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold text-gray-700 mb-4 flex items-center">
                    <i class="fas fa-minus-circle text-red-600 mr-2"></i>Withdraw
                </h2>
                <form method="POST" class="space-y-3">
                    <input type="text" name="from_username" placeholder="Username" required
                        class="w-full border rounded p-2">
                    <input type="number" step="0.01" name="amount" placeholder="Amount" required
                        class="w-full border rounded p-2">
                    <button type="submit" name="withdraw"
                        class="w-full bg-red-600 hover:bg-red-700 text-white font-semibold py-2 rounded">
                        Withdraw
                    </button>
                </form>
            </div>
            <!-- Transfer Card -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold text-gray-700 mb-4 flex items-center">
                    <i class="fas fa-random text-blue-600 mr-2"></i>Transfer (Any)
                </h2>
                <form method="POST" class="space-y-3">
                    <input type="text" name="from_username" placeholder="From username" required
                        class="w-full border rounded p-2">
                    <input type="text" name="to_username" placeholder="To username" required
                        class="w-full border rounded p-2">
                    <input type="number" step="0.01" name="amount" placeholder="Amount" required
                        class="w-full border rounded p-2">
                    <button type="submit" name="transfer"
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 rounded">
                        Transfer
                    </button>
                </form>
            </div>
        </div>

        <!-- All Transactions -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold text-gray-700 mb-4 flex items-center">
                <i class="fas fa-globe text-blue-600 mr-2"></i>All Transactions
            </h2>
            <?php if ($all_transactions && $all_transactions->num_rows > 0): ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">From</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">To</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Amount</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Type</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php while ($t = $all_transactions->fetch_assoc()): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?= date('M j, Y H:i', strtotime($t['transaction_date'])) ?>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900"><?= htmlspecialchars($t['from_name'] ?: '—') ?></td>
                                    <td class="px-6 py-4 text-sm text-gray-900"><?= htmlspecialchars($t['to_name'] ?: '—') ?></td>
                                    <td class="px-6 py-4 text-sm text-right font-medium text-gray-900">$<?= number_format($t['amount'], 2) ?></td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                            <?= $t['type'] == 'deposit' ? 'bg-green-100 text-green-800' : ($t['type'] == 'withdrawal' ? 'bg-red-100 text-red-800' : 'bg-blue-100 text-blue-800') ?>">
                                            <?= ucfirst($t['type']) ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-gray-500 text-center py-4">No transactions yet.</p>
            <?php endif; ?>
        </div>
    </main>
</body>

</html>
<?php $conn->close(); ?>