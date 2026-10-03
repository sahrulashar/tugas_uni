<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — Sistem ERP</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #1e3a5f 0%, #2d6a9f 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .card {
            background: #fff;
            border-radius: 12px;
            padding: 2.5rem 2rem;
            width: 100%;
            max-width: 380px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }

        .logo {
            text-align: center;
            margin-bottom: 1.5rem;
        }

        .logo h1 {
            font-size: 1.6rem;
            color: #1e3a5f;
            font-weight: 700;
        }

        .logo p {
            color: #6c757d;
            font-size: 0.85rem;
            margin-top: 0.25rem;
        }

        .alert {
            padding: 0.75rem 1rem;
            border-radius: 6px;
            margin-bottom: 1rem;
            font-size: 0.88rem;
        }

        .alert-danger  { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }

        .form-group {
            margin-bottom: 1.2rem;
        }

        label {
            display: block;
            font-size: 0.85rem;
            font-weight: 600;
            color: #495057;
            margin-bottom: 0.4rem;
        }

        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 0.65rem 0.9rem;
            border: 1px solid #ced4da;
            border-radius: 6px;
            font-size: 0.95rem;
            transition: border-color 0.2s;
            outline: none;
        }

        input:focus {
            border-color: #2d6a9f;
            box-shadow: 0 0 0 3px rgba(45,106,159,0.15);
        }

        .btn-login {
            width: 100%;
            padding: 0.75rem;
            background: #1e3a5f;
            color: #fff;
            border: none;
            border-radius: 6px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
            margin-top: 0.5rem;
        }

        .btn-login:hover { background: #2d6a9f; }

        .footer-text {
            text-align: center;
            margin-top: 1.5rem;
            font-size: 0.78rem;
            color: #adb5bd;
        }
    </style>
</head>
<body>

<div class="card">
    <div class="logo">
        <h1>⚙️ Sistem ERP</h1>
        <p>Silakan masuk untuk melanjutkan</p>
    </div>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger">
            <?= esc(session()->getFlashdata('error')) ?>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success">
            <?= esc(session()->getFlashdata('success')) ?>
        </div>
    <?php endif; ?>

    <form action="<?= base_url('login/proses') ?>" method="POST">
        <?= csrf_field() ?>

        <div class="form-group">
            <label for="kode">Kode User</label>
            <input
                type="text"
                id="kode"
                name="kode"
                value="<?= esc(old('kode')) ?>"
                placeholder="Masukkan kode user"
                autofocus
                required
            >
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <input
                type="password"
                id="password"
                name="password"
                placeholder="Masukkan password"
                required
            >
        </div>

        <button type="submit" class="btn-login">🔐 Masuk</button>
    </form>

    <p class="footer-text">Sistem ERP &copy; <?= date('Y') ?></p>
</div>

</body>
</html>
