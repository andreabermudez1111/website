<?php

session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

$username =
    $_SESSION['username']
    ?? 'Customer';

$rating = intval($_POST['rating']);

$review_text =
    trim($_POST['review_text']);

if ($review_text === '') {
    header("Location: reviews.php");
    exit;
}

/* ==========================
   PHOTO UPLOAD
========================== */

$photoName = null;

if (
    isset($_FILES['review_photo']) &&
    $_FILES['review_photo']['error'] === 0
) {

    $ext = strtolower(
        pathinfo(
            $_FILES['review_photo']['name'],
            PATHINFO_EXTENSION
        )
    );

    $allowed = [
        'jpg',
        'jpeg',
        'png',
        'webp'
    ];

    if (in_array($ext, $allowed)) {

        // create uploads/reviews folder if missing
        if (!is_dir('uploads/reviews')) {
            mkdir('uploads/reviews', 0777, true);
        }

        $photoName =
            time() .
            '_' .
            uniqid() .
            '.' .
            $ext;

        move_uploaded_file(
            $_FILES['review_photo']['tmp_name'],
            'uploads/reviews/' . $photoName
        );
    }
}

/* ==========================
   SAVE REVIEW
========================== */

$stmt = $conn->prepare(
    "INSERT INTO reviews
    (
        user_id,
        username,
        rating,
        review_text,
        review_photo
    )
    VALUES
    (
        ?, ?, ?, ?, ?
    )"
);

$stmt->bind_param(
    "isiss",
    $user_id,
    $username,
    $rating,
    $review_text,
    $photoName
);

$stmt->execute();

$stmt->close();

header("Location: reviews.php");
exit;

?>