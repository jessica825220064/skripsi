<?php
session_start();
include('koneksi.php'); 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE username = ?");
    mysqli_stmt_bind_param($stmt, 's', $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($result && mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);

        if (password_verify($password, $user['password'])) {
            $_SESSION['username'] = $username;
            $_SESSION['loggedin'] = true;
            
            header('Location: dashboard.html'); 
            exit;
        } else {
            echo "<script>alert('Password salah!'); window.location.href='login.html';</script>";
        }
    } else {
        echo "<script>alert('Username tidak ditemukan!'); window.location.href='login.html';</script>";
    }
} else {
    header('Location: login.html');
    exit;
}
mysqli_close($conn);
?>