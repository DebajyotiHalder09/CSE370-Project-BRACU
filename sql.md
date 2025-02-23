<h2>create database in MariaDB</h2>

```sh
CREATE DATABASE lunch;

```
<h2>create tables and triggers in MariaDB</h2>

```sh

CREATE TABLE user (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    username VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) UNIQUE NOT NULL,
    UNIQUE KEY (username, password)
);

CREATE TABLE seller (
    seller_id INT PRIMARY KEY AUTO_INCREMENT,
    seller_userid INT NOT NULL UNIQUE,
    total_earnings DECIMAL(10) DEFAULT 0,
    followers_count INT DEFAULT 0,
    FOREIGN KEY (seller_userid) REFERENCES user(id)
);

CREATE TABLE item (
    item_id INT AUTO_INCREMENT PRIMARY KEY,
    seller_id INT NOT NULL,
    item_name VARCHAR(255) NOT NULL,
    item_price DECIMAL(5) NOT NULL,
    item_image VARCHAR(255),
    FOREIGN KEY (seller_id) REFERENCES seller(seller_id) ON DELETE CASCADE
);

CREATE TABLE item_catagory (
    cat_id INT AUTO_INCREMENT PRIMARY KEY,
    cat_name VARCHAR(255) NOT NULL UNIQUE,
    cat_image VARCHAR(255)
);

CREATE TABLE catagory_store (
    store_id INT AUTO_INCREMENT PRIMARY KEY,
    seller_id INT NOT NULL,
    cat_id INT NOT NULL,
    FOREIGN KEY (seller_id) REFERENCES seller(seller_id) ON DELETE CASCADE,
    FOREIGN KEY (cat_id) REFERENCES item_catagory(cat_id) ON DELETE CASCADE
);

CREATE TABLE fav_follower (
    user_id INT NOT NULL,                        
    seller_id INT NOT NULL,                      
    FOREIGN KEY (user_id) REFERENCES user(id),   
    FOREIGN KEY (seller_id) REFERENCES seller(seller_id), 
    PRIMARY KEY (user_id, seller_id)             
);
CREATE TABLE fav_cat (
    user_id INT NOT NULL,                        
    cat_id INT NOT NULL,                      
    FOREIGN KEY (user_id) REFERENCES user(id),   
    FOREIGN KEY (cat_id) REFERENCES item_catagory(cat_id), 
    PRIMARY KEY (user_id, cat_id)             
);
CREATE TABLE cart (
    cart_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE CASCADE
);
CREATE TABLE cart_item (
    cart_id INT NOT NULL,
    item_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    item_price DECIMAL(10) NOT NULL,
    PRIMARY KEY (cart_id, item_id),
    FOREIGN KEY (cart_id) REFERENCES cart(cart_id) ON DELETE CASCADE,
    FOREIGN KEY (item_id) REFERENCES item(item_id) ON DELETE CASCADE
);

CREATE TABLE orders (
    order_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    order_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE CASCADE
);
CREATE TABLE order_item (
    order_item_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    item_id INT NOT NULL,
    quantity INT NOT NULL,
    item_price DECIMAL(10) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders (order_id) ON DELETE CASCADE,
    FOREIGN KEY (item_id) REFERENCES item(item_id) ON DELETE CASCADE
);

DELIMITER $$

CREATE TRIGGER after_user_insert
AFTER INSERT ON user
FOR EACH ROW
BEGIN
    INSERT INTO cart (user_id) VALUES (NEW.id);
END $$

DELIMITER ;

DELIMITER //

CREATE TRIGGER update_followers_count_after_insert
AFTER INSERT ON fav_follower
FOR EACH ROW
BEGIN
    UPDATE seller
    SET followers_count = followers_count + 1
    WHERE seller_id = NEW.seller_id;
END//

DELIMITER ;


DELIMITER //

CREATE TRIGGER update_followers_count_after_delete
AFTER DELETE ON fav_follower
FOR EACH ROW
BEGIN
    UPDATE seller
    SET followers_count = followers_count - 1
    WHERE seller_id = OLD.seller_id;
END//

DELIMITER ;
```