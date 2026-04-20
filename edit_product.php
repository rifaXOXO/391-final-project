<?php
session_start();
include 'db.php';
// ADMIN CHECK
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}
if (!isset($_GET['id'])) {
    header("Location: manage_products.php");
    exit();
}
$id = (int) $_GET['id'];
// GET PRODUCT
$result = mysqli_query($conn, "SELECT * FROM products WHERE id=$id");
$product = mysqli_fetch_assoc($result);
if (!$product) {
    echo "Product not found";
    exit();
}
// UPDATE PRODUCT
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $price = (float) $_POST['price'];
    $category_id = (int) $_POST['category_id'];
    $subcategory_id = (int) $_POST['subcategory_id'];
    $imagePath = $product['image'];
    if (!empty($_FILES['image']['name'])) {
        if (!empty($imagePath) && file_exists($imagePath)) {
            unlink($imagePath);
        }
        $newImg = time() . "_" . basename($_FILES['image']['name']);
        move_uploaded_file($_FILES['image']['tmp_name'], $newImg);
        $imagePath = $newImg;
    }
    mysqli_query($conn, "
        UPDATE products SET
            name='$name',
            description='$description',
            price=$price,
            image='$imagePath',
            category_id=$category_id,
            subcategory_id=$subcategory_id
        WHERE id=$id
    ");
    header("Location: manage_products.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product — Zenith Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;700&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --ink:        #1a1a18;
            --cream:      #f7f4ef;
            --warm:       #ede8df;
            --accent:     #c8753a;
            --accent-lt:  #f0e0d0;
            --muted:      #7a7671;
            --white:      #ffffff;
            --radius:     12px;
            --shadow:     0 8px 30px rgba(0,0,0,0.05);
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
            padding: 0 40px; height: 72px;
            background: var(--white);
            border-bottom: 1px solid var(--warm);
        }
        .navbar .logo { display: flex; align-items: center; gap: 12px; text-decoration: none; }
        .navbar .logo img { width: 40px; height: 40px; border-radius: 8px; object-fit: contain; }
        .navbar .store-name {
            font-family: 'Playfair Display', serif;
            font-size: 1.4rem; font-weight: 700;
            color: var(--ink);
        }
        .nav-btn {
            display: flex; align-items: center; gap: 8px;
            padding: 10px 18px; border-radius: 8px;
            font-size: .85rem; font-weight: 500;
            text-decoration: none;
            background: var(--warm); color: var(--muted);
            transition: 0.2s;
        }
        .nav-btn:hover { background: #e2ddd3; color: var(--ink); }

        /* ── PAGE HEADER ── */
        .page-header {
            background: var(--white);
            border-bottom: 1px solid var(--warm);
            padding: 40px;
            display: flex; align-items: center; justify-content: space-between;
        }
        .breadcrumb { font-size: .8rem; color: var(--muted); margin-bottom: 8px; }
        .breadcrumb a { color: var(--accent); text-decoration: none; }
        .page-header h1 { font-family: 'Playfair Display', serif; font-size: 2.2rem; font-weight: 700; }

        /* ── SECTION ── */
        .section {
            max-width: 1400px;
            margin: 40px auto;
            padding: 0 40px 100px;
        }

        /* ── FORM CARD ── */
        .form-card {
            background: var(--white);
            border-radius: var(--radius);
            border: 1px solid var(--warm);
            box-shadow: var(--shadow);
            padding: 40px;
            max-width: 680px;
        }

        .field { margin-bottom: 22px; }

        label {
            display: block;
            font-size: .75rem;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: .8px;
            color: var(--muted);
            margin-bottom: 8px;
        }

        input[type="text"],
        input[type="number"],
        input[type="file"],
        textarea {
            width: 100%;
            padding: 13px 16px;
            background: var(--cream);
            border: 1.5px solid var(--warm);
            border-radius: 8px;
            font-family: 'DM Sans', sans-serif;
            font-size: .95rem;
            color: var(--ink);
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        input[type="text"]:focus,
        input[type="number"]:focus,
        textarea:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 3px var(--accent-lt);
            background: var(--white);
        }

        textarea { resize: vertical; min-height: 120px; line-height: 1.6; }
        input[type="file"] { cursor: pointer; font-size: .85rem; color: var(--muted); }

        .current-img {
            display: block;
            width: 100px; height: 100px;
            object-fit: cover;
            border-radius: 8px;
            border: 1px solid var(--warm);
            margin-bottom: 12px;
        }

        .btn-save {
            display: inline-flex; align-items: center; gap: 10px;
            padding: 14px 28px;
            background: var(--ink); color: var(--white);
            border: none; border-radius: 8px;
            font-family: 'DM Sans', sans-serif;
            font-size: .9rem; font-weight: 500;
            cursor: pointer;
            transition: 0.2s;
        }
        .btn-save:hover { background: var(--accent); transform: translateY(-2px); }

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
        .footer-bottom {
            font-size: .75rem;
            border-top: 1px solid rgba(255,255,255,.1);
            padding-top: 20px;
        }

        @media (max-width: 900px) {
            .navbar { padding: 0 20px; }
            .page-header { padding: 24px 20px; }
            .section { padding: 0 20px 60px; margin: 24px auto; }
            .footer-inner { padding: 32px 20px 20px; }
        }
    </style>
</head>
<body>

<nav class="navbar">
    <a class="logo" href="index.php">
        <img src="logo.png" alt="Zenith Logo">
        <span class="store-name">Zenith</span>
    </a>
    <div style="display:flex; align-items:center; gap:12px;">
        <span style="font-size:.72rem; font-weight:500; background:var(--accent-lt); color:var(--accent); padding:3px 10px; border-radius:20px;">Admin</span>
        <a href="manage_products.php" class="nav-btn">← Products</a>
    </div>
</nav>

<div class="page-header">
    <div>
        <p class="breadcrumb"><a href="admin_dashboard.php">Dashboard</a> / <a href="manage_products.php">Manage Products</a> / Edit</p>
        <h1>Edit Product</h1>
    </div>
</div>

<div class="section">
    <div class="form-card">
        <form method="POST" enctype="multipart/form-data">

            <div class="field">
                <label>Product Name</label>
                <input type="text" name="name"
                       value="<?php echo htmlspecialchars($product['name']); ?>"
                       required>
            </div>

            <div class="field">
                <label>Description</label>
                <textarea name="description"><?php echo htmlspecialchars($product['description']); ?></textarea>
            </div>

            <div class="field">
                <label>Price (Tk)</label>
                <input type="number" step="0.01" name="price"
                       value="<?php echo $product['price']; ?>" required>
            </div>

            <div class="field">
                <label>Category ID</label>
                <input type="number" name="category_id"
                       value="<?php echo $product['category_id']; ?>" required>
            </div>

            <div class="field">
                <label>Subcategory ID</label>
                <input type="number" name="subcategory_id"
                       value="<?php echo $product['subcategory_id']; ?>">
            </div>

            <div class="field">
                <label>Product Image</label>
                <?php if (!empty($product['image']) && file_exists($product['image'])): ?>
                    <img class="current-img" src="<?php echo htmlspecialchars($product['image']); ?>" alt="Current Image">
                <?php endif; ?>
                <input type="file" name="image" accept="image/*">
            </div>

            <button type="submit" class="btn-save">✔ Update Product</button>

        </form>
    </div>
</div>

<footer>
    <div class="footer-inner">
        <div class="footer-top">
            <span class="footer-brand">Zenith</span>
        </div>
        <p class="footer-bottom">© <?php echo date('Y'); ?> Zenith. All rights reserved.</p>
    </div>
</footer>

</body>
</html>