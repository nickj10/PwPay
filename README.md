# Local Environment
> Using Docker for our local environment

## Requirements

1. Having [Docker installed](https://www.docker.com/products/docker-desktop) (you will need to create a Hub account)
2. Having [Git installed](https://git-scm.com/downloads)

## Installation

1. Clone this repository into your projects folder using the `git clone` command

## Instructions

1. After cloning the project, open your terminal and access the root folder using the `cd /path/to/the/folder` command.
2. To start the local environment, execute the command `docker-compose up -d` in your terminal.

**Note:** The first time you run this command it will take some time because it will download all the required images from the Hub.

At this point, if you execute the command `docker ps` you should see a total of 4 containers running:

```
pw_local_env-nginx
pw_local_env-admin
pw_local_env-php
pw_local_env-db
```

At this point, you should be able to access to the application by visiting the following address in your browser [http://pwpay.grup16:8030/](http://pwpay.grup16:8030/).

### Resources

1. MySQL Tables

```sql
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

CREATE TABLE IF NOT EXISTS AuthToken (
    uuid char(36) NOT NULL, 
    user_id bigint,
    used BOOLEAN DEFAULT false,
    FOREIGN KEY (user_id) REFERENCES user(user_id),
    PRIMARY KEY (uuid)
);

CREATE TABLE IF NOT EXISTS Accounts (
    account_id BIGINT AUTO_INCREMENT, 
    user_id bigint,
    owner_name VARCHAR(255),
    iban VARCHAR(50),
    activity_status BOOLEAN DEFAULT true,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
	modified_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES user(user_id),
    PRIMARY KEY (account_id)
);

CREATE TABLE IF NOT EXISTS Transactions (
    transactions_id BIGINT AUTO_INCREMENT, 
    user_id bigint,
    description VARCHAR(255),
    amount FLOAT DEFAULT 0.0,
    action VARCHAR(255),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES user(user_id),
    PRIMARY KEY (transactions_id)
);

CREATE TABLE IF NOT EXISTS Requests (
    request_id BIGINT AUTO_INCREMENT,
    org_user_id BIGINT,
    dest_user_id BIGINT,
    amount FLOAT,
    status VARCHAR(255) DEFAULT 'PENDING',
    FOREIGN KEY (org_user_id) REFERENCES user(user_id),
    FOREIGN KEY (dest_user_id) REFERENCES user(user_id),
    PRIMARY KEY (request_id)
);
```




