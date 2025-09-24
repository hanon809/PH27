<?php
session_start();

// ログインしていなければリダイレクト
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// DB接続
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

$user_id = $_SESSION['user_id'];
$message = '';
$message_type = '';

// ユーザー情報取得
$stmt = $pdo->prepare("SELECT * FROM user WHERE f_user_id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die("ユーザー情報が見つかりません。");
}

// 更新処理
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
    $name        = trim($_POST['name'] ?? '');
    $email       = trim($_POST['email'] ?? '');
    $phone       = trim($_POST['phone'] ?? '');
    $postal_code = trim($_POST['zipcode'] ?? '');
    $address     = trim($_POST['address'] ?? '');

    if (empty($name) || empty($email) || empty($address)) {
        $message = '必須項目を入力してください。';
        $message_type = 'error';
    } else {
        $stmt = $pdo->prepare("UPDATE user SET f_user_name=?, f_user_mail=?, f_phone_number=?, f_postal_code=?, f_address=? WHERE f_user_id=?");
        if ($stmt->execute([$name, $email, $phone, $postal_code, $address, $user_id])) {
            $message = '更新しました。';
            $message_type = 'success';
            // セッションも更新
            $_SESSION['user_name'] = $name;
            // DBから再取得
            $stmt = $pdo->prepare("SELECT * FROM user WHERE f_user_id = ?");
            $stmt->execute([$user_id]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
        } else {
            $message = '更新に失敗しました。';
            $message_type = 'error';
        }
    }
}

// フォーム用変数（POSTがあれば優先、それ以外はDBの値）
$name        = $_POST['name'] ?? $user['f_user_name'];
$email       = $_POST['email'] ?? $user['f_user_mail'];
$phone       = $_POST['phone'] ?? $user['f_phone_number'];
$zipcode     = $_POST['zipcode'] ?? $user['f_postal_code'];
$address     = $_POST['address'] ?? $user['f_address'];

?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>アカウント情報 - ぶっくどっとこむ！</title>
    <link rel="stylesheet" href="css/reset.css">
    <link rel="stylesheet" href="css/common.css">
    <link rel="stylesheet" href="css/account.css">
</head>

<body>
    <a href="index.php" class="back-home">← ホームに戻る</a>

    <div id="wrap">
        <main class="container">
            <h2 class="form-title">アカウント情報</h2>

            <?php if ($message): ?>
                <div class="message <?= $message_type ?>"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-row">
                    <div class="form-col">
                        <label>名前</label>
                        <input type="text" name="name" value="<?= htmlspecialchars($name) ?>" required>
                    </div>
                    <div class="form-col">
                        <label>メールアドレス</label>
                        <input type="email" name="email" value="<?= htmlspecialchars($email) ?>" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-col">
                        <label>電話番号</label>
                        <input type="text" name="phone" value="<?= htmlspecialchars($phone) ?>">
                    </div>
                    <div class="form-col">
                        <label>郵便番号</label>
                        <input type="text" name="zipcode" value="<?= htmlspecialchars($zipcode) ?>">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-col">
                        <label>住所</label>
                        <input type="text" name="address" value="<?= htmlspecialchars($address) ?>" required>
                    </div>
                </div>
                <div class="button">
                    <button type="submit" name="update">更新</button>
                    <div class="logout">
                        <a href="logout.php">ログアウト</a>
                    </div>
                </div>
            </form>

        </main>
    </div>
</body>

</html>