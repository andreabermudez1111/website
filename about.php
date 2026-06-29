<?php 
include 'header.php'; 

$activeTab = isset($_GET['tab']) ? $_GET['tab'] : 'story';

// ==========================================
// ADMIN LOGIC: UPLOAD CUSTOMER GALLERY IMAGE
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'upload_gallery') {
    if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
        $target_dir = "images/customers/";
        
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $filename = time() . '_' . basename($_FILES["gallery_image"]["name"]);
        $target_file = $target_dir . $filename;
        
        if (move_uploaded_file($_FILES["gallery_image"]["tmp_name"], $target_file)) {
            $uploadSuccessMsg = "Customer photo added to gallery successfully!";
        } else {
            $uploadErrorMsg = "Sorry, there was an error uploading your file.";
        }
    }
}

// ==========================================
// ADMIN LOGIC: DELETE REVIEW
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_review') {
    if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
        $review_id = intval($_POST['review_id']);
        
        $delStmt = $conn->prepare("DELETE FROM reviews WHERE id = ?");
        $delStmt->bind_param("i", $review_id);
        
        if ($delStmt->execute()) {
            $feedbackMessage = "<div class='success-alert'>Review deleted successfully.</div>";
        } else {
            $feedbackMessage = "<div class='error-alert'>Failed to delete review.</div>";
        }
        $delStmt->close();
    }
}

// ==========================================
// ADMIN LOGIC: REPLY TO REVIEW
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'reply_review') {
    if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
        $review_id = intval($_POST['review_id']);
        $admin_reply = trim($_POST['admin_reply']);
        
        $replyStmt = $conn->prepare("UPDATE reviews SET admin_reply = ? WHERE id = ?");
        $replyStmt->bind_param("si", $admin_reply, $review_id);
        
        if ($replyStmt->execute()) {
            $feedbackMessage = "<div class='success-alert'>Admin reply posted successfully!</div>";
        } else {
            $feedbackMessage = "<div class='error-alert'>Failed to post reply.</div>";
        }
        $replyStmt->close();
    }
}

// ==========================================
// USER LOGIC: SUBMIT REVIEW
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'submit_review') {
    $rating = intval($_POST['rating']);
    $review_text = trim($_POST['review_text']);
    
    $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
    $username = isset($_SESSION['username']) ? $_SESSION['username'] : (!empty($_POST['anon_name']) ? trim($_POST['anon_name']) : 'Anonymous Guest');

    if (!empty($review_text) && $rating >= 1 && $rating <= 5) {
        $stmt = $conn->prepare("INSERT INTO reviews (user_id, username, rating, review_text) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isis", $user_id, $username, $rating, $review_text);
        if ($stmt->execute()) {
            $feedbackMessage = "<div class='success-alert'>Thank you for posting your review!</div>";
        } else {
            $feedbackMessage = "<div class='error-alert'>Error saving feedback.</div>";
        }
        $stmt->close();
    }
}

// FETCH REVIEWS
$reviewsList = [];
$res = $conn->query("SELECT * FROM reviews ORDER BY created_at DESC");
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $reviewsList[] = $row;
    }
}
?>

<style>   
    .about-layout-container { max-width: 1200px; margin: 0 auto; padding: 20px 4%; }
    .review-card { padding: 24px 20px; border-bottom: 1px solid var(--border-color); margin-bottom: 15px; position: relative; }
    
    /* USER FORM CONFIGURATIONS */
    .add-feedback-form { margin-top: 50px; padding: 30px; background: var(--bg-warm-creme); border-radius: 8px; }
    .add-feedback-form textarea, .add-feedback-form input, .add-feedback-form select { 
        width: 100%; 
        box-sizing: border-box; 
        padding: 12px; 
        margin: 10px 0; 
        border: 1px solid var(--border-color); 
        border-radius: 4px; 
    }
    
    .faq-row { padding-bottom: 20px; margin-bottom: 20px; border-bottom: 1px solid var(--border-color); }
    .faq-row:last-child { border-bottom: none; margin-bottom: 0; padding-bottom: 0; }

    /* GALLERY CLASSES - UPDATED FOR LARGER SIZE */
    .gallery-img { width: 100%; height: 240px; object-fit: cover; border-radius: 8px; box-shadow: 0 4px 15px rgba(43,29,20,0.08); transition: transform 0.3s ease; cursor: pointer; }
    .gallery-img:hover { transform: scale(1.04); }

    /* ADMIN BUTTONS */
    .admin-review-actions { position: absolute; top: 20px; right: 20px; display: flex; gap: 12px; }
    .admin-reply-toggle-btn { background: none; border: none; color: var(--crema-gold); font-size: 12px; font-weight: 700; cursor: pointer; text-decoration: underline; transition: color 0.2s; }
    .admin-reply-toggle-btn:hover { color: var(--dark-charcoal); }
    .admin-delete-review-btn { background: none; border: none; color: #b91c1c; font-size: 12px; font-weight: 700; cursor: pointer; text-decoration: underline; transition: color 0.2s; }
    .admin-delete-review-btn:hover { color: #7f1d1d; }

    /* ADMIN REPLY BOX & FORM STYLES - STABILIZED INPUT LENGTHS */
    .admin-reply-display { margin-top: 15px; padding: 14px 18px; background-color: var(--bg-warm-creme); border-left: 4px solid var(--crema-gold); border-radius: 0 6px 6px 0; font-size: 13px; max-width: 650px; box-shadow: 0 2px 6px rgba(0,0,0,0.02); }
    .admin-reply-form-container { display: none; margin-top: 15px; padding-top: 15px; border-top: 1px dashed var(--border-color); }
    .admin-reply-form-container textarea { 
        width: 100%; 
        box-sizing: border-box; 
        padding: 10px; 
        border: 1px solid var(--border-color); 
        border-radius: 6px; 
        font-size: 13px; 
        margin-bottom: 10px; 
        outline: none; 
    }
    .admin-reply-form-container textarea:focus { border-color: var(--crema-gold); }
    
    .checkout-submit-btn { background-color: var(--dark-charcoal, #2b1d14); color: var(--crema-gold, #dfba6b); border: none; padding: 12px 24px; font-weight: 700; border-radius: 4px; cursor: pointer; text-transform: uppercase; letter-spacing: 1px; transition: background 0.3s; width: 100%; box-sizing: border-box; }
    .checkout-submit-btn:hover { background-color: #1a110c; }
</style>

<div class="about-layout-container">
    <div class="about-main-content">
        <h1 style="font-family:'Playfair Display', serif; font-size:42px; margin-top: 20px; margin-bottom:20px; color:var(--dark-charcoal);">
            About G's Coffee
        </h1>
        
        <?php if(!empty($feedbackMessage)) echo $feedbackMessage; ?>

<script>
    function toggleReplyForm(reviewId) {
        const formContainer = document.getElementById('reply-form-' + reviewId);
        if (formContainer.style.display === 'none' || formContainer.style.display === '') {
            formContainer.style.display = 'block';
        } else {
            formContainer.style.display = 'none';
        }
    }

    function openGalleryLightbox(imgSrc) {
        document.getElementById('lightbox-img').src = imgSrc;
        document.getElementById('gallery-lightbox').classList.add('active');
    }

    function closeGalleryLightbox() {
        document.getElementById('gallery-lightbox').classList.remove('active');
    }
</script>

<?php include 'footer.php'; ?>