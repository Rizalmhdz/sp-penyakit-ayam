<?php
session_start();

if (!isset($_SESSION['diagnosis'])) {
    header('Location: index.php');
    exit();
}

$diagnosis = $_SESSION['diagnosis'];

?>

<!DOCTYPE html>
<html>
<head>
    <title>Hasil Diagnosa Penyakit Pada Ayam</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.3.1/jspdf.umd.min.js"></script>
</head>
<body class="d-flex h-100 text-center">
  <div class="cover-container d-flex w-100 h-100 p-3 mx-auto flex-column">
    <header class="mb-auto text-center">
      <h3 class="mb-0">Sistem Pakar</h3>
    </header>

    <main class="px-3">
      <h1>Hasil Diagnosa</h1>
      <div>
        <h5>Penyakit yang terdeteksi: <?= $diagnosis['name'] ?></h5>
        <p>Saran: <?= $diagnosis['advice'] ?></p>
      </div>
      <div class="d-flex justify-content-between">
        <button type="button" class="btn btn-secondary" onclick="window.location.href='clear_diagnosis.php'">Tutup</button>
        <button type="button" class="btn btn-primary" onclick="generatePDF()">Cetak PDF</button>
      </div>
    </main>

    <footer class="mt-auto text-white-50">
      <p>SP - <a href="index.php" class="text-white">Daftar Penanganan Penyakit Ayam</a> @2024</p>
    </footer>
  </div>

  <script>
    function generatePDF() {
      const { jsPDF } = window.jspdf;
      const doc = new jsPDF();
      doc.text('Hasil Diagnosa Penyakit Ayam', 10, 10);
      doc.text('Penyakit: <?= $diagnosis['name'] ?>', 10, 20);
      doc.text('Saran: <?= $diagnosis['advice'] ?>', 10, 30);
      doc.save('diagnosis_report.pdf');
    }
  </script>
  
</body>
</html>
