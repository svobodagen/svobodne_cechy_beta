-- schema.sql

CREATE TABLE IF NOT EXISTS users (
    email VARCHAR(255) PRIMARY KEY,
    role VARCHAR(50) DEFAULT 'navstevnik',
    password VARCHAR(255),
    tempPassword VARCHAR(255),
    name VARCHAR(255),
    phone VARCHAR(50),
    socials JSON,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS masters (
    id VARCHAR(50) PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    craft VARCHAR(255),
    location VARCHAR(255),
    rank VARCHAR(255),
    aura VARCHAR(255),
    description LONGTEXT,
    stats JSON,
    tags JSON,
    badges JSON,
    gallery LONGTEXT,
    photo LONGTEXT,
    audio LONGTEXT,
    photoSettings JSON,
    socials JSON,
    education LONGTEXT,
    accommodation LONGTEXT,
    compensation LONGTEXT,
    recommendations LONGTEXT,
    requirements LONGTEXT,
    updatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS messages (
    id VARCHAR(50) PRIMARY KEY,
    toMaster VARCHAR(50),
    fromEmail VARCHAR(255),
    text TEXT,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deletedByAdmin BOOLEAN DEFAULT FALSE,
    deletedByUser BOOLEAN DEFAULT FALSE,
    userName VARCHAR(255),
    userPhone VARCHAR(50),
    FOREIGN KEY (toMaster) REFERENCES masters(id) ON DELETE SET NULL,
    FOREIGN KEY (fromEmail) REFERENCES users(email) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS media_folders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    parentId INT DEFAULT NULL,
    FOREIGN KEY (parentId) REFERENCES media_folders(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS media_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    folderId INT DEFAULT NULL,
    name VARCHAR(255) NOT NULL,
    type ENUM('image', 'video', 'audio') NOT NULL,
    path LONGTEXT NOT NULL,
    thumbnail LONGTEXT,
    size INT,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (folderId) REFERENCES media_folders(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS site_content (
    key_name VARCHAR(255) PRIMARY KEY,
    content_value TEXT,
    updatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS crafts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL UNIQUE
);

CREATE TABLE IF NOT EXISTS cities (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL UNIQUE
);

CREATE TABLE IF NOT EXISTS master_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    phone VARCHAR(50),
    email VARCHAR(255),
    age INT,
    crafts JSON,
    cities JSON,
    max_distance INT,
    note TEXT,
    admin_note TEXT,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS landing_sessions (
    session_id VARCHAR(64) PRIMARY KEY,
    landing_slug VARCHAR(100) NOT NULL,
    source VARCHAR(100) DEFAULT 'direct',
    referrer VARCHAR(255) DEFAULT NULL,
    utm_source VARCHAR(100) DEFAULT NULL,
    utm_medium VARCHAR(100) DEFAULT NULL,
    utm_campaign VARCHAR(100) DEFAULT NULL,
    utm_content VARCHAR(100) DEFAULT NULL,
    device_type VARCHAR(20) DEFAULT 'desktop',
    max_section VARCHAR(100) DEFAULT 'hero',
    clicked_button VARCHAR(255) DEFAULT NULL,
    clicked_section VARCHAR(100) DEFAULT NULL,
    form_status VARCHAR(50) DEFAULT 'none',
    lead_id INT DEFAULT NULL,
    duration_seconds INT DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX (landing_slug),
    INDEX (source),
    INDEX (created_at),
    INDEX (lead_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS landing_events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    session_id VARCHAR(64) NOT NULL,
    landing_slug VARCHAR(100) NOT NULL,
    event_type VARCHAR(50) NOT NULL,
    event_label VARCHAR(255) DEFAULT NULL,
    event_section VARCHAR(100) DEFAULT NULL,
    event_data TEXT DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX (session_id),
    INDEX (landing_slug),
    INDEX (event_type),
    INDEX (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS landing_links (
    id INT AUTO_INCREMENT PRIMARY KEY,
    landing_slug VARCHAR(100) NOT NULL,
    source_tag VARCHAR(100) NOT NULL,
    label VARCHAR(255) NOT NULL,
    channel VARCHAR(50) DEFAULT 'other',
    full_url TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_slug_source (landing_slug, source_tag),
    INDEX (landing_slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
