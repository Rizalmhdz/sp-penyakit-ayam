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

// Mengambil data dari tabel rules
$page_rules = isset($_GET['page_rules']) ? intval($_GET['page_rules']) : 1;
$offset_rules = ($page_rules - 1) * $limit;
// Mengambil data dari tabel rules dengan symptoms
$rules = fetchDataWithSymptoms($limit, $offset_rules);

$total_rules = getTotalRows('rules');
$total_pages_rules = ceil($total_rules / $limit);

// Proses tambah aturan
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_rule'])) {
    $disease_code = filter_input(INPUT_POST, 'disease_code', FILTER_SANITIZE_STRING);
    $symptoms = filter_input(INPUT_POST, 'symptoms', FILTER_SANITIZE_STRING);
    $result = addRule($disease_code, $symptoms);
    $message = $result === true ? "Aturan berhasil ditambahkan." : "Gagal menambahkan aturan: " . $result;
    $page_rules = ceil(($total_rules + 1) / $limit); // Menghitung halaman yang akan memuat data baru
    $_SESSION['message'] = $message;
    $_SESSION['message_type'] = $result === true ? 'success' : 'danger';
    header("Location: aturan.php?page_rules=$page_rules");
    exit();
}

// Proses edit aturan
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_rule'])) {
    $id = filter_input(INPUT_POST, 'ruleid', FILTER_VALIDATE_INT);
    $disease_code = filter_input(INPUT_POST, 'disease_code', FILTER_SANITIZE_STRING);
    $symptoms = filter_input(INPUT_POST, 'symptoms', FILTER_SANITIZE_STRING);
    $result = updateRule($id, $disease_code, $symptoms);
    $message = $result === true ? "Aturan berhasil diperbarui." : "Gagal memperbarui aturan: " . $result;
    $_SESSION['message'] = $message;
    $_SESSION['message_type'] = $result === true ? 'success' : 'danger';
    header("Location: aturan.php?page_rules=$page_rules");
    exit();
}

// Proses hapus aturan
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_rule'])) {
    $id = filter_input(INPUT_POST, 'ruleid', FILTER_VALIDATE_INT);
    $result = deleteRule($id);
    if ($page_rules > 1 && $offset_rules >= $total_rules - 1) {
        $page_rules--;
    }
    $message = $result === true ? "Aturan berhasil dihapus." : "Gagal menghapus aturan: " . $result;
    $_SESSION['message'] = $message;
    $_SESSION['message_type'] = $result === true ? 'success' : 'danger';
    header("Location: aturan.php?page_rules=$page_rules");
    exit();
}

// Cek pesan dari operasi sebelumnya
$message = isset($_SESSION['message']) ? $_SESSION['message'] : null;
$message_type = isset($_SESSION['message_type']) ? $_SESSION['message_type'] : null;
unset($_SESSION['message']);
unset($_SESSION['message_type']);

// Ambil data penyakit
$diseases = fetchAllData('diseases');
$symptoms = fetchAllData('symptoms');
?>

<!DOCTYPE html>
<html>
<head>
    <title>Aturan</title>
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
                <a class="nav-link fw-bold py-1 px-0 active" href="aturan.php">Kelola Data</a>
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
                    <a class="nav-link text-white" href="penyakit.php">Penyakit</a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link active text-white" href="aturan.php">Aturan</a>
                </li>
            </ul>
        </div>

        <div class="card card-admin mx-auto content-container mb-3">
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
                    <span class="data-info">Menampilkan <?= count($rules) ?> data dari <?= $total_rules ?></span>
                    <button type="button" class="btn btn-primary ms-3" data-bs-toggle="modal" data-bs-target="#addRuleModal">Tambah Aturan</button>
                </div>
                <!-- Tabel Rules -->
                <table class="table mt-3">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Kode Penyakit</th>
                            <th>Gejala</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($rules) > 0): ?>
                            <?php foreach ($rules as $rule): ?>
                            <tr>
                                <td><?= $rule['id'] ?></td>
                                <td><?= $rule['disease_code'] ?></td>
                                <td><?= $rule['symptoms'] ?></td>
                                <td class="btn-action">
                                    <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editRuleModal" data-ruleid="<?= $rule['id'] ?>" data-disease_code="<?= $rule['disease_code'] ?>" data-symptoms="<?= $rule['symptoms'] ?>">Edit</button>
                                    <button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteRuleModal" data-ruleid="<?= $rule['id'] ?>" data-disease_code="<?= $rule['disease_code'] ?>">Hapus</button>
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
                <?php if ($total_rules > $limit): ?>
                <nav>
                    <ul class="pagination justify-content-center">
                        <?php for ($i = 1; $i <= $total_pages_rules; $i++): ?>
                            <li class="page-item <?= $i == $page_rules ? 'active' : '' ?>">
                                <a class="page-link" href="aturan.php?page_rules=<?= $i ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
                <?php endif; ?>
            </div>
        </div>

        <footer class="text-white-50 mt-auto">
            <p>SP - <a href="../index.php" class="text-white">Daftar Penanganan Penyakit Ayam</a> @2024</p>
        </footer>
    </div>

    <!-- Modals -->
    <!-- Add Rule Modal -->
    <div class="modal fade" id="addRuleModal" tabindex="-1" aria-labelledby="addRuleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-dark" id="addRuleModalLabel">Tambah Aturan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="post" action="aturan.php" onsubmit="return validateCheckboxes('selectingSymptomsList')">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <label for="disease_code" class="form-label text-dark mb-0">Kode Penyakit</label>
                            <a href="penyakit.php" class="link-primary small">Kelola Penyakit</a>
                        </div>
                        <select class="form-select mt-1" id="disease_code" name="disease_code" required>
                            <?php foreach ($diseases as $disease): ?>
                                <option value="<?= $disease['code'] ?>"><?= $disease['code'] ?> - <?= $disease['name'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-0 d-flex justify-content-between align-items-center">
                        <label for="symptom_codes" class="form-label text-dark">Gejala</label>
                        <a href="gejala.php" class="link-primary small" id="selectSymptomsLink">Kelola Gejala</a>
                    </div>
                    <div class="mb-3 selected-symptoms" id="selectingSymptomsList">
                        <div class="row">
                            <?php foreach ($symptoms as $symptom): ?>
                            <div class="col-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="<?= $symptom['code'] ?>" id="symptom<?= $symptom['code'] ?>">
                                    <label class="form-check-label" for="symptom<?= $symptom['code'] ?>" style="font-size: smaller;">
                                        <?= $symptom['code'] ?> - <?= $symptom['name'] ?>
                                    </label>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <input type="hidden" name="symptoms" id="symptomCodes">
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary" name="add_rule">Tambah Aturan</button>
            </div>
            </form>
        </div>
    </div>
</div>



    <!-- Edit Rule Modal -->
    <div class="modal fade" id="editRuleModal" tabindex="-1" aria-labelledby="editRuleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-dark" id="editRuleModalLabel">Edit Aturan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="post" action="aturan.php?page_rules=<?= $page_rules ?>" onsubmit="return validateCheckboxes('selectingSymptomsListEdit')">
                        <input type="hidden" id="editRuleId" name="ruleid">
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <label for="editDiseaseCode" class="form-label text-dark mb-0">Kode Penyakit</label>
                                <a href="penyakit.php" class="link-primary small">Kelola Penyakit</a>
                            </div>
                            <select class="form-select mt-1" id="editDiseaseCode" name="disease_code" required>
                                <?php foreach ($diseases as $disease): ?>
                                    <option value="<?= $disease['code'] ?>"><?= $disease['code'] ?> - <?= $disease['name'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-0 d-flex justify-content-between align-items-center">
                            <label for="editSymptomCodes" class="form-label text-dark mb-0">Gejala</label>
                            <a href="gejala.php" class="link-primary small" id="editSelectSymptomsLink">Kelola Gejala</a>
                        </div>
                        <div class="selected-symptoms mt-2" id="selectingSymptomsListEdit">
                                <div class="row">
                                    <?php foreach ($symptoms as $symptom): ?>
                                    <div class="col-4">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" value="<?= $symptom['code'] ?>" id="editsymptom<?= $symptom['code'] ?>">
                                            <label class="form-check-label" for="editsymptom<?= $symptom['code'] ?>">
                                                <?= $symptom['code'] ?> - <?= $symptom['name'] ?>
                                            </label>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                        </div>
                        <input type="hidden" name="symptoms" id="editSymptomCodes">
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary" name="edit_rule">Perbarui Aturan</button>
                </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Rule Modal -->
    <div class="modal fade" id="deleteRuleModal" tabindex="-1" aria-labelledby="deleteRuleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-dark" id="deleteRuleModalLabel">Hapus Aturan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="post" action="aturan.php?page_rules=<?= $page_rules ?>">
                        <p class="text-dark">Anda yakin ingin menghapus aturan dengan kode penyakit <span id="deleteDiseaseCode"></span>?</p>
                        <input type="hidden" id="deleteRuleId" name="ruleid">
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-danger" name="delete_rule">Hapus</button>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        </div>
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

        // Menangani penampilan data di modals
        var editRuleModal = document.getElementById('editRuleModal');
        editRuleModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            var ruleId = button.getAttribute('data-ruleid');
            var diseaseCode = button.getAttribute('data-disease_code');
            var symptoms = button.getAttribute('data-symptoms');
            var modalTitle = editRuleModal.querySelector('.modal-title');
            var modalBodyInputDiseaseCode = editRuleModal.querySelector('.modal-body select#editDiseaseCode');
            var modalBodyIdInput = editRuleModal.querySelector('.modal-body input#editRuleId');
            var modalBodySymptomList = editRuleModal.querySelector('.modal-body #editSelectedSymptomsList');

            modalTitle.textContent = 'Edit Aturan ' + diseaseCode;
            modalBodyInputDiseaseCode.value = diseaseCode;
            modalBodyIdInput.value = ruleId;

            // Tampilkan gejala yang dipilih
            var symptomList = symptoms.split(', ');

            // Set checkbox berdasarkan gejala yang dipilih
            document.querySelectorAll('#selectingSymptomsListEdit .form-check-input').forEach(checkbox => {
                checkbox.checked = symptomList.includes(checkbox.value);
            });
        });

        var deleteRuleModal = document.getElementById('deleteRuleModal');
        deleteRuleModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            var ruleId = button.getAttribute('data-ruleid');
            var diseaseCode = button.getAttribute('data-disease_code');
            var modalBodyDiseaseCode = deleteRuleModal.querySelector('.modal-body #deleteDiseaseCode');
            var modalBodyIdInput = deleteRuleModal.querySelector('.modal-body input#deleteRuleId');

            modalBodyDiseaseCode.textContent = diseaseCode;
            modalBodyIdInput.value = ruleId;
        });

        // Validasi minimal satu checkbox dipilih
        function validateCheckboxes(containerId) {
            var checkboxes = document.querySelectorAll(`#${containerId} .form-check-input:checked`);
            if (checkboxes.length === 0) {
                alert('Pilih minimal satu gejala.');
                return false;
            }
            return true;
        }

        // Menyimpan gejala yang dipilih pada modal tambah aturan
        document.querySelector('#addRuleModal form').addEventListener('submit', function(event) {
            var selectedSymptoms = Array.from(document.querySelectorAll('#selectingSymptomsList .form-check-input:checked'))
                .map(input => input.value);
            document.getElementById('symptomCodes').value = selectedSymptoms.join(',');
        });

        // Menyimpan gejala yang dipilih pada modal edit aturan
        document.querySelector('#editRuleModal form').addEventListener('submit', function(event) {
            var selectedSymptoms = Array.from(document.querySelectorAll('#selectingSymptomsListEdit .form-check-input:checked'))
                .map(input => input.value);
            document.getElementById('editSymptomCodes').value = selectedSymptoms.join(',');
        });

        // Menampilkan modal pesan jika ada pesan
        <?php if ($message): ?>
        var messageModal = new bootstrap.Modal(document.getElementById('messageModal'));
        messageModal.show();
        <?php endif; ?>
    </script>
</body>
</html>
