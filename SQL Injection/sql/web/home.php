<?php
include "config.php";

// Check if user is logged in
if (!isset($_SESSION['uname'])) {
    header('Location: index.php');
    exit();
}

$username = $_SESSION['uname'];
$name = $_SESSION['name'];
$is_admin = isset($_SESSION['is_admin']) ? $_SESSION['is_admin'] : 0;

// Check if the user has captured any flags
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
    <title>SQL Injection Lab - Home</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
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
                        <a class="nav-link active" href="home.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="search.php">Search Users</a>
                    </li>
                    <?php if ($is_admin): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="admin.php">Admin Panel</a>
                    </li>
                    <?php endif; ?>
                </ul>
                <span class="navbar-text me-3">
                    Welcome, <?php echo htmlspecialchars($name); ?>!
                </span>
                <a href="logout.php" class="btn btn-outline-light">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row">
            <div class="col-md-8">
                <h1>Welcome to the SQL Injection Lab</h1>
                
                <div class="card mt-4">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">Challenge Overview</h4>
                    </div>
                    <div class="card-body">
                        <p>This lab contains four SQL injection vulnerabilities you need to exploit:</p>
                        <ol>
                            <li><strong>Flag 1:</strong> Bypass authentication on the login page</li>
                            <li><strong>Flag 2:</strong> Extract personal information of a specific user</li>
                            <li><strong>Flag 3:</strong> Find a user's hashed password and crack it (the password is "abc123")</li>
                            <li><strong>Flag 4:</strong> Discover the MySQL version of the database</li>
                        </ol>
                        <p>Each flag you capture will be displayed in the panel on the right.</p>
                        <div class="alert alert-info">
                            <strong>Hint:</strong> Try the "Search Users" page to find more vulnerabilities!
                        </div>
                    </div>
                </div>
                
                <div class="card mt-4">
                    <div class="card-header bg-info text-white">
                        <h4 class="mb-0">SQL Injection Techniques</h4>
                    </div>
                    <div class="card-body">
                        <p>Here are some SQL injection techniques you might find useful:</p>
                        <ul>
                            <li><strong>Basic Authentication Bypass:</strong> <code>' OR '1'='1</code></li>
                            <li><strong>Comment-based Bypass:</strong> <code>admin'--</code></li>
                            <li><strong>UNION-based Attacks:</strong> <code>' UNION SELECT column1, column2, column3, column4 FROM table --</code></li>
                            <li><strong>Extracting Database Information:</strong> <code>' UNION SELECT 1, 2, 3, VERSION() --</code></li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card sticky-top" style="top: 20px">
                    <div class="card-header bg-success text-white">
                        <h4 class="mb-0">Captured Flags</h4>
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
                            
                            <?php if (count($flags_captured) == 4): ?>
                                <div class="alert alert-success mt-3">
                                    <strong>Congratulations!</strong> You've captured all the flags!
                                </div>
                            <?php else: ?>
                                <div class="alert alert-warning mt-3">
                                    <strong>Keep going!</strong> You've found <?php echo count($flags_captured); ?> out of 4 flags.
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>