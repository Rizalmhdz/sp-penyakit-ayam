<?php
include 'db.php';

session_start();

// Fetch Symptoms
$symptoms_sql = "SELECT * FROM symptoms";
$symptoms_result = $conn->query($symptoms_sql);
$symptoms = $symptoms_result->fetch_all(MYSQLI_ASSOC);

if (!isset($_SESSION['symptom_index'])) {
    $_SESSION['symptom_index'] = 0;
}

$symptom_index = $_SESSION['symptom_index'];
$symptoms_to_display = array_slice($symptoms, $symptom_index, 5);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['next'])) {
        foreach ($_POST['symptom'] as $index => $value) {
            if (!empty($value)) {
                $_SESSION['selected_symptoms'][] = $value;
            }
        }

        $symptom_index += 5;
        $_SESSION['symptom_index'] = $symptom_index;

        // Check if there are more symptoms to ask
        if ($symptom_index >= count($symptoms)) {
            $symptom_index = 0;
            $_SESSION['symptom_index'] = 0;
        }
        
        // Forward Chaining Logic
        $selected_symptoms = "'" . implode("', '", $_SESSION['selected_symptoms']) . "'";
        $sql = "SELECT d.code, d.name, d.advice, COUNT(*) as symptom_count
                FROM diseases d
                JOIN rules r ON d.code = r.disease_code
                JOIN rule_symptoms rs ON r.id = rs.rule_id
                WHERE rs.symptom_code IN ($selected_symptoms)
                GROUP BY d.code
                ORDER BY symptom_count DESC
                LIMIT 1";

        $result = $conn->query($sql);
        $diagnosis = $result->fetch_assoc();

        if ($diagnosis && $diagnosis['symptom_count'] >= 5) {
            $_SESSION['diagnosis'] = [
                'name' => $diagnosis['name'],
                'advice' => $diagnosis['advice']
            ];
            header('Location: result.php');
            exit();
        }
    } elseif (isset($_POST['reset'])) {
        session_unset();
        session_destroy();
        header('Location: index.php');
        exit();
    }
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
      <h3 class="mb-0">Sistem Pakar</h3>
    </header>

    <main class="px-3">
      <h1>Diagnosa Penyakit Pada Ayam</h1>
      <form method="post" action="diagnose.php">
        <?php foreach ($symptoms_to_display as $index => $symptom): ?>
          <div class="mb-3 symptom-question">
            <p class="question">Apakah ayam anda mengalami gejala <?= $symptom['name'] ?>?</p>
            <div class="form-check form-check-inline">
              <input class="form-check-input" type="radio" name="symptom[<?= $index ?>]" id="symptom-yes-<?= $index ?>" value="<?= $symptom['code'] ?>">
              <label class="form-check-label text-dark" for="symptom-yes-<?= $index ?>">Iya</label>
            </div>
            <div class="form-check form-check-inline">
              <input class="form-check-input" type="radio" name="symptom[<?= $index ?>]" id="symptom-no-<?= $index ?>" value="">
              <label class="form-check-label text-dark" for="symptom-no-<?= $index ?>">Tidak</label>
            </div>
          </div>
        <?php endforeach; ?>
        <div class="d-flex justify-content-between">
          <button type="submit" name="reset" class="btn btn-secondary">Reset</button>
          <button type="submit" name="next" class="btn btn-primary">Next</button>
        </div>
      </form>
    </main>

    <footer class="mt-auto text-white-50">
      <p>Cover template for <a href="https://getbootstrap.com/" class="text-white">Bootstrap</a>, by <a href="https://twitter.com/mdo" class="text-white">@mdo</a>.</p>
    </footer>
  </div>
</body>
</html>
