CREATE TABLE IF NOT EXISTS Accounts (
    account_id bigint NOT NULL, 
    user_id bigint,
    activity_status BOOLEAN DEFAULT false,
    FOREIGN KEY (user_id) REFERENCES user(user_id),
    PRIMARY KEY (account_id)
);

CREATE TABLE IF NOT EXISTS Transactions (
    transactions_id bigint NOT NULL, 
    user_id bigint,
    account_id bigint,
    amount FLOAT DEFAULT 0.0,
    action VARCHAR(255),
    FOREIGN KEY (user_id) REFERENCES user(user_id),
    FOREIGN KEY (account_id) REFERENCES Accounts(account_id),
    PRIMARY KEY (transactions_id)
);