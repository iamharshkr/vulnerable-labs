<?php
$page_title = 'Transactions - SecureBank';
$breadcrumbs = [
    ['name' => 'Home', 'url' => 'index.php'],
    ['name' => 'Dashboard', 'url' => 'dashboard.php'],
    ['name' => 'Transactions']
];

require_once 'includes/header.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

// VULNERABILITY: IDOR for transaction viewing
$transaction_id = $_GET['id'] ?? null;
$user_id = $_GET['user_id'] ?? $_SESSION['user_id'];

if ($transaction_id) {
    // View specific transaction - VULNERABLE to IDOR
    $stmt = $pdo->prepare("
        SELECT t.*, 
               a1.account_number as from_account_num,
               a2.account_number as to_account_num,
               u1.username as from_user,
               u2.username as to_user
        FROM transactions t
        JOIN accounts a1 ON t.from_account = a1.id
        JOIN accounts a2 ON t.to_account = a2.id
        JOIN users u1 ON a1.user_id = u1.id
        JOIN users u2 ON a2.user_id = u2.id
        WHERE t.id = ?
    ");
    $stmt->execute([$transaction_id]);
    $transaction = $stmt->fetch(PDO::FETCH_ASSOC);
} else {
    // List user transactions
    $stmt = $pdo->prepare("
        SELECT t.*, 
               a1.account_number as from_account_num,
               a2.account_number as to_account_num,
               u1.username as from_user,
               u2.username as to_user
        FROM transactions t
        JOIN accounts a1 ON t.from_account = a1.id
        JOIN accounts a2 ON t.to_account = a2.id
        JOIN users u1 ON a1.user_id = u1.id
        JOIN users u2 ON a2.user_id = u2.id
        WHERE a1.user_id = ? OR a2.user_id = ?
        ORDER BY t.transaction_date DESC
        LIMIT 20
    ");
    $stmt->execute([$user_id, $user_id]);
    $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<div class="max-w-6xl mx-auto">
    <?php if ($transaction_id && $transaction): ?>
        <!-- Single Transaction View -->
        <div class="bg-white rounded-xl shadow-lg p-8 mb-8">
            <div class="flex justify-between items-start mb-6">
                <h2 class="text-2xl font-bold text-gray-800">Transaction Details</h2>
                <div class="text-right">
                    <span class="text-sm text-gray-500">Transaction ID</span>
                    <p class="font-mono text-lg">#<?php echo htmlspecialchars($transaction['id']); ?></p>
                </div>
            </div>
            
            <!-- Flag for IDOR vulnerability -->
            <?php if (strpos($transaction['memo'], 'FLAG{') !== false): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                    <strong>🎉 Flag Found!</strong> You've successfully exploited an IDOR vulnerability!<br>
                    <code class="bg-green-200 px-2 py-1 rounded"><?php echo htmlspecialchars($transaction['memo']); ?></code>
                </div>
            <?php endif; ?>
            
            <div class="grid md:grid-cols-2 gap-8">
                <div class="space-y-4">
                    <div class="p-4 bg-red-50 rounded-lg">
                        <h3 class="font-semibold text-red-800 mb-2">From Account</h3>
                        <p class="text-sm text-gray-600">Account: <?php echo htmlspecialchars($transaction['from_account_num']); ?></p>
                        <p class="text-sm text-gray-600">User: <?php echo htmlspecialchars($transaction['from_user']); ?></p>
                    </div>
                    
                    <div class="p-4 bg-blue-50 rounded-lg">
                        <h3 class="font-semibold text-blue-800 mb-2">Transaction Info</h3>
                        <p class="text-sm text-gray-600">Date: <?php echo date('M j, Y g:i A', strtotime($transaction['transaction_date'])); ?></p>
                        <p class="text-sm text-gray-600">Amount: <span class="font-bold text-green-600">$<?php echo number_format($transaction['amount'], 2); ?></span></p>
                    </div>
                </div>
                
                <div class="space-y-4">
                    <div class="p-4 bg-green-50 rounded-lg">
                        <h3 class="font-semibold text-green-800 mb-2">To Account</h3>
                        <p class="text-sm text-gray-600">Account: <?php echo htmlspecialchars($transaction['to_account_num']); ?></p>
                        <p class="text-sm text-gray-600">User: <?php echo htmlspecialchars($transaction['to_user']); ?></p>
                    </div>
                    
                    <?php if (!empty($transaction['memo'])): ?>
                    <div class="p-4 bg-yellow-50 rounded-lg">
                        <h3 class="font-semibold text-yellow-800 mb-2">Memo</h3>
                        <p class="text-sm text-gray-600"><?php echo htmlspecialchars($transaction['memo']); ?></p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="mt-6 flex space-x-4">
                <a href="transactions.php" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition duration-200">
                    ← Back to Transactions
                </a>
            </div>
        </div>
        
    <?php elseif ($transaction_id && !$transaction): ?>
        <!-- Transaction Not Found -->
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-8">
            <strong>Transaction not found!</strong> Transaction ID <?php echo htmlspecialchars($transaction_id); ?> does not exist.
        </div>
        
    <?php else: ?>
        <!-- Transaction List -->
        <div class="bg-white rounded-xl shadow-lg p-8">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold text-gray-800">Transaction History</h2>
                <div class="flex space-x-2">
                    <input type="number" id="transaction_id" placeholder="Transaction ID" 
                           class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    <button onclick="viewTransaction()" 
                            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm transition duration-200">
                        View Transaction
                    </button>
                </div>
            </div>
            
            <!-- Vulnerability Testing Panel -->
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
                <h3 class="font-semibold text-yellow-800 mb-2">🎯 IDOR Testing</h3>
                <p class="text-sm text-yellow-700 mb-3">Try different transaction IDs to find hidden transactions:</p>
                <div class="flex space-x-2">
                    <button onclick="testTransaction(12340)" class="bg-yellow-600 text-white px-3 py-1 rounded text-sm hover:bg-yellow-700">Test ID: 12340</button>
                    <button onclick="testTransaction(12341)" class="bg-yellow-600 text-white px-3 py-1 rounded text-sm hover:bg-yellow-700">Test ID: 12341</button>
                    <button onclick="testTransaction(12342)" class="bg-yellow-600 text-white px-3 py-1 rounded text-sm hover:bg-yellow-700">Test ID: 12342</button>
                </div>
            </div>
            
            <?php if (!empty($transactions)): ?>
                <div class="overflow-x-auto">
                    <table class="w-full table-auto">
                        <thead>
                            <tr class="bg-gray-50">
                                <th class="px-4 py-3 text-left text-sm font-semibold">ID</th>
                                <th class="px-4 py-3 text-left text-sm font-semibold">Date</th>
                                <th class="px-4 py-3 text-left text-sm font-semibold">From</th>
                                <th class="px-4 py-3 text-left text-sm font-semibold">To</th>
                                <th class="px-4 py-3 text-left text-sm font-semibold">Amount</th>
                                <th class="px-4 py-3 text-left text-sm font-semibold">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php foreach ($transactions as $trans): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 text-sm font-mono"><?php echo $trans['id']; ?></td>
                                <td class="px-4 py-3 text-sm"><?php echo date('M j, Y', strtotime($trans['transaction_date'])); ?></td>
                                <td class="px-4 py-3 text-sm"><?php echo htmlspecialchars($trans['from_user']); ?></td>
                                <td class="px-4 py-3 text-sm"><?php echo htmlspecialchars($trans['to_user']); ?></td>
                                <td class="px-4 py-3 text-sm font-bold text-green-600">$<?php echo number_format($trans['amount'], 2); ?></td>
                                <td class="px-4 py-3 text-sm">
                                    <a href="transactions.php?id=<?php echo $trans['id']; ?>" 
                                       class="text-blue-600 hover:text-blue-800 text-sm">View Details</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-8">
                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-gray-800 mb-2">No Transactions Found</h3>
                    <p class="text-gray-600">You don't have any transactions yet.</p>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<script>
function viewTransaction() {
    const transactionId = document.getElementById('transaction_id').value;
    if (transactionId) {
        window.location.href = `transactions.php?id=${transactionId}`;
    } else {
        alert('Please enter a transaction ID');
    }
}

function testTransaction(id) {
    window.location.href = `transactions.php?id=${id}`;
}

// API testing functionality
function testAPI() {
    fetch('api/user-data.php?action=transactions&user_id=2')
        .then(response => response.json())
        .then(data => {
            console.log('API Response:', data);
            if (data.success) {
                alert('API access successful! Check console for details.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
}
</script>

</main>
</body>
</html>
