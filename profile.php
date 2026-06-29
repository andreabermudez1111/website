// Sa profile.php (simpleng update query)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $address = $_POST['address'];
    $phone = $_POST['phone'];
    $stmt = $conn->prepare("UPDATE users SET address = ?, phone_number = ? WHERE id = ?");
    $stmt->bind_param("ssi", $address, $phone, $_SESSION['user_id']);
    $stmt->execute();
    echo "Profile updated!";
}