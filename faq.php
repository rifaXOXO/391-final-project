<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FAQ — Zenith</title>
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
            max-width: 780px;
            margin: 48px auto;
            padding: 0 40px 80px;
        }

        /* ── FAQ ITEMS ── */
        .faq-item {
            background: var(--white);
            border: 1px solid var(--warm);
            border-radius: var(--radius);
            margin-bottom: 12px;
            overflow: hidden;
            transition: border-color .2s, box-shadow .2s;
        }
        .faq-item.open {
            border-color: var(--accent-lt);
            box-shadow: var(--shadow);
        }
        .question {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding: 18px 22px;
            cursor: pointer;
            font-size: .95rem;
            font-weight: 500;
            color: var(--ink);
            user-select: none;
            transition: background .18s;
        }
        .question:hover { background: var(--cream); }
        .faq-item.open .question { background: var(--accent-lt); color: var(--ink); }
        .faq-icon {
            width: 24px; height: 24px;
            border-radius: 50%;
            background: var(--warm);
            display: flex; align-items: center; justify-content: center;
            font-size: .85rem;
            flex-shrink: 0;
            transition: background .18s, transform .25s;
            color: var(--muted);
            font-style: normal;
        }
        .faq-item.open .faq-icon {
            background: var(--accent);
            color: var(--white);
            transform: rotate(45deg);
        }
        .answer {
            display: none;
            padding: 0 22px 18px;
            font-size: .9rem;
            color: var(--muted);
            line-height: 1.7;
            border-top: 1px solid var(--warm);
            padding-top: 14px;
        }
        .faq-item.open .answer { display: block; }

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
        <a href="index.php">Home</a> → FAQ
    </p>
    <h1>Frequently Asked Questions</h1>
</div>

<!-- FAQ -->
<div class="section">

    <?php
    $faqs = [
        ["What is Zenith?", "Zenith is an online store where you can browse categories, discover products, and place orders — all in one place."],
        ["Do I need an account to shop?", "Yes, you need to log in to browse products and place orders. This helps us keep your cart and order history safe."],
        ["How do I place an order?", "Browse a category, add items to your cart, then head to checkout and fill in your shipping details."],
        ["What payment methods are accepted?", "Currently we accept cash on delivery. More payment options may be added in the future."],
        ["How long does delivery take?", "Delivery typically takes 2–5 business days depending on your location."],
        ["Can I cancel or change my order?", "Please contact us as soon as possible via the contact page if you need to make changes to your order."],
        ["Who can post blog articles?", "Only admin users are allowed to create and publish blog posts on Zenith."],
        ["Is my personal information safe?", "Yes, we take privacy seriously and do not share your personal data with third parties."],
        ["Can I suggest products or topics?", "Absolutely! Use the contact page to send us suggestions and we'll do our best to consider them."],
        ["How do I contact support?", "You can reach us anytime through the contact page linked in the footer below."],
    ];
    foreach ($faqs as $faq): ?>
    <div class="faq-item">
        <div class="question">
            <?php echo $faq[0]; ?>
            <i class="faq-icon">+</i>
        </div>
        <div class="answer"><?php echo $faq[1]; ?></div>
    </div>
    <?php endforeach; ?>

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

<script>
    document.querySelectorAll('.faq-item').forEach(item => {
        item.querySelector('.question').addEventListener('click', () => {
            const isOpen = item.classList.contains('open');
            document.querySelectorAll('.faq-item').forEach(i => i.classList.remove('open'));
            if (!isOpen) item.classList.add('open');
        });
    });
</script>

</body>
</html>