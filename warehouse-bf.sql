-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Versión del servidor:         5.7.44-log - MySQL Community Server (GPL)
-- SO del servidor:              Win64
-- HeidiSQL Versión:             12.8.0.6908
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

-- Volcando estructura para tabla warehouse_bf.companies
CREATE TABLE IF NOT EXISTS `companies` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `company` varchar(255) CHARACTER SET utf8mb4 DEFAULT NULL,
  `rut` varchar(255) CHARACTER SET utf8mb4 DEFAULT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 NOT NULL,
  `phone` varchar(255) CHARACTER SET utf8mb4 DEFAULT NULL,
  `address` varchar(255) CHARACTER SET utf8mb4 DEFAULT NULL,
  `logo` varchar(255) CHARACTER SET utf8mb4 DEFAULT NULL,
  `giro` varchar(255) CHARACTER SET utf8mb4 DEFAULT NULL,
  `email_confirmation` tinyint(4) NOT NULL DEFAULT '0',
  `account_confirmed` tinyint(4) NOT NULL DEFAULT '0',
  `email_confirmation_date` timestamp NULL DEFAULT NULL,
  `account_confirmed_date` timestamp NULL DEFAULT NULL,
  `status` tinyint(4) NOT NULL DEFAULT '1',
  `user_created` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `user_updated` bigint(20) unsigned DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `user_deleted` bigint(20) unsigned DEFAULT NULL,
  `deleted` tinyint(4) NOT NULL DEFAULT '0',
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla warehouse_bf.companies: ~1 rows (aproximadamente)
INSERT INTO `companies` (`id`, `company`, `rut`, `email`, `phone`, `address`, `logo`, `giro`, `email_confirmation`, `account_confirmed`, `email_confirmation_date`, `account_confirmed_date`, `status`, `user_created`, `created_at`, `user_updated`, `updated_at`, `user_deleted`, `deleted`, `deleted_at`) VALUES
	(1, 'MDMN', '11111111-1', 'root@hitch.cl', NULL, NULL, NULL, NULL, 1, 1, NULL, NULL, 1, NULL, '2024-08-26 21:10:40', NULL, '2024-08-26 21:10:40', NULL, 0, NULL);

-- Volcando estructura para tabla warehouse_bf.departments
CREATE TABLE IF NOT EXISTS `departments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `department` varchar(255) CHARACTER SET utf8mb4 DEFAULT NULL,
  `company_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla warehouse_bf.departments: ~4 rows (aproximadamente)
INSERT INTO `departments` (`id`, `department`, `company_id`, `created_at`, `updated_at`) VALUES
	(1, 'Tecnología', 1, '2024-08-29 21:06:03', '2024-08-29 21:06:03'),
	(2, 'Recursos Humanos', 1, '2024-08-29 21:06:03', '2024-08-29 21:06:03'),
	(3, 'Finanzas', 1, '2024-08-29 21:06:03', '2024-08-29 21:06:03'),
	(4, 'Marketing', 1, '2024-08-29 21:06:03', '2024-08-29 21:06:03');

-- Volcando estructura para tabla warehouse_bf.migrations
CREATE TABLE IF NOT EXISTS `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=38 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla warehouse_bf.migrations: ~37 rows (aproximadamente)
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
	(1, '2019_12_14_000001_create_personal_access_tokens_table', 1),
	(2, '2024_08_24_162240_create_companies_table', 1),
	(3, '2024_08_24_162240_create_profiles_table', 1),
	(4, '2024_08_24_162241_create_users_table', 1),
	(5, '2024_08_24_162243_create_appointments_table', 1),
	(6, '2024_08_24_162243_create_contact_config_table', 1),
	(7, '2024_08_24_162243_create_contact_forms_table', 1),
	(8, '2024_08_24_162243_create_newsletter_subscribers_table', 1),
	(9, '2024_08_24_162243_create_permissions_table', 1),
	(10, '2024_08_24_162243_create_priority_table', 1),
	(11, '2024_08_24_162243_create_status_report_admin_table', 1),
	(12, '2024_08_24_162243_create_status_report_user_table', 1),
	(13, '2024_08_24_162244_create_harassment_types_table', 1),
	(14, '2024_08_24_162244_create_permission_profiles_table', 1),
	(15, '2024_08_24_162244_create_reports_table', 1),
	(16, '2024_08_24_162244_create_reset_password_table', 1),
	(17, '2024_08_24_162244_create_users_connections_table', 1),
	(18, '2024_08_24_162245_create_report_files_table', 1),
	(19, '2024_08_24_230916_add_company_active_to_users_table', 1),
	(20, '2024_08_25_003045_create_employees_table', 1),
	(21, '2024_08_25_043802_create_reports_comments_table', 1),
	(22, '2024_08_25_092520_create_report_comment_files_table', 1),
	(23, '2024_08_25_105244_add_principal_user__to_users_table', 1),
	(24, '2024_08_26_011844_add_update_fields_to_permission_profiles_table', 1),
	(25, '2024_08_26_015237_create_reports_timeline_table', 1),
	(26, '2024_08_26_032636_add_fields_cancelled_and_finished_report_table', 1),
	(27, '2024_08_26_034234_add_field_employee_id_report_table', 1),
	(28, '2024_08_26_035705_create_reports_resolutions_table', 1),
	(29, '2024_08_27_122241_add_expired_at_column_to_reports_table', 2),
	(30, '2024_08_27_145518_remove_psychological_support_column_from_reports_table', 3),
	(31, '2024_08_28_055638_add_text_color_column_to_priorities_table', 4),
	(32, '2024_08_28_121400_add_user_assigned_column_to_reports_table', 5),
	(33, '2024_08_29_155950_add_assigned_at_column_to_reports_table', 6),
	(34, '2024_08_29_165223_create_departments_table', 7),
	(35, '2024_08_29_165239_create_positions_table', 7),
	(36, '2024_08_29_172328_remove_department_position_columns_from_reports_table', 8),
	(37, '2024_08_29_172524_add_department_id_and_position_id_to_reports_table', 9);

-- Volcando estructura para tabla warehouse_bf.permissions
CREATE TABLE IF NOT EXISTS `permissions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `permission` varchar(255) CHARACTER SET utf8mb4 DEFAULT NULL,
  `description` varchar(255) CHARACTER SET utf8mb4 DEFAULT NULL,
  `order` int(11) DEFAULT NULL,
  `status` tinyint(4) NOT NULL DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `deleted` tinyint(4) NOT NULL DEFAULT '0',
  `category` varchar(255) CHARACTER SET utf8mb4 DEFAULT NULL,
  `code` varchar(255) CHARACTER SET utf8mb4 DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla warehouse_bf.permissions: ~9 rows (aproximadamente)
INSERT INTO `permissions` (`id`, `permission`, `description`, `order`, `status`, `created_at`, `deleted`, `category`, `code`) VALUES
	(1, 'Crear Perfiles', 'Permite crear perfiles', 2, 1, '2024-09-09 09:48:08', 0, 'PERFILES', 'ADD_PROFILES'),
	(2, 'Editar Perfiles', 'Permite editar perfiles', 3, 1, '2024-09-09 09:48:08', 0, 'PERFILES', 'EDIT_PROFILES'),
	(3, 'Eliminar Perfiles', 'Permite eliminar perfiles', 4, 1, '2024-09-09 09:48:08', 0, 'PERFILES', 'DEL_PROFILES'),
	(4, 'Listar Perfiles', 'Permite visualizar listado de perfiles', 1, 1, '2024-09-09 09:48:08', 0, 'PERFILES', 'LIST_PROFILES'),
	(5, 'Asignar permisos a Perfil', 'Permite asignar y quitar permisos a los perfiles', 5, 1, '2024-09-09 09:48:08', 0, 'PERFILES', 'EDIT_PERMISSIONS'),
	(6, 'Crear Usuarios', 'Permite crear usuarios', 2, 1, '2024-09-09 09:48:08', 0, 'USUARIOS', 'ADD_USERS'),
	(7, 'Editar Usuarios', 'Permite editar usuarios', 3, 1, '2024-09-09 09:48:08', 0, 'USUARIOS', 'EDIT_USERS'),
	(8, 'Eliminar Usuarios', 'Permite eliminar usuarios', 4, 1, '2024-09-09 09:48:08', 0, 'USUARIOS', 'DEL_USERS'),
	(9, 'Listar Usuarios', 'Permite visualizar listado de usuarios', 1, 1, '2024-09-09 09:48:08', 0, 'USUARIOS', 'LIST_USERS');

-- Volcando estructura para tabla warehouse_bf.permissions_profile
CREATE TABLE IF NOT EXISTS `permissions_profile` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `profile_id` bigint(20) unsigned DEFAULT NULL,
  `permission_id` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `user_created` bigint(20) unsigned DEFAULT NULL,
  `deleted` tinyint(4) NOT NULL DEFAULT '0',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `user_deleted` bigint(20) unsigned DEFAULT NULL,
  `user_updated` bigint(20) unsigned DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `permissions_profile_profile_id_foreign` (`profile_id`),
  KEY `permissions_profile_permission_id_foreign` (`permission_id`),
  CONSTRAINT `permissions_profile_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `permissions_profile_profile_id_foreign` FOREIGN KEY (`profile_id`) REFERENCES `profiles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla warehouse_bf.permissions_profile: ~13 rows (aproximadamente)
INSERT INTO `permissions_profile` (`id`, `profile_id`, `permission_id`, `created_at`, `user_created`, `deleted`, `deleted_at`, `user_deleted`, `user_updated`, `updated_at`) VALUES
	(17, 2, 6, '2025-01-15 02:00:20', 1, 0, NULL, NULL, NULL, '2025-01-15 02:00:20'),
	(18, 2, 7, '2025-01-15 02:00:20', 1, 0, NULL, NULL, NULL, '2025-01-15 02:00:20'),
	(19, 2, 8, '2025-01-15 02:00:20', 1, 0, NULL, NULL, NULL, '2025-01-15 02:00:20'),
	(20, 2, 9, '2025-01-15 02:00:20', 1, 0, NULL, NULL, NULL, '2025-01-15 02:00:20'),
	(21, 1, 1, '2025-01-15 02:00:27', 1, 0, NULL, NULL, NULL, '2025-01-15 02:00:27'),
	(22, 1, 2, '2025-01-15 02:00:27', 1, 0, NULL, NULL, NULL, '2025-01-15 02:00:27'),
	(23, 1, 3, '2025-01-15 02:00:27', 1, 0, NULL, NULL, NULL, '2025-01-15 02:00:27'),
	(24, 1, 4, '2025-01-15 02:00:27', 1, 0, NULL, NULL, NULL, '2025-01-15 02:00:27'),
	(25, 1, 5, '2025-01-15 02:00:27', 1, 0, NULL, NULL, NULL, '2025-01-15 02:00:27'),
	(26, 1, 6, '2025-01-15 02:00:27', 1, 0, NULL, NULL, NULL, '2025-01-15 02:00:27'),
	(27, 1, 7, '2025-01-15 02:00:27', 1, 0, NULL, NULL, NULL, '2025-01-15 02:00:27'),
	(28, 1, 8, '2025-01-15 02:00:27', 1, 0, NULL, NULL, NULL, '2025-01-15 02:00:27'),
	(29, 1, 9, '2025-01-15 02:00:27', 1, 0, NULL, NULL, NULL, '2025-01-15 02:00:27');

-- Volcando estructura para tabla warehouse_bf.personal_access_tokens
CREATE TABLE IF NOT EXISTS `personal_access_tokens` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint(20) unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla warehouse_bf.personal_access_tokens: ~0 rows (aproximadamente)

-- Volcando estructura para tabla warehouse_bf.profiles
CREATE TABLE IF NOT EXISTS `profiles` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `code` bigint(20) unsigned DEFAULT NULL,
  `profile` varchar(255) CHARACTER SET utf8mb4 NOT NULL,
  `description` varchar(255) CHARACTER SET utf8mb4 DEFAULT NULL,
  `status` tinyint(4) NOT NULL DEFAULT '1',
  `show_user` tinyint(4) NOT NULL DEFAULT '1',
  `user_created` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `user_updated` bigint(20) unsigned DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `user_deleted` bigint(20) unsigned DEFAULT NULL,
  `deleted` tinyint(4) NOT NULL DEFAULT '0',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `company_id` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `profiles_company_id_foreign` (`company_id`),
  CONSTRAINT `profiles_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla warehouse_bf.profiles: ~5 rows (aproximadamente)
INSERT INTO `profiles` (`id`, `code`, `profile`, `description`, `status`, `show_user`, `user_created`, `created_at`, `user_updated`, `updated_at`, `user_deleted`, `deleted`, `deleted_at`, `company_id`) VALUES
	(1, 999, 'SUPER ADMINISTRADOR', 'ACCESO TOTAL A SISTEMA', 1, 0, NULL, '2024-08-26 21:09:58', NULL, NULL, NULL, 0, NULL, NULL),
	(2, 100, 'ADMINISTRADOR', 'ACCESO SEMI TOTAL A SISTEMA', 1, 1, NULL, '2024-08-26 21:09:58', NULL, NULL, NULL, 0, NULL, NULL),
	(3, 200, 'GESTOR', 'ACCESO RESTRINGIDO', 1, 1, NULL, '2024-08-26 21:09:58', NULL, '2025-01-15 01:59:47', 1, 1, '2025-01-15 01:59:47', NULL),
	(4, 300, 'USUARIO', 'ACCESO RESTRINGIDO', 1, 1, NULL, '2024-08-26 21:09:58', 1, '2025-01-15 01:59:58', NULL, 0, NULL, NULL),
	(5, NULL, 'TEST', 'fdfsfgfgf', 1, 1, 1, '2025-01-15 00:18:03', 1, '2025-01-15 00:18:29', 1, 1, '2025-01-15 00:18:29', NULL);

-- Volcando estructura para tabla warehouse_bf.reset_password
CREATE TABLE IF NOT EXISTS `reset_password` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 DEFAULT NULL,
  `token` varchar(255) CHARACTER SET utf8mb4 DEFAULT NULL,
  `change_date` timestamp NULL DEFAULT NULL,
  `expiration_date` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `status` tinyint(4) NOT NULL DEFAULT '1',
  `company_id` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `reset_password_user_id_foreign` (`user_id`),
  KEY `reset_password_company_id_foreign` (`company_id`),
  CONSTRAINT `reset_password_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`),
  CONSTRAINT `reset_password_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla warehouse_bf.reset_password: ~0 rows (aproximadamente)

-- Volcando estructura para tabla warehouse_bf.users
CREATE TABLE IF NOT EXISTS `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 DEFAULT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 DEFAULT NULL,
  `username` varchar(255) CHARACTER SET utf8mb4 DEFAULT NULL,
  `rut` varchar(15) CHARACTER SET utf8mb4 DEFAULT NULL,
  `mobile` varchar(15) CHARACTER SET utf8mb4 DEFAULT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 DEFAULT NULL,
  `remember_token` varchar(255) CHARACTER SET utf8mb4 DEFAULT NULL,
  `connection_token` varchar(255) CHARACTER SET utf8mb4 DEFAULT NULL,
  `last_entry` timestamp NULL DEFAULT NULL,
  `activation_token` varchar(255) CHARACTER SET utf8mb4 DEFAULT NULL,
  `profile_id` bigint(20) unsigned NOT NULL DEFAULT '3',
  `status` tinyint(4) NOT NULL DEFAULT '0',
  `validate_password` tinyint(4) NOT NULL DEFAULT '0',
  `user_created` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `user_updated` bigint(20) unsigned DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `user_deleted` bigint(20) unsigned DEFAULT NULL,
  `deleted` tinyint(4) NOT NULL DEFAULT '0',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `user_confirmed` bigint(20) unsigned DEFAULT NULL,
  `account_confirmed` tinyint(4) NOT NULL DEFAULT '0',
  `account_confirmed_at` timestamp NULL DEFAULT NULL,
  `menu_type` varchar(255) CHARACTER SET utf8mb4 DEFAULT NULL,
  `company_id` bigint(20) unsigned DEFAULT NULL,
  `company_active` int(11) DEFAULT NULL,
  `principal_user` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `users_profile_id_foreign` (`profile_id`),
  KEY `users_company_id_foreign` (`company_id`),
  CONSTRAINT `users_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`),
  CONSTRAINT `users_profile_id_foreign` FOREIGN KEY (`profile_id`) REFERENCES `profiles` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla warehouse_bf.users: ~3 rows (aproximadamente)
INSERT INTO `users` (`id`, `name`, `email`, `username`, `rut`, `mobile`, `password`, `remember_token`, `connection_token`, `last_entry`, `activation_token`, `profile_id`, `status`, `validate_password`, `user_created`, `created_at`, `user_updated`, `updated_at`, `user_deleted`, `deleted`, `deleted_at`, `user_confirmed`, `account_confirmed`, `account_confirmed_at`, `menu_type`, `company_id`, `company_active`, `principal_user`) VALUES
	(1, 'SUPER USUARIO', 'root@leykarin-chile.cl', 'root@leykarin-chile.cl', NULL, NULL, '$2y$10$PEcp2KJuhvwtfQsiCSgMS.yNRHBnFWERm.6D92uZiXdZk8/R3Xo4m', NULL, 'm6OfM2Bj8c9GrfhP2yrhi4nzimmuaKQPzJL9My6m', '2025-01-14 23:43:22', NULL, 1, 1, 0, NULL, '2024-08-26 21:10:40', NULL, '2025-01-14 23:43:22', NULL, 0, NULL, NULL, 1, NULL, NULL, 1, NULL, 0),
	(2, 'TRABAJADOR', 'root@leykarin-chile.cll', '17287397-9', '172873979', NULL, '$2y$10$PEcp2KJuhvwtfQsiCSgMS.yNRHBnFWERm.6D92uZiXdZk8/R3Xo4m', NULL, 'gUvgsk7AARJg2GQb0Ay1h9vuWrkoPs6cAYtBjfSE', '2024-08-29 12:21:53', NULL, 4, 1, 0, NULL, '2024-08-26 21:14:40', NULL, '2024-08-29 12:21:53', NULL, 0, NULL, NULL, 1, NULL, NULL, 1, 1, 0),
	(4, 'GESTOR', 'root@leykarin-chile.clll', 'root@leykarin-chile.clll', NULL, NULL, '$2y$10$PEcp2KJuhvwtfQsiCSgMS.yNRHBnFWERm.6D92uZiXdZk8/R3Xo4m', NULL, 'w6hXUVd2aaL3WCbMMbJFumtnOJ75QjJQR0gUFXJu', '2025-01-15 01:02:10', NULL, 3, 1, 0, NULL, '2024-08-26 21:10:40', NULL, '2025-01-15 01:02:10', NULL, 0, NULL, NULL, 1, NULL, NULL, 1, NULL, 0);

-- Volcando estructura para tabla warehouse_bf.users_connections
CREATE TABLE IF NOT EXISTS `users_connections` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `last_login` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `ip_address` varchar(255) CHARACTER SET utf8mb4 DEFAULT NULL,
  `company_id` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `users_connections_user_id_foreign` (`user_id`),
  KEY `users_connections_company_id_foreign` (`company_id`),
  CONSTRAINT `users_connections_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`),
  CONSTRAINT `users_connections_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Volcando datos para la tabla warehouse_bf.users_connections: ~0 rows (aproximadamente)

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
