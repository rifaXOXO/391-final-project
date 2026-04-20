<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name           = mysqli_real_escape_string($conn, $_POST['name']);
    $description    = mysqli_real_escape_string($conn, $_POST['description']);
    $price          = (float) $_POST['price'];
    $category_id    = (int) $_POST['category_id'];
    $subcategory_id = (int) $_POST['subcategory_id'];
    $imagePath      = "";

    if (!empty($_FILES['image']['name'])) {
        $imgName   = time() . "_" . basename($_FILES['image']['name']);
        move_uploaded_file($_FILES['image']['tmp_name'], $imgName);
        $imagePath = $imgName;
    }

    mysqli_query($conn, "
        INSERT INTO products (name, description, price, image, category_id, subcategory_id)
        VALUES ('$name', '$description', $price, '$imagePath', $category_id, $subcategory_id)
    ");

    header("Location: manage_products.php");
    exit();
}

// fetch categories and subcategories for dropdowns
$categories    = mysqli_query($conn, "SELECT * FROM categories ORDER BY name ASC");
$subcategories = mysqli_query($conn, "SELECT * FROM subcategories ORDER BY name ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product — Zenith Admin</title>
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
        .admin-badge {
            font-size: .72rem; font-weight: 500;
            background: var(--accent-lt); color: var(--accent);
            padding: 3px 10px; border-radius: 20px; letter-spacing: .3px;
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

        /* ── SECTION ── */
        .section {
            max-width: 720px;
            margin: 48px auto;
            padding: 0 40px 80px;
        }

        /* ── FORM BOX ── */
        .form-box {
            background: var(--white);
            border: 1px solid var(--warm);
            border-radius: var(--radius);
            padding: 32px 36px;
        }

        .form-group { margin-bottom: 20px; }
        .form-group label {
            display: block;
            font-size: .78rem; font-weight: 500;
            color: var(--muted);
            margin-bottom: 7px;
            letter-spacing: .3px;
            text-transform: uppercase;
        }
        .form-group input[type="text"],
        .form-group input[type="number"],
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 11px 14px;
            border: 1px solid var(--warm);
            border-radius: 8px;
            font-family: 'DM Sans', sans-serif;
            font-size: .92rem;
            color: var(--ink);
            background: var(--cream);
            transition: border-color .18s, background .18s;
            outline: none;
            appearance: none;
        }
        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            border-color: var(--accent);
            background: var(--white);
        }
        .form-group textarea {
            resize: vertical;
            min-height: 120px;
            line-height: 1.7;
        }

        /* ── TWO COLUMN ── */
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }

        /* ── FILE UPLOAD ── */
        .file-upload-label {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            border: 1px dashed #c8b8a8;
            border-radius: 8px;
            background: var(--cream);
            cursor: pointer;
            transition: border-color .18s, background .18s;
        }
        .file-upload-label:hover {
            border-color: var(--accent);
            background: var(--accent-lt);
        }
        .file-upload-label span { font-size: .88rem; color: var(--muted); }
        .upload-icon { font-size: 1.2rem; }
        input[type="file"] { display: none; }

        /* ── ACTIONS ── */
        .form-actions {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-top: 8px;
        }
        .submit-btn {
            display: inline-flex; align-items: center; gap: 8px;
            padding: 12px 32px;
            background: var(--ink); color: var(--white);
            border: none; border-radius: 8px;
            font-family: 'DM Sans', sans-serif;
            font-size: .95rem; font-weight: 500;
            cursor: pointer;
            transition: background .18s;
        }
        .submit-btn:hover { background: var(--accent); }
        .cancel-link {
            font-size: .85rem; color: var(--muted);
            text-decoration: none;
            transition: color .18s;
        }
        .cancel-link:hover { color: var(--ink); }

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
            .form-box { padding: 24px 20px; }
            .form-row { grid-template-columns: 1fr; }
            .footer-inner { padding: 32px 20px 20px; }
        }
    </style>

    <script>
        // filter subcategories based on selected category
        function filterSubcategories() {
            const catId   = document.getElementById('category_id').value;
            const subSel  = document.getElementById('subcategory_id');
            const options = subSel.querySelectorAll('option');
            options.forEach(opt => {
                if (opt.value === '') return;
                opt.style.display = (opt.dataset.cat === catId || catId === '') ? '' : 'none';
            });
            subSel.value = '';
        }
    </script>
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar">
    <a class="logo" href="index.php">
        <img src="logo.png" alt="Logo">
        <span class="store-name">Zenith</span>
    </a>
    <div style="display:flex; align-items:center; gap:12px;">
        <span class="admin-badge">Admin</span>
        <a href="manage_products.php" class="nav-btn">← Products</a>
    </div>
</nav>

<!-- PAGE HEADER -->
<div class="page-header">
    <p class="breadcrumb">
        <a href="index.php">Home</a> →
        <a href="admin_dashboard.php">Dashboard</a> →
        <a href="manage_products.php">Manage Products</a> →
        Add Product
    </p>
    <h1>Add Product</h1>
</div>

<!-- FORM -->
<div class="section">
    <div class="form-box">
        <form method="POST" enctype="multipart/form-data">

            <div class="form-group">
                <label>Product Name</label>
                <input type="text" name="name" placeholder="Enter product name" required>
            </div>

            <div class="form-group">
                <label>Description</label>
                <textarea name="description" placeholder="Describe the product…"></textarea>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Price (Tk)</label>
                    <input type="number" step="0.01" name="price" placeholder="0.00" required>
                </div>
                <div class="form-group">
                    <label>Category</label>
                    <select name="category_id" id="category_id" onchange="filterSubcategories()" required>
                        <option value="">Select category…</option>
                        <?php
                        mysqli_data_seek($categories, 0);
                        while ($cat = mysqli_fetch_assoc($categories)): ?>
                        <option value="<?php echo $cat['id']; ?>">
                            <?php echo htmlspecialchars($cat['name']); ?>
                        </option>
                        <?php endwhile; ?>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label>Subcategory</label>
                <select name="subcategory_id" id="subcategory_id" required>
                    <option value="">Select subcategory…</option>
                    <?php while ($sub = mysqli_fetch_assoc($subcategories)): ?>
                    <option value="<?php echo $sub['id']; ?>"
                            data-cat="<?php echo $sub['category_id']; ?>">
                        <?php echo htmlspecialchars($sub['name']); ?>
                    </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Product Image</label>
                <label class="file-upload-label" for="image-input">
                    <span class="upload-icon">🖼️</span>
                    <span id="file-label">Choose an image to upload…</span>
                </label>
                <input type="file" name="image" id="image-input" accept="image/*"
                    onchange="document.getElementById('file-label').textContent = this.files[0]?.name || 'Choose an image to upload…'">
            </div>

            <div class="form-actions">
                <a href="manage_products.php" class="cancel-link">Cancel</a>
                <button type="submit" class="submit-btn">Add Product →</button>
            </div>

        </form>
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