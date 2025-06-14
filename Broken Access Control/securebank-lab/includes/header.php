<?php
require_once 'config.php';
require_once 'auth.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'SecureBank'; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../css/styles.css">
    <script>
        // Add some interactivity
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'bank-blue': '#1e40af',
                        'bank-green': '#059669',
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-50 min-h-screen">
    <!-- Navigation Bar -->
    <nav class="bg-white shadow-lg sticky top-0 z-50">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center py-4">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-gradient-to-r from-blue-600 to-purple-600 rounded-lg flex items-center justify-center">
                        <span class="text-white font-bold text-lg">SB</span>
                    </div>
                    <div>
                        <h1 class="text-xl font-bold text-gray-800">SecureBank</h1>
                        <p class="text-xs text-gray-500">Penetration Testing Lab</p>
                    </div>
                </div>
                
                <div class="flex items-center space-x-4">
                    <?php if (isLoggedIn()): ?>
                        <div class="flex items-center space-x-3">
                            <div class="text-right">
                                <p class="text-sm font-medium text-gray-800">
                                    <?php echo htmlspecialchars($_SESSION['username']); ?>
                                </p>
                                <p class="text-xs text-gray-500 capitalize">
                                    <?php echo htmlspecialchars($_SESSION['role']); ?> User
                                </p>
                            </div>
                            
                            <?php if (isAdmin()): ?>
                                <span class="bg-red-100 text-red-800 px-2 py-1 rounded-full text-xs font-medium">
                                    Admin
                                </span>
                            <?php elseif ($_SESSION['role'] === 'premium'): ?>
                                <span class="bg-purple-100 text-purple-800 px-2 py-1 rounded-full text-xs font-medium">
                                    Premium
                                </span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="flex space-x-2">
                            <a href="../dashboard.php" 
                               class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded-lg text-sm transition duration-200">
                                Dashboard
                            </a>
                            <a href="../logout.php" 
                               class="bg-red-600 hover:bg-red-700 text-white px-3 py-2 rounded-lg text-sm transition duration-200">
                                Logout
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="flex space-x-2">
                            <a href="../login.php" 
                               class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm transition duration-200">
                                Login
                            </a>
                            <a href="../index.php" 
                               class="text-gray-600 hover:text-gray-800 px-4 py-2 text-sm">
                                Home
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- Breadcrumb -->
    <?php if (isset($breadcrumbs) && !empty($breadcrumbs)): ?>
    <div class="bg-gray-100 border-b">
        <div class="container mx-auto px-4 py-2">
            <nav class="flex" aria-label="Breadcrumb">
                <ol class="flex items-center space-x-2 text-sm">
                    <?php foreach ($breadcrumbs as $index => $crumb): ?>
                        <li class="flex items-center">
                            <?php if ($index > 0): ?>
                                <svg class="w-4 h-4 text-gray-400 mx-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                                </svg>
                            <?php endif; ?>
                            
                            <?php if (isset($crumb['url'])): ?>
                                <a href="<?php echo $crumb['url']; ?>" 
                                   class="text-blue-600 hover:text-blue-800">
                                    <?php echo htmlspecialchars($crumb['name']); ?>
                                </a>
                            <?php else: ?>
                                <span class="text-gray-500">
                                    <?php echo htmlspecialchars($crumb['name']); ?>
                                </span>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ol>
            </nav>
        </div>
    </div>
    <?php endif; ?>

    <!-- Main Content Container -->
    <main class="container mx-auto px-4 py-6">
