<?php
session_start();
include 'db.php';

// ADMIN CHECK (FIXED)
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Quick stats
$total_orders = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM orders"))['c'];
$total_products = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM products"))['c'];
$total_blogs = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM blog_posts"))['c'];
$total_users = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM users"))['c'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard — Zenith</title>
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
            background: var(--accent-lt);
            color: var(--accent);
            padding: 3px 10px;
            border-radius: 20px;
            letter-spacing: .3px;
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
        .page-header p {
            font-size: .88rem; color: var(--muted); margin-bottom: 6px;
        }
        .page-header h1 {
            font-family: 'Playfair Display', serif;
            font-size: 2rem; font-weight: 700;
        }

        /* ── SECTION ── */
        .section {
            max-width: 1000px;
            margin: 48px auto;
            padding: 0 40px 80px;
            display: flex;
            flex-direction: column;
            gap: 32px;
        }

        /* ── STATS ── */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
        }
        .stat-card {
            background: var(--white);
            border: 1px solid var(--warm);
            border-radius: var(--radius);
            padding: 20px 22px;
            display: flex;
            flex-direction: column;
            gap: 6px;
        }
        .stat-icon { font-size: 1.4rem; }
        .stat-value {
            font-family: 'Playfair Display', serif;
            font-size: 1.8rem; font-weight: 700;
            color: var(--ink);
            line-height: 1;
        }
        .stat-label {
            font-size: .78rem; font-weight: 500;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: .4px;
        }

        /* ── MENU GRID ── */
        .menu-heading {
            font-family: 'Playfair Display', serif;
            font-size: 1.25rem; font-weight: 700;
            margin-bottom: 4px;
        }
        .menu-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 16px;
        }
        .menu-card {
            display: flex;
            align-items: center;
            gap: 18px;
            padding: 20px 24px;
            background: var(--white);
            border: 1px solid var(--warm);
            border-radius: var(--radius);
            text-decoration: none;
            color: var(--ink);
            transition: box-shadow .2s, transform .2s, border-color .2s;
        }
        .menu-card:hover {
            box-shadow: var(--shadow);
            transform: translateY(-3px);
            border-color: var(--accent-lt);
        }
        .menu-card.danger { border-color: #f5d0c8; }
        .menu-card.danger:hover { border-color: #e8a898; box-shadow: 0 4px 24px rgba(200,80,60,.08); }
        .menu-icon {
            width: 46px; height: 46px;
            border-radius: 10px;
            background: var(--accent-lt);
            display: flex; align-items: center; justify-content: center;
            font-size: 1.3rem;
            flex-shrink: 0;
        }
        .menu-card.danger .menu-icon { background: #fdecea; }
        .menu-info { flex: 1; }
        .menu-title {
            font-size: .95rem; font-weight: 700;
            color: var(--ink); margin-bottom: 3px;
        }
        .menu-desc {
            font-size: .78rem;
            color: var(--muted);
        }
        .menu-arrow {
            font-size: .85rem;
            color: var(--accent);
            transition: transform .2s;
        }
        .menu-card:hover .menu-arrow { transform: translateX(4px); }

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
        @media (max-width: 700px) {
            .stats-grid { grid-template-columns: repeat(2, 1fr); }
            .menu-grid { grid-template-columns: 1fr; }
        }
        @media (max-width: 600px) {
            .navbar { padding: 0 20px; }
            .page-header { padding: 24px 20px; }
            .section { padding: 0 20px 60px; margin: 32px auto; }
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
        <a href="index.php" class="nav-btn">← Storefront</a>
    </div>
</nav>

<!-- PAGE HEADER -->
<div class="page-header">
    <p>Welcome back, <?php echo htmlspecialchars($_SESSION['role']); ?></p>
    <h1>Admin Dashboard</h1>
</div>

<!-- CONTENT -->
<div class="section">

    <!-- STATS -->
    <div class="stats-grid">
        <div class="stat-card">
            <span class="stat-icon">📦</span>
            <span class="stat-value"><?php echo $total_orders; ?></span>
            <span class="stat-label">Total Orders</span>
        </div>
        <div class="stat-card">
            <span class="stat-icon">🛍️</span>
            <span class="stat-value"><?php echo $total_products; ?></span>
            <span class="stat-label">Products</span>
        </div>
        <div class="stat-card">
            <span class="stat-icon">📰</span>
            <span class="stat-value"><?php echo $total_blogs; ?></span>
            <span class="stat-label">Blog Posts</span>
        </div>
        <div class="stat-card">
            <span class="stat-icon">👤</span>
            <span class="stat-value"><?php echo $total_users; ?></span>
            <span class="stat-label">Users</span>
        </div>
    </div>

    <!-- MENU -->
    <div>
        <p class="menu-heading">Quick Actions</p>
        <div class="menu-grid">
			<a href="manage_products.php" class="menu-card">
                <div class="menu-icon">🛍️</div>
                <div class="menu-info">
                    <div class="menu-title">Manage Products </div>
                    <div class="menu-desc">Add or Delete Products</div>
                </div>
                <span class="menu-arrow">→</span>
            </a>

            <a href="add_blog.php" class="menu-card">
                <div class="menu-icon">➕</div>
                <div class="menu-info">
                    <div class="menu-title">Add Blog Post</div>
                    <div class="menu-desc">Write and publish a new article</div>
                </div>
                <span class="menu-arrow">→</span>
            </a>

            <a href="manage_blog.php" class="menu-card">
                <div class="menu-icon">📝</div>
                <div class="menu-info">
                    <div class="menu-title">Manage Blogs</div>
                    <div class="menu-desc">Edit or delete existing posts</div>
                </div>
                <span class="menu-arrow">→</span>
            </a>

            <a href="manage_orders.php" class="menu-card">
                <div class="menu-icon">📦</div>
                <div class="menu-info">
                    <div class="menu-title">Manage Orders</div>
                    <div class="menu-desc">View and process customer orders</div>
                </div>
                <span class="menu-arrow">→</span>
            </a>

			<a href="admin_stats.php" class="menu-card">
				<div class="menu-icon">📊</div>
				<div class="menu-info">
					<div class="menu-title">Statistics</div>
					<div class="menu-desc">View sales, orders, and system analytics</div>
				</div>
				<span class="menu-arrow">→</span>
			</a>

            <a href="logout.php" class="menu-card danger">
                <div class="menu-icon">🚪</div>
                <div class="menu-info">
                    <div class="menu-title">Logout</div>
                    <div class="menu-desc">Sign out of the admin panel</div>
                </div>
                <span class="menu-arrow">→</span>
            </a>

        </div>
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