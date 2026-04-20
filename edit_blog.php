<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: manage_blog.php");
    exit();
}

$id = intval($_GET['id']);

$result = mysqli_query($conn, "SELECT * FROM blog_posts WHERE id = $id");
$blog   = mysqli_fetch_assoc($result);

if (!$blog) {
    header("Location: manage_blog.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title     = mysqli_real_escape_string($conn, $_POST['title']);
    $content   = mysqli_real_escape_string($conn, $_POST['content']);
    $imagePath = $blog['image'];

    if (!empty($_FILES['image']['name'])) {
        $image     = $_FILES['image']['name'];
        $tmp       = $_FILES['image']['tmp_name'];
        $imagePath = time() . "_" . basename($image);
        move_uploaded_file($tmp, $imagePath);
    }

    $query = "UPDATE blog_posts SET title='$title', content='$content', image='$imagePath' WHERE id=$id";

    if (mysqli_query($conn, $query)) {
        header("Location: manage_blog.php");
        exit();
    } else {
        $error = "Error: " . mysqli_error($conn);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Blog Post — Zenith Admin</title>
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

        /* ── ERROR ── */
        .error-box {
            background: #fdecea;
            border: 1px solid #f5c0bb;
            border-radius: 8px;
            padding: 10px 14px;
            font-size: .85rem;
            color: #a33;
            margin-bottom: 20px;
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
        .form-group textarea {
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
        }
        .form-group input[type="text"]:focus,
        .form-group textarea:focus {
            border-color: var(--accent);
            background: var(--white);
        }
        .form-group textarea {
            resize: vertical;
            min-height: 220px;
            line-height: 1.7;
        }

        /* ── CURRENT IMAGE ── */
        .current-image-box {
            display: flex;
            align-items: center;
            gap: 16px;
            padding: 14px;
            background: var(--cream);
            border: 1px solid var(--warm);
            border-radius: 8px;
            margin-bottom: 12px;
        }
        .current-image-box img {
            width: 80px; height: 60px;
            object-fit: cover;
            border-radius: 6px;
            border: 1px solid var(--warm);
            display: block;
        }
        .current-image-box span {
            font-size: .82rem;
            color: var(--muted);
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
        .save-btn {
            display: inline-flex; align-items: center; gap: 8px;
            padding: 12px 32px;
            background: var(--ink); color: var(--white);
            border: none; border-radius: 8px;
            font-family: 'DM Sans', sans-serif;
            font-size: .95rem; font-weight: 500;
            cursor: pointer;
            transition: background .18s;
        }
        .save-btn:hover { background: var(--accent); }
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
    <div style="display:flex; align-items:center; gap:12px;">
        <span class="admin-badge">Admin</span>
        <a href="manage_blog.php" class="nav-btn">← Manage Blogs</a>
    </div>
</nav>

<!-- PAGE HEADER -->
<div class="page-header">
    <p class="breadcrumb">
        <a href="admin_dashboard.php">Dashboard</a> →
        <a href="manage_blog.php">Manage Blogs</a> →
        Edit Post
    </p>
    <h1>Edit Blog Post</h1>
</div>

<!-- FORM -->
<div class="section">

    <?php if (!empty($error)): ?>
        <div class="error-box">⚠️ <?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <div class="form-box">
        <form method="POST" enctype="multipart/form-data">

            <div class="form-group">
                <label>Post Title</label>
                <input type="text" name="title" value="<?php echo htmlspecialchars($blog['title']); ?>" required>
            </div>

            <div class="form-group">
                <label>Content</label>
                <textarea name="content" required><?php echo htmlspecialchars($blog['content']); ?></textarea>
            </div>

            <div class="form-group">
                <label>Current Image</label>
                <?php if (!empty($blog['image'])): ?>
                <div class="current-image-box">
                    <img src="<?php echo htmlspecialchars($blog['image']); ?>" alt="Current image">
                    <span>This image will be kept unless you upload a new one.</span>
                </div>
                <?php else: ?>
                <div class="current-image-box">
                    <span>No image currently set.</span>
                </div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label>Replace Image</label>
                <label class="file-upload-label" for="image-input">
                    <span class="upload-icon">🖼️</span>
                    <span id="file-label">Choose a new image to replace…</span>
                </label>
                <input type="file" name="image" id="image-input" accept="image/*"
                    onchange="document.getElementById('file-label').textContent = this.files[0]?.name || 'Choose a new image to replace…'">
            </div>

            <div class="form-actions">
                <a href="manage_blog.php" class="cancel-link">Cancel</a>
                <button type="submit" class="save-btn">Save Changes →</button>
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