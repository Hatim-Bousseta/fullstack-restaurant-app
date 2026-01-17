-- Insert default admin (DO NOT include plaintext passwords)
-- Use a password hash instead of plaintext. Replace <PASSWORD_HASH_PLACEHOLDER>
INSERT INTO users (username, email, password, is_admin) 
VALUES ('admin', 'admin@food.com', '<PASSWORD_HASH_PLACEHOLDER>', 1);
-- Replace <PASSWORD_HASH_PLACEHOLDER> with a bcrypt or argon2 hash before importing.

-- Insert sample menu items
INSERT INTO menu_items (name, description, price, image_path) 
VALUES ('Cheeseburger', 'Juicy beef patty with cheese', 8.99, 'burger.jpg');

INSERT INTO menu_items (name, description, price, image_path) 
VALUES ('French Fries', 'Crispy golden fries', 3.99, 'fries.jpg');

INSERT INTO menu_items (name, description, price, image_path) 
VALUES ('Pizza', 'Pepperoni pizza 12 inch', 12.99, 'pizza.jpg');

INSERT INTO menu_items (name, description, price, image_path) 
VALUES ('Coca Cola', 'Cold refreshment', 1.99, 'cola.jpg');

-- Add more menu items matching your index.html
INSERT INTO menu_items (name, description, price, image_path) 
VALUES ('Creamy Alfredo Pasta', 'Fettuccine pasta with creamy Alfredo sauce, garlic, and Parmesan cheese', 14.99, 'pasta.png');

INSERT INTO menu_items (name, description, price, image_path) 
VALUES ('Pepperoni Pizza', 'Classic pizza with mozzarella, spicy pepperoni, and tomato sauce', 16.99, 'pizza.png');

INSERT INTO menu_items (name, description, price, image_path) 
VALUES ('Grilled Chicken Caesar', 'Fresh romaine lettuce with grilled chicken, croutons, and Caesar dressing', 12.99, 'salad.png');

INSERT INTO menu_items (name, description, price, image_path) 
VALUES ('Sushi Platter', 'Assorted sushi rolls with salmon, tuna, avocado, and crab', 18.99, 'sushi.png');

INSERT INTO menu_items (name, description, price, image_path) 
VALUES ('Classic Cheeseburger', 'Juicy beef patty with cheddar, lettuce, tomato, and special sauce', 13.99, 'burger.png');

INSERT INTO menu_items (name, description, price, image_path) 
VALUES ('Fish Tacos', 'Beer-battered fish with cabbage slaw, lime crema, and fresh salsa', 15.99, 'tacos.png');

-- Sides & Drinks
INSERT INTO menu_items (name, description, price, image_path) 
VALUES ('Garlic Bread', 'Fresh baked with garlic butter', 5.99, 'garlic-bread.jpg');

INSERT INTO menu_items (name, description, price, image_path) 
VALUES ('Sweet Potato Fries', 'Crispy with sea salt', 6.99, 'fries.jpg');

INSERT INTO menu_items (name, description, price, image_path) 
VALUES ('Fresh Lemonade', 'Homemade with real lemons', 4.99, 'lemonade.jpg');

INSERT INTO menu_items (name, description, price, image_path) 
VALUES ('Chocolate Brownie', 'Warm with vanilla ice cream', 7.99, 'brownie.jpg');

COMMIT;



--what i have added from the app ---
-- Insert menu items
INSERT INTO menu_items (id, name, description, price, image_path, is_available)
VALUES (1, 'Cheeseburger', 'Juicy beef patty with cheese', 8.99, '1766872511_burger-removebg-preview.png', 1);

INSERT INTO menu_items (id, name, description, price, image_path, is_available)
VALUES (2, 'French Fries', 'Crispy golden fries', 3.99, 'fries.jpg', 0);

INSERT INTO menu_items (id, name, description, price, image_path, is_available)
VALUES (3, 'Pizza', 'Classic pizza with mozzarella, spicy pepperoni, and tomato sauce', 12.99, '1766873900_af4ccc982a868a629b4026d6557e2f8d-removebg-preview.png', 1);

INSERT INTO menu_items (id, name, description, price, image_path, is_available)
VALUES (4, 'Coca Cola', 'Cold refreshment', 1.99, '1766872651_8ddec1ae7c20e4ca2dd9899f47edb2b3.jpg', 1);

INSERT INTO menu_items (id, name, description, price, image_path, is_available)
VALUES (5, 'Creamy Alfredo Pasta', 'Fettuccine pasta with creamy Alfredo sauce, garlic, and Parmesan cheese', 14.99, '1766872449_alfredo-removebg-preview.png', 1);

INSERT INTO menu_items (id, name, description, price, image_path, is_available)
VALUES (6, 'Pepperoni Pizza', 'Pepperoni pizza 12 inch', 16.99, '1766872729_4da73f313deef52c2373795a970b4082-removebg-preview.png', 1);

INSERT INTO menu_items (id, name, description, price, image_path, is_available)
VALUES (7, 'Grilled Chicken Caesar', 'Fresh romaine lettuce with grilled chicken, croutons, and Caesar dressing', 12.99, '1766872951_cfa1eb04fc220a10aa0f822603583590-removebg-preview.png', 1);

INSERT INTO menu_items (id, name, description, price, image_path, is_available)
VALUES (8, 'Sushi Platter', 'Assorted sushi rolls with salmon, tuna, avocado, and crab', 18.99, '1766873040_b30c5a3c14d0455260d47a762d1b3d3a-removebg-preview.png', 1);

INSERT INTO menu_items (id, name, description, price, image_path, is_available)
VALUES (9, 'Classic Cheeseburger', 'Juicy beef patty with cheddar, lettuce, tomato, and special sauce', 13.99, '1766873188_127ef0f217f0d923b2afc78f8703e16f-removebg-preview.png', 1);

INSERT INTO menu_items (id, name, description, price, image_path, is_available)
VALUES (10, 'Fish Tacos', 'Beer-battered fish with cabbage slaw, lime crema, and fresh salsa', 15.99, '1766873312_6ef80fb787db97cf5895015b237b604f-removebg-preview.png', 1);

INSERT INTO menu_items (id, name, description, price, image_path, is_available)
VALUES (11, 'Garlic Bread', 'Fresh baked with garlic butter', 5.99, '1766873417_6724d060f6987ca2b47a300b953ef675-removebg-preview.png', 1);

INSERT INTO menu_items (id, name, description, price, image_path, is_available)
VALUES (12, 'Sweet Potato Fries', 'Crispy with sea salt', 6.99, '1766873485_31937a75473ad951ce6cdf4d07c93548-removebg-preview.png', 1);

INSERT INTO menu_items (id, name, description, price, image_path, is_available)
VALUES (13, 'Fresh Lemonade', 'Homemade with real lemons', 4.99, '1766873565_f4a03c24b1a978943b5c7e9a4e206e7b-removebg-preview.png', 1);

INSERT INTO menu_items (id, name, description, price, image_path, is_available)
VALUES (14, 'Chocolate Brownie', 'It is typically dense, fudgy, and rich in flavor, making it a favorite treat for chocolate lovers', 7.99, '1766873716_ae099d0fff5d91a71ebacf78ec612889-removebg-preview.png', 1);

INSERT INTO menu_items (id, name, description, price, image_path, is_available)
VALUES (16, 'Ice lemon tea', 'A cold yummy ice lemon tea', 5, '1766879215_99d8bc4d4ec9d198ae66b69939f80949-removebg-preview.png', 1);

-- Insert orders
INSERT INTO orders (id, user_id, total_price, status, order_date, delivery_address)
VALUES (1, 3, 43.96, 'Out for Delivery', TO_DATE('27-DEC-2025', 'DD-MON-YYYY'), 'avenu el hajeb');

INSERT INTO orders (id, user_id, total_price, status, order_date, delivery_address)
VALUES (2, 3, 16.98, 'preparing', TO_DATE('27-DEC-2025', 'DD-MON-YYYY'), 'avenu el hajeb');

INSERT INTO orders (id, user_id, total_price, status, order_date, delivery_address)
VALUES (3, 5, 8.99, 'Delivered', TO_DATE('27-DEC-2025', 'DD-MON-YYYY'), 'meknes');

INSERT INTO orders (id, user_id, total_price, status, order_date, delivery_address)
VALUES (4, 5, 8.99, 'preparing', TO_DATE('27-DEC-2025', 'DD-MON-YYYY'), 'meknes');

INSERT INTO orders (id, user_id, total_price, status, order_date, delivery_address)
VALUES (5, 5, 3.99, 'Delivered', TO_DATE('27-DEC-2025', 'DD-MON-YYYY'), 'tetouan');

INSERT INTO orders (id, user_id, total_price, status, order_date, delivery_address)
VALUES (6, 6, 25.98, 'preparing', TO_DATE('27-DEC-2025', 'DD-MON-YYYY'), 'martil');

INSERT INTO orders (id, user_id, total_price, status, order_date, delivery_address)
VALUES (7, 6, 108.9, 'preparing', TO_DATE('28-DEC-2025', 'DD-MON-YYYY'), 'Rabat, rue el aousaq');

-- Insert order items
INSERT INTO order_items (id, order_id, item_id, item_name, quantity, price, total)
VALUES (1, 5, 2, 'French Fries', 1, 3.99, 3.99);

INSERT INTO order_items (id, order_id, item_id, item_name, quantity, price, total)
VALUES (2, 6, 7, 'Grilled Chicken Caesar', 1, 12.99, 12.99);

INSERT INTO order_items (id, order_id, item_id, item_name, quantity, price, total)
VALUES (3, 6, 3, 'Pizza', 1, 12.99, 12.99);

INSERT INTO order_items (id, order_id, item_id, item_name, quantity, price, total)
VALUES (4, 7, 16, 'Ice lemon tea', 1, 5, 5);

INSERT INTO order_items (id, order_id, item_id, item_name, quantity, price, total)
VALUES (5, 7, 12, 'Sweet Potato Fries', 1, 6.99, 6.99);

INSERT INTO order_items (id, order_id, item_id, item_name, quantity, price, total)
VALUES (6, 7, 11, 'Garlic Bread', 1, 5.99, 5.99);

INSERT INTO order_items (id, order_id, item_id, item_name, quantity, price, total)
VALUES (7, 7, 4, 'Coca Cola', 1, 1.99, 1.99);

INSERT INTO order_items (id, order_id, item_id, item_name, quantity, price, total)
VALUES (8, 7, 13, 'Fresh Lemonade', 1, 4.99, 4.99);

INSERT INTO order_items (id, order_id, item_id, item_name, quantity, price, total)
VALUES (9, 7, 14, 'Chocolate Brownie', 1, 7.99, 7.99);

INSERT INTO order_items (id, order_id, item_id, item_name, quantity, price, total)
VALUES (10, 7, 10, 'Fish Tacos', 1, 15.99, 15.99);

INSERT INTO order_items (id, order_id, item_id, item_name, quantity, price, total)
VALUES (11, 7, 7, 'Grilled Chicken Caesar', 1, 12.99, 12.99);

INSERT INTO order_items (id, order_id, item_id, item_name, quantity, price, total)
VALUES (12, 7, 8, 'Sushi Platter', 1, 18.99, 18.99);

INSERT INTO order_items (id, order_id, item_id, item_name, quantity, price, total)
VALUES (13, 7, 3, 'Pizza', 1, 12.99, 12.99);

INSERT INTO order_items (id, order_id, item_id, item_name, quantity, price, total)
VALUES (14, 7, 5, 'Creamy Alfredo Pasta', 1, 14.99, 14.99);

COMMIT;