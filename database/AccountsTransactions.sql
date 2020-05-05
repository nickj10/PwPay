CREATE TABLE IF NOT EXISTS Accounts (
    account_id BIGINT AUTO_INCREMENT, 
    user_id bigint,
    owner_name VARCHAR(255),
    iban VARCHAR(50),
    balance FLOAT DEFAULT 0.0,
    activity_status BOOLEAN DEFAULT true,
    FOREIGN KEY (user_id) REFERENCES user(user_id),
    PRIMARY KEY (account_id)
);

CREATE TABLE IF NOT EXISTS Transactions (
    transactions_id BIGINT AUTO_INCREMENT, 
    user_id bigint,
    account_id bigint,
    amount FLOAT DEFAULT 0.0,
    action VARCHAR(255),
    FOREIGN KEY (user_id) REFERENCES user(user_id),
    FOREIGN KEY (account_id) REFERENCES Accounts(account_id),
    PRIMARY KEY (transactions_id)
);