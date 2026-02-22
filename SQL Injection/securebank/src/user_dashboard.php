<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header('Location: index.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$message = '';
$error = '';

// Connect (vulnerable – we'll reuse same connection style)
$conn = new mysqli(
    getenv('DB_HOST') ?: 'db',
    getenv('DB_USER') ?: 'root',
    getenv('DB_PASS') ?: 'rootpassword',
    getenv('DB_NAME') ?: 'bank'
);
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

// Handle transfer
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['transfer'])) {
    $to_username = $_POST['to_username'];
    $amount = floatval($_POST['amount']);

    // 🔓 VULNERABLE: get recipient ID
    $sql_to = "SELECT id, balance FROM users WHERE username = '$to_username'";
    $result_to = $conn->query($sql_to);

    if ($result_to && $result_to->num_rows > 0) {
        $to_user = $result_to->fetch_assoc();
        $to_id = $to_user['id'];

        // Get sender balance (vulnerable)
        $sql_sender = "SELECT balance FROM users WHERE id = $user_id";
        $sender_res = $conn->query($sql_sender);
        $sender = $sender_res->fetch_assoc();

        if ($sender['balance'] >= $amount && $amount > 0) {
            // Deduct from sender
            $conn->query("UPDATE users SET balance = balance - $amount WHERE id = $user_id");
            // Add to recipient
            $conn->query("UPDATE users SET balance = balance + $amount WHERE id = $to_id");
            // Record transaction
            $conn->query("INSERT INTO transactions (from_account, to_account, amount, type) VALUES ($user_id, $to_id, $amount, 'transfer')");

            $message = "Transfer of $$amount to $to_username successful.";
        } else {
            $error = "Insufficient funds or invalid amount.";
        }
    } else {
        $error = "Recipient username not found.";
    }
}

// Get current user balance (vulnerable)
$balance_res = $conn->query("SELECT balance FROM users WHERE id = $user_id");
$balance = $balance_res->fetch_assoc()['balance'];

// Get transaction history (vulnerable)
$transactions = $conn->query("
    SELECT t.*,
           u_from.username as from_name,
           u_to.username as to_name
    FROM transactions t
    LEFT JOIN users u_from ON t.from_account = u_from.id
    LEFT JOIN users u_to ON t.to_account = u_to.id
    WHERE t.from_account = $user_id OR t.to_account = $user_id
    ORDER BY t.transaction_date DESC
");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard · SecureBank</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>

<body class="bg-gray-100">

    <!-- Navigation -->
    <nav class="bg-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <i class="fas fa-university text-2xl text-blue-700 mr-2"></i>
                    <span class="font-bold text-xl text-gray-800">SecureBank</span>
                    <span class="ml-4 text-sm bg-green-100 text-green-800 px-2 py-1 rounded">User: <?= htmlspecialchars($_SESSION['username']) ?></span>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="logout.php" class="text-gray-600 hover:text-gray-900">
                        <i class="fas fa-sign-out-alt mr-1"></i>Logout
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <!-- Balance Card -->
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg mb-6">
            <div class="p-6 bg-gradient-to-r from-blue-500 to-blue-600">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-blue-100 text-sm font-medium">Current Balance</p>
                        <p class="text-4xl font-bold text-white">$<?= number_format($balance, 2) ?></p>
                    </div>
                    <i class="fas fa-wallet text-6xl text-white opacity-30"></i>
                </div>
            </div>
        </div>

        <!-- Messages -->
        <?php if ($message): ?>
            <div class="bg-green-50 border-l-4 border-green-400 p-4 mb-6">
                <p class="text-green-700"><?= htmlspecialchars($message) ?></p>
            </div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="bg-red-50 border-l-4 border-red-400 p-4 mb-6">
                <p class="text-red-700"><?= htmlspecialchars($error) ?></p>
            </div>
        <?php endif; ?>

        <!-- Two columns: Transfer form + Quick actions -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Transfer Money Card -->
            <div class="bg-white rounded-lg shadow p-6 md:col-span-1">
                <h2 class="text-lg font-semibold text-gray-700 mb-4 flex items-center">
                    <i class="fas fa-exchange-alt text-blue-600 mr-2"></i>Transfer Money
                </h2>
                <form method="POST" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-600">Recipient Username</label>
                        <input type="text" name="to_username" required
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600">Amount ($)</label>
                        <input type="number" step="0.01" name="amount" required
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <button type="submit" name="transfer"
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-md transition">
                        <i class="fas fa-paper-plane mr-2"></i>Send
                    </button>
                </form>
                <!-- Injection hint (remove for clean demo) -->
                <p class="text-xs text-yellow-600 mt-3 p-2 bg-yellow-50 rounded">Try: <span class="font-mono">' OR 1=1 -- </span> in username field</p>
            </div>

            <!-- Transaction History -->
            <div class="bg-white rounded-lg shadow p-6 md:col-span-2">
                <h2 class="text-lg font-semibold text-gray-700 mb-4 flex items-center">
                    <i class="fas fa-history text-blue-600 mr-2"></i>Recent Transactions
                </h2>
                <?php if ($transactions && $transactions->num_rows > 0): ?>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Description</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Amount</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php while ($row = $transactions->fetch_assoc()): ?>
                                    <?php
                                    $sign = '';
                                    $desc = '';
                                    if ($row['type'] == 'transfer') {
                                        if ($row['from_account'] == $user_id) {
                                            $sign = '-';
                                            $desc = 'Transfer to ' . htmlspecialchars($row['to_name']);
                                        } else {
                                            $sign = '+';
                                            $desc = 'Transfer from ' . htmlspecialchars($row['from_name']);
                                        }
                                    } elseif ($row['type'] == 'deposit') {
                                        $sign = '+';
                                        $desc = 'Deposit';
                                    } elseif ($row['type'] == 'withdrawal') {
                                        $sign = '-';
                                        $desc = 'Withdrawal';
                                    }
                                    ?>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?= date('M j, Y H:i', strtotime($row['transaction_date'])) ?>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-900"><?= $desc ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-medium <?= ($sign === '+') ? 'text-green-600' : 'text-red-600' ?>">
                                            <?= $sign ?>$<?= number_format($row['amount'], 2) ?>
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
        </div>
    </main>
</body>

</html>
<?php $conn->close(); ?>