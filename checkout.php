<?php
session_start();

// ログインチェック
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

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

// ユーザー情報取得
$user_id = $_SESSION['user_id'] ?? null;
$is_logged_in = $user_id !== null;

// カート内商品数取得
$cart_count = 0;
if ($is_logged_in) {
    $stmt = $pdo->prepare("SELECT SUM(f_quantity) FROM cart WHERE f_user_id = ?");
    $stmt->execute([$user_id]);
    $cart_count = $stmt->fetchColumn() ?: 0;
}

// ユーザー情報取得
$stmt = $pdo->prepare("SELECT * FROM user WHERE f_user_id = ?");
$stmt->execute([$user_id]);
$user_info = $stmt->fetch(PDO::FETCH_ASSOC);

// カート商品取得
$stmt = $pdo->prepare("
    SELECT c.f_cart_id, c.f_quantity, p.f_product_id, p.f_product_name, 
           p.f_product_price, p.f_writer_name, p.f_product_photo,
           (p.f_product_price * c.f_quantity) as subtotal
    FROM cart c
    JOIN product p ON c.f_product_id = p.f_product_id
    WHERE c.f_user_id = ?
    ORDER BY c.f_adding_time DESC
");
$stmt->execute([$user_id]);
$cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// カートが空の場合はカートページにリダイレクト
if (empty($cart_items)) {
    header('Location: cart.php');
    exit;
}

// 合計計算
$total_amount = array_sum(array_column($cart_items, 'subtotal'));
$shipping_fee = $total_amount >= 2000 ? 0 : 500;
$final_total = $total_amount + $shipping_fee;

$message = '';
$order_completed = false;

// 注文処理
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {
    try {
        $pdo->beginTransaction();

        // 配送情報の更新
        $address = trim($_POST['address']);
        $postal_code = trim($_POST['postal_code']);
        $phone = trim($_POST['phone']);
        $payment_method = $_POST['payment_method'];

        if (empty($address) || empty($postal_code) || empty($phone)) {
            throw new Exception('配送情報をすべて入力してください');
        }

        // ユーザー情報更新
        $stmt = $pdo->prepare("UPDATE user SET f_address = ?, f_postal_code = ?, f_phone_number = ?, f_payment = ?, f_update = NOW() WHERE f_user_id = ?");
        $stmt->execute([$address, $postal_code, $phone, $payment_method, $user_id]);

        // 注文情報作成
        $stmt = $pdo->prepare("INSERT INTO order_info (f_user_id, f_total_amount, f_order_status, f_order_time) VALUES (?, ?, '注文確定', NOW())");
        $stmt->execute([$user_id, $final_total]);
        $order_id = $pdo->lastInsertId();

        // 注文履歴作成
        $stmt = $pdo->prepare("INSERT INTO order_history (f_order_id, f_user_id, f_order_status) VALUES (?, ?, '注文確定')");
        $stmt->execute([$order_id, $user_id]);

        // 配送情報作成
        $stmt = $pdo->prepare("INSERT INTO delivery (f_order_id, f_order_info_status) VALUES (?, '準備中')");
        $stmt->execute([$order_id]);

        // カートをクリア
        $stmt = $pdo->prepare("DELETE FROM cart WHERE f_user_id = ?");
        $stmt->execute([$user_id]);

        $pdo->commit();
        $order_completed = true;
    } catch (Exception $e) {
        $pdo->rollback();
        $message = $e->getMessage();
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
    <link rel="stylesheet" href="css/checkout.css">

    <title>注文手続き - ぶっくどっとこむ！</title>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

</head>

<body>
    <header>
        <div class="header-left">
            <h1><a href="index.php"><img src="./images/logo-black.png" alt="ぶっくどっとこむ！"></a></h1>

            <!-- ジャンル一覧 -->
            <div class="accodion">
                <ul class="menu">
                    <li>
                        <a href="#" class="menu-title">ジャンル一覧</a>
                        <ul class="sub-menu">
                            <li><a href="index.php#category-101">小説</a></li>
                            <li><a href="index.php#category-102">漫画</a></li>
                            <li><a href="index.php#category-103">恋愛漫画</a></li>
                            <li><a href="index.php#category-104">BL漫画</a></li>
                        </ul>
                    </li>
                </ul>
            </div>

            <!-- search -->
            <form class="search" action="search.php" method="GET">
                <input type="text" name="q" class="search-input" placeholder="検索">
                <button type="submit" class="search-btn"><i class="fa-solid fa-magnifying-glass"></i></button>
            </form>
        </div>

        <div class="header-right">
            <?php if ($is_logged_in): ?>
                <!-- アカウントページへのリンク -->
                <a href="account.php" class="user-icon" title="アカウント">
                    <i class="fa-solid fa-user"></i>
                </a>
            <?php else: ?>
                <!-- ログインページ -->
                <a href="login.php" class="user-icon" title="ログイン">
                    <i class="fa-solid fa-user"></i>
                </a>
            <?php endif; ?>

            <a href="cart.php" class="cart-icon" title="カート">
                <i class="fa-solid fa-cart-shopping"></i>
                <?php if ($cart_count > 0): ?>
                    <span class="cart-count"><?= $cart_count ?></span>
                <?php endif; ?>
            </a>
        </div>

    </header>

    <div id="wrap">
        <main>
            <h1 class="page-title">
                <?= $order_completed ? '注文完了' : '注文手続き' ?>
            </h1>

            <?php if ($message): ?>
                <div class="message"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>

            <?php if ($order_completed): ?>
                <div class="order-success">
                    <h2 class="success-title">ご注文ありがとうございました！</h2>
                    <p class="success-message">
                        ご注文を承りました。<br>
                        商品の発送準備が整い次第、メールでご連絡いたします。
                    </p>
                    <a href="index.php" class="home-btn">ホームに戻る</a>
                </div>
            <?php else: ?>
                <div class="checkout-container">
                    <form method="POST" class="checkout-form">
                        <h3 class="section-title">配送・お支払い情報</h3>

                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">郵便番号 *</label>
                                <input type="text" name="postal_code" class="form-input" required
                                    value="<?= htmlspecialchars($user_info['f_postal_code'] ?? '') ?>"
                                    placeholder="123-4567">
                            </div>

                            <div class="form-group">
                                <label class="form-label">電話番号 *</label>
                                <input type="tel" name="phone" class="form-input" required
                                    value="<?= htmlspecialchars($user_info['f_phone_number'] ?? '') ?>"
                                    placeholder="090-1234-5678">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">お届け先住所 *</label>
                            <input type="text" name="address" class="form-input" required
                                value="<?= htmlspecialchars($user_info['f_address'] ?? '') ?>"
                                placeholder="東京都渋谷区○○○...">
                        </div>

                        <div class="form-group">
                            <label class="form-label">お支払い方法 *</label>
                            <select name="payment_method" class="form-select" required>
                                <option value="">選択してください</option>
                                <option value="クレジットカード" <?= ($user_info['f_payment'] ?? '') === 'クレジットカード' ? 'selected' : '' ?>>クレジットカード</option>
                                <option value="代金引換" <?= ($user_info['f_payment'] ?? '') === '代金引換' ? 'selected' : '' ?>>代金引換</option>
                                <option value="銀行振込" <?= ($user_info['f_payment'] ?? '') === '銀行振込' ? 'selected' : '' ?>>銀行振込</option>
                                <option value="コンビニ支払い" <?= ($user_info['f_payment'] ?? '') === 'コンビニ支払い' ? 'selected' : '' ?>>コンビニ支払い</option>
                            </select>
                        </div>

                        <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0;">
                            <h4 style="margin-bottom: 10px; color: #6AA89D;">注文内容確認</h4>
                            <p style="color: #666; font-size: 0.9rem; margin-bottom: 15px;">
                                以下の内容でご注文を確定します。内容をご確認ください。
                            </p>
                            <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                                <input type="checkbox" required style="width: auto;">
                                <span style="font-size: 0.95rem;">上記の注文内容および利用規約に同意します</span>
                            </label>
                        </div>

                        <button type="submit" name="place_order" class="place-order-btn">
                            注文を確定
                        </button>
                    </form>

                    <div class="order-summary">
                        <h3 class="section-title">ご注文内容</h3>

                        <div class="order-items">
                            <?php foreach ($cart_items as $item): ?>
                                <div class="order-item">
                                    <div class="item-image">
                                        <?php if ($item['f_product_photo']): ?>
                                            <img src="images/product/<?= $item['f_product_photo'] ?>"
                                                style="width:100%;height:100%;object-fit:cover;border-radius:6px;">
                                        <?php else: ?>
                                            📖
                                        <?php endif; ?>
                                    </div>
                                    <div class="item-details">
                                        <div class="item-name"><?= htmlspecialchars($item['f_product_name']) ?></div>
                                        <div class="item-author"><?= htmlspecialchars($item['f_writer_name'] ?? '') ?></div>
                                        <div class="item-quantity">数量: <?= $item['f_quantity'] ?>冊</div>
                                    </div>
                                    <div class="item-price">¥<?= number_format($item['subtotal']) ?></div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="summary-row">
                            <span>小計 (<?= count($cart_items) ?>点)</span>
                            <span>¥<?= number_format($total_amount) ?></span>
                        </div>

                        <div class="summary-row">
                            <span>送料</span>
                            <span><?= $shipping_fee > 0 ? '¥' . number_format($shipping_fee) : '無料' ?></span>
                        </div>

                        <div class="summary-row">
                            <span>合計</span>
                            <span>¥<?= number_format($final_total) ?></span>
                        </div>

                        <?php if ($shipping_fee > 0): ?>
                            <div style="text-align: center; color: #666; font-size: 0.9rem; margin-bottom: 20px;">
                                あと、<?= number_format(2000 - $total_amount) ?>円以上購入で送料無料
                            </div>
                        <?php else: ?>
                            <div style="text-align: center; color: #28a745; font-size: 0.9rem; margin-bottom: 20px;">
                                送料無料でお届けします
                            </div>
                        <?php endif; ?>

                        <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; font-size: 0.9rem; color: #666;">
                            <div style="margin-bottom: 8px;">📦 <strong>配送について</strong></div>
                            <div style="margin-bottom: 5px;">• 通常2-3営業日でお届け</div>
                            <div style="margin-bottom: 5px;">• 土日祝日は発送休業</div>
                            <div>• 配送状況はメールでお知らせします</div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </main>
    </div>

    <script>
        // フォームバリデーション
        document.querySelector('form').addEventListener('submit', function(e) {
            const postalCode = document.querySelector('[name="postal_code"]').value.trim();
            const phone = document.querySelector('[name="phone"]').value.trim();
            const address = document.querySelector('[name="address"]').value.trim();
            const paymentMethod = document.querySelector('[name="payment_method"]').value;
            const checkbox = document.querySelector('input[type="checkbox"]');

            // 必須項目チェック
            if (!postalCode || !phone || !address || !paymentMethod) {
                e.preventDefault();
                alert('すべての必須項目を入力してください');
                return;
            }

            // 同意チェック
            if (!checkbox.checked) {
                e.preventDefault();
                alert('利用規約への同意が必要です');
                return;
            }

            // 郵便番号形式チェック
            const postalPattern = /^\d{3}-?\d{4}$/;
            if (!postalPattern.test(postalCode)) {
                e.preventDefault();
                alert('郵便番号は123-4567の形式で入力してください');
                return;
            }

            // 電話番号形式チェック
            const phonePattern = /^[\d\-\(\)\s]{10,15}$/;
            if (!phonePattern.test(phone)) {
                e.preventDefault();
                alert('正しい電話番号を入力してください');
                return;
            }

            // 確認ダイアログ
            if (!confirm('注文を確定しますか？')) {
                e.preventDefault();
                return;
            }

            // 送信ボタンを無効化
            const submitButton = this.querySelector('[type="submit"]');
            submitButton.disabled = true;
            submitButton.textContent = '処理中...';
        });

        // 郵便番号の自動フォーマット
        document.querySelector('[name="postal_code"]').addEventListener('input', function(e) {
            let value = e.target.value.replace(/[^\d]/g, '');
            if (value.length > 3) {
                value = value.substring(0, 3) + '-' + value.substring(3, 7);
            }
            e.target.value = value;
        });

        // 電話番号の自動フォーマット
        document.querySelector('[name="phone"]').addEventListener('input', function(e) {
            let value = e.target.value.replace(/[^\d]/g, '');
            if (value.length > 3 && value.length <= 7) {
                value = value.substring(0, 3) + '-' + value.substring(3);
            } else if (value.length > 7) {
                value = value.substring(0, 3) + '-' + value.substring(3, 7) + '-' + value.substring(7, 11);
            }
            e.target.value = value;
        });

        // メッセージの自動非表示
        const message = document.querySelector('.message');
        if (message) {
            setTimeout(() => {
                message.style.opacity = '0';
                setTimeout(() => message.remove(), 300);
            }, 5000);
        }
    </script>
</body>

</html>