<?php
include "config.php";

// Check if user is logged in
if (!isset($_SESSION['uname'])) {
    header('Location: index.php');
    exit();
}

$results = [];
$debug_sql = '';
$search_term = '';
$flag2_captured = false;
$flag3_captured = false;
$flag4_captured = false;

// Process search query
if (isset($_GET['search'])) {
    $search_term = $_GET['search'];
    
    if (!empty($search_term)) {
        // Vulnerable to SQL injection - no input sanitization
        $sql = "SELECT id, username, email, personal_info FROM users WHERE username LIKE '%$search_term%' OR email LIKE '%$search_term%'";
        $debug_sql = $sql;
        
        try {
            $result = mysqli_query($conn, $sql);
            
            if ($result) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $results[] = $row;
                    
                    // Check if flag_user's personal info was found (Flag 2)
                    if ($row['personal_info'] == 'FLAG{PERSONAL_DATA_LEAKED}') {
                        $_SESSION['flag2'] = 'FLAG{PERSONAL_DATA_LEAKED}';
                        $flag2_captured = true;
                    }
                    
                    // Check if password was leaked (Flag 3)
                    if (strpos($search_term, "password") !== false && 
                        (strpos($search_term, "union") !== false || strpos($search_term, "UNION") !== false)) {
                        $_SESSION['flag3'] = 'FLAG{PASSWORD_CRACKED}';
                        $flag3_captured = true;
                    }
                    
                    // Check if MySQL version was found (Flag 4)
                    if ((strpos($search_term, "version") !== false || strpos($search_term, "VERSION") !== false) && 
                        (strpos($search_term, "union") !== false || strpos($search_term, "UNION") !== false)) {
                        $_SESSION['flag4'] = 'FLAG{MYSQL_VERSION_LEAKED}';
                        $flag4_captured = true;
                    }
                }
            }
        } catch (Exception $e) {
            $error = "SQL Error: " . mysqli_error($conn);
        }
    }
}

// Get any flags already captured
$flags_captured = [];
if (isset($_SESSION['flag1'])) $flags_captured[] = $_SESSION['flag1'];
if (isset($_SESSION['flag2'])) $flags_captured[] = $_SESSION['flag2'];
if (isset($_SESSION['flag3'])) $flags_captured[] = $_SESSION['flag3'];
if (isset($_SESSION['flag4'])) $flags_captured[] = $_SESSION['flag4'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SQL Injection Lab - Search Users</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .sql-debug {
            margin-top: 20px;
            padding: 10px;
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            font-family: monospace;
            word-break: break-all;
        }
        .hint-box {
            margin-top: 30px;
            padding: 15px;
            background-color: #e2f3e5;
            border: 1px solid #c3e6cb;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="#">SQL Injection Lab</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="home.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="search.php">Search Users</a>
                    </li>
                    <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin']): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="admin.php">Admin Panel</a>
                    </li>
                    <?php endif; ?>
                </ul>
                <span class="navbar-text me-3">
                    Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?>!
                </span>
                <a href="logout.php" class="btn btn-outline-light">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row mb-4">
            <div class="col-md-8">
                <h1>Search Users</h1>
                <p class="lead">Search for users by username or email</p>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">Captured Flags</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($flags_captured)): ?>
                            <p>You haven't captured any flags yet. Keep trying!</p>
                        <?php else: ?>
                            <ul class="list-group">
                                <?php foreach ($flags_captured as $flag): ?>
                                    <li class="list-group-item"><?php echo htmlspecialchars($flag); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card mb-4">
            <div class="card-body">
                <form method="get" action="" class="row g-3">
                    <div class="col-md-10">
                        <input type="text" name="search" class="form-control" placeholder="Search users..." value="<?php echo htmlspecialchars($search_term); ?>">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">Search</button>
                    </div>
                </form>
            </div>
        </div>
        
        <?php if (isset($_GET['search'])): ?>
            <?php if (!empty($debug_sql)): ?>
                <div class="sql-debug mb-4">
                    <p><strong>SQL Query:</strong></p>
                    <code><?php echo htmlspecialchars($debug_sql); ?></code>
                </div>
            <?php endif; ?>
            
            <?php if (empty($results)): ?>
                <div class="alert alert-info">No users found matching your search.</div>
            <?php else: ?>
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Search Results</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-striped mb-0">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Username</th>
                                        <th>Email</th>
                                        <th>Personal Info</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($results as $user): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($user['id']); ?></td>
                                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                                            <td><?php echo htmlspecialchars($user['personal_info']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>
        
        <div class="row mt-4">
            <div class="col-md-4 mb-4">
                <div class="hint-box">
                    <h5>Flag #2 Challenge:</h5>
                    <p>Can you find the hidden user with a special flag in their personal info?</p>
                    <p><strong>Hint:</strong> Try using SQL injection to expand your search beyond the normal filters.</p>
                </div>
            </div>
            
            <div class="col-md-4 mb-4">
                <div class="hint-box">
                    <h5>Flag #3 Challenge:</h5>
                    <p>Find a user's hashed password. Once you crack it, you'll reveal Flag #3.</p>
                    <p><strong>Hint:</strong> Try using a UNION query to include password column data.</p>
                </div>
            </div>
            
            <div class="col-md-4 mb-4">
                <div class="hint-box">
                    <h5>Flag #4 Challenge:</h5>
                    <p>What version of MySQL is the server running?</p>
                    <p><strong>Hint:</strong> You can use SQL functions like VERSION() to find out.</p>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>