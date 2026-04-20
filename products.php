<?php
session_start();
include 'db.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
}
if (!isset($_GET['subcategory'])) {
    echo "No subcategory selected";
    exit();
}

$subcategory_id = (int) $_GET['subcategory'];

$sub_result = mysqli_query($conn, "SELECT name FROM subcategories WHERE id = $subcategory_id");
$sub_row = mysqli_fetch_assoc($sub_result);
$subcategory_name = $sub_row ? $sub_row['name'] : 'Products';
$cat_result = mysqli_query($conn, "
    SELECT c.id, c.name 
    FROM categories c
    JOIN subcategories s ON s.category_id = c.id
    WHERE s.id = $subcategory_id
");

$cat_row = mysqli_fetch_assoc($cat_result);
$category_name = $cat_row['name'] ?? 'Category';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $subcategory_name; ?> — Zenith</title>
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
		.nav-btn.orders { background: var(--warm); color:#7A716E; }
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

        /* ── PRODUCTS GRID ── */
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
        }

        /* ── PRODUCT CARD — vertical like blog ── */
        .product-card {
            background: var(--white);
            border: 1px solid var(--warm);
            border-radius: var(--radius);
            overflow: hidden;
            display: flex;
            flex-direction: column;
            transition: box-shadow .2s, transform .2s, border-color .2s;
        }
        .product-card:hover {
            box-shadow: var(--shadow);
            transform: translateY(-4px);
            border-color: #d4cfc6;
        }

        /* ── IMAGE ── */
        .product-img-box {
            width: 100%;
           
            overflow: hidden;
            background: var(--accent-lt);
            flex-shrink: 0;
        }
        .product-img-box img {
            width: 100%;
            height: auto;
            object-fit: contain;
            display: block;
            transition: transform .3s;
        }
        .product-card:hover .product-img-box img {
            transform: scale(1.03);
        }
        .product-img-placeholder {
            width: 100%;
            height: 360px;
            background: linear-gradient(135deg, var(--warm), var(--accent-lt));
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
        }

        /* ── BODY ── */
        .product-body {
            padding: 16px 18px 20px;
            display: flex;
            flex-direction: column;
            flex: 1;
            gap: 8px;
        }
        .product-name {
            font-size: .95rem;
            font-weight: 700;
            color: var(--ink);
            line-height: 1.3;
        }
        .product-desc {
            font-size: .78rem;
            color: var(--muted);
            line-height: 1.5;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
            flex: 1;
        }
        .product-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            margin-top: 8px;
        }
        .product-price {
            font-family: 'Playfair Display', serif;
            font-size: 1.05rem;
            font-weight: 700;
            color: #9D1C06;
        }
        .add-to-cart {
            padding: 7px 14px;
            background: var(--ink);
            color: var(--white);
            border-radius: 8px;
            font-size: .8rem;
            font-weight: 500;
            text-decoration: none;
            white-space: nowrap;
            transition: background .18s;
        }
        .add-to-cart:hover { background: var(--accent); }

        /* ── EMPTY STATE ── */
        .empty-state {
            text-align: center;
            padding: 80px 20px;
            color: var(--muted);
        }
        .empty-state .icon { font-size: 3rem; margin-bottom: 16px; }
        .empty-state p { font-size: 1rem; margin-bottom: 20px; }
        .empty-state a {
            display: inline-block;
            padding: 10px 24px;
            background: var(--accent);
            color: var(--white);
            border-radius: 8px;
            text-decoration: none;
            font-size: .9rem; font-weight: 500;
        }
		.top-bar {
			display: flex;
			justify-content: flex-end;
		}

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
        @media (max-width: 600px) {
            .navbar { padding: 0 20px; }
            .page-header { padding: 24px 20px; }
            .section { padding: 32px 20px; }
            .footer-inner { padding: 32px 20px 20px; }
            .products-grid { grid-template-columns: 1fr; }
            .product-img-box,
            .product-img-placeholder { height: 260px; }
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
        <?php echo htmlspecialchars($category_name); ?> →
        <?php echo htmlspecialchars($subcategory_name); ?>
    </p>
    <h1><?php echo htmlspecialchars($subcategory_name); ?></h1>
	<div class="top-bar">
		<a href="javascript:history.back()" class="back-link">← Back</a>
	</div>
</div>

<!-- PRODUCTS -->
<div class="section">
    <?php
    $result = mysqli_query($conn, "SELECT * FROM products WHERE subcategory_id = $subcategory_id");
    if (mysqli_num_rows($result) > 0):
    ?>
    <div class="products-grid">
        <?php while ($row = mysqli_fetch_assoc($result)): ?>
		
			<div class="product-card">
				
		
				<?php if (!empty($row['image']) && file_exists(__DIR__ . '/' . $row['image'])): ?>
					<a href="single_product.php?id=<?php echo $row['id']; ?>" class="product-link">
					<div class="product-img-box">
						<img src="<?php echo $row['image']; ?>" alt="<?php echo htmlspecialchars($row['name']); ?>">
					</div>
					</a>
				<?php else: ?>
					<div class="product-img-placeholder">🛍️</div>
				<?php endif; ?>

				<div class="product-body">
					<p class="product-name"><?php echo htmlspecialchars($row['name']); ?></p>
					<p class="product-desc"><?php echo htmlspecialchars($row['description']); ?></p>
					<div class="product-footer">
						<span class="product-price"><?php echo number_format($row['price']); ?> Tk</span>
						<a href="add_to_cart.php?id=<?php echo $row['id']; ?>" class="add-to-cart">Add to Cart</a>
					</div>
				</div>
				
			</div>
		
        <?php endwhile; ?>
    </div>

    <?php else: ?>
    <div class="empty-state">
        <div class="icon">📦</div>
        <p>No products found in this category.</p>
        <a href="index.php">← Back to Home</a>
    </div>
    <?php endif; ?>
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