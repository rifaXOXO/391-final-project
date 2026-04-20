<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$id      = intval($_GET['id']);
$result  = mysqli_query($conn, "SELECT * FROM products WHERE id = $id");
$product = mysqli_fetch_assoc($result);

if (!$product) {
    header("Location: index.php");
    exit();
}

// get subcategory and category for breadcrumb
$sub_result = mysqli_query($conn, "SELECT * FROM subcategories WHERE id = {$product['subcategory_id']}");
$sub_row    = mysqli_fetch_assoc($sub_result);
$cat_result = mysqli_query($conn, "SELECT * FROM categories WHERE id = {$sub_row['category_id']}");
$cat_row    = mysqli_fetch_assoc($cat_result);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['name']); ?> — Zenith</title>
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
            padding: 0 40px;
            height: 68px;
            background: var(--white);
            border-bottom: 1px solid var(--warm);
            box-shadow: 0 2px 12px rgba(26,26,24,.06);
        }
        .navbar .logo {
            display: flex; align-items: center; gap: 12px;
            text-decoration: none;
        }
        .navbar .logo img {
            width: 42px; height: 42px;
            border-radius: 10px; object-fit: contain;
        }
        .navbar .store-name {
            font-family: 'Playfair Display', serif;
            font-size: 1.35rem; font-weight: 700;
            color: var(--ink); letter-spacing: -.3px;
        }
        .navbar .nav-actions {
            display: flex; align-items: center; gap: 8px;
        }
        .nav-btn {
            display: flex; align-items: center; gap: 6px;
            padding: 8px 16px;
            border-radius: 8px;
            font-family: 'DM Sans', sans-serif;
            font-size: .85rem; font-weight: 500;
            text-decoration: none;
            transition: background .18s, color .18s;
        }
        .nav-btn.cart { background: var(--accent-lt); color: var(--accent); }
        .nav-btn.cart:hover { background: var(--accent); color: var(--white); }
        .nav-btn.logout { background: var(--warm); color: var(--muted); }
        .nav-btn.logout:hover { background: #e0dbd2; color: var(--ink); }
        .nav-btn.orders { background: var(--warm); color: #7A716E; }
        .nav-btn.orders:hover { background: #e0dbd2; color: var(--ink); }

        /* ── PAGE HEADER ── */
        .page-header {
            background: var(--white);
            border-bottom: 1px solid var(--warm);
            padding: 32px 40px 28px;
        }
        .page-header .breadcrumb {
            font-size: .8rem; color: var(--muted);
            margin-bottom: 10px;
        }
        .page-header .breadcrumb a {
            color: var(--accent); text-decoration: none;
        }
        .page-header .breadcrumb a:hover { text-decoration: underline; }
        .page-header h1 {
            font-family: 'Playfair Display', serif;
            font-size: 2rem; font-weight: 700;
        }

        /* ── SECTION ── */
        .section {
            padding: 48px 40px;
            max-width: 1200px;
            margin: 0 auto;
        }

        /* ── PRODUCT LAYOUT ── */
        .product-layout {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 48px;
            align-items: start;
        }

        /* ── IMAGE ── */
        .product-img-box {
            width: 100%;
            background: var(--white);
            border: 1px solid var(--warm);
            border-radius: var(--radius);
            overflow: hidden;
            display: flex; align-items: center; justify-content: center;
        }
        .product-img-box img {
            width: 100%;
            height: auto;
            object-fit: contain;
            display: block;
        }
        .product-img-placeholder {
            width: 100%;
            padding: 80px 0;
            background: linear-gradient(135deg, var(--warm), var(--accent-lt));
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 5rem;
            border-radius: var(--radius);
            border: 1px solid var(--warm);
        }

        /* ── INFO ── */
        .product-info {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        .product-name {
            font-family: 'Playfair Display', serif;
            font-size: 1.8rem; font-weight: 700;
            line-height: 1.25;
            color: var(--ink);
        }
        .product-price {
            font-family: 'Playfair Display', serif;
            font-size: 1.5rem; font-weight: 700;
            color: #9D1C06;
        }
        .divider {
            border: none;
            border-top: 1px solid var(--warm);
        }
        .desc-label {
            font-size: .75rem; font-weight: 500;
            text-transform: uppercase; letter-spacing: .5px;
            color: var(--muted);
            margin-bottom: 8px;
        }
        .product-desc {
            font-size: .92rem;
            line-height: 1.8;
            color: var(--muted);
        }
        .add-to-cart {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 14px 32px;
            background: var(--ink);
            color: var(--white);
            border-radius: 8px;
            font-family: 'DM Sans', sans-serif;
            font-size: .95rem; font-weight: 500;
            text-decoration: none;
            transition: background .18s;
            text-align: center;
        }
        .add-to-cart:hover { background: var(--accent); }
        .back-link {
			display: inline-flex; align-items: center; justify-content: center; gap: 6px;
			font-size: .85rem; font-weight: 500;
			color: var(--muted);
			text-decoration: none;
			transition: background .18s, color .18s;
			background: #EDF5B0;
			padding: 10px 20px;
			border-radius: 8px;
			width: fit-content;
		}
		.back-link:hover { background: #e0dbd2; color: var(--ink); }

        /* ── FOOTER ── */
        footer {
            background: var(--ink);
            color: rgba(255,255,255,.55);
            margin-top: 60px;
        }
        .footer-inner {
            max-width: 1200px; margin: 0 auto;
            padding: 40px 40px 28px;
            display: flex; flex-direction: column; gap: 32px;
        }
        .footer-top {
            display: flex; align-items: center; justify-content: space-between;
            flex-wrap: wrap; gap: 20px;
        }
        .footer-brand {
            font-family: 'Playfair Display', serif;
            font-size: 1.25rem; color: var(--white);
        }
        .footer-links { display: flex; gap: 8px; flex-wrap: wrap; }
        .footer-link {
            display: flex; align-items: center; gap: 7px;
            padding: 9px 18px;
            background: rgba(255,255,255,.07);
            border-radius: 8px;
            color: rgba(255,255,255,.8);
            font-size: .84rem;
            text-decoration: none;
            transition: background .16s, color .16s;
        }
        .footer-link:hover { background: rgba(255,255,255,.14); color: var(--white); }
        .footer-bottom {
            font-size: .75rem;
            border-top: 1px solid rgba(255,255,255,.1);
            padding-top: 20px;
        }

        /* ── RESPONSIVE ── */
        @media (max-width: 750px) {
            .product-layout { grid-template-columns: 1fr; gap: 24px; }
            .navbar { padding: 0 20px; }
            .page-header { padding: 24px 20px; }
            .section { padding: 32px 20px; }
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
    <div class="nav-actions">
        <a href="my_orders.php" class="nav-btn orders">📦 My Orders</a>
        <a href="cart.php" class="nav-btn cart">🛒 Cart</a>
        <a href="logout.php" class="nav-btn logout">Logout</a>
    </div>
</nav>

<!-- PAGE HEADER -->
<div class="page-header">
    <p class="breadcrumb">
        <a href="index.php">Home</a> →
        <?php if ($cat_row): ?>
            <a href="subcategories.php?category=<?php echo $cat_row['id']; ?>">
                <?php echo htmlspecialchars($cat_row['name']); ?>
            </a> →
        <?php endif; ?>
        <?php if ($sub_row): ?>
            <a href="products.php?subcategory=<?php echo $sub_row['id']; ?>">
                <?php echo htmlspecialchars($sub_row['name']); ?>
            </a> →
        <?php endif; ?>
        <?php echo htmlspecialchars($product['name']); ?>
    </p>
    <h1><?php echo htmlspecialchars($product['name']); ?></h1>
</div>

<!-- PRODUCT -->
<div class="section">
    <div class="product-layout">

        <!-- IMAGE -->
        <?php if (!empty($product['image']) && file_exists(__DIR__ . '/' . $product['image'])): ?>
            <div class="product-img-box">
                <img src="<?php echo htmlspecialchars($product['image']); ?>"
                     alt="<?php echo htmlspecialchars($product['name']); ?>">
            </div>
        <?php else: ?>
            <div class="product-img-placeholder">🛍️</div>
        <?php endif; ?>

        <!-- INFO -->
        <div class="product-info">
            <p class="product-price"><?php echo number_format($product['price']); ?> Tk</p>
            <hr class="divider">
            <div>
                <p class="desc-label">Description</p>
                <p class="product-desc">
                    <?php echo nl2br(htmlspecialchars($product['description'] ?? 'No description available.')); ?>
                </p>
            </div>
            <hr class="divider">
            <a href="add_to_cart.php?id=<?php echo $product['id']; ?>" class="add-to-cart">
                🛒 Add to Cart
            </a>
            <a href="javascript:history.back()" class="back-link">← Back</a>
        </div>

    </div>
</div>

<!-- FOOTER -->
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