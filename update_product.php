<?php

include 'config.php';

$id = intval($_POST['id']);
$name = trim($_POST['product_name']);
$priceM = floatval($_POST['price_medium']);
$priceL = floatval($_POST['price_large']);

$stmt = $conn->prepare("
UPDATE products
SET
    product_name = ?,
    price_medium = ?,
    price_large = ?
WHERE id = ?
");

$stmt->bind_param(
    "sddi",
    $name,
    $priceM,
    $priceL,
    $id
);

if($stmt->execute()){
    echo "success";
}else{
    echo "error";
}
