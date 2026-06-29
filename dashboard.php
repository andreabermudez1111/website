<?php
include 'header.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$successMsg = "";

/* ==========================================
   DELETE ADDRESS
========================================== */

if(isset($_POST['delete_address'])){

    $address_id = intval($_POST['address_id']);

    $stmt = $conn->prepare("
        DELETE FROM user_addresses
        WHERE id = ?
        AND user_id = ?
    ");

    $stmt->bind_param(
        "ii",
        $address_id,
        $user_id
    );

    $stmt->execute();
    $stmt->close();

    $successMsg =
"<div id='successAlert' class='success-alert'>
    Address deleted successfully.
</div>";
}

if(isset($_POST['update_address'])){

    $address_id =
    intval($_POST['edit_address_id']);

    $address_name =
    trim($_POST['edit_address_name']);

    $full_address =
    trim($_POST['edit_full_address']);

     $ncrCities = [
    'manila',
    'quezon city',
    'makati',
    'pasig',
    'taguig',
    'pasay',
    'mandaluyong',
    'marikina',
    'muntinlupa',
    'paranaque',
    'parañaque',
    'las pinas',
    'las piñas',
    'caloocan',
    'malabon',
    'navotas',
    'valenzuela',
    'san juan',
    'pateros'
];

$isNCR = false;

foreach($ncrCities as $city){

    if(stripos($full_address, $city) !== false){

        $isNCR = true;
        break;
    }
}

if(!$isNCR){

    $successMsg =
    "<div class='error-alert'>
        Sorry, delivery service is available only within NCR.
    </div>";

}
else{

    $stmt = $conn->prepare("
        UPDATE user_addresses
        SET
            address_name=?,
            full_address=?
        WHERE
            id=?
            AND user_id=?
    ");

    $stmt->bind_param(
        "ssii",
        $address_name,
        $full_address,
        $address_id,
        $user_id
    );

    $stmt->execute();
    $stmt->close();

    $successMsg =
    "<div id='successAlert' class='success-alert'>
        Address updated successfully.
    </div>";
}
}

/* ==========================================
   ADD NEW ADDRESS
========================================== */
if(isset($_POST['add_address'])){

    $address_name = trim($_POST['address_name']);

    $full_address =
    !empty($_POST['full_address'])
    ? trim($_POST['full_address'])
    : trim($_POST['manual_address']);

    $ncrCities = [
        'manila',
        'quezon city',
        'makati',
        'pasig',
        'taguig',
        'pasay',
        'mandaluyong',
        'marikina',
        'muntinlupa',
        'paranaque',
        'parañaque',
        'las pinas',
        'las piñas',
        'caloocan',
        'malabon',
        'navotas',
        'valenzuela',
        'san juan',
        'pateros'
    ];

    $isNCR = false;

    foreach($ncrCities as $city){

        if(
            stripos($full_address, $city)
            !== false
        ){
            $isNCR = true;
            break;
        }
    }

    if(!$isNCR){

        $successMsg =
        "<div class='error-alert'>
            Sorry, delivery service is available only within NCR.
        </div>";

    }
    elseif(!empty($address_name) && !empty($full_address)){

    $stmt = $conn->prepare("
        INSERT INTO user_addresses
        (
            user_id,
            address_name,
            full_address,
            latitude,
            longitude
        )
        VALUES(?,?,?,?,?)
    ");

    $latitude =
    !empty($_POST['latitude'])
    ? floatval($_POST['latitude'])
    : null;

    $longitude =
    !empty($_POST['longitude'])
    ? floatval($_POST['longitude'])
    : null;

    $stmt->bind_param(
        "issdd",
        $user_id,
        $address_name,
        $full_address,
        $latitude,
        $longitude
    );

    if($stmt->execute()){

        $successMsg =
        "<div id='successAlert' class='success-alert'>
            Address added successfully.
        </div>";

    }

    $stmt->close();
}
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'cancel_order') {
    $order_id = intval($_POST['order_id']);
    
    $checkStmt = $conn->prepare("SELECT status FROM orders WHERE id = ? AND user_id = ?");
    $checkStmt->bind_param("ii", $order_id, $user_id);
    $checkStmt->execute();
    $res = $checkStmt->get_result();
    
    if ($res->num_rows === 1) {
        $order = $res->fetch_assoc();
        if ($order['status'] === 'Pending') {
            $updateStmt = $conn->prepare("UPDATE orders SET status = 'Cancelled' WHERE id = ?");
            $updateStmt->bind_param("i", $order_id);
            if ($updateStmt->execute()) {
               $successMsg = "<div id='successAlert' class='success-alert'>
                    Order #{$order_id} has been cancelled successfully.
                    </div>";
            }
            $updateStmt->close();
        } else {
            $successMsg = "<div class='error-alert'>You cannot cancel an order that is already being processed.</div>";
        }
    }
    $checkStmt->close();
}

$ordersQuery = "SELECT * FROM orders WHERE user_id = ? ORDER BY order_date DESC";
$stmt = $conn->prepare($ordersQuery);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$ordersResult = $stmt->get_result();
?>

<!-- MOKA POT THEME DASHBOARD STYLES -->
<style>
    .dashboard-wrapper { max-width: 1100px; margin: 80px auto; padding: 0 4%; min-height: 60vh; }
    .dashboard-title { font-family: 'Playfair Display', serif; color: var(--dark-charcoal); font-size: 38px; margin-bottom: 10px; }
    .dashboard-divider { width: 80px; height: 3px; background-color: var(--crema-gold); margin-bottom: 40px; }
    
    .dashboard-card { background-color: var(--clean-white); border: 1px solid var(--border-color); border-radius: 8px; padding: 35px; margin-bottom: 40px; box-shadow: 0 10px 30px rgba(43, 29, 20, 0.05); border-left: 4px solid var(--crema-gold); }
    .dashboard-card h2 { font-size: 18px; font-weight: 600; color: var(--dark-charcoal); margin-bottom: 20px; text-transform: uppercase; letter-spacing: 1.5px; }
    
    .moka-table { width: 100%; border-collapse: collapse; font-family: 'Inter', sans-serif; }
    .moka-table th { background-color: var(--dark-charcoal); color: var(--crema-gold); padding: 18px 16px; text-align: left; font-size: 12px; text-transform: uppercase; letter-spacing: 1px; }
    .moka-table th:first-child { border-top-left-radius: 6px; }
    .moka-table th:last-child { border-top-right-radius: 6px; }
    .moka-table td { padding: 18px 16px; border-bottom: 1px solid var(--border-color); color: var(--muted-gray); font-size: 14px; transition: background-color 0.2s ease; }
    .moka-table tr:hover td { background-color: #fafafa; }
    
    .cancel-btn-moka { display: inline-block; background: transparent; border: 1px solid #c94a4a; color: #c94a4a; padding: 8px 16px; font-size: 11px; font-weight: 700; text-transform: uppercase; text-decoration: none; border-radius: 4px; transition: all 0.3s ease; cursor: pointer; }
    .cancel-btn-moka:hover { background-color: #c94a4a; color: var(--clean-white); transform: translateY(-2px); box-shadow: 0 4px 10px rgba(201, 74, 74, 0.2); }

</style>

<div class="dashboard-wrapper">
    <h1 class="dashboard-title">Client Account Panel</h1>
    <div class="dashboard-divider"></div>
    
    <?php echo $successMsg; ?>
    
    <!-- ACCOUNT DETAILS CARD -->
    <div class="dashboard-card">
        <h2>My Account Details</h2>
        <div style="font-size: 15px; color: var(--muted-gray); line-height: 2;">
            <p class="account-info">
                <span class="account-label">Username:</span>
                <?php echo htmlspecialchars($_SESSION['username']); ?>
            </p>
            <?php
                $addrStmt = $conn->prepare("
                    SELECT * 
                    FROM user_addresses
                    WHERE user_id = ?
                    ORDER BY is_default DESC, id ASC
                ");
                $addrStmt->bind_param("i", $user_id);
                $addrStmt->execute();
                $addrResult = $addrStmt->get_result();
                ?>

                <hr style="margin:20px 0;">

                <h3>Saved Delivery Addresses</h3>

                <?php while($addr = $addrResult->fetch_assoc()): ?>

<div style="
    display:flex;
    justify-content:space-between;
    align-items:flex-start;
    margin-bottom:20px;
">

    <div style="flex:1;">

    <p class="address-name">
        <?php echo htmlspecialchars($addr['address_name']); ?>
    </p>

    <p class="address-text">
        <?php echo htmlspecialchars($addr['full_address']); ?>
    </p>

</div>

    <div style="
        display:flex;
        flex-direction:column;
        gap:8px;
        margin-left:20px;
    ">

                <button
                    type="button"
                    class="address-btn"
                    onclick="openEditModal(
                        <?php echo $addr['id']; ?>,
                        '<?php echo addslashes($addr['address_name']); ?>',
                        '<?php echo addslashes($addr['full_address']); ?>'
                    )">
                    Edit
                </button>

            <form method="POST">

                <input
                    type="hidden"
                    name="address_id"
                    value="<?php echo $addr['id']; ?>"
                >

                <button
                type="submit"
                name="delete_address"
                class="address-btn delete-btn">
                Delete
            </button>

            </form>

    </div>

</div>

<hr style="margin:20px 0;">

<?php endwhile; ?>
        

<h3>Add New Address</h3>

<form method="POST" id="addAddressForm">
    <input
        type="text"
        name="address_name"
        placeholder="Home, School, Office..."
        required
        style="
            width:100%;
            padding:10px;
            margin-bottom:10px;
            border:1px solid #ddd;
            border-radius:5px;
        ">

    <input
        type="text"
        id="addressSearch"
        placeholder="Search your location..."
        style="
            width:100%;
            padding:10px;
            margin-bottom:10px;
            border:1px solid #ddd;
            border-radius:5px;
        ">

    <div
        id="suggestions"
        style="
            background:white;
            border:1px solid #ddd;
            border-radius:5px;
            margin-bottom:10px;
            max-height:200px;
            overflow:auto;
        ">
    </div>

    <input
        type="hidden"
        name="full_address"
        id="full_address">

    <input
        type="hidden"
        name="latitude"
        id="latitude">

    <input
        type="hidden"
        name="longitude"
        id="longitude">

<textarea
    id="manual_address"
    name="manual_address"
    placeholder="Enter full delivery address"
    style="
        width:100%;
        min-height:80px;
        padding:10px;
        border:1px solid #ddd;
        border-radius:5px;
        margin-bottom:10px;
    ">
</textarea>

    <div
        id="map"
        style="
            height:350px;
            border-radius:8px;
            margin-bottom:15px;
        ">
    </div>

    <button
        type="submit"
        name="add_address"
        style="
            background:#3b2416;
            color:white;
            border:none;
            padding:10px 20px;
            border-radius:5px;
            cursor:pointer;
        ">
        Add Address
    </button>

</form>
        </div>
    </div>

    <!-- ORDER LOGS CARD -->
    <div class="dashboard-card" style="border-left: none; padding: 0; overflow: hidden;">
        <h2 style="padding: 30px 30px 10px 30px;">Order Logs and Activity Transactions</h2>
        
        <?php if ($ordersResult->num_rows === 0): ?>
            <p style="padding: 0 30px 30px 30px; color: var(--muted-gray);">You have not placed any orders yet. Visit our <a href="shop.php" style="color: var(--crema-gold); font-weight: 600; text-decoration: none;">Shop Menu</a> to start ordering!</p>
        <?php else: ?>
            <div style="overflow-x: auto;">
                <table class="moka-table">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Transaction Date</th>
                            <th>Line Items Purchased</th>
                            <th>Grand Total</th>
                            <th>Billing Method</th>
                            <th>Status Flag</th>
                            <th>Action Controls</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($ord = $ordersResult->fetch_assoc()): ?>
                            <tr>
                                <td style="font-weight: 600; color: var(--dark-charcoal);">#<?php echo $ord['id']; ?></td>
                                <td><?php echo date('Y-m-d h:i A', strtotime($ord['order_date'])); ?></td>
                                <td style="line-height: 1.6; max-width: 320px;">
                                    <ul style="list-style: none; padding: 0; margin: 0; font-size: 13px;">
                                        <?php 
                                        $itStmt = $conn->prepare("SELECT * FROM order_items WHERE order_id = ?");
                                        $itStmt->bind_param("i", $ord['id']);
                                        $itStmt->execute();
                                        $itRes = $itStmt->get_result();
                                        while ($it = $itRes->fetch_assoc()) {
                                            echo "<li style='margin-bottom: 4px;'><strong>{$it['quantity']}x</strong> {$it['product_name']} <span style='color: #888;'>({$it['size']}, {$it['ice']} Ice)</span> - ₱" . number_format($it['price'], 2) . "</li>";
                                        }
                                        $itStmt->close();
                                        ?>
                                    </ul>
                                </td>
                                <td style="font-weight: 700; color: var(--dark-charcoal);">₱<?php echo number_format($ord['total_price'], 2); ?></td>
                                <td><?php echo $ord['payment_method'] === 'Online' ? 'GCash' : 'COD'; ?></td>
                                <td>
                                    <span class="status-badge <?php echo strtolower($ord['status']); ?>">
                                        <?php echo $ord['status']; ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($ord['status'] === 'Pending'): ?>
                                        <form method="POST" action="dashboard.php" onsubmit="return confirm('Confirm cancellation of order #<?php echo $ord['id']; ?>?');" style="margin: 0;">
                                            <input type="hidden" name="action" value="cancel_order">
                                            <input type="hidden" name="order_id" value="<?php echo $ord['id']; ?>">
                                            <button type="submit" class="cancel-btn-moka">Cancel Order</button>
                                        </form>
                                    <?php else: ?>
                                        <span style="font-size: 13px; color: var(--muted-gray); font-style: italic;">Finalized</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<div id="editAddressModal"
     style="
        display:none;
        position:fixed;
        inset:0;
        background:rgba(0,0,0,.5);
        z-index:9999;
        justify-content:center;
        align-items:center;
     ">

    <div style="
        background:white;
        width:500px;
        padding:25px;
        border-radius:10px;
    ">

        <h2>Edit Address</h2>

       <form method="POST">

            <input
                type="hidden"
                id="edit_address_id"
                name="edit_address_id">

            <label>Address Name</label>

            <input
                type="text"
                id="edit_address_name"
                name="edit_address_name"
                style="width:100%;padding:10px;margin:10px 0;">

            <label>Full Address</label>

            <textarea
                id="edit_full_address"
                name="edit_full_address"
                style="
                    width:100%;
                    height:100px;
                    padding:10px;
                    margin:10px 0;
                ">
            </textarea>

            <div style="
    display:flex;
    justify-content:flex-end;
    gap:10px;
">

    <button
        type="button"
        onclick="closeEditModal()"
        style="
            background:#a67c63;
            color:white;
            border:none;
            padding:10px 18px;
            border-radius:6px;
            cursor:pointer;
            font-size:14px;
            font-weight:500;
        ">
        Cancel
    </button>

    <button
        type="submit"
        name="update_address"
        style="
            background:#a67c63;
            color:white;
            border:none;
            padding:10px 18px;
            border-radius:6px;
            cursor:pointer;
            font-size:14px;
            font-weight:500;
        ">
        Save Changes
    </button>

</div>

        </form>

    </div>

</div>

<script>

function openEditModal(id, name, address){

    document.getElementById(
        'edit_address_id'
    ).value = id;

    document.getElementById(
        'edit_address_name'
    ).value = name;

    document.getElementById(
        'edit_full_address'
    ).value = address;

    document.getElementById(
        'editAddressModal'
    ).style.display = 'flex';
}

function closeEditModal(){

    document.getElementById(
        'editAddressModal'
    ).style.display = 'none';
}

document.getElementById(
    'addAddressForm'
).addEventListener(
    'submit',
    function(){

        let hiddenAddress =
            document.getElementById(
                'full_address'
            ).value.trim();

        let manualAddress =
            document.getElementById(
                'manual_address'
            ).value.trim();

        if(hiddenAddress === ''){

            document.getElementById(
                'full_address'
            ).value =
                manualAddress;
        }

    }
);

document.addEventListener(
    "DOMContentLoaded",
    function(){

        const successAlert =
            document.getElementById(
                "successAlert"
            );

        if(successAlert){

            setTimeout(function(){

                successAlert.style.transition =
                    "opacity 0.5s ease";

                successAlert.style.opacity =
                    "0";

                setTimeout(function(){
                    successAlert.remove();
                },500);

            },1000);

        }

    }
);

</script>

<?php include 'footer.php'; ?>