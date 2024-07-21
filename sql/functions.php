<?php
require '../db.php';

// Fungsi untuk mengambil data dari tabel dengan pagination
function fetchData($table, $limit, $offset) {
    global $conn;
    $sql = "SELECT * FROM $table LIMIT ? OFFSET ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ii', $limit, $offset);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

function fetchAllData($table) {
    global $conn;
    $sql = "SELECT * FROM $table";
    $result = $conn->query($sql);
    return $result->fetch_all(MYSQLI_ASSOC);
}

function addRule($disease_code, $symptom_codes) {
    global $conn;
    $conn->begin_transaction();
    try {
        $sql = "INSERT INTO rules (disease_code) VALUES (?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('s', $disease_code);
        $stmt->execute();
        $ruleId = $stmt->insert_id;

        $symptom_codes_array = explode(',', $symptom_codes);
        foreach ($symptom_codes_array as $symptom_code) {
            $symptom_code = trim($symptom_code);
            $sql = "INSERT INTO rule_symptoms (rule_id, symptom_code) VALUES (?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('is', $ruleId, $symptom_code);
            $stmt->execute();
        }

        $conn->commit();
        return true;
    } catch (Exception $e) {
        $conn->rollback();
        return $e->getMessage();
    }
}

function updateRule($id, $disease_code, $symptom_codes) {
    global $conn;
    $conn->begin_transaction();
    try {
        $sql = "UPDATE rules SET disease_code = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('si', $disease_code, $id);
        $stmt->execute();

        $sql = "DELETE FROM rule_symptoms WHERE rule_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $id);
        $stmt->execute();

        $symptom_codes_array = explode(',', $symptom_codes);
        foreach ($symptom_codes_array as $symptom_code) {
            $symptom_code = trim($symptom_code);
            $sql = "INSERT INTO rule_symptoms (rule_id, symptom_code) VALUES (?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('is', $id, $symptom_code);
            $stmt->execute();
        }

        $conn->commit();
        return true;
    } catch (Exception $e) {
        $conn->rollback();
        return $e->getMessage();
    }
}

function deleteRule($id) {
    global $conn;
    $conn->begin_transaction();
    try {
        $sql = "DELETE FROM rule_symptoms WHERE rule_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $id);
        $stmt->execute();

        $sql = "DELETE FROM rules WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $id);
        $stmt->execute();

        $conn->commit();
        return true;
    } catch (Exception $e) {
        $conn->rollback();
        return $e->getMessage();
    }
}



// Fungsi untuk mengambil data dari tabel dengan pagination
function fetchDataWithSymptoms($limit, $offset) {
    global $conn;
    $sql = "
        SELECT r.id, r.disease_code, GROUP_CONCAT(rs.symptom_code SEPARATOR ', ') AS symptoms
        FROM rules r
        LEFT JOIN rule_symptoms rs ON r.id = rs.rule_id
        GROUP BY r.id, r.disease_code
        LIMIT ? OFFSET ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ii', $limit, $offset);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}


// Fungsi untuk mendapatkan total baris dalam tabel
function getTotalRows($table) {
    global $conn;
    $sql = "SELECT COUNT(*) FROM $table";
    $result = $conn->query($sql);
    $row = $result->fetch_row();
    return $row[0];
}

// Fungsi untuk menambah gejala
function addSymptom($code, $name) {
    global $conn;
    $sql = "INSERT INTO symptoms (code, name) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ss', $code, $name);
    return $stmt->execute() ? true : $stmt->error;
}

// Fungsi untuk memperbarui gejala
function updateSymptom($id, $code, $name) {
    global $conn;
    $sql = "UPDATE symptoms SET code = ?, name = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ssi', $code, $name, $id);
    return $stmt->execute() ? true : $stmt->error;
}

// Fungsi untuk menghapus gejala
function deleteSymptom($id) {
    global $conn;
    $sql = "DELETE FROM symptoms WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $id);
    return $stmt->execute() ? true : $stmt->error;
}

// Fungsi untuk menambah penyakit
function addDisease($code, $name, $advice) {
    global $conn;
    $sql = "INSERT INTO diseases (code, name, advice) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('sss', $code, $name, $advice);
    return $stmt->execute() ? true : $stmt->error;
}

// Fungsi untuk memperbarui penyakit
function updateDisease($id, $code, $name, $advice) {
    global $conn;
    $sql = "UPDATE diseases SET code = ?, name = ?, advice = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('sssi', $code, $name, $advice, $id);
    return $stmt->execute() ? true : $stmt->error;
}

// Fungsi untuk menghapus penyakit
function deleteDisease($id) {
    global $conn;
    $sql = "DELETE FROM diseases WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $id);
    return $stmt->execute() ? true : $stmt->error;
}

// Fungsi untuk menambah pengguna
function addUser($username, $password) {
    global $conn;
    $sql = "INSERT INTO users (username, password) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ss', $username, $password);
    return $stmt->execute() ? true : $stmt->error;
}

// Fungsi untuk memperbarui pengguna
function updateUser($id, $username, $password = null) {
    global $conn;
    if ($password) {
        $sql = "UPDATE users SET username = ?, password = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ssi', $username, $password, $id);
    } else {
        $sql = "UPDATE users SET username = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('si', $username, $id);
    }
    return $stmt->execute() ? true : $stmt->error;
}

// Fungsi untuk menghapus pengguna
function deleteUser($id) {
    global $conn;
    $sql = "DELETE FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $id);
    return $stmt->execute() ? true : $stmt->error;
}
?>
