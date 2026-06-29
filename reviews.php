<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

include 'config.php';
include 'header.php';

$statsQuery = mysqli_query($conn, "
    SELECT
        COUNT(*) AS total_reviews,
        ROUND(AVG(rating), 1) AS avg_rating
    FROM reviews
");

$stats = mysqli_fetch_assoc($statsQuery);

$totalReviews = $stats['total_reviews'] ?? 0;
$avgRating = $stats['avg_rating'] ?? 0;

$result = mysqli_query(
    $conn,
    "SELECT * FROM reviews ORDER BY created_at DESC"
);

if(!$result){
    die(mysqli_error($conn));
}

$isAdmin =
    isset($_SESSION['role']) &&
    $_SESSION['role'] === 'admin';
?>

<div class="reviews-page">

    <!-- HERO -->
    <section class="reviews-hero">

        <div class="reviews-hero-text">
            <h1>Community Voices</h1>

            <p>
                The heart of G's Coffee Shop beats through the stories shared
                by our community. Whether it's your first morning sip or a
                late-afternoon ritual, we love hearing how our coffee brightens
                your day.
            </p>

        </div>

        <div class="reviews-hero-image">
            <img src="images/latte.JPG" alt="Coffee">
        </div>

    </section>

    <div style="background-color: var(--bg-warm-creme); padding: 80px 4%; border-top: 1px solid var(--border-color);">

    <div style="max-width: 1200px; margin: 0 auto; text-align: center;">

        <h2 style="font-family: 'Playfair Display', serif; color: var(--dark-charcoal); font-size: 36px; margin-bottom: 15px;">
            Our Coffee Community
        </h2>

        <p style="color: var(--muted-gray); margin-bottom: 50px; font-size: 16px; max-width: 600px; margin-left: auto; margin-right: auto;">
            Join the growing family of coffee lovers spotted at G's Coffee.
            Great conversations and even better brews.
        </p>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px,1fr)); gap: 20px;">

            <img src="images/customers/customer1.jpg"
                 alt="Happy Customer"
                 onclick="openGalleryLightbox(this.src)"
                 style="width:100%; height:220px; object-fit:cover; border-radius:8px; cursor:pointer;">

            <img src="images/customers/customer2.jpg"
                 alt="Happy Customer"
                 onclick="openGalleryLightbox(this.src)"
                 style="width:100%; height:220px; object-fit:cover; border-radius:8px; cursor:pointer;">

            <img src="images/customers/customer3.jpg"
                 alt="Happy Customer"
                 onclick="openGalleryLightbox(this.src)"
                 style="width:100%; height:220px; object-fit:cover; border-radius:8px; cursor:pointer;">

            <img src="images/customers/customer4.jpg"
                 alt="Happy Customer"
                 onclick="openGalleryLightbox(this.src)"
                 style="width:100%; height:220px; object-fit:cover; border-radius:8px; cursor:pointer;">

        </div>

    </div>

</div>


    <section class="review-stats">

    <div class="stat-card">

        <h2><?php echo $avgRating; ?></h2>

        <div class="review-stars">
        <?php
        $fullStars = floor($avgRating);
        $halfStar = ($avgRating - $fullStars) >= 0.5;

        for($i = 1; $i <= $fullStars; $i++){
            echo '<i class="fas fa-star"></i>';
        }

        if($halfStar){
            echo '<i class="fas fa-star-half-alt"></i>';
        }

        $emptyStars = 5 - $fullStars - ($halfStar ? 1 : 0);

        for($i = 1; $i <= $emptyStars; $i++){
            echo '<i class="far fa-star"></i>';
        }
        ?>
        </div>

        <p>Average Satisfaction</p>

    </div> <!-- CLOSE FIRST CARD -->

    <div class="stat-card">

        <h2><?php echo $totalReviews; ?></h2>

        <p>Happy Locals</p>

    </div>

</section>

</section>

    <!-- REVIEWS GRID -->
    <section class="reviews-grid-modern">

        <?php while($row = mysqli_fetch_assoc($result)): ?>

            <div class="review-modern-card">

                <div class="review-user">

                    <div class="review-avatar">
                        <?= strtoupper(substr($row['username'],0,1)) ?>
                    </div>

                    <div>
                        <h3><?= htmlspecialchars($row['username']) ?></h3>
                        <small>G's Coffee Customer</small>
                    </div>

                </div>

                <div class="review-stars">
                    <?= str_repeat("★",$row['rating']) ?>
                </div>

                <p class="review-text">
                    <?= nl2br(htmlspecialchars($row['review_text'])) ?>
                </p>

                <?php if(!empty($row['review_photo'])): ?>

                    <div class="review-image">

                        <img
                            src="uploads/reviews/<?= htmlspecialchars($row['review_photo']) ?>"
                            alt="Review Photo"
                        >

                    </div>

                <?php endif; ?>

                <?php if(!empty($row['admin_reply'])): ?>

                    <div class="admin-reply">

                        <strong>
                            G's Coffee Reply
                        </strong>

                        <p>
                            <?= nl2br(htmlspecialchars($row['admin_reply'])) ?>
                        </p>

                    </div>

                <?php endif; ?>

                <?php if($isAdmin): ?>

                    <div class="admin-actions">

                        <button
                            type="button"
                            onclick="openReplyModal(<?= $row['id'] ?>)"
                        >
                            Reply
                        </button>

                        <button
                            type="button"
                            class="delete-btn"
                            onclick="openDeleteModal(<?= $row['id'] ?>)"
                        >
                            Delete
                        </button>

                    </div>

                <?php endif; ?>

            </div>

        <?php endwhile; ?>

        <div class="voice-card">
            <h2>Your Voice Matters</h2>
            <p>
                Had a great experience?
                Join our wall of community voices.
            </p>
            <button
                class="write-review-btn"
                onclick="openReviewModal()"
            >
                Write a Review
            </button>

        </div>


            </section>

        </div>

<div
    id="reviewFormModal"
    class="admin-modal-overlay"
>

    <div class="admin-edit-modal">

        <button
            type="button"
            onclick="
            document.getElementById('reviewFormModal')
            .classList.remove('active')
            "
        >
            ✕
        </button>

        <h2>Leave a Review</h2>

        <form
            action="submit_review.php"
            method="POST"
        >

            <select name="rating" required>
                <option value="">⭐ Select Rating</option>
                <option value="5">★★★★★ Excellent</option>
                <option value="4">★★★★ Very Good</option>
                <option value="3">★★★ Good</option>
                <option value="2">★★ Fair</option>
                <option value="1">★ Poor</option>
            </select>

            <textarea
                name="review_text"
                rows="5"
                required
            ></textarea>

            <button type="submit">
                Submit Review
            </button>

        </form>

    </div>

</div>

<!-- REPLY MODAL -->

<div
    id="replyModal"
    class="admin-modal-overlay"
>

    <div class="admin-edit-modal">

        <button
            type="button"
            class="close-modal-btn"
            onclick="closeReplyModal()"
        >
            ✕
        </button>

        <h2>Reply to Review</h2>

        <form
            action="reply_review.php"
            method="POST"
        >

            <input
                type="hidden"
                name="id"
                id="reviewId"
            >

            <textarea
                name="admin_reply"
                rows="5"
                required
                placeholder="Write your reply..."
            ></textarea>

            <button
                type="submit"
                class="admin-save-btn"
            >
                Send Reply
            </button>

        </form>

    </div>

</div>

<script>

function openReplyModal(id){
    document.getElementById("reviewId").value = id;
    document
        .getElementById("replyModal")
        .classList.add("active");
}

function closeReplyModal(){
    document
        .getElementById("replyModal")
        .classList.remove("active");
}

window.onclick = function(event){
    const modal =
        document.getElementById("replyModal");
    if(event.target === modal){
        modal.classList.remove("active");
    }
}

function openReviewModal(){
    document
        .getElementById("reviewModal")
        .classList.add("active");
}

function closeReviewModal(){
    document
        .getElementById("reviewModal")
        .classList.remove("active");
}

function openDeleteModal(id){

    document
        .getElementById("confirmDeleteBtn")
        .href =
        "delete_review.php?id=" + id;

    document
        .getElementById("deleteModal")
        .classList.add("active");

}

function closeDeleteModal(){
    document
        .getElementById("deleteModal")
        .classList.remove("active");

}

</script>

<div id="reviewModal" class="admin-modal-overlay">

    <div class="review-modal">

        <button
            type="button"
            class="close-modal-btn"
            onclick="closeReviewModal()"
        >
            ✕
        </button>

        <h2>Leave a Review</h2>

        <form
            action="submit_review.php"
            method="POST"
            enctype="multipart/form-data"
        >

            <select name="rating" required>
                <option value="">Select Rating</option>
                <option value="5">★★★★★</option>
                <option value="4">★★★★</option>
                <option value="3">★★★</option>
                <option value="2">★★</option>
                <option value="1">★</option>
            </select>

            <textarea
                name="review_text"
                rows="5"
                required
                placeholder="Tell us about your experience..."
            ></textarea>

            <div class="review-upload-wrapper">

                <label class="upload-label">
                    Upload Photo (Optional)
                </label>

                <label for="review_photo" class="custom-upload-btn">
                    Choose Photo
                </label>

                <span id="file-name">
                    No file selected
                </span>

                <input
                    type="file"
                    id="review_photo"
                    name="review_photo"
                    accept="image/*"
                    hidden
                    onchange="
                        document.getElementById('file-name').textContent =
                        this.files[0]
                        ? this.files[0].name
                        : 'No file selected';
                    "
                >

            </div>

            <button
                type="submit"
                class="review-submit-btn"
            >
                Submit Review
            </button>
        </form>
    </div>
</div>

<div id="deleteModal" class="admin-modal-overlay">

    <div class="delete-modal">

        <button
            class="close-modal-btn"
            onclick="closeDeleteModal()"
        >
            ✕
        </button>

        <h2>Delete Review</h2>

        <p>
            Are you sure you want to delete this review?
        </p>

        <div class="delete-modal-actions">

            <button
                type="button"
                class="cancel-delete-btn"
                onclick="closeDeleteModal()"
            >
                Cancel
            </button>

            <a
                id="confirmDeleteBtn"
                href="#"
                class="confirm-delete-btn"
            >
                Delete
            </a>

        </div>

    </div>

</div>

<?php include 'footer.php'; ?>