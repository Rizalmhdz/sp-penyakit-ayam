<?php
session_start();
require '../sql/functions.php'; // Memuat fungsi dari file terpisah

// Cek apakah pengguna sudah login
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

// Jumlah data per halaman
$limit = 10;

// Mengambil data dari tabel diseases
$page_diseases = isset($_GET['page_diseases']) ? intval($_GET['page_diseases']) : 1;
$offset_diseases = ($page_diseases - 1) * $limit;
$diseases = fetchData('diseases', $limit, $offset_diseases);
$total_diseases = getTotalRows('diseases');
$total_pages_diseases = ceil($total_diseases / $limit);

// Proses tambah penyakit
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_disease'])) {
    $code = filter_input(INPUT_POST, 'code', FILTER_SANITIZE_STRING);
    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
    $advice = filter_input(INPUT_POST, 'advice', FILTER_SANITIZE_STRING);
    $medicine = filter_input(INPUT_POST, 'medicine', FILTER_SANITIZE_STRING);
    $result = addDisease($code, $name, $advice, $medicine);
    $message = $result === true ? "Penyakit $code berhasil ditambahkan." : "Gagal menambahkan penyakit $code: " . $result;
    $page_diseases = ceil(($total_diseases + 1) / $limit); // Menghitung halaman yang akan memuat data baru
    $_SESSION['message'] = $message;
    $_SESSION['message_type'] = $result === true ? 'success' : 'danger';
    header("Location: penyakit.php?page_diseases=$page_diseases");
    exit();
}

// Proses edit penyakit
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_disease'])) {
    $id = filter_input(INPUT_POST, 'diseaseid', FILTER_VALIDATE_INT);
    $code = filter_input(INPUT_POST, 'code', FILTER_SANITIZE_STRING);
    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
    $advice = filter_input(INPUT_POST, 'advice', FILTER_SANITIZE_STRING);
    $medicine = filter_input(INPUT_POST, 'medicine', FILTER_SANITIZE_STRING);
    $result = updateDisease($id, $code, $name, $advice, $medicine);
    $message = $result === true ? "Penyakit $code berhasil diperbarui." : "Gagal memperbarui penyakit $code: " . $result;
    $_SESSION['message'] = $message;
    $_SESSION['message_type'] = $result === true ? 'success' : 'danger';
    header("Location: penyakit.php?page_diseases=$page_diseases");
    exit();
}

// Proses hapus penyakit
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_disease'])) {
    $id = filter_input(INPUT_POST, 'diseaseid', FILTER_VALIDATE_INT);
    $result = deleteDisease($id);
    if ($page_diseases > 1 && $offset_diseases >= $total_diseases - 1) {
        $page_diseases--;
    }
    $message = $result === true ? "Penyakit berhasil dihapus." : "Gagal menghapus penyakit: " . $result;
    $_SESSION['message'] = $message;
    $_SESSION['message_type'] = $result === true ? 'success' : 'danger';
    header("Location: penyakit.php?page_diseases=$page_diseases");
    exit();
}

// Cek pesan dari operasi sebelumnya
$message = isset($_SESSION['message']) ? $_SESSION['message'] : null;
$message_type = isset($_SESSION['message_type']) ? $_SESSION['message_type'] : null;
unset($_SESSION['message']);
unset($_SESSION['message_type']);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Penyakit</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../style.css" rel="stylesheet">
</head>
<body class="d-flex h-100 text-center">
    <div class="cover-container d-flex w-100 h-100 p-3 mx-auto flex-column">
        <header class="mb-auto text-center">
            <h3 class="mb-0" onclick="window.location.href = '../index.php'" style="cursor: pointer;">Sistem Pakar</h3>
            <nav class="nav nav-masthead justify-content-center mt-2">
                <?php if (isset($_SESSION['username'])): ?>
                <a class="nav-link fw-bold py-1 px-0" href="../index.php"><?= $_SESSION['username'] ?></a>
                <a class="nav-link fw-bold py-1 px-0 active" href="penyakit.php">Kelola Data</a>
                <a class="nav-link fw-bold py-1 px-0" href="../logout.php">Logout</a>
                <?php else: ?>
                <a class="nav-link fw-bold py-1 px-0 active" aria-current="page" href="../index.php">Home</a>
                <a class="nav-link fw-bold py-1 px-0" href="../login.php">Login</a>
                <?php endif; ?>
            </nav>
        </header>

        <div class="d-flex justify-content-center mb-3 mt-3">
            <ul class="nav nav-pills" id="adminTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <a class="nav-link text-white" href="pengguna.php">Pengguna</a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link text-white" href="gejala.php">Gejala</a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link active text-white" href="penyakit.php">Penyakit</a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link text-white" href="aturan.php">Aturan</a>
                </li>
            </ul>
        </div>

        <div class="card card-admin mx-auto">
            <div class="card-body">
                <?php if ($message): ?>
                <div class="modal fade" id="messageModal" tabindex="-1" aria-labelledby="messageModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header justify-content-center">
                                <h5 class="modal-title" id="messageModalLabel">Informasi</h5>
                            </div>
                            <div class="modal-body text-center">
                                <p class="mb-0"><?= htmlspecialchars($message) ?></p>
                            </div>
                            <div class="modal-footer justify-content-center">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="data-info">Menampilkan <?= count($diseases) ?> data dari <?= $total_diseases ?></span>
                    <button type="button" class="btn btn-primary ms-3" data-bs-toggle="modal" data-bs-target="#addDiseaseModal">Tambah Penyakit</button>
                </div>
                <!-- Tabel Diseases -->
                <table class="table mt-3">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Kode</th>
                            <th>Nama</th>
                            <th>Advice</th>
                            <th>Obat</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($diseases) > 0): ?>
                            <?php foreach ($diseases as $disease): ?>
                            <tr>
                                <td><?= $disease['id'] ?></td>
                                <td><?= $disease['code'] ?></td>
                                <td><?= $disease['name'] ?></td>
                                <td><?= $disease['advice'] ?></td>
                                <td><?= $disease['medicine'] ?></td>
                                <td class="btn-action">
                                    <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editDiseaseModal" data-diseaseid="<?= $disease['id'] ?>" data-code="<?= $disease['code'] ?>" data-name="<?= $disease['name'] ?>" data-advice="<?= $disease['advice'] ?>" data-medicine="<?= $disease['medicine'] ?>">Edit</button>
                                    <button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteDiseaseModal" data-diseaseid="<?= $disease['id'] ?>" data-code="<?= $disease['code'] ?>">Hapus</button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                                <td colspan="6">Tidak ada data tersedia.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
                <!-- Pagination -->
                <?php if ($total_diseases > $limit): ?>
                <nav>
                    <ul class="pagination justify-content-center">
                        <?php for ($i = 1; $i <= $total_pages_diseases; $i++): ?>
                            <li class="page-item <?= $i == $page_diseases ? 'active' : '' ?>">
                                <a class="page-link" href="penyakit.php?page_diseases=<?= $i ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
                <?php endif; ?>
            </div>
        </div>

        <footer class="mt-auto text-white-50">
            <p>SP - <a href="../index.php" class="text-white">Daftar Penanganan Penyakit Ayam</a> @2024</p>
        </footer>
    </div>

    <!-- Modals -->
    <!-- Add Disease Modal -->
    <div class="modal fade" id="addDiseaseModal" tabindex="-1" aria-labelledby="addDiseaseModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-dark" id="addDiseaseModalLabel">Tambah Penyakit</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="post" action="penyakit.php?page_diseases=<?= $page_diseases ?>">
                        <div class="mb-3">
                            <label for="code" class="form-label text-dark">Kode</label>
                            <input type="text" class="form-control" id="code" name="code" required>
                        </div>
                        <div class="mb-3">
                            <label for="name" class="form-label text-dark">Nama</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="advice" class="form-label text-dark">Advice</label>
                            <textarea class="form-control" id="advice" name="advice" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="medicine" class="form-label text-dark">Obat</label>
                            <textarea class="form-control" id="medicine" name="medicine" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary" name="add_disease">Tambah Penyakit</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Disease Modal -->
    <div class="modal fade" id="editDiseaseModal" tabindex="-1" aria-labelledby="editDiseaseModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-dark" id="editDiseaseModalLabel">Edit Penyakit</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="post" action="penyakit.php?page_diseases=<?= $page_diseases ?>">
                        <input type="hidden" id="editDiseaseId" name="diseaseid">
                        <div class="mb-3">
                            <label for="editCode" class="form-label text-dark">Kode</label>
                            <input type="text" class="form-control" id="editCode" name="code" required>
                        </div>
                        <div class="mb-3">
                            <label for="editName" class="form-label text-dark">Nama</label>
                            <input type="text" class="form-control" id="editName" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="editAdvice" class="form-label text-dark">Advice</label>
                            <textarea class="form-control" id="editAdvice" name="advice" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="editMedicine" class="form-label text-dark">Obat</label>
                            <textarea class="form-control" id="editMedicine" name="medicine" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary" name="edit_disease">Perbarui Penyakit</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Disease Modal -->
    <div class="modal fade" id="deleteDiseaseModal" tabindex="-1" aria-labelledby="deleteDiseaseModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-dark" id="deleteDiseaseModalLabel">Hapus Penyakit</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="post" action="penyakit.php?page_diseases=<?= $page_diseases ?>">
                        <p class="text-dark">Anda yakin ingin menghapus penyakit dengan kode <span id="deleteDiseaseCode"></span>?</p>
                        <input type="hidden" id="deleteDiseaseId" name="diseaseid">
                        <button type="submit" class="btn btn-danger" name="delete_disease">Hapus</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"></script>
    <script>
        function deleteData(table, id) {
            if (confirm('Anda yakin ingin menghapus data ini?')) {
                window.location.href = 'delete.php?table=' + table + '&id=' + id;
            }
        }

        // Handle showing data in modals
        var editDiseaseModal = document.getElementById('editDiseaseModal');
        editDiseaseModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            var diseaseId = button.getAttribute('data-diseaseid');
            var code = button.getAttribute('data-code');
            var name = button.getAttribute('data-name');
            var advice = button.getAttribute('data-advice');
            var medicine = button.getAttribute('data-medicine');
            var modalTitle = editDiseaseModal.querySelector('.modal-title');
            var modalBodyInputCode = editDiseaseModal.querySelector('.modal-body input#editCode');
            var modalBodyInputName = editDiseaseModal.querySelector('.modal-body input#editName');
            var modalBodyInputAdvice = editDiseaseModal.querySelector('.modal-body textarea#editAdvice');
            var modalBodyInputMedicine = editDiseaseModal.querySelector('.modal-body textarea#editMedicine');
            var modalBodyIdInput = editDiseaseModal.querySelector('.modal-body input#editDiseaseId');

            modalTitle.textContent = 'Edit Penyakit ' + name;
            modalBodyInputCode.value = code;
            modalBodyInputName.value = name;
            modalBodyInputAdvice.value = advice;
            modalBodyInputMedicine.value = medicine;
            modalBodyIdInput.value = diseaseId;
        });

        var deleteDiseaseModal = document.getElementById('deleteDiseaseModal');
        deleteDiseaseModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            var diseaseId = button.getAttribute('data-diseaseid');
            var code = button.getAttribute('data-code');
            var modalBodyCode = deleteDiseaseModal.querySelector('.modal-body #deleteDiseaseCode');
            var modalBodyIdInput = deleteDiseaseModal.querySelector('.modal-body input#deleteDiseaseId');

            modalBodyCode.textContent = code;
            modalBodyIdInput.value = diseaseId;
        });

        // Automatically show the message modal if there is a message
        <?php if ($message): ?>
        var messageModal = new bootstrap.Modal(document.getElementById('messageModal'));
        messageModal.show();
        <?php endif; ?>
    </script>
</body>
</html>
