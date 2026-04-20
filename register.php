<?php
session_start();
include 'db.php';

if (isset($_SESSION['user'])) {
    header("Location: home.php");
    exit();
}

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = mysqli_real_escape_string($conn, trim($_POST['name']));
    $email    = mysqli_real_escape_string($conn, trim($_POST['email']));
    $password = trim($_POST['password']);
    $confirm  = trim($_POST['confirm_password']);

    // Validation
    if (empty($name) || empty($email) || empty($password)) {
        $error = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters.";
    } elseif ($password !== $confirm) {
        $error = "Passwords do not match.";
    } else {
        // Check if email already exists
        $check = mysqli_query($conn, "SELECT id FROM users WHERE email = '$email'");
        if (mysqli_num_rows($check) > 0) {
            $error = "An account with this email already exists.";
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            mysqli_query($conn, "INSERT INTO users (name, email, password) VALUES ('$name', '$email', '$hashed')");
            header("Location: index.php?registered=1");
            exit();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up — My Store</title>
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

        .auth-wrapper {
            display: grid;
            grid-template-columns: 1fr 1fr;
            min-height: 100vh;
        }

        /* ── LEFT PANEL ── */
        .auth-left {
            background: var(--ink);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 48px;
            position: relative;
            overflow: hidden;
        }
        .auth-left::before {
            content: '';
            position: absolute;
            width: 400px; height: 400px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(200,117,58,.3) 0%, transparent 70%);
            top: -80px; right: -80px;
        }
        .auth-left::after {
            content: '';
            position: absolute;
            width: 300px; height: 300px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(200,117,58,.2) 0%, transparent 70%);
            bottom: -60px; left: -60px;
        }
        .auth-brand {
            display: flex; align-items: center; gap: 12px;
            text-decoration: none; position: relative; z-index: 1;
        }
        .auth-brand img { width: 44px; height: 44px; border-radius: 10px; object-fit: contain; }
        .auth-brand span {
            font-family: 'Playfair Display', serif;
            font-size: 1.4rem; font-weight: 700; color: var(--white);
        }
        .auth-left-content { position: relative; z-index: 1; }
        .auth-left-content h2 {
            font-family: 'Playfair Display', serif;
            font-size: clamp(2rem, 3vw, 2.8rem);
            color: var(--white); line-height: 1.2; margin-bottom: 20px;
        }
        .auth-left-content h2 em { font-style: italic; color: var(--accent-lt); }
        .auth-left-content p {
            font-size: .95rem; color: rgba(255,255,255,.6);
            line-height: 1.7; max-width: 340px;
        }
        .auth-left-footer {
            font-size: .78rem; color: rgba(255,255,255,.3);
            position: relative; z-index: 1;
        }

        /* ── RIGHT PANEL ── */
        .auth-right {
            display: flex; align-items: center; justify-content: center;
            padding: 48px 40px;
            background: var(--cream);
            overflow-y: auto;
        }
        .auth-box { width: 100%; max-width: 400px; }
        .auth-box h1 {
            font-family: 'Playfair Display', serif;
            font-size: 1.8rem; font-weight: 700; margin-bottom: 8px;
        }
        .auth-box .sub {
            font-size: .9rem; color: var(--muted); margin-bottom: 32px;
        }
        .auth-box .sub a { color: var(--accent); text-decoration: none; font-weight: 500; }
        .auth-box .sub a:hover { text-decoration: underline; }

        /* ── ERROR ── */
        .error-msg {
            background: #fdf0ee;
            border: 1.5px solid #e8b4a8;
            border-radius: 8px;
            padding: 12px 16px;
            font-size: .87rem; color: #a63a20;
            margin-bottom: 20px;
        }

        /* ── FORM ── */
        .form-group {
            display: flex; flex-direction: column;
            gap: 6px; margin-bottom: 18px;
        }
        .form-group label {
            font-size: .82rem; font-weight: 500;
            color: var(--muted); letter-spacing: .3px;
        }
        .form-group input {
            font-family: 'DM Sans', sans-serif;
            font-size: .93rem;
            padding: 12px 14px;
            border: 1.5px solid var(--warm);
            border-radius: 8px;
            background: var(--white);
            color: var(--ink);
            outline: none;
            transition: border-color .18s;
            width: 100%;
        }
        .form-group input:focus { border-color: var(--accent); }

        .hint {
            font-size: .76rem; color: var(--muted);
            margin-top: 2px;
        }

        .submit-btn {
            width: 100%; padding: 13px;
            background: var(--ink); color: var(--white);
            border: none; border-radius: 8px;
            font-family: 'DM Sans', sans-serif;
            font-size: .95rem; font-weight: 500;
            cursor: pointer; margin-top: 8px;
            transition: background .18s;
        }
        .submit-btn:hover { background: var(--accent); }

        .divider {
            text-align: center; font-size: .8rem; color: var(--muted);
            margin: 24px 0; position: relative;
        }
        .divider::before, .divider::after {
            content: ''; position: absolute;
            top: 50%; width: 42%; height: 1px; background: var(--warm);
        }
        .divider::before { left: 0; }
        .divider::after { right: 0; }

        .login-link {
            display: block; width: 100%; padding: 12px;
            background: var(--white); color: var(--ink);
            border: 1.5px solid var(--warm); border-radius: 8px;
            font-family: 'DM Sans', sans-serif;
            font-size: .93rem; font-weight: 500;
            text-align: center; text-decoration: none;
            transition: border-color .18s, color .18s;
        }
        .login-link:hover { border-color: var(--accent); color: var(--accent); }

        /* ── RESPONSIVE ── */
        @media (max-width: 768px) {
            .auth-wrapper { grid-template-columns: 1fr; }
            .auth-left { display: none; }
            .auth-right { padding: 40px 24px; }
        }
    </style>
</head>
<body>

<div class="auth-wrapper">

    <!-- LEFT -->
    <div class="auth-left">
        <a class="auth-brand" href="index.php">
            <img src="logo.png" alt="Logo">
            <span>My Store</span>
        </a>
        <div class="auth-left-content">
            <h2>Join <em>My Store</em><br>Today</h2>
            <p>Create your account and get access to our full collection of clothing and fashion — delivered right to your door.</p>
        </div>
        <p class="auth-left-footer">© <?php echo date('Y'); ?> My Store. All rights reserved.</p>
    </div>

    <!-- RIGHT -->
    <div class="auth-right">
        <div class="auth-box">
            <h1>Create Account</h1>
            <p class="sub">Already have an account? <a href="index.php">Log in</a></p>

            <?php if ($error): ?>
            <div class="error-msg"><?php echo $error; ?></div>
            <?php endif; ?>

            <form method="POST" action="register.php">
                <div class="form-group">
                    <label for="name">Full Name</label>
                    <input type="text" id="name" name="name"
                           placeholder="Your full name" required
                           value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">
                </div>
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email"
                           placeholder="you@email.com" required
                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Min. 6 characters" required>
                    <span class="hint">At least 6 characters</span>
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" placeholder="Repeat your password" required>
                </div>
                <button type="submit" class="submit-btn">Create Account →</button>
            </form>

            <div class="divider">or</div>
            <a href="index.php" class="login-link">Log In to Existing Account</a>
        </div>
    </div>

</div>

</body>
</html>
</html>