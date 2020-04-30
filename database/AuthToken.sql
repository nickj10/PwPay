CREATE TABLE IF NOT EXISTS AuthToken (
    uuid char(16) NOT NULL, 
    user_id bigint,
    used BOOLEAN DEFAULT false,
    FOREIGN KEY (user_id) REFERENCES user(user_id),
    PRIMARY KEY (uuid)
);