CREATE TABLE IF NOT EXISTS AuthToken (
    uuid char(36) NOT NULL, 
    user_id bigint,
    used BOOLEAN DEFAULT false,
    FOREIGN KEY (user_id) REFERENCES user(user_id),
    PRIMARY KEY (uuid)
);