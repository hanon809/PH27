<?php
session_start();

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

// AJAX リクエストの処理
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax_add_to_cart']) && $is_logged_in) {
  header('Content-Type: application/json');

  try {
    $product_id = (int)$_POST['product_id'];

    // 商品名を取得
    $stmt = $pdo->prepare("SELECT f_product_name FROM product WHERE f_product_id = ?");
    $stmt->execute([$product_id]);
    $product_name = $stmt->fetchColumn();

    if (!$product_name) {
      throw new Exception("商品が見つかりません");
    }

    // カートに商品を追加
    $stmt = $pdo->prepare("SELECT f_cart_id, f_quantity FROM cart WHERE f_user_id = ? AND f_product_id = ?");
    $stmt->execute([$user_id, $product_id]);
    $existing = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existing) {
      // 既存商品の数量を更新
      $new_quantity = $existing['f_quantity'] + 1;
      $stmt = $pdo->prepare("UPDATE cart SET f_quantity = ? WHERE f_cart_id = ?");
      $stmt->execute([$new_quantity, $existing['f_cart_id']]);
    } else {
      // 新しい商品をカートに追加
      $stmt = $pdo->prepare("INSERT INTO cart (f_user_id, f_product_id, f_quantity, f_adding_time) VALUES (?, ?, 1, NOW())");
      $stmt->execute([$user_id, $product_id]);
    }

    // カート内の総数を取得
    $stmt = $pdo->prepare("SELECT SUM(f_quantity) FROM cart WHERE f_user_id = ?");
    $stmt->execute([$user_id]);
    $cart_count = $stmt->fetchColumn() ?: 0;

    echo json_encode([
      'success' => true,
      'message' => $product_name . 'をカートに追加しました',
      'cart_count' => $cart_count
    ]);
  } catch (Exception $e) {
    echo json_encode([
      'success' => false,
      'message' => 'エラーが発生しました: ' . $e->getMessage()
    ]);
  }
  exit;
}

// カート内商品数取得
$cart_count = 0;
if ($is_logged_in) {
  $stmt = $pdo->prepare("SELECT SUM(f_quantity) FROM cart WHERE f_user_id = ?");
  $stmt->execute([$user_id]);
  $cart_count = $stmt->fetchColumn() ?: 0;
}

// カテゴリー別商品取得
$categories = [
  101 => '小説',
  102 => '漫画',
  103 => '恋愛漫画',
  104 => 'BL漫画'
];

$products_by_category = [];
foreach ($categories as $cat_id => $cat_name) {
  $stmt = $pdo->prepare("SELECT * FROM product WHERE f_category_id = ? LIMIT 99");
  $stmt->execute([$cat_id]);
  $products_by_category[$cat_id] = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// おすすめ商品取得
$stmt = $pdo->prepare("SHOW COLUMNS FROM product LIKE 'f_is_recommend'");
$stmt->execute();
$has_recommend_column = $stmt->fetch();

if ($has_recommend_column) {
  $stmt = $pdo->prepare("SELECT * FROM product WHERE f_is_recommend = 1 ORDER BY f_product_id DESC LIMIT 3");
  $stmt->execute();
  $recommended_products = $stmt->fetchAll(PDO::FETCH_ASSOC);

  if (empty($recommended_products)) {
    $stmt = $pdo->prepare("SELECT * FROM product ORDER BY f_product_id DESC LIMIT 3");
    $stmt->execute();
    $recommended_products = $stmt->fetchAll(PDO::FETCH_ASSOC);
  }
} else {
  $stmt = $pdo->prepare("SELECT * FROM product ORDER BY f_product_id DESC LIMIT 3");
  $stmt->execute();
  $recommended_products = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width">

  <title>ぶっくどっとこむ！</title>

  <link rel="stylesheet" href="css/reset.css">
  <link rel="stylesheet" href="css/common.css">
  <link rel="stylesheet" href="css/index.css">

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
          <span class="cart-count" id="cartCount"><?= $cart_count ?></span>
        <?php else: ?>
          <span class="cart-count" id="cartCount" style="display: none;">0</span>
        <?php endif; ?>
      </a>
    </div>
  </header>

  <!-- メッセージ表示エリア -->
  <div class="cart-message" id="cartMessage"></div>

  <div class="top-img"></div>

  <div id="wrap">
    <main class="container">
      <!-- おすすめ商品 -->
      <section class="section">
        <h2 class="section-title">おすすめ商品</h2>
        <div class="products-grid">
          <?php foreach ($recommended_products as $product): ?>
            <div class="product-card">
              <div class="product-image">
                <?php if ($product['f_product_photo']): ?>
                  <img src="images/product/<?= htmlspecialchars($product['f_product_photo']) ?>"
                    alt="<?= htmlspecialchars($product['f_product_name']) ?>">
                <?php endif; ?>
              </div>
              <div class="product-info">
                <div class="product-title"><?= htmlspecialchars($product['f_product_name']) ?></div>
                <div class="product-author"><?= htmlspecialchars($product['f_writer_name'] ?? '') ?></div>
                <div class="product-price">¥<?= number_format($product['f_product_price']) ?></div>
                <?php if ($is_logged_in): ?>
                  <button class="add-to-cart-btn" data-product-id="<?= $product['f_product_id'] ?>">
                    <i class="fa-solid fa-cart-shopping"></i>
                  </button>
                <?php else: ?>
                  <button class="add-to-cart-btn" disabled>ログインが必要です</button>
                <?php endif; ?>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </section>

      <!-- カテゴリー別商品 -->
      <?php foreach ($categories as $cat_id => $cat_name): ?>
        <section id="category-<?= $cat_id ?>">
          <h2 class="section-title"><?= $cat_name ?></h2>
          <div class="products-grid">
            <?php foreach ($products_by_category[$cat_id] as $product): ?>
              <div class="product-card">
                <div class="product-image">
                  <?php if ($product['f_product_photo']): ?>
                    <img src="images/product/<?= $product['f_product_photo'] ?>"
                      alt="<?= htmlspecialchars($product['f_product_name']) ?>">
                  <?php endif; ?>
                </div>
                <div class="product-info">
                  <div class="product-title"><?= htmlspecialchars($product['f_product_name']) ?></div>
                  <div class="product-author"><?= htmlspecialchars($product['f_writer_name'] ?? '') ?></div>
                  <div class="product-price">¥<?= number_format($product['f_product_price']) ?></div>
                  <?php if ($is_logged_in): ?>
                    <button class="add-to-cart-btn" data-product-id="<?= $product['f_product_id'] ?>">
                      <i class="fa-solid fa-cart-shopping"></i>
                    </button>
                  <?php else: ?>
                    <button class="add-to-cart-btn" disabled>ログインが必要です</button>
                  <?php endif; ?>
                </div>
              </div>
            <?php endforeach; ?>
            <?php
            $count = count($products_by_category[$cat_id]);
            $remainder = $count % 3;
            if ($remainder !== 0) {
              $add_empty = 3 - $remainder;
              for ($i = 0; $i < $add_empty; $i++): ?>
                <div class="product-card empty"></div>
            <?php endfor;
            }
            ?>
          </div>
        </section>
      <?php endforeach; ?>
    </main>
  </div>

  <footer>
    <h1>
      <a href="index.php"><img src="./images/logo-black.png" alt="ぶっくどっとこむ！"></a>
    </h1>
    <div class="copyright">
      copyright &copy;
      <script>
        var hiduke = new Date();
        document.write(hiduke.getFullYear());
      </script> YamazakiHanon. all rights reserved.
    </div>
  </footer>

  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="js/main.js"></script>
</body>

</html>