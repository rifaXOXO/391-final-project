<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['category']) || !is_numeric($_GET['category'])) {
    header("Location: index.php");
    exit();
}

$category_id = intval($_GET['category']);

$category_query = mysqli_query($conn, "SELECT name FROM categories WHERE id = $category_id");
$category = mysqli_fetch_assoc($category_query);

if (!$category) {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($category['name']); ?> — Zenith</title>
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
            --radius:    16px;
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
        .navbar .nav-actions { display: flex; align-items: center; gap: 8px; }
        .nav-btn {
            display: flex; align-items: center; gap: 6px;
            padding: 8px 16px; border-radius: 8px;
            font-family: 'DM Sans', sans-serif;
            font-size: .85rem; font-weight: 500;
            text-decoration: none;
            transition: background .18s, color .18s;
        }
        .nav-btn.cart { background: var(--accent-lt); color: var(--accent); }
        .nav-btn.cart:hover { background: var(--accent); color: var(--white); }
        .nav-btn.logout { background: var(--warm); color: var(--muted); }
        .nav-btn.logout:hover { background: #e0dbd2; color: var(--ink); }
        .nav-btn.orders { background: var(--warm); color:#7A716E; }
        .nav-btn.orders:hover { background: #e0dbd2; color: var(--ink); }

        /* ── PAGE HEADER ── */
        .page-header {
            background: var(--white);
            border-bottom: 1px solid var(--warm);
            padding: 40px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .header-content h1 {
            font-family: 'Playfair Display', serif;
            font-size: 2.2rem; font-weight: 700;
            margin-bottom: 4px;
        }
        .breadcrumb { font-size: .85rem; color: var(--muted); }
        .breadcrumb a { color: var(--accent); text-decoration: none; font-weight: 500; }

        .back-link {
			display: inline-flex;
			align-items: center;
			justify-content: center;
			gap: 6px;
			font-size: .85rem;
			font-weight: 500;
			color: var(--muted);
			text-decoration: none;
			background: #EDF5B0;
			padding: 10px 20px;
			border-radius: 8px;
			transition: background .18s, color .18s;
		}
		.back-link:hover { background: #e0dbd2; color: var(--ink); }

        /* ── CONTENT SECTION ── */
        .section { padding: 60px 40px; max-width: 1200px; margin: 0 auto; }
        .section-header { margin-bottom: 40px; }
        .section-header h2 {
            font-family: 'Playfair Display', serif;
            font-size: 1.6rem; color: var(--muted); font-weight: 500;
        }

        /* ── PROFESSIONAL SUBCATEGORIES GRID ── */
        .subcategories-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 32px;
        }
        .subcategory-card {
            display: block;
            text-decoration: none;
            background: var(--white);
            border-radius: var(--radius);
            overflow: hidden;
            border: 1px solid rgba(26,26,24, 0.05);
            transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
        }
        .subcategory-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 40px rgba(26,26,24, 0.08);
            border-color: var(--accent-lt);
        }

        .sub-img-box {
            width: 100%; height: 260px;
            background: var(--warm);
            overflow: hidden;
            position: relative;
        }
        .sub-img-box img {
            width: 100%; height: 100%;
            object-fit: cover;
            transition: transform 0.6s ease;
        }
        .subcategory-card:hover .sub-img-box img { transform: scale(1.1); }

        .sub-img-placeholder {
            width: 100%; height: 100%;
            display: flex; align-items: center; justify-content: center;
            font-size: 3rem; background: var(--warm);
        }

        .sub-info {
            padding: 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .sub-info span {
            font-family: 'Playfair Display', serif;
            font-size: 1.3rem; font-weight: 600; color: var(--ink);
        }
        .sub-arrow {
            width: 36px; height: 36px;
            background: var(--cream);
            color: var(--accent);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            transition: all 0.3s ease;
        }
        .subcategory-card:hover .sub-arrow {
            background: var(--accent);
            color: var(--white);
            transform: translateX(4px);
        }

        /* ── EMPTY STATE ── */
        .empty-state {
            text-align: center; padding: 100px 20px;
            grid-column: 1 / -1;
        }
        .empty-state .icon { font-size: 4rem; margin-bottom: 20px; }
        .empty-state p { font-size: 1.1rem; color: var(--muted); margin-bottom: 30px; }
        .empty-state a {
            display: inline-block; padding: 12px 32px;
            background: var(--ink); color: var(--white);
            border-radius: 8px; text-decoration: none;
            font-weight: 500; transition: opacity .2s;
        }
        .empty-state a:hover { opacity: 0.8; }

        /* ── FOOTER ── */
        footer { background: var(--ink); color: rgba(255,255,255,.55); margin-top: 80px; }
        .footer-inner {
            max-width: 1200px; margin: 0 auto;
            padding: 40px 40px 28px;
            display: flex; flex-direction: column; gap: 32px;
        }
        .footer-top { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 20px; }
        .footer-brand { font-family: 'Playfair Display', serif; font-size: 1.25rem; color: var(--white); }
        .footer-links { display: flex; gap: 8px; flex-wrap: wrap; }
        .footer-link {
            padding: 9px 18px; background: rgba(255,255,255,.07);
            border-radius: 8px; color: rgba(255,255,255,.8);
            font-size: .84rem; text-decoration: none; transition: all .2s;
        }
        .footer-link:hover { background: rgba(255,255,255,.14); color: var(--white); }
        .footer-bottom { font-size: .75rem; border-top: 1px solid rgba(255,255,255,.1); padding-top: 20px; }

        @media (max-width: 600px) {
            .navbar, .page-header, .section { padding: 20px; }
            .page-header { flex-direction: column; align-items: flex-start; gap: 20px; }
        }
    </style>
</head>
<body>

<nav class="navbar">
    <a class="logo" href="index.php">
        <img src="logo.png" alt="Logo">
        <span class="store-name">Zenith</span>
    </a>
    <div class="nav-actions">
        <a href="my_orders.php" class="nav-btn orders">📦 My Orders</a>
        <a href="cart.php" class="nav-btn cart">🛒 Cart</a>
        <a href="logout.php" class="nav-btn logout">Logout</a>
    </div>
</nav>

<div class="page-header">
    <div class="header-content">
        <p class="breadcrumb">
            <a href="index.php">Home</a> / <?php echo htmlspecialchars($category['name']); ?>
        </p>
        <h1><?php echo htmlspecialchars($category['name']); ?></h1>
    </div>
    <a href="javascript:history.back()" class="back-link">← Return</a>
</div>

<div class="section">
    <div class="section-header">
        <h2>Refine your search</h2>
    </div>

    <div class="subcategories-grid">
        <?php
        $stmt = mysqli_prepare($conn, "SELECT * FROM subcategories WHERE category_id = ? ORDER BY name ASC");
        mysqli_stmt_bind_param($stmt, "i", $category_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        $has_results = false;
        while ($row = mysqli_fetch_assoc($result)) {
            $has_results = true;
            $img_name = strtolower(str_replace(' ', '_', $row['name']));
        ?>

        <a href="products.php?subcategory=<?php echo $row['id']; ?>" class="subcategory-card">
            <div class="sub-img-box">
                <?php if (file_exists($img_name . '.jpg')): ?>
                    <img src="<?php echo $img_name; ?>.jpg" alt="<?php echo htmlspecialchars($row['name']); ?>">
                <?php else: ?>
                    <div class="sub-img-placeholder">🛍️</div>
                <?php endif; ?>
            </div>
            <div class="sub-info">
                <span><?php echo htmlspecialchars($row['name']); ?></span>
                <div class="sub-arrow">→</div>
            </div>
        </a>

        <?php
        }
        mysqli_stmt_close($stmt);

        if (!$has_results) { ?>
        <div class="empty-state">
            <div class="icon">🔍</div>
            <p>No subcategories found in this category yet.</p>
            <a href="index.php">Browse Other Categories</a>
        </div>
        <?php } ?>
    </div>
</div>

<footer>
    <div class="footer-inner">
        <div class="footer-top">
            <span class="footer-brand">Zenith</span>
            <nav class="footer-links">
                <a href="about.php"   class="footer-link">ℹ️ About</a>
                <a href="blog.php"    class="footer-link">📰 Blog</a>
                <a href="faq.php"     class="footer-link">❓ FAQ</a>
                <a href="contact.php" class="footer-link">📩 Contact</a>
            </nav>
        </div>
        <p class="footer-bottom">© <?php echo date('Y'); ?> Zenith. All rights reserved.</p>
    </div>
</footer>

</body>
</html>