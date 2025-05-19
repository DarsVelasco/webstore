-- Create admin_permissions table
CREATE TABLE IF NOT EXISTS `admin_permissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `permission_name` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `permission_name` (`permission_name`),
  CONSTRAINT `admin_permissions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert default permissions for existing admin users
INSERT INTO admin_permissions (user_id, permission_name)
SELECT user_id, 'manage_products'
FROM users
WHERE role = 'admin';

INSERT INTO admin_permissions (user_id, permission_name)
SELECT user_id, 'manage_categories'
FROM users
WHERE role = 'admin';

INSERT INTO admin_permissions (user_id, permission_name)
SELECT user_id, 'manage_inventory'
FROM users
WHERE role = 'admin';

INSERT INTO admin_permissions (user_id, permission_name)
SELECT user_id, 'view_orders'
FROM users
WHERE role = 'admin';

INSERT INTO admin_permissions (user_id, permission_name)
SELECT user_id, 'manage_orders'
FROM users
WHERE role = 'admin';

INSERT INTO admin_permissions (user_id, permission_name)
SELECT user_id, 'view_users'
FROM users
WHERE role = 'admin';

INSERT INTO admin_permissions (user_id, permission_name)
SELECT user_id, 'manage_users'
FROM users
WHERE role = 'admin';

INSERT INTO admin_permissions (user_id, permission_name)
SELECT user_id, 'view_reports'
FROM users
WHERE role = 'admin';

INSERT INTO admin_permissions (user_id, permission_name)
SELECT user_id, 'manage_reports'
FROM users
WHERE role = 'admin'; 