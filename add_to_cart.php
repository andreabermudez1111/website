<?php
session_start();
require_once 'config.php';
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please log in to add items.']);
    exit();
}

$user_id = $_SESSION['user_id'];
$action = isset($_POST['action']) ? $_POST['action'] : 'load';

// ==========================================
// ACTION: ADD TO CART
// ==========================================
if ($action === 'add') {
    $name = trim($_POST['name']);
    $size = trim($_POST['size']);
    $qty = intval($_POST['qty']);
    $ice = trim($_POST['ice']);
    $sugar = isset($_POST['sugar']) ? trim($_POST['sugar']) : '100%';
    $price = floatval($_POST['price']);

    $check = $conn->prepare("SELECT id, quantity FROM user_cart WHERE user_id = ? AND product_name = ? AND size = ? AND ice = ? AND sugar = ?");
    $check->bind_param("issss", $user_id, $name, $size, $ice, $sugar);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $new_qty = $row['quantity'] + $qty;
        $update = $conn->prepare("UPDATE user_cart SET quantity = ? WHERE id = ?");
        $update->bind_param("ii", $new_qty, $row['id']);
        $update->execute();
        $update->close();
    } else {
        $insert = $conn->prepare("INSERT INTO user_cart (user_id, product_name, size, ice, sugar, quantity, price) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $insert->bind_param("issssii", $user_id, $name, $size, $ice, $sugar, $qty, $price);
        $insert->execute();
        $insert->close();
    }
    $check->close();
}

// ==========================================
// ACTION: REMOVE FROM CART
// ==========================================
if ($action === 'remove' && isset($_POST['index'])) {
    $cart_id = intval($_POST['index']);
    $delete = $conn->prepare("DELETE FROM user_cart WHERE id = ? AND user_id = ?");
    $delete->bind_param("ii", $cart_id, $user_id);
    $delete->execute();
    $delete->close();
}

// ==========================================
// GENERATE CART UI & DATA
// ==========================================
// 1. Fetch Cart Items
$fetch = $conn->prepare("SELECT * FROM user_cart WHERE user_id = ? ORDER BY added_at ASC");
$fetch->bind_param("i", $user_id);
$fetch->execute();
$cart_items = $fetch->get_result()->fetch_all(MYSQLI_ASSOC);
$fetch->close();

// 2. Fetch User Profile Info for Delivery Address
$user_query = $conn->prepare("SELECT address, phone_number FROM users WHERE id = ?");
$user_query->bind_param("i", $user_id);
$user_query->execute();
$user_info = $user_query->get_result()->fetch_assoc();
$user_query->close();

$totalItems = 0;
$grandTotal = 0;
$html = '';
$cartDataArray = [];

if (count($cart_items) === 0) {
    $html = '<p class="empty-msg" style="color: var(--muted-gray); text-align: center; margin-top: 40px; font-family: \'Inter\', sans-serif;">Your active layout tray is empty.</p>';
} else {
    foreach ($cart_items as $item) {
        $totalItems += $item['quantity'];
        $itemTotal = $item['price']; 
        $grandTotal += $itemTotal;
        
        $html .= '<div class="cart-item-row" style="padding: 14px 0;">';
        $html .= '<div style="display:flex; justify-content:space-between; margin-bottom: 4px;">';
        $html .= '<strong>' . $item['quantity'] . 'x ' . htmlspecialchars($item['product_name']) . '</strong>';
        $html .= '<span>₱' . number_format($itemTotal, 2) . '</span>';
        $html .= '</div>';
        $html .= '<div style="font-size:12px; color:#666; margin-bottom: 8px;">Size: ' . $item['size'] . ' | Ice: ' . $item['ice'] . ' | Sugar: ' . $item['sugar'] . '</div>';
        $html .= '<button class="cart-item-delete-btn" style="background: none; color: #A64B2A; font-weight: 600; cursor: pointer; font-size: 12px; text-transform: uppercase;" onclick="removeCartItem(' . $item['id'] . ')">✕ Remove</button>';
        $html .= '</div>';
        
        $cartDataArray[] = [
            'name' => $item['product_name'],
            'size' => $item['size'],
            'ice' => $item['ice'],
            'sugar' => $item['sugar'],
            'qty' => $item['quantity'],
            'price' => $itemTotal
        ];
    }
    
    // Grand Total Section
    $html .= '<div style="margin-top: 20px; padding-top: 15px; display: flex; justify-content: space-between; align-items: center; font-weight: 700; font-size: 16px; color: var(--dark-charcoal);">';
    $html .= '<span>Total:</span>';
    $html .= '<span>₱' . number_format($grandTotal, 2) . '</span>';
    $html .= '</div>';

}

echo json_encode([
    'success' => true,
    'totalItems' => $totalItems,
    'html' => $html,
    'cartData' => json_encode($cartDataArray)
]);
?>