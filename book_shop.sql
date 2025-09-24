-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- ホスト: 127.0.0.1
-- 生成日時: 2025-09-17 05:22:10
-- サーバのバージョン： 10.4.32-MariaDB
-- PHP のバージョン: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- データベース: `book_shop`
--

-- --------------------------------------------------------

--
-- テーブルの構造 `admin`
--

CREATE TABLE `admin` (
  `f_admin_id` int(11) NOT NULL COMMENT '管理者ID',
  `f_name` varchar(100) NOT NULL COMMENT '名前',
  `f_mail` varchar(255) NOT NULL COMMENT 'メール',
  `f_password` varchar(255) NOT NULL COMMENT 'パスワード'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='管理者';

--
-- テーブルのデータのダンプ `admin`
--

INSERT INTO `admin` (`f_admin_id`, `f_name`, `f_mail`, `f_password`) VALUES
(1, '山崎 葉音', 'hanon@book.com', 'book1234');

-- --------------------------------------------------------

--
-- テーブルの構造 `cart`
--

CREATE TABLE `cart` (
  `f_cart_id` int(11) NOT NULL COMMENT 'カートID',
  `f_user_id` int(11) NOT NULL COMMENT 'ユーザID',
  `f_product_id` int(11) NOT NULL COMMENT '商品ID',
  `f_quantity` int(11) NOT NULL DEFAULT 1 COMMENT '数量',
  `f_adding_time` datetime NOT NULL COMMENT '追加日時'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='カート';

-- --------------------------------------------------------

--
-- テーブルの構造 `category`
--

CREATE TABLE `category` (
  `f_category_id` int(11) NOT NULL COMMENT 'カテゴリーID',
  `f_category_name` varchar(50) NOT NULL COMMENT 'カテゴリー名'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='カテゴリー';

--
-- テーブルのデータのダンプ `category`
--

INSERT INTO `category` (`f_category_id`, `f_category_name`) VALUES
(101, '小説'),
(102, '漫画'),
(103, '恋愛漫画'),
(104, 'BL漫画');

-- --------------------------------------------------------

--
-- テーブルの構造 `delivery`
--

CREATE TABLE `delivery` (
  `f_delivery_id` int(11) NOT NULL COMMENT '配送ID',
  `f_order_id` int(11) NOT NULL COMMENT '注文ID',
  `f_order_info_status` varchar(50) NOT NULL COMMENT '注文情報ステータス',
  `f_delivery_date` date DEFAULT NULL COMMENT '配送日',
  `f_arrival_complete_date` date DEFAULT NULL COMMENT '到着完了日'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='配送';

--
-- テーブルのデータのダンプ `delivery`
--

INSERT INTO `delivery` (`f_delivery_id`, `f_order_id`, `f_order_info_status`, `f_delivery_date`, `f_arrival_complete_date`) VALUES
(1, 1, '準備中', NULL, NULL),
(2, 2, '準備中', NULL, NULL),
(3, 3, '準備中', NULL, NULL),
(4, 4, '準備中', NULL, NULL),
(5, 5, '準備中', NULL, NULL);

-- --------------------------------------------------------

--
-- テーブルの構造 `order_history`
--

CREATE TABLE `order_history` (
  `f_order_history_id` int(11) NOT NULL COMMENT '注文履歴ID',
  `f_order_id` int(11) NOT NULL COMMENT '注文ID',
  `f_user_id` int(11) NOT NULL COMMENT 'ユーザID',
  `f_order_status` varchar(50) NOT NULL COMMENT '注文ステータス'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='注文履歴';

--
-- テーブルのデータのダンプ `order_history`
--

INSERT INTO `order_history` (`f_order_history_id`, `f_order_id`, `f_user_id`, `f_order_status`) VALUES
(1, 1, 1, '注文確定'),
(2, 2, 1, '注文確定'),
(3, 3, 1, '注文確定'),
(4, 4, 1, '注文確定'),
(5, 5, 1, '注文確定');

-- --------------------------------------------------------

--
-- テーブルの構造 `order_info`
--

CREATE TABLE `order_info` (
  `f_order_id` int(11) NOT NULL COMMENT '注文ID',
  `f_user_id` int(11) NOT NULL COMMENT 'ユーザID',
  `f_total_amount` decimal(10,2) NOT NULL COMMENT '合計金額',
  `f_order_status` varchar(50) NOT NULL COMMENT '注文ステータス',
  `f_order_time` datetime NOT NULL COMMENT '注文日時'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='注文情報';

--
-- テーブルのデータのダンプ `order_info`
--

INSERT INTO `order_info` (`f_order_id`, `f_user_id`, `f_total_amount`, `f_order_status`, `f_order_time`) VALUES
(1, 1, 6442.00, '注文確定', '2025-09-17 11:10:04'),
(2, 1, 2159.00, '注文確定', '2025-09-17 11:10:47'),
(3, 1, 1270.00, '注文確定', '2025-09-17 11:11:58'),
(4, 1, 1747.00, '注文確定', '2025-09-17 11:13:18'),
(5, 1, 2017.00, '注文確定', '2025-09-17 11:23:58');

-- --------------------------------------------------------

--
-- テーブルの構造 `product`
--

CREATE TABLE `product` (
  `f_product_id` int(11) NOT NULL COMMENT '商品ID',
  `f_category_id` int(11) NOT NULL COMMENT 'カテゴリーID',
  `f_product_name` varchar(100) NOT NULL COMMENT '商品名',
  `f_product_photo` varchar(255) DEFAULT NULL COMMENT '商品画像',
  `f_product_price` decimal(10,2) NOT NULL COMMENT '商品価格',
  `f_writer_name` varchar(100) DEFAULT NULL COMMENT '作家名',
  `f_product_size` varchar(50) DEFAULT NULL COMMENT '大きさ',
  `f_release_date` date DEFAULT NULL COMMENT '発売日',
  `f_publisher` varchar(100) DEFAULT NULL COMMENT '出版社',
  `f_is_recommend` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='商品';

--
-- テーブルのデータのダンプ `product`
--

INSERT INTO `product` (`f_product_id`, `f_category_id`, `f_product_name`, `f_product_photo`, `f_product_price`, `f_writer_name`, `f_product_size`, `f_release_date`, `f_publisher`, `f_is_recommend`) VALUES
(1, 101, '夜が明けたら、いちばんに君に会いにいく', 'book01.jpg', 1320.00, '汐見 夏衛', '単行本 360ページ', '2017-06-01', 'スターツ出版', 1),
(2, 101, 'だから私は、明日のきみを描く', 'book02.jpg', 1247.00, '汐見 夏衛', '単行本 277ページ', '2018-01-25', 'スターツ出版', 0),
(3, 101, '雨上がり、君が映す空はきっと美しい', 'book03.jpg', 1373.00, '汐見 夏衛', '単行本 325ページ', '2021-10-25', 'スターツ出版', 0),
(4, 101, 'さよなら嘘つき人魚姫', 'book04.jpg', 1320.00, '汐見 夏衛', '単行本 351ページ', '2021-01-25', '一迅社', 0),
(9, 102, '東京エイリアンズ', 'comic01.jpg', 770.00, 'NAOE', 'コミック 188ページ', '2020-09-26', 'スクウェア・エニックス', 1),
(10, 102, '地縛少年花子くん', 'comic02.jpg', 770.00, 'あいだいろ', 'コミック 172ページ', '2015-05-22', 'スクウェア・エニックス', 0),
(13, 102, '岸部露伴は動かない', 'comic03.jpg', 506.00, '荒木飛呂彦', 'コミック 240ページ', '2013-11-19', '集英社', 0),
(14, 103, 'その着せ替え人形は恋をする', 'love-comic01.jpg', 770.00, '福田 晋一', 'コミック 199ページ', '2018-11-24', 'スクウェア・エニックス', 0),
(15, 103, '山田くんとLv999の恋をする', 'love-comic02.jpg', 693.00, 'ましろ', 'コミック 194ページ', '2020-02-21', 'KADOKAWA', 0),
(16, 103, 'ピンクとハバネロ', 'love-comic03.jpg', 484.00, '里中 実華', 'コミック 192ページ', '2022-02-25', '集英社', 0),
(17, 104, '鯛代くん君ってやつは', 'bl-comic01.jpg', 889.00, 'ヤマダ', 'コミック 223ページ', '2018-02-20', 'リブレ', 1),
(18, 104, '着飾るヒナはまだ恋を知らない', 'bl-comic02.jpg', 790.00, 'ざらめ鮫', 'コミック 170ページ', '2023-06-01', 'リブレ', 0),
(19, 104, '体感予報', 'bl-comic03.jpg', 922.00, '鯛野ニッケ', 'コミック 242ページ', '2022-11-10', 'リブレ', 0);

-- --------------------------------------------------------

--
-- テーブルの構造 `review`
--

CREATE TABLE `review` (
  `f_review_id` int(11) NOT NULL COMMENT 'レビューID',
  `f_user_id` int(11) NOT NULL COMMENT 'ユーザID',
  `f_product_id` int(11) NOT NULL COMMENT '商品ID',
  `f_evaluation` int(1) NOT NULL COMMENT '評価（1〜5）',
  `f_comment` text DEFAULT NULL COMMENT 'コメント',
  `f_post_time` datetime NOT NULL COMMENT '投稿日時'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='レビュー';

-- --------------------------------------------------------

--
-- テーブルの構造 `stock`
--

CREATE TABLE `stock` (
  `f_stock_id` int(11) NOT NULL COMMENT '在庫ID',
  `f_product_id` int(11) NOT NULL COMMENT '商品ID',
  `f_stock_quantity` int(11) NOT NULL DEFAULT 0 COMMENT '在庫数',
  `f_update_time` datetime NOT NULL COMMENT '更新日時'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='在庫';

-- --------------------------------------------------------

--
-- テーブルの構造 `user`
--

CREATE TABLE `user` (
  `f_user_id` int(11) NOT NULL COMMENT 'ユーザID',
  `f_user_name` varchar(100) NOT NULL COMMENT 'ユーザ名',
  `f_user_mail` varchar(255) NOT NULL COMMENT 'メールアドレス',
  `f_phone_number` varchar(20) DEFAULT NULL COMMENT '電話番号',
  `f_payment` varchar(50) DEFAULT NULL COMMENT '支払方法',
  `f_password` varchar(255) NOT NULL COMMENT 'パスワード',
  `f_purchase_history` varchar(255) DEFAULT NULL COMMENT '購入履歴',
  `f_address` varchar(255) DEFAULT NULL COMMENT '住所',
  `f_postal_code` varchar(10) DEFAULT NULL COMMENT '郵便番号',
  `f_registration_time` datetime NOT NULL COMMENT '登録日時',
  `f_delete` tinyint(1) NOT NULL DEFAULT 0 COMMENT '削除フラグ',
  `f_update` datetime DEFAULT NULL COMMENT '更新日時'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='ユーザ情報';

--
-- テーブルのデータのダンプ `user`
--

INSERT INTO `user` (`f_user_id`, `f_user_name`, `f_user_mail`, `f_phone_number`, `f_payment`, `f_password`, `f_purchase_history`, `f_address`, `f_postal_code`, `f_registration_time`, `f_delete`, `f_update`) VALUES
(1, '山崎 葉音', 'user@example.com', '090-0000-0000', 'クレジットカード', '$2y$10$6lsXDxSGNa1b9i/Iittmz.G3GjvMiLFwI41UId4Mv07wysVbRJzGe', NULL, '愛知県長久手市', '123-4567', '2025-09-16 13:46:42', 0, '2025-09-17 11:23:58'),
(2, 'A', 'example@example', NULL, NULL, '$2y$10$gSEd49x/XRSvsAqUS9DZ/uRTGof78UT7A0MFjwPjkGJz2cKX3tJjy', NULL, NULL, NULL, '2025-09-16 16:20:16', 0, NULL);

--
-- ダンプしたテーブルのインデックス
--

--
-- テーブルのインデックス `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`f_admin_id`),
  ADD UNIQUE KEY `f_mail` (`f_mail`);

--
-- テーブルのインデックス `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`f_cart_id`),
  ADD KEY `f_user_id` (`f_user_id`),
  ADD KEY `f_product_id` (`f_product_id`);

--
-- テーブルのインデックス `category`
--
ALTER TABLE `category`
  ADD PRIMARY KEY (`f_category_id`);

--
-- テーブルのインデックス `delivery`
--
ALTER TABLE `delivery`
  ADD PRIMARY KEY (`f_delivery_id`),
  ADD KEY `f_order_id` (`f_order_id`);

--
-- テーブルのインデックス `order_history`
--
ALTER TABLE `order_history`
  ADD PRIMARY KEY (`f_order_history_id`),
  ADD KEY `f_order_id` (`f_order_id`),
  ADD KEY `f_user_id` (`f_user_id`);

--
-- テーブルのインデックス `order_info`
--
ALTER TABLE `order_info`
  ADD PRIMARY KEY (`f_order_id`),
  ADD KEY `f_user_id` (`f_user_id`);

--
-- テーブルのインデックス `product`
--
ALTER TABLE `product`
  ADD PRIMARY KEY (`f_product_id`),
  ADD KEY `f_category_id` (`f_category_id`);

--
-- テーブルのインデックス `review`
--
ALTER TABLE `review`
  ADD PRIMARY KEY (`f_review_id`),
  ADD KEY `f_user_id` (`f_user_id`),
  ADD KEY `f_product_id` (`f_product_id`);

--
-- テーブルのインデックス `stock`
--
ALTER TABLE `stock`
  ADD PRIMARY KEY (`f_stock_id`),
  ADD KEY `f_product_id` (`f_product_id`);

--
-- テーブルのインデックス `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`f_user_id`),
  ADD UNIQUE KEY `f_user_mail` (`f_user_mail`);

--
-- ダンプしたテーブルの AUTO_INCREMENT
--

--
-- テーブルの AUTO_INCREMENT `admin`
--
ALTER TABLE `admin`
  MODIFY `f_admin_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '管理者ID', AUTO_INCREMENT=2;

--
-- テーブルの AUTO_INCREMENT `cart`
--
ALTER TABLE `cart`
  MODIFY `f_cart_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'カートID', AUTO_INCREMENT=32;

--
-- テーブルの AUTO_INCREMENT `category`
--
ALTER TABLE `category`
  MODIFY `f_category_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'カテゴリーID', AUTO_INCREMENT=105;

--
-- テーブルの AUTO_INCREMENT `delivery`
--
ALTER TABLE `delivery`
  MODIFY `f_delivery_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '配送ID', AUTO_INCREMENT=6;

--
-- テーブルの AUTO_INCREMENT `order_history`
--
ALTER TABLE `order_history`
  MODIFY `f_order_history_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '注文履歴ID', AUTO_INCREMENT=6;

--
-- テーブルの AUTO_INCREMENT `order_info`
--
ALTER TABLE `order_info`
  MODIFY `f_order_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '注文ID', AUTO_INCREMENT=6;

--
-- テーブルの AUTO_INCREMENT `product`
--
ALTER TABLE `product`
  MODIFY `f_product_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '商品ID', AUTO_INCREMENT=20;

--
-- テーブルの AUTO_INCREMENT `review`
--
ALTER TABLE `review`
  MODIFY `f_review_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'レビューID';

--
-- テーブルの AUTO_INCREMENT `stock`
--
ALTER TABLE `stock`
  MODIFY `f_stock_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '在庫ID';

--
-- テーブルの AUTO_INCREMENT `user`
--
ALTER TABLE `user`
  MODIFY `f_user_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ユーザID', AUTO_INCREMENT=3;

--
-- ダンプしたテーブルの制約
--

--
-- テーブルの制約 `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`f_user_id`) REFERENCES `user` (`f_user_id`),
  ADD CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`f_product_id`) REFERENCES `product` (`f_product_id`);

--
-- テーブルの制約 `delivery`
--
ALTER TABLE `delivery`
  ADD CONSTRAINT `delivery_ibfk_1` FOREIGN KEY (`f_order_id`) REFERENCES `order_info` (`f_order_id`);

--
-- テーブルの制約 `order_history`
--
ALTER TABLE `order_history`
  ADD CONSTRAINT `order_history_ibfk_1` FOREIGN KEY (`f_order_id`) REFERENCES `order_info` (`f_order_id`),
  ADD CONSTRAINT `order_history_ibfk_2` FOREIGN KEY (`f_user_id`) REFERENCES `user` (`f_user_id`);

--
-- テーブルの制約 `order_info`
--
ALTER TABLE `order_info`
  ADD CONSTRAINT `order_info_ibfk_1` FOREIGN KEY (`f_user_id`) REFERENCES `user` (`f_user_id`);

--
-- テーブルの制約 `product`
--
ALTER TABLE `product`
  ADD CONSTRAINT `product_ibfk_1` FOREIGN KEY (`f_category_id`) REFERENCES `category` (`f_category_id`);

--
-- テーブルの制約 `review`
--
ALTER TABLE `review`
  ADD CONSTRAINT `review_ibfk_1` FOREIGN KEY (`f_user_id`) REFERENCES `user` (`f_user_id`),
  ADD CONSTRAINT `review_ibfk_2` FOREIGN KEY (`f_product_id`) REFERENCES `product` (`f_product_id`);

--
-- テーブルの制約 `stock`
--
ALTER TABLE `stock`
  ADD CONSTRAINT `stock_ibfk_1` FOREIGN KEY (`f_product_id`) REFERENCES `product` (`f_product_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
