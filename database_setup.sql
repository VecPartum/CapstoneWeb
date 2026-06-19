-- A4th Forum Database Setup
-- Run this SQL script in phpMyAdmin or MySQL to set up the database

CREATE DATABASE IF NOT EXISTS a4th_forum;
USE a4th_forum;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    avatar VARCHAR(50) DEFAULT '🌿',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL,
    INDEX idx_email (email),
    INDEX idx_username (username)
);

-- Threads table
CREATE TABLE IF NOT EXISTS threads (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    preview TEXT NOT NULL,
    category VARCHAR(50) NOT NULL,
    replies INT DEFAULT 0,
    views INT DEFAULT 0,
    pinned BOOLEAN DEFAULT FALSE,
    hot BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_category (category),
    INDEX idx_created_at (created_at),
    FULLTEXT INDEX ft_search (title, preview)
);

-- Posts/Replies table (for future expansion)
CREATE TABLE IF NOT EXISTS posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    thread_id INT NOT NULL,
    user_id INT NOT NULL,
    content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (thread_id) REFERENCES threads(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_thread_id (thread_id),
    INDEX idx_user_id (user_id)
);

-- Insert sample admin user (password: admin123)
INSERT INTO users (username, email, password_hash, avatar) 
VALUES ('A4th Team', 'admin@a4th.dev', '$2y$10$vHOXtkynzTs7SXAsu6WZe.jqtDCF2kLG5la4FImbQXZB2dNh001qi', '🌿')
ON DUPLICATE KEY UPDATE id=id;

-- Insert sample threads
INSERT INTO threads (user_id, title, preview, category, replies, views, pinned, hot)
SELECT 1, 'Official: Welcome to the A4th Community Forums!', 'Hey everyone! We\'re so glad you\'re here. This is your space to share ideas, fan art, theories, and more...', 'Dev Updates', 42, 1204, TRUE, FALSE
WHERE NOT EXISTS (SELECT 1 FROM threads WHERE title = 'Official: Welcome to the A4th Community Forums!');

INSERT INTO threads (user_id, title, preview, category, replies, views, pinned, hot)
SELECT 1, 'Dev Update #1 — Progress on the combat system', 'We\'ve been working on getting the basic combat loop feeling right. Here\'s a breakdown of what we\'ve implemented...', 'Dev Updates', 35, 712, TRUE, FALSE
WHERE NOT EXISTS (SELECT 1 FROM threads WHERE title = 'Dev Update #1');
