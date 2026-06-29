<?php
session_start();
require_once 'config.php';

// Siguraduhing admin lang ang pwedeng mag-add
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo "Error: Unauthorized access.";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['product_name'];
    $category = $_POST['category'];
    $priceM = floatval($_POST['price_medium']);
    
    // Kung single size, gawing parehas ang Medium at Large price para safe sa database
    $singleSize = isset($_POST['single_size']) && $_POST['single_size'] === '1' ? 1 : 0;
    $priceL = $singleSize ? $priceM : floatval($_POST['price_large']);
    
    // Default image kapag walang in-upload
    $imagePath = 'images/logo-placeholder.png'; 
    
    // Image Upload Logic
    if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === 0) {
        $target_dir = "images/products/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        $filename = time() . '_' . basename($_FILES['product_image']['name']);
        $target_file = $target_dir . $filename;
        
        if (move_uploaded_file($_FILES['product_image']['tmp_name'], $target_file)) {
            $imagePath = $target_file;
        }
    }

    // Insert sa database (default: hindi pa bestseller, hindi pa sold out)
    $stmt = $conn->prepare("INSERT INTO products (category, product_name, price_medium, price_large, single_size, image_path, bestseller, is_sold_out) VALUES (?, ?, ?, ?, ?, ?, 0, 0)");
    $stmt->bind_param("ssddis", $category, $name, $priceM, $priceL, $singleSize, $imagePath);
    
    if ($stmt->execute()) {
        echo "success";
    } else {
        echo "Error: " . $conn->error;
    }
    $stmt->close();
}
?>