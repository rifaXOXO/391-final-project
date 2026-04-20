<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

if (isset($_GET['delete'])) {
    $id  = (int) $_GET['delete'];
    $res = mysqli_query($conn, "SELECT image FROM blog_posts WHERE id=$id");
    $row = mysqli_fetch_assoc($res);
    if ($row && !empty($row['image']) && file_exists($row['image'])) {
        unlink($row['image']);
    }
    mysqli_query($conn, "DELETE FROM blog_posts WHERE id=$id");
    header("Location: manage_blog.php");
    exit();
}

$result = mysqli_query($conn, "SELECT * FROM blog_posts ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Blogs — Zenith Admin</title>
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
            display: flex; align-items: center; justify-content: space-between;
            flex-wrap: wrap; gap: 16px;
        }
        .page-header-left .breadcrumb {
            font-size: .8rem; color: var(--muted); margin-bottom: 10px;
        }
        .page-header-left .breadcrumb a { color: var(--accent); text-decoration: none; }
        .page-header-left .breadcrumb a:hover { text-decoration: underline; }
        .page-header-left h1 {
            font-family: 'Playfair Display', serif;
            font-size: 2rem; font-weight: 700;
        }
        .add-btn {
            display: inline-flex; align-items: center; gap: 8px;
            padding: 11px 22px;
            background: var(--ink); color: var(--white);
            border-radius: 8px;
            font-size: .88rem; font-weight: 500;
            text-decoration: none;
            transition: background .18s;
            white-space: nowrap;
        }
        .add-btn:hover { background: var(--accent); }

        /* ── SECTION ── */
        .section {
            max-width: 1100px;
            margin: 48px auto;
            padding: 0 40px 80px;
        }

        /* ── TABLE CARD ── */
        .table-card {
            background: var(--white);
            border: 1px solid var(--warm);
            border-radius: var(--radius);
            overflow: hidden;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        thead {
            background: var(--cream);
            border-bottom: 1px solid var(--warm);
        }
        th {
            padding: 13px 18px;
            font-size: .75rem; font-weight: 500;
            text-transform: uppercase; letter-spacing: .5px;
            color: var(--muted);
            text-align: left;
        }
        td {
            padding: 16px 18px;
            border-bottom: 1px solid var(--warm);
            font-size: .88rem;
            vertical-align: middle;
        }
        tr:last-child td { border-bottom: none; }
        tr:hover td { background: #faf8f5; }

        /* ── THUMB ── */
        .thumb {
            width: 72px; height: 54px;
            border-radius: 7px;
            object-fit: cover;
            display: block;
            border: 1px solid var(--warm);
        }
        .thumb-placeholder {
            width: 72px; height: 54px;
            border-radius: 7px;
            background: var(--accent-lt);
            display: flex; align-items: center; justify-content: center;
            font-size: 1.3rem;
            border: 1px solid var(--warm);
        }

        /* ── POST INFO ── */
        .post-title {
            font-weight: 500; color: var(--ink);
            margin-bottom: 4px;
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
            max-width: 420px;
        }
        .post-excerpt {
            font-size: .78rem; color: var(--muted);
            margin-bottom: 6px;
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
            max-width: 420px;
        }
        .post-date {
            display: inline-block;
            font-size: .72rem; font-weight: 500;
            color: var(--accent);
            background: var(--accent-lt);
            padding: 2px 8px; border-radius: 20px;
        }

        /* ── ACTION BUTTONS ── */
        .actions { display: flex; gap: 8px; align-items: center; }
        .btn-edit, .btn-delete {
            display: inline-flex; align-items: center; gap: 5px;
            padding: 7px 14px;
            border-radius: 7px;
            font-size: .78rem; font-weight: 500;
            text-decoration: none;
            transition: background .18s, color .18s;
            white-space: nowrap;
        }
        .btn-edit {
            background: var(--warm); color: var(--ink);
        }
        .btn-edit:hover { background: #ddd8cf; }
        .btn-delete {
            background: #fdecea; color: #a33;
            border: 1px solid #f5c0bb;
        }
        .btn-delete:hover { background: #f5c0bb; }

        /* ── EMPTY STATE ── */
        .empty-state {
            text-align: center; padding: 60px 20px;
            color: var(--muted);
        }
        .empty-state .icon { font-size: 2.5rem; margin-bottom: 12px; }
        .empty-state p { margin-bottom: 20px; font-size: .9rem; }

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
        @media (max-width: 700px) {
            .page-header { padding: 24px 20px; }
            .section { padding: 0 20px 60px; margin: 32px auto; }
            .navbar { padding: 0 20px; }
            th:nth-child(1), td:nth-child(1) { display: none; }
            .post-title, .post-excerpt { max-width: 200px; }
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
        <a href="admin_dashboard.php" class="nav-btn">← Dashboard</a>
    </div>
</nav>

<!-- PAGE HEADER -->
<div class="page-header">
    <div class="page-header-left">
        <p class="breadcrumb">
   
            <a href="admin_dashboard.php">Dashboard</a> →
            Manage Blogs
        </p>
        <h1>Manage Blog Posts</h1>
    </div>
    <a href="add_blog.php" class="add-btn">➕ Add New Post</a>
</div>

<!-- TABLE -->
<div class="section">
    <div class="table-card">
        <?php if (mysqli_num_rows($result) > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Image</th>
                    <th>Post</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td>
                        <?php if (!empty($row['image'])): ?>
                            <img class="thumb" src="<?php echo htmlspecialchars($row['image']); ?>" alt="">
                        <?php else: ?>
                            <div class="thumb-placeholder">📰</div>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div class="post-title"><?php echo htmlspecialchars($row['title']); ?></div>
                        <div class="post-excerpt"><?php echo htmlspecialchars(substr($row['content'], 0, 80)) . '...'; ?></div>
                        <span class="post-date"><?php echo date("d M Y", strtotime($row['created_at'])); ?></span>
                    </td>
                    <td>
                        <div class="actions">
                            <a href="edit_blog.php?id=<?php echo $row['id']; ?>" class="btn-edit">✏️ Edit</a>
                            <a href="manage_blog.php?delete=<?php echo $row['id']; ?>"
                               class="btn-delete"
                               onclick="return confirm('Are you sure you want to delete this post?');">
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
            <div class="icon">📰</div>
            <p>No blog posts yet.</p>
            <a href="add_blog.php" class="add-btn">➕ Add First Post</a>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- FOOTER -->
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