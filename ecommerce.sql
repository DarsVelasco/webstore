-- MySQL dump 10.13  Distrib 8.0.38, for Win64 (x86_64)
--
-- Host: localhost    Database: ecommerce
-- ------------------------------------------------------
-- Server version	8.0.39

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `admin_permissions`
--

DROP TABLE IF EXISTS `admin_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `admin_permissions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `permission_name` varchar(50) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_user_permission` (`user_id`,`permission_name`),
  CONSTRAINT `admin_permissions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `admin_permissions`
--

LOCK TABLES `admin_permissions` WRITE;
/*!40000 ALTER TABLE `admin_permissions` DISABLE KEYS */;
INSERT INTO `admin_permissions` VALUES (1,1,'manage_products','2025-05-19 13:35:19'),(2,1,'manage_categories','2025-05-19 13:35:19'),(3,1,'manage_inventory','2025-05-19 13:35:19'),(4,1,'view_orders','2025-05-19 13:35:19'),(5,1,'manage_orders','2025-05-19 13:35:19'),(6,1,'view_users','2025-05-19 13:35:19'),(7,1,'manage_users','2025-05-19 13:35:19'),(8,1,'view_reports','2025-05-19 13:35:19'),(9,1,'manage_reports','2025-05-19 13:35:19');
/*!40000 ALTER TABLE `admin_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cart_items`
--

DROP TABLE IF EXISTS `cart_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cart_items` (
  `cart_item_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int DEFAULT NULL,
  `product_id` int DEFAULT NULL,
  `quantity` int NOT NULL DEFAULT '1',
  `added_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`cart_item_id`),
  KEY `user_id` (`user_id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `cart_items_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  CONSTRAINT `cart_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cart_items`
--

LOCK TABLES `cart_items` WRITE;
/*!40000 ALTER TABLE `cart_items` DISABLE KEYS */;
INSERT INTO `cart_items` VALUES (6,1,58,1,'2025-05-15 16:27:12'),(7,2,59,1,'2025-05-15 16:40:08');
/*!40000 ALTER TABLE `cart_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `categories` (
  `category_id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`category_id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categories`
--

LOCK TABLES `categories` WRITE;
/*!40000 ALTER TABLE `categories` DISABLE KEYS */;
INSERT INTO `categories` VALUES (1,'Electronics','Devices and gadgets like phones, laptops, and accessories.','2025-05-15 13:14:49'),(2,'Clothing','Apparel for men, women, and children.','2025-05-15 13:14:49'),(3,'Books','Fiction, non-fiction, educational, and more.','2025-05-15 13:14:49'),(4,'Home & Kitchen','Appliances, furniture, and kitchen tools.','2025-05-15 13:14:49'),(5,'Beauty & Personal Care','Cosmetics, skincare, and wellness products.','2025-05-15 13:14:49'),(6,'Toys & Games','Products for kids including games and toys.','2025-05-15 13:14:49'),(7,'Sports & Outdoors','Gear and apparel for sports and outdoor activities.','2025-05-15 13:14:49'),(8,'Automotive','Car accessories, parts, and tools.','2025-05-15 13:14:49'),(9,'Grocery','Food, beverages, and other daily essentials.','2025-05-15 13:14:49'),(10,'Pet Supplies','Items for pet care, food, and toys.','2025-05-15 13:14:49');
/*!40000 ALTER TABLE `categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `contact_messages`
--

DROP TABLE IF EXISTS `contact_messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `contact_messages` (
  `message_id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `subject` varchar(150) DEFAULT NULL,
  `message` text NOT NULL,
  `sent_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`message_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `contact_messages`
--

LOCK TABLES `contact_messages` WRITE;
/*!40000 ALTER TABLE `contact_messages` DISABLE KEYS */;
/*!40000 ALTER TABLE `contact_messages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `inventory_logs`
--

DROP TABLE IF EXISTS `inventory_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `inventory_logs` (
  `log_id` int NOT NULL AUTO_INCREMENT,
  `product_id` int DEFAULT NULL,
  `change_type` enum('add','edit','delete','restock','sold') NOT NULL,
  `quantity_change` int NOT NULL,
  `action_by` int DEFAULT NULL,
  `action_time` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`log_id`),
  KEY `product_id` (`product_id`),
  KEY `action_by` (`action_by`),
  CONSTRAINT `inventory_logs_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`),
  CONSTRAINT `inventory_logs_ibfk_2` FOREIGN KEY (`action_by`) REFERENCES `users` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `inventory_logs`
--

LOCK TABLES `inventory_logs` WRITE;
/*!40000 ALTER TABLE `inventory_logs` DISABLE KEYS */;
INSERT INTO `inventory_logs` VALUES (1,57,'sold',1,3,'2025-05-16 12:49:43'),(2,56,'sold',1,3,'2025-05-16 12:49:53'),(3,85,'sold',1,3,'2025-05-16 12:52:06');
/*!40000 ALTER TABLE `inventory_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `order_items`
--

DROP TABLE IF EXISTS `order_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `order_items` (
  `order_item_id` int NOT NULL AUTO_INCREMENT,
  `order_id` int DEFAULT NULL,
  `product_id` int DEFAULT NULL,
  `quantity` int NOT NULL,
  `price` decimal(10,2) NOT NULL,
  PRIMARY KEY (`order_item_id`),
  KEY `order_id` (`order_id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE,
  CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `order_items`
--

LOCK TABLES `order_items` WRITE;
/*!40000 ALTER TABLE `order_items` DISABLE KEYS */;
INSERT INTO `order_items` VALUES (1,1,57,1,14.99),(2,2,56,1,24.99),(3,3,85,1,19.99),(4,4,59,1,499.99),(5,4,75,1,29.99),(6,4,78,1,15.99),(7,5,57,1,14.99);
/*!40000 ALTER TABLE `order_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `orders`
--

DROP TABLE IF EXISTS `orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `orders` (
  `order_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int DEFAULT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `status` enum('pending','processing','shipped','delivered','cancelled') DEFAULT 'pending',
  `address` text NOT NULL,
  `payment_method` varchar(50) NOT NULL,
  `order_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`order_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `orders`
--

LOCK TABLES `orders` WRITE;
/*!40000 ALTER TABLE `orders` DISABLE KEYS */;
/*!40000 ALTER TABLE `orders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `products`
--

DROP TABLE IF EXISTS `products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `products` (
  `product_id` int NOT NULL AUTO_INCREMENT,
  `category_id` int DEFAULT NULL,
  `name` varchar(150) NOT NULL,
  `description` text,
  `image` varchar(255) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `stock` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`product_id`),
  KEY `category_id` (`category_id`),
  CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=86 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `products`
--

LOCK TABLES `products` WRITE;
/*!40000 ALTER TABLE `products` DISABLE KEYS */;
INSERT INTO `products` VALUES (56,3,'World History 101','Comprehensive guide to world history.','uploads/products/product_682605b9432d6.png',24.99,99,'2025-05-15 13:15:02'),(57,3,'Children\'s Bedtime Stories','Colorful and fun bedtime stories.','uploads/products/product_682605caf327e.jpg',14.99,88,'2025-05-15 13:15:02'),(58,4,'Non-stick Frying Pan','Durable frying pan with non-stick coating.','uploads/products/product_682606d305055.webp',25.99,75,'2025-05-15 13:15:02'),(59,4,'Sofa Set - 3 Piece','Comfortable and stylish sofa set.','uploads/products/product_682606c675851.jpg',499.99,9,'2025-05-15 13:15:02'),(60,4,'LED Table Lamp','Adjustable table lamp with touch control.','uploads/products/product_682606b69f25b.jpg',34.99,60,'2025-05-15 13:15:02'),(61,4,'Electric Kettle','1.5L electric kettle with auto shut-off.','uploads/products/product_6826069f75bae.webp',29.99,100,'2025-05-15 13:15:02'),(62,5,'Facial Cleanser','Gentle cleanser for daily use.','uploads/products/product_6826068dd51a8.jpg',12.99,180,'2025-05-15 13:15:02'),(63,5,'Moisturizing Lotion','Hydrating body lotion.','uploads/products/product_6826067fd510c.webp',15.99,150,'2025-05-15 13:15:02'),(64,5,'Lipstick Set','Set of 5 matte lipsticks.','uploads/products/product_682606728825f.jpg',22.99,90,'2025-05-15 13:15:02'),(65,5,'Hair Dryer 2200W','Powerful hair dryer with heat settings.','uploads/products/product_68260667803a9.jpg',49.99,40,'2025-05-15 13:15:02'),(66,6,'Building Blocks Set','Creative play for kids aged 3+.','uploads/products/product_6826065577ed6.jpg',19.99,120,'2025-05-15 13:15:02'),(67,6,'Remote Control Car','Fast RC car with rechargeable battery.','uploads/products/product_6826064761a5d.webp',34.99,60,'2025-05-15 13:15:02'),(68,6,'Board Game Classic','Family fun board game.','uploads/products/product_68260637b06c8.webp',29.99,80,'2025-05-15 13:15:02'),(69,6,'Dollhouse Mini Set','Mini dollhouse with furniture.','uploads/products/product_6826062951558.jpg',39.99,55,'2025-05-15 13:15:02'),(70,7,'Yoga Mat','Non-slip yoga mat with carrying strap.','uploads/products/product_68260618f247b.webp',24.99,100,'2025-05-15 13:15:02'),(71,7,'Mountain Bike Helmet','Adjustable and protective helmet.','uploads/products/product_6826060b0e371.webp',49.99,45,'2025-05-15 13:15:02'),(72,7,'Camping Tent - 4 Person','Easy setup waterproof tent.','uploads/products/product_682605fec11d0.jpg',129.99,25,'2025-05-15 13:15:02'),(73,7,'Dumbbell Set 20kg','Adjustable dumbbell set.','uploads/products/product_682605f135a8e.jpg',59.99,70,'2025-05-15 13:15:02'),(74,8,'Car Vacuum Cleaner','Portable vacuum cleaner for car interiors.','uploads/products/product_682605e151d23.jpg',39.99,90,'2025-05-15 13:15:02'),(75,8,'LED Headlight Bulbs','Bright and efficient car headlights.','uploads/products/6825ff184a38f_1747320600.jpg',29.99,74,'2025-05-15 13:15:02'),(76,8,'Car Seat Cover Set','Universal fit for most vehicles.','uploads/products/682600a829c56_1747321000.jpg',59.99,50,'2025-05-15 13:15:02'),(77,8,'Dash Cam 1080p','HD dash cam with night vision.','uploads/products/product_6826041f48544.webp',89.99,35,'2025-05-15 13:15:02'),(78,9,'Organic Rice 5kg','Premium long grain organic rice.','uploads/products/product_68260411e33b9.png',15.99,149,'2025-05-15 13:15:02'),(79,9,'Ground Coffee 1lb','Freshly roasted Arabica beans.','uploads/products/product_682603b7ba663.webp',11.99,120,'2025-05-15 13:15:02'),(80,9,'Olive Oil 1L','Extra virgin cold-pressed olive oil.','uploads/products/product_682603abd9dff.webp',9.99,140,'2025-05-15 13:15:02'),(81,9,'Breakfast Cereal','High fiber whole grain cereal.','uploads/products/product_68260399e7f80.webp',6.99,110,'2025-05-15 13:15:02'),(82,10,'Dog Food 10kg','Complete and balanced dry food.','uploads/products/product_6826031cbfb09.jpg',34.99,100,'2025-05-15 13:15:02'),(83,10,'Cat Scratching Post','Durable scratching post with toys.','uploads/products/product_6826030f748e3.jpg',29.99,50,'2025-05-15 13:15:02'),(84,10,'Pet Shampoo','Gentle formula for dogs and cats.','uploads/products/product_68260300cbc3b.jpg',12.99,70,'2025-05-15 13:15:02'),(85,10,'Chew Toy Pack','Set of 5 toys for dogs.','uploads/products/6825ff756c50a_1747320693.jpg',19.99,89,'2025-05-15 13:15:02');
/*!40000 ALTER TABLE `products` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reviews`
--

DROP TABLE IF EXISTS `reviews`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `reviews` (
  `review_id` int NOT NULL AUTO_INCREMENT,
  `product_id` int DEFAULT NULL,
  `user_id` int DEFAULT NULL,
  `rating` int DEFAULT NULL,
  `comment` text,
  `review_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`review_id`),
  KEY `product_id` (`product_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`),
  CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  CONSTRAINT `reviews_chk_1` CHECK ((`rating` between 1 and 5))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reviews`
--

LOCK TABLES `reviews` WRITE;
/*!40000 ALTER TABLE `reviews` DISABLE KEYS */;
/*!40000 ALTER TABLE `reviews` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `user_id` int NOT NULL AUTO_INCREMENT,
  `role` enum('customer','admin') DEFAULT 'customer',
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'admin','Darwin Velasco','darwin.velasco1210@gmail.com','$2y$10$AKZ5OB/UnwJ7gTYUR6sPlOs.iQVxpezFfue2OfmJd9ez8cPGeK72q','993414121','Tiniguiban','2025-05-15 04:10:55'),(2,'customer','Aprille Guanson','aprille1@gmail.com','$2y$10$kmLL3zFmwbp/AseNFl8.J.kFI4sYQ4az1CfaPEyyYf0b6hKlg0cOq','09946768879','Tiniguiban','2025-05-15 13:37:16'),(3,'admin','RJ Coloquit','rjcoloquit@gmail.com','$2y$10$2IZbnmlwx5ZlF4acI7GiNOtFEVDQznWjdU5JAj8IBfsMF13vJCBdW','09563080167','San Manuel Grounds','2025-05-16 12:20:44');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `wishlist`
--

DROP TABLE IF EXISTS `wishlist`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `wishlist` (
  `wishlist_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `product_id` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`wishlist_id`),
  UNIQUE KEY `unique_user_product` (`user_id`,`product_id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `wishlist_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  CONSTRAINT `wishlist_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wishlist`
--

LOCK TABLES `wishlist` WRITE;
/*!40000 ALTER TABLE `wishlist` DISABLE KEYS */;
INSERT INTO `wishlist` VALUES (1,1,56,'2025-05-15 16:21:50'),(2,3,58,'2025-05-16 14:16:17');
/*!40000 ALTER TABLE `wishlist` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-05-19 21:37:10
