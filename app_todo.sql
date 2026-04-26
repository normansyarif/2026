-- phpMyAdmin SQL Dump
-- version 5.2.1deb3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Apr 25, 2026 at 05:51 PM
-- Server version: 8.0.45-0ubuntu0.24.04.1
-- PHP Version: 8.3.6

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `app_todo`
--

-- --------------------------------------------------------

--
-- Table structure for table `app_settings`
--

CREATE TABLE `app_settings` (
  `id` bigint UNSIGNED NOT NULL,
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `app_settings`
--

INSERT INTO `app_settings` (`id`, `key`, `value`, `created_at`, `updated_at`) VALUES
(1, 'login_username', 'norman', '2026-03-24 23:31:54', '2026-03-24 23:31:54'),
(2, 'login_password_hash', '$2y$12$C1/aAOich2dnUBsOEplioODrOu8q6AQmU5ziDXejFBROIJWWNvz.m', '2026-03-24 23:31:55', '2026-03-24 23:31:55'),
(3, 'timeline_start_date', '2026-04-01', '2026-04-01 02:25:22', '2026-04-01 02:25:22'),
(4, 'timeline_deadline_date', '2027-02-28', '2026-04-01 02:25:22', '2026-04-01 02:25:22');

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `daily_completion_statuses`
--

CREATE TABLE `daily_completion_statuses` (
  `id` bigint UNSIGNED NOT NULL,
  `tracked_for` date NOT NULL,
  `state` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `target_weight` decimal(8,2) DEFAULT NULL,
  `rolling_average_weight` decimal(8,2) DEFAULT NULL,
  `scheduled_habit_count` int UNSIGNED NOT NULL DEFAULT '0',
  `completed_habit_count` int UNSIGNED NOT NULL DEFAULT '0',
  `habit_snapshot` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `daily_completion_statuses`
--

INSERT INTO `daily_completion_statuses` (`id`, `tracked_for`, `state`, `target_weight`, `rolling_average_weight`, `scheduled_habit_count`, `completed_habit_count`, `habit_snapshot`, `created_at`, `updated_at`) VALUES
(1, '2026-04-01', 'missed', 105.60, 105.60, 3, 1, '[{\"goal\": 1, \"name\": \"Practice Piano\", \"unit\": \"count\", \"value\": \"No log\", \"status\": \"Incomplete\", \"todo_id\": 1, \"completed\": false}, {\"goal\": 6000, \"name\": \"Walk\", \"unit\": \"steps\", \"value\": \"6172 / 6000 steps\", \"status\": \"Completed\", \"todo_id\": 2, \"completed\": true}, {\"goal\": 2, \"name\": \"Wash face\", \"unit\": \"counts\", \"value\": \"1 / 2 counts\", \"status\": \"Incomplete\", \"todo_id\": 5, \"completed\": false}]', '2026-04-01 12:34:43', '2026-04-01 18:14:03'),
(2, '2026-04-02', 'missed', 105.46, 105.35, 3, 2, '[{\"goal\": 6000, \"name\": \"Walk\", \"unit\": \"steps\", \"value\": \"6000 / 6000 steps\", \"status\": \"Completed\", \"todo_id\": 2, \"completed\": true}, {\"goal\": 1, \"name\": \"Practice Piano\", \"unit\": \"count\", \"value\": \"No log\", \"status\": \"Incomplete\", \"todo_id\": 1, \"completed\": false}, {\"goal\": 2, \"name\": \"Wash face\", \"unit\": \"counts\", \"value\": \"2 / 2 counts\", \"status\": \"Completed\", \"todo_id\": 5, \"completed\": true}]', '2026-04-02 15:06:59', '2026-04-02 23:58:19'),
(3, '2026-04-03', 'complete', 105.32, 105.03, 3, 3, '[{\"goal\": 3000, \"name\": \"Walk\", \"unit\": \"steps\", \"value\": \"3596 / 3000 steps\", \"status\": \"Completed\", \"todo_id\": 3, \"completed\": true}, {\"goal\": 1, \"name\": \"Practice Piano\", \"unit\": \"count\", \"value\": \"1 / 1 count\", \"status\": \"Completed\", \"todo_id\": 1, \"completed\": true}, {\"goal\": 2, \"name\": \"Wash face\", \"unit\": \"counts\", \"value\": \"2 / 2 counts\", \"status\": \"Completed\", \"todo_id\": 5, \"completed\": true}]', '2026-04-03 14:55:38', '2026-04-03 23:44:54'),
(4, '2026-04-04', 'complete', 105.18, 104.70, 3, 3, '[{\"goal\": 6000, \"name\": \"Walk\", \"unit\": \"steps\", \"value\": \"6034 / 6000 steps\", \"status\": \"Completed\", \"todo_id\": 2, \"completed\": true}, {\"goal\": 1, \"name\": \"Practice Piano\", \"unit\": \"count\", \"value\": \"1 / 1 count\", \"status\": \"Completed\", \"todo_id\": 1, \"completed\": true}, {\"goal\": 2, \"name\": \"Wash face\", \"unit\": \"counts\", \"value\": \"2 / 2 counts\", \"status\": \"Completed\", \"todo_id\": 5, \"completed\": true}]', '2026-04-04 11:43:17', '2026-04-04 21:21:51'),
(5, '2026-04-13', 'missed', 103.90, 104.60, 3, 0, '[{\"goal\": 3000, \"name\": \"Walk\", \"unit\": \"steps\", \"value\": \"No log\", \"status\": \"Incomplete\", \"todo_id\": 3, \"completed\": false}, {\"goal\": 1, \"name\": \"Practice Piano\", \"unit\": \"count\", \"value\": \"No log\", \"status\": \"Incomplete\", \"todo_id\": 1, \"completed\": false}, {\"goal\": 2, \"name\": \"Wash face\", \"unit\": \"counts\", \"value\": \"No log\", \"status\": \"Incomplete\", \"todo_id\": 5, \"completed\": false}]', '2026-04-13 13:54:18', '2026-04-13 13:54:18');

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `goals`
--

CREATE TABLE `goals` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `goals`
--

INSERT INTO `goals` (`id`, `name`, `created_at`, `updated_at`) VALUES
(1, 'Be A Piano Player', '2026-04-01 02:44:33', '2026-04-01 02:44:33');

-- --------------------------------------------------------

--
-- Table structure for table `goal_milestones`
--

CREATE TABLE `goal_milestones` (
  `id` bigint UNSIGNED NOT NULL,
  `goal_id` bigint UNSIGNED NOT NULL,
  `name` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `estimated_completion_month` date NOT NULL,
  `sort_order` int UNSIGNED NOT NULL DEFAULT '0',
  `completed` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `goal_milestones`
--

INSERT INTO `goal_milestones` (`id`, `goal_id`, `name`, `estimated_completion_month`, `sort_order`, `completed`, `created_at`, `updated_at`) VALUES
(1, 1, 'Finish level 1', '2026-04-01', 18, 0, '2026-04-01 02:45:33', '2026-04-01 02:50:45'),
(2, 1, 'Finish level 2', '2026-04-01', 17, 0, '2026-04-01 02:45:43', '2026-04-01 02:50:51'),
(3, 1, 'Finish level 3', '2026-04-01', 16, 0, '2026-04-01 02:45:50', '2026-04-01 02:50:56'),
(4, 1, 'Finish level 4', '2026-05-01', 15, 0, '2026-04-01 02:45:57', '2026-04-01 02:50:45'),
(5, 1, 'Finish read music intro (vid. 15)', '2026-06-01', 14, 0, '2026-04-01 02:46:06', '2026-04-01 02:51:19'),
(6, 1, 'Finish read music intro (vid. 31)', '2026-07-01', 13, 0, '2026-04-01 02:46:14', '2026-04-01 02:51:23'),
(7, 1, 'Finish level 5.2', '2026-08-01', 12, 0, '2026-04-01 02:46:21', '2026-04-01 02:51:52'),
(8, 1, 'Finish level 5.3', '2026-08-01', 11, 0, '2026-04-01 02:46:28', '2026-04-01 02:51:56'),
(9, 1, 'Finish level 6.1', '2026-09-01', 10, 0, '2026-04-01 02:46:37', '2026-04-01 02:52:08'),
(10, 1, 'Finish level 6.2', '2026-09-01', 9, 0, '2026-04-01 02:46:44', '2026-04-01 02:52:12'),
(11, 1, 'Finish level 6.3', '2026-10-01', 8, 0, '2026-04-01 02:46:51', '2026-04-01 02:52:23'),
(12, 1, 'Finish level 6.4', '2026-10-01', 7, 0, '2026-04-01 02:46:58', '2026-04-01 02:52:27'),
(13, 1, 'Finish level 7.1', '2026-11-01', 6, 0, '2026-04-01 02:47:05', '2026-04-01 02:52:37'),
(14, 1, 'Finish level 7.2', '2026-11-01', 5, 0, '2026-04-01 02:47:12', '2026-04-01 02:52:40'),
(15, 1, 'Finish level 7.3', '2026-12-01', 4, 0, '2026-04-01 02:47:19', '2026-04-01 02:52:46'),
(16, 1, 'Finish level 7.4', '2026-12-01', 3, 0, '2026-04-01 02:47:26', '2026-04-01 02:52:49'),
(17, 1, 'Play Bach Minuet III in G (lv. 8.1.3)', '2027-01-01', 2, 0, '2026-04-01 02:47:32', '2026-04-01 02:52:59'),
(18, 1, 'Play Handel Impertinence (lv. 8.1.5)', '2027-02-01', 1, 0, '2026-04-01 02:50:20', '2026-04-01 02:53:06');

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint UNSIGNED NOT NULL,
  `reserved_at` int UNSIGNED DEFAULT NULL,
  `available_at` int UNSIGNED NOT NULL,
  `created_at` int UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int UNSIGNED NOT NULL,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2026_03_24_201100_create_todos_table', 1),
(5, '2026_03_24_201200_create_todo_logs_table', 1),
(6, '2026_03_24_210000_add_sort_order_to_todos_table', 1),
(7, '2026_03_24_220000_create_weight_loss_goals_table', 1),
(8, '2026_03_24_230000_create_weight_logs_table', 1),
(9, '2026_03_25_000000_create_goals_table', 1),
(10, '2026_03_25_000100_create_goal_milestones_table', 1),
(11, '2026_03_25_000200_add_sort_order_to_goal_milestones_table', 1),
(12, '2026_03_25_000300_add_completed_to_goal_milestones_table', 1),
(13, '2026_03_25_000400_create_app_settings_table', 1),
(14, '2026_03_25_000500_create_daily_completion_statuses_table', 2);

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `todos`
--

CREATE TABLE `todos` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `days_of_week` json NOT NULL,
  `daily_goal` decimal(10,2) NOT NULL,
  `unit` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sort_order` int UNSIGNED NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `todos`
--

INSERT INTO `todos` (`id`, `name`, `days_of_week`, `daily_goal`, `unit`, `sort_order`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Practice piano', '[\"monday\", \"tuesday\", \"wednesday\", \"thursday\", \"friday\", \"saturday\", \"sunday\"]', 1.00, 'count', 4, 1, '2026-04-01 02:26:52', '2026-04-20 02:35:42'),
(2, 'Walk', '[\"monday\", \"tuesday\", \"wednesday\", \"thursday\", \"friday\", \"saturday\", \"sunday\"]', 1.00, 'count', 5, 1, '2026-04-01 02:27:50', '2026-04-20 02:35:42'),
(5, 'Wash face', '[\"monday\", \"tuesday\", \"wednesday\", \"thursday\", \"friday\", \"saturday\", \"sunday\"]', 2.00, 'counts', 6, 1, '2026-04-01 03:12:49', '2026-04-20 02:35:42'),
(6, 'Eat below 1500 cals', '[\"monday\", \"tuesday\", \"wednesday\", \"thursday\", \"friday\", \"saturday\", \"sunday\"]', 1.00, 'count', 7, 1, '2026-04-19 02:27:01', '2026-04-20 02:35:42'),
(7, 'Clean room', '[\"sunday\"]', 1.00, 'count', 1, 1, '2026-04-19 02:31:52', '2026-04-20 02:35:42'),
(8, 'Do laundry', '[\"sunday\"]', 1.00, 'count', 2, 1, '2026-04-19 02:32:05', '2026-04-20 02:35:42'),
(9, 'Work', '[\"monday\", \"tuesday\", \"wednesday\", \"thursday\", \"friday\"]', 1.00, 'ticket', 3, 1, '2026-04-20 02:35:21', '2026-04-20 02:35:42');

-- --------------------------------------------------------

--
-- Table structure for table `todo_logs`
--

CREATE TABLE `todo_logs` (
  `id` bigint UNSIGNED NOT NULL,
  `todo_id` bigint UNSIGNED NOT NULL,
  `logged_for` date NOT NULL,
  `value` decimal(10,2) NOT NULL,
  `completed` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `weight_logs`
--

CREATE TABLE `weight_logs` (
  `id` bigint UNSIGNED NOT NULL,
  `logged_for` date NOT NULL,
  `weight` decimal(6,2) NOT NULL,
  `rolling_average_weight` decimal(6,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `weight_logs`
--

INSERT INTO `weight_logs` (`id`, `logged_for`, `weight`, `rolling_average_weight`, `created_at`, `updated_at`) VALUES
(1, '2026-04-01', 105.60, 105.60, '2026-04-01 12:34:43', '2026-04-01 12:34:43'),
(2, '2026-04-02', 105.10, 105.35, '2026-04-02 15:06:59', '2026-04-02 15:06:59'),
(3, '2026-04-03', 104.40, 105.03, '2026-04-03 14:55:38', '2026-04-03 14:55:38'),
(4, '2026-04-04', 103.70, 104.70, '2026-04-04 11:43:17', '2026-04-04 11:43:17'),
(5, '2026-04-13', 104.60, 104.60, '2026-04-13 13:54:18', '2026-04-13 13:54:18');

-- --------------------------------------------------------

--
-- Table structure for table `weight_loss_goals`
--

CREATE TABLE `weight_loss_goals` (
  `id` bigint UNSIGNED NOT NULL,
  `month` date NOT NULL,
  `starting_weight` decimal(6,2) NOT NULL,
  `goal_weight` decimal(6,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `weight_loss_goals`
--

INSERT INTO `weight_loss_goals` (`id`, `month`, `starting_weight`, `goal_weight`, `created_at`, `updated_at`) VALUES
(1, '2026-04-01', 105.60, 101.50, '2026-04-01 02:41:24', '2026-04-01 02:41:24'),
(2, '2026-05-01', 101.50, 98.20, '2026-04-01 02:41:54', '2026-04-01 02:41:54'),
(3, '2026-06-01', 98.20, 95.00, '2026-04-01 02:42:05', '2026-04-01 02:42:05'),
(4, '2026-07-01', 95.00, 92.00, '2026-04-01 02:42:17', '2026-04-01 02:42:17'),
(5, '2026-08-01', 92.00, 89.20, '2026-04-01 02:42:31', '2026-04-01 02:42:31'),
(6, '2026-09-01', 89.20, 86.50, '2026-04-01 02:42:43', '2026-04-01 02:42:43'),
(7, '2026-10-01', 86.50, 83.90, '2026-04-01 02:42:58', '2026-04-01 02:42:58'),
(8, '2026-11-01', 83.90, 81.50, '2026-04-01 02:43:09', '2026-04-01 02:43:09'),
(9, '2026-12-01', 81.50, 79.20, '2026-04-01 02:43:21', '2026-04-01 02:43:21'),
(10, '2027-01-01', 79.20, 77.00, '2026-04-01 02:43:31', '2026-04-01 02:43:31'),
(11, '2027-02-01', 77.00, 75.00, '2026-04-01 02:43:35', '2026-04-01 02:43:35');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `app_settings`
--
ALTER TABLE `app_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `app_settings_key_unique` (`key`);

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_expiration_index` (`expiration`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_locks_expiration_index` (`expiration`);

--
-- Indexes for table `daily_completion_statuses`
--
ALTER TABLE `daily_completion_statuses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `daily_completion_statuses_tracked_for_unique` (`tracked_for`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `goals`
--
ALTER TABLE `goals`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `goal_milestones`
--
ALTER TABLE `goal_milestones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `goal_milestones_goal_id_foreign` (`goal_id`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indexes for table `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `todos`
--
ALTER TABLE `todos`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `todo_logs`
--
ALTER TABLE `todo_logs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `todo_logs_todo_id_logged_for_unique` (`todo_id`,`logged_for`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- Indexes for table `weight_logs`
--
ALTER TABLE `weight_logs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `weight_logs_logged_for_unique` (`logged_for`);

--
-- Indexes for table `weight_loss_goals`
--
ALTER TABLE `weight_loss_goals`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `weight_loss_goals_month_unique` (`month`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `app_settings`
--
ALTER TABLE `app_settings`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `daily_completion_statuses`
--
ALTER TABLE `daily_completion_statuses`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `goals`
--
ALTER TABLE `goals`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `goal_milestones`
--
ALTER TABLE `goal_milestones`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `todos`
--
ALTER TABLE `todos`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `todo_logs`
--
ALTER TABLE `todo_logs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `weight_logs`
--
ALTER TABLE `weight_logs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `weight_loss_goals`
--
ALTER TABLE `weight_loss_goals`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `goal_milestones`
--
ALTER TABLE `goal_milestones`
  ADD CONSTRAINT `goal_milestones_goal_id_foreign` FOREIGN KEY (`goal_id`) REFERENCES `goals` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `todo_logs`
--
ALTER TABLE `todo_logs`
  ADD CONSTRAINT `todo_logs_todo_id_foreign` FOREIGN KEY (`todo_id`) REFERENCES `todos` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
