<?php
// logout.php
session_start();

// 1. Unset all session variables
$_SESSION = array();

// 2. Delete the Session Cookie (Crucial for "Remember Me")
// We force the path to '/' to ensure the global cookie is deleted, 
// regardless of which folder (admin/staff) the user came from.
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        '/', 
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// 3. Destroy the session
session_destroy();

// 4. Redirect to Login Page
header("Location: index.php");
exit;
?>