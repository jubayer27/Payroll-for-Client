<?php
session_start();
require_once "config/db.php";

$msg = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $username = trim($_POST["username"]);
    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);
    $role = $_POST["role"]; // admin or staff

    // Check duplicate username/email
    $check = $conn->prepare("SELECT id FROM employees_users WHERE username=? OR email=? LIMIT 1");
    $check->execute([$username, $email]);

    if ($check->fetch()) {
        $error = "Username or email already exists!";
    } else {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        // Insert into merged table
        $stmt = $conn->prepare("
            INSERT INTO employees_users 
            (username, email, password_hash, role, status, created_at)
            VALUES (?, ?, ?, ?, 'active', NOW())
        ");

        $stmt->execute([
            $username,
            $email,
            $password_hash,
            $role
        ]);

        $msg = "Account created successfully. You may now login.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Register - HRM System</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

<div class="container d-flex justify-content-center mt-5">
    <div class="card shadow p-4" style="max-width: 450px; width:100%;">

        <h3 class="text-center mb-3">Create an Account</h3>

        <?php if ($msg): ?>
            <div class="alert alert-success py-2"><?= $msg ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger py-2"><?= $error ?></div>
        <?php endif; ?>

        <form method="POST">

            <div class="mb-3">
                <label class="form-label">Username</label>
                <input required name="username" class="form-control">
            </div>

            <div class="mb-3">
                <label class="form-label">Email</label>
                <input required type="email" name="email" class="form-control">
            </div>

            <div class="mb-3">
                <label class="form-label">Password</label>
                <input required type="password" name="password" class="form-control">
            </div>

            <div class="mb-3">
                <label class="form-label">Role</label>
                <select name="role" class="form-control">
                    <option value="staff">Staff</option>
                    <option value="admin">Admin</option>
                </select>
            </div>

            <button class="btn btn-success w-100">Register</button>

            <div class="text-center mt-3">
                <a href="index.php">Back to Login</a>
            </div>
        </form>
    </div>
</div>

</body>
</html>
