let cartItems = [];
let itemIdCounter = Date.now();
let currentProduct = {};
let qty = 1;

// Load active database configuration state on startup
document.addEventListener("DOMContentLoaded", () => {
    // 1. Run the initial cart sync across ALL pages
    syncCartUI();
    
    // 2. Initialize the search menu listeners safely (it will auto-skip if the search box isn't on the page)
    searchMenu();
    
    // Safety check the customizer size select listener
    const sizeSelect = document.getElementById('modal-size');
    if (sizeSelect) {
        sizeSelect.addEventListener('change', updateModalPrice);
    }
    
    // Safety check your checkout form submissions if they exist on the current page
    const checkoutForm = document.getElementById('checkout-form') || document.querySelector('form[action="submit_order.php"]'); 
    if (checkoutForm) {
        checkoutForm.addEventListener('submit', prepareCheckoutForm);
    }
});

// ==========================================
// CAROUSEL SLIDER (Wrapped Safely)
// ==========================================
let currentSlideIndex = 0;
const track = document.getElementById("heroTrack");
if (track) {
    setInterval(() => {
        currentSlideIndex = (currentSlideIndex === 0) ? 1 : 0;
        jumpToSlide(currentSlideIndex);
    }, 6000);
}

function jumpToSlide(index) {
    currentSlideIndex = index;
    const track = document.getElementById("heroTrack");
    if (track) {
        track.style.transform = `translateX(-${index * 100}vw)`;
    }
    const dots = document.querySelectorAll(".carousel-dots .dot");
    dots.forEach((dot, idx) => {
        if (idx === index) dot.classList.add("active");
        else dot.classList.remove("active");
    });
}

// Quantity Counter Adjustments
function changeQty(amount) {
    // Read current state cleanly from UI to sync across files
    let qtySpan = document.getElementById('modal-qty');
    if (!qtySpan) return;
    
    let currentQty = parseInt(qtySpan.innerText) || 1;
    currentQty += amount;
    if (currentQty < 1) currentQty = 1;
    
    qtySpan.innerText = currentQty;
    updateModalPrice();
}

// Routes to Shop Page
function routeDirectToProduct(name, priceM, priceL, isSingleSize = false) {
    window.location.href = `shop.php?open=${encodeURIComponent(name)}&pM=${priceM}&pL=${priceL}&single=${isSingleSize}`;
}

window.openCustomizer = function(name, pM, pL, single, imagePath) {
    // IGALAW: I-save ang product data sa global variable
    currentProduct = {
        name: name,
        priceM: parseFloat(pM),
        priceL: parseFloat(pL),
        isSingle: (single === true || single === 'true')
    };

    document.getElementById('modal-product-title').innerText = name;
    document.getElementById('modal-product-img').src = imagePath;
    document.getElementById('modal-total-price').innerText = currentProduct.priceM.toFixed(2);
    
    // I-reset ang quantity sa 1 tuwing magbubukas ng bagong modal
    document.getElementById('modal-qty').innerText = '1';

    const sizeWrapper = document.getElementById('size-group-wrapper');
    const sizeSelect = document.getElementById('modal-size');
    
    if (currentProduct.isSingle) {
        sizeWrapper.style.display = 'none';
    } else {
        sizeWrapper.style.display = 'block';
        sizeSelect.innerHTML = `
            <option value="Medium">Medium - ₱${currentProduct.priceM.toFixed(2)}</option>
            <option value="Large">Large - ₱${currentProduct.priceL.toFixed(2)}</option>
        `;
    }
    document.getElementById('customizer-modal').classList.add('active');
};

function updateModalPrice() {
    const qty = parseInt(document.getElementById('modal-qty').innerText) || 1;
    const size = document.getElementById('modal-size').value;
    
    // Gamitin ang data mula sa global 'currentProduct'
    let unitPrice = (size === 'Large' && !currentProduct.isSingle) ? currentProduct.priceL : currentProduct.priceM;
    
    document.getElementById('modal-total-price').innerText = (unitPrice * qty).toFixed(2);
}

// 1. FIXED CLOSE FUNCTION (Unlocks the screen properly)
function closeCustomizer() {
    const modal = document.getElementById('customizer-modal');
    if (modal) {
        modal.classList.remove('active');
    }
    // This strips the scroll lock from both the body AND the html completely
    document.body.style.overflow = ''; 
    document.documentElement.style.overflow = ''; 
}

// 2. FIXED ADD TO CART FUNCTION (Reads actual quantity and sizes)
function addCustomizedItemToCart() {
    // ... (logic para sa item object) ...
    const newItem = {
        name: currentProduct.name,
        price: total,
        qty: currentQty,
        size: size,
        ice: ice,
        sugar: sugar 
    };

    saveCart(newItem); // I-save sa server
    closeCustomizer();
    toggleCart(true);
}

function removeItemFromCart(targetId) {
    cartItems = cartItems.filter(item => item.id !== targetId);
    saveCart();
}

function saveCart(item) {
    // Format payload as URL-encoded key-value pairs matching add_to_cart.php variables
    const payload = `action=add&name=${encodeURIComponent(item.name)}&size=${encodeURIComponent(item.size)}&qty=${item.qty}&ice=${encodeURIComponent(item.ice)}&sugar=${encodeURIComponent(item.sugar)}&price=${item.price}`;

    fetch('add_to_cart.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: payload
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            syncCartUI(); // Re-fetch the refreshed list state mapping from the DB
        }
    });
}

function syncCartUI() {
    fetch('add_to_cart.php?action=load')
    .then(response => response.json())
    .then(data => {
        const container = document.getElementById('cart-items-container');
        const counter = document.getElementById('cart-counter');
        
        // Safety checks: only modify elements if they actually exist on the current page
        if (counter && data.totalItems !== undefined) {
            counter.innerText = data.totalItems;
        }
        if (container && data.html) {
            container.innerHTML = data.html;
        }
        
        if (data.cartData) {
            try {
                cartItems = JSON.parse(data.cartData);
            } catch (e) {
                cartItems = [];
            }
        }
    })
    .catch(err => console.log("Cart sync skipped or not logged in yet."));
}

function toggleCart(isOpen) {
    const drawer = document.getElementById('cart-drawer');
    if (drawer) {
        if (isOpen) drawer.classList.add('active');
        else drawer.classList.remove('active');
    }
}

// Payment Selection Handlers
function handlePaymentMethodChange() {
    const paymentMethod = document.getElementById('payment_method').value;
    const uploadNode = document.getElementById('payment-instructions');
    const receiptInput = document.getElementById('receipt_file');

    if (paymentMethod === 'Online') {
        uploadNode.classList.remove('hidden');
        receiptInput.setAttribute('required', 'required');
    } else {
        uploadNode.classList.add('hidden');
        receiptInput.removeAttribute('required');
    }
}

// Checkout Formatting
function prepareCheckoutForm(e) {
    if (cartItems.length === 0) {
        e.preventDefault();
        alert("The shopping tray cannot be submitted empty.");
        return false;
    }
    
    // Make sure your HTML input element ID matches 'cart_data' or 'cart_data_input'
    const input = document.getElementById('cart_data') || document.getElementById('cart_data_input');
    if (input) {
        input.value = JSON.stringify(cartItems);
    }
    return true;
}



// ==================== MEGA MENU CAROUSEL SCROLL ====================
function scrollBestSellers(direction) {
    const carousel = document.getElementById('best-sellers-carousel');
    
    // Safety check para hindi mag-error kung nasa ibang page ang user
    if (carousel) {
        // Kukunin ng JS ang eksaktong lapad ng container (yung 3 items)
        const scrollAmount = carousel.clientWidth; 
        
        carousel.scrollBy({
            left: direction * scrollAmount,
            behavior: 'smooth'
        });
    }
}

// ==========================================
// SHOP LIVE SEARCH FUNCTION
// ==========================================
function searchMenu() {
    // FIX: Wrap it inside an event listener block so it only runs if the search input is interacting
    const searchInputElement = document.getElementById('shop-search-input');
    if (searchInputElement) {
        searchInputElement.addEventListener('input', () => {
            let input = searchInputElement.value.toLowerCase();
            let allCategories = document.querySelectorAll('.menu-category-group');

            if (input === '') {
                if (typeof filterMenu === 'function') { filterMenu(); }
                document.querySelectorAll('.product-card').forEach(card => { card.style.display = 'flex'; });
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

                category.style.display = hasVisibleCard ? 'block' : 'none';
            });
        });
    }
}