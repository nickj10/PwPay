CREATE TABLE IF NOT EXISTS Accounts (
    account_id BIGINT AUTO_INCREMENT, 
    user_id bigint,
    owner_name VARCHAR(255),
    iban VARCHAR(50),
    balance FLOAT DEFAULT 0.0,
    activity_status BOOLEAN DEFAULT true,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
	modified_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES user(user_id),
    PRIMARY KEY (account_id)
);

CREATE TABLE IF NOT EXISTS Transactions (
    transactions_id BIGINT AUTO_INCREMENT, 
    user_id bigint,
    account_id bigint,
    description VARCHAR(255),
    amount FLOAT DEFAULT 0.0,
    action VARCHAR(255),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES user(user_id),
    FOREIGN KEY (account_id) REFERENCES Accounts(account_id),
    PRIMARY KEY (transactions_id)
);