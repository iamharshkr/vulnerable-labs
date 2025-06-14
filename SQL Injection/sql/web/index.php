<?php
include "config.php";

// Check user login or not
if (isset($_SESSION['uname'])) {
    header('Location: home.php');
}

$error = '';
$debug_sql = ''; // For showing SQL query (educational purposes)

if (isset($_POST['but_submit'])) {

    $uname = $_POST['txt_uname'];
    $password = $_POST['txt_pwd'];
    if ($uname != "" && $password != "") {
        // These lines are commented out to make SQL injection possible
        // $uname = mysqli_real_escape_string($conn, $uname);
        // $password = mysqli_real_escape_string($conn, $password);

        $sql_query = "SELECT * FROM users WHERE username = '$uname' and password='$password'";
        $debug_sql = $sql_query; // Store for display
        
        try {
            $result = mysqli_query($conn, $sql_query);
            
            if ($result && mysqli_num_rows($result) > 0) {
                $row = mysqli_fetch_assoc($result);

                $_SESSION['uname'] = $row['username']; // Use actual username from DB
                $_SESSION['name'] = $row['username'];
                $_SESSION['user_id'] = $row['id'];
                $_SESSION['is_admin'] = $row['is_admin'];
                
                // If the user found the first flag, store it in session
                if ($row['is_admin']) {
                    $_SESSION['flag1'] = "FLAG{SQL_INJECTION_AUTH_BYPASS}";
                }
                
                header('Location: home.php');
                exit;
            } else {
                $error = "Invalid username and password";
            }
        } catch (Exception $e) {
            // For educational purposes, we'll show the error
            $error = "SQL Error: " . mysqli_error($conn);
        }
    } else {
        $error = "Username or password is missing";
    }
}

// Let's create the database and add flag data if it doesn't exist
$check_table = mysqli_query($conn, "SHOW TABLES LIKE 'users'");
if(mysqli_num_rows($check_table) == 0) {
    // Create users table if not exists
    $create_table = "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL,
        password VARCHAR(255) NOT NULL,
        email VARCHAR(100),
        personal_info TEXT,
        is_admin BOOLEAN DEFAULT FALSE
    )";
    mysqli_query($conn, $create_table);
    
    // Insert data including our flags
    $insert_data = "INSERT INTO users (username, password, email, personal_info, is_admin) VALUES
    ('admin', 'a#D!N@2024SqL', 'admin@example.com', 'Administrator account', TRUE),
    ('alice', 'password123', 'alice@example.com', 'Regular user account', FALSE),
    ('bob', 'bobpass', 'bob@example.com', 'Regular user account', FALSE),
    ('charlie', 'letmein', 'charlie@example.com', 'Regular user account', FALSE),
    ('flag_user', '5f4dcc3b5aa765d61d8327deb882cf99', 'flag@example.com', 'FLAG{PERSONAL_DATA_LEAKED}', FALSE),
    ('secret_user', 'e99a18c428cb38d5f260853678922e03', 'secret@example.com', 'Contains the third flag: FLAG{PASSWORD_CRACKED}', FALSE)";
    mysqli_query($conn, $insert_data);
}
?>

<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <title>SQL Injection Lab - Login</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">
    <style>
        .sql-debug {
            margin-top: 20px;
            padding: 10px;
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            font-family: monospace;
        }
        .hint-box {
            margin-top: 30px;
            padding: 15px;
            background-color: #e2f3e5;
            border: 1px solid #c3e6cb;
            border-radius: 5px;
        }
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            background-color: #f5f5f5;
        }
        .form-signin {
            width: 100%;
            max-width: 500px;
            padding: 15px;
            margin: auto;
        }
    </style>
</head>

<body>
    <main class="form-signin">
        <form action="" method="post">
            <div class="text-center mb-4">
                <img src="https://cdn-icons-png.flaticon.com/512/6195/6195700.png" alt="" width="72" height="72">
                <h1 class="h3 mb-3 fw-normal">SQL Injection Lab</h1>
            </div>

            <div class="form-floating">
                <input type="text" class="form-control" name="txt_uname" id="floatingInput" placeholder="username">
                <label for="floatingInput">Username</label>
            </div>
            <div class="form-floating my-2">
                <input type="password" name="txt_pwd" class="form-control" id="floatingPassword" placeholder="Password">
                <label for="floatingPassword">Password</label>
            </div>

            <div class="checkbox mb-3">
                <label>
                    <input type="checkbox" value="remember-me"> Remember me
                </label>
            </div>
            <?php
            if ($error) {
                echo '<div class="alert alert-danger">' . $error . '</div>';
            }
            ?>
            <button name="but_submit" class="w-100 btn btn-lg btn-primary" type="submit">Sign in</button>
            
            <?php
            // Show the SQL query for educational purposes
            if ($debug_sql) {
                echo '<div class="sql-debug">
                    <p><strong>SQL Query:</strong></p>
                    <code>' . htmlspecialchars($debug_sql) . '</code>
                </div>';
            }
            ?>
            
            <div class="hint-box">
                <h5>Flag #1 Challenge:</h5>
                <p>Can you login without knowing the correct username and password?</p>
                <!-- <p><strong>Hint:</strong> Try using <code>' OR '1'='1</code> or <code>admin'--</code> in the username field.</p> -->
            </div>
        </form>
        <p class="mt-5 mb-3 text-muted text-center">© 2025 SQL Injection Lab</p>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>