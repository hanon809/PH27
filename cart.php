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

$message = '';

// カート内商品数取得
$cart_count = 0;
if ($is_logged_in) {
    $stmt = $pdo->prepare("SELECT SUM(f_quantity) FROM cart WHERE f_user_id = ?");
    $stmt->execute([$user_id]);
    $cart_count = $stmt->fetchColumn() ?: 0;
}

// カート操作処理
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_quantity'])) {
        $cart_id = (int)$_POST['cart_id'];
        $quantity = (int)$_POST['quantity'];

        if ($quantity > 0) {
            $stmt = $pdo->prepare("UPDATE cart SET f_quantity = ? WHERE f_cart_id = ? AND f_user_id = ?");
            $stmt->execute([$quantity, $cart_id, $user_id]);
        } else {
            $stmt = $pdo->prepare("DELETE FROM cart WHERE f_cart_id = ? AND f_user_id = ?");
            $stmt->execute([$cart_id, $user_id]);
        }
    }

    if (isset($_POST['remove_item'])) {
        $cart_id = (int)$_POST['cart_id'];
        $stmt = $pdo->prepare("DELETE FROM cart WHERE f_cart_id = ? AND f_user_id = ?");
        $stmt->execute([$cart_id, $user_id]);
    }

    if (isset($_POST['clear_cart'])) {
        $stmt = $pdo->prepare("DELETE FROM cart WHERE f_user_id = ?");
        $stmt->execute([$user_id]);
    }
}

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

// 合計計算
$total_amount = array_sum(array_column($cart_items, 'subtotal'));
$shipping_fee = $total_amount >= 2000 ? 0 : 500;
$final_total = $total_amount + $shipping_fee;
?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="css/reset.css">
    <link rel="stylesheet" href="css/common.css">
    <link rel="stylesheet" href="css/cart.css">

    <title>カート - ぶっくどっとこむ！</title>

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
        <main class="container">
            <h1 class="page-title">ショッピングカート</h1>

            <?php if ($message): ?>
                <div class="message"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>

            <?php if (empty($cart_items)): ?>
                <div class="empty-cart">
                    <div class="empty-cart-icon">🛒</div>
                    <h3>カートが空です</h3>
                    <p>お気に入りの本を見つけてカートに追加してください</p>
                    <a href="index.php" class="checkout-btn">本を探しに行く</a>
                </div>
            <?php else: ?>
                <div class="cart-container">
                    <div class="cart-items">
                        <div class="cart-actions">
                            <form method="POST" style="display: inline;">
                                <button type="submit" name="clear_cart" class="clear-btn"
                                    onclick="return confirm('カートを空にしますか？')">
                                    カートを空にする
                                </button>
                            </form>
                        </div>

                        <?php foreach ($cart_items as $item): ?>
                            <div class="cart-item">
                                <div class="item-image">
                                    <?php if ($item['f_product_photo']): ?>
                                        <img src="images/product/<?= $item['f_product_photo'] ?>">
                                    <?php else: ?>
                                        📖
                                    <?php endif; ?>
                                </div>

                                <div class="item-info">
                                    <div class="item-title"><?= htmlspecialchars($item['f_product_name']) ?></div>
                                    <div class="item-author"><?= htmlspecialchars($item['f_writer_name'] ?? '') ?></div>
                                    <div class="item-price">¥<?= number_format($item['f_product_price']) ?></div>
                                </div>

                                <div class="item-controls">
                                    <form method="POST" class="quantity-controls">
                                        <input type="hidden" name="cart_id" value="<?= $item['f_cart_id'] ?>">
                                        <button type="submit" name="update_quantity" class="qty-btn"
                                            value="<?= $item['f_quantity'] - 1 ?>">-</button>
                                        <input type="number" name="quantity" value="<?= $item['f_quantity'] ?>"
                                            class="qty-input" min="0" max="99"
                                            onchange="this.form.submit();">
                                        <button type="submit" name="update_quantity" class="qty-btn"
                                            value="<?= $item['f_quantity'] + 1 ?>">+</button>
                                        <input type="hidden" name="update_quantity">
                                    </form>

                                    <div class="subtotal">¥<?= number_format($item['subtotal']) ?></div>

                                    <form method="POST">
                                        <input type="hidden" name="cart_id" value="<?= $item['f_cart_id'] ?>">
                                        <button type="submit" name="remove_item" class="remove-btn"
                                            onclick="return confirm('この商品を削除しますか？')">
                                            削除
                                        </button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="cart-summary">
                        <h3 class="summary-title">ご注文内容</h3>

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
                            <p>
                                あと、<?= number_format(2000 - $total_amount) ?>円以上購入で送料無料
                            </p>
                        <?php endif; ?>
                        <form method="POST" action="checkout.php">
                            <button type="submit" class="checkout-btn">注文手続きへ</button>
                        </form>

                        <a href="index.php" class="continue-shopping">ショッピングを続ける</a>
                    </div>
                </div>
            <?php endif; ?>
        </main>
    </div>

    <script>
        // 数量変更の処理
        document.querySelectorAll('.qty-btn').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const form = this.closest('form');
                const quantityInput = form.querySelector('input[name="quantity"]');
                const newQuantity = parseInt(this.value);

                if (newQuantity >= 0) {
                    quantityInput.value = newQuantity;
                    form.querySelector('input[name="update_quantity"]').value = newQuantity;
                    form.submit();
                }
            });
        });

        // メッセージの自動非表示
        const message = document.querySelector('.message');
        if (message) {
            setTimeout(() => {
                message.style.opacity = '0';
                setTimeout(() => message.remove(), 300);
            }, 3000);
        }
    </script>
</body>

</html>