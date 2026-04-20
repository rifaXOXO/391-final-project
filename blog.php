<?php
session_start();
include 'db.php';
$result = mysqli_query($conn, "SELECT * FROM blog_posts ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blog — Zenith</title>
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

        /* ── BLOG GRID ── */
        .blog-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 24px;
        }

        /* ── BLOG CARD ── */
        .blog-card {
            background: var(--white);
            border: 1px solid var(--warm);
            border-radius: var(--radius);
            overflow: hidden;
            text-decoration: none;
            color: var(--ink);
            display: flex;
            flex-direction: column;
            transition: box-shadow .2s, transform .2s, border-color .2s;
        }
        .blog-card:hover {
            box-shadow: var(--shadow);
            transform: translateY(-4px);
            border-color: var(--accent-lt);
        }
        .blog-img {
            width: 100%;
            overflow: hidden;
            background: linear-gradient(135deg, var(--warm), var(--accent-lt));
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }
        .blog-img img {
            width: 100%;
            height: auto;
            object-fit: contain;
            display: block;
            transition: transform .3s;
        }
        .blog-card:hover .blog-img img { transform: scale(1.02); }
        .blog-img-placeholder {
            font-size: 3rem;
            padding: 40px;
        }

        .blog-body {
            padding: 20px 22px 24px;
            display: flex;
            flex-direction: column;
            flex: 1;
            gap: 10px;
        }
        .blog-date {
            font-size: .75rem; font-weight: 500;
            color: var(--accent);
            letter-spacing: .5px;
            text-transform: uppercase;
        }
        .blog-title {
            font-family: 'Playfair Display', serif;
            font-size: 1.1rem; font-weight: 700;
            color: var(--ink);
            line-height: 1.35;
        }
        .blog-text {
            font-size: .85rem;
            color: var(--muted);
            line-height: 1.6;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
            flex: 1;
        }
        .blog-read-more {
            font-size: .82rem; font-weight: 500;
            color: var(--accent);
            margin-top: 4px;
            display: inline-flex; align-items: center; gap: 4px;
            transition: gap .2s;
        }
        .blog-card:hover .blog-read-more { gap: 8px; }

        /* ── EMPTY STATE ── */
        .empty-state {
            text-align: center; padding: 80px 20px;
            color: var(--muted); grid-column: 1 / -1;
        }
        .empty-state .icon { font-size: 3rem; margin-bottom: 16px; }
        .empty-state p { font-size: 1rem; }

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
    <div class="nav-actions">
		<a href="my_orders.php" class="nav-btn orders">📦 My Orders</a>
        <a href="cart.php" class="nav-btn cart">🛒 Cart</a>
        <a href="logout.php" class="nav-btn logout">Logout</a>
    </div>
</nav>

<!-- PAGE HEADER -->
<div class="page-header">
    <p class="breadcrumb">
        <a href="index.php">Home</a> → Blog
    </p>
    <h1>Our Blog</h1>
</div>

<!-- BLOG GRID -->
<div class="section">
    <div class="blog-grid">
        <?php
        $has = false;
        while ($row = mysqli_fetch_assoc($result)):
            $has = true;
        ?>
        <a href="blog_single.php?id=<?php echo $row['id']; ?>" class="blog-card">
            <div class="blog-img">
                <?php if (!empty($row['image'])): ?>
                    <img src="<?php echo htmlspecialchars($row['image']); ?>"
                         alt="<?php echo htmlspecialchars($row['title']); ?>">
                <?php else: ?>
                    <div class="blog-img-placeholder">📰</div>
                <?php endif; ?>
            </div>
            <div class="blog-body">
                <span class="blog-date"><?php echo date("F j, Y", strtotime($row['created_at'])); ?></span>
                <div class="blog-title"><?php echo htmlspecialchars($row['title']); ?></div>
                <div class="blog-text"><?php echo htmlspecialchars($row['content']); ?></div>
                <span class="blog-read-more">Read more →</span>
            </div>
        </a>
        <?php endwhile; ?>

        <?php if (!$has): ?>
        <div class="empty-state">
            <div class="icon">📰</div>
            <p>No blog posts yet. Check back soon!</p>
        </div>
        <?php endif; ?>
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