<?php
require_once 'includes/db.php';

if ($_FILES['image']) {
    $file = $_FILES['image'];
    $path = "uploads/" . basename($file['name']);

    move_uploaded_file($file['tmp_name'], $path);

    $pdo = getDbConnection();
    $stmt = $pdo->prepare("UPDATE species SET image_url=? WHERE gbif_species_key=?");
    $stmt->execute([$path, $_POST['key']]);
}

header("Location: species.php?key=" . $_POST['key']);