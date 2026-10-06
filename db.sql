-- Import file ini di phpMyAdmin (XAMPP)
CREATE DATABASE IF NOT EXISTS simple_store CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE simple_store;

CREATE TABLE IF NOT EXISTS sellers (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  owner_name VARCHAR(120) NULL,
  phone VARCHAR(50) NULL,
  email VARCHAR(120) NULL,
  address TEXT NULL,
  bonus_points_balance INT NOT NULL DEFAULT 0,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS products (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  description TEXT,
  price INT NOT NULL,
  image_path VARCHAR(255),
  sizes VARCHAR(120) DEFAULT 'S,M,L,XL',
  seller_id INT NULL DEFAULT NULL,
  is_best_seller TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  KEY idx_products_seller_id (seller_id),
  CONSTRAINT fk_products_seller FOREIGN KEY (seller_id) REFERENCES sellers(id) ON DELETE SET NULL
) ENGINE=InnoDB;

INSERT INTO products (name, description, price, image_path, sizes) VALUES
('Kaos Polos', 'Kaos cotton combed 30s, adem dan nyaman.', 75000, NULL, 'S,M,L,XL'),
('Kemeja Flanel', 'Cocok buat nongkrong dan kerja santai.', 150000, NULL, 'M,L,XL'),
('Hoodie Basic', 'Nyaman buat cuaca adem, bahan fleece.', 200000, NULL, 'M,L,XL,XXL');

CREATE TABLE IF NOT EXISTS diy_resources (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(180) NOT NULL,
  description TEXT NULL,
  resource_type VARCHAR(20) NOT NULL DEFAULT 'video',
  resource_url VARCHAR(500) NOT NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS diy_submissions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  customer_id INT NOT NULL,
  customer_name VARCHAR(120) NOT NULL,
  school_name VARCHAR(180) NULL,
  title VARCHAR(180) NOT NULL,
  description TEXT NULL,
  video_path VARCHAR(255) NOT NULL,
  status VARCHAR(20) NOT NULL DEFAULT 'pending',
  points_awarded INT NOT NULL DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  KEY idx_diy_submissions_status (status)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS auctions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(180) NOT NULL,
  description TEXT NULL,
  image_url VARCHAR(500) NULL,
  starts_at DATETIME NOT NULL,
  ends_at DATETIME NOT NULL,
  starting_bid INT NOT NULL DEFAULT 0,
  min_increment INT NOT NULL DEFAULT 10000,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS auction_bids (
  id INT AUTO_INCREMENT PRIMARY KEY,
  auction_id INT NOT NULL,
  customer_id INT NOT NULL,
  customer_name VARCHAR(120) NOT NULL,
  bid_amount INT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  KEY idx_auction_bids_auction (auction_id, bid_amount),
  CONSTRAINT fk_auction_bids_auction FOREIGN KEY (auction_id) REFERENCES auctions(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS auction_settlements (
  auction_id INT PRIMARY KEY,
  winner_customer_id INT NULL,
  winner_name VARCHAR(120) NULL,
  winning_bid INT NOT NULL DEFAULT 0,
  order_id INT NULL UNIQUE,
  settlement_status VARCHAR(20) NOT NULL,
  finalized_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_auction_settlements_auction FOREIGN KEY (auction_id) REFERENCES auctions(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS school_events (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(180) NOT NULL,
  description TEXT NULL,
  starts_at DATETIME NOT NULL,
  ends_at DATETIME NOT NULL,
  top_prize VARCHAR(255) NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  is_finalized TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS school_scores (
  id INT AUTO_INCREMENT PRIMARY KEY,
  event_id INT NOT NULL,
  school_name VARCHAR(180) NOT NULL,
  team_name VARCHAR(180) NOT NULL,
  score INT NOT NULL DEFAULT 0,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_school_team_event (event_id, school_name, team_name),
  CONSTRAINT fk_school_scores_event FOREIGN KEY (event_id) REFERENCES school_events(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS school_submissions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  event_id INT NOT NULL,
  customer_id INT NOT NULL,
  customer_name VARCHAR(120) NOT NULL,
  school_name VARCHAR(180) NOT NULL,
  team_name VARCHAR(180) NOT NULL,
  mission_description TEXT NOT NULL,
  status VARCHAR(20) NOT NULL DEFAULT 'pending',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  KEY idx_school_submissions_status (status),
  CONSTRAINT fk_school_submissions_event FOREIGN KEY (event_id) REFERENCES school_events(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS school_awards (
  id INT AUTO_INCREMENT PRIMARY KEY,
  event_id INT NOT NULL,
  rank_position TINYINT NOT NULL,
  school_name VARCHAR(180) NOT NULL,
  team_name VARCHAR(180) NOT NULL,
  prize VARCHAR(255) NULL,
  award_status VARCHAR(20) NOT NULL DEFAULT 'pending',
  awarded_at TIMESTAMP NULL,
  UNIQUE KEY uq_school_award_rank (event_id, rank_position),
  CONSTRAINT fk_school_awards_event FOREIGN KEY (event_id) REFERENCES school_events(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS seller_bonus_periods (
  quarter_start DATE PRIMARY KEY,
  finalized_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS seller_bonus_awards (
  id INT AUTO_INCREMENT PRIMARY KEY,
  seller_id INT NOT NULL,
  quarter_start DATE NOT NULL,
  points INT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_seller_quarter_bonus (seller_id, quarter_start)
) ENGINE=InnoDB;
