<?php
session_start();
unset($_SESSION['diagnosis']);
unset($_SESSION['symptom_index']);
unset($_SESSION['selected_symptoms']);
header('Location: index.php');
exit();
?>
