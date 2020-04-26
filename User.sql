CREATE DATABASE IF NOT EXISTS pwpay; 
USE pwpay; 
CREATE TABLE IF NOT EXISTS user(
	user_id BIGINT AUTO_INCREMENT,
	email VARCHAR(255),
	password VARCHAR(255),
	birthday date,
	phone INTEGER,
	status VARCHAR(255) DEFAULT 'inactive',
  	PRIMARY KEY (user_id)
);
