<?php
session_start();
require 'db.php'; // File ini berisi konfigurasi database Anda

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            header('Location: admin.php');
            exit();
        } else {
            $error = 'Invalid username or password';;
            
        }
    } else {
        $error = 'Invalid username or password';
        
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="style.css" rel="stylesheet">
</head>
<body class="d-flex h-100 text-center">
  <div class="cover-container d-flex w-100 h-100 p-3 mx-auto flex-column">
    <header class="mb-auto text-center">
      <h3 class="mb-0" onclick="window.location.href = 'index.php'" style="cursor: pointer;">Sistem Pakar</h3>
      <nav class="nav nav-masthead justify-content-center mt-2">
        <?php if (isset($_SESSION['username'])): ?>
          <a class="nav-link fw-bold py-1 px-0 active" href="index.php"><?= $_SESSION['username'] ?></a>
          <a class="nav-link fw-bold py-1 px-0" href="admin.php">Kelola Data</a>
          <a class="nav-link fw-bold py-1 px-0" href="logout.php">Logout</a>
        <?php else: ?>
          <a class="nav-link fw-bold py-1 px-0 " aria-current="page" href="index.php">Home</a>
          <a class="nav-link fw-bold py-1 px-0 active" href="login.php">Login</a>
        <?php endif; ?>
      </nav>
    </header>

    <div class="card card-login">
        <div class="card-body">
            <h5 class="card-title text-center">Login</h5>
            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>
            <div class="divider"></div>
            <form method="post" action="login.php">
                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" class="form-control" id="username" name="username" placeholder="Masukkan username anda ..." required>
                </div>
                <div class="mb-5">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" placeholder="Masukkan password anda ..." required>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <div class="form-check form-check-inline">
                        <input type="checkbox" class="form-check-input" id="rememberMe">
                        <label class="form-check-label" for="rememberMe">Ingat Saya</label>
                    </div>
                    <button type="submit" class="btn btn-primary">Login</button>
                </div>
            </form>
        </div>
    </div>

    <footer class="mt-auto text-white-50">
      <p>SP - <a href="index.php" class="text-white">Daftar Penanganan Penyakit Ayam</a> @2024</p>
    </footer>
  </div>
</body>
</html>
