<?php
session_start();
include 'config.php';

/* Admin only */
if (
    !isset($_SESSION['role']) ||
    $_SESSION['role'] !== 'admin'
) {
    header("Location: reviews.php");
    exit;
}

if (!isset($_GET['id'])) {
    header("Location: reviews.php");
    exit;
}

$id = intval($_GET['id']);

$stmt = $conn->prepare(
    "DELETE FROM reviews WHERE id = ?"
);

$stmt->bind_param("i", $id);

$stmt->execute();

$stmt->close();

header("Location: reviews.php");
exit;
?>