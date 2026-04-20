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
    <title>About Us — Zenith</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,500;0,700;1,500&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
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
        /* ── HERO BANNER ── */
        .hero-banner {
            position: relative;
            height: 520px;
            overflow: hidden;
            background: var(--ink);
        }
        .hero-banner .banner-img {
            width: 100%; height: 100%;
            object-fit: cover; opacity: .55; display: block;
        }
        .hero-banner .banner-placeholder {
            width: 100%; height: 100%;
            background: linear-gradient(135deg, #2a2420 0%, #3d2e1e 50%, #1a1a18 100%);
            display: flex; align-items: center; justify-content: center;
            font-size: 8rem; opacity: .25;
        }
        .hero-banner .banner-overlay {
            position: absolute; inset: 0;
            display: flex; flex-direction: column;
            align-items: center; justify-content: center;
            text-align: center; padding: 0 24px;
        }
        .banner-tag {
            font-size: .75rem; font-weight: 500; letter-spacing: 3px;
            text-transform: uppercase;
            color: var(--accent-lt); margin-bottom: 16px;
        }
        .hero-banner h1 {
            font-family: 'Playfair Display', serif;
            font-size: clamp(2.4rem, 5vw, 4rem);
            color: var(--white); line-height: 1.15;
            max-width: 680px; margin-bottom: 20px;
        }
        .hero-banner .sub {
            font-size: 1.05rem;
            color: rgba(255,255,255,.72);
            max-width: 500px; line-height: 1.7;
        }

        /* ── MILESTONES ── */
        .milestones {
            background: var(--accent);
            padding: 52px 40px;
        }
        .milestones-inner {
            max-width: 800px; margin: 0 auto;
            display: flex; justify-content: space-around;
            flex-wrap: wrap; gap: 36px; text-align: center;
        }
        .milestone-number {
            font-family: 'Playfair Display', serif;
            font-size: 3.2rem; font-weight: 700;
            color: var(--white); line-height: 1; margin-bottom: 8px;
        }
        .milestone-label {
            font-size: .82rem; font-weight: 500;
            letter-spacing: 2px; text-transform: uppercase;
            color: rgba(255,255,255,.8);
        }

        /* ── STORY SECTIONS ── */
        .story {
            max-width: 1100px; margin: 0 auto;
            padding: 80px 40px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 72px; align-items: center;
        }
        .story.reverse { direction: rtl; }
        .story.reverse > * { direction: ltr; }
        .story-text h2 {
            font-family: 'Playfair Display', serif;
            font-size: 1.9rem; font-weight: 700;
            margin-bottom: 20px; line-height: 1.25;
        }
        .story-text h2 em { font-style: italic; color: var(--accent); }
        .story-text p {
            font-size: .97rem; line-height: 1.85;
            color: #3a3a38; margin-bottom: 14px;
        }
        .story-img {
            border-radius: 16px; overflow: hidden;
            aspect-ratio: 4/3; background: var(--warm);
        }
        .story-img img { width: 100%; height: 100%; object-fit: cover; display: block; }
        .img-placeholder {
            width: 100%; height: 100%; min-height: 280px;
            background: linear-gradient(135deg, var(--warm) 0%, var(--accent-lt) 100%);
            display: flex; flex-direction: column;
            align-items: center; justify-content: center;
            gap: 12px; color: var(--muted); font-size: .8rem; text-align: center;
            padding: 20px;
        }
        .img-placeholder span { font-size: 3rem; }

        /* ── DIVIDER ── */
        .divider {
            border: none; height: 1px;
            background: var(--warm);
            max-width: 1100px; margin: 0 auto;
        }

        /* ── PHOTO STRIP ── */
        .photo-strip {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            height: 340px; margin: 60px 0;
        }
        .strip-item {
            overflow: hidden; position: relative;
        }
        .strip-item:nth-child(1) { background: #e8e0d5; }
        .strip-item:nth-child(2) { background: #dfd6c8; }
        .strip-item:nth-child(3) { background: #e8e0d5; }
        .strip-item img {
            width: 100%; height: 100%;
            object-fit: cover; display: block;
            transition: transform .4s;
        }
        .strip-item:hover img { transform: scale(1.05); }
        .strip-placeholder {
            width: 100%; height: 100%;
            display: flex; flex-direction: column;
            align-items: center; justify-content: center;
            gap: 10px; color: var(--muted); font-size: .78rem;
        }
        .strip-placeholder span { font-size: 2.8rem; }

        /* ── LOCATIONS ── */
        .locations-section {
            max-width: 1100px; margin: 0 auto;
            padding: 0 40px 80px;
        }
        .locations-section h2 {
            font-family: 'Playfair Display', serif;
            font-size: 1.7rem; font-weight: 700; margin-bottom: 32px;
        }
        .locations-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px; margin-bottom: 40px;
        }
        .location-card {
            background: var(--white);
            border: 1px solid var(--warm);
            border-radius: var(--radius);
            padding: 28px 24px;
            transition: box-shadow .2s, transform .2s;
        }
        .location-card:hover { box-shadow: var(--shadow); transform: translateY(-3px); }
        .location-card h3 {
            font-family: 'Playfair Display', serif;
            font-size: 1.05rem; font-weight: 700; margin-bottom: 10px;
        }
        .location-card p { font-size: .87rem; line-height: 1.7; color: var(--muted); margin-bottom: 14px; }
        .location-meta { display: flex; flex-direction: column; gap: 6px; }
        .location-meta span {
            font-size: .83rem; color: var(--ink);
            display: flex; align-items: flex-start; gap: 8px;
        }
        .location-meta .label { color: var(--accent); font-weight: 500; min-width: 48px; }

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
        @media (max-width: 800px) {
            .story { grid-template-columns: 1fr; gap: 32px; padding: 48px 24px; }
            .story.reverse { direction: ltr; }
            .photo-strip { height: 220px; }
            .milestones { padding: 40px 24px; }
        }
        @media (max-width: 600px) {
            .navbar { padding: 0 20px; }
            .hero-banner { height: 380px; }
            .locations-section { padding: 0 20px 60px; }
            .footer-inner { padding: 32px 20px 20px; }
            .photo-strip { grid-template-columns: 1fr; height: auto; }
            .strip-item { height: 200px; }
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

<!-- HERO BANNER -->
<div class="hero-banner">
    <?php if (file_exists('about_banner.jpg')): ?>
        <img class="banner-img" src="about_banner.jpg" alt="About Us">
    <?php else: ?>
        <div class="banner-placeholder">🧵</div>
    <?php endif; ?>
    <div class="banner-overlay">
        <span class="banner-tag">Our Story</span>
        <h1>Fashion That Feels Like Home</h1>
        <p class="sub">Bringing quality clothing and timeless style to the heart of Bangladesh since day one.</p>
    </div>
</div>

<!-- MILESTONES -->
<div class="milestones">
    <div class="milestones-inner">
        <div class="milestone-item">
            <div class="milestone-number">2010</div>
            <div class="milestone-label">Year Founded</div>
        </div>
        <div class="milestone-item">
            <div class="milestone-number">250+</div>
            <div class="milestone-label">Products</div>
        </div>
        <div class="milestone-item">
            <div class="milestone-number">15+</div>
            <div class="milestone-label">Years of Experience</div>
        </div>
    </div>
</div>

<!-- STORY 1 -->
<div class="story">
    <div class="story-text">
        <h2>Rooted in <em>Bangladesh,</em><br>Dressed for the World</h2>
        <p>Zenith is rooted in the rich traditions and craftsmanship of Bangladesh, where clothing is more than just style — it is a reflection of culture, identity, and heritage.</p>
        <p>We collaborate with skilled local artisans who carry forward generations of craftsmanship, blending traditional techniques with modern design sensibilities. Each piece is thoughtfully created to honor our cultural roots while fitting seamlessly into everyday life.Whether it’s a celebration of tradition or a modern expression of everyday wear, Zenith exists to connect you with clothing that feels personal, timeless, and deeply connected to where we come from.</p>
    </div>
    <div class="story-img">
        <?php if (file_exists('about_story1.jpg')): ?>
            <img src="about_story1.jpg" alt="Our Story">
        <?php else: ?>
            <div class="img-placeholder"><span>👗</span>Replace with about_story1.jpg</div>
        <?php endif; ?>
    </div>
</div>

<hr class="divider">

<!-- STORY 2 -->
<div class="story reverse">
    <div class="story-text">
        <h2>Quality You Can <em>Feel,</em><br>Style You Can Trust</h2>
        <p>Over time, Zenith has grown from a single space into a brand that reflects the fashion culture of Dhaka, while staying true to its roots. Growth hasn’t changed our core — a commitment to quality, craftsmanship, and the people we serve.</p>
        <p>Each fabric is chosen with care, every stitch is checked with attention, and every collection is designed to balance tradition with modern style — helping you feel confident, comfortable, and connected to what you wear.</p>
    </div>
    <div class="story-img">
        <?php if (file_exists('about_story2.jpg')): ?>
            <img src="about_story2.jpg" alt="Our Quality">
        <?php else: ?>
            <div class="img-placeholder"><span>🪡</span>Replace with about_story2.jpg</div>
        <?php endif; ?>
    </div>
</div>

<!-- PHOTO STRIP -->
<div class="photo-strip">
    <div class="strip-item">
        <?php if (file_exists('about_photo1.jpg')): ?>
            <img src="about_photo1.jpg" alt="Store photo">
        <?php else: ?>
            <div class="strip-placeholder"><span>🏬</span>about_photo1.jpg</div>
        <?php endif; ?>
    </div>
    <div class="strip-item">
        <?php if (file_exists('about_photo2.jpg')): ?>
            <img src="about_photo2.jpg" alt="Store photo">
        <?php else: ?>
            <div class="strip-placeholder"><span>👔</span>about_photo2.jpg</div>
        <?php endif; ?>
    </div>
    <div class="strip-item">
        <?php if (file_exists('about_photo3.jpg')): ?>
            <img src="about_photo3.jpg" alt="Store photo">
        <?php else: ?>
            <div class="strip-placeholder"><span>🛍️</span>about_photo3.jpg</div>
        <?php endif; ?>
    </div>
</div>

<!-- STORE LOCATIONS -->
<div class="locations-section">
    <h2>Visit Us In Store</h2>
    <div class="locations-grid">
        <div class="location-card">
            <h3>Zenith Mirpur 1</h3>
            <p>Zoo Rd, Plot # 1, Block # G, Section # 1,<br>
               Multiplan Red Crescent City, Mirpur,<br>
               Dhaka, Bangladesh</p>
            <div class="location-meta">
                <span><span class="label">Tel</span> +880255075683, +880255075684</span>
                <span><span class="label">Hours</span> 10am – 8pm, open 7 days a week</span>
            </div>
        </div>
        <div class="location-card">
            <h3>Zenith Dhanmondi 1</h3>
            <p>House - 1/1, Block # A, Asad Gate,<br>
               Lalmatia, Mirpur Road, Dhanmondi,<br>
               Dhaka, Bangladesh</p>
            <div class="location-meta">
                <span><span class="label">Tel</span> +880258154710, +880258155231</span>
                <span><span class="label">Hours</span> 10am – 8pm, open 7 days a week</span>
            </div>
        </div>
		        <div class="location-card">
            <h3>Zenith Banani</h3>
            <p>Ashfiya Tower, House 76<br>
               Road 11, Banani<br>
               Dhaka, Bangladesh</p>
            <div class="location-meta">
                <span><span class="label">Tel</span>  +880241082245, +880241082246</span>
                <span><span class="label">Hours</span> 10am – 8pm, open 7 days a week</span>
            </div>
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