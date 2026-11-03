<?php
session_start();
require_once 'koneksi.php';

// PROTEKSI HALAMAN: Hanya Admin terautentikasi yang bisa masuk
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: landing.php?error=Akses Ditolak! Harap Login Admin Terlebih Dahulu.");
    exit();
}

// --- PROSES HAPUS ANGGOTA ---
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $delete_id = intval($_GET['id']);
    $stmt = $conn->prepare("DELETE FROM members WHERE id = ?");
    $stmt->bind_param("i", $delete_id);
    $stmt->execute();
    $stmt->close();
    header("Location: index.php");
    exit();
}

// --- AMBIL DATA ANGGOTA YANG AKAN DIEDIT (JIKA ADA) ---
$edit_mode = false;
$edit_data = [
    'id' => '', 'full_name' => '', 'email' => '', 'phone' => '', 'package' => '', 'instructor_id' => ''
];

if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['id'])) {
    $edit_id = intval($_GET['id']);
    $stmt = $conn->prepare("SELECT * FROM members WHERE id = ?");
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows === 1) {
        $edit_data = $res->fetch_assoc();
        $edit_mode = true;
    }
    $stmt->close();
}

// --- PROSES TAMBAH & EDIT ANGGOTA ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // 1. Aksi Tambah Anggota
    if (isset($_POST['add_member'])) {
        $full_name     = $_POST['full_name'];
        $email         = $_POST['email'];
        $phone         = $_POST['phone'];
        $package       = $_POST['package'];
        $instructor_id = !empty($_POST['instructor_id']) ? $_POST['instructor_id'] : NULL;

        try {
            $stmt = $conn->prepare("INSERT INTO members (full_name, email, phone, package, instructor_id) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssi", $full_name, $email, $phone, $package, $instructor_id);
            $stmt->execute();
            $stmt->close();
            
            header("Location: index.php");
            exit();
        } catch (mysqli_sql_exception $e) {
            if ($e->getCode() == 1062) {
                echo "<script>alert('Error: Email sudah terdaftar!'); window.location.href = 'index.php';</script>";
                exit();
            } else {
                die("Terjadi kesalahan: " . $e->getMessage());
            }
        }
    }
    
    // 2. Aksi Update/Edit Anggota
    if (isset($_POST['update_member'])) {
        $id            = intval($_POST['id']);
        $full_name     = $_POST['full_name'];
        $email         = $_POST['email'];
        $phone         = $_POST['phone'];
        $package       = $_POST['package'];
        $instructor_id = !empty($_POST['instructor_id']) ? $_POST['instructor_id'] : NULL;

        try {
            $stmt = $conn->prepare("UPDATE members SET full_name = ?, email = ?, phone = ?, package = ?, instructor_id = ? WHERE id = ?");
            $stmt->bind_param("ssssii", $full_name, $email, $phone, $package, $instructor_id, $id);
            $stmt->execute();
            $stmt->close();
            
            header("Location: index.php");
            exit();
        } catch (mysqli_sql_exception $e) {
            if ($e->getCode() == 1062) {
                echo "<script>alert('Error: Email sudah digunakan oleh anggota lain!'); window.location.href = 'index.php?action=edit&id=".$id."';</script>";
                exit();
            } else {
                die("Terjadi kesalahan: " . $e->getMessage());
            }
        }
    }
}

// Ambil data instruktur & data anggota untuk tabel
$instructors = $conn->query("SELECT * FROM instructors ORDER BY name ASC");
$query = "SELECT members.*, instructors.name AS instructor_name 
          FROM members 
          LEFT JOIN instructors ON members.instructor_id = instructors.id 
          ORDER BY members.id DESC";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Gym Management</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* Penambahan style minor untuk tombol edit & batal */
        .btn-edit {
            display: inline-block;
            padding: 6px 12px;
            background-color: #ffc107;
            color: #212529;
            text-decoration: none;
            border-radius: 4px;
            font-size: 13px;
            font-weight: bold;
            margin-right: 5px;
        }
        .btn-edit:hover { background-color: #ffca2c; }
        .btn-cancel {
            background-color: #6c757d !important;
        }
        .btn-cancel:hover { background-color: #5c636a !important; }
        .form-title { margin-bottom: 15px; color: #0d6efd; }
    </style>
</head>
<body>

<div class="container">
    <div class="header-admin">
        <h2>Panel Admin - Kelola Anggota Gym</h2>
        <div>
            <span>Halo, <strong><?= htmlspecialchars($_SESSION['admin_name']) ?></strong></span>
            <a href="logout.php" class="btn-logout">Logout</a>
        </div>
    </div>
    
    <!-- Judul Form Dinamis -->
    <h3 class="form-title"><?= $edit_mode ? "Edit Data Anggota (ID: ".$edit_data['id'].")" : "Tambah Anggota Baru" ?></h3>
    
    <form action="index.php" method="POST">
        <!-- Hidden input untuk menyimpan ID saat mode edit -->
        <?php if ($edit_mode): ?>
            <input type="hidden" name="id" value="<?= $edit_data['id'] ?>">
        <?php endif; ?>

        <input type="text" name="full_name" placeholder="Nama Lengkap" value="<?= htmlspecialchars($edit_data['full_name']) ?>" required>
        <input type="email" name="email" placeholder="Email" value="<?= htmlspecialchars($edit_data['email']) ?>" required>
        <input type="text" name="phone" placeholder="Nomor Telepon" value="<?= htmlspecialchars($edit_data['phone']) ?>" required>
        
        <select name="package" required>
            <option value="">Pilih Paket Membership</option>
            <option value="Bronze (1 Bulan)" <?= $edit_data['package'] == 'Bronze (1 Bulan)' ? 'selected' : '' ?>>Bronze (1 Bulan)</option>
            <option value="Silver (6 Bulan)" <?= $edit_data['package'] == 'Silver (6 Bulan)' ? 'selected' : '' ?>>Silver (6 Bulan)</option>
            <option value="Gold (12 Bulan)" <?= $edit_data['package'] == 'Gold (12 Bulan)' ? 'selected' : '' ?>>Gold (12 Bulan)</option>
        </select>

        <select name="instructor_id">
            <option value="">-- Pilih Instruktur / Personal Trainer --</option>
            <?php if ($instructors && $instructors->num_rows > 0): ?>
                <?php while($inst = $instructors->fetch_assoc()): ?>
                    <option value="<?= $inst['id'] ?>" <?= $edit_data['instructor_id'] == $inst['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($inst['name']) ?> (<?= htmlspecialchars($inst['specialization']) ?>)
                    </option>
                <?php endwhile; ?>
            <?php endif; ?>
        </select>

        <!-- Tombol Dinamis -->
        <?php if ($edit_mode): ?>
            <button type="submit" name="update_member" style="grid-column: span 1;">Simpan Perubahan</button>
            <button type="button" class="btn-cancel" onclick="window.location.href='index.php';" style="grid-column: span 1;">Batal Edit</button>
        <?php else: ?>
            <button type="submit" name="add_member">Tambah Anggota</button>
        <?php endif; ?>
    </form>

    <h2>Daftar Anggota Terdaftar</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nama</th>
                <th>Email</th>
                <th>Telepon</th>
                <th>Paket</th>
                <th>Instruktur</th>
                <th>Tanggal Daftar</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result && $result->num_rows > 0): ?>
                <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['id'] ?></td>
                        <td><?= htmlspecialchars($row['full_name']) ?></td>
                        <td><?= htmlspecialchars($row['email']) ?></td>
                        <td><?= htmlspecialchars($row['phone']) ?></td>
                        <td><?= htmlspecialchars($row['package']) ?></td>
                        <td><?= $row['instructor_name'] ? htmlspecialchars($row['instructor_name']) : '<em>Tidak ada</em>' ?></td>
                        <td><?= $row['created_at'] ?></td>
                        <td class="action-cell">
                            <!-- Link tombol Edit Baru -->
                            <a href="index.php?action=edit&id=<?= $row['id'] ?>" class="btn-edit">Edit</a>
                            
                            <a href="index.php?action=delete&id=<?= $row['id'] ?>" 
                               class="btn-delete" 
                               onclick="return confirm('Yakin hapus anggota ini?');">
                               Hapus
                            </a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="8" style="text-align: center;">Belum ada anggota terdaftar.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

</body>
</html>
