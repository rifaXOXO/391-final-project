<?php
session_start();
include 'db.php';

// ADMIN CHECK
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

/* TOTAL REVENUE */
$revenueQ = mysqli_query($conn, "SELECT SUM(total) AS total_revenue FROM orders WHERE status='Completed'");
$revenue = mysqli_fetch_assoc($revenueQ)['total_revenue'] ?? 0;

/* TOTAL ORDERS */
$totalOrdersQ = mysqli_query($conn, "SELECT COUNT(*) AS c FROM orders");
$totalOrders = mysqli_fetch_assoc($totalOrdersQ)['c'];

/* PENDING / COMPLETED */
$pendingQ = mysqli_query($conn, "SELECT COUNT(*) AS c FROM orders WHERE status='Pending'");
$pending = mysqli_fetch_assoc($pendingQ)['c'];

$completedQ = mysqli_query($conn, "SELECT COUNT(*) AS c FROM orders WHERE status='Completed'");
$completed = mysqli_fetch_assoc($completedQ)['c'];

/* TOTAL PRODUCTS */
$productQ = mysqli_query($conn, "SELECT COUNT(*) AS c FROM products");
$products = mysqli_fetch_assoc($productQ)['c'];

/* USERS */
$userQ = mysqli_query($conn, "SELECT COUNT(*) AS c FROM users");
$users = mysqli_fetch_assoc($userQ)['c'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analytics Dashboard — Zenith</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet">
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
            --shadow:    0 10px 30px rgba(26,26,24, 0.05);
        }

        body {
            font-family: 'DM Sans', sans-serif;
            background: var(--cream);
            color: var(--ink);
            line-height: 1.6;
            display: flex;
            flex-direction: column;
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
        .nav-btn {
            text-decoration: none;
            color: var(--muted);
            font-size: 0.82rem;
            font-weight: 500;
            padding: 8px 16px;
            background: var(--warm);
            border-radius: 8px;
            transition: all 0.2s ease;
        }
        .nav-btn:hover { background: #e0dbd2; color: var(--ink); }

        /* ── CONTENT CONTAINER ── */
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 56px 40px;
            flex: 1;
            width: 100%;
        }

        header { margin-bottom: 48px; }
        header h1 {
            font-family: 'Playfair Display', serif;
            font-size: 2.2rem;
            letter-spacing: -0.5px;
            margin-bottom: 4px;
        }
        header p { color: var(--muted); font-size: 0.95rem; }

        /* ── FIXED 3-COLUMN GRID ── */
        .grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr); /* Force 3 boxes per row */
            gap: 24px;
        }

        .card {
            background: var(--white);
            padding: 32px;
            border-radius: 16px;
            border: 1px solid rgba(0,0,0,0.03);
            box-shadow: var(--shadow);
            display: flex;
            flex-direction: column;
            gap: 12px;
            transition: transform 0.3s ease;
        }
        .card:hover { transform: translateY(-5px); }

        .card-header { display: flex; justify-content: space-between; align-items: center; }
        .label {
            color: var(--muted);
            font-size: 0.8rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .icon-box {
            width: 40px; height: 40px;
            background: var(--cream);
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.2rem;
        }
        .value { font-size: 2rem; font-weight: 700; letter-spacing: -1px; }

        .card.revenue { background: var(--ink); color: var(--white); }
        .card.revenue .label { color: rgba(255,255,255,0.6); }
        .card.revenue .value { color: var(--accent-lt); }
        .card.revenue .icon-box { background: rgba(255,255,255,0.1); }

        /* ── FOOTER ── */
        footer { background: var(--ink); color: rgba(255,255,255,.55); margin-top: 60px; }
        .footer-inner {
            max-width: 1200px; margin: 0 auto;
            padding: 40px 40px 28px;
            display: flex; flex-direction: column; gap: 32px;
        }
        .footer-top { display: flex; align-items: center; justify-content: space-between; }
        .footer-brand { font-family: 'Playfair Display', serif; font-size: 1.25rem; color: var(--white); }
        .footer-bottom {
            font-size: .75rem;
            border-top: 1px solid rgba(255,255,255,.1);
            padding-top: 20px;
        }

        /* ── MOBILE RESPONSIVE ── */
        @media (max-width: 900px) {
            .grid { grid-template-columns: repeat(2, 1fr); } /* 2 per row on tablets */
            .navbar { padding: 0 20px; }
            .container { padding: 40px 20px; }
            .footer-inner { padding: 32px 20px 20px; }
        }
        @media (max-width: 600px) {
            .grid { grid-template-columns: 1fr; } /* 1 per row on phones */
        }
    </style>
</head>

<body>

<nav class="navbar">
    <a class="logo" href="index.php">
        <img src="logo.png" alt="Zenith Logo">
        <span class="store-name">Zenith</span>
    </a>
    <div style="display:flex; align-items:center; gap:12px;">
        <span style="font-size: .72rem; font-weight: 500; background: var(--accent-lt); color: var(--accent); padding: 3px 10px; border-radius: 20px;">Admin</span>
        <a href="admin_dashboard.php" class="nav-btn">← Dashboard</a>
    </div>
</nav>

<div class="container">
    <header>
        <h1>Analytics Dashboard</h1>
        <p>Real-time performance overview for Zenith</p>
    </header>

    <div class="grid">
        <div class="card revenue">
            <div class="card-header">
                <span class="label">Total Revenue</span>
                <div class="icon-box">💰</div>
            </div>
            <div class="value">৳ <?php echo number_format($revenue); ?></div>
        </div>

        <div class="card">
            <div class="card-header">
                <span class="label">Total Orders</span>
                <div class="icon-box">📦</div>
            </div>
            <div class="value"><?php echo number_format($totalOrders); ?></div>
        </div>

        <div class="card">
            <div class="card-header">
                <span class="label">Pending Orders</span>
                <div class="icon-box">⏳</div>
            </div>
            <div class="value"><?php echo number_format($pending); ?></div>
        </div>

        <div class="card">
            <div class="card-header">
                <span class="label">Completed</span>
                <div class="icon-box">✅</div>
            </div>
            <div class="value"><?php echo number_format($completed); ?></div>
        </div>

        <div class="card">
            <div class="card-header">
                <span class="label">Live Products</span>
                <div class="icon-box">🏷️</div>
            </div>
            <div class="value"><?php echo number_format($products); ?></div>
        </div>

        <div class="card">
            <div class="card-header">
                <span class="label">Registered Users</span>
                <div class="icon-box">👥</div>
            </div>
            <div class="value"><?php echo number_format($users); ?></div>
        </div>
    </div>
</div>

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