<?php
session_start();

// 管理者認証チェック
if (!isset($_SESSION['admin_id'])) {
  if (!isset($_POST['admin_login'])) {
    // ログインフォーム
?>
    <!DOCTYPE html>
    <html lang="ja">

    <head>
      <meta charset="UTF-8">
      <title>管理者ログイン</title>
      <link rel="stylesheet" href="css/reset.css">
      <link rel="stylesheet" href="css/common.css">
      <link rel="stylesheet" href="css/admin.css">
    </head>

    <body>
      <a href="index.php" class="back-home">← ホームに戻る</a>
      <form method="POST" class="login-form">
        <h2>管理者ログイン</h2>
        <?php if (isset($_GET['error'])): ?>
          <div class="error">ログイン情報が正しくありません</div>
        <?php endif; ?>
        <div class="form-group">
          <label>メールアドレス</label>
          <input type="email" name="admin_email" required>
        </div>
        <div class="form-group">
          <label>パスワード</label>
          <input type="password" name="admin_password" required>
        </div>
        <button type="submit" name="admin_login">ログイン</button>
      </form>
    </body>

    </html>
<?php
    exit;
  } else {
    // 管理者認証処理
    $host = 'localhost';
    $dbname = 'book_shop';
    $username = 'root';
    $password = 'root';
    try {
      $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
      $stmt = $pdo->prepare("SELECT f_admin_id, f_name FROM admin WHERE f_mail=? AND f_password=?");
      $stmt->execute([$_POST['admin_email'], $_POST['admin_password']]);
      $admin = $stmt->fetch();
      if ($admin) {
        $_SESSION['admin_id'] = $admin['f_admin_id'];
        $_SESSION['admin_name'] = $admin['f_name'];
      } else {
        header('Location: admin.php?error=1');
        exit;
      }
    } catch (PDOException $e) {
      die("データベース接続エラー: " . $e->getMessage());
    }
  }
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

$message = '';
$upload_dir = 'images/product/'; // サーバー上の画像フォルダ
$image_files = array_diff(scandir($upload_dir), ['.', '..']);


// 商品操作処理
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  // おすすめ切替
  if (isset($_POST['toggle_recommend'])) {
    $product_id = (int)$_POST['product_id'];
    $stmt = $pdo->prepare("SELECT f_is_recommend FROM product WHERE f_product_id=?");
    $stmt->execute([$product_id]);
    $current = $stmt->fetchColumn();
    $new_status = $current ? 0 : 1;
    $stmt = $pdo->prepare("UPDATE product SET f_is_recommend=? WHERE f_product_id=?");
    $stmt->execute([$new_status, $product_id]);
    $message = $new_status ? 'おすすめに追加しました' : 'おすすめを解除しました';
  }

  // 商品削除
  if (isset($_POST['delete_product'])) {
    $product_id = (int)$_POST['product_id'];
    $stmt = $pdo->prepare("DELETE FROM product WHERE f_product_id=?");
    $stmt->execute([$product_id]);
    $message = '商品を削除しました';
  }

  $photo_path = trim($_POST['product_image_select'] ?? '');

  // 商品追加
  if (isset($_POST['add_product'])) {
    $category_id = (int)$_POST['category_id'];
    $name = trim($_POST['product_name']);
    $price = (float)$_POST['price'];
    $writer = trim($_POST['writer_name']);
    $size = trim($_POST['product_size']);
    $publisher = trim($_POST['publisher']);
    $release_date = $_POST['release_date'];

    $stmt = $pdo->prepare("INSERT INTO product (f_category_id,f_product_name,f_product_price,f_writer_name,f_product_size,f_publisher,f_release_date,f_product_photo) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    if ($stmt->execute([$category_id, $name, $price, $writer, $size, $publisher, $release_date, $photo_path])) {
      $message = '商品を追加しました';
    }
  }

  // 商品更新
  if (isset($_POST['update_product'])) {
    $product_id = (int)$_POST['product_id'];
    $category_id = (int)$_POST['category_id'];
    $name = trim($_POST['product_name']);
    $price = (float)$_POST['price'];
    $writer = trim($_POST['writer_name']);
    $size = trim($_POST['product_size']);
    $publisher = trim($_POST['publisher']);
    $release_date = $_POST['release_date'];

    $stmt = $pdo->prepare("UPDATE product SET f_category_id=?, f_product_name=?, f_product_price=?, f_writer_name=?, f_product_size=?, f_publisher=?, f_release_date=?, f_product_photo=? WHERE f_product_id=?");
    $stmt->execute([$category_id, $name, $price, $writer, $size, $publisher, $release_date, $photo_path, $product_id]);

    $message = '商品を更新しました';
  }
}

// 商品一覧取得
$stmt = $pdo->prepare("SELECT p.*, c.f_category_name FROM product p JOIN category c ON p.f_category_id=c.f_category_id ORDER BY p.f_product_id DESC");
$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// カテゴリー一覧取得
$stmt = $pdo->prepare("SELECT * FROM category ORDER BY f_category_id");
$stmt->execute();
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 編集対象の商品取得
$edit_product = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
  $stmt = $pdo->prepare("SELECT * FROM product WHERE f_product_id=?");
  $stmt->execute([$_GET['edit']]);
  $edit_product = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>管理者画面 - ぶっくどっとこむ！</title>
  <link rel="stylesheet" href="css/reset.css">
  <link rel="stylesheet" href="css/admin.css">
</head>

<body>
  <header class="admin-header">
    <div class="header-container">
      <h1 class="admin-title">管理者画面</h1>
      <nav class="admin-nav">
        <span>ようこそ、<?= htmlspecialchars($_SESSION['admin_name']) ?>さん</span>
        <a href="index.php" class="nav-link">サイトを見る</a>
        <a href="admin.php?logout=1" class="nav-link">ログアウト</a>
      </nav>
    </div>
  </header>

  <?php
  if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: index.php');
    exit;
  }
  ?>

  <div class="container">
    <?php if ($message): ?>
      <div class="message"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <!-- 商品一覧 -->
    <section class="products-section">
      <h2 class="section-title">商品管理</h2>
      <?php if (empty($products)): ?>
        <div class="empty-state">
          <div class="empty-icon">📚</div>
          <h3>商品がありません</h3>
          <p>下のフォームから商品を追加してください</p>
        </div>
      <?php else: ?>
        <div style="overflow-x:auto;">
          <table class="products-table">
            <thead>
              <tr>
                <th>ID</th>
                <th>画像</th>
                <th>商品名</th>
                <th>カテゴリー</th>
                <th>作者</th>
                <th>価格</th>
                <th>発売日</th>
                <th>操作</th>
                <th>おすすめ</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($products as $product): ?>
                <tr>
                  <td><?= $product['f_product_id'] ?></td>
                  <td>
                    <?php if (!empty($product['f_product_photo'])): ?>
                      <img src="images/product/<?= htmlspecialchars($product['f_product_photo']) ?>"
                        alt="<?= htmlspecialchars($product['f_product_name']) ?>"
                        style="width:60px; height:auto;">
                    <?php else: ?>
                      なし
                    <?php endif; ?>
                  </td>
                  <td><?= htmlspecialchars($product['f_product_name']) ?></td>
                  <td><?= htmlspecialchars($product['f_category_name']) ?></td>
                  <td><?= htmlspecialchars($product['f_writer_name'] ?? '') ?></td>
                  <td class="price">¥<?= number_format($product['f_product_price']) ?></td>
                  <td><?= $product['f_release_date'] ? date('Y/m/d', strtotime($product['f_release_date'])) : '' ?></td>
                  <td>
                    <div class="action-buttons">
                      <a href="admin.php?edit=<?= $product['f_product_id'] ?>" class="btn btn-edit">編集</a>
                      <form method="POST" style="display:inline;">
                        <input type="hidden" name="product_id" value="<?= $product['f_product_id'] ?>">
                        <button type="submit" name="delete_product" class="btn btn-delete"
                          onclick="return confirm('この商品を削除しますか？')">削除
                        </button>
                      </form>
                    </div>
                  </td>
                  <td>
                    <div class="action-buttons">
                      <form method="POST" style="display:inline;">
                        <input type="hidden" name="product_id" value="<?= $product['f_product_id'] ?>">
                        <?php if ($product['f_is_recommend']): ?>
                          <button type="submit" name="toggle_recommend" class="btn btn-secondary">解除</button>
                        <?php else: ?>
                          <button type="submit" name="toggle_recommend" class="btn btn-primary">追加</button>
                        <?php endif; ?>
                      </form>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </section>

    <!-- 商品追加/編集フォーム -->
    <section class="product-form">
      <h2 class="section-title"><?= $edit_product ? '商品編集' : '商品追加' ?></h2>

      <form id="addEditForm" method="POST" enctype="multipart/form-data">
        <?php if ($edit_product): ?>
          <input type="hidden" name="product_id" value="<?= $edit_product['f_product_id'] ?>">
        <?php endif; ?>

        <div class="form-grid">
          <div class="form-group">
            <label class="form-label">商品名 *</label>
            <input type="text" name="product_name" class="form-input" required
              value="<?= htmlspecialchars($edit_product['f_product_name'] ?? '') ?>">
          </div>

          <div class="form-group">
            <label class="form-label">カテゴリー *</label>
            <select name="category_id" class="form-select" required>
              <option value="">選択してください</option>
              <?php foreach ($categories as $category): ?>
                <option value="<?= $category['f_category_id'] ?>"
                  <?= ($edit_product && $edit_product['f_category_id'] == $category['f_category_id']) ? 'selected' : '' ?>>
                  <?= htmlspecialchars($category['f_category_name']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="form-group">
            <label class="form-label">価格 *</label>
            <input type="number" name="price" class="form-input" required step="0.01" min="0"
              value="<?= $edit_product['f_product_price'] ?? '' ?>">
          </div>

          <div class="form-group">
            <label class="form-label">作者</label>
            <input type="text" name="writer_name" class="form-input"
              value="<?= htmlspecialchars($edit_product['f_writer_name'] ?? '') ?>">
          </div>

          <div class="form-group">
            <label class="form-label">商品サイズ</label>
            <input type="text" name="product_size" class="form-input"
              value="<?= htmlspecialchars($edit_product['f_product_size'] ?? '') ?>">
          </div>

          <div class="form-group">
            <label class="form-label">出版社</label>
            <input type="text" name="publisher" class="form-input"
              value="<?= htmlspecialchars($edit_product['f_publisher'] ?? '') ?>">
          </div>

          <div class="form-group">
            <label class="form-label">発売日</label>
            <input type="date" name="release_date" class="form-input"
              value="<?= $edit_product['f_release_date'] ?? '' ?>">
          </div>

          <div class="form-group">
            <label class="form-label">商品画像</label>
            <select name="product_image_select" class="form-input">
              <option value="">選択してください</option>
              <?php foreach ($image_files as $img): ?>
                <option value="<?= $img ?>" <?= ($edit_product && $edit_product['f_product_photo'] == $img) ? 'selected' : '' ?>>
                  <?= $img ?>
                </option>
              <?php endforeach; ?>
            </select>
            <p>※フォルダ内の画像から選択してください</p>
          </div>
        </div>

        <div class="form-buttons">
          <?php if ($edit_product): ?>
            <button type="submit" name="update_product" class="btn-primary">更新する</button>
            <a href="admin.php" class="btn-secondary">キャンセル</a>
          <?php else: ?>
            <button type="submit" name="add_product" class="btn-primary">商品を追加</button>
          <?php endif; ?>
        </div>
      </form>
    </section>
  </div>

  <script src="js/main.js"></script>
</body>

</html>