CREATE DATABASE IF NOT EXISTS pwpay; 
USE pwpay; 
CREATE TABLE IF NOT EXISTS user(
    user_id BIGINT AUTO_INCREMENT,
    email VARCHAR(255),
    password VARCHAR(255),
    birthday date,
    phone INTEGER,
    balance FLOAT DEFAULT 0.0,
    status VARCHAR(255) DEFAULT 'inactive',
    profile_picture VARCHAR(255),
    PRIMARY KEY (user_id)
);