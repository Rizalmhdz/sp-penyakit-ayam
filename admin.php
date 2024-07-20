<?php
session_start();
require 'db.php'; // File ini berisi konfigurasi database Anda

// Cek apakah pengguna sudah login
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Fungsi untuk mengambil data tabel dengan pagination
function fetchData($table, $limit, $offset) {
    global $conn;
    $sql = "SELECT * FROM $table LIMIT $limit OFFSET $offset";
    $result = $conn->query($sql);
    return $result->fetch_all(MYSQLI_ASSOC);
}

function getTotalRows($table) {
    global $conn;
    $sql = "SELECT COUNT(*) FROM $table";
    $result = $conn->query($sql);
    $row = $result->fetch_row();
    return $row[0];
}

// Jumlah data per halaman
$limit = 10;

// Mengambil data dari tabel users
$page_users = isset($_GET['page_users']) ? $_GET['page_users'] : 1;
$offset_users = ($page_users - 1) * $limit;
$users = fetchData('users', $limit, $offset_users);
$total_users = getTotalRows('users');
$total_pages_users = ceil($total_users / $limit);

// Mengambil data dari tabel symptoms
$page_symptoms = isset($_GET['page_symptoms']) ? $_GET['page_symptoms'] : 1;
$offset_symptoms = ($page_symptoms - 1) * $limit;
$symptoms = fetchData('symptoms', $limit, $offset_symptoms);
$total_symptoms = getTotalRows('symptoms');
$total_pages_symptoms = ceil($total_symptoms / $limit);

// Mengambil data dari tabel diseases
$page_diseases = isset($_GET['page_diseases']) ? $_GET['page_diseases'] : 1;
$offset_diseases = ($page_diseases - 1) * $limit;
$diseases = fetchData('diseases', $limit, $offset_diseases);
$total_diseases = getTotalRows('diseases');
$total_pages_diseases = ceil($total_diseases / $limit);

// Mengambil data dari tabel rules
$page_rules = isset($_GET['page_rules']) ? $_GET['page_rules'] : 1;
$offset_rules = ($page_rules - 1) * $limit;
$rules = fetchData('rules', $limit, $offset_rules);
$total_rules = getTotalRows('rules');
$total_pages_rules = ceil($total_rules / $limit);

?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="style.css" rel="stylesheet">
    <style>
        .nav-pills .nav-link {
            color: white;
            margin-right: 10px;
        }
        .nav-pills .nav-link.active {
            background-color: #007bff;
        }
        .card-admin {
            width: 100%;
            max-width: 800px;
            background: #fff;
            color: #000;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            margin-bottom: 20px;
        }
        .card-body {
            margin: 0 15px;
        }
        .data-info {
            position: absolute;
            top: 0;
            right: 0;
            padding: 10px;
            font-weight: bold;
        }
        .pagination {
            margin-top: 10px;
        }
        footer {
            margin-top: 20px;
        }
    </style>
</head>
<body class="d-flex h-100 text-center">
    <div class="cover-container d-flex w-100 h-100 p-3 mx-auto flex-column">
        <header class="mb-auto text-center">
            <h3 class="mb-0" onclick="window.location.href = 'index.php'" style="cursor: pointer;">Sistem Pakar</h3>
            <nav class="nav nav-masthead justify-content-center mt-2">
                <a class="nav-link fw-bold py-1 px-0" href="index.php">Home</a>
                <a class="nav-link fw-bold py-1 px-0" href="logout.php">Logout</a>
            </nav>
        </header>

        <div class="d-flex justify-content-center mb-3">
            <ul class="nav nav-pills" id="adminTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="users-tab" data-bs-toggle="pill" data-bs-target="#users" type="button" role="tab" aria-controls="users" aria-selected="true">Users</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="symptoms-tab" data-bs-toggle="pill" data-bs-target="#symptoms" type="button" role="tab" aria-controls="symptoms" aria-selected="false">Symptoms</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="diseases-tab" data-bs-toggle="pill" data-bs-target="#diseases" type="button" role="tab" aria-controls="diseases" aria-selected="false">Diseases</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="rules-tab" data-bs-toggle="pill" data-bs-target="#rules" type="button" role="tab" aria-controls="rules" aria-selected="false">Rules</button>
                </li>
            </ul>
        </div>

        <div class="card card-admin mx-auto">
            <div class="card-body">
                <div class="tab-content" id="adminTabContent">
                    <!-- Users Tab -->
                    <div class="tab-pane fade show active" id="users" role="tabpanel" aria-labelledby="users-tab">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="data-info">Menampilkan <?= count($users) ?> data dari <?= $total_users ?></span>
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">Add User</button>
                        </div>
                        <!-- Tabel Users -->
                        <table class="table mt-3">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Username</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($users) > 0): ?>
                                    <?php foreach ($users as $user): ?>
                                    <tr>
                                        <td><?= $user['id'] ?></td>
                                        <td><?= $user['username'] ?></td>
                                        <td>
                                            <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editUserModal" data-userid="<?= $user['id'] ?>" data-username="<?= $user['username'] ?>">Edit</button>
                                            <button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteUserModal" data-userid="<?= $user['id'] ?>">Delete</button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="3">No data available.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                        <!-- Pagination -->
                        <?php if ($total_users > $limit): ?>
                        <nav>
                            <ul class="pagination justify-content-center">
                                <?php for ($i = 1; $i <= $total_pages_users; $i++): ?>
                                    <li class="page-item <?= $i == $page_users ? 'active' : '' ?>">
                                        <a class="page-link" href="admin.php?page_users=<?= $i ?>"><?= $i ?></a>
                                    </li>
                                <?php endfor; ?>
                            </ul>
                        </nav>
                        <?php endif; ?>
                    </div>
                    <!-- Symptoms Tab -->
                    <div class="tab-pane fade" id="symptoms" role="tabpanel" aria-labelledby="symptoms-tab">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="data-info">Menampilkan <?= count($symptoms) ?> data dari <?= $total_symptoms ?></span>
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addSymptomModal">Add Symptom</button>
                        </div>
                        <!-- Tabel Symptoms -->
                        <table class="table mt-3">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Code</th>
                                    <th>Name</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($symptoms) > 0): ?>
                                    <?php foreach ($symptoms as $symptom): ?>
                                    <tr>
                                        <td><?= $symptom['id'] ?></td>
                                        <td><?= $symptom['code'] ?></td>
                                        <td><?= $symptom['name'] ?></td>
                                        <td>
                                            <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editSymptomModal" data-symptomid="<?= $symptom['id'] ?>" data-code="<?= $symptom['code'] ?>" data-name="<?= $symptom['name'] ?>">Edit</button>
                                            <button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteSymptomModal" data-symptomid="<?= $symptom['id'] ?>">Delete</button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4">No data available.</td>
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
                                        <a class="page-link" href="admin.php?page_symptoms=<?= $i ?>"><?= $i ?></a>
                                    </li>
                                <?php endfor; ?>
                            </ul>
                        </nav>
                        <?php endif; ?>
                    </div>
                    <!-- Diseases Tab -->
                    <div class="tab-pane fade" id="diseases" role="tabpanel" aria-labelledby="diseases-tab">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="data-info">Menampilkan <?= count($diseases) ?> data dari <?= $total_diseases ?></span>
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addDiseaseModal">Add Disease</button>
                        </div>
                        <!-- Tabel Diseases -->
                        <table class="table mt-3">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Code</th>
                                    <th>Name</th>
                                    <th>Advice</th>
                                    <th>Actions</th>
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
                                        <td>
                                            <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editDiseaseModal" data-diseaseid="<?= $disease['id'] ?>" data-code="<?= $disease['code'] ?>" data-name="<?= $disease['name'] ?>" data-advice="<?= $disease['advice'] ?>">Edit</button>
                                            <button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteDiseaseModal" data-diseaseid="<?= $disease['id'] ?>">Delete</button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5">No data available.</td>
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
                                        <a class="page-link" href="admin.php?page_diseases=<?= $i ?>"><?= $i ?></a>
                                    </li>
                                <?php endfor; ?>
                            </ul>
                        </nav>
                        <?php endif; ?>
                    </div>
                    <!-- Rules Tab -->
                    <div class="tab-pane fade" id="rules" role="tabpanel" aria-labelledby="rules-tab">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="data-info">Menampilkan <?= count($rules) ?> data dari <?= $total_rules ?></span>
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addRuleModal">Add Rule</button>
                        </div>
                        <!-- Tabel Rules -->
                        <table class="table mt-3">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Disease Code</th>
                                    <th>Symptom Code</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($rules) > 0): ?>
                                    <?php foreach ($rules as $rule): ?>
                                    <tr>
                                        <td><?= $rule['id'] ?></td>
                                        <td><?= $rule['disease_code'] ?></td>
                                        <td><?= $rule['symptom_code'] ?></td>
                                        <td>
                                            <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editRuleModal" data-ruleid="<?= $rule['id'] ?>" data-diseasecode="<?= $rule['disease_code'] ?>" data-symptomcode="<?= $rule['symptom_code'] ?>">Edit</button>
                                            <button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteRuleModal" data-ruleid="<?= $rule['id'] ?>">Delete</button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4">No data available.</td>
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
                                        <a class="page-link" href="admin.php?page_rules=<?= $i ?>"><?= $i ?></a>
                                    </li>
                                <?php endfor; ?>
                            </ul>
                        </nav>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <footer class="mt-auto text-white-50">
            <p>SP - <a href="index.php" class="text-white">Daftar Penanganan Penyakit Ayam</a> @2024</p>
        </footer>
    </div>

    <!-- Modals -->
    <!-- Add User Modal -->
    <div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addUserModalLabel">Add User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form>
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="username" name="username" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Add User</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit User Modal -->
    <div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editUserModalLabel">Edit User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form>
                        <input type="hidden" id="editUserId" name="userid">
                        <div class="mb-3">
                            <label for="editUsername" class="form-label">Username</label>
                            <input type="text" class="form-control" id="editUsername" name="username" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Update User</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete User Modal -->
    <div class="modal fade" id="deleteUserModal" tabindex="-1" aria-labelledby="deleteUserModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteUserModalLabel">Delete User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this user?</p>
                    <button type="button" class="btn btn-danger" id="confirmDeleteUserButton">Delete</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Add more modals for Symptoms, Diseases, and Rules similarly -->

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"></script>
    <script>
        function deleteData(table, id) {
            if (confirm('Are you sure you want to delete this data?')) {
                window.location.href = 'delete.php?table=' + table + '&id=' + id;
            }
        }

        // Handle showing data in modals
        var editUserModal = document.getElementById('editUserModal');
        editUserModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            var userId = button.getAttribute('data-userid');
            var username = button.getAttribute('data-username');
            var modalTitle = editUserModal.querySelector('.modal-title');
            var modalBodyInput = editUserModal.querySelector('.modal-body input#editUsername');
            var modalBodyIdInput = editUserModal.querySelector('.modal-body input#editUserId');

            modalTitle.textContent = 'Edit User ' + username;
            modalBodyInput.value = username;
            modalBodyIdInput.value = userId;
        });

        var deleteUserModal = document.getElementById('deleteUserModal');
        deleteUserModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            var userId = button.getAttribute('data-userid');
            var confirmDeleteUserButton = deleteUserModal.querySelector('.modal-body #confirmDeleteUserButton');

            confirmDeleteUserButton.onclick = function () {
                deleteData('users', userId);
            };
        });

        // Repeat similar modal handling for Symptoms, Diseases, and Rules

    </script>
</body>
</html>
