<?php
require_once __DIR__ . '/config.php';

function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";charset=" . DB_CHARSET;
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);

            $pdo->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            $pdo->exec("USE `" . DB_NAME . "`");

            $pdo->exec("
                CREATE TABLE IF NOT EXISTS `project_visits` (
                    `id` INT AUTO_INCREMENT PRIMARY KEY,
                    `visit_date` DATE DEFAULT NULL,
                    `visit_no` VARCHAR(50) DEFAULT NULL,
                    `time_from` TIME DEFAULT NULL,
                    `time_to` TIME DEFAULT NULL,
                    `project_name_code` VARCHAR(255) DEFAULT NULL,
                    `tehsil` VARCHAR(100) DEFAULT NULL,
                    `town_district_division` VARCHAR(100) DEFAULT NULL,
                    `unit_construction_stage` VARCHAR(255) DEFAULT NULL,
                    `contractor_company` VARCHAR(255) DEFAULT NULL,
                    `committee_head` VARCHAR(255) DEFAULT NULL,
                    `previous_visit_date` DATE DEFAULT NULL,
                    `reason_for_visit` TEXT DEFAULT NULL,
                    `project_activities` TEXT DEFAULT NULL,
                    `activity_status` TEXT DEFAULT NULL,
                    `obstructions_violations` TEXT DEFAULT NULL,
                    `manpower_details` TEXT DEFAULT NULL,
                    `witnesses` TEXT DEFAULT NULL,
                    `supervisor_signature` VARCHAR(255) DEFAULT NULL,
                    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
            try {
                $pdo->exec("ALTER TABLE `project_visits`
                    MODIFY `visit_date` DATE DEFAULT NULL,
                    MODIFY `visit_no` VARCHAR(50) DEFAULT NULL,
                    MODIFY `time_from` TIME DEFAULT NULL,
                    MODIFY `time_to` TIME DEFAULT NULL,
                    MODIFY `project_name_code` VARCHAR(255) DEFAULT NULL,
                    MODIFY `unit_construction_stage` VARCHAR(255) DEFAULT NULL,
                    MODIFY `contractor_company` VARCHAR(255) DEFAULT NULL,
                    MODIFY `committee_head` VARCHAR(255) DEFAULT NULL,
                    MODIFY `reason_for_visit` TEXT DEFAULT NULL,
                    MODIFY `project_activities` TEXT DEFAULT NULL");
            } catch (PDOException $e) {
                // table may be new, ignore
            }
            try {
                $pdo->exec("ALTER TABLE `project_visits` ADD `witnesses` TEXT DEFAULT NULL AFTER `manpower_details`");
            } catch (PDOException $e) {
                // column may already exist
            }

            $pdo->exec("
                CREATE TABLE IF NOT EXISTS `users` (
                    `id` INT AUTO_INCREMENT PRIMARY KEY,
                    `username` VARCHAR(100) NOT NULL UNIQUE,
                    `password_hash` VARCHAR(255) NOT NULL,
                    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");

            $check = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
            if ($check == 0) {
                $default_hash = password_hash('admin123', PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (username, password_hash) VALUES (?, ?)");
                $stmt->execute(['admin', $default_hash]);
            }
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }
    return $pdo;
}
