<?php
// index.php

// 1. Server Configuration
ini_set('session.gc_maxlifetime', 2592000); 
session_set_cookie_params(2592000);

session_start();
require_once "./config/db.php";

$error = "";
$redirect = $_REQUEST['redirect'] ?? null;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST["username"]);
    $password = trim($_POST["password"]);

    $stmt = $conn->prepare("SELECT * FROM employees_users WHERE username = ? LIMIT 1");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user["password_hash"])) {

        session_regenerate_id(true);

        $_SESSION["user_id"] = $user["id"];
        $_SESSION["employee_id"] = $user["id"];
        $_SESSION["name"] = $user["first_name"]; // Store name for dashboard
        $_SESSION["username"] = $user["username"];
        $_SESSION["user_role"] = $user["role"];

        // Remember Me Logic
        if (isset($_POST['remember'])) {
            $params = session_get_cookie_params();
            setcookie(session_name(), session_id(), time() + (30 * 24 * 60 * 60), 
                $params["path"], $params["domain"], $params["secure"], $params["httponly"]
            );
        }

        // Redirect Logic
        $requestedRedirect = $_POST['redirect'] ?? $_GET['redirect'] ?? ($_SESSION['redirect_url'] ?? null);
        if ($requestedRedirect) {
            if (stripos($requestedRedirect, 'http://') === 0 || stripos($requestedRedirect, 'https://') === 0) {
                $requestedRedirect = null;
            } else {
                if (strpos($requestedRedirect, '/') !== 0) $requestedRedirect = '/' . ltrim($requestedRedirect, '/');
            }
        }

        if (!empty($requestedRedirect)) {
            unset($_SESSION['redirect_url']);
            header("Location: " . $requestedRedirect);
            exit;
        }

        // Role Redirects
        switch ($user["role"]) {
            case 'admin': header("Location: admin/admin_dashboard.php"); break;
            case 'attender': header("Location: attender/attender_dashboard.php"); break;
            case 'breaker': header("Location: attender/break.php"); break;
            case 'staff':
            case 'employee': header("Location: staff/index.php"); break;
            default: header("Location: staff/index.php"); break;
        }
        exit;

    } else {
        $error = "Invalid username or password";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - Staff Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .shimmy-text {
            background: linear-gradient(to right, #ffffff 20%, #fca5a5 40%, #fca5a5 60%, #ffffff 80%);
            background-size: 200% auto;
            color: #000;
            background-clip: text;
            text-fill-color: transparent;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            animation: shine 3s linear infinite;
        }
        @keyframes shine { to { background-position: 200% center; } }
    </style>
</head>
<body class="h-screen w-full flex items-center justify-center bg-gray-50">

    <div class="bg-white w-full max-w-5xl h-full md:h-[80vh] md:rounded-3xl shadow-2xl flex overflow-hidden">
        
        <div class="hidden md:flex w-1/2 bg-gradient-to-br from-red-600 via-red-700 to-red-900 p-12 flex-col justify-between relative overflow-hidden">
            
            <div class="absolute top-0 right-0 w-64 h-64 bg-white opacity-5 rounded-full blur-3xl -mr-16 -mt-16"></div>
            <div class="absolute bottom-0 left-0 w-48 h-48 bg-red-500 opacity-20 rounded-full blur-2xl -ml-10 -mb-10"></div>

            <div class="relative z-10">
                <div class="flex items-center gap-3 mb-6">
                    <div class="bg-white/20 p-2 rounded-lg backdrop-blur-sm">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                    </div>
                    <h2 class="text-xl font-bold text-white tracking-widest uppercase">Company Portal</h2>
                </div>
                <h1 class="text-5xl font-extrabold text-white mb-4 leading-tight">Welcome <br><span class="shimmy-text">Back!</span></h1>
                <p class="text-red-100 text-lg opacity-80">Manage your attendance, payroll, and leaves seamlessly.</p>
            </div>

            <div class="relative z-10 text-xs text-red-200 opacity-60">
                &copy; <?= date('Y') ?> HRM System. Secure Login.
            </div>
        </div>

        <div class="w-full md:w-1/2 bg-white p-8 md:p-12 flex flex-col justify-center">
            
            <div class="max-w-sm mx-auto w-full">
                <div class="md:hidden flex items-center justify-center mb-8">
                    <div class="bg-gradient-to-br from-red-600 to-red-800 p-3 rounded-xl shadow-lg">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                    </div>
                </div>

                <h2 class="text-3xl font-bold text-gray-900 mb-2 text-center md:text-left">Sign In</h2>
                <p class="text-gray-500 mb-8 text-center md:text-left">Please enter your credentials to access your account.</p>

                <?php if ($error): ?>
                    <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded text-sm flex items-center shadow-sm">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <?= $error ?>
                    </div>
                <?php endif; ?>

                <form method="POST" class="space-y-6">
                    <?php if (!empty($redirect)): ?>
                        <input type="hidden" name="redirect" value="<?= htmlspecialchars($redirect) ?>">
                    <?php endif; ?>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Username</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                            </span>
                            <input type="text" name="username" required 
                                   class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 transition outline-none" 
                                   placeholder="e.g. johndoe">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Password</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                            </span>
                            <input type="password" name="password" required 
                                   class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 transition outline-none" 
                                   placeholder="••••••••">
                        </div>
                    </div>

                    <div class="flex items-center justify-between">
                        <label class="flex items-center cursor-pointer">
                            <input type="checkbox" name="remember" class="w-4 h-4 text-red-600 border-gray-300 rounded focus:ring-red-500">
                            <span class="ml-2 text-sm text-gray-600">Remember me</span>
                        </label>
                        <a href="#" class="text-sm font-bold text-red-600 hover:text-red-800 transition">Forgot Password?</a>
                    </div>

                    <button type="submit" class="w-full bg-gradient-to-r from-red-600 to-red-700 text-white font-bold py-3 rounded-lg shadow-lg hover:shadow-xl hover:from-red-500 hover:to-red-600 transform hover:-translate-y-0.5 transition-all duration-200">
                        Sign In
                    </button>
                </form>

                <p class="text-center text-sm text-gray-500 mt-8">
                    Don't have an account? <a href="register.php" class="text-red-600 font-bold hover:underline">Register</a>
                </p>
            </div>
        </div>
    </div>

</body>
</html>