<?php
session_start();
require_once 'koneksi.php';

if (!isset($_SESSION['member_logged_in']) || $_SESSION['member_logged_in'] !== true) {
    header("Location: landing.php?error=Silakan login sebagai member terlebih dahulu.");
    exit();
}

$member_id = $_SESSION['member_id'];
$query = "SELECT members.*, instructors.name AS instructor_name, instructors.specialization 
          FROM members 
          LEFT JOIN instructors ON members.instructor_id = instructors.id 
          WHERE members.id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $member_id);
$stmt->execute();
$member = $stmt->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Member - GymFit</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="container">
    <div class="header-admin">
        <h2>Dashboard Member</h2>
        <a href="logout.php" class="btn-logout">Logout</a>
    </div>

    <div class="card-profile">
        <h3>Selamat Datang, <?= htmlspecialchars($member['full_name']) ?>!</h3>
        <p><strong>Email:</strong> <?= htmlspecialchars($member['email']) ?></p>
        <p><strong>Nomor Telepon:</strong> <?= htmlspecialchars($member['phone']) ?></p>
        <p><strong>Paket Aktif:</strong> <span class="badge"><?= htmlspecialchars($member['package']) ?></span></p>
        <p><strong>Personal Trainer:</strong> <?= $member['instructor_name'] ? htmlspecialchars($member['instructor_name']) . ' (' . htmlspecialchars($member['specialization']) . ')' : 'Belum memilih' ?></p>
        <p><strong>Tanggal Bergabung:</strong> <?= $member['created_at'] ?></p>
    </div>
</div>

</body>
</html>