<?php
include 'db.php';

session_start();

// Fetch Symptoms
$symptoms_sql = "SELECT * FROM symptoms";
$symptoms_result = $conn->query($symptoms_sql);
$symptoms = $symptoms_result->fetch_all(MYSQLI_ASSOC);

?>

<!DOCTYPE html>
<html>
<head>
    <title>Diagnosa Penyakit Pada Ayam</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"></script>
</head>
<body class="d-flex h-100 text-center">
  <div class="cover-container d-flex w-100 h-100 p-3 mx-auto flex-column">
    <header class="mb-auto text-center">
      <h3 class="mb-0">Sistem Pakar</h3>
      <nav class="nav nav-masthead justify-content-center mt-2">
        <a class="nav-link fw-bold py-1 px-0 active" aria-current="page" href="#">Home</a>
        <?php if (isset($_SESSION['username'])): ?>
          <a class="nav-link fw-bold py-1 px-0" href="#"><?= $_SESSION['username'] ?></a>
          <a class="nav-link fw-bold py-1 px-0" href="logout.php">Logout</a>
        <?php else: ?>
          <a class="nav-link fw-bold py-1 px-0" href="login.php">Login</a>
        <?php endif; ?>
      </nav>
    </header>

    <main class="px-3">
      <h1>Daftar Penanganan Penyakit Ayam</h1>
      <p class="lead">Membantu mendiagnosa dan memberikan saran untuk ternak ayam anda agar lebih sehat.</p>
      <p class="lead">
        <a href="diagnose.php" class="btn btn-lg btn-light fw-bold text-dark hover-dark">Mulai Pemeriksaan</a>
      </p>
    </main>

    <footer class="mt-auto text-white-50">
      <p>Cover template for <a href="https://getbootstrap.com/" class="text-white">Bootstrap</a>, by <a href="https://twitter.com/mdo" class="text-white">@mdo</a>.</p>
    </footer>
  </div>
</body>
</html>
