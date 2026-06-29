<?php 
// 1. Start session and include header at the top
session_start();
require_once 'config.php'; // Ensure your DB connection is ready
include 'header.php'; 

// 2. Fetch User Address for the footer/cart
$user_info = ['address' => 'No address set']; 
if (isset($_SESSION['user_id'])) {
    $stmt = $conn->prepare("SELECT address FROM users WHERE id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $user_info = $row;
    }
    $stmt->close();
}
?>
<div id="view-home" class="page-view active">
    
    <header class="hero-carousel">
        <div class="hero-carousel">
            <button class="carousel-btn prev-btn">&#10094;</button>
            <button class="carousel-btn next-btn">&#10095;</button>
            <div class="carousel-track">
                <div class="slide">
                    <video autoplay loop muted playsinline preload="auto" class="slide-video">
                        <source src="videos/video1.mp4" type="video/mp4">
                    </video>
                    <div class="hero-overlay"></div>
                    <div class="hero-content">
                        <h1>The Art of the<br>Slow Brew.</h1>
                        <p>No rushing. No shortcuts. Meticulously crafted through the traditional Moka Pot method,
                            each cup is freshly brewed in just <strong>3-5 minutes</strong> to deliver rich flavor, aroma, and a truly authentic coffee experience.</p>
                        <a href="shop.php" class="hero-btn">EXPLORE MENU</a>
                    </div>
                </div>
                <div class="slide">
                    <video autoplay loop muted playsinline preload="auto" class="slide-video">
                        <source src="videos/video2.mp4" type="video/mp4">
                    </video>
                    <div class="hero-overlay"></div>
                    <div class="hero-content">
                        <h1>Worth the<br>Wait.</h1>
                        <p>Intense, rich, and authentic. Experience coffee made with patience and precision.</p>
                        <a href="shop.php" class="hero-btn">EXPLORE MENU</a>
                    </div>
                </div>
                <div class="slide">
                    <video autoplay loop muted playsinline preload="auto" class="slide-video">
                        <source src="videos/video3.mp4" type="video/mp4">
                    </video>
                    <div class="hero-overlay"></div>
                    <div class="hero-content">
                        <h1>Handcrafted<br>Espresso.</h1>
                        <p>Every cup is a deliberate process, bringing out the boldest flavors on the stove.</p>
                        <a href="shop.php" class="hero-btn">EXPLORE MENU</a>
                    </div>
                </div>
                <div class="slide">
                    <video autoplay loop muted playsinline preload="auto" class="slide-video">
                        <source src="videos/video4.mp4" type="video/mp4">
                    </video>
                    <div class="hero-overlay"></div>
                    <div class="hero-content">
                        <h1>Slow Down<br>& Sip.</h1>
                        <p>Your local space to pause, connect, and savor the moment.</p>
                        <a href="shop.php" class="hero-btn">EXPLORE MENU</a>
                    </div>
                </div>
                <div class="slide">
                    <video autoplay loop muted playsinline preload="auto" class="slide-video">
                        <source src="videos/video5.2.mp4" type="video/mp4">
                    </video>
                    <div class="hero-overlay"></div>
                    <div class="hero-content">
                        <h1>Patiently<br>Blended.</h1>
                        <p>Discover our refreshing iced creations and non-coffee favorites.</p>
                        <a href="shop.php" class="hero-btn">EXPLORE MENU</a>
                    </div>
                </div>
            </div>
            <div class="carousel-dots">
                <span class="dot active"></span> 
                <span class="dot"></span>      
                <span class="dot"></span>        
                <span class="dot"></span>       
                <span class="dot"></span>      
            </div>
        </div>
    </header>

    <div style="
    text-align:center;
    padding:40px 20px 25px;
    background:#fff;
">

    <h2 style="
    font-family:'Playfair Display', serif;
    font-size:48px;
    font-weight:700;
    color:#2b1d14;
    margin:12px 0;
    ">
        Crowd Favorites at G's Coffee
    </h2>
    <section class="products-marquee-section" style="padding: 60px 0 40px 0; overflow: hidden; width: 100%;">
        <div class="marquee-track">
            <?php 
            $bestSellersHTML = '';
            
            if (!isset($menuData) && file_exists('menu.xml')) {
                $menuData = simplexml_load_file('menu.xml');
            }

            if (isset($menuData)) {
                foreach ($menuData->category as $cat) {
                    foreach ($cat->product as $prod) {
                        if (isset($prod->bestseller) && (string)$prod->bestseller == 'true') {
                            
                            $displayName = htmlspecialchars(trim((string)$prod->name));
                            $imagePath = (isset($prod->image) && !empty((string)$prod->image)) ? (string)$prod->image : 'images/logo-placeholder.png'; 
                            
                            $pM = (float)$prod->priceM;
                            $pL = (float)$prod->priceL;
                            
                            // I-pass natin ang 'true' kung single size, at 'false' kung hindi.
                            $isSingle = (isset($prod->singleSize) && (string)$prod->singleSize == 'true') ? 'true' : 'false';

                            $bestSellersHTML .= '
                            <div class="marquee-item-card" 
                                 onclick="openCustomizer(\''.addslashes($displayName).'\', '.$pM.', '.$pL.', '.$isSingle.', \''.addslashes($imagePath).'\')" 
                                 style="cursor: pointer;">
                                <div class="marquee-img-box">
                                    <svg class="instagram-like-heart" viewBox="0 0 24 24" fill="currentColor"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"></path></svg>
                                    <img src="'.$imagePath.'" alt="'.$displayName.'">
                                </div>
                                <h4>'.$displayName.'</h4>
                            </div>';
                        }
                    }
                }
            }
            echo $bestSellersHTML;
            echo $bestSellersHTML; // Double echo para sa marquee effect
            ?>
        </div>
    </section>

    <section class="gscoffee-split-block">
    <div class="split-text-column">
        <div class="content-wrapper">
            <span class="featured-tag">FEATURED &mdash; MENU</span>
            <h2>Experience the<br>Slow Bar Difference.</h2>
            <p>From carefully sourced beans to our signature Moka Pot brewing method, every cup is designed with intention. Discover the rich flavors of our full roster.</p>
            <a href="shop.php" class="gscoffee-explore-btn">EXPLORE MENU &rarr;</a>
        </div>
    </div>
    <div class="split-media-column">
        <div class="split-image-box">
            <img src="images/menu.png" alt="G's Coffee Full Menu" style="cursor: pointer;" onclick="openMenuLightbox()">
        </div>
    </div>
</section>

    <div style="background-color: var(--bg-warm-creme); padding: 80px 4%; border-top: 1px solid var(--border-color);">
        <div style="max-width: 1200px; margin: 0 auto; text-align: center;">
            <h2 style="font-family: 'Playfair Display', serif; color: var(--dark-charcoal); font-size: 36px; margin-bottom: 15px;">Our Coffee Community</h2>
            <p style="color: var(--muted-gray); margin-bottom: 50px; font-size: 16px; max-width: 600px; margin-left: auto; margin-right: auto;">Join the growing family of coffee lovers spotted at G's Coffee. Great conversations and even better brews.</p>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px;">
                <img src="images/customers/customer1.jpg" alt="Happy Customer" onclick="openGalleryLightbox(this.src)" style="width: 100%; height: 220px; object-fit: cover; border-radius: 8px; box-shadow: 0 4px 15px rgba(43,29,20,0.08); cursor: pointer; transition: transform 0.3s ease;" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
                <img src="images/customers/customer2.jpg" alt="Happy Customer" onclick="openGalleryLightbox(this.src)" style="width: 100%; height: 220px; object-fit: cover; border-radius: 8px; box-shadow: 0 4px 15px rgba(43,29,20,0.08); cursor: pointer; transition: transform 0.3s ease;" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
                <img src="images/customers/customer3.jpg" alt="Happy Customer" onclick="openGalleryLightbox(this.src)" style="width: 100%; height: 220px; object-fit: cover; border-radius: 8px; box-shadow: 0 4px 15px rgba(43,29,20,0.08); cursor: pointer; transition: transform 0.3s ease;" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
                <img src="images/customers/customer4.jpg" alt="Happy Customer" onclick="openGalleryLightbox(this.src)" style="width: 100%; height: 220px; object-fit: cover; border-radius: 8px; box-shadow: 0 4px 15px rgba(43,29,20,0.08); cursor: pointer; transition: transform 0.3s ease;" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
            </div>
            <div style="margin-top: 50px;">
               <a href="reviews.php" class="gscoffee-explore-btn">
                    View All Reviews & Photos
                </a>
            </div>
        </div>
    </div>
</div> 

<div id="menu-lightbox" class="lightbox-overlay" onclick="closeMenuLightbox()">
    <span class="lightbox-close">&times;</span>
    <img class="lightbox-content" src="images/menu.png" alt="Zoomed Menu">
</div>

<div id="gallery-lightbox" class="lightbox-overlay" onclick="closeGalleryLightbox()">
    <span class="lightbox-close" onclick="closeGalleryLightbox()">&times;</span>
    <img class="lightbox-content" id="lightbox-img" src="" onclick="event.stopPropagation();">
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const allVideos = document.querySelectorAll('.slide-video');
    allVideos.forEach(video => {
        video.play().catch(error => console.log("Browser autoplay blocked."));
    });

    const track = document.querySelector('.carousel-track');
    const dots = document.querySelectorAll('.dot');
    const prevBtn = document.querySelector('.prev-btn');
    const nextBtn = document.querySelector('.next-btn');
    
    if (track && prevBtn && nextBtn) {
        let currentSlide = 0;
        const totalSlides = dots.length;
        let slideInterval;

        function moveToSlide(index) {
            track.style.transform = `translateX(-${index * 100}vw)`;
            dots.forEach(dot => dot.classList.remove('active'));
            if(dots[index]) dots[index].classList.add('active');
            currentSlide = index;
        }

        function nextSlide() { moveToSlide((currentSlide + 1) % totalSlides); }
        function prevSlide() { moveToSlide((currentSlide - 1 + totalSlides) % totalSlides); }

        function startAutoSlide() { slideInterval = setInterval(nextSlide, 10000); }
        function resetAutoSlide() { clearInterval(slideInterval); startAutoSlide(); }

        nextBtn.addEventListener('click', () => { nextSlide(); resetAutoSlide(); });
        prevBtn.addEventListener('click', () => { prevSlide(); resetAutoSlide(); });

        dots.forEach((dot, index) => {
            dot.addEventListener('click', () => { moveToSlide(index); resetAutoSlide(); });
        });

        startAutoSlide();
    }
});

function openMenuLightbox() {
    const lightbox = document.getElementById('menu-lightbox');
    if (lightbox) { lightbox.classList.add('active'); document.body.style.overflow = 'hidden'; }
}
function closeMenuLightbox() {
    const lightbox = document.getElementById('menu-lightbox');
    if (lightbox) { lightbox.classList.remove('active'); document.body.style.overflow = 'auto'; }
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