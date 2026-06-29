<?php

include 'config.php';

$id = intval($_GET['id']);

$stmt = $conn->prepare("
SELECT *
FROM products
WHERE id = ?
");

$stmt->bind_param("i",$id);
$stmt->execute();

$result = $stmt->get_result();

header('Content-Type: application/json');

echo json_encode(
    $result->fetch_assoc()
);