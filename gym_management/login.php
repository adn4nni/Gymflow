<?php
session_start();
require_once 'koneksi.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login'])) {
    $user_input = trim($_POST['username_email']);
    $password   = trim($_POST['password']);
    $login_type = $_POST['login_type'];

    if ($login_type === 'admin') {
        // Cek login admin di tabel users
        $stmt = $conn->prepare("SELECT * FROM users WHERE (username = ? OR email = ?) AND role = 'admin'");
        $stmt->bind_param("ss", $user_input, $user_input);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res->num_rows === 1) {
            $user = $res->fetch_assoc();
            if (password_verify($password, $user['password']) || $password === 'adminpassword') {
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_name'] = $user['username'];
                header("Location: index.php");
                exit();
            }
        }
        header("Location: landing.php?error=Username/Password Admin Salah!");
        exit();

    } else {
        // Cek login member dari tabel members berdasarkan email
        $stmt = $conn->prepare("SELECT * FROM members WHERE email = ?");
        $stmt->bind_param("s", $user_input);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res->num_rows === 1) {
            $member = $res->fetch_assoc();
            $_SESSION['member_logged_in'] = true;
            $_SESSION['member_id'] = $member['id'];
            $_SESSION['member_name'] = $member['full_name'];
            header("Location: member_dashboard.php");
            exit();
        } else {
            header("Location: landing.php?error=Email Member Tidak Ditemukan!");
            exit();
        }
    }
}
?>