
<?php if (
    !isset($_SESSION['role']) ||
    $_SESSION['role'] !== 'admin'
): ?>
<div id="cart-drawer" class="cart-sidebar">
        <div class="cart-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3 style="font-family: 'Playfair Display', serif; color: var(--dark-charcoal); font-size: 35px;">Coffee Tray</h3>
            <button class="close-cart" onclick="document.getElementById('cart-drawer').classList.remove('active');" style="font-size: 28px; background: none; border: none; cursor: pointer; color: var(--muted-gray);">&times;</button>
        </div>
        
        <div class="cart-contents" id="cart-items-container">
            <p class="empty-msg" style="color: var(--muted-gray); text-align: center; margin-top: 40px; font-family: 'Inter', sans-serif;">Your active layout tray is empty.</p>
        </div>
        
        <form
        class="checkout-form"
        id="orderForm"
        action="submit_order.php"
        method="POST"
        enctype="multipart/form-data">
    <input type="hidden" name="cart_data" id="cart_data_input">
    
    <div class="payment-selection-box">
        <label style="font-size: 13px; font-weight: 600; color: var(--muted-gray); text-transform: uppercase;">Route Payment Via:</label>
        <select name="payment_method" id="payment_method" onchange="document.getElementById('payment-instructions').style.display = (this.value === 'Online') ? 'block' : 'none';" style="width: 100%; padding: 12px; margin-top: 8px; border-radius: 4px; border: 1px solid var(--border-color); outline: none; font-family: 'Inter', sans-serif;" required>
            <option value="COD">Cash on Delivery (COD)</option>
            <option value="Online">Manual Online Transfer</option>
            <option value="Pay at Store">Pay at Store (Cash/GCash)</option>
        </select>
        
        <div id="payment-instructions" style="display: none; margin-top: 10px; padding: 15px; background: #fef9f3; border: 1px dashed var(--crema-gold); border-radius: 6px;">
            <div style="font-size: 13px; margin-bottom: 18px; color: var(--dark-charcoal); line-height: 1.5;">
                <strong style="text-transform: uppercase; letter-spacing: 1px; font-size: 11px; color: var(--muted-gray);">Accepted Accounts:</strong><br>
                <div style="margin-top: 6px; padding: 8px; background: var(--clean-white); border: 1px solid var(--border-color); border-radius: 4px;">
                    <p style="margin-bottom: 4px;"><strong>GCash / Maya:</strong> 0954-425-8134 </p>
                </div>
            </div>

            <label style="display: block; font-size: 12px; font-weight: 600; text-transform: uppercase; margin-bottom: 8px;">Upload Proof of Payment:</label>
            <input type="file" name="receipt_file" id="receipt_file" accept="image/*" capture="environment" style="width: 100%; padding: 8px; border: 1px solid var(--border-color); border-radius: 4px; background: #fff; margin-bottom: 12px; font-size: 12px; cursor: pointer;">
        </div>
    </div>

    <div class="payment-selection-box" style="margin-top: 15px;">
        <label style="font-size: 13px; font-weight: 600; color: var(--muted-gray); text-transform: uppercase;">Order Type:</label>
<select name="pickup_type" id="pickup_type_selector" onchange="updateCheckoutFlow()" required ...>
                <option value="Store Pickup">Store Pickup (In-Store)</option>
            <option value="Delivery">Delivery</option>
        </select>
    </div>

    <div id="delivery-fee-note" style="display: none; margin-top: 10px; padding: 12px; background-color: #fef3c7; border-left: 3px solid #d97706; border-radius: 4px; font-size: 12px; color: #92400e; line-height: 1.5;">
        <strong>🚚 Note for Delivery:</strong> An additional delivery fee will be applied...
    </div>

<?php
$addressStmt = $conn->prepare("
SELECT *
FROM user_addresses
WHERE user_id = ?
ORDER BY id ASC
");

$addressStmt->bind_param(
"i",
$_SESSION['user_id']
);

$addressStmt->execute();
$addresses = $addressStmt->get_result();
?>

<div id="address-container" class="payment-selection-box" style="margin-top: 15px;">
    <label style="font-size: 13px; font-weight: 600; color: var(--muted-gray); text-transform: uppercase;">
        Delivery Address
    </label>
    
    <select 
        name="delivery_address" 
        id="delivery_address_select" 
        required 
        style="width: 100%; padding: 12px; margin-top: 8px; border-radius: 4px; border: 1px solid var(--border-color);">
        <?php 
        $addresses->data_seek(0); // This ensures the dropdown fills correctly
        while($address = $addresses->fetch_assoc()): ?>
            <option value="<?php echo htmlspecialchars($address['full_address']); ?>">
                <?php echo htmlspecialchars($address['address_name']); ?> - <?php echo htmlspecialchars($address['full_address']); ?>
            </option>
        <?php endwhile; ?>
    </select>
</div>

<script>
// 1. Unified Checkout Logic
function updateCheckoutFlow() {
    const pickupType = document.getElementById('pickup_type_selector').value;
    const paymentMethod = document.getElementById('payment_method');
    const addressContainer = document.getElementById('address-container');
    const addressSelect = document.getElementById('delivery_address_select');
    const note = document.getElementById('delivery-fee-note');

    if (pickupType === 'Store Pickup') {
        // Hide address fields
        addressContainer.style.display = 'none';
        addressSelect.removeAttribute('required');
        note.style.display = 'none';

        // Add "Pay at Store" back if missing
        if (!paymentMethod.querySelector('option[value="Pay at Store"]')) {
            const opt = document.createElement('option');
            opt.value = 'Pay at Store';
            opt.textContent = 'Pay at Store (Cash/GCash)';
            paymentMethod.appendChild(opt);
        }

        // Remove COD — not valid for in-store pickup
        const codOption = paymentMethod.querySelector('option[value="COD"]');
        if (codOption) {
            if (paymentMethod.value === 'COD') paymentMethod.value = 'Pay at Store';
            codOption.remove();
        }

        paymentMethod.value = 'Pay at Store';

    } else {
        // Delivery mode — show address fields
        addressContainer.style.display = 'block';
        addressSelect.setAttribute('required', 'required');
        note.style.display = 'block';

        // Add COD back if missing
        if (!paymentMethod.querySelector('option[value="COD"]')) {
            const opt = document.createElement('option');
            opt.value = 'COD';
            opt.textContent = 'Cash on Delivery (COD)';
            paymentMethod.insertBefore(opt, paymentMethod.firstChild);
        }

        // Remove "Pay at Store" — not valid for delivery
        const payAtStoreOption = paymentMethod.querySelector('option[value="Pay at Store"]');
        if (payAtStoreOption) {
            if (paymentMethod.value === 'Pay at Store') paymentMethod.value = 'COD';
            payAtStoreOption.remove();
        }
    }

    updatePaymentVisibility();
}

// 2. Payment Instructions Toggle
function updatePaymentVisibility() {
    const method = document.getElementById('payment_method').value;
    const instructions = document.getElementById('payment-instructions');
    instructions.style.display = (method === 'Online') ? 'block' : 'none';
}

// 3. Initialize Everything
document.addEventListener('DOMContentLoaded', () => {
    const pickupSelector = document.getElementById('pickup_type_selector');
    const paymentSelector = document.getElementById('payment_method');

    if(pickupSelector) pickupSelector.addEventListener('change', updateCheckoutFlow);
    if(paymentSelector) paymentSelector.addEventListener('change', () => {
        // Prevent Delivery + Pay at Store selection
        if (pickupSelector.value === 'Delivery' && paymentSelector.value === 'Pay at Store') {
            alert('Pay at Store is only available for Store Pickup.');
            paymentSelector.value = 'COD';
        }
        updatePaymentVisibility();
    });

    updateCheckoutFlow();
});
</script>

 <button type="button" 
        class="add-to-cart-trigger" 
        onclick="forceSubmitOrder()"
        style="width: 100%; margin-top: 15px; display: block; cursor: pointer;">
    ROUTE ORDER TO ADMIN
</button>
</form>

    </div>
<?php endif; ?>
    <footer class="shop-global-footer">
        <div class="footer-grid-wrapper">
            <div class="footer-column-block">
                <h2 class="footer-logo">G'S COFFEE</h2>
                <p class="footer-tagline">Crafting exceptional flavor ratios daily.</p>
            </div>
            <div class="footer-column-block">
                <h3>Shop Coordinates</h3>
                <p><a>2534 Singalong St., Manila, Philippines</a>
                <p><strong>Hotline:</strong> +63 917 123 4567</p>

                <p>
        <a href="https://maps.app.goo.gl/tfHtdb8pfSxz9p466" target="_blank">
            Get Directions
        </a>
    </p>

                
            </div>
            <div class="footer-column-block">
                <h3>Operating Hours</h3>
                <p>Mon - Fri: 3:00 PM - 9:00 PM</p>
                <p>Sat - Sun: 5:00 PM - 12:00 AM</p>
            </div>
            <div class="footer-column-block">
                <h3>Follow Our Facebook Page</h3>
                <p><a href="https://www.facebook.com/profile.php?id=61560581421136">Facebook Page</a></p>
            </div>
        </div>
        <div class="footer-bottom-copyright">
            <p>&copy; <?php echo date("Y"); ?> G's Coffee Shop. Developed for ITS122P Major Requirements.</p>
        </div>
    </footer>

    <script>
    // FUNCTION PARA I-UPDATE YUNG HIDDEN FORM INPUT
    function syncCartDataToForm(cartDataString) {
        const cartInput = document.getElementById('cart_data_input');
        if (cartInput) {
            cartInput.value = cartDataString;
        }
    }

    function refreshCartUI() {
        fetch('add_to_cart.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'action=load'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const cartCounter = document.getElementById('cart-counter');
                if(cartCounter) { cartCounter.innerText = data.totalItems; }
                const container = document.getElementById('cart-items-container');
                if(container) { container.innerHTML = data.html; }
                
                // I-sync din natin sa form!
                syncCartDataToForm(data.cartData);
            }
        }).catch(err => console.log(err));
    }

    // Call onload
    // Call onload
document.addEventListener('DOMContentLoaded', () => {
    // This fetches the cart from the DB/Session and syncs the hidden input
    refreshCartUI(); 

    const orderForm = document.getElementById('orderForm');
    if (orderForm) {
        orderForm.addEventListener('submit', function(e) {
            const cartInput = document.getElementById('cart_data_input').value;
            // Add a console log here to debug what exactly is in the cart when clicking
            console.log("Submitting cart data:", cartInput);
            
            if (!cartInput || cartInput === '[]' || cartInput === "") {
                e.preventDefault();
                alert('Your tray is empty or data failed to load. Please refresh the page.');
            }
        });
    }
});

window.onload = function() {
        
        // ==========================================
        // NEW SUCCESS MODAL CLOSE FUNCTION
        // ==========================================
        window.closeSuccessModal = function() {
            const successModal = document.getElementById('success-modal');
            if(successModal) {
                successModal.classList.remove('active');
            }
            document.body.style.overflow = ''; 
            document.documentElement.style.overflow = '';
        };

        // ==========================================
        // ADD TO CART FUNCTION (TRIGGERS NEW MODAL)
        // ==========================================
        window.addCustomizedItemToCart = function() {
            const productName = currentProduct.name;
            const size = document.getElementById('modal-size').value;
            const qty = parseInt(document.getElementById('modal-qty').innerText);
            const ice = document.getElementById('modal-ice').value;
            const sugarSelect = document.getElementById('modal-sugar');
            const sugar = sugarSelect ? sugarSelect.value : '100%';
            const price = parseFloat(document.getElementById('modal-total-price').innerText);

            // Capture the image currently showing in the customizer
            const imgSrc = document.getElementById('modal-product-img').src;

            const formData = 'action=add&name=' + encodeURIComponent(productName) + '&size=' + encodeURIComponent(size) + '&qty=' + qty + '&ice=' + encodeURIComponent(ice) + '&sugar=' + encodeURIComponent(sugar) + '&price=' + price;

            fetch('add_to_cart.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // 1. Update Cart Drawer Data
                    const cartCounter = document.getElementById('cart-counter');
                    if(cartCounter) { cartCounter.innerText = data.totalItems; }
                    const container = document.getElementById('cart-items-container');
                    if(container) { container.innerHTML = data.html; }
                    syncCartDataToForm(data.cartData);
                    
                    // 2. Populate the Success Modal
                    document.getElementById('success-img').src = imgSrc;
                    document.getElementById('success-name').innerText = productName;
                    document.getElementById('success-details').innerHTML = `Quantity: <strong>${qty}</strong><br>Size: ${size}<br>Ice: ${ice} | Sugar: ${sugar}`;
                    document.getElementById('success-price').innerText = '₱' + price.toFixed(2);

                    // 3. Close Customizer, Open Success Modal
                    closeCustomizer();
                    const successModal = document.getElementById('success-modal');
                    if(successModal) {
                        successModal.classList.add('active');
                    }
                    document.body.style.overflow = 'hidden'; // Keep screen locked for this modal
                } else {
                    // Isara ang customizer kung nakabukas
                    if (typeof closeCustomizer === "function") {
                        closeCustomizer();
                    }
                    // Palabasin ang bagong Login Required Modal
                    document.getElementById('loginRequiredModal').classList.add('active');
                }
            })
            .catch(error => console.error('Error:', error));
        };
        
        // ==========================================
        // REMOVE FROM CART FUNCTION
        // ==========================================
        window.removeCartItem = function(index) {
            fetch('add_to_cart.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'action=remove&index=' + index
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const cartCounter = document.getElementById('cart-counter');
                    if(cartCounter) { cartCounter.innerText = data.totalItems; }
                    const container = document.getElementById('cart-items-container');
                    if(container) { container.innerHTML = data.html; }
                    
                    syncCartDataToForm(data.cartData);
                }
            });
        };
        
        // ==========================================
        // TOGGLE CART DRAWER
        // ==========================================
        window.toggleCart = function(show) {
            const drawer = document.getElementById('cart-drawer');
            if(drawer) {
                if(show) { drawer.classList.add('active'); } 
                else { drawer.classList.remove('active'); }
            }
        };
    };
    </script>

    <div id="customizer-modal" class="modal-overlay">
    <div class="modal-box wide-modal">
        <button class="close-modal" onclick="closeCustomizer()">&times;</button>
        
        <div class="modal-split-layout">
            <div class="modal-image-column">
                <img id="modal-product-img" src="images/logo-placeholder.png" alt="Product Preview">
            </div>

            <div class="modal-options-column">
                <h3 id="modal-product-title">Product Name</h3>
                
                <div class="option-group" id="size-group-wrapper">
                    <label>Size</label>
                    <select id="modal-size" onchange="updateModalPrice()">
                        <option value="Medium">Medium</option>
                        <option value="Large">Large</option>
                    </select>
                </div>

                <div class="option-group">
                    <label>Quantity</label>
                    <div class="qty-selector">
                        <button type="button" onclick="changeQty(-1)">-</button>
                        <span id="modal-qty">1</span>
                        <button type="button" onclick="changeQty(1)">+</button>
                    </div>
                </div>

                <div class="option-group">
                    <label>Ice Matrix</label>
                    <select id="modal-ice">
                        <option value="Normal">Normal</option>
                        <option value="Less Ice">Less Ice</option>
                        <option value="No Ice">No Ice</option>
                    </select>
                </div>

                <div class="option-group">
                    <label>Sugar Level</label>
                    <select id="modal-sugar">
                        <option value="100%">100% (Normal)</option>
                        <option value="75%">75% (Less Sweet)</option>
                        <option value="50%">50% (Half Sweet)</option>
                        <option value="25%">25% (Low Sweet)</option>
                    </select>
                </div>

                <button class="add-to-cart-btn" onclick="addCustomizedItemToCart()">
                    Brew This Order - ₱<span id="modal-total-price">0.00</span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ADDED TO CART SUCCESS MODAL (Upper Middle Position) -->
<div id="success-modal" class="modal-overlay" style="align-items: flex-start; padding-top: 100px;" onclick="if(event.target === this) closeSuccessModal()">
    <div class="modal-box" style="max-width: 420px; padding: 25px; width:90%; background: var(--clean-white); border-radius: 8px; box-shadow: 0 15px 35px rgba(0,0,0,0.15);">
        
        <!-- Header -->
        <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border-color); padding-bottom: 15px; margin-bottom: 25px;">
            <h3 style="font-family: 'Inter', sans-serif; font-size: 16px; font-weight: 600; color: brown; display: flex; align-items: center; gap: 8px; margin: 0;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                Product successfully added to your tray!
            </h3>
            <button onclick="closeSuccessModal()" style="background: none; border: none; font-size: 28px; cursor: pointer; color: var(--muted-gray); line-height: 1;">&times;</button>
        </div>

        <!-- Product Summary Layout -->
        <div style="display: flex; gap: 25px; margin-bottom: 30px; align-items: center;">
            <div style="width: 120px; height: 140px; background: var(--bg-warm-creme); border-radius: 8px; display: flex; align-items: center; justify-content: center; overflow: hidden; border: 1px solid var(--border-color);">
                <img id="success-img" src="" alt="Product" style="width: 100%; height: 100%; object-fit: cover;">
            </div>
            <div style="flex: 1;">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 8px;">
                    <h4 id="success-name" style="font-size: 20px; font-weight: 700; color: var(--dark-charcoal); font-family: 'Playfair Display', serif; margin: 0;">Product</h4>
                    <span id="success-price" style="font-size: 16px; font-weight: 700; color: var(--dark-charcoal);">₱0.00</span>
                </div>
                <p id="success-details" style="font-size: 13px; color: var(--muted-gray); line-height: 1.6; margin: 0;"></p>
            </div>
        </div>

        <!-- Action Buttons -->
        <div style="display: flex; gap: 15px;">
            <button onclick="closeSuccessModal(); toggleCart(true);" style="flex: 1; padding: 14px; background: var(--dark-charcoal); color: var(--crema-gold); border: 1px solid var(--dark-charcoal); border-radius: 4px; font-size: 13px; font-weight: 600; text-transform: uppercase; letter-spacing: 1px; cursor: pointer; transition: all 0.3s;">View Tray</button>
            <button onclick="closeSuccessModal()" style="flex: 1; padding: 14px; background: transparent; color: var(--dark-charcoal); border: 1px solid var(--border-color); border-radius: 4px; font-size: 13px; font-weight: 600; text-transform: uppercase; letter-spacing: 1px; cursor: pointer; transition: all 0.3s;">Continue Shopping</button>
        </div>
    </div>
</div>

<div id="loginRequiredModal" class="admin-modal-overlay">
    <div class="admin-edit-modal" style="background: var(--clean-white); padding: 30px; border-radius: 12px; width: 90%; max-width: 400px; margin: auto; text-align: center; box-shadow: 0 10px 25px rgba(0,0,0,0.15);">
        
        <div style="width: 60px; height: 60px; border-radius: 50%; background: #fef3c7; color: #d97706; display: flex; align-items: center; justify-content: center; font-size: 28px; margin: 0 auto 15px;">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                <circle cx="12" cy="7" r="4"></circle>
            </svg>
        </div>

        <h3 style="margin: 0 0 10px; font-family: 'Playfair Display', serif; font-size: 22px; color: var(--dark-charcoal);">Authentication Required</h3>
        
        <p style="font-size: 14px; color: var(--muted-gray); margin-bottom: 25px; line-height: 1.5;">
            Looks like you are ordering as a guest! Please log in or create an account to start adding your favorite drinks to your tray.
        </p>

        <div style="display: flex; gap: 10px; justify-content: center;">
            <button onclick="document.getElementById('loginRequiredModal').classList.remove('active')" style="flex: 1; padding: 12px; border: 1px solid var(--border-color); background: transparent; border-radius: 6px; cursor: pointer; font-weight: 600; color: var(--dark-charcoal); transition: 0.2s;">Cancel</button>
            <button onclick="window.location.href='login.php'" style="flex: 1; padding: 12px; border: none; background: #6d5242; border-radius: 6px; cursor: pointer; font-weight: 600; color: white; transition: 0.2s;">Log In / Register</button>
        </div>
    </div>
</div>


    <script src="script.js?v=5"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
   
   <script>
if(document.getElementById('map')){

    const map = L.map('map').setView(
        [14.5995,120.9842],
        13
    );

    L.tileLayer(
        'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
        {
            attribution:'© OpenStreetMap'
        }
    ).addTo(map);

    const marker = L.marker(
        [14.5995,120.9842],
        {
            draggable:true
        }
    ).addTo(map);

    const searchBox =
        document.getElementById(
            'addressSearch'
        );

    const suggestions =
        document.getElementById(
            'suggestions'
        );

    const fullAddress =
        document.getElementById(
            'full_address'
        );

    const latitude =
        document.getElementById(
            'latitude'
        );

    const longitude =
        document.getElementById(
            'longitude'
        );

    marker.on(
        'dragend',
        function(e){

            let lat =
                e.target.getLatLng().lat;

            let lng =
                e.target.getLatLng().lng;

            latitude.value = lat;
            longitude.value = lng;

            fetch(
                `https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`
            )
            .then(res => res.json())
            .then(data => {

                if(data.display_name){

                    fullAddress.value =
                        data.display_name;

                    searchBox.value =
                        data.display_name;
                }

            });

        }
    );
    searchBox.addEventListener(
        'input',
        function(){
            let query =
                this.value.trim();
            if(query.length < 3){
                suggestions.innerHTML = '';
                return;
            }
            fetch(
                'https://photon.komoot.io/api/?q='
                + encodeURIComponent(query)
                + '&limit=5'
            )
            .then(res => res.json())
            .then(data => {
                suggestions.innerHTML='';
                data.features.forEach(function(place){
                    const div =
                        document.createElement(
                            'div'
                        );
                    const address =
                        (
                            place.properties.name || ''
                        )
                        + ' '
                        + (
                            place.properties.city || ''
                        )
                        + ' '
                        + (
                            place.properties.country || ''
                        );

                    div.innerHTML =
                        address;
                    div.style.padding =
                        '10px';
                    div.style.cursor =
                        'pointer';
                    div.style.borderBottom =
                        '1px solid #eee';
                    div.onmouseover =
                        () =>
                        div.style.background =
                        '#f5f5f5';
                    div.onmouseout =
                        () =>
                        div.style.background =
                        'white';
                    div.onclick =
                    function(){
                        const lat =
                            place.geometry.coordinates[1];
                        const lng =
                            place.geometry.coordinates[0];
                        map.setView(
                            [lat,lng],
                            18
                        );
                        marker.setLatLng(
                            [lat,lng]
                        );
                        latitude.value =
                            lat;
                        longitude.value =
                            lng;
                        searchBox.value =
                            address;
                        fullAddress.value =
                            address;
                        suggestions.innerHTML =
                            '';
                    };
                    suggestions.appendChild(
                        div
                    );
                });
            });
        }
    );
}

function toggleAddressVisibility() {
    // 1. Get the value of the order type
    const pickupType = document.getElementById('pickup_type_selector').value;
    
    // 2. Get the container and select
    const addressContainer = document.getElementById('address-container');
    const addressSelect = document.getElementById('delivery_address_select');

    // 3. Logic:
    if (pickupType === 'Store Pickup') {
        addressContainer.style.display = 'none';
        addressSelect.removeAttribute('required');
    } else {
        // This 'block' ensures it shows up when 'Delivery' is selected
        addressContainer.style.display = 'block'; 
        addressSelect.setAttribute('required', 'required');
    }
}

// 4. Run on page load and whenever the dropdown changes
document.addEventListener('DOMContentLoaded', () => {
    // Bind the event listener to the dropdown
    const selector = document.getElementById('pickup_type_selector');
    if (selector) {
        selector.addEventListener('change', toggleAddressVisibility);
    }
    
    // Run once on load to set correct state based on default selection
    toggleAddressVisibility();
});
</script>

</body>
</html>
