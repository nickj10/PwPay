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
  	PRIMARY KEY (user_id)
);
```




