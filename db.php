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
                    `visit_date` DATE NOT NULL,
                    `visit_no` VARCHAR(50) NOT NULL,
                    `time_from` TIME NOT NULL,
                    `time_to` TIME NOT NULL,
                    `project_name_code` VARCHAR(255) NOT NULL,
                    `tehsil` VARCHAR(100) DEFAULT NULL,
                    `town_district_division` VARCHAR(100) DEFAULT NULL,
                    `unit_construction_stage` VARCHAR(255) NOT NULL,
                    `contractor_company` VARCHAR(255) NOT NULL,
                    `committee_head` VARCHAR(255) NOT NULL,
                    `previous_visit_date` DATE DEFAULT NULL,
                    `reason_for_visit` TEXT NOT NULL,
                    `project_activities` TEXT NOT NULL,
                    `activity_status` TEXT DEFAULT NULL,
                    `obstructions_violations` TEXT DEFAULT NULL,
                    `manpower_details` TEXT DEFAULT NULL,
                    `witness_name` VARCHAR(255) DEFAULT NULL,
                    `witness_responsibility` VARCHAR(255) DEFAULT NULL,
                    `witness_signature` VARCHAR(255) DEFAULT NULL,
                    `supervisor_signature` VARCHAR(255) DEFAULT NULL,
                    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }
    return $pdo;
}
