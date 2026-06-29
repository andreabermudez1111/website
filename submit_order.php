<?php
// submit_order.php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once 'config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error_msg'] = "Please log in first.";
    header("Location: shop.php?success=1");
exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $payment_method = $_POST['payment_method'];
    $pickup_type = $_POST['pickup_type'] ?? 'Store Pickup';
    
    // If Store Pickup, address is empty or "N/A"
    $delivery_address = ($pickup_type === 'Delivery') ? ($_POST['delivery_address'] ?? '') : 'Store Pickup';
    
    $cart_json = $_POST['cart_data'] ?? '';
    $cart_items = json_decode($cart_json, true);
    
    if (empty($cart_items)) {
        $_SESSION['error_msg'] = "Your tray is empty.";
        header("Location: shop.php");
        exit();
    }
    
    $grand_total = 0;
    foreach ($cart_items as $item) { $grand_total += floatval($item['price']); }
    
    // Receipt Upload Logic
    $receipt_filename = null;
    if ($payment_method === 'Online' && isset($_FILES['receipt_file']) && $_FILES['receipt_file']['error'] === 0) {
        $file = $_FILES['receipt_file'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $receipt_filename = time() . '_' . uniqid() . '.' . $ext;
        move_uploaded_file($file['tmp_name'], 'uploads/' . $receipt_filename);
    }
    
    // 1. Insert Main Order
    $stmt = $conn->prepare("INSERT INTO orders (user_id, total_price, payment_method, pickup_type, delivery_address, receipt_file) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("idssss", $user_id, $grand_total, $payment_method, $pickup_type, $delivery_address, $receipt_filename);
    
    if ($stmt->execute()) {

    $order_id = $conn->insert_id;

    $stmt_item = $conn->prepare(
        "INSERT INTO order_items
        (order_id, product_name, size, ice, sugar, quantity, price)
        VALUES (?, ?, ?, ?, ?, ?, ?)"
    );

    foreach ($cart_items as $item) {

        $p_name = $item['name'];
        $p_size = $item['size'] ?? 'Medium';
        $p_ice = $item['ice'] ?? 'Normal';
        $p_sugar = $item['sugar'] ?? '100%';
        $p_qty = intval($item['qty']);
        $p_price = floatval($item['price']);

        $stmt_item->bind_param(
            "issssid",
            $order_id,
            $p_name,
            $p_size,
            $p_ice,
            $p_sugar,
            $p_qty,
            $p_price
        );

        $stmt_item->execute();
    }

    $stmt_item->close();

    $conn->query("DELETE FROM user_cart WHERE user_id = $user_id");

    $userStmt = $conn->prepare(
        "SELECT username FROM users WHERE id=?"
    );

    $userStmt->bind_param("i", $user_id);
    $userStmt->execute();

    $userRes = $userStmt->get_result();
    $userData = $userRes->fetch_assoc();

    header('Content-Type: application/json');

    echo json_encode([
        "status" => "success",
        "order_id" => $order_id,
        "customer_name" => $userData['username'],
        "payment_method" => $payment_method,
        "pickup_type" => $pickup_type,
        "total_amount" => $grand_total
    ]);

    exit();

} else {

    header('Content-Type: application/json');

    echo json_encode([
        "status" => "error",
        "message" => $conn->error
    ]);

    exit();
}
}
?>