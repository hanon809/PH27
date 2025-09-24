<?php
session_start();

// echo password_hash('user123', PASSWORD_DEFAULT);


// データベース接続
$host = 'localhost';
$dbname = 'book_shop';
$username = 'root';
$password = 'root';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("データベース接続エラー: " . $e->getMessage());
}


$message = '';
$message_type = '';

// ログイン処理
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $message = 'メールアドレスとパスワードを入力してください';
        $message_type = 'error';
    } else {
        $stmt = $pdo->prepare("SELECT f_user_id, f_user_name, f_password FROM user WHERE f_user_mail = ? AND f_delete = 0");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['f_password'])) {
            $_SESSION['user_id'] = $user['f_user_id'];
            $_SESSION['user_name'] = $user['f_user_name'];
            header('Location: account.php');

            exit;
        } else {
            $message = 'メールアドレスまたはパスワードが間違っています';
            $message_type = 'error';
        }
    }
}

// 新規登録処理
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    $name = trim($_POST['name']) ?? '';
    $email = trim($_POST['email']) ?? '';
    $phone = trim($_POST['phone']) ?? '';
    $address = trim($_POST['address']) ?? '';
    $password = $_POST['password'] ?? '';
    $zipcode = $_POST['zipcode'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($name) || empty($email) || empty($password) || empty($confirm_password) || empty($address)) {
        $message = 'すべての必須項目を入力してください';
        $message_type = 'error';
    } elseif ($password !== $confirm_password) {
        $message = 'パスワードが一致しません';
        $message_type = 'error';
    } elseif (strlen($password) < 6) {
        $message = 'パスワードは6文字以上で入力してください';
        $message_type = 'error';
    } else {
        // メールアドレス重複チェック
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM user WHERE f_user_mail = ?");
        $stmt->execute([$email]);

        if ($stmt->fetchColumn() > 0) {
            $message = 'このメールアドレスは既に登録されています';
            $message_type = 'error';
        } else {
            // 新規登録
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("
                INSERT INTO user 
                (f_user_name, f_user_mail, f_phone_number, f_zipcode, f_address, f_password, f_registration_time, f_delete) 
                VALUES (?, ?, ?, ?, ?, ?, NOW(), 0)
            ");

            if ($stmt->execute([$name, $email, $phone, $zipcode, $address, $hashed_password])) {
                $_SESSION['user_id'] = $pdo->lastInsertId();
                $_SESSION['user_name'] = $name;
                header('Location: index.php');
                exit;
            } else {
                $message = '登録に失敗しました。もう一度お試しください';
                $message_type = 'error';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/reset.css">
    <link rel="stylesheet" href="css/common.css">
    <link rel="stylesheet" href="css/login.css">

    <title>ログイン - ぶっくどっとこむ！</title>

</head>

<body>
    <a href="index.php" class="back-home">← ホームに戻る</a>

    <div class="wrap container">
        <main>
            <div class="container slide-container">
                <!-- ログインフォーム -->
                <div class="form-section login-section" id="loginSection">
                    <div class="form-header">
                        <h2 class="form-title">ログイン</h2>
                        <p class="form-subtitle">アカウントにログインしてください</p>
                    </div>

                    <?php if ($message && isset($_POST['login'])): ?>
                        <div class="message <?= $message_type ?>"><?= htmlspecialchars($message) ?></div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="form-group">
                            <label class="form-label">メールアドレス</label>
                            <input type="email" name="email" class="form-input" required
                                value="<?= isset($_POST['login']) ? htmlspecialchars($_POST['email'] ?? '') : '' ?>">
                        </div>

                        <div class="form-group">
                            <label class="form-label">パスワード</label>
                            <input type="password" name="password" class="form-input" required>
                        </div>

                        <button type="submit" name="login" class="form-button">ログイン</button>
                    </form>

                    <div class="switch-link" onclick="showRegister()">
                        新規登録はこちら
                    </div>
                </div>

                <!-- 新規登録フォーム -->
                <div class="form-section register-section" id="registerSection">
                    <div class="form-header">
                        <h2 class="form-title">新規登録</h2>
                        <p class="form-subtitle">アカウントを作成してください</p>
                    </div>

                    <?php if ($message && isset($_POST['register'])): ?>
                        <div class="message <?= $message_type ?>"><?= htmlspecialchars($message) ?></div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="form-row">
                            <div class="form-col">
                                <label for="name">名前</label>
                                <input type="text" id="name" name="name" class="form-input">
                            </div>
                            <div class="form-col">
                                <label for="email">メールアドレス</label>
                                <input type="email" id="email" name="email" class="form-input">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-col">
                                <label for="phone">電話番号</label>
                                <input type="text" id="phone" name="phone" class="form-input">
                            </div>
                            <div class="form-col">
                                <label for="zipcode">郵便番号</label>
                                <input type="text" id="zipcode" name="zipcode" class="form-input">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-col">
                                <label for="address">住所</label>
                                <input type="text" id="address" name="address" class="form-input">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-col">
                                <label for="password">パスワード(6文字以上)</label>
                                <input type="password" id="password" name="password" class="form-input">
                            </div>
                            <div class="form-col">
                                <label for="confirm_password">パスワード(確認用)</label>
                                <input type="password" id="confirm_password" name="confirm_password" class="form-input">
                            </div>
                        </div>
                        <button type="submit" name="register" class="form-button">新規登録</button>
                    </form>
                    <div class="switch-link" onclick="showLogin()">
                        すでにアカウントをお持ちの方
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        let currentForm = 'login';

        function showRegister() {
            document.getElementById("loginSection").style.display = "none";
            document.getElementById("registerSection").style.display = "block";
        }

        function showLogin() {
            document.getElementById("registerSection").style.display = "none";
            document.getElementById("loginSection").style.display = "block";
        }

        // 初期状態の設定
        document.addEventListener('DOMContentLoaded', function() {
            <?php if (isset($_POST['register'])): ?>
                showRegister();
            <?php endif; ?>
        });

        // パスワード確認のバリデーション
        document.addEventListener('DOMContentLoaded', function() {
            const registerForm = document.querySelector('#registerSection form');
            if (registerForm) {
                registerForm.addEventListener('submit', function(e) {
                    const password = registerForm.querySelector('[name="password"]').value;
                    const confirmPassword = registerForm.querySelector('[name="confirm_password"]').value;

                    if (password !== confirmPassword) {
                        e.preventDefault();
                        alert('パスワードが一致しません');
                    }
                });
            }
        });

        document.addEventListener('DOMContentLoaded', function() {
            <?php if (isset($_POST['register'])): ?>
                showRegister();
            <?php else: ?>
                showLogin();
            <?php endif; ?>
        });
    </script>
</body>

</html>