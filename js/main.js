// アコーディオンメニュー ----------------------------------------------------------------
$(".accodion .menu>li").find("ul").hide();

$(".accodion .menu>li").hover(
  function () {
    $(this).children(".sub-menu").stop().slideDown(500);
  },
  function () {
    $(this).children(".sub-menu").stop().slideUp(500);
  }
);

// AJAX カート追加機能
$(document).ready(function () {
  $(".add-to-cart-btn").on("click", function (e) {
    e.preventDefault();

    var $button = $(this);
    var productId = $button.data("product-id");

    // ボタンが無効化されている場合は処理しない
    if ($button.prop("disabled") || $button.hasClass("loading")) {
      return;
    }

    // ローディング状態にする
    var originalHtml = $button.html();
    $button.addClass("loading").prop("disabled", true);
    $button.html('<i class="fa-solid fa-spinner loading-spinner"></i>');

    // AJAX リクエスト
    $.ajax({
      url: "index.php",
      type: "POST",
      dataType: "json",
      data: {
        ajax_add_to_cart: 1,
        product_id: productId,
      },
      success: function (response) {
        if (response.success) {
          // 成功時の処理
          showMessage(response.message, false);
          updateCartCount(response.cart_count);

          // ボタンを成功状態にする
          $button.removeClass("loading").addClass("success");
          $button.html('<i class="fa-solid fa-check"></i>');

          // 2秒後に元の状態に戻す
          setTimeout(function () {
            $button.removeClass("success").prop("disabled", false);
            $button.html(originalHtml);
          }, 2000);
        } else {
          // エラー時の処理
          showMessage(response.message, true);
          $button.removeClass("loading").prop("disabled", false);
          $button.html(originalHtml);
        }
      },
      error: function () {
        showMessage("通信エラーが発生しました", true);
        $button.removeClass("loading").prop("disabled", false);
        $button.html(originalHtml);
      },
    });
  });

  // メッセージを表示する関数
  function showMessage(message, isError) {
    var $messageDiv = $("#cartMessage");
    $messageDiv.removeClass("show error");

    if (isError) {
      $messageDiv.addClass("error");
    }

    $messageDiv.html(
      '<i class="fa-solid ' +
        (isError ? "fa-exclamation-triangle" : "fa-check-circle") +
        '"></i> ' +
        message
    );
    $messageDiv.addClass("show");

    // 3秒後に非表示にする
    setTimeout(function () {
      $messageDiv.removeClass("show");
    }, 3000);
  }

  // カート数を更新する関数
  function updateCartCount(count) {
    var $cartCount = $("#cartCount");
    $cartCount.text(count);

    if (count > 0) {
      $cartCount.show().addClass("animate");
      setTimeout(function () {
        $cartCount.removeClass("animate");
      }, 600);
    } else {
      $cartCount.hide();
    }
  }
});

// スライドアウトアニメーション -------------------------------------------------------------
const style = document.createElement("style");
style.textContent = `
            @keyframes slideOut {
                to {
                    transform: translateX(100%);
                    opacity: 0;
                }
            }
        `;
document.head.appendChild(style);

// admin.phpのscript -----------------------------------------------------------
// メッセージ自動非表示
const message = document.querySelector(".message");
if (message) {
  setTimeout(() => {
    message.style.opacity = "0";
    setTimeout(() => message.remove(), 300);
  }, 4000);
}

// 商品追加/編集フォームバリデーション（要素が存在する場合のみ実行）
const addEditForm = document.querySelector("#addEditForm");
if (addEditForm) {
  addEditForm.addEventListener("submit", function (e) {
    const productName = addEditForm
      .querySelector('[name="product_name"]')
      .value.trim();
    const categoryId = addEditForm.querySelector('[name="category_id"]').value;
    const price = addEditForm.querySelector('[name="price"]').value;

    if (!productName || !categoryId || !price) {
      e.preventDefault();
      alert("必須項目を入力してください");
      return false;
    }

    if (parseFloat(price) < 0) {
      e.preventDefault();
      alert("価格は0以上で入力してください");
      return false;
    }
  });
}
