<?php
// ==========================================
// 1. INITIALIZE SESSIONS & DATABASE FIRST
// ==========================================
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'config.php';

// Authentication Check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// ==========================================
// 2. FILTER LOGIC (Needed for CSV and Table)
// ==========================================
$filter_date = isset($_GET['filter_date']) ? $_GET['filter_date'] : '';
$whereClause = "";

if (!empty($filter_date)) {
    $safe_date = $conn->real_escape_string($filter_date);
    $whereClause = " WHERE DATE(o.order_date) = '$safe_date' ";
}

// ==========================================
// 3. EXPORT CSV LOGIC 
// ==========================================
if (isset($_GET['export_csv'])) {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=GSCoffee_Orders_' . (empty($filter_date) ? 'All' : $filter_date) . '.csv');
    
    $output = fopen('php://output', 'w');
    fputcsv($output, ['Order ID', 'Customer Name', 'Items Ordered', 'Total Amount (PHP)', 'Date & Time', 'Status', 'Payment Method']);
    
    $exportQuery = "SELECT o.*, u.username FROM orders o JOIN users u ON o.user_id = u.id $whereClause ORDER BY o.order_date DESC";
    $exportResult = $conn->query($exportQuery);
    
    while ($row = $exportResult->fetch_assoc()) {
        $itemsStr = "";
        $itStmt = $conn->prepare("SELECT quantity, product_name FROM order_items WHERE order_id = ?");
        $itStmt->bind_param("i", $row['id']);
        $itStmt->execute();
        $itRes = $itStmt->get_result();
        while ($it = $itRes->fetch_assoc()) {
            $itemsStr .= $it['quantity'] . "x " . $it['product_name'] . "; ";
        }
        $itStmt->close();
        
        $formatted_id = "ORD-" . str_pad($row['id'], 5, '0', STR_PAD_LEFT);
        $date_formatted = date('M d, Y g:i A', strtotime($row['order_date']));
        
        fputcsv($output, [
            $formatted_id,
            $row['username'],
            trim($itemsStr, "; "),
            $row['total_price'],
            $date_formatted,
            $row['status'],
            $row['payment_method']
        ]);
    }
    
    fclose($output);
    exit();
}

// ==========================================
// 4. NOW INCLUDE HEADER & RENDER PAGE
// ==========================================
include 'header.php';

$notice = "";

// Handle Form Actions (Update Status)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    $order_id = intval($_POST['order_id']);
    $new_status = $_POST['status'];
    
    $updateStmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $updateStmt->bind_param("si", $new_status, $order_id);
    if ($updateStmt->execute()) {
        $formatted_id = "ORD-" . str_pad($order_id, 5, '0', STR_PAD_LEFT);
        $notice = "<div class='success-alert'>{$formatted_id} state updated to {$new_status}!</div>";
    }
    $updateStmt->close();
}

// Pagination Logic
$limit = 10; 
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$countQuery = "SELECT COUNT(*) as total FROM orders o" . $whereClause;
$totalResult = $conn->query($countQuery);
$total_rows = $totalResult->fetch_assoc()['total'];
$total_pages = ceil($total_rows / $limit);

$orderQuery = "SELECT o.*, u.username FROM orders o JOIN users u ON o.user_id = u.id 
               $whereClause 
               ORDER BY o.order_date DESC 
               LIMIT $limit OFFSET $offset";
$ordersList = $conn->query($orderQuery);

// Fetch Real-time Stats
$statsQuery = $conn->query("SELECT 
    COUNT(*) as total_orders,
    SUM(CASE WHEN status NOT IN ('Completed', 'Cancelled') THEN 1 ELSE 0 END) as active_queue,
    SUM(CASE WHEN status = 'Completed' THEN total_price ELSE 0 END) as daily_revenue,
    SUM(CASE WHEN status = 'Completed' THEN 1 ELSE 0 END) as completed_count
    FROM orders o $whereClause");
$stats = $statsQuery->fetch_assoc();

function getInitials($name) {
    $words = explode(" ", trim($name));
    $initials = "";
    foreach ($words as $w) {
        if(!empty($w)) $initials .= strtoupper($w[0]);
    }
    return substr($initials, 0, 2);
}
?>

<style>
    :root {
        --bg-main: #fcfbf8;
        --table-bg: #ffffff;
        --table-header: #f3f0e0;
        --text-dark: #1f1814;
        --text-muted: #6d645a;
        --border-color: #e8e4d3;
        --btn-bg: #ffffff;
        
        --status-green-bg: #dcfce7;
        --status-green-txt: #166534;
        --status-pending-bg: #f3f4f6;
        --status-pending-txt: #4b5563;
        --status-prep-bg: #e0f2fe;
        --status-prep-txt: #0369a1;
        --status-cancel-bg: #fee2e2;
        --status-cancel-txt: #991b1b;
    }

    body { background-color: var(--bg-main); font-family: 'Inter', sans-serif; }

    .admin-wrapper { max-width: 1250px; margin: 50px auto; padding: 0 4%; }
    
    /* HEADER SECTION */
    .admin-header-flex { display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 30px; }
    .admin-title { font-family: 'Playfair Display', serif; color: var(--text-dark); font-size: 38px; margin: 0 0 8px 0; letter-spacing: -0.5px; }
    .admin-subtitle { font-size: 15px; color: var(--text-muted); margin: 0; line-height: 1.5; }
    
    .action-buttons { display: flex; gap: 15px; align-items: center; }
    
    .date-filter-input { padding: 10px 16px; border-radius: 8px; border: 1px solid var(--border-color); background: var(--btn-bg); font-size: 14px; font-family: inherit; font-weight: 500; color: var(--text-dark); cursor: pointer; outline: none; transition: all 0.2s; box-shadow: 0 2px 4px rgba(0,0,0,0.02); }
    .date-filter-input:hover { border-color: #d1cbb8; }
    .clear-filter-btn { font-size: 13px; color: #d97706; text-decoration: none; font-weight: 600; padding: 8px 4px; transition: color 0.2s; }
    .clear-filter-btn:hover { color: #b45309; text-decoration: underline; }

    .top-btn { display: flex; align-items: center; gap: 8px; background: var(--text-dark); border: 1px solid var(--text-dark); padding: 10px 20px; border-radius: 8px; font-size: 14px; font-weight: 600; color: #fff; cursor: pointer; text-decoration: none; transition: all 0.2s; box-shadow: 0 4px 10px rgba(31, 24, 20, 0.15); }
    .top-btn:hover { background: #3a2e26; transform: translateY(-1px); }

    .success-alert { background: #dcfce7; color: #166534; padding: 16px 20px; border-radius: 8px; font-size: 14px; font-weight: 600; border-left: 4px solid #22c55e; margin-bottom: 25px; box-shadow: 0 2px 8px rgba(34, 197, 94, 0.1); }

    /* STATS GRID (MOVED TO TOP) */
    .stats-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; margin-bottom: 40px; }
    .stat-card { background: white; padding: 25px 30px; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.04); border: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center; transition: transform 0.2s; }
    .stat-card:hover { transform: translateY(-2px); }
    .stat-card small { font-size: 12px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1px; display: block; margin-bottom: 10px; }
    .stat-card h2 { font-family: 'Playfair Display', serif; font-size: 32px; margin: 0; color: var(--text-dark); }
    .stat-icon { width: 50px; height: 50px; border-radius: 12px; display: flex; align-items: center; justify-content: center; }

    /* TABLE LAYOUT */
    .table-container { background: white; border-radius: 16px; border: 1px solid var(--border-color); overflow: hidden; box-shadow: 0 4px 25px rgba(0,0,0,0.03); margin-bottom: 60px; }
    .admin-data-table { width: 100%; border-collapse: collapse; }
    .admin-data-table thead { background: var(--table-header); border-bottom: 2px solid var(--border-color); }
    .admin-data-table th { padding: 18px 24px; text-align: left; font-size: 12px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; }
    .admin-data-table td { padding: 20px 24px; border-bottom: 1px solid #f0eddf; font-size: 14px; color: var(--text-dark); vertical-align: middle; transition: background-color 0.2s ease; }
    .admin-data-table tbody tr:hover td { background-color: #faf9f5; }

    /* USER AVATAR & INFO */
    .user-cell { display: flex; align-items: center; gap: 14px; }
    .avatar { width: 40px; height: 40px; border-radius: 50%; background: #e5e0cf; display: flex; align-items: center; justify-content: center; font-size: 13px; font-weight: 700; color: var(--text-dark); }
    .user-info strong { display: block; font-size: 14px; color: var(--text-dark); margin-bottom: 2px; }
    .user-info span { font-size: 12px; color: var(--text-muted); }

    /* ITEM BADGES */
    .items-container { display: flex; flex-wrap: wrap; gap: 8px; max-width: 280px; }
    .item-badge { background: #f5f2e6; padding: 6px 12px; border-radius: 6px; font-size: 13px; color: var(--text-dark); display: inline-block; border: 1px solid #ede8d0; }
    .item-badge strong { color: #8e876c; font-weight: 700; margin-right: 4px; }

    .order-time-cell { font-size: 14px; line-height: 1.5; }
    .order-time-cell strong { display: block; color: var(--text-dark); margin-bottom: 2px; }
    .order-time-cell span { color: var(--text-muted); font-size: 12px; }

    /* STATUS PILL WITH DOT */
    .status-pill { display: inline-flex; align-items: center; gap: 6px; padding: 6px 14px; border-radius: 20px; font-size: 12px; font-weight: 600; letter-spacing: 0.5px; }
    .status-dot { width: 6px; height: 6px; border-radius: 50%; }
    
    .status-completed { background: var(--status-green-bg); color: var(--status-green-txt); }
    .status-completed .status-dot { background: var(--status-green-txt); }
    
    .status-pending { background: var(--status-pending-bg); color: var(--status-pending-txt); }
    .status-pending .status-dot { background: var(--status-pending-txt); }
    
    .status-preparing { background: var(--status-prep-bg); color: var(--status-prep-txt); }
    .status-preparing .status-dot { background: var(--status-prep-txt); }
    
    .status-cancelled { background: var(--status-cancel-bg); color: var(--status-cancel-txt); }
    .status-cancelled .status-dot { background: var(--status-cancel-txt); }

    /* ACTION DROPDOWN */
    .action-select { padding: 8px 14px; border-radius: 8px; border: 1px solid var(--border-color); background: var(--btn-bg); font-size: 13px; font-weight: 600; color: var(--text-dark); outline: none; cursor: pointer; width: 120px; transition: border-color 0.2s; }
    .action-select:hover { border-color: #c2bca3; }

    /* PAGINATION FOOTER */
    .pagination-footer { background: white; padding: 20px 24px; display: flex; justify-content: space-between; align-items: center; }
    .page-controls { display: flex; gap: 8px; align-items: center; }
    .page-btn { padding: 8px 14px; border-radius: 6px; border: 1px solid var(--border-color); background: var(--btn-bg); color: var(--text-dark); cursor: pointer; text-decoration: none; font-size: 13px; font-weight: 500; transition: all 0.2s; }
    .page-btn.active { background: var(--text-dark); color: white; border-color: var(--text-dark); }
    .page-btn:hover:not(.active) { background: #f5f2e6; border-color: #d1cbb8; }
</style>

<div class="admin-wrapper">
    
    <div class="admin-header-flex">
        <div>
            <h1 class="admin-title">Client Transaction Queue</h1>
            <p class="admin-subtitle">Manage and audit real-time customer coffee orders and payments.</p>
        </div>
        
        <div class="action-buttons">
            <form method="GET" id="filterForm" style="margin: 0; display: flex; align-items: center; gap: 10px;">
                <input type="date" name="filter_date" value="<?php echo htmlspecialchars($filter_date); ?>" onchange="this.form.submit()" class="date-filter-input">
                <?php if (!empty($filter_date)): ?>
                    <a href="admin.php" class="clear-filter-btn">Clear Filter</a>
                <?php endif; ?>
            </form>
            
            <a href="?export_csv=1&filter_date=<?php echo urlencode($filter_date); ?>" class="top-btn">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                Export CSV
            </a>
        </div>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div>
                <small>Active Queue</small>
                <h2><?php echo $stats['active_queue'] ?: 0; ?> <span style="font-size:18px; font-family: 'Inter', sans-serif; font-weight: 500; color: var(--text-muted);">Orders</span></h2>
            </div>
            <div class="stat-icon" style="background: #f5f2e6;">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="var(--text-dark)" stroke-width="2"><polygon points="12 2 2 7 12 12 22 7 12 2"></polygon><polyline points="2 17 12 22 22 17"></polyline><polyline points="2 12 12 17 22 12"></polyline></svg>
            </div>
        </div>

        <div class="stat-card">
            <div>
                <small>Daily Revenue</small>
                <h2 style="color: #166534;">₱<?php echo number_format($stats['daily_revenue'] ?: 0, 2); ?></h2>
            </div>
            <div class="stat-icon" style="background: #dcfce7;">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#166534" stroke-width="2"><rect x="2" y="6" width="20" height="12" rx="2"></rect><circle cx="12" cy="12" r="2"></circle><path d="M6 12h.01M18 12h.01"></path></svg>
            </div>
        </div>

        <div class="stat-card">
            <div>
                <small>Completed Orders</small>
                <h2><?php echo $stats['completed_count'] ?: 0; ?> <span style="font-size:18px; font-family: 'Inter', sans-serif; font-weight: 500; color: var(--text-muted);">Orders</span></h2>
            </div>
            <div class="stat-icon" style="background: #fae8c8;">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#b45309" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
            </div>
        </div>
    </div>

    <?php echo $notice; ?>

    <div class="table-container">
        <div style="overflow-x: auto;">
            <table class="admin-data-table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>User Account</th>
                        <th>Purchase Details</th>
                        <th>Billing Total</th>
                        <th>Order Date & Time</th>
                        <th>Document</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($ordersList->num_rows === 0): ?>
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 60px 20px;">
                                <div style="display: flex; flex-direction: column; align-items: center; gap: 15px;">
                                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#d1cbb8" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><line x1="9" y1="3" x2="9" y2="21"></line></svg>
                                    <span style="color: var(--text-muted); font-size: 15px; font-weight: 500;">No transactions found for this view.</span>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php while ($ord = $ordersList->fetch_assoc()): ?>
                            <tr>
                                <td style="font-weight: 700; color: var(--text-dark); font-family: 'Inter', monospace; font-size: 13px;">
                                    #ORD-<br><?php echo str_pad($ord['id'], 5, '0', STR_PAD_LEFT); ?>
                                </td>
                                
                                <td>
                                    <div class="user-cell">
                                        <div class="avatar"><?php echo getInitials($ord['username']); ?></div>
                                        <div class="user-info">
                                            <strong><?php echo htmlspecialchars($ord['username']); ?></strong>
                                            <span><?php echo strtolower(str_replace(' ', '.', $ord['username'])) . '@mail.com'; ?></span>
                                        </div>
                                    </div>
                                </td>
                                
                                <td>
                                    <div class="items-container">
                                        <?php 
                                        $itStmt = $conn->prepare("SELECT quantity, product_name FROM order_items WHERE order_id = ?");
                                        $itStmt->bind_param("i", $ord['id']);
                                        $itStmt->execute();
                                        $itRes = $itStmt->get_result();
                                        if ($itRes->num_rows > 0) {
                                            while ($it = $itRes->fetch_assoc()) {
                                                echo "<span class='item-badge'><strong>{$it['quantity']}x</strong> {$it['product_name']}</span>";
                                            }
                                        } else {
                                            echo "<span class='item-badge' style='background: transparent; border: 1px dashed #ccc;'>No items</span>";
                                        }
                                        $itStmt->close();
                                        ?>
                                    </div>
                                </td>
                                
                                <td style="font-weight: 700; font-size: 15px;">₱<?php echo number_format($ord['total_price'], 2); ?></td>
                                
                                <td class="order-time-cell">
                                    <strong><?php echo date('M d, Y', strtotime($ord['order_date'])); ?></strong>
                                    <span><?php echo date('g:i A', strtotime($ord['order_date'])); ?></span>
                                </td>
                                
                                <td style="text-align: center;">
                                    <?php if ($ord['payment_method'] === 'Online' && !empty($ord['receipt_file'])): ?>
                                        <a href="uploads/<?php echo htmlspecialchars($ord['receipt_file']); ?>" target="_blank" title="View Document" style="display: inline-block; padding: 8px; border-radius: 6px; background: #fef9f3; border: 1px solid #f5eadd; transition: transform 0.2s;">
                                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#d97706" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                                        </a>
                                    <?php else: ?>
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#e5e0cf" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline></svg>
                                    <?php endif; ?>
                                </td>
                                
                                <td>
                                    <?php $statusClass = "status-" . strtolower($ord['status']); ?>
                                    <span class="status-pill <?php echo $statusClass; ?>">
                                        <span class="status-dot"></span>
                                        <?php echo $ord['status']; ?>
                                    </span>
                                </td>
                                
                                <td>
                                    <form method="POST" action="admin.php" style="margin: 0;">
                                        <input type="hidden" name="action" value="update_status">
                                        <input type="hidden" name="order_id" value="<?php echo $ord['id']; ?>">
                                        <select name="status" class="action-select" onchange="this.form.submit()">
                                            <option value="Pending" <?php echo $ord['status'] === 'Pending' ? 'selected' : ''; ?>>Pending</option>
                                            <option value="Preparing" <?php echo $ord['status'] === 'Preparing' ? 'selected' : ''; ?>>Preparing</option>
                                            <option value="Completed" <?php echo $ord['status'] === 'Completed' ? 'selected' : ''; ?>>Completed</option>
                                            <option value="Cancelled" <?php echo $ord['status'] === 'Cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                        </select>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="pagination-footer">
            <span style="font-size: 13px; font-weight: 500; color: var(--text-muted);">
                Showing <?php echo $ordersList->num_rows; ?> of <?php echo $total_rows; ?> transactions
            </span>
            
            <?php if ($total_pages > 1): ?>
            <div class="page-controls">
                <?php if ($page > 1): ?>
                    <a href="?page=<?php echo $page - 1; ?>&filter_date=<?php echo urlencode($filter_date); ?>" class="page-btn"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"></polyline></svg></a>
                <?php else: ?>
                    <span class="page-btn" style="opacity: 0.4; cursor: not-allowed;"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"></polyline></svg></span>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="?page=<?php echo $i; ?>&filter_date=<?php echo urlencode($filter_date); ?>" class="page-btn <?php echo $page == $i ? 'active' : ''; ?>"><?php echo $i; ?></a>
                <?php endfor; ?>

                <?php if ($page < $total_pages): ?>
                    <a href="?page=<?php echo $page + 1; ?>&filter_date=<?php echo urlencode($filter_date); ?>" class="page-btn"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"></polyline></svg></a>
                <?php else: ?>
                    <span class="page-btn" style="opacity: 0.4; cursor: not-allowed;"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"></polyline></svg></span>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>