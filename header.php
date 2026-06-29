<?php
// Combine everything into one single opening tag
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'config.php';

// Initialize and calculate $totalQty defensively to prevent undefined variable warnings
$totalQty = 0;
if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $totalQty += isset($item['quantity']) ? intval($item['quantity']) : 0;
    }
}

// Fetch the user address globally
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

$xmlFile = 'menu.xml';
$menuData = file_exists($xmlFile) ? simplexml_load_file($xmlFile) : null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>G's Coffee | Premium Integrated Ordering System</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
    <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>

    <nav class="navbar">
        <div class="nav-left">
            <a href="index.php" class="brand-logo">G'S COFFEE</a>
            <ul class="nav-links">
                <li class="dropdown-trigger">
                    <a href="shop.php">Shop</a>
                    <div class="mega-dropdown-panel nested-navigation">
                        <div class="dropdown-links-sidebar">
                            <div class="nav-item-with-flyout">
                                <a href="shop.php" class="sidebar-main-link">Shop All Products</a>
                            </div>
                            <?php if ($menuData): ?>
                                <?php foreach ($menuData->category as $cat): ?>
                                    <div class="nav-item-with-flyout">
                                        <a href="shop.php#cat-<?php echo $cat['id']; ?>" class="sidebar-main-link">
                                            <?php echo htmlspecialchars($cat['title']); ?> ›
                                        </a>
                                        <div class="text-flyout-panel">
                                            <?php foreach ($cat->product as $prod): 
                                                $single = isset($prod->singleSize) && $prod->singleSize == 'true' ? 'true' : 'false';
                                                $flyoutImg = (isset($prod->image) && !empty((string)$prod->image)) ? (string)$prod->image : 'images/logo-placeholder.png';
                                            ?>
                                                <a href="shop.php?open=<?php echo urlencode($prod->name); ?>&pM=<?php echo $prod->priceM; ?>&pL=<?php echo $prod->priceL; ?>&single=<?php echo $single; ?>&image=<?php echo urlencode($flyoutImg); ?>">
                                                    <?php echo htmlspecialchars($prod->name); ?>
                                                </a>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                        
                        <div class="dropdown-content-display-area">
                            <div class="dropdown-best-sellers-preview active">
                                <div class="nav-bs-wrapper">
                                    <button class="nav-bs-btn nav-bs-prev">&#10094;</button>
                                    <button class="nav-bs-btn nav-bs-next">&#10095;</button>
                                    <div class="nav-bs-track-container">
                                        <div class="nav-bs-track">
                                            <?php 
                                            $navBestSellersHTML = '';
                                            if ($menuData) {
                                                foreach ($menuData->category as $cat) {
                                                    foreach ($cat->product as $prod) {
                                                        if (isset($prod->bestseller) && (string)$prod->bestseller == 'true') {
                                                            
                                                            $displayName = htmlspecialchars(trim((string)$prod->name));
                                                            
                                                            if (isset($prod->image) && !empty((string)$prod->image)) {
                                                                $imagePath = (string)$prod->image;
                                                            } else {
                                                                $imagePath = 'images/logo-placeholder.png'; 
                                                            }
                                                            
                                                            $pM = (float)$prod->priceM;
                                                            $pL = (float)$prod->priceL;
                                                            $single = isset($prod->singleSize) && (string)$prod->singleSize == 'true' ? 'true' : 'false';
                                                            $displayPrice = ($single === 'true') ? '₱' . number_format($pM, 2) : '₱' . number_format($pM, 2) . ' - ₱' . number_format($pL, 2);

                                                            $navBestSellersHTML .= '
                                                            <div class="nav-bs-card">
                                                                <div class="nav-bs-img-box">
                                                                    <img src="'.$imagePath.'" alt="'.$displayName.'">
                                                                    <button class="nav-quick-view-btn" onclick="openCustomizer(\''.addslashes($displayName).'\', '.$pM.', '.$pL.', '.$single.', \''.addslashes($imagePath).'\')">QUICK VIEW</button>
                                                                </div>
                                                                <div class="nav-bs-details">
                                                                    <h4>'.$displayName.'</h4>
                                                                    <p class="nav-bs-price">'.$displayPrice.'</p>
                                                                </div>
                                                            </div>';
                                                        }
                                                    }
                                                }
                                            }
                                            echo $navBestSellersHTML;
                                            ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </li>     
                <li><a href="our_story.php">OUR STORY</a></li>
                <li><a href="contact_details.php">CONTACT DETAILS</a></li>
                <li><a href="faqs.php">FAQS</a></li>
                <li><a href="reviews.php">CUSTOMER REVIEWS</a></li> 
            </ul>
        </div>
        
        <div class="nav-right" style="display: flex; align-items: center; gap: 20px;">
            <?php if (isset($_SESSION['username'])): ?>
                
                <?php
                // Determine the correct destination based on user role
                $profileDestination = 'dashboard.php'; // Default for standard clients
                if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
                    $profileDestination = 'admin.php'; // Override for admins
                }
                ?>
                <a href="<?php echo $profileDestination; ?>" style="display: flex; align-items: center; gap: 8px; color: var(--clean-white); text-decoration: none; font-size: 14px; letter-spacing: 0.5px;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                        <circle cx="12" cy="7" r="4"></circle>
                    </svg>
                    <span>Hello, <strong style="font-weight: 700;"><?php echo htmlspecialchars($_SESSION['username']); ?></strong>!</span>
                </a>
                
                <a href="login.php?logout=1" class="nav-auth-btn" style="color: #c94a4a; margin-right: 10px;">LOG OUT</a>
                
            <?php else: ?>
                <a href="login.php" class="nav-auth-btn">Log In</a>
            <?php endif; ?>
            
            <button class="nav-cart-icon-btn" onclick="toggleCart(true)">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
                    <line x1="3" y1="6" x2="21" y2="6"></line>
                    <path d="M16 10a4 4 0 0 1-8 0"></path>
                </svg>

                <span class="cart-badge" id="cart-counter">
                    <?php echo $totalQty; ?>
                </span>
            </button>
        </div>
    </nav>

    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const navBsTrackContainer = document.querySelector('.nav-bs-track-container');
        const navBsPrevBtn = document.querySelector('.nav-bs-prev');
        const navBsNextBtn = document.querySelector('.nav-bs-next');
        if (navBsTrackContainer && navBsPrevBtn && navBsNextBtn) {
            navBsNextBtn.addEventListener('click', (e) => { e.preventDefault(); e.stopPropagation(); const card = navBsTrackContainer.querySelector('.nav-bs-card'); if(card) navBsTrackContainer.scrollBy({ left: card.offsetWidth + 15, behavior: 'smooth' }); });
            navBsPrevBtn.addEventListener('click', (e) => { e.preventDefault(); e.stopPropagation(); const card = navBsTrackContainer.querySelector('.nav-bs-card'); if(card) navBsTrackContainer.scrollBy({ left: -(card.offsetWidth + 15), behavior: 'smooth' }); });
        }
    });
    </script>