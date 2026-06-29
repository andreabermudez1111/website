<?php
session_start();
require_once 'config.php';

// Siguraduhing admin lang ang makakagawa nito
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo "Error: Unauthorized access.";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = intval($_POST['id']);

    // Kunin ang image path bago burahin para matanggal din sa folder natin (Optional but recommended)
    $imgStmt = $conn->prepare("SELECT image_path FROM products WHERE id = ?");
    $imgStmt->bind_param("i", $id);
    $imgStmt->execute();
    $result = $imgStmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $imagePath = $row['image_path'];
        // Wag burahin ang placeholder image para hindi mag-error sa iba
        if ($imagePath && $imagePath !== 'images/logo-placeholder.png' && file_exists($imagePath)) {
            unlink($imagePath); // Binubura ang picture sa folder
        }
    }
    $imgStmt->close();

    // Burahin ang product sa database
    $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        echo "success";
    } else {
        echo "Error deleting product: " . $conn->error;
    }
    $stmt->close();
} else {
    echo "Invalid request.";
}
?>