<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$cart = $_SESSION['cart'] ?? [];
$total = 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Cart — Zenith</title>
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
        .nav-btn {
            display: flex; align-items: center; gap: 6px;
            padding: 8px 16px; border-radius: 8px;
            font-family: 'DM Sans', sans-serif;
            font-size: .85rem; font-weight: 500;
            text-decoration: none;
            transition: background .18s, color .18s;
            background: var(--warm); color: var(--muted);
        }
        .nav-btn:hover { background: #e0dbd2; color: var(--ink); }

        /* ── PAGE HEADER ── */
        .page-header {
            background: var(--white);
            border-bottom: 1px solid var(--warm);
            padding: 32px 40px 28px;
        }
        .breadcrumb {
            font-size: .8rem; color: var(--muted); margin-bottom: 10px;
        }
        .breadcrumb a { color: var(--accent); text-decoration: none; }
        .breadcrumb a:hover { text-decoration: underline; }
        .page-header h1 {
            font-family: 'Playfair Display', serif;
            font-size: 2rem; font-weight: 700;
        }

        /* ── LAYOUT ── */
        .section {
            max-width: 900px;
            margin: 48px auto;
            padding: 0 40px;
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        /* ── CART ITEM ── */
        .cart-item {
            display: flex;
            align-items: center;
            gap: 20px;
            background: var(--white);
            border: 1px solid var(--warm);
            border-radius: var(--radius);
            padding: 16px 20px;
            transition: box-shadow .2s;
        }
        .cart-item:hover { box-shadow: var(--shadow); }
        .cart-item-img {
            width: 90px; height: 90px;
            border-radius: 10px;
            overflow: hidden;
            background: var(--accent-lt);
            flex-shrink: 0;
        }
        .cart-item-img img {
            width: 100%; height: 100%;
            object-fit: cover; display: block;
        }
        .cart-item-img-placeholder {
            width: 90px; height: 90px;
            border-radius: 10px;
            background: var(--accent-lt);
            display: flex; align-items: center; justify-content: center;
            font-size: 2rem;
            flex-shrink: 0;
        }
        .cart-info { flex: 1; }
        .cart-name {
            font-size: .95rem; font-weight: 700;
            color: var(--ink); margin-bottom: 6px;
        }
        .cart-qty {
            display: inline-block;
            font-size: .78rem; font-weight: 500;
            color: var(--muted);
            background: var(--warm);
            padding: 3px 10px;
            border-radius: 20px;
        }
        .cart-price {
            font-family: 'Playfair Display', serif;
            font-size: 1.1rem; font-weight: 700;
            color: var(--accent);
            white-space: nowrap;
        }

        /* ── SUMMARY BOX ── */
        .summary-box {
            background: var(--white);
            border: 1px solid var(--warm);
            border-radius: var(--radius);
            padding: 24px 28px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            margin-top: 8px;
        }
        .summary-label {
            font-size: .85rem;
            color: var(--muted);
            margin-bottom: 4px;
        }
        .summary-total {
            font-family: 'Playfair Display', serif;
            font-size: 1.6rem; font-weight: 700;
            color: var(--ink);
        }
        .checkout-btn {
            display: inline-flex; align-items: center; gap: 8px;
            padding: 13px 32px;
            background: var(--ink);
            color: var(--white);
            border-radius: 8px;
            font-size: .9rem; font-weight: 500;
            text-decoration: none;
            transition: background .18s;
            white-space: nowrap;
        }
        .checkout-btn:hover { background: var(--accent); }

        /* ── EMPTY STATE ── */
        .empty-state {
            text-align: center;
            padding: 80px 20px;
            color: var(--muted);
        }
        .empty-state .icon { font-size: 3rem; margin-bottom: 16px; }
        .empty-state p { font-size: 1rem; margin-bottom: 20px; }
        .empty-state a {
            display: inline-block; padding: 10px 24px;
            background: var(--accent); color: var(--white);
            border-radius: 8px; text-decoration: none;
            font-size: .9rem; font-weight: 500;
            transition: background .18s;
        }
        .empty-state a:hover { background: #b5622c; }
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
        @media (max-width: 600px) {
            .navbar { padding: 0 20px; }
            .page-header { padding: 24px 20px; }
            .section { padding: 0 20px; margin: 32px auto; }
            .summary-box { flex-direction: column; align-items: flex-start; }
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
    <a href="index.php" class="nav-btn">← Continue Shopping</a>
</nav>

<!-- PAGE HEADER -->
<div class="page-header">
    <p class="breadcrumb">
        <a href="index.php">Home</a> → Cart
    </p>
    <h1>Your Cart</h1>
</div>

<!-- CART CONTENTS -->
<div class="section">
    <?php if (empty($cart)): ?>
        <div class="empty-state">
            <div class="icon">🛒</div>
            <p>Your cart is empty.</p>
            <a href="index.php">← Continue Shopping</a>
        </div>
    <?php else: ?>

        <?php foreach ($cart as $id => $item):
            $subtotal = $item['price'] * $item['qty'];
            $total += $subtotal;
        ?>
        <div class="cart-item">

            <?php if (!empty($item['image'])): ?>
                <div class="cart-item-img">
                    <img src="<?php echo htmlspecialchars($item['image']); ?>" alt="">
                </div>
            <?php else: ?>
                <div class="cart-item-img-placeholder">🛍️</div>
            <?php endif; ?>

            <div class="cart-info">
                <div class="cart-name"><?php echo htmlspecialchars($item['name']); ?></div>
                <span class="cart-qty">Qty: <?php echo $item['qty']; ?></span>
            </div>

            <div class="cart-price">
                <?php echo number_format($subtotal); ?> Tk
            </div>

        </div>
        <?php endforeach; ?>

        <div class="summary-box">
            <div>
                <p class="summary-label">Order Total</p>
                <p class="summary-total"><?php echo number_format($total); ?> Tk</p>
            </div>
            <a href="checkout.php" class="checkout-btn">Proceed to Checkout →</a>
			<a href="javascript:history.back()" class="back-link">← Back</a>
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