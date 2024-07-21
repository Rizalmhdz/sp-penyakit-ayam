<?php
include 'db.php';

session_start();

// Inisialisasi sesi jika belum ada
if (!isset($_SESSION['symptom_index'])) {
    $_SESSION['symptom_index'] = 0;
}

if (!isset($_SESSION['selected_symptoms'])) {
    $_SESSION['selected_symptoms'] = [];
}

// Ambil semua gejala dari database
$symptoms_sql = "SELECT * FROM symptoms";
$symptoms_result = $conn->query($symptoms_sql);
$symptoms = $symptoms_result->fetch_all(MYSQLI_ASSOC);

$total_symptoms = count($symptoms);
$symptom_index = intval($_SESSION['symptom_index']);

// Slice gejala untuk ditampilkan
$symptoms_to_display = array_slice($symptoms, $symptom_index, 5);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['next'])) {
        // Simpan gejala yang dipilih
        foreach ($_POST['symptom'] as $index => $value) {
            if ($value == 'true') {
                $_SESSION['selected_symptoms'][] = $symptoms[$symptom_index + $index]['code'];
            }
        }

        // Update indeks gejala
        $_SESSION['symptom_index'] += 5;
        $symptom_index = $_SESSION['symptom_index'];

        // Jika sudah tidak ada gejala yang tersisa untuk ditampilkan
        if ($symptom_index >= $total_symptoms) {
            // Logika Forward Chaining
            $selected_symptoms = "'" . implode("', '", $_SESSION['selected_symptoms']) . "'";
            $sql = "SELECT d.code, d.name, d.advice, COUNT(*) as symptom_count
                    FROM diseases d
                    JOIN rules r ON d.code = r.disease_code
                    JOIN rule_symptoms rs ON r.id = rs.rule_id
                    WHERE rs.symptom_code IN ($selected_symptoms)
                    GROUP BY d.code, d.name, d.advice
                    ORDER BY symptom_count DESC, d.name ASC
                    LIMIT 1";

            $result = $conn->query($sql);
            $diagnosis = $result->fetch_assoc();

            if ($diagnosis) {
                $_SESSION['diagnosis'] = [
                    'name' => $diagnosis['name'],
                    'advice' => $diagnosis['advice']
                ];
                header('Location: result.php');
                exit();
            }
        }
    } elseif (isset($_POST['reset'])) {
        // Reset sesi
        session_unset();
        session_destroy();
        header('Location: index.php');
        exit();
    }

    // Refresh gejala untuk ditampilkan setelah submit
    $symptoms_to_display = array_slice($symptoms, $symptom_index, 5);
}
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
      <h3 class="mb-0" onclick="window.location.href = 'index.php'" style="cursor: pointer;">Sistem Pakar</h3>
      <nav class="nav nav-masthead justify-content-center mt-2">
        <?php if (isset($_SESSION['username'])): ?>
          <a class="nav-link fw-bold py-1 px-0 active" href="index.php"><?= $_SESSION['username'] ?></a>
          <a class="nav-link fw-bold py-1 px-0" href="admin/gejala.php">Kelola Data</a>
          <a class="nav-link fw-bold py-1 px-0" href="logout.php">Logout</a>
        <?php else: ?>
          <a class="nav-link fw-bold py-1 px-0 active" aria-current="page" href="index.php">Home</a>
          <a class="nav-link fw-bold py-1 px-0" href="login.php">Login</a>
        <?php endif; ?>
      </nav>
    </header>

    <main class="px-3">
      <h1>Diagnosa Penyakit Pada Ayam</h1>
      <form method="post">
        <?php if (!empty($symptoms_to_display)): ?>
          <?php foreach ($symptoms_to_display as $index => $symptom):?>
            <div class="mb-3 symptom-question">
              <p class="question text-white">Apakah ayam anda mengalami gejala <?= htmlspecialchars($symptom['name']) ?> (<?= htmlspecialchars($symptom['code']) ?>) ?</p>
              <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" required name="symptom[<?= $index ?>]" id="symptom-yes-<?= $index ?>" value="true"> 
                <label class="form-check-label text-white" for="symptom-yes-<?= $index ?>">Iya</label>
              </div>
              <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" required name="symptom[<?= $index ?>]" id="symptom-no-<?= $index ?>" value="false" checked>
                <label class="form-check-label text-white" for="symptom-no-<?= $index ?>">Tidak</label>
              </div>
            </div>
          <?php endforeach; ?>
          <div class="d-flex justify-content-between">
            <button type="submit" name="reset" class="btn btn-secondary">Reset</button>
            <button type="submit" name="next" class="btn btn-primary">Next</button>
          </div>
        <?php else: ?>
          <p>Tidak ada gejala yang tersedia.</p>
          <?php 
          session_unset();
          session_destroy();
          ?><div class="d-flex justify-content-center">
          <button type="submit" name="reset" class="btn btn-secondary">Reset</button>
        </div>
        <?php endif; ?>
      </form>
    </main>

    <footer class="mt-auto text-white-50">
      <p>SP - <a href="index.php" class="text-white">Daftar Penanganan Penyakit Ayam</a> @2024</p>
    </footer>
  </div>
</body>
</html>
