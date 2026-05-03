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
