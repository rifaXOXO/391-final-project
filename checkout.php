<?php
session_start();
include 'db.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
$cart = $_SESSION['cart'] ?? [];
if (empty($cart)) {
    header("Location: cart.php");
    exit();
}
$total = 0;
foreach ($cart as $item) {
    $total += $item['price'] * $item['qty'];
}
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name    = mysqli_real_escape_string($conn, $_POST['name']);
    $phone   = mysqli_real_escape_string($conn, $_POST['phone']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    $user    = $_SESSION['user_id'];
    mysqli_query($conn, "
        INSERT INTO orders (user, name, phone, address, total)
        VALUES ('$user', '$name', '$phone', '$address', '$total')
    ");
    unset($_SESSION['cart']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Placed — Zenith</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;700&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        :root {
            --ink: #1a1a18; --cream: #f7f4ef; --warm: #ede8df;
            --accent: #c8753a; --accent-lt: #f0e0d0; --muted: #7a7671; --white: #ffffff;
        }
        body { font-family: 'DM Sans', sans-serif; background: var(--cream); color: var(--ink); min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .success-box {
            background: var(--white);
            border: 1px solid var(--warm);
            border-radius: 16px;
            padding: 60px 48px;
            text-align: center;
            max-width: 460px;
            width: 100%;
        }
        .success-icon { font-size: 3rem; margin-bottom: 20px; }
        .success-box h1 {
            font-family: 'Playfair Display', serif;
            font-size: 1.7rem; font-weight: 700;
            margin-bottom: 12px;
        }
        .success-box p { font-size: .9rem; color: var(--muted); margin-bottom: 32px; line-height: 1.6; }
        .success-box a {
            display: inline-block;
            padding: 12px 28px;
            background: var(--ink);
            color: var(--white);
            border-radius: 8px;
            text-decoration: none;
            font-size: .9rem; font-weight: 500;
            transition: background .18s;
        }
        .success-box a:hover { background: var(--accent); }
    </style>
</head>
<body>
    <div class="success-box">
        <div class="success-icon">🎉</div>
        <h1>Order Placed!</h1>
        <p>Thank you, <?php echo htmlspecialchars($name); ?>. Your order has been received and is being processed.</p>
        <a href="index.php">← Back to Home</a>
    </div>
</body>
</html>
<?php
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout — Zenith</title>
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
            background: var(--warm); color: var(--muted);
            transition: background .18s, color .18s;
        }
        .nav-btn:hover { background: #e0dbd2; color: var(--ink); }

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

        /* ── LAYOUT ── */
        .section {
            max-width: 960px;
            margin: 48px auto;
            padding: 0 40px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
            align-items: start;
        }

        /* ── BOXES ── */
        .box {
            background: var(--white);
            border: 1px solid var(--warm);
            border-radius: var(--radius);
            padding: 28px;
        }
        .box h2 {
            font-family: 'Playfair Display', serif;
            font-size: 1.25rem; font-weight: 700;
            margin-bottom: 20px;
        }

        /* ── FORM ── */
        .form-group { margin-bottom: 14px; }
        .form-group label {
            display: block;
            font-size: .78rem; font-weight: 500;
            color: var(--muted);
            margin-bottom: 6px;
            letter-spacing: .3px;
            text-transform: uppercase;
        }
        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 10px 14px;
            border: 1px solid var(--warm);
            border-radius: 8px;
            font-family: 'DM Sans', sans-serif;
            font-size: .9rem;
            color: var(--ink);
            background: var(--cream);
            transition: border-color .18s;
            outline: none;
        }
        .form-group input:focus,
        .form-group textarea:focus {
            border-color: var(--accent);
            background: var(--white);
        }
        .form-group textarea { resize: vertical; min-height: 90px; }
        .place-btn {
            display: block; width: 100%;
            margin-top: 8px;
            padding: 13px;
            background: var(--ink);
            color: var(--white);
            border: none; border-radius: 8px;
            font-family: 'DM Sans', sans-serif;
            font-size: .95rem; font-weight: 500;
            cursor: pointer;
            transition: background .18s;
        }
        .place-btn:hover { background: var(--accent); }

        /* ── ORDER SUMMARY ── */
        .order-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            padding: 12px 0;
            border-bottom: 1px solid var(--warm);
        }
        .order-item:last-of-type { border-bottom: none; }
        .order-item-name {
            font-size: .88rem; font-weight: 500;
            color: var(--ink);
        }
        .order-item-qty {
            font-size: .75rem;
            color: var(--muted);
            background: var(--warm);
            padding: 2px 8px;
            border-radius: 20px;
            margin-top: 3px;
            display: inline-block;
        }
        .order-item-price {
            font-weight: 700;
            color: var(--accent);
            white-space: nowrap;
            font-size: .9rem;
        }
        .order-total {
            display: flex;
            justify-content: space-between;
            align-items: baseline;
            margin-top: 20px;
            padding-top: 16px;
            border-top: 2px solid var(--warm);
        }
        .order-total span:first-child {
            font-size: .85rem; color: var(--muted); font-weight: 500;
        }
        .order-total span:last-child {
            font-family: 'Playfair Display', serif;
            font-size: 1.4rem; font-weight: 700;
            color: var(--ink);
        }

        /* ── RESPONSIVE ── */
        @media (max-width: 700px) {
            .section { grid-template-columns: 1fr; padding: 0 20px; margin: 32px auto; }
            .navbar { padding: 0 20px; }
            .page-header { padding: 24px 20px; }
        }

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
    </style>
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar">
    <a class="logo" href="index.php">
        <img src="logo.png" alt="Logo">
        <span class="store-name">Zenith</span>
    </a>
    <a href="cart.php" class="nav-btn">← Back to Cart</a>
</nav>

<!-- PAGE HEADER -->
<div class="page-header">
    <p class="breadcrumb">
        <a href="index.php">Home</a> →
        <a href="cart.php">Cart</a> →
        Checkout
    </p>
    <h1>Checkout</h1>
</div>

<!-- CONTENT -->
<div class="section">

    <!-- SHIPPING FORM -->
    <div class="box">
        <h2>Shipping Info</h2>
        <form method="POST">
            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="name" placeholder="e.g. Rahim Uddin" required>
            </div>
            <div class="form-group">
                <label>Phone Number</label>
                <input type="text" name="phone" placeholder="e.g. 01700000000" required>
            </div>
            <div class="form-group">
                <label>Delivery Address</label>
                <textarea name="address" placeholder="House, Road, Area, City" required></textarea>
            </div>
            <button type="submit" class="place-btn">Place Order →</button>
        </form>
    </div>

    <!-- ORDER SUMMARY -->
    <div class="box">
        <h2>Order Summary</h2>
        <?php foreach ($cart as $item): ?>
        <div class="order-item">
            <div>
                <div class="order-item-name"><?php echo htmlspecialchars($item['name']); ?></div>
                <span class="order-item-qty">x<?php echo $item['qty']; ?></span>
            </div>
            <div class="order-item-price"><?php echo number_format($item['price'] * $item['qty']); ?> Tk</div>
        </div>
        <?php endforeach; ?>
        <div class="order-total">
            <span>Total</span>
            <span><?php echo number_format($total); ?> Tk</span>
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