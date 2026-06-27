-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: db:3306
-- Generation Time: Jun 27, 2026 at 02:34 PM
-- Server version: 8.0.44
-- PHP Version: 8.3.30

SET FOREIGN_KEY_CHECKS=0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `laptop_shop_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `brands`
--

DROP TABLE IF EXISTS `brands`;
CREATE TABLE `brands` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `logo` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `brands`
--

INSERT INTO `brands` (`id`, `name`, `slug`, `logo`, `description`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Asus', 'asus', NULL, NULL, 1, '2026-04-23 08:45:21', '2026-04-23 08:45:21'),
(2, 'Dell', 'dell', NULL, NULL, 1, '2026-04-23 08:45:22', '2026-04-23 08:45:22'),
(3, 'HP', 'hp', NULL, NULL, 1, '2026-04-23 08:45:22', '2026-04-23 08:45:22'),
(4, 'Lenovo', 'lenovo', NULL, NULL, 1, '2026-04-23 08:45:22', '2026-04-23 08:45:22'),
(5, 'Acer', 'acer', NULL, NULL, 1, '2026-04-23 08:45:22', '2026-04-23 08:45:22'),
(6, 'MSI', 'msi', NULL, NULL, 1, '2026-04-23 08:45:22', '2026-04-23 08:45:22'),
(7, 'Apple', 'apple', NULL, NULL, 1, '2026-04-23 08:45:22', '2026-04-23 08:45:22'),
(8, 'LG', 'lg', NULL, NULL, 1, '2026-04-23 08:45:22', '2026-04-23 08:45:22');

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
CREATE TABLE `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `carts`
--

DROP TABLE IF EXISTS `carts`;
CREATE TABLE `carts` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `product_id` bigint UNSIGNED NOT NULL,
  `quantity` int NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
CREATE TABLE `categories` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `image` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `slug`, `description`, `image`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Laptop Gaming', 'gaming', NULL, NULL, 1, '2026-04-23 08:45:28', '2026-04-23 08:45:28'),
(2, 'Văn Phòng', 'van-phong', NULL, NULL, 1, '2026-04-23 08:45:28', '2026-04-23 08:45:28'),
(3, 'Đồ Họa', 'do-hoa', NULL, NULL, 1, '2026-04-23 08:45:28', '2026-04-23 08:45:28'),
(4, 'Mỏng Nhẹ', 'mong-nhe', NULL, NULL, 1, '2026-04-23 08:45:28', '2026-04-23 08:45:28'),
(5, 'Sinh Viên', 'sinh-vien', NULL, NULL, 1, '2026-04-23 08:45:28', '2026-04-23 08:45:28');

-- --------------------------------------------------------

--
-- Table structure for table `coupons`
--

DROP TABLE IF EXISTS `coupons`;
CREATE TABLE `coupons` (
  `id` bigint UNSIGNED NOT NULL,
  `code` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` enum('percent','fixed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'percent',
  `value` decimal(10,2) NOT NULL,
  `min_order` decimal(15,0) NOT NULL DEFAULT '0',
  `max_discount` decimal(15,0) DEFAULT NULL,
  `usage_limit` int DEFAULT NULL,
  `usage_count` int NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `starts_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `coupons`
--

INSERT INTO `coupons` (`id`, `code`, `type`, `value`, `min_order`, `max_discount`, `usage_limit`, `usage_count`, `is_active`, `starts_at`, `expires_at`, `created_at`, `updated_at`) VALUES
(2, 'SINHVIEN10', 'percent', 10.00, 0, NULL, NULL, 0, 1, '2026-06-10 15:00:00', '2026-07-10 00:57:00', '2026-06-10 20:57:59', '2026-06-10 21:11:48'),
(3, 'SINHVIEN5', 'percent', 5.00, 0, NULL, NULL, 0, 1, '2026-06-20 10:45:00', '2026-07-12 10:45:00', '2026-06-21 03:45:38', '2026-06-21 03:45:38'),
(4, 'SINHVIEN20', 'percent', 20.00, 0, NULL, NULL, 1, 1, '2026-06-20 10:45:00', '2026-07-12 10:45:00', '2026-06-21 03:45:58', '2026-06-21 04:03:35');

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
CREATE TABLE `failed_jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

DROP TABLE IF EXISTS `jobs`;
CREATE TABLE `jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint UNSIGNED NOT NULL,
  `reserved_at` int UNSIGNED DEFAULT NULL,
  `available_at` int UNSIGNED NOT NULL,
  `created_at` int UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

DROP TABLE IF EXISTS `job_batches`;
CREATE TABLE `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
CREATE TABLE `migrations` (
  `id` int UNSIGNED NOT NULL,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2026_04_23_082127_create_personal_access_tokens_table', 1),
(5, '2026_04_23_084038_add_role_to_users_table', 2),
(6, '2026_04_23_084356_create_laptop_shop_tables', 3);

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

DROP TABLE IF EXISTS `orders`;
CREATE TABLE `orders` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `order_code` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_amount` decimal(15,0) NOT NULL,
  `discount_amount` decimal(15,0) NOT NULL DEFAULT '0',
  `final_amount` decimal(15,0) NOT NULL,
  `status` enum('pending','confirmed','processing','shipped','delivered','cancelled','refunded') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `payment_method` enum('cod','vnpay','bank_transfer') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'cod',
  `payment_status` enum('unpaid','paid','refunded') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'unpaid',
  `vnpay_txn_ref` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `shipping_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `shipping_phone` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `shipping_address` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `shipping_city` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `note` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `order_code`, `total_amount`, `discount_amount`, `final_amount`, `status`, `payment_method`, `payment_status`, `vnpay_txn_ref`, `shipping_name`, `shipping_phone`, `shipping_address`, `shipping_city`, `note`, `created_at`, `updated_at`) VALUES
(1, 3, 'ORD-31TQBWL7NP', 19000000, 0, 19000000, 'delivered', 'vnpay', 'paid', 'ORD-31TQBWL7NP_1780745557', 'minhadcarry', '0123456789', 'số nhà 39 nghách 41 thịnh quang tây sơn', 'Hà Nội', NULL, '2026-06-06 11:32:33', '2026-06-08 17:00:27'),
(3, 3, 'ORD-T4NYDHNIH7', 23000000, 4600000, 18400000, 'pending', 'vnpay', 'unpaid', 'ORD-T4NYDHNIH7_1782014618', 'minhadcarry', '0123456789', '39 NGÁCH 41', 'Bắc Ninh', NULL, '2026-06-21 04:03:35', '2026-06-24 20:00:49');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

DROP TABLE IF EXISTS `order_items`;
CREATE TABLE `order_items` (
  `id` bigint UNSIGNED NOT NULL,
  `order_id` bigint UNSIGNED NOT NULL,
  `product_id` bigint UNSIGNED NOT NULL,
  `product_name` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `product_sku` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `quantity` int NOT NULL,
  `unit_price` decimal(15,0) NOT NULL,
  `total_price` decimal(15,0) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `product_name`, `product_sku`, `quantity`, `unit_price`, `total_price`, `created_at`) VALUES
(1, 1, 2, 'Dell 15 DC15250 i7U161W11SLU', 'Dell-DC15-001', 1, 19000000, 19000000, '2026-06-06 11:32:33'),
(3, 3, 5, 'ASUS TUF Gaming F16 FX607VU-RL045W', 'ASUS-TUF-F16-001', 1, 23000000, 23000000, '2026-06-21 04:03:35');

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `password_reset_tokens`
--

INSERT INTO `password_reset_tokens` (`email`, `token`, `created_at`) VALUES
('minhadcarry@gmail.com', '$2y$12$NakSxiA1y.WK5qE6.DwaVOM1F90jFHOrN4zgY2gNG6FDfE9JdfA5W', '2026-04-23 16:50:58');

-- --------------------------------------------------------

--
-- Table structure for table `personal_access_tokens`
--

DROP TABLE IF EXISTS `personal_access_tokens`;
CREATE TABLE `personal_access_tokens` (
  `id` bigint UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint UNSIGNED NOT NULL,
  `name` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `personal_access_tokens`
--

INSERT INTO `personal_access_tokens` (`id`, `tokenable_type`, `tokenable_id`, `name`, `token`, `abilities`, `last_used_at`, `expires_at`, `created_at`, `updated_at`) VALUES
(25, 'App\\Models\\User', 4, 'auth_token', 'cc0b780e5b1dfcf32291423a93d88de8df28e5e35b7c7606ffac78ba134e7c60', '[\"*\"]', NULL, '2026-05-28 06:40:08', '2026-04-28 06:40:08', '2026-04-28 06:40:08'),
(59, 'App\\Models\\User', 1, 'auth_token', 'c6b15a66998c59378621ee5585f238461842ac7515162fb8ae1bbc7922d93419', '[\"*\"]', '2026-06-27 14:22:20', '2026-07-27 09:11:14', '2026-06-27 09:11:14', '2026-06-27 14:22:20');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

DROP TABLE IF EXISTS `products`;
CREATE TABLE `products` (
  `id` bigint UNSIGNED NOT NULL,
  `category_id` bigint UNSIGNED NOT NULL,
  `brand_id` bigint UNSIGNED NOT NULL,
  `name` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sku` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` longtext COLLATE utf8mb4_unicode_ci,
  `price` decimal(15,0) NOT NULL,
  `sale_price` decimal(15,0) DEFAULT NULL,
  `stock` int NOT NULL DEFAULT '0',
  `thumbnail` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cpu` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ram` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `storage` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `display` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gpu` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `os` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `battery` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `weight` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `is_featured` tinyint(1) NOT NULL DEFAULT '0',
  `views` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `category_id`, `brand_id`, `name`, `slug`, `sku`, `description`, `price`, `sale_price`, `stock`, `thumbnail`, `cpu`, `ram`, `storage`, `display`, `gpu`, `os`, `battery`, `weight`, `is_active`, `is_featured`, `views`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'Asus ROG  Strix G15', 'asus-rog-strix-g15-vISpU0', 'ASUS-ROG-G15-001', 'Sản phẩm laptop gaming chơi game cực mạnh đáp ứng mọi loại game', 20000000, NULL, 10, '/storage/products/1/tOHM6CADTNFEKmACktKF3sHZWvAOCpMU8cCYtapT.png', 'Intel core i5-13900h', '16GB DDR5', '512GB SSD NVMe', '15.6 inch FHD 144Hz', 'NVIDIA RTX 4060 8GB', 'Windown 11 Home', '90WH', '2.0kg', 1, 1, 21, '2026-04-28 08:38:39', '2026-06-10 20:55:08'),
(2, 1, 2, 'Dell 15 DC15250 i7U161W11SLU', 'dell-15-dc15250-i7u161w11slu-HfvqYO', 'Dell-DC15-001', 'Laptop dell cấu hình mạnh dùng để học tập làm việc và chơi game FPS cao', 19000000, NULL, 9, 'http://localhost:8000/storage/products/2/VLMq18YomG6wgLfIV9EKf1ePXdcUAlKu2FuPX4uS.png', 'Intel Core i7-1355U', '16GB', '512GB SSD NVMe', '15.6 inch FHD 144Hz', 'NVIDIA RTX 4060 8GB', 'Windown 11 Home', '90WH', '2.1kg', 1, 1, 2, '2026-05-10 19:55:08', '2026-06-10 22:39:23'),
(3, 1, 1, 'ASUS ROG Strix G16 G614PH-S5101W', 'asus-rog-strix-g16-g614ph-s5101w-4Ovt3Q', 'ASUS-ROG-G16-001', NULL, 21000000, NULL, 10, 'http://localhost:8000/storage/products/3/tk4b6J3rLR6l98MwBDhARifAjP6fREdwcwz3hCB8.webp', 'Intel core i7-11700H', '16GB', '512GB PCIe 4.0 NVMe M.2 SSD', '16 inches', 'NVIDIA GeForce RTX 5050 8GB GDDR7', 'Windown 11 Home', '100Wh', '2.0kg', 1, 1, 2, '2026-06-07 14:05:35', '2026-06-10 22:06:03'),
(4, 1, 1, 'ASUS Gaming V16 V3607VU-RP343W', 'asus-gaming-v16-v3607vu-rp343w-dE9ypY', 'ASUS-V1- V3607VU-RP343W', 'Sản phẩm chơi game siêu mạnh', 25000000, NULL, 10, 'http://localhost:8000/storage/products/4/Q3agdysIVourwYZaFdxPyHeldpzXLfoIE1s5Coyf.webp', 'Intel Graphics', '16GB DDR5', '512GB', '16 inches 144hz', 'NVIDIA GeForce RTX 4050 6GB GDDR6', 'Windown 10 Pro', '90Wh', '1.8kg', 1, 1, 4, '2026-06-07 14:09:58', '2026-06-18 23:56:50'),
(5, 1, 1, 'ASUS TUF Gaming F16 FX607VU-RL045W', 'asus-tuf-gaming-f16-fx607vu-rl045w-8j1gTi', 'ASUS-TUF-F16-001', NULL, 23000000, NULL, 9, 'http://localhost:8000/storage/products/5/udiE0nBPgylr3HHTCVRGVWT1NWBre71PVNukHNaw.webp', 'Intel core i7-11700H', '16GB DDR5', '512GB PCIe 4.0 NVMe M.2 SSD', '16 inches', 'NVIDIA GeForce RTX 4050 6GB GDDR6', NULL, '100Wh', '2.0kg', 1, 1, 5, '2026-06-07 14:13:19', '2026-06-21 04:03:35'),
(6, 4, 7, 'MacBook Neo 13', 'macbook-neo-13-ZqPYaS', 'MacBook-Neo -3-001', 'Mac neo 13 siêu mỏng nhẹ', 19500000, NULL, 10, 'http://localhost:8000/storage/products/6/VidgLNZfGuvGJf9dVKOfIE4KLTTGdbRw7bRDyloh.webp', 'Chip Apple A18 Pro', '8GB', '256GB', 'Màn hình Liquid Retina có led', 'GPU 5 lõi Neural Engine 16 lõi', 'Windown 11 pro', '90Wh', '1.5kg', 1, 0, 0, '2026-06-27 12:53:42', '2026-06-27 12:53:46'),
(7, 4, 7, 'MacBook Air M5', 'macbook-air-m5-Q1Apb5', 'MacBook-Air-M5-001', 'MacBook Air M5 siêu đẹp siêu khỏe nhỏ gọn', 35000000, NULL, 10, 'http://localhost:8000/storage/products/7/8xiZoeMw5zreEdB3cAhVmJ0KlQyteZv58uS7Lh9m.webp', 'Chip Apple M5', '16GB', '512GB', 'Màn hình Liquid Retina 13.6 inches', 'Neural Engine 16', 'Windowns 11 Home', '90Wh', '1.5kg', 1, 1, 0, '2026-06-27 12:58:00', '2026-06-27 12:58:05'),
(8, 4, 7, 'MacBook Pro 14 M5', 'macbook-pro-14-m5-dZDNoa', 'MacBook-Pro-14-M5 -001', 'MacBook Pro 14 M5 chính hãng siêu mạnh', 49990000, NULL, 10, 'http://localhost:8000/storage/products/8/QxSKA4SOzSYubvJLSB0govt1Omq1HpBEQL2okvMR.webp', 'Chip Apple M5 10', '16GB', '512GB', '14.2 inches', 'GPU 10 lõi Neural Engine 16 lõi', 'macOS', '100Wh', '1.5kg', 1, 1, 0, '2026-06-27 13:00:39', '2026-06-27 13:00:42'),
(9, 4, 7, 'Apple MacBook Air M2 2024', 'apple-macbook-air-m2-2024-w8mqH3', 'MacBook-Air-M2-001', 'Apple MacBook Air M2 2024 8CPU 8GPU 16GB 256GB I Chính hãng Apple Việt Nam', 18950000, NULL, 10, 'http://localhost:8000/storage/products/9/1FXZWdRKRTxuYezdHjS1CQRsbVxaJ5qGDEMIywgo.webp', 'Apple M2 8 nhân', '16GB', '256GB', '13.6 inches', '8 nhân GPU, 16 nhân Neural Engine', 'MacOS', '90wh', '1.5kg', 1, 1, 0, '2026-06-27 13:09:40', '2026-06-27 13:09:44'),
(10, 4, 7, 'MacBook Air M2 2022', 'macbook-air-m2-2022-Q3uf65', 'MacBook-Air-M2-2022-001', 'MacBook Air M2 2024 8CPU 10 GPU 16GB 512GB Sạc 35W - Cũ Xước Cấn', 22000000, NULL, 10, 'http://localhost:8000/storage/products/10/evap5XstBvE0ZstDdIIICjW4OflGEvCXfX20PaOc.webp', 'Apple M2 8 nhân', '16GB', NULL, '13.6 inchesv', '16 nhân Neural Engine', 'MacOS', '60wh', '2.4kg', 1, 0, 1, '2026-06-27 13:12:09', '2026-06-27 13:29:53'),
(11, 3, 4, 'Laptop Lenovo LOQ 15ARP10', 'laptop-lenovo-loq-15arp10-g94NDe', 'Laptop-Lenovo-LOQ-15ARP10-001', NULL, 25990000, NULL, 10, 'http://localhost:8000/storage/products/11/EsekHHMoRGqwDWODWj8xWHNXW4U9KD0JvxHMWKMK.webp', 'AMD Ryzen 7 7735HS', '16GB', '512GB SSD M.2 2242', '15.6 inches', 'NVIDIA GeForce RTX 3050 6GB GDDR6', 'Windows 11 Home', '90Wh', '2.0kg', 1, 1, 0, '2026-06-27 13:15:10', '2026-06-27 13:15:13'),
(12, 3, 4, 'Laptop Lenovo IdeaPad Slim 5', 'laptop-lenovo-ideapad-slim-5-MMSgQc', 'Laptop-Lenovo-IdeaPad-Slim-5-001', 'Laptop Lenovo IdeaPad Slim 5 14IMH10 83V6001HVN\nĐồ họa cực cao', 29490000, NULL, 10, 'http://localhost:8000/storage/products/12/WNNc0XYfYtijQtpXrpZ4SOfeXSngSmEW9JtWQS4p.webp', 'Intel Core Ultra 5 135H', 'DDR5-5600 32GB', '512GB SSD M.2 2242', '14 inches', 'Intel Arc Graphics', 'Windows 11 Home', '60wh', '2.1kg', 1, 1, 0, '2026-06-27 13:18:32', '2026-06-27 13:18:36'),
(13, 3, 4, 'Laptop Lenovo Legion 5 15AHP11', 'laptop-lenovo-legion-5-15ahp11-zy2s7k', 'Laptop-Lenovo-Legion 5-001', NULL, 55990000, NULL, 10, 'http://localhost:8000/storage/products/13/NSxDUW8Th3izH2MDmt0oWUGTLJDzN1za2k2boihh.webp', 'AMD Ryzen 7 250', '16GB', '512GB SSD M.2 2242', NULL, 'NVIDIA GeForce RTX 5060 8GB GDDR7', 'Windows 11 Home', '90wh', '2.0', 1, 1, 0, '2026-06-27 13:32:20', '2026-06-27 13:32:24'),
(14, 3, 4, 'Laptop Lenovo LOQ 15AHP11', 'laptop-lenovo-loq-15ahp11-QZScEj', 'Lenovo-LOQ -5AHP11-001', NULL, 41900000, NULL, 10, 'http://localhost:8000/storage/products/14/wOOlU9oHw6vCN45R5qwDQedruZVlWyzjEdw2XlDM.webp', 'AMD Ryzen 7 250', 'DDR5-5600', '512GB SSD M.2 2242', '15.3 inches', 'NVIDIA GeForce RTX 5050 8GB GDDR7,', 'Windows 11 Home', '90wh', '2.2kg', 1, 1, 0, '2026-06-27 13:36:16', '2026-06-27 13:36:18'),
(15, 5, 6, 'Laptop MSI Cyborg 15', 'laptop-msi-cyborg-15-mGHAVv', 'Laptop MSI Cyborg 15', NULL, 25000000, NULL, 10, 'http://localhost:8000/storage/products/15/15f6ta3txbXi9EAVJT2myLSpK0tft6VecSp6IEpL.webp', 'Intel Core i7-13620H', 'DDR5 5600', '512GB NVMe PCIe Gen4', '15.6 inches', 'NVIDIA GeForce RTX 3050 4GB', 'Windows 11 Home', '90wh', '2.5kg', 1, 1, 0, '2026-06-27 13:39:04', '2026-06-27 13:39:08'),
(16, 5, 6, 'Laptop MSI Stealth 16', 'laptop-msi-stealth-16-cOHcCv', 'MSI-Stealth-16-001', NULL, 84590000, NULL, 10, 'http://localhost:8000/storage/products/16/xH9Moy7ZZYZAdPC9xqpKtEgV0WnddFmBbwAac2l4.webp', 'Intel Core Ultra 9 386H', 'DDR5-7200 32GB', '1TB*1, 2x M.2 SSD slot', '16 inches', 'NVIDIA GeForce RTX 5070', 'Windows 11 Home', '90wh', '2.0kg', 1, 0, 0, '2026-06-27 13:45:54', '2026-06-27 13:45:57'),
(17, 5, 6, 'Laptop MSI Modern 14', 'laptop-msi-modern-14-M5keZJ', 'MSI-Modern-14-001', NULL, 14900000, NULL, 10, 'http://localhost:8000/storage/products/17/ZukW99qrYTAjbfe3G6EB4fQ2qiQfyQNhDtdM24VX.webp', 'I7-1355U', '16GB', '512GB M.2 NVMe', '14 inches', 'Intel Iris Xe Graphics', 'Windows 11 Home', '90wh', '2.0kg', 1, 0, 0, '2026-06-27 13:59:20', '2026-06-27 13:59:24'),
(18, 5, 6, 'Laptop MSI Modern 14 F13MG', 'laptop-msi-modern-14-f13mg-4FvIEV', 'MSI-Modern-14-F13MG-001', NULL, 16900000, NULL, 10, 'http://localhost:8000/storage/products/18/BmOlQHj0VNVhnc4e9KsFfXfU4mnJaSzgDjoCIkKB.webp', 'Intel Core i5-1335U', 'DDR4-3200 16GB', '512GB SSD NVMe', NULL, 'Intel Graphics', NULL, '90wh', '2.0kg', 1, 0, 0, '2026-06-27 14:01:00', '2026-06-27 14:01:02'),
(19, 2, 5, 'Laptop Acer Gaming Aspire 7', 'laptop-acer-gaming-aspire-7-DUaH8N', 'Ace-Gaming-Aspire-7-001', NULL, 20900000, NULL, 10, 'http://localhost:8000/storage/products/19/KzVtbGzBBXxnDVaqMErS2fHhEtnQ2vhSvryvwDB6.webp', 'Intel Core i5-12450H', '16GB', '512GB PCIe NVMe SSD', '15.6 inches', 'NVIDIA GeForce RTX 3050', 'Windows 11 Home', '90wh', '2.0.kg', 1, 0, 0, '2026-06-27 14:18:52', '2026-06-27 14:18:56'),
(20, 2, 5, 'Laptop Acer Aspire Lite 16', 'laptop-acer-aspire-lite-16-1LvQgJ', 'Acer-Aspire-Lite-16-001', NULL, 18000000, NULL, 10, 'http://localhost:8000/storage/products/20/oxACThoVfiTXhiQrdFeuFjERBp3gANXNAXGIVrcf.webp', 'Intel Core i7 Raptor Lake - 1355U', 'DDR5 4800 MHz16GB', '512 GB SSD NVMe PCIe', '16 inches', 'ntel Iris Xe Graphics', 'Windows 11 Home SL', '90wh', '2.2kg', 1, 0, 0, '2026-06-27 14:22:15', '2026-06-27 14:22:19');

-- --------------------------------------------------------

--
-- Table structure for table `product_images`
--

DROP TABLE IF EXISTS `product_images`;
CREATE TABLE `product_images` (
  `id` bigint UNSIGNED NOT NULL,
  `product_id` bigint UNSIGNED NOT NULL,
  `image_path` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `alt_text` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_primary` tinyint(1) NOT NULL DEFAULT '0',
  `sort_order` int NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `product_images`
--

INSERT INTO `product_images` (`id`, `product_id`, `image_path`, `alt_text`, `is_primary`, `sort_order`, `created_at`) VALUES
(2, 1, 'http://localhost:8000/storage/products/1/tOHM6CADTNFEKmACktKF3sHZWvAOCpMU8cCYtapT.png', NULL, 0, 1, '2026-04-28 16:42:13'),
(3, 2, 'http://localhost:8000/storage/products/2/VLMq18YomG6wgLfIV9EKf1ePXdcUAlKu2FuPX4uS.png', NULL, 1, 0, '2026-05-10 19:55:12'),
(4, 3, 'http://localhost:8000/storage/products/3/tk4b6J3rLR6l98MwBDhARifAjP6fREdwcwz3hCB8.webp', NULL, 1, 0, '2026-06-07 14:05:40'),
(5, 4, 'http://localhost:8000/storage/products/4/Q3agdysIVourwYZaFdxPyHeldpzXLfoIE1s5Coyf.webp', NULL, 1, 0, '2026-06-07 14:10:03'),
(6, 5, 'http://localhost:8000/storage/products/5/udiE0nBPgylr3HHTCVRGVWT1NWBre71PVNukHNaw.webp', NULL, 1, 0, '2026-06-07 14:13:24'),
(7, 6, 'http://localhost:8000/storage/products/6/VidgLNZfGuvGJf9dVKOfIE4KLTTGdbRw7bRDyloh.webp', NULL, 1, 0, '2026-06-27 12:53:46'),
(8, 7, 'http://localhost:8000/storage/products/7/8xiZoeMw5zreEdB3cAhVmJ0KlQyteZv58uS7Lh9m.webp', NULL, 1, 0, '2026-06-27 12:58:05'),
(9, 8, 'http://localhost:8000/storage/products/8/QxSKA4SOzSYubvJLSB0govt1Omq1HpBEQL2okvMR.webp', NULL, 1, 0, '2026-06-27 13:00:42'),
(10, 9, 'http://localhost:8000/storage/products/9/1FXZWdRKRTxuYezdHjS1CQRsbVxaJ5qGDEMIywgo.webp', NULL, 1, 0, '2026-06-27 13:09:44'),
(11, 10, 'http://localhost:8000/storage/products/10/evap5XstBvE0ZstDdIIICjW4OflGEvCXfX20PaOc.webp', NULL, 1, 0, '2026-06-27 13:12:13'),
(12, 11, 'http://localhost:8000/storage/products/11/EsekHHMoRGqwDWODWj8xWHNXW4U9KD0JvxHMWKMK.webp', NULL, 1, 0, '2026-06-27 13:15:13'),
(13, 12, 'http://localhost:8000/storage/products/12/WNNc0XYfYtijQtpXrpZ4SOfeXSngSmEW9JtWQS4p.webp', NULL, 1, 0, '2026-06-27 13:18:36'),
(14, 13, 'http://localhost:8000/storage/products/13/NSxDUW8Th3izH2MDmt0oWUGTLJDzN1za2k2boihh.webp', NULL, 1, 0, '2026-06-27 13:32:24'),
(15, 14, 'http://localhost:8000/storage/products/14/wOOlU9oHw6vCN45R5qwDQedruZVlWyzjEdw2XlDM.webp', NULL, 1, 0, '2026-06-27 13:36:18'),
(16, 15, 'http://localhost:8000/storage/products/15/15f6ta3txbXi9EAVJT2myLSpK0tft6VecSp6IEpL.webp', NULL, 1, 0, '2026-06-27 13:39:08'),
(17, 16, 'http://localhost:8000/storage/products/16/xH9Moy7ZZYZAdPC9xqpKtEgV0WnddFmBbwAac2l4.webp', NULL, 1, 0, '2026-06-27 13:45:57'),
(18, 17, 'http://localhost:8000/storage/products/17/ZukW99qrYTAjbfe3G6EB4fQ2qiQfyQNhDtdM24VX.webp', NULL, 1, 0, '2026-06-27 13:59:24'),
(19, 18, 'http://localhost:8000/storage/products/18/BmOlQHj0VNVhnc4e9KsFfXfU4mnJaSzgDjoCIkKB.webp', NULL, 1, 0, '2026-06-27 14:01:02'),
(20, 19, 'http://localhost:8000/storage/products/19/KzVtbGzBBXxnDVaqMErS2fHhEtnQ2vhSvryvwDB6.webp', NULL, 1, 0, '2026-06-27 14:18:56'),
(21, 20, 'http://localhost:8000/storage/products/20/oxACThoVfiTXhiQrdFeuFjERBp3gANXNAXGIVrcf.webp', NULL, 1, 0, '2026-06-27 14:22:19');

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

DROP TABLE IF EXISTS `reviews`;
CREATE TABLE `reviews` (
  `id` bigint UNSIGNED NOT NULL,
  `product_id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `order_id` bigint UNSIGNED DEFAULT NULL,
  `rating` tinyint NOT NULL,
  `comment` text COLLATE utf8mb4_unicode_ci,
  `is_approved` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('admin','user') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'user',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` text COLLATE utf8mb4_unicode_ci,
  `avatar` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `role`, `is_active`, `phone`, `address`, `avatar`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'Administrator', 'admin@laptopshop.com', 'admin', 1, NULL, NULL, NULL, NULL, '$2y$12$P4CCvWUhX9eLdxxuoEB8g.XaAxyWiaWvGLsIm5M0CbUHXPz4Uay5y', NULL, '2026-04-23 08:41:43', '2026-04-23 08:41:43'),
(3, 'minhadcarry', 'minhadcarry@gmail.com', 'user', 1, '0123456789', NULL, NULL, NULL, '$2y$12$bepA5VLqqdhOaqL1CcxF1exxy43tmLx7ygQgON5cKfp72fjJQVQC2', NULL, '2026-04-23 15:13:29', '2026-04-23 15:13:29'),
(4, 'minhtopcarry', 'minhtopcarry@gmail.com', 'user', 1, '0912345678', NULL, NULL, NULL, '$2y$12$PbqAHVvNUsHYhEctYK7uO.10fEuHcnk3CTG6/yqvs.xM6q5vwcmaK', NULL, '2026-04-28 06:40:08', '2026-04-28 06:40:08'),
(5, 'minhmidcarry', 'minhmidcarry@gmail.com', 'user', 1, '0912346784', NULL, NULL, NULL, '$2y$12$ZfMp9VmYj9BGoNDxrpo4bunA8u0EyWyW5oWOBmAK8NgBpbfx2gyDe', NULL, '2026-06-07 07:31:05', '2026-06-19 06:56:06');

-- --------------------------------------------------------

--
-- Table structure for table `wishlists`
--

DROP TABLE IF EXISTS `wishlists`;
CREATE TABLE `wishlists` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `product_id` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `wishlists`
--

INSERT INTO `wishlists` (`id`, `user_id`, `product_id`, `created_at`) VALUES
(1, 1, 1, '2026-04-28 09:19:59'),
(10, 1, 5, '2026-06-21 03:55:19');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `brands`
--
ALTER TABLE `brands`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `brands_slug_unique` (`slug`);

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `carts`
--
ALTER TABLE `carts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `carts_user_id_product_id_unique` (`user_id`,`product_id`),
  ADD KEY `carts_product_id_foreign` (`product_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `categories_slug_unique` (`slug`);

--
-- Indexes for table `coupons`
--
ALTER TABLE `coupons`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `coupons_code_unique` (`code`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indexes for table `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `orders_order_code_unique` (`order_code`),
  ADD KEY `orders_status_index` (`status`),
  ADD KEY `orders_user_id_index` (`user_id`),
  ADD KEY `orders_payment_status_index` (`payment_status`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_items_product_id_foreign` (`product_id`),
  ADD KEY `order_items_order_id_index` (`order_id`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`),
  ADD KEY `personal_access_tokens_expires_at_index` (`expires_at`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `products_slug_unique` (`slug`),
  ADD UNIQUE KEY `products_sku_unique` (`sku`),
  ADD KEY `products_price_index` (`price`),
  ADD KEY `products_brand_id_index` (`brand_id`),
  ADD KEY `products_category_id_index` (`category_id`),
  ADD KEY `products_is_active_index` (`is_active`);

--
-- Indexes for table `product_images`
--
ALTER TABLE `product_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_images_product_id_index` (`product_id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `reviews_product_id_user_id_order_id_unique` (`product_id`,`user_id`,`order_id`),
  ADD KEY `reviews_user_id_foreign` (`user_id`),
  ADD KEY `reviews_order_id_foreign` (`order_id`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- Indexes for table `wishlists`
--
ALTER TABLE `wishlists`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `wishlists_user_id_product_id_unique` (`user_id`,`product_id`),
  ADD KEY `wishlists_product_id_foreign` (`product_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `brands`
--
ALTER TABLE `brands`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `carts`
--
ALTER TABLE `carts`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `coupons`
--
ALTER TABLE `coupons`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=60;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `product_images`
--
ALTER TABLE `product_images`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `wishlists`
--
ALTER TABLE `wishlists`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `carts`
--
ALTER TABLE `carts`
  ADD CONSTRAINT `carts_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `carts_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE RESTRICT;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_brand_id_foreign` FOREIGN KEY (`brand_id`) REFERENCES `brands` (`id`) ON DELETE RESTRICT,
  ADD CONSTRAINT `products_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE RESTRICT;

--
-- Constraints for table `product_images`
--
ALTER TABLE `product_images`
  ADD CONSTRAINT `product_images_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `reviews_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reviews_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `wishlists`
--
ALTER TABLE `wishlists`
  ADD CONSTRAINT `wishlists_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `wishlists_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
SET FOREIGN_KEY_CHECKS=1;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
