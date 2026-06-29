
<?php

session_start();
$total = 0; // <--- ADD THIS LINE HERE

require_once 'config.php';
include 'header.php'; 

// 1. Fetch products ONE time
$productsResult = $conn->query("
    SELECT *
    FROM products
    ORDER BY FIELD(category, 'Coffee Drinks', 'Non-Coffee Blends', 'Soda', 'Ice Blended Frappes'), product_name
");

$isAdmin = (isset($_SESSION['role']) && $_SESSION['role'] === 'admin');

$categories = [];
while ($row = $productsResult->fetch_assoc()) {
    $categories[$row['category']][] = $row;
}

// 2. Fetch User Address ONCE
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

<div class="menu-container" style="padding-top: 50px;">
    
    <div style="text-align: center; margin-bottom: 25px;">
        <h1 style="font-size: 38px; font-weight: 800; color: var(--dark-charcoal); margin-bottom: 10px;">Our Menu</h1>
        <p style="color: var(--muted-gray); font-size: 15px;">Find your favorite drink.</p>
    </div>

    <div class="shop-search-container">
        <svg class="search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="11" cy="11" r="8"></circle>
            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
        </svg>
        <input type="text" id="shop-search-input" placeholder="Search for your favorite drinks (e.g. Spanish Latte)..." onkeyup="searchMenu()">
    </div>

        <?php if ($isAdmin): ?>
        <div style="text-align: center; margin-bottom: 50px;">
            <button onclick="openAddModal()" style="background-color: #6d5242; color: var(--clean-white); border: 2px solid #6d5242; padding: 12px 25px; border-radius: 30px; font-size: 13px; font-weight: 700; letter-spacing: 1px; cursor: pointer; box-shadow: 0 5px 15px rgba(109, 82, 66, 0.2); transition: all 0.3s ease; text-transform: uppercase;">
                + Add New Drink
            </button>
        </div>
    <?php endif; ?>

<?php foreach ($categories as $categoryName => $products): ?>

<section class="menu-category-group" id="cat-<?php echo strtolower(str_replace(' ', '-', $categoryName)); ?>">

<h2 class="pickup-style-heading">
    <?php echo strtoupper($categoryName); ?>
</h2>

<div class="product-grid">
    <?php foreach ($products as $prod): ?>
        <div class="product-card">
            <div class="product-image-container">
                <img
                    src="<?php echo $prod['image_path']; ?>"
                    alt="<?php echo htmlspecialchars($prod['product_name']); ?>"
                    class="product-img">
                <?php if ($prod['bestseller']): ?>
                    <span class="best-seller-badge">
                        ♥️ Best Seller
                    </span>
                <?php endif; ?>
            </div>
            <h3>
                <?php echo htmlspecialchars($prod['product_name']); ?>
            </h3>
            <p
                id="price-<?php echo $prod['id']; ?>"
                style="color:#666;font-size:14px;margin-bottom:15px;font-weight:500;">
                <?php if ($prod['single_size']): ?>
                    ₱<?php echo number_format($prod['price_medium'],2); ?>
                <?php else: ?>
                    ₱<?php echo number_format($prod['price_medium'],2); ?>
                    -
                    ₱<?php echo number_format($prod['price_large'],2); ?>
                <?php endif; ?>
            </p>

            <div class="card-footer">
                <?php if ($isAdmin): ?>
                    <div style="display: flex; gap: 8px; width: 100%;">
                        <button class="add-to-cart-trigger" style="flex: 1; padding: 12px 5px;" onclick="editProduct(<?php echo $prod['id']; ?>)">
                            EDIT
                        </button>
                        <button class="add-to-cart-trigger" style="flex: 1; padding: 12px 5px; color: #6d5242; border-color: #6d5242;" onclick="deleteProduct(<?php echo $prod['id']; ?>)">
                            DELETE
                        </button>
                    </div>

                <?php else: ?>

                    <?php if ($prod['is_sold_out']): ?>

                        <button disabled
                                style="background:#999;color:white;">
                            SOLD OUT
                        </button>

                    <?php else: ?>

                        <button
                            class="add-to-cart-trigger"
                            onclick="openCustomizer(
                                '<?php echo addslashes($prod['product_name']); ?>',
                                <?php echo $prod['price_medium']; ?>,
                                <?php echo $prod['price_large']; ?>,
                                <?php echo $prod['single_size']; ?>,
                                '<?php echo addslashes($prod['image_path']); ?>'
                            )">
                            Add To Cart
                        </button>

                    <?php endif; ?>

                <?php endif; ?>

            </div>

        </div>

    <?php endforeach; ?>

</div>

</section>

<?php endforeach; ?>

</div>

<script>
window.addEventListener('DOMContentLoaded', () => {
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('open')) {
        const name = urlParams.get('open');
        const pM = parseFloat(urlParams.get('pM'));
        const pL = parseFloat(urlParams.get('pL'));
        const single = urlParams.get('single') === 'true';
        const imagePath = urlParams.get('image') || 'images/logo-placeholder.png'; 
        
        setTimeout(() => {
            openCustomizer(name, pM, pL, single, imagePath); 
        }, 300);
    }
});

function searchMenu() {
    let searchInputElement = document.getElementById('shop-search-input');
    if (!searchInputElement) return; 

    let input = searchInputElement.value.toLowerCase();
    let allCategories = document.querySelectorAll('.menu-category-group');

    if (input === '') {
        filterMenu();
        document.querySelectorAll('.product-card').forEach(card => {
            card.style.display = 'flex'; 
        });
        return;
    }

    allCategories.forEach(category => {
        let cards = category.querySelectorAll('.product-card');
        let hasVisibleCard = false; 

        cards.forEach(card => {
            let productName = card.querySelector('h3').innerText.toLowerCase();
            if (productName.includes(input)) {
                card.style.display = 'flex'; 
                hasVisibleCard = true;
            } else {
                card.style.display = 'none'; 
            }
        });

        if (hasVisibleCard) {
            category.style.display = 'block';
        } else {
            category.style.display = 'none';
        }
    });
}

function filterMenu() {
    const selectedCategory = window.location.hash;
    const allCategories = document.querySelectorAll('.menu-category-group');

    if (selectedCategory) {
        allCategories.forEach(category => {
            if ('#' + category.id === selectedCategory.substring(1)) {
                category.style.display = 'block';
            } else {
                category.style.display = 'none';
            }
        });
    } else {
        allCategories.forEach(category => {
            category.style.display = 'block';
        });
    }
}

function editProduct(id){
    fetch("get_product.php?id=" + id)
    .then(response => response.json())
    .then(data => {
        document.getElementById("edit_id").value = data.id;
        document.getElementById("edit_name").value = data.product_name;
        document.getElementById("edit_priceM").value = data.price_medium;
        document.getElementById("edit_priceL").value = data.price_large;

        document.getElementById("editModal").classList.add("active");
    })
    .catch(error => {
        console.log(error);
        alert("Failed to load product.");
    });
}

function closeEditModal(){
    document.getElementById("editModal").classList.remove("active");
    // Clear the file input when modal closes
    document.getElementById("edit_image").value = ""; 
}

function showSuccessToast(message){
    const toast = document.getElementById("successToast");
    toast.innerHTML = "✓ " + message;
    toast.classList.add("show");

    setTimeout(() => {
        toast.classList.remove("show");
    }, 3000);
}

function saveProduct(){
    let formData = new FormData();

    formData.append("id", document.getElementById("edit_id").value);
    formData.append("product_name", document.getElementById("edit_name").value);
    formData.append("price_medium", document.getElementById("edit_priceM").value);
    formData.append("price_large", document.getElementById("edit_priceL").value);

    // BAGO: Kunin ang image file kung may inupload
    let imageFile = document.getElementById("edit_image").files[0];
    if (imageFile) {
        formData.append("new_image", imageFile);
    }

    fetch("update_product.php",{
        method: "POST",
        body: formData
    })
    .then(response => response.text())
    .then(data => {
        console.log("SERVER RESPONSE:", data);

        if(data.trim() === "success" || data.includes("success")){
            closeEditModal();
            showSuccessToast("Product Updated Successfully");

            const productId = document.getElementById("edit_id").value;
            const newMedium = document.getElementById("edit_priceM").value;
            const newLarge = document.getElementById("edit_priceL").value;
            const priceElement = document.getElementById("price-" + productId);

            // Update text price immediately
            if(priceElement){
                priceElement.innerHTML = "₱" + parseFloat(newMedium).toFixed(2) + " - ₱" + parseFloat(newLarge).toFixed(2);
            }

            // Reload page shortly to display the new uploaded image
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        }
        else {
            alert("Server returned: " + data);
        }
    })
    .catch(error => {
        console.error("Error:", error);
        alert("An error occurred while saving.");
    });
}

window.addEventListener('DOMContentLoaded', filterMenu);
window.addEventListener('hashchange', filterMenu);
</script>

<div id="editModal" class="admin-modal-overlay">
    <div class="admin-edit-modal" style="background: var(--clean-white); padding: 30px; border-radius: 12px; width: 90%; max-width: 450px; margin: auto;">
        
        <div class="modal-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h2 style="font-family: 'Playfair Display', serif; margin: 0; font-size: 24px; color: var(--dark-charcoal);">Edit Product</h2>
            <button class="close-modal" onclick="closeEditModal()" style="background:none; border:none; font-size:24px; cursor:pointer; color: var(--dark-charcoal);">&times;</button>
        </div>

        <form id="editProductForm" onsubmit="event.preventDefault(); saveProduct();">
            <input type="hidden" id="edit_id">
            
            <div style="margin-bottom: 15px;">
                <label style="display: block; font-size: 13px; font-weight: 600; margin-bottom: 6px; color: var(--dark-charcoal);">Drink Name</label>
                <input type="text" id="edit_name" style="width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: 6px; outline: none;" required>
            </div>

            <div style="display: flex; gap: 15px; margin-bottom: 15px;">
                <div style="flex: 1;">
                    <label style="display: block; font-size: 13px; font-weight: 600; margin-bottom: 6px; color: var(--dark-charcoal);">Medium Price (₱)</label>
                    <input type="number" id="edit_priceM" step="0.01" style="width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: 6px; outline: none;" required>
                </div>
                <div style="flex: 1;">
                    <label style="display: block; font-size: 13px; font-weight: 600; margin-bottom: 6px; color: var(--dark-charcoal);">Large Price (₱)</label>
                    <input type="number" id="edit_priceL" step="0.01" style="width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: 6px; outline: none;" required>
                </div>
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: block; font-size: 13px; font-weight: 600; margin-bottom: 6px; color: var(--dark-charcoal);">Update Product Image (Optional)</label>
                <input type="file" id="edit_image" accept="image/png, image/jpeg, image/webp" style="width: 100%; padding: 8px; border: 1px dashed var(--border-color); border-radius: 6px; background: #fbf8f5; font-size: 12px; outline: none;">
                <small style="color: var(--muted-gray); font-size: 11px;">Leave blank to keep the current image.</small>
            </div>

            <div class="admin-modal-actions" style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px;">
                <button type="button" onclick="closeEditModal()" style="background: transparent; color: #6d5242; border: 1px solid #6d5242; padding: 10px 20px; border-radius: 20px; cursor: pointer; font-weight: 600;">Cancel</button>
                <button type="submit" style="background-color: #6d5242; color: white; border: none; padding: 10px 20px; border-radius: 20px; cursor: pointer; font-weight: 600;">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<div id="successToast" class="success-toast">
    <span style="font-size:20px;">✓</span>
    <span style="margin-left:8px;">
        Product Updated Successfully
    </span>
</div>

<div id="receipt-sidebar" class="receipt-sidebar">
    <div class="receipt-header">
        <span style="font-weight: 600; font-size: 15px; color: var(--dark-charcoal); display: flex; align-items: center; gap: 8px;">
            Receipt Confirmation
        </span>
        <button class="close-receipt" onclick="closeReceiptAndReload()">&times;</button>
    </div>
    <div class="receipt-content">
        <div class="receipt-success-icon">✓</div>
        <h2 style="font-family: 'Playfair Display', serif; color: var(--dark-charcoal); font-size: 28px; margin-bottom: 5px;">Order Placed!</h2>
        <p style="font-size: 11px; color: var(--muted-gray); font-weight: 700; letter-spacing: 1.5px; margin-bottom: 20px;">CODE ID: ord_<span id="rcpt-id">---</span></p>
        
        <p style="font-size: 13px; color: var(--muted-gray); line-height: 1.6; text-align: center; margin-bottom: 30px;">
            Thank you for supporting G's Coffee Shop! Your order has been registered in the database, and baristas are preparing your custom selection now.
        </p>

        <div class="receipt-details-box">
            <div class="receipt-row">
                <span class="receipt-label">Recipient Profile</span>
                <span class="receipt-value" id="rcpt-name">---</span>
            </div>
            <div class="receipt-row">
                <span class="receipt-label">Payment Channel</span>
                <span class="receipt-value" id="rcpt-payment">---</span>
            </div>
            <div class="receipt-row">
                <span class="receipt-label">Pickup Type</span>
                <span class="receipt-value" id="rcpt-pickup">---</span>
            </div>
        </div>

        <div class="receipt-total-row">
            <span style="font-weight: 700; color: #a34e26;">Total Amount Paid</span>
            <span style="font-weight: 800; color: #a34e26; font-size: 20px;" id="rcpt-total">₱0.00</span>
        </div>

        <button class="continue-browsing-btn" onclick="closeReceiptAndReload()">Continue Browsing Menu</button>
    </div>
</div>

<div id="addModal" class="admin-modal-overlay">
    <div class="admin-edit-modal" style="background: var(--clean-white); padding: 30px; border-radius: 12px; width: 90%; max-width: 450px; margin: auto;">
        
        <div class="modal-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h2 style="font-family: 'Playfair Display', serif; margin: 0; font-size: 24px; color: var(--dark-charcoal);">Add New Drink</h2>
            <button onclick="closeAddModal()" style="background:none; border:none; font-size:24px; cursor:pointer; color: var(--dark-charcoal);">&times;</button>
        </div>

        <form id="addProductForm" onsubmit="event.preventDefault(); submitNewProduct();">
            
            <div style="margin-bottom: 15px;">
                <label style="display: block; font-size: 13px; font-weight: 600; margin-bottom: 6px;">Category</label>
                <select id="add_category" style="width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: 6px; outline: none;" required>
                    <option value="Coffee Drinks">Coffee Drinks</option>
                    <option value="Non-Coffee Blends">Non-Coffee Blends</option>
                    <option value="Soda">Soda</option>
                    <option value="Ice Blended Frappes">Ice Blended Frappes</option>
                </select>
            </div>

            <div style="margin-bottom: 15px;">
                <label style="display: block; font-size: 13px; font-weight: 600; margin-bottom: 6px;">Drink Name</label>
                <input type="text" id="add_name" style="width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: 6px; outline: none;" required>
            </div>

            <div style="margin-bottom: 15px;">
                <label style="display: flex; align-items: center; gap: 8px; font-size: 13px; font-weight: 600;">
                    <input type="checkbox" id="add_single_size" onchange="toggleLargePrice()"> Single Size Only? (No Large)
                </label>
            </div>

            <div style="display: flex; gap: 15px; margin-bottom: 15px;">
                <div style="flex: 1;">
                    <label style="display: block; font-size: 13px; font-weight: 600; margin-bottom: 6px;" id="lbl_priceM">Medium Price (₱)</label>
                    <input type="number" id="add_priceM" step="0.01" style="width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: 6px; outline: none;" required>
                </div>
                <div style="flex: 1;" id="div_priceL">
                    <label style="display: block; font-size: 13px; font-weight: 600; margin-bottom: 6px;">Large Price (₱)</label>
                    <input type="number" id="add_priceL" step="0.01" style="width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: 6px; outline: none;" required>
                </div>
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: block; font-size: 13px; font-weight: 600; margin-bottom: 6px;">Upload Product Image</label>
                <input type="file" id="add_image" accept="image/png, image/jpeg, image/webp" style="width: 100%; padding: 8px; border: 1px dashed var(--border-color); border-radius: 6px; background: #fbf8f5; font-size: 12px; outline: none;" required>
            </div>

            <div class="admin-modal-actions" style="display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" onclick="closeAddModal()" style="background: transparent; color: #6d5242; border: 1px solid #6d5242; padding: 10px 20px; border-radius: 20px; cursor: pointer; font-weight: 600;">Cancel</button>
                <button type="submit" style="background-color: #6d5242; color: white; border: none; padding: 10px 20px; border-radius: 20px; cursor: pointer; font-weight: 600;">+ Add Drink</button>
            </div>
        </form>
    </div>
</div>

<script>
function forceSubmitOrder() {
    const badgeElement = document.getElementById('cart-counter');
    const visualTotalCount = badgeElement ? parseInt(badgeElement.innerText.trim()) : 0;

    if (visualTotalCount === 0 || isNaN(visualTotalCount)) {
        alert("The shopping tray cannot be submitted empty.");
        return;
    }

    // 1. Fetch data from your cart loader endpoint
    fetch('add_to_cart.php?action=load')
    .then(res => res.json())
    .then(cartResponse => {
        let serverCalculatedPrice = 0;

        // 2. SUCCESS ENGINE: Read and parse the stringified cartData array from the server
        if (cartResponse.cartData) {
            try {
                // Parse the inner JSON string into an actual JavaScript array
                let actualItemsArray = typeof cartResponse.cartData === 'string' 
                    ? JSON.parse(cartResponse.cartData) 
                    : cartResponse.cartData;

                // Loop through your items and add up their total costs safely
                actualItemsArray.forEach(item => {
                    serverCalculatedPrice += parseFloat(item.price || 0);
                });
            } catch (e) {
                console.error("Error parsing the internal cartData array structure:", e);
                serverCalculatedPrice = 0;
            }
        }

        // 3. Locate your sidebar dropdowns and options
        const paymentSelect = document.getElementById('payment_method') || document.querySelector('select');
        const paymentMethodValue = paymentSelect ? paymentSelect.value : 'Manual Online Transfer';
        const finalPaymentMethod = (paymentMethodValue.includes('Online') || paymentMethodValue === 'Online') ? 'Online' : 'COD';

        const pickupSelect = document.getElementById('pickup_type') || document.querySelector('select[name="pickup_type"]') || document.querySelector('select:last-of-type');
        const pickupType = pickupSelect ? pickupSelect.value : 'Store Pickup (In-Store)';

        const fileInput = document.getElementById('receipt_file') || document.querySelector('input[type="file"]');

        // 4. Construct form data payload map
        let formData = new FormData();
        formData.append('payment_method', finalPaymentMethod);
        formData.append('pickup_type', pickupType);
        
        if (fileInput && fileInput.files[0]) {
            formData.append('receipt_file', fileInput.files[0]);
        }

        // Pass the actual extracted price array directly back to satisfy submit_order.php
        const synchronizedCartPayload = [{ name: 'Coffee Selection', price: serverCalculatedPrice, qty: visualTotalCount }];
        formData.append('cart_data', JSON.stringify(synchronizedCartPayload));

        // 5. Submit your order parameters
        return fetch('submit_order.php', {
            method: 'POST',
            body: formData
        });
    })
    .then(response => {
        if (!response) return;
        return response.json();
    })
    .then(data => {
        if (!data) return;
        
        if (data.status === 'success') {
            const cartSidebar = document.querySelector('.cart-sidebar') || document.getElementById('cart-drawer');
            if(cartSidebar) cartSidebar.classList.remove('active');

            // Render elements onto the Confirmation Receipt sidebar card interface
            document.getElementById('rcpt-id').innerText = data.order_id;
            document.getElementById('rcpt-name').innerText = data.customer_name;
            document.getElementById('rcpt-payment').innerHTML = data.payment_method + '<br><small style="font-size: 10px; color: var(--muted-gray);">Pending Payment 💸</small>';
            document.getElementById('rcpt-pickup').innerText = data.pickup_type; 
            
            // This will now display your real, calculated currency price values!
            document.getElementById('rcpt-total').innerText = '₱' + parseFloat(data.total_amount).toFixed(2);
            
            localStorage.removeItem('cart_data');
            document.getElementById('receipt-sidebar').classList.add('active');
        } else {
            alert("Error: " + data.message);
        }
    })
    .catch(error => {
        console.error('Checkout loop computation breakdown:', error);
        alert("Something went wrong processing your order total.");
    });
}

function closeReceiptAndReload() {
    document.getElementById('receipt-sidebar').classList.remove('active');
    // I-refresh ang page para ma-clear talaga ang laman ng tray cart sa UI
    setTimeout(() => { window.location.reload(); }, 300);
}
function openAddModal() {
    document.getElementById("addModal").classList.add("active");
}

function closeAddModal() {
    document.getElementById("addModal").classList.remove("active");
    document.getElementById("addProductForm").reset(); // Clear the form
}

function toggleLargePrice() {
    const isSingle = document.getElementById("add_single_size").checked;
    const divLarge = document.getElementById("div_priceL");
    const inputLarge = document.getElementById("add_priceL");
    const labelMedium = document.getElementById("lbl_priceM");

    if (isSingle) {
        divLarge.style.display = "none";
        inputLarge.required = false;
        inputLarge.value = "0";
        labelMedium.innerText = "Price (₱)";
    } else {
        divLarge.style.display = "block";
        inputLarge.required = true;
        inputLarge.value = "";
        labelMedium.innerText = "Medium Price (₱)";
    }
}

function submitNewProduct() {
    let formData = new FormData();
    formData.append("category", document.getElementById("add_category").value);
    formData.append("product_name", document.getElementById("add_name").value);
    formData.append("price_medium", document.getElementById("add_priceM").value);
    formData.append("price_large", document.getElementById("add_priceL").value);
    
    if (document.getElementById("add_single_size").checked) {
        formData.append("single_size", "1");
    }

    let imageFile = document.getElementById("add_image").files[0];
    if (imageFile) {
        formData.append("product_image", imageFile);
    }

    // ... (code mo sa taas) ...
    fetch("add_product.php", {
        method: "POST",
        body: formData
    })
    .then(response => response.text())
    .then(data => {
        if(data.trim() === "success") {
            closeAddModal();
            showSuccessToast("New Drink Added!");
            setTimeout(() => { window.location.reload(); }, 1000);
        } else {
            alert("Server returned: " + data);
        }
    })
    .catch(error => {
        console.error("Error:", error);
        alert("An error occurred while saving.");
    });
}
</script> 

<div id="deleteConfirmModal" class="admin-modal-overlay">
    <div class="admin-edit-modal" style="background: var(--clean-white); padding: 30px; border-radius: 12px; width: 90%; max-width: 400px; margin: auto; text-align: center; box-shadow: 0 10px 25px rgba(0,0,0,0.15);">
        <div style="width: 60px; height: 60px; border-radius: 50%; background: #fee2e2; color: #dc2626; display: flex; align-items: center; justify-content: center; font-size: 28px; margin: 0 auto 15px;">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="3 6 5 6 21 6"></polyline>
                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                <line x1="10" y1="11" x2="10" y2="17"></line>
                <line x1="14" y1="11" x2="14" y2="17"></line>
            </svg>
        </div>
        <h3 style="margin: 0 0 10px; font-family: 'Playfair Display', serif; font-size: 22px; color: var(--dark-charcoal);">Delete Drink?</h3>
        <p style="font-size: 14px; color: var(--muted-gray); margin-bottom: 25px; line-height: 1.5;">
            Are you sure you want to permanently delete this drink? This action cannot be undone.
        </p>
        <div style="display: flex; gap: 10px; justify-content: center;">
            <button onclick="closeDeleteModal()" style="flex: 1; padding: 12px; border: 1px solid var(--border-color); background: transparent; border-radius: 6px; cursor: pointer; font-weight: 600; color: var(--dark-charcoal); transition: 0.2s;">Cancel</button>
            <button id="confirmDeleteBtn" onclick="executeDeleteProduct()" style="flex: 1; padding: 12px; border: none; background: #6d5242; border-radius: 6px; cursor: pointer; font-weight: 600; color: white; transition: 0.2s;">Yes, Delete</button>
        </div>
    </div>
</div>

<script>
// Logic para sa delete
let productToDeleteId = null;

function deleteProduct(id) {
    productToDeleteId = id;
    document.getElementById('deleteConfirmModal').classList.add('active');
}

function closeDeleteModal() {
    productToDeleteId = null;
    document.getElementById('deleteConfirmModal').classList.remove('active');
}

function executeDeleteProduct() {
    if (!productToDeleteId) return;
    const delBtn = document.getElementById('confirmDeleteBtn');
    delBtn.innerText = "Deleting...";
    delBtn.disabled = true;

    let formData = new FormData();
    formData.append("id", productToDeleteId);

    fetch("delete_product.php", { method: "POST", body: formData })
    .then(response => response.text())
    .then(data => {
        if(data.trim() === "success") {
            closeDeleteModal();
            showSuccessToast("Drink Deleted Successfully!");
            setTimeout(() => { window.location.reload(); }, 1000);
        } else {
            alert("Error: " + data);
            delBtn.innerText = "Yes, Delete";
            delBtn.disabled = false;
        }
    });
}
</script>

<?php include 'footer.php'; ?>