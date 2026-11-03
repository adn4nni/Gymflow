<?php
session_start();
require_once 'koneksi.php';

// Ambil data instruktur untuk ditampilkan di landing page
$instructors = $conn->query("SELECT * FROM instructors ORDER BY name ASC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GymFlow</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <!-- Header & Navigation -->
    <header class="navbar">
        <div class="logo">Gym<span>Flow</span></div>
        <nav>
            <a href="#home">Home</a>
            <a href="#packages">Paket</a>
            <a href="#instructors">Instruktur</a>
            <button class="btn-login" onclick="openModal('loginModal')">Login</button>
        </nav>
    </header>

    <!-- Hero Section -->
    <section id="home" class="hero">
        <div class="hero-content">
            <h1>Bentuk Tubuh Impianmu Sekarang</h1>
            <p>Fasilitas terbaik, instruktur profesional, dan suasana latihan yang kondusif.</p>
            <button class="btn-primary" onclick="openModal('loginModal')">Mulai Sekarang</button>
        </div>
    </section>

    <!-- Package Section -->
    <section id="packages" class="section">
        <h2>Paket Membership</h2>
        <div class="cards-container">
            <div class="card">
                <h3>Bronze</h3>
                <p class="price">Rp 250.000 / bln</p>
                <ul>
                    <li>Akses Alat Fitnes</li>
                    <li>Loker & Shower</li>
                    <li>Durasi 1 Bulan</li>
                </ul>
            </div>
            <div class="card featured">
                <h3>Silver</h3>
                <p class="price">Rp 1.200.000 / 6 bln</p>
                <ul>
                    <li>Akses Semua Fasilitas</li>
                    <li>Free Konsultasi Trainer</li>
                    <li>Durasi 6 Bulan</li>
                </ul>
            </div>
            <div class="card">
                <h3>Gold</h3>
                <p class="price">Rp 2.000.000 / thn</p>
                <ul>
                    <li>Akses 24/7 VIP</li>
                    <li>Personal Trainer Gratis</li>
                    <li>Durasi 12 Bulan</li>
                </ul>
            </div>
        </div>
    </section>

    <!-- Instructors Section -->
    <section id="instructors" class="section bg-light">
        <h2>Instruktur Kami</h2>
        <div class="cards-container">
            <?php if ($instructors && $instructors->num_rows > 0): ?>
                <?php while($inst = $instructors->fetch_assoc()): ?>
                    <div class="card">
                        <h3><?= htmlspecialchars($inst['name']) ?></h3>
                        <p class="specialization"><?= htmlspecialchars($inst['specialization']) ?></p>
                    </div>
                <?php endwhile; ?>
            <?php endif; ?>
        </div>
    </section>

    <!-- Modal Login -->
    <div id="loginModal" class="modal">
        <div class="modal-content">
            <span class="close-btn" onclick="closeModal('loginModal')">&times;</span>
            <h2>Login Sistem Gym</h2>
            
            <?php if (isset($_GET['error'])): ?>
                <p class="error-msg"><?= htmlspecialchars($_GET['error']) ?></p>
            <?php endif; ?>

            <form action="login.php" method="POST">
                <input type="text" name="username_email" placeholder="Username / Email" required>
                <div class="password-wrapper">
    <input
        type="password"
        name="password"
        id="loginPassword"
        placeholder="Password"
        required
    >

    <button
        type="button"
        id="togglePassword"
        class="toggle-password"
    >
        👁️
    </button>
</div>
                <select name="login_type" required>
                    <option value="member">Login sebagai Member</option>
                    <option value="admin">Login sebagai Admin</option>
                </select>
                <button type="submit" name="login" class="btn-submit">Masuk</button>
            </form>
        </div>
    </div>

    <script>
        <!-- Menu HIde/show -->
        const passwordInput = document.getElementById("loginPassword");
const togglePassword = document.getElementById("togglePassword");

togglePassword.addEventListener("click", function () {
    const isHidden = passwordInput.type === "password";

    passwordInput.type = isHidden ? "text" : "password";
    togglePassword.textContent = isHidden ? "🙈" : "👁️";
});
        <!-- Modal Functions -->
        function openModal(id) {
            document.getElementById(id).style.display = 'block';
        }
        function closeModal(id) {
            document.getElementById(id).style.display = 'none';
        }
        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.style.display = "none";
            }
        }
    </script>
    <?php if (isset($_GET['error'])): ?>
        <script>openModal('loginModal');</script>
    <?php endif; ?>
</body>
</html>