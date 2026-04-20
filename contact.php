<<?php
session_start();
include 'db.php';

// FIXED SESSION CHECK (use your actual system)
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$submitted = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name    = mysqli_real_escape_string($conn, trim($_POST['name']));
    $email   = mysqli_real_escape_string($conn, trim($_POST['email']));
    $message = mysqli_real_escape_string($conn, trim($_POST['message']));

    $query = "INSERT INTO contacts (name, email, message)
              VALUES ('$name', '$email', '$message')";

    if (mysqli_query($conn, $query)) {
        $submitted = true;
    } else {
        die("Database error: " . mysqli_error($conn));
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us — Zenith</title>
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
        .page-header .breadcrumb {
            font-size: .8rem; color: var(--muted); margin-bottom: 10px;
        }
        .page-header .breadcrumb a { color: var(--accent); text-decoration: none; }
        .page-header .breadcrumb a:hover { text-decoration: underline; }
        .page-header h1 {
            font-family: 'Playfair Display', serif;
            font-size: 2rem; font-weight: 700;
        }

        /* ── MAIN LAYOUT ── */
        .contact-wrapper {
            max-width: 1100px; margin: 0 auto;
            padding: 56px 40px 80px;
            display: grid;
            grid-template-columns: 1fr 1.6fr;
            gap: 48px;
            align-items: start;
        }

        /* ── LEFT: CONTACT INFO ── */
        .contact-info { display: flex; flex-direction: column; gap: 20px; }
        .contact-info h2 {
            font-family: 'Playfair Display', serif;
            font-size: 1.5rem; font-weight: 700;
            margin-bottom: 4px;
        }
        .contact-info .subtitle {
            font-size: .93rem; color: var(--muted);
            line-height: 1.7; margin-bottom: 8px;
        }
        .info-card {
            background: var(--white);
            border: 1px solid var(--warm);
            border-radius: var(--radius);
            padding: 22px 24px;
            display: flex; gap: 16px; align-items: flex-start;
            transition: box-shadow .2s, transform .2s;
        }
        .info-card:hover { box-shadow: var(--shadow); transform: translateY(-2px); }
        .info-icon {
            font-size: 1.5rem;
            background: var(--accent-lt);
            border-radius: 10px;
            width: 44px; height: 44px;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }
        .info-body {}
        .info-body h3 {
            font-size: .85rem; font-weight: 500;
            letter-spacing: 1px; text-transform: uppercase;
            color: var(--accent); margin-bottom: 5px;
        }
        .info-body p {
            font-size: .93rem; color: var(--ink);
            line-height: 1.6; font-weight: 500;
        }
        .info-body span {
            font-size: .8rem; color: var(--muted);
            display: block; margin-top: 3px;
        }
        .info-body a { color: var(--ink); text-decoration: none; }
        .info-body a:hover { color: var(--accent); }

        /* ── RIGHT: FORM ── */
        .contact-form-box {
            background: var(--white);
            border: 1px solid var(--warm);
            border-radius: var(--radius);
            padding: 40px 36px;
            box-shadow: var(--shadow);
        }
        .contact-form-box h2 {
            font-family: 'Playfair Display', serif;
            font-size: 1.4rem; font-weight: 700;
            margin-bottom: 28px;
        }
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }
        .form-group {
            display: flex; flex-direction: column;
            gap: 6px; margin-bottom: 18px;
        }
        .form-group label {
            font-size: .82rem; font-weight: 500;
            color: var(--muted); letter-spacing: .3px;
        }
        .form-group input,
        .form-group select,
        .form-group textarea {
            font-family: 'DM Sans', sans-serif;
            font-size: .93rem;
            padding: 11px 14px;
            border: 1.5px solid var(--warm);
            border-radius: 8px;
            background: var(--cream);
            color: var(--ink);
            outline: none;
            transition: border-color .18s, background .18s;
            width: 100%;
        }
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            border-color: var(--accent);
            background: var(--white);
        }
        .form-group textarea { resize: vertical; min-height: 130px; }

        .submit-btn {
            display: inline-flex; align-items: center; gap: 8px;
            padding: 13px 32px;
            background: var(--ink); color: var(--white);
            border: none; border-radius: 8px;
            font-family: 'DM Sans', sans-serif;
            font-size: .93rem; font-weight: 500;
            cursor: pointer; width: 100%;
            justify-content: center;
            transition: background .18s;
        }
        .submit-btn:hover { background: var(--accent); }

        /* ── SUCCESS MESSAGE ── */
        .success-box {
            background: #eef8f0;
            border: 1.5px solid #a8dbb4;
            border-radius: var(--radius);
            padding: 32px;
            text-align: center;
        }
        .success-box .success-icon { font-size: 2.8rem; margin-bottom: 14px; }
        .success-box h3 {
            font-family: 'Playfair Display', serif;
            font-size: 1.3rem; font-weight: 700;
            color: #2a6e3f; margin-bottom: 10px;
        }
        .success-box p { font-size: .93rem; color: #3a6e4a; line-height: 1.6; }
        .success-box a {
            display: inline-block; margin-top: 20px;
            padding: 10px 24px;
            background: var(--accent); color: var(--white);
            border-radius: 8px; text-decoration: none;
            font-size: .88rem; font-weight: 500;
            transition: background .18s;
        }
        .success-box a:hover { background: #b5622c; }

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
            .contact-wrapper { grid-template-columns: 1fr; gap: 32px; padding: 40px 24px 60px; }
            .form-row { grid-template-columns: 1fr; }
        }
        @media (max-width: 600px) {
            .navbar { padding: 0 20px; }
            .page-header { padding: 24px 20px; }
            .contact-form-box { padding: 28px 20px; }
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
    <p class="breadcrumb"><a href="index.php">Home</a> → Contact Us</p>
    <h1>Contact Us</h1>
</div>

<!-- MAIN -->
<div class="contact-wrapper">

    <!-- LEFT: CONTACT INFO -->
    <div class="contact-info">
        <div>
            <h2>Get in Touch</h2>
            <p class="subtitle">Have a question or feedback? We'd love to hear from you. Fill in the form or reach us directly.</p>
        </div>

        <div class="info-card">
            <div class="info-icon">📞</div>
            <div class="info-body">
                <h3>Hotline</h3>
                <p>(+88) 09 678 444 777</p>
                <span>7 days a week, 10:00 am – 8:00 pm (BST)</span>
            </div>
        </div>

        <div class="info-card">
            <div class="info-icon">✉️</div>
            <div class="info-body">
                <h3>Email</h3>
                <p><a href="mailto:customerservice@zenith.net">customerservice@zenith.net</a></p>
                <span>We'll respond as soon as possible</span>
            </div>
        </div>

        <div class="info-card">
            <div class="info-icon">🕐</div>
            <div class="info-body">
                <h3>Shopping Hours</h3>
                <p>10:00 am – 8:00 pm</p>
                <span>Open 7 days a week</span>
            </div>
        </div>
    </div>

    <!-- RIGHT: FORM -->
    <div class="contact-form-box">

        <?php if ($submitted): ?>
        <!-- SUCCESS MESSAGE -->
        <div class="success-box">
            <div class="success-icon">✅</div>
            <h3>Message Sent!</h3>
            <p>Thank you for reaching out. Our team will get back to you within 24 hours during business hours.</p>
            <a href="contact.php">Send Another Message</a>
        </div>

        <?php else: ?>
        <!-- FORM -->
        <h2>Send Us a Message</h2>
        <form method="POST" action="contact.php">
            <div class="form-row">
                <div class="form-group">
                    <label for="name">Full Name</label>
                    <input type="text" id="name" name="name" placeholder="Your name" required>
                </div>
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" placeholder="you@email.com" required>
                </div>
            </div>

            <div class="form-group">
                <label for="message">Message</label>
                <textarea id="message" name="message" placeholder="Write your message here..." required></textarea>
            </div>
            <button type="submit" class="submit-btn">Send Message →</button>
        </form>
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