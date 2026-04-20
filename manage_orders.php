<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

if (isset($_POST['order_id']) && isset($_POST['status'])) {
    $order_id = (int) $_POST['order_id'];
    $status   = mysqli_real_escape_string($conn, $_POST['status']);
    mysqli_query($conn, "UPDATE orders SET status='$status' WHERE id=$order_id");
}

$result = mysqli_query($conn, "SELECT * FROM orders ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Orders — Zenith Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;700&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --ink:       #1a1a18;
            --cream:     #f7f4ef;
            --warm:      #ede8df;
            --accent:    #c8753a;
            --accent-lt: #f0e0d0;
            --muted:     #7a7671;
            --white:     #ffffff;
            --radius:    12px;
            --shadow:    0 4px 24px rgba(26,26,24,.08);
        }

        body {
            font-family: 'DM Sans', sans-serif;
            background: var(--cream);
            color: var(--ink);
            min-height: 100vh;
        }

        /* ── NAVBAR ── */
        .navbar {
            position: sticky; top: 0; z-index: 100;
            display: flex; align-items: center; justify-content: space-between;
            padding: 0 40px; height: 68px;
            background: var(--white);
            border-bottom: 1px solid var(--warm);
            box-shadow: 0 2px 12px rgba(26,26,24,.06);
        }
        .navbar .logo { display: flex; align-items: center; gap: 12px; text-decoration: none; }
        .navbar .logo img { width: 42px; height: 42px; border-radius: 10px; object-fit: contain; }
        .navbar .store-name {
            font-family: 'Playfair Display', serif;
            font-size: 1.35rem; font-weight: 700;
            color: var(--ink); letter-spacing: -.3px;
        }
        .admin-badge {
            font-size: .72rem; font-weight: 500;
            background: var(--accent-lt); color: var(--accent);
            padding: 3px 10px; border-radius: 20px; letter-spacing: .3px;
        }
        .nav-btn {
            display: flex; align-items: center; gap: 6px;
            padding: 8px 16px; border-radius: 8px;
            font-family: 'DM Sans', sans-serif;
            font-size: .85rem; font-weight: 500;
            text-decoration: none;
            background: var(--warm); color: var(--muted);
            transition: background .18s, color .18s;
        }
        .nav-btn:hover { background: #e0dbd2; color: var(--ink); }

        /* ── PAGE HEADER ── */
        .page-header {
            background: var(--white);
            border-bottom: 1px solid var(--warm);
            padding: 32px 40px 28px;
        }
        .breadcrumb { font-size: .8rem; color: var(--muted); margin-bottom: 10px; }
        .breadcrumb a { color: var(--accent); text-decoration: none; }
        .breadcrumb a:hover { text-decoration: underline; }
        .page-header h1 {
            font-family: 'Playfair Display', serif;
            font-size: 2rem; font-weight: 700;
        }

        /* ── SECTION ── */
        .section {
            max-width: 1200px;
            margin: 48px auto;
            padding: 0 40px 80px;
        }

        /* ── TABLE CARD ── */
        .table-card {
            background: var(--white);
            border: 1px solid var(--warm);
            border-radius: var(--radius);
            overflow: hidden;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        thead {
            background: var(--cream);
            border-bottom: 1px solid var(--warm);
        }
        th {
            padding: 13px 16px;
            font-size: .75rem; font-weight: 500;
            text-transform: uppercase; letter-spacing: .5px;
            color: var(--muted);
            text-align: left;
            white-space: nowrap;
        }
        td {
            padding: 14px 16px;
            border-bottom: 1px solid var(--warm);
            font-size: .85rem;
            vertical-align: middle;
        }
        tr:last-child td { border-bottom: none; }
        tr:hover td { background: #faf8f5; }

        /* ── ORDER ID ── */
        .order-id {
            font-size: .75rem; font-weight: 500;
            color: var(--muted);
            background: var(--warm);
            padding: 2px 8px; border-radius: 20px;
            white-space: nowrap;
        }

        /* ── CUSTOMER INFO ── */
        .customer-name { font-weight: 500; color: var(--ink); margin-bottom: 3px; }
        .customer-phone { font-size: .76rem; color: var(--muted); }

        /* ── ADDRESS ── */
        .address-cell {
            max-width: 180px;
            font-size: .8rem;
            color: var(--muted);
            line-height: 1.4;
        }

        /* ── TOTAL ── */
        .order-total {
            font-family: 'Playfair Display', serif;
            font-weight: 700;
            color: #9D1C06;
            white-space: nowrap;
        }

        /* ── STATUS BADGE ── */
        .status-badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: .75rem; font-weight: 500;
            white-space: nowrap;
        }
        .status-pending    { background: #fff8e6; color: #a07000; border: 1px solid #f5d97a; }
        .status-processing { background: #e6f0ff; color: #1a4fa0; border: 1px solid #aac4f5; }
        .status-completed  { background: #e6f5ec; color: #1a7a3c; border: 1px solid #8fd4a8; }

        /* ── DATE ── */
        .order-date { font-size: .76rem; color: var(--muted); white-space: nowrap; }

        /* ── UPDATE FORM ── */
        .update-form { display: flex; align-items: center; gap: 6px; }
        .update-form select {
            padding: 6px 10px;
            border: 1px solid var(--warm);
            border-radius: 7px;
            font-family: 'DM Sans', sans-serif;
            font-size: .78rem;
            color: var(--ink);
            background: var(--cream);
            outline: none;
            cursor: pointer;
            transition: border-color .18s;
        }
        .update-form select:focus { border-color: var(--accent); }
        .update-btn {
            padding: 6px 12px;
            background: var(--ink);
            color: var(--white);
            border: none; border-radius: 7px;
            font-family: 'DM Sans', sans-serif;
            font-size: .75rem; font-weight: 500;
            cursor: pointer;
            transition: background .18s;
            white-space: nowrap;
        }
        .update-btn:hover { background: var(--accent); }

        /* ── EMPTY STATE ── */
        .empty-state {
            text-align: center; padding: 60px 20px;
            color: var(--muted);
        }
        .empty-state .icon { font-size: 2.5rem; margin-bottom: 12px; }
        .empty-state p { font-size: .9rem; }

        /* ── FOOTER ── */
        footer { background: var(--ink); color: rgba(255,255,255,.55); margin-top: 60px; }
        .footer-inner {
            max-width: 1200px; margin: 0 auto;
            padding: 40px 40px 28px;
            display: flex; flex-direction: column; gap: 32px;
        }
        .footer-top {
            display: flex; align-items: center; justify-content: space-between;
            flex-wrap: wrap; gap: 20px;
        }
        .footer-brand { font-family: 'Playfair Display', serif; font-size: 1.25rem; color: var(--white); }
        .footer-links { display: flex; gap: 8px; flex-wrap: wrap; }
        .footer-link {
            display: flex; align-items: center; gap: 7px;
            padding: 9px 18px; background: rgba(255,255,255,.07);
            border-radius: 8px; color: rgba(255,255,255,.8);
            font-size: .84rem; text-decoration: none;
            transition: background .16s, color .16s;
        }
        .footer-link:hover { background: rgba(255,255,255,.14); color: var(--white); }
        .footer-bottom {
            font-size: .75rem;
            border-top: 1px solid rgba(255,255,255,.1);
            padding-top: 20px;
        }

        /* ── RESPONSIVE ── */
        @media (max-width: 900px) {
            .section { padding: 0 20px 60px; margin: 32px auto; }
            .navbar { padding: 0 20px; }
            .page-header { padding: 24px 20px; }
            th:nth-child(4), td:nth-child(4),
            th:nth-child(2), td:nth-child(2) { display: none; }
            .footer-inner { padding: 32px 20px 20px; }
        }
    </style>
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar">
    <a class="logo" href="index.php">
        <img src="logo.png" alt="Logo">
        <span class="store-name">Zenith</span>
    </a>
    <div style="display:flex; align-items:center; gap:12px;">
        <span class="admin-badge">Admin</span>
        <a href="admin_dashboard.php" class="nav-btn">← Dashboard</a>
    </div>
</nav>

<!-- PAGE HEADER -->
<div class="page-header">
    <p class="breadcrumb">

        <a href="admin_dashboard.php">Dashboard</a> →
        Manage Orders
    </p>
    <h1>Manage Orders</h1>
</div>

<!-- TABLE -->
<div class="section">
    <div class="table-card">
        <?php if (mysqli_num_rows($result) > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Order</th>
                    <th>User</th>
                    <th>Customer</th>
                    <th>Address</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Update</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td><span class="order-id">#<?php echo $row['id']; ?></span></td>
                    <td><?php echo htmlspecialchars($row['user']); ?></td>
                    <td>
                        <div class="customer-name"><?php echo htmlspecialchars($row['name']); ?></div>
                        <div class="customer-phone"><?php echo htmlspecialchars($row['phone']); ?></div>
                    </td>
                    <td><div class="address-cell"><?php echo htmlspecialchars($row['address']); ?></div></td>
                    <td><span class="order-total"><?php echo number_format($row['total']); ?> Tk</span></td>
                    <td>
                        <?php
                        $s = $row['status'];
                        $cls = 'status-pending';
                        if ($s === 'Processing') $cls = 'status-processing';
                        if ($s === 'Completed')  $cls = 'status-completed';
                        ?>
                        <span class="status-badge <?php echo $cls; ?>"><?php echo $s; ?></span>
                    </td>
                    <td>
                        <form method="POST" class="update-form">
                            <input type="hidden" name="order_id" value="<?php echo $row['id']; ?>">
                            <select name="status">
                                <option value="Pending"    <?php if($row['status']==='Pending')    echo 'selected'; ?>>Pending</option>
                                <option value="Processing" <?php if($row['status']==='Processing') echo 'selected'; ?>>Processing</option>
                                <option value="Completed"  <?php if($row['status']==='Completed')  echo 'selected'; ?>>Completed</option>
                            </select>
                            <button type="submit" class="update-btn">Save</button>
                        </form>
                    </td>
                    <td><span class="order-date"><?php echo date("d M Y", strtotime($row['created_at'])); ?></span></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <?php else: ?>
        <div class="empty-state">
            <div class="icon">📦</div>
            <p>No orders yet.</p>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- FOOTER -->
<footer>
    <div class="footer-inner">
        <div class="footer-top">
            <span class="footer-brand">Zenith</span>
            
        </div>
        <p class="footer-bottom">© <?php echo date('Y'); ?> Zenith. All rights reserved.</p>
    </div>
</footer>

</body>
</html>