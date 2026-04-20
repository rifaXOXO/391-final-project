<?php
session_start();
include 'db.php';
if (!isset($_GET['id'])) {
    header("Location: blog.php");
    exit();
}
$id = (int) $_GET['id'];
$result = mysqli_query($conn, "SELECT * FROM blog_posts WHERE id = $id");
$post = mysqli_fetch_assoc($result);
if (!$post) {
    header("Location: blog.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($post['title']); ?> — Zenith</title>
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
        .nav-btn.back { background: var(--warm); color: var(--muted); }
        .nav-btn.back:hover { background: #e0dbd2; color: var(--ink); }
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
        .post-date {
            font-size: .78rem; font-weight: 500;
            color: var(--accent);
            letter-spacing: .5px;
            text-transform: uppercase;
            margin-bottom: 12px;
        }
        .page-header h1 {
            font-family: 'Playfair Display', serif;
            font-size: 2rem; font-weight: 700;
            line-height: 1.25;
            max-width: 700px;
        }

        /* ── ARTICLE ── */
        .article {
            max-width: 780px;
            margin: 48px auto;
            padding: 0 40px 80px;
        }

        /* ── POST IMAGE ── */
        .post-image {
            width: 100%;
            border-radius: var(--radius);
            overflow: hidden;
            background: linear-gradient(135deg, var(--warm), var(--accent-lt));
            display: flex; align-items: center; justify-content: center;
            margin-bottom: 40px;
            border: 1px solid var(--warm);
        }
        .post-image img {
            width: 100%;
            height: auto;
            object-fit: contain;
            display: block;
        }
        .post-image-placeholder {
            font-size: 4rem;
            padding: 60px;
        }

        /* ── CONTENT ── */
        .post-content {
            font-size: 1rem;
            line-height: 1.85;
            color: var(--ink);
            white-space: pre-line;
        }
        .post-content p { margin-bottom: 1.2em; }

        /* ── BACK LINK ── */
        .back-footer {
            margin-top: 48px;
            padding-top: 28px;
            border-top: 1px solid var(--warm);
        }
        .back-footer a {
            display: inline-flex; align-items: center; gap: 6px;
            font-size: .88rem; font-weight: 500;
            color: var(--muted); text-decoration: none;
            transition: color .18s;
        }
        .back-footer a:hover { color: var(--accent); }

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
            .article { padding: 0 20px 60px; margin: 32px auto; }
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
        <a href="blog.php" class="nav-btn back">← Blog</a>
    </div>
</nav>

<!-- PAGE HEADER -->
<div class="page-header">
    <p class="breadcrumb">
        <a href="index.php">Home</a> →
        <a href="blog.php">Blog</a> →
        <?php echo htmlspecialchars($post['title']); ?>
    </p>
    <p class="post-date"><?php echo date("F j, Y", strtotime($post['created_at'])); ?></p>
    <h1><?php echo htmlspecialchars($post['title']); ?></h1>
</div>

<!-- ARTICLE -->
<div class="article">

    <?php if (!empty($post['image'])): ?>
        <div class="post-image">
            <img src="<?php echo htmlspecialchars($post['image']); ?>"
                 alt="<?php echo htmlspecialchars($post['title']); ?>">
        </div>
    <?php else: ?>
        <div class="post-image">
            <div class="post-image-placeholder">📰</div>
        </div>
    <?php endif; ?>

    <div class="post-content">
        <?php echo htmlspecialchars($post['content']); ?>
    </div>

    <div class="back-footer">
        <a href="blog.php">← Back to Blog</a>
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