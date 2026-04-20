<?php
session_start();
include 'db.php';

// 1. ACCESS CONTROL
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// 2. DELETE LOGIC (Mechanism)
if (isset($_GET['delete'])) {
    $id   = (int) $_GET['delete'];
    $imgQ = mysqli_query($conn, "SELECT image FROM products WHERE id=$id");
    $img  = mysqli_fetch_assoc($imgQ);
    if (!empty($img['image']) && file_exists($img['image'])) {
        unlink($img['image']);
    }
    mysqli_query($conn, "DELETE FROM products WHERE id=$id");
    header("Location: manage_products.php");
    exit();
}

// 3. FETCH DATA
$result = mysqli_query($conn, "
    SELECT p.*, c.name AS category_name, s.name AS subcategory_name
    FROM products p
    LEFT JOIN categories c ON c.id = p.category_id
    LEFT JOIN subcategories s ON s.id = p.subcategory_id
    ORDER BY p.id DESC
");
$total_products = mysqli_num_rows($result);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Products — Zenith Admin</title>
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

        .add-btn {
            display: inline-flex; align-items: center; gap: 10px;
            padding: 14px 28px;
            background: var(--ink); color: var(--white);
            border-radius: 8px;
            font-size: .9rem; font-weight: 500;
            text-decoration: none;
            transition: 0.2s;
        }
        .add-btn:hover { background: var(--accent); transform: translateY(-2px); }

        /* ── MAIN SECTION (WIDE STYLE) ── */
        .section {
            max-width: 1400px;
            margin: 40px auto;
            padding: 0 40px 100px;
        }

        .table-card {
            background: var(--white);
            border-radius: var(--radius);
            border: 1px solid var(--warm);
            box-shadow: var(--shadow);
            overflow: hidden;
        }

        table { width: 100%; border-collapse: collapse; }
        thead { background: #faf9f7; border-bottom: 2px solid var(--warm); }
        th {
            padding: 20px;
            font-size: .75rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--muted);
            text-align: left;
        }

        td {
            padding: 24px 20px;
            border-bottom: 1px solid var(--warm);
            vertical-align: middle;
            font-size: .9rem;
        }
        tr:last-child td { border-bottom: none; }
        tr:hover td { background: #fdfdfc; }

        /* ── COLUMN STYLES ── */
        .col-sn { width: 70px; font-weight: 600; color: var(--muted); }
        
        .thumb { 
            width: 64px; height: 64px; 
            object-fit: cover; border-radius: 8px; 
            border: 1px solid var(--warm); 
            display: block;
        }
        .thumb-placeholder { 
            width: 64px; height: 64px; background: var(--warm); 
            display: flex; align-items: center; justify-content: center; 
            border-radius: 8px; font-size: 1.4rem; 
        }

        .product-name { font-weight: 600; color: var(--ink); margin-bottom: 4px; font-size: 1rem; }
        .product-sub { font-size: .8rem; color: var(--muted); line-height: 1.4; max-width: 350px; }
        
        .product-price { 
            font-family: 'Playfair Display', serif; 
            font-weight: 700; font-size: 1.1rem; color: #9D1C06; 
        }

        /* Category Pills */
        .cat-pill {
            display: inline-block;
            padding: 4px 12px;
            background: var(--warm);
            color: var(--muted);
            border-radius: 20px;
            font-size: .72rem;
            font-weight: 600;
            margin-bottom: 4px;
        }

        .btn-delete {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 9px 16px;
            background: #fff0f0;
            color: #b33939;
            border: 1px solid #f7d2d2;
            border-radius: 8px;
            text-decoration: none;
            font-size: .8rem;
            font-weight: 500;
            transition: 0.2s;
        }
        .btn-delete:hover { background: #b33939; color: #fff; }
		.btn-edit {
			margin-right: 8px;
		}
		.btn-edit {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 9px 16px;
            background: #fff0f0;
            color: #b33939;
            border: 1px solid #f7d2d2;
            border-radius: 8px;
            text-decoration: none;
            font-size: .8rem;
            font-weight: 500;
            transition: 0.2s;
        }
        .btn-edit:hover { background: #b33939; color: #fff; }
		.action-buttons {
			display: flex;
			gap: 8px;
			align-items: center;
		}
        .empty-state { text-align: center; padding: 100px; color: var(--muted); }

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
        @media (max-width: 900px) {
            .section { padding: 0 20px 60px; margin: 32px auto; }
            .navbar { padding: 0 20px; }
            .page-header { padding: 24px 20px; }
            th:nth-child(4), td:nth-child(4),
            th:nth-child(2), td:nth-child(2) { display: none; }
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
        <span style="font-size: .72rem; font-weight: 500; background: var(--accent-lt); color: var(--accent); padding: 3px 10px; border-radius: 20px;">Admin</span>
        <a href="admin_dashboard.php" class="nav-btn">← Dashboard</a>
    </div>
</nav>

<div class="page-header">
    <div>
        <p class="breadcrumb"><a href="admin_dashboard.php">Dashboard</a> / Manage Products</p>
        <h1>Product Inventory</h1>
    </div>
    <a href="add_product.php" class="add-btn"><span>+</span> Add New Product</a>
</div>

<div class="section">
    <div class="table-card">
        <?php if ($total_products > 0): ?>
        <table>
            <thead>
                <tr>
                    <th class="col-sn">SL</th>
                    <th>Image</th>
                    <th>Product Details</th>
                    <th>Pricing</th>
                    <th>Category</th>
                    <th style="text-align: right;">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                // Descending Serial Number logic
                $sl = $total_products; 
                while ($row = mysqli_fetch_assoc($result)): 
                ?>
                <tr>
                    <td class="col-sn">#<?php echo $sl--; ?></td>
                    <td>
                        <?php if (!empty($row['image']) && file_exists($row['image'])): ?>
                            <img class="thumb" src="<?php echo htmlspecialchars($row['image']); ?>" alt="Product">
                        <?php else: ?>
                            <div class="thumb-placeholder">🛍️</div>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div class="product-name"><?php echo htmlspecialchars($row['name']); ?></div>
                        <div class="product-sub"><?php echo htmlspecialchars(substr($row['description'], 0, 100)); ?>...</div>
                    </td>
                    <td>
                        <span class="product-price"><?php echo number_format($row['price']); ?> Tk</span>
                    </td>
                    <td>
                        <div><span class="cat-pill"><?php echo htmlspecialchars($row['category_name'] ?? 'General'); ?></span></div>
                        <?php if($row['subcategory_name']): ?>
                            <div><span class="cat-pill" style="background:#f0f0f0;"><?php echo htmlspecialchars($row['subcategory_name']); ?></span></div>
                        <?php endif; ?>
                    </td>
                    <td>
						<div class="action-buttons">
							<a href="edit_product.php?id=<?php echo $row['id']; ?>" class="btn-edit">✏️ Edit</a>

							<a href="manage_products.php?delete=<?php echo $row['id']; ?>" class="btn-delete"
							   onclick="return confirm('Delete this product permanently?')">
							   🗑️ Delete
							</a>
						</div>
					</td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <?php else: ?>
        <div class="empty-state">
            <div style="font-size: 3rem; margin-bottom: 20px;">📦</div>
            <h3>Your inventory is empty</h3>
            <p>Click "Add New Product" to start building your catalog.</p>
        </div>
        <?php endif; ?>
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