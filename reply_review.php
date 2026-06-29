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

if (
    !isset($_POST['id']) ||
    !isset($_POST['admin_reply'])
) {
    header("Location: reviews.php");
    exit;
}

$id = intval($_POST['id']);

$reply = trim($_POST['admin_reply']);

$stmt = $conn->prepare(
    "UPDATE reviews
     SET admin_reply = ?
     WHERE id = ?"
);

$stmt->bind_param(
    "si",
    $reply,
    $id
);

$stmt->execute();

$stmt->close();

header("Location: reviews.php");
exit;
?>