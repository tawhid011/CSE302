<?php
session_start();
include("db_connect.php");

if (!isset($_SESSION['username'])) {
    header("Location: index.html");
    exit();
}

$role = $_SESSION['role'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Dashboard</title>
    <link rel="stylesheet" href="vendor/bootstrap/css/bootstrap.min.css">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <a class="navbar-brand" href="#">Student Portal</a>
    <div class="ml-auto text-white">
        Logged in as: <?php echo $_SESSION['username']; ?> (<?php echo $role; ?>)
        <a href="logout.php" class="btn btn-danger btn-sm ms-3">Logout</a>
    </div>
</nav>
<div class="container mt-4">
    <?php
    if ($role == 'student') {
        include("student_content.php");
    } elseif ($role == 'faculty') {
        include("faculty_content.php");
    }
    ?>
</div>
</body>
</html>
