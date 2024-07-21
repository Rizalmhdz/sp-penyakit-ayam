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

// Mengambil data dari tabel users
$page_users = isset($_GET['page_users']) ? intval($_GET['page_users']) : 1;
$offset_users = ($page_users - 1) * $limit;
$users = fetchData('users', $limit, $offset_users);
$total_users = getTotalRows('users');
$total_pages_users = ceil($total_users / $limit);

// Proses tambah pengguna
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_user'])) {
    $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
    $password = password_hash(filter_input(INPUT_POST, 'password', FILTER_SANITIZE_STRING), PASSWORD_BCRYPT); 
    $result = addUser($username, $password);
    $message = $result === true ? "Pengguna $username berhasil ditambahkan." : "Gagal menambahkan pengguna $username: " . $result;
    $page_users = ceil(($total_users + 1) / $limit); // Menghitung halaman yang akan memuat data baru
    $_SESSION['message'] = $message;
    $_SESSION['message_type'] = $result === true ? 'success' : 'danger';
    header("Location: pengguna.php?page_users=$page_users");
    exit();
}

// Proses edit pengguna
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_user'])) {
    $id = filter_input(INPUT_POST, 'userid', FILTER_VALIDATE_INT);
    $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
    $password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_STRING);
    if (!empty($password)) {
        $password = password_hash($password, PASSWORD_BCRYPT);
    }
    $result = updateUser($id, $username, $password);
    $message = $result === true ? "Pengguna $username berhasil diperbarui." : "Gagal memperbarui pengguna $username: " . $result;
    $_SESSION['message'] = $message;
    $_SESSION['message_type'] = $result === true ? 'success' : 'danger';
    header("Location: pengguna.php?page_users=$page_users");
    exit();
}

// Proses hapus pengguna
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_user'])) {
    $id = filter_input(INPUT_POST, 'userid', FILTER_VALIDATE_INT);
    $result = deleteUser($id);
    if ($page_users > 1 && $offset_users >= $total_users - 1) {
        $page_users--;
    }
    $message = $result === true ? "Pengguna berhasil dihapus." : "Gagal menghapus pengguna: " . $result;
    $_SESSION['message'] = $message;
    $_SESSION['message_type'] = $result === true ? 'success' : 'danger';
    header("Location: pengguna.php?page_users=$page_users");
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
    <title>Pengguna</title>
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
                <a class="nav-link fw-bold py-1 px-0 active" href="pengguna.php">Kelola Data</a>
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
                    <a class="nav-link active text-white" href="pengguna.php">Pengguna</a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link text-white" href="gejala.php">Gejala</a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link text-white" href="penyakit.php">Penyakit</a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link text-white" href="aturan.php">Aturan</a>
                </li>
            </ul>
        </div>

        <div class="card card-admin mx-auto mb-3">
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
                    <span class="data-info">Menampilkan <?= count($users) ?> data dari <?= $total_users ?></span>
                    <button type="button" class="btn btn-primary ms-3" data-bs-toggle="modal" data-bs-target="#addUserModal">Tambah Pengguna</button>
                </div>
                <!-- Tabel Users -->
                <table class="table mt-3">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($users) > 0): ?>
                            <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?= $user['id'] ?></td>
                                <td><?= $user['username'] ?></td>
                                <td class="btn-action">
                                    <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editUserModal" data-userid="<?= $user['id'] ?>" data-username="<?= $user['username'] ?>">Edit</button>
                                    <button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteUserModal" data-userid="<?= $user['id'] ?>" data-username="<?= $user['username'] ?>">Hapus</button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="3">Tidak ada data tersedia.</td>
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
                                <a class="page-link" href="pengguna.php?page_users=<?= $i ?>"><?= $i ?></a>
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
    <!-- Add User Modal -->
    <div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-dark" id="addUserModalLabel">Tambah Pengguna</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="post" action="pengguna.php?page_users=<?= $page_users ?>">
                        <div class="mb-3">
                            <label for="username" class="form-label text-dark">Username</label>
                            <input type="text" class="form-control" id="username" name="username" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label text-dark">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <button type="submit" class="btn btn-primary" name="add_user">Tambah Pengguna</button>
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
                    <h5 class="modal-title text-dark" id="editUserModalLabel">Edit Pengguna</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="post" action="pengguna.php?page_users=<?= $page_users ?>">
                        <input type="hidden" id="editUserId" name="userid">
                        <div class="mb-3">
                            <label for="editUsername" class="form-label text-dark">Username</label>
                            <input type="text" class="form-control" id="editUsername" name="username" required>
                        </div>
                        <div class="mb-3">
                            <label for="editPassword" class="form-label text-dark">Password</label>
                            <input type="password" class="form-control" id="editPassword" name="password">
                            <small class="text-muted">Kosongkan jika tidak ingin mengubah password</small>
                        </div>
                        <button type="submit" class="btn btn-primary" name="edit_user">Perbarui Pengguna</button>
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
                    <h5 class="modal-title text-dark" id="deleteUserModalLabel">Hapus Pengguna</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="post" action="pengguna.php?page_users=<?= $page_users ?>">
                        <p class="text-dark">Anda yakin ingin menghapus pengguna dengan username <span id="deleteUsername"></span>?</p>
                        <input type="hidden" id="deleteUserId" name="userid">
                        <button type="submit" class="btn btn-danger" name="delete_user">Hapus</button>
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
        var editUserModal = document.getElementById('editUserModal');
        editUserModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            var userId = button.getAttribute('data-userid');
            var username = button.getAttribute('data-username');
            var modalTitle = editUserModal.querySelector('.modal-title');
            var modalBodyInputUsername = editUserModal.querySelector('.modal-body input#editUsername');
            var modalBodyIdInput = editUserModal.querySelector('.modal-body input#editUserId');

            modalTitle.textContent = 'Edit Pengguna ' + username;
            modalBodyInputUsername.value = username;
            modalBodyIdInput.value = userId;
        });

        var deleteUserModal = document.getElementById('deleteUserModal');
        deleteUserModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            var userId = button.getAttribute('data-userid');
            var username = button.getAttribute('data-username');
            var modalBodyUsername = deleteUserModal.querySelector('.modal-body #deleteUsername');
            var modalBodyIdInput = deleteUserModal.querySelector('.modal-body input#deleteUserId');

            modalBodyUsername.textContent = username;
            modalBodyIdInput.value = userId;
        });

        // Automatically show the message modal if there is a message
        <?php if ($message): ?>
        var messageModal = new bootstrap.Modal(document.getElementById('messageModal'));
        messageModal.show();
        <?php endif; ?>
    </script>
</body>
</html>
