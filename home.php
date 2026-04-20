<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Zenith</title>
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
        .nav-btn.cart {
            background: var(--accent-lt); color: var(--accent);
        }
        .nav-btn.cart:hover { background: var(--accent); color: var(--white); }
        .nav-btn.logout {
            background: var(--warm); color: var(--muted);
        }
        .nav-btn.logout:hover { background: #e0dbd2; color: var(--ink); }
        .nav-btn.orders { background: var(--warm); color:#7A716E; }
        .nav-btn.orders:hover { background: #e0dbd2; color: var(--ink); }

        /* ── HERO ── */
        .hero {
            position: relative; overflow: hidden;
            height: 420px;
            margin: 0;
        }
        .hero img {
            width: 100%; height: 100%; object-fit: cover;
            display: block;
        }
        .hero-overlay {
            position: absolute; inset: 0;
            background: linear-gradient(100deg, rgba(26,26,24,.62) 0%, rgba(26,26,24,.15) 60%);
            display: flex; flex-direction: column;
            justify-content: center;
            padding: 0 64px;
        }
        .hero-tag {
            display: inline-block;
            font-size: .75rem; font-weight: 500; letter-spacing: 2px;
            text-transform: uppercase;
            color: var(--accent-lt);
            margin-bottom: 14px;
        }
        .hero h1 {
            font-family: 'Playfair Display', serif;
            font-size: clamp(2rem, 4vw, 3rem);
            color: var(--white);
            line-height: 1.15;
            max-width: 480px;
            margin-bottom: 24px;
        }

        /* ── SECTION WRAPPER ── */
        .section { padding: 56px 40px; max-width: 1200px; margin: 0 auto; }
        .section-header {
            display: flex; align-items: baseline; justify-content: space-between;
            margin-bottom: 32px;
        }
        .section-header h2 {
            font-family: 'Playfair Display', serif;
            font-size: 1.7rem; font-weight: 700;
        }

        /* ── PROFESSIONAL CATEGORIES ── */
        .categories-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 32px;
        }

        .category-card {
            position: relative;
            display: block;
            text-decoration: none;
            background: var(--white);
            border-radius: 16px;
            overflow: hidden;
            border: 1px solid rgba(26,26,24, 0.05);
            transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
        }

        .category-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.06);
            border-color: var(--accent-lt);
        }

        .cat-img-box {
            width: 100%;
            height: 240px;
            background: var(--warm);
            overflow: hidden;
            position: relative;
        }

        .cat-img-box img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.6s ease;
        }

        .category-card:hover .cat-img-box img {
            transform: scale(1.08);
        }

        .cat-info {
            padding: 24px;
            background: var(--white);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .cat-info span {
            font-family: 'Playfair Display', serif;
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--ink);
        }

        .cat-arrow {
            width: 36px;
            height: 36px;
            background: var(--cream);
            color: var(--accent);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }

        .category-card:hover .cat-arrow {
            background: var(--accent);
            color: var(--white);
            transform: translateX(4px);
        }

        .cat-img-placeholder {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            background: var(--warm);
        }

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
        .footer-links {
            display: flex; gap: 8px; flex-wrap: wrap;
        }
        .footer-link {
            display: flex; align-items: center; gap: 7px;
            padding: 9px 18px;
            background: rgba(255,255,255,.07);
            border-radius: 8px;
            color: rgba(255,255,255,.8);
            font-size: .84rem; font-weight: 400;
            text-decoration: none;
            transition: background .16s, color .16s;
        }
        .footer-link:hover {
            background: rgba(255,255,255,.14);
            color: var(--white);
        }
        .footer-bottom {
            font-size: .75rem;
            border-top: 1px solid rgba(255,255,255,.1);
            padding-top: 20px;
        }

        /* ── RESPONSIVE ── */
        @media (max-width: 600px) {
            .navbar { padding: 0 20px; }
            .hero-overlay { padding: 0 28px; }
            .section { padding: 40px 20px; }
            .footer-inner { padding: 32px 20px 20px; }
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

<div class="hero">
    <img src="feature.jpg" alt="Featured collection">
    <div class="hero-overlay">
        <span class="hero-tag">New Arrivals</span>
        <h1>Discover Our Latest Collection</h1>
    </div>
</div>

<div class="section">
    <div class="section-header">
        <h2>Browse Categories</h2>
    </div>
    <div class="categories-grid">
        <?php
        $cat = mysqli_query($conn, "SELECT * FROM categories");
        while ($c = mysqli_fetch_assoc($cat)) {
            $img_name = strtolower(str_replace(' ', '_', $c['name']));
        ?>
        <a href="subcategories.php?category=<?php echo $c['id']; ?>" class="category-card">
            <div class="cat-img-box">
                <?php if (file_exists($img_name . '.jpg')): ?>
                    <img src="<?php echo $img_name; ?>.jpg" alt="<?php echo htmlspecialchars($c['name']); ?>">
                <?php else: ?>
                    <div class="cat-img-placeholder">🛍️</div>
                <?php endif; ?>
            </div>
            <div class="cat-info">
                <span><?php echo htmlspecialchars($c['name']); ?></span>
                <div class="cat-arrow">→</div>
            </div>
        </a>
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