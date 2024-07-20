<?php
session_start();
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
}

include 'db.php';

// Add Symptom
if (isset($_POST['add_symptom'])) {
    $code = $_POST['symptom_code'];
    $name = $_POST['symptom_name'];
    $sql = "INSERT INTO symptoms (code, name) VALUES ('$code', '$name')";
    $conn->query($sql);
}

// Add Disease
if (isset($_POST['add_disease'])) {
    $code = $_POST['disease_code'];
    $name = $_POST['disease_name'];
    $advice = $_POST['disease_advice'];
    $sql = "INSERT INTO diseases (code, name, advice) VALUES ('$code', '$name', '$advice')";
    $conn->query($sql);
}

// Add Rule
if (isset($_POST['add_rule'])) {
    $disease_code = $_POST['disease_code'];
    $symptom_codes = implode(',', $_POST['symptom_codes']);
    $sql = "INSERT INTO rules (disease_code, symptom_codes) VALUES ('$disease_code', '$symptom_codes')";
    $conn->query($sql);
}

// Add Admin
if (isset($_POST['add_admin'])) {
    $username = $_POST['admin_username'];
    $password = password_hash($_POST['admin_password'], PASSWORD_BCRYPT);
    $sql = "INSERT INTO users (username, password, authority_level) VALUES ('$username', '$password', 0)";
    $conn->query($sql);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Panel</title>
</head>
<body>
    <h1>Welcome, <?php echo $_SESSION['username']; ?></h1>
    <a href="logout.php">Logout</a>
    
    <h2>Add Symptom</h2>
    <form method="POST" action="">
        <label>Code:</label>
        <input type="text" name="symptom_code" required>
        <br>
        <label>Name:</label>
        <input type="text" name="symptom_name" required>
        <br>
        <button type="submit" name="add_symptom">Add Symptom</button>
    </form>

    <h2>Add Disease</h2>
    <form method="POST" action="">
        <label>Code:</label>
        <input type="text" name="disease_code" required>
        <br>
        <label>Name:</label>
        <input type="text" name="disease_name" required>
        <br>
        <label>Advice:</label>
        <textarea name="disease_advice" required></textarea>
        <br>
        <button type="submit" name="add_disease">Add Disease</button>
    </form>

    <h2>Add Rule</h2>
    <form method="POST" action="">
        <label>Disease Code:</label>
        <input type="text" name="disease_code" required>
        <br>
        <label>Symptom Codes (comma separated):</label>
        <input type="text" name="symptom_codes" required>
        <br>
        <button type="submit" name="add_rule">Add Rule</button>
    </form>

    <h2>Add Admin</h2>
    <form method="POST" action="">
        <label>Username:</label>
        <input type="text" name="admin_username" required>
        <br>
        <label>Password:</label>
        <input type="password" name="admin_password" required>
        <br>
        <button type="submit" name="add_admin">Add Admin</button>
    </form>
</body>
</html>
