<?php

namespace App\Repositories;

use App\Support\Database;

class UserRepository
{
    public function findByEmail(string $email): ?array
    {
        return Database::queryOne("SELECT * FROM users WHERE email = ? AND is_active = 1", [$email]);
    }

    public function findById(string $id): ?array
    {
        return Database::queryOne("SELECT * FROM users WHERE id = ?", [$id]);
    }

    public function getTeacherProfile(string $userId): ?array
    {
        return Database::queryOne("SELECT * FROM teacher_profiles WHERE user_id = ?", [$userId]);
    }

    public function getGuardianProfile(string $userId): ?array
    {
        return Database::queryOne("SELECT * FROM guardian_profiles WHERE user_id = ?", [$userId]);
    }

    public function getStudentProfile(string $userId): ?array
    {
        return Database::queryOne("SELECT * FROM student_profiles WHERE user_id = ?", [$userId]);
    }

    public function getAllUsers(): array
    {
        return Database::query("SELECT id, email, role, first_name, last_name, phone, created_at FROM users ORDER BY role ASC, first_name ASC");
    }

    public function createStudentUser(array $data): string
    {
        $userId = 'usr_' . bin2hex(random_bytes(6));
        $studentId = 'stu_' . bin2hex(random_bytes(6));
        $now = date('Y-m-d H:i:s');
        $hash = password_hash($data['password'] ?: 'parola123', PASSWORD_DEFAULT);

        Database::execute(
            "INSERT INTO users (id, email, password_hash, role, first_name, last_name, is_active, created_at, updated_at) VALUES (?, ?, ?, 'student', ?, ?, 1, ?, ?)",
            [$userId, $data['email'], $hash, $data['first_name'], $data['last_name'], $now, $now]
        );

        Database::execute(
            "INSERT INTO student_profiles (id, user_id, workspace_id, first_name, last_name, email, phone, is_paid, created_at, updated_at) VALUES (?, ?, 'ws_radu_teodorescu', ?, ?, ?, ?, 1, ?, ?)",
            [$studentId, $userId, $data['first_name'], $data['last_name'], $data['email'], $data['phone'] ?? null, $now, $now]
        );

        if (!empty($data['group_id'])) {
            $enrId = 'enr_' . bin2hex(random_bytes(6));
            Database::execute(
                "INSERT OR REPLACE INTO group_enrollments (id, group_id, student_id, enrolled_at, status) VALUES (?, ?, ?, ?, 'active')",
                [$enrId, $data['group_id'], $studentId, $now]
            );
        }

        return $userId;
    }

    public function createParentUser(array $data): string
    {
        $userId = 'usr_' . bin2hex(random_bytes(6));
        $guardianId = 'grd_' . bin2hex(random_bytes(6));
        $now = date('Y-m-d H:i:s');
        $hash = password_hash($data['password'] ?: 'parola123', PASSWORD_DEFAULT);

        Database::execute(
            "INSERT INTO users (id, email, password_hash, role, first_name, last_name, phone, is_active, created_at, updated_at) VALUES (?, ?, ?, 'parent', ?, ?, ?, 1, ?, ?)",
            [$userId, $data['email'], $hash, $data['first_name'], $data['last_name'], $data['phone'] ?? null, $now, $now]
        );

        Database::execute(
            "INSERT INTO guardian_profiles (id, user_id, workspace_id, first_name, last_name, email, phone, relationship, created_at, updated_at) VALUES (?, ?, 'ws_radu_teodorescu', ?, ?, ?, ?, ?, ?, ?)",
            [$guardianId, $userId, $data['first_name'], $data['last_name'], $data['email'], $data['phone'] ?? '', $data['relationship'] ?? 'legal_guardian', $now, $now]
        );

        if (!empty($data['student_id'])) {
            $linkId = 'gsl_' . bin2hex(random_bytes(6));
            Database::execute(
                "INSERT OR REPLACE INTO guardian_student_links (id, guardian_id, student_id, status, created_at) VALUES (?, ?, ?, 'active', ?)",
                [$linkId, $guardianId, $data['student_id'], $now]
            );
        }

        return $userId;
    }

    public function resetPassword(string $userId, string $newPassword): bool
    {
        $now = date('Y-m-d H:i:s');
        $hash = password_hash($newPassword, PASSWORD_DEFAULT);
        return Database::execute("UPDATE users SET password_hash = ?, updated_at = ? WHERE id = ?", [$hash, $now, $userId]);
    }

    public function deleteUserAccount(string $userId): bool
    {
        // Don't allow deleting the main teacher account
        $user = $this->findById($userId);
        if ($user && $user['role'] === 'teacher') return false;

        Database::execute("DELETE FROM student_profiles WHERE user_id = ?", [$userId]);
        Database::execute("DELETE FROM guardian_profiles WHERE user_id = ?", [$userId]);
        return Database::execute("DELETE FROM users WHERE id = ?", [$userId]);
    }

    public function updateTeacherBio(string $teacherId, string $title, ?string $phone, ?string $bio): bool
    {
        $now = date('Y-m-d H:i:s');
        return Database::execute(
            "UPDATE teacher_profiles SET title = ?, phone = ?, bio = ?, updated_at = ? WHERE id = ?",
            [$title, $phone, $bio, $now, $teacherId]
        );
    }
}
