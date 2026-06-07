-- =============================================================
--  MySQL Initialization Script - Laptop Shop DATN
--  Chạy tự động khi container MySQL khởi động lần đầu
-- =============================================================

-- Tạo database chính
CREATE DATABASE IF NOT EXISTS laptop_shop_db
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

-- Tạo user và phân quyền
CREATE USER IF NOT EXISTS 'laptop_user'@'%' IDENTIFIED BY 'laptop_password_2024';
GRANT ALL PRIVILEGES ON laptop_shop_db.* TO 'laptop_user'@'%';
FLUSH PRIVILEGES;

-- Sử dụng database
USE laptop_shop_db;

-- =============================================================
--  BẢNG: users (Quản lý người dùng + Admin)
-- =============================================================
CREATE TABLE IF NOT EXISTS users (
    id            BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name          VARCHAR(255) NOT NULL,
    email         VARCHAR(255) NOT NULL UNIQUE,
    password      VARCHAR(255) NOT NULL,
    role          ENUM('admin', 'user') NOT NULL DEFAULT 'user',
    phone         VARCHAR(20) NULL,
    address       TEXT NULL,
    avatar        VARCHAR(500) NULL,
    is_active     TINYINT(1) NOT NULL DEFAULT 1,
    email_verified_at TIMESTAMP NULL,
    remember_token VARCHAR(100) NULL,
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================
--  BẢNG: personal_access_tokens (Laravel Sanctum)
-- =============================================================
CREATE TABLE IF NOT EXISTS personal_access_tokens (
    id            BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tokenable_type VARCHAR(255) NOT NULL,
    tokenable_id  BIGINT UNSIGNED NOT NULL,
    name          VARCHAR(255) NOT NULL,
    token         VARCHAR(64) NOT NULL UNIQUE,
    abilities     TEXT NULL,
    last_used_at  TIMESTAMP NULL,
    expires_at    TIMESTAMP NULL,
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_tokenable (tokenable_type, tokenable_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================
--  BẢNG: categories (Danh mục sản phẩm)
-- =============================================================
CREATE TABLE IF NOT EXISTS categories (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name          VARCHAR(255) NOT NULL,
    slug          VARCHAR(255) NOT NULL UNIQUE,
    description   TEXT NULL,
    image         VARCHAR(500) NULL,
    is_active     TINYINT(1) NOT NULL DEFAULT 1,
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================
--  BẢNG: brands (Hãng laptop)
-- =============================================================
CREATE TABLE IF NOT EXISTS brands (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name          VARCHAR(100) NOT NULL,
    slug          VARCHAR(100) NOT NULL UNIQUE,
    logo          VARCHAR(500) NULL,
    description   TEXT NULL,
    is_active     TINYINT(1) NOT NULL DEFAULT 1,
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================
--  BẢNG: products (Sản phẩm Laptop)
-- =============================================================
CREATE TABLE IF NOT EXISTS products (
    id            BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    category_id   INT UNSIGNED NOT NULL,
    brand_id      INT UNSIGNED NOT NULL,
    name          VARCHAR(500) NOT NULL,
    slug          VARCHAR(500) NOT NULL UNIQUE,
    sku           VARCHAR(100) NOT NULL UNIQUE,
    description   LONGTEXT NULL,
    price         DECIMAL(15,0) NOT NULL,
    sale_price    DECIMAL(15,0) NULL,
    stock         INT NOT NULL DEFAULT 0,
    thumbnail     VARCHAR(500) NULL,
    -- Thông số kỹ thuật (Dùng để filter/tìm kiếm)
    cpu           VARCHAR(255) NULL COMMENT 'VD: Intel Core i5-1235U',
    ram           VARCHAR(100) NULL COMMENT 'VD: 8GB DDR4',
    storage       VARCHAR(100) NULL COMMENT 'VD: 512GB SSD NVMe',
    display       VARCHAR(255) NULL COMMENT 'VD: 15.6 inch FHD IPS',
    gpu           VARCHAR(255) NULL COMMENT 'VD: NVIDIA RTX 3050',
    os            VARCHAR(100) NULL COMMENT 'VD: Windows 11 Home',
    battery       VARCHAR(100) NULL COMMENT 'VD: 56Wh',
    weight        VARCHAR(50)  NULL COMMENT 'VD: 1.8kg',
    -- Trạng thái
    is_active     TINYINT(1) NOT NULL DEFAULT 1,
    is_featured   TINYINT(1) NOT NULL DEFAULT 0,
    views         INT NOT NULL DEFAULT 0,
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE RESTRICT,
    FOREIGN KEY (brand_id)    REFERENCES brands(id)     ON DELETE RESTRICT,
    INDEX idx_price (price),
    INDEX idx_brand (brand_id),
    INDEX idx_category (category_id),
    INDEX idx_active (is_active),
    FULLTEXT INDEX ft_search (name, description, cpu, ram)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================
--  BẢNG: product_images (Ảnh sản phẩm)
-- =============================================================
CREATE TABLE IF NOT EXISTS product_images (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    product_id BIGINT UNSIGNED NOT NULL,
    image_path VARCHAR(500) NOT NULL,
    alt_text   VARCHAR(255) NULL,
    is_primary TINYINT(1) NOT NULL DEFAULT 0,
    sort_order INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    INDEX idx_product (product_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================
--  BẢNG: carts (Giỏ hàng)
-- =============================================================
CREATE TABLE IF NOT EXISTS carts (
    id         BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id    BIGINT UNSIGNED NOT NULL,
    product_id BIGINT UNSIGNED NOT NULL,
    quantity   INT NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id)    REFERENCES users(id)    ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY unique_cart (user_id, product_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================
--  BẢNG: orders (Đơn hàng)
-- =============================================================
CREATE TABLE IF NOT EXISTS orders (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id         BIGINT UNSIGNED NOT NULL,
    order_code      VARCHAR(50) NOT NULL UNIQUE,
    total_amount    DECIMAL(15,0) NOT NULL,
    discount_amount DECIMAL(15,0) NOT NULL DEFAULT 0,
    final_amount    DECIMAL(15,0) NOT NULL,
    status          ENUM('pending','confirmed','processing','shipped','delivered','cancelled','refunded')
                    NOT NULL DEFAULT 'pending',
    payment_method  ENUM('cod','vnpay','bank_transfer') NOT NULL DEFAULT 'cod',
    payment_status  ENUM('unpaid','paid','refunded') NOT NULL DEFAULT 'unpaid',
    vnpay_txn_ref   VARCHAR(100) NULL,
    -- Thông tin giao hàng (snapshot tại thời điểm đặt hàng)
    shipping_name    VARCHAR(255) NOT NULL,
    shipping_phone   VARCHAR(20)  NOT NULL,
    shipping_address TEXT NOT NULL,
    shipping_city    VARCHAR(100) NOT NULL,
    note            TEXT NULL,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE RESTRICT,
    INDEX idx_status (status),
    INDEX idx_user (user_id),
    INDEX idx_payment_status (payment_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================
--  BẢNG: order_items (Chi tiết đơn hàng)
-- =============================================================
CREATE TABLE IF NOT EXISTS order_items (
    id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id    BIGINT UNSIGNED NOT NULL,
    product_id  BIGINT UNSIGNED NOT NULL,
    product_name VARCHAR(500) NOT NULL,  -- snapshot tên SP
    product_sku  VARCHAR(100) NOT NULL,  -- snapshot SKU
    quantity    INT NOT NULL,
    unit_price  DECIMAL(15,0) NOT NULL,
    total_price DECIMAL(15,0) NOT NULL,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id)   REFERENCES orders(id)   ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE RESTRICT,
    INDEX idx_order (order_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================
--  BẢNG: reviews (Đánh giá sản phẩm)
-- =============================================================
CREATE TABLE IF NOT EXISTS reviews (
    id         BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    product_id BIGINT UNSIGNED NOT NULL,
    user_id    BIGINT UNSIGNED NOT NULL,
    order_id   BIGINT UNSIGNED NULL,
    rating     TINYINT NOT NULL CHECK (rating BETWEEN 1 AND 5),
    comment    TEXT NULL,
    is_approved TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id)    REFERENCES users(id)    ON DELETE CASCADE,
    FOREIGN KEY (order_id)   REFERENCES orders(id)   ON DELETE SET NULL,
    UNIQUE KEY unique_review (product_id, user_id, order_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================
--  BẢNG: coupons (Mã giảm giá)
-- =============================================================
CREATE TABLE IF NOT EXISTS coupons (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code            VARCHAR(50) NOT NULL UNIQUE,
    type            ENUM('percent','fixed') NOT NULL DEFAULT 'percent',
    value           DECIMAL(10,2) NOT NULL,
    min_order       DECIMAL(15,0) NOT NULL DEFAULT 0,
    max_discount    DECIMAL(15,0) NULL,
    usage_limit     INT NULL,
    usage_count     INT NOT NULL DEFAULT 0,
    is_active       TINYINT(1) NOT NULL DEFAULT 1,
    starts_at       TIMESTAMP NULL,
    expires_at      TIMESTAMP NULL,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================
--  BẢNG: wishlists (Danh sách yêu thích)
-- =============================================================
CREATE TABLE IF NOT EXISTS wishlists (
    id         BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id    BIGINT UNSIGNED NOT NULL,
    product_id BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id)    REFERENCES users(id)    ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY unique_wishlist (user_id, product_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================
--  DATA MẪU - Admin Account
-- =============================================================
-- Password: Admin@123456 (bcrypt hash)
INSERT INTO users (name, email, password, role, is_active, email_verified_at) VALUES
(
    'Administrator',
    'admin@laptopshop.com',
    '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'admin',
    1,
    NOW()
);

-- =============================================================
--  DATA MẪU - Brands
-- =============================================================
INSERT INTO brands (name, slug, is_active) VALUES
('Asus',    'asus',    1),
('Dell',    'dell',    1),
('HP',      'hp',      1),
('Lenovo',  'lenovo',  1),
('Acer',    'acer',    1),
('MSI',     'msi',     1),
('Apple',   'apple',   1),
('LG',      'lg',      1);

-- =============================================================
--  DATA MẪU - Categories
-- =============================================================
INSERT INTO categories (name, slug, is_active) VALUES
('Gaming',      'gaming',      1),
('Văn Phòng',   'van-phong',   1),
('Đồ Họa',      'do-hoa',      1),
('Mỏng Nhẹ',    'mong-nhe',    1),
('Sinh Viên',   'sinh-vien',   1);

SELECT 'Database initialized successfully!' AS status;
