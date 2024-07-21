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

// Mengambil data dari tabel symptoms
$page_symptoms = isset($_GET['page_symptoms']) ? intval($_GET['page_symptoms']) : 1;
$offset_symptoms = ($page_symptoms - 1) * $limit;
$symptoms = fetchData('symptoms', $limit, $offset_symptoms);
$total_symptoms = getTotalRows('symptoms');
$total_pages_symptoms = ceil($total_symptoms / $limit);

// Proses tambah gejala
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_symptom'])) {
    $code = filter_input(INPUT_POST, 'code', FILTER_SANITIZE_STRING);
    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
    $result = addSymptom($code, $name);
    $message = $result === true ? "Gejala $code berhasil ditambahkan." : "Gagal menambahkan gejala $code: " . $result;
    $page_symptoms = ceil(($total_symptoms + 1) / $limit); // Menghitung halaman yang akan memuat data baru
    $_SESSION['message'] = $message;
    $_SESSION['message_type'] = $result === true ? 'success' : 'danger';
    header("Location: gejala.php?page_symptoms=$page_symptoms");
    exit();
}

// Proses edit gejala
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_symptom'])) {
    $id = filter_input(INPUT_POST, 'symptomid', FILTER_VALIDATE_INT);
    $code = filter_input(INPUT_POST, 'code', FILTER_SANITIZE_STRING);
    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
    $result = updateSymptom($id, $code, $name);
    $message = $result === true ? "Gejala $code berhasil diperbarui." : "Gagal memperbarui gejala $code: " . $result;
    $_SESSION['message'] = $message;
    $_SESSION['message_type'] = $result === true ? 'success' : 'danger';
    header("Location: gejala.php?page_symptoms=$page_symptoms");
    exit();
}

// Proses hapus gejala
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_symptom'])) {
    $id = filter_input(INPUT_POST, 'symptomid', FILTER_VALIDATE_INT);
    $result = deleteSymptom($id);
    if ($page_symptoms > 1 && $offset_symptoms >= $total_symptoms - 1) {
        $page_symptoms--;
    }
    $message = $result === true ? "Gejala berhasil dihapus." : "Gagal menghapus gejala: " . $result;
    $_SESSION['message'] = $message;
    $_SESSION['message_type'] = $result === true ? 'success' : 'danger';
    header("Location: gejala.php?page_symptoms=$page_symptoms");
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
    <title>Gejala</title>
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
                <a class="nav-link fw-bold py-1 px-0 active" href="gejala.php">Kelola Data</a>
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
                    <a class="nav-link active text-white" href="gejala.php">Gejala</a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link text-white" href="penyakit.php">Penyakit</a>
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
                    <span class="data-info">Menampilkan <?= count($symptoms) ?> data dari <?= $total_symptoms ?></span>
                    <button type="button" class="btn btn-primary ms-3" data-bs-toggle="modal" data-bs-target="#addSymptomModal">Tambah Gejala</button>
                </div>
                <!-- Tabel Symptoms -->
                <table class="table mt-3">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Kode</th>
                            <th>Nama</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($symptoms) > 0): ?>
                            <?php foreach ($symptoms as $symptom): ?>
                            <tr>
                                <td><?= $symptom['id'] ?></td>
                                <td><?= $symptom['code'] ?></td>
                                <td><?= $symptom['name'] ?></td>
                                <td class="btn-action">
                                    <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editSymptomModal" data-symptomid="<?= $symptom['id'] ?>" data-code="<?= $symptom['code'] ?>" data-name="<?= $symptom['name'] ?>">Edit</button>
                                    <button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteSymptomModal" data-symptomid="<?= $symptom['id'] ?>" data-code="<?= $symptom['code'] ?>">Hapus</button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4">Tidak ada data tersedia.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
                <!-- Pagination -->
                <?php if ($total_symptoms > $limit): ?>
                <nav>
                    <ul class="pagination justify-content-center">
                        <?php for ($i = 1; $i <= $total_pages_symptoms; $i++): ?>
                            <li class="page-item <?= $i == $page_symptoms ? 'active' : '' ?>">
                                <a class="page-link" href="gejala.php?page_symptoms=<?= $i ?>"><?= $i ?></a>
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
    <!-- Add Symptom Modal -->
    <div class="modal fade" id="addSymptomModal" tabindex="-1" aria-labelledby="addSymptomModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-dark" id="addSymptomModalLabel">Tambah Gejala</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="post" action="gejala.php?page_symptoms=<?= $page_symptoms ?>">
                        <div class="mb-3">
                            <label for="code" class="form-label text-dark">Kode</label>
                            <input type="text" class="form-control" id="code" name="code" required>
                        </div>
                        <div class="mb-3">
                            <label for="name" class="form-label text-dark">Nama</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <button type="submit" class="btn btn-primary" name="add_symptom">Tambah Gejala</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Symptom Modal -->
    <div class="modal fade" id="editSymptomModal" tabindex="-1" aria-labelledby="editSymptomModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-dark" id="editSymptomModalLabel">Edit Gejala</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="post" action="gejala.php?page_symptoms=<?= $page_symptoms ?>">
                        <input type="hidden" id="editSymptomId" name="symptomid">
                        <div class="mb-3">
                            <label for="editCode" class="form-label text-dark">Kode</label>
                            <input type="text" class="form-control" id="editCode" name="code" required>
                        </div>
                        <div class="mb-3">
                            <label for="editName" class="form-label text-dark">Nama</label>
                            <input type="text" class="form-control" id="editName" name="name" required>
                        </div>
                        <button type="submit" class="btn btn-primary" name="edit_symptom">Perbarui Gejala</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Symptom Modal -->
    <div class="modal fade" id="deleteSymptomModal" tabindex="-1" aria-labelledby="deleteSymptomModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-dark" id="deleteSymptomModalLabel">Hapus Gejala</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="post" action="gejala.php?page_symptoms=<?= $page_symptoms ?>">
                        <p class="text-dark">Anda yakin ingin menghapus gejala dengan kode <span id="deleteSymptomCode"></span>?</p>
                        <input type="hidden" id="deleteSymptomId" name="symptomid">
                        <button type="submit" class="btn btn-danger" name="delete_symptom">Hapus</button>
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
        var editSymptomModal = document.getElementById('editSymptomModal');
        editSymptomModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            var symptomId = button.getAttribute('data-symptomid');
            var code = button.getAttribute('data-code');
            var name = button.getAttribute('data-name');
            var modalTitle = editSymptomModal.querySelector('.modal-title');
            var modalBodyInputCode = editSymptomModal.querySelector('.modal-body input#editCode');
            var modalBodyInputName = editSymptomModal.querySelector('.modal-body input#editName');
            var modalBodyIdInput = editSymptomModal.querySelector('.modal-body input#editSymptomId');

            modalTitle.textContent = 'Edit Gejala ' + name;
            modalBodyInputCode.value = code;
            modalBodyInputName.value = name;
            modalBodyIdInput.value = symptomId;
        });

        var deleteSymptomModal = document.getElementById('deleteSymptomModal');
        deleteSymptomModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            var symptomId = button.getAttribute('data-symptomid');
            var code = button.getAttribute('data-code');
            var modalBodyCode = deleteSymptomModal.querySelector('.modal-body #deleteSymptomCode');
            var modalBodyIdInput = deleteSymptomModal.querySelector('.modal-body input#deleteSymptomId');

            modalBodyCode.textContent = code;
            modalBodyIdInput.value = symptomId;
        });

        // Automatically show the message modal if there is a message
        <?php if ($message): ?>
        var messageModal = new bootstrap.Modal(document.getElementById('messageModal'));
        messageModal.show();
        <?php endif; ?>
    </script>
</body>
</html>
