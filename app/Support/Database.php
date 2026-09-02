<?php

namespace App\Support;

use PDO;
use PDOException;

class Database
{
    private static ?PDO $pdo = null;

    public static function connect(): PDO
    {
        if (self::$pdo !== null) {
            return self::$pdo;
        }

        $config = require __DIR__ . '/../../config/database.php';
        $driver = $config['default'] ?? 'mysql';

        if ($driver === 'mysql') {
            try {
                $dbConfig = $config['connections']['mysql'];
                $dsn = sprintf(
                    "mysql:host=%s;port=%s;dbname=%s;charset=%s",
                    $dbConfig['host'],
                    $dbConfig['port'],
                    $dbConfig['database'],
                    $dbConfig['charset']
                );
                self::$pdo = new PDO($dsn, $dbConfig['username'], $dbConfig['password'], $dbConfig['options']);
                return self::$pdo;
            } catch (PDOException $e) {
                // Fall back gracefully to SQLite if MySQL is not running locally
                error_log("MySQL connection failed: " . $e->getMessage() . ". Falling back to SQLite.");
            }
        }

        // SQLite initialization
        $sqlitePath = __DIR__ . '/../../storage/indrumar.sqlite';
        $isNewDb = !file_exists($sqlitePath);
        self::$pdo = new PDO("sqlite:" . $sqlitePath, null, null, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);

        if ($isNewDb) {
            self::initializeSqliteSchema(self::$pdo);
        } else {
            self::ensureMigrations(self::$pdo);
        }

        return self::$pdo;
    }

    private static function ensureMigrations(PDO $pdo): void
    {
        try {
            $pdo->query("SELECT is_paid FROM student_profiles LIMIT 1");
        } catch (\Throwable $e) {
            try {
                $pdo->exec("ALTER TABLE student_profiles ADD COLUMN is_paid INTEGER NOT NULL DEFAULT 1");
            } catch (\Throwable $ignored) {}
        }

        try {
            $pdo->query("SELECT is_paid FROM attendance_records LIMIT 1");
        } catch (\Throwable $e) {
            try {
                $pdo->exec("ALTER TABLE attendance_records ADD COLUMN is_paid INTEGER NOT NULL DEFAULT 1");
            } catch (\Throwable $ignored) {}
        }

        try {
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS system_settings (
                    key TEXT PRIMARY KEY,
                    value TEXT NOT NULL,
                    updated_at TEXT NOT NULL
                )
            ");
        } catch (\Throwable $ignored) {}

        try {
            $pdo->query("SELECT attachment_url FROM assignments LIMIT 1");
        } catch (\Throwable $e) {
            try {
                $pdo->exec("ALTER TABLE assignments ADD COLUMN attachment_url TEXT");
            } catch (\Throwable $ignored) {}
        }

        try {
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS assignment_submissions (
                    id TEXT PRIMARY KEY,
                    assignment_id TEXT NOT NULL,
                    student_id TEXT NOT NULL,
                    submission_text TEXT,
                    file_url TEXT,
                    file_name TEXT,
                    file_type TEXT,
                    status TEXT DEFAULT 'submitted',
                    teacher_feedback TEXT,
                    grade REAL,
                    submitted_at TEXT NOT NULL,
                    updated_at TEXT NOT NULL
                )
            ");
        } catch (\Throwable $ignored) {}

        try {
            $pdo->query("SELECT lesson_id FROM assessments LIMIT 1");
        } catch (\Throwable $e) {
            try {
                $pdo->exec("ALTER TABLE assessments ADD COLUMN lesson_id TEXT");
            } catch (\Throwable $ignored) {}
        }

        try {
            $pdo->query("SELECT username FROM users LIMIT 1");
        } catch (\Throwable $e) {
            try {
                $pdo->exec("ALTER TABLE users ADD COLUMN username TEXT");
            } catch (\Throwable $ignored) {}
        }
    }

    public static function query(string $sql, array $params = []): array
    {
        $stmt = self::connect()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function queryOne(string $sql, array $params = []): ?array
    {
        $stmt = self::connect()->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public static function execute(string $sql, array $params = []): bool
    {
        $stmt = self::connect()->prepare($sql);
        return $stmt->execute($params);
    }

    public static function lastInsertId(): string
    {
        return self::connect()->lastInsertId();
    }

    private static function initializeSqliteSchema(PDO $pdo): void
    {
        $schema = "
        CREATE TABLE IF NOT EXISTS workspaces (
            id TEXT PRIMARY KEY,
            name TEXT NOT NULL,
            owner_id TEXT NOT NULL,
            created_at TEXT NOT NULL,
            updated_at TEXT NOT NULL
        );
        CREATE TABLE IF NOT EXISTS users (
            id TEXT PRIMARY KEY,
            email TEXT UNIQUE NOT NULL,
            password_hash TEXT NOT NULL,
            role TEXT NOT NULL,
            first_name TEXT NOT NULL,
            last_name TEXT NOT NULL,
            phone TEXT,
            avatar_url TEXT,
            is_active INTEGER DEFAULT 1,
            created_at TEXT NOT NULL,
            updated_at TEXT NOT NULL
        );
        CREATE TABLE IF NOT EXISTS teacher_profiles (
            id TEXT PRIMARY KEY,
            user_id TEXT UNIQUE NOT NULL,
            title TEXT NOT NULL,
            phone TEXT,
            bio TEXT,
            created_at TEXT NOT NULL,
            updated_at TEXT NOT NULL
        );
        CREATE TABLE IF NOT EXISTS student_profiles (
            id TEXT PRIMARY KEY,
            user_id TEXT UNIQUE,
            workspace_id TEXT NOT NULL,
            first_name TEXT NOT NULL,
            last_name TEXT NOT NULL,
            father_initial TEXT,
            email TEXT,
            phone TEXT,
            date_of_birth TEXT,
            is_paid INTEGER DEFAULT 1,
            private_notes TEXT,
            created_at TEXT NOT NULL,
            updated_at TEXT NOT NULL
        );
        CREATE TABLE IF NOT EXISTS guardian_profiles (
            id TEXT PRIMARY KEY,
            user_id TEXT UNIQUE NOT NULL,
            workspace_id TEXT NOT NULL,
            first_name TEXT NOT NULL,
            last_name TEXT NOT NULL,
            email TEXT NOT NULL,
            phone TEXT NOT NULL,
            relationship TEXT DEFAULT 'legal_guardian',
            created_at TEXT NOT NULL,
            updated_at TEXT NOT NULL
        );
        CREATE TABLE IF NOT EXISTS guardian_student_links (
            id TEXT PRIMARY KEY,
            guardian_id TEXT NOT NULL,
            student_id TEXT NOT NULL,
            status TEXT DEFAULT 'active',
            created_at TEXT NOT NULL
        );
        CREATE TABLE IF NOT EXISTS groups (
            id TEXT PRIMARY KEY,
            workspace_id TEXT NOT NULL,
            name TEXT NOT NULL,
            type TEXT NOT NULL,
            description TEXT,
            color_tag TEXT DEFAULT '#4A77DA',
            created_at TEXT NOT NULL,
            updated_at TEXT NOT NULL
        );
        CREATE TABLE IF NOT EXISTS group_enrollments (
            id TEXT PRIMARY KEY,
            group_id TEXT NOT NULL,
            student_id TEXT NOT NULL,
            enrolled_at TEXT NOT NULL,
            status TEXT DEFAULT 'active'
        );
        CREATE TABLE IF NOT EXISTS group_schedules (
            id TEXT PRIMARY KEY,
            group_id TEXT NOT NULL,
            day_of_week INTEGER NOT NULL,
            start_time TEXT NOT NULL,
            end_time TEXT NOT NULL,
            room_or_link TEXT
        );
        CREATE TABLE IF NOT EXISTS lessons (
            id TEXT PRIMARY KEY,
            group_id TEXT NOT NULL,
            title TEXT NOT NULL,
            lesson_date TEXT NOT NULL,
            start_time TEXT NOT NULL,
            end_time TEXT NOT NULL,
            lesson_notes TEXT,
            created_at TEXT NOT NULL
        );
        CREATE TABLE IF NOT EXISTS attendance_records (
            id TEXT PRIMARY KEY,
            lesson_id TEXT NOT NULL,
            student_id TEXT NOT NULL,
            status TEXT NOT NULL,
            note TEXT,
            created_at TEXT NOT NULL,
            updated_at TEXT NOT NULL
        );
        CREATE TABLE IF NOT EXISTS assignments (
            id TEXT PRIMARY KEY,
            group_id TEXT NOT NULL,
            title TEXT NOT NULL,
            description TEXT NOT NULL,
            attachment_url TEXT,
            assigned_date TEXT NOT NULL,
            due_date TEXT NOT NULL,
            created_at TEXT NOT NULL
        );
        CREATE TABLE IF NOT EXISTS assignment_submissions (
            id TEXT PRIMARY KEY,
            assignment_id TEXT NOT NULL,
            student_id TEXT NOT NULL,
            submission_text TEXT,
            file_url TEXT,
            file_name TEXT,
            file_type TEXT,
            status TEXT DEFAULT 'submitted',
            teacher_feedback TEXT,
            grade REAL,
            submitted_at TEXT NOT NULL,
            updated_at TEXT NOT NULL
        );
        CREATE TABLE IF NOT EXISTS learning_materials (
            id TEXT PRIMARY KEY,
            group_id TEXT NOT NULL,
            lesson_id TEXT,
            title TEXT NOT NULL,
            url TEXT,
            file_type TEXT NOT NULL,
            created_at TEXT NOT NULL
        );
        CREATE TABLE IF NOT EXISTS assessments (
            id TEXT PRIMARY KEY,
            group_id TEXT NOT NULL,
            lesson_id TEXT,
            title TEXT NOT NULL,
            assessment_type TEXT NOT NULL,
            max_score REAL DEFAULT 10.0,
            assessment_date TEXT NOT NULL,
            created_at TEXT NOT NULL
        );
        CREATE TABLE IF NOT EXISTS assessment_results (
            id TEXT PRIMARY KEY,
            assessment_id TEXT NOT NULL,
            student_id TEXT NOT NULL,
            score REAL NOT NULL,
            private_teacher_notes TEXT,
            published_feedback TEXT,
            is_published INTEGER DEFAULT 1,
            created_at TEXT NOT NULL,
            updated_at TEXT NOT NULL
        );
        CREATE TABLE IF NOT EXISTS published_feedbacks (
            id TEXT PRIMARY KEY,
            student_id TEXT NOT NULL,
            content TEXT NOT NULL,
            category TEXT DEFAULT 'progress',
            created_at TEXT NOT NULL
        );
        CREATE TABLE IF NOT EXISTS announcements (
            id TEXT PRIMARY KEY,
            group_id TEXT,
            title TEXT NOT NULL,
            content TEXT NOT NULL,
            created_at TEXT NOT NULL
        );
        CREATE TABLE IF NOT EXISTS conversations (
            id TEXT PRIMARY KEY,
            teacher_id TEXT NOT NULL,
            guardian_id TEXT NOT NULL,
            student_id TEXT,
            updated_at TEXT NOT NULL,
            created_at TEXT NOT NULL
        );
        CREATE TABLE IF NOT EXISTS messages (
            id TEXT PRIMARY KEY,
            conversation_id TEXT NOT NULL,
            sender_role TEXT NOT NULL,
            sender_id TEXT NOT NULL,
            content TEXT NOT NULL,
            is_read INTEGER DEFAULT 0,
            sent_at TEXT NOT NULL
        );
        CREATE TABLE IF NOT EXISTS learning_goals (
            id TEXT PRIMARY KEY,
            student_id TEXT NOT NULL,
            title TEXT NOT NULL,
            target_date TEXT,
            is_completed INTEGER DEFAULT 0,
            completed_at TEXT,
            created_at TEXT NOT NULL
        );
        ";
        $pdo->exec($schema);

        try {
            $pdo->exec("ALTER TABLE assessments ADD COLUMN lesson_id TEXT");
        } catch (\Throwable $e) {}
        try {
            $pdo->exec("ALTER TABLE users ADD COLUMN username TEXT");
        } catch (\Throwable $e) {}
        
        // Seed demo data immediately
        \Database\Seeds\DemoSeeder::seed($pdo);
    }
}
