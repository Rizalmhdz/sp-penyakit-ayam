<?php
session_start();

// Cek apakah data diagnosis dan gejala telah diset di sesi
if (!isset($_SESSION['diagnosis']) || !isset($_SESSION['selected_symptoms'])) {
    header('Location: index.php');
    exit();
}

$diagnosis = $_SESSION['diagnosis'];
$selected_symptoms = $_SESSION['selected_symptoms'];

// Mendapatkan nama gejala dari database berdasarkan kode gejala yang dipilih
include 'db.php';
$gejala_names = [];
foreach ($selected_symptoms as $code) {
    $stmt = $conn->prepare("SELECT name FROM symptoms WHERE code = ?");
    $stmt->bind_param("s", $code);
    $stmt->execute();
    $stmt->bind_result($name);
    $stmt->fetch();
    $gejala_names[] = "$code - $name";
    $stmt->close();
}

$advice = $diagnosis['advice'];
$diag_name = $diagnosis['name'];

date_default_timezone_set('Asia/Jakarta');
$timestamp = date('Y-m-d H:i:s', time());
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
      <h3 class="mb-0" onclick="window.location.href = 'index.php'" style="cursor: pointer;">Sistem Pakar</h3>
      <nav class="nav nav-masthead justify-content-center mt-2">
        <?php if (isset($_SESSION['username'])): ?>
          <a class="nav-link fw-bold py-1 px-0 active" href="index.php"><?= htmlspecialchars($_SESSION['username']) ?></a>
          <a class="nav-link fw-bold py-1 px-0" href="admin/gejala.php">Kelola Data</a>
          <a class="nav-link fw-bold py-1 px-0" href="logout.php">Logout</a>
        <?php else: ?>
          <a class="nav-link fw-bold py-1 px-0 active" aria-current="page" href="index.php">Home</a>
          <a class="nav-link fw-bold py-1 px-0" href="login.php">Login</a>
        <?php endif; ?>
      </nav>
    </header>

    <main class="px-3">
      <h1>Hasil Diagnosa</h1>
      <div>
        <h5>Penyakit yang terdeteksi: <?= htmlspecialchars($diagnosis['name']) ?></h5>
        <p>Saran: <?= htmlspecialchars($diagnosis['advice']) ?></p>
      </div>
      <div class="d-flex justify-content-between">
        <button type="button" class="btn btn-secondary" onclick="window.location.href='clear_diagnosis.php'">Tutup</button>
        <button type="button" class="btn btn-primary" onclick="showModalOrGeneratePDF()">Cetak PDF</button>
      </div>
    </main>

    <footer class="mt-auto text-white-50">
      <p>SP - <a href="index.php" class="text-white">Daftar Penanganan Penyakit Ayam</a> @2024</p>
    </footer>
  </div>

  <!-- Modal untuk input nama -->
  <div class="modal fade" id="nameModal" tabindex="-1" aria-labelledby="nameModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title text-dark" id="nameModalLabel">Masukkan Nama Anda</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <input type="text" class="form-control" id="userName" placeholder="Nama Anda">
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-primary" onclick="generatePDF()">Cetak</button>
        </div>
      </div>
    </div>
  </div>

  <script>
  function showModalOrGeneratePDF() {
    <?php if (!isset($_SESSION['username'])): ?>
      var nameModal = new bootstrap.Modal(document.getElementById('nameModal'));
      nameModal.show();
    <?php else: ?>
      generatePDF();
    <?php endif; ?>
  }

  function generatePDF() {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF();

    // Mendapatkan input pengguna atau default ke 'User'
    var userName = document.getElementById('userName').value || 'User';
    var diagnosisName = '<?= $diag_name ?>';
    var advice = <?= json_encode($advice) ?>;
    var timestamp = new Date().toLocaleString('id-ID', { timeZone: 'Asia/Jakarta' });

    // Pengaturan margin
    const marginX = 20;
    const marginY = 20;
    const contentWidth = doc.internal.pageSize.width - 2 * marginX;

    // Posisi awal
    let y = marginY;

    // Header
    doc.setFontSize(24);
    doc.setFont('helvetica', 'bold');
    doc.text('Laporan', doc.internal.pageSize.width / 2, y, { align: 'center' });
    y += 10;
    doc.setFontSize(14);
    doc.text('Daftar Penanganan Penyakit Ayam', doc.internal.pageSize.width / 2, y, { align: 'center' });
    y += 20;

    // Informasi Pengguna
    doc.setFontSize(12);
    doc.setFont('helvetica', 'normal');
    doc.text('Nama', marginX, y);
    doc.setFont('helvetica', 'normal');
    doc.text(`:  ${userName}`, marginX + 35, y); // Mengatur titik dua agar sejajar
    y+= 5

    // Mulai border rounded
    const startY = y ; // Posisi awal border dengan margin atas

    y+= 10

    // Diagnosis
    doc.setFont('helvetica', 'bold');
    doc.text('Diagnosa', marginX, y);
    doc.setFont('helvetica', 'bold');
    doc.text(`:`, marginX + 35, y); // Mengatur titik dua agar sejajar
    doc.setFont('helvetica', 'normal');
    doc.text(`${diagnosisName}`, marginX + 38, y); // Mengatur titik dua agar sejajar
    y += 10;

    // Saran
    doc.setFont('helvetica', 'bold');
    doc.text('Saran', marginX, y);
    doc.setFont('helvetica', 'bold');
    doc.text(`:`, marginX + 35, y); // Mengatur titik dua agar sejajar
    doc.setFont('helvetica', 'normal');
    let adviceLines = doc.splitTextToSize(advice, contentWidth - 38);
    doc.text(adviceLines, marginX + 38, y); // Mengatur titik dua agar sejajar
    y += adviceLines.length * 10;

    // Gejala
    doc.setFont('helvetica', 'bold');
    doc.text('Gejala', marginX, y);
    doc.setFont('helvetica', 'bold');
    doc.text(`:`, marginX + 35, y); // Mengatur titik dua agar sejajar
    
    y += 10;
    const gejala = <?= json_encode($gejala_names); ?>;
    const colWidth = contentWidth / 2;
    let table = [];

    // Membuat format tabel untuk gejala
    const itemsPerRow = Math.ceil(gejala.length / 2);
    for (let i = 0; i < itemsPerRow; i++) {
      let row = [];
      for (let j = 0; j < 2; j++) {
        let index = i + j * itemsPerRow;
        if (index < gejala.length) {
          row.push(gejala[index]);
        }
      }
      table.push(row);
    }

    // Mencetak tabel tanpa border
    table.forEach(row => {
      row.forEach((cell, index) => {
        doc.text(cell, marginX + (index * colWidth), y);
      });
      y += 10;
    });

    // Menambahkan margin dari daftar gejala ke border bawah
    const endY = y;

    // Timestamp di luar border
    doc.setFont('helvetica', 'normal');
    doc.text(`Timestamp: ${timestamp}`, marginX, endY + 10);

    // Menambahkan border luar dengan rounded corners
    doc.setLineWidth(0.5); // Border tidak bold
    doc.roundedRect(marginX - 10, startY, doc.internal.pageSize.width - 2 * marginX + 20, endY - startY, 10, 10, 'S');

    // Menyimpan PDF
    doc.save(`diagnosis_report_${userName}.pdf`);
  }
</script>

</body>
</html>
