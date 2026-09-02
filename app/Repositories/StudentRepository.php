<?php

namespace App\Repositories;

use App\Support\Database;

class StudentRepository
{
    public function all(string $role = 'teacher'): array
    {
        $fields = ($role === 'teacher')
            ? "id, user_id, workspace_id, first_name, last_name, father_initial, email, phone, date_of_birth, is_paid, private_notes, created_at"
            : "id, user_id, workspace_id, first_name, last_name, father_initial, email, phone, date_of_birth, is_paid, NULL as private_notes, created_at";

        return Database::query("SELECT $fields FROM student_profiles ORDER BY first_name ASC, last_name ASC");
    }

    public function findById(string $id, string $role = 'teacher'): ?array
    {
        $fields = ($role === 'teacher')
            ? "s.id, s.user_id, s.workspace_id, s.first_name, s.last_name, s.father_initial, s.email, s.phone, s.date_of_birth, s.is_paid, s.private_notes, s.created_at, u.username as username"
            : "s.id, s.user_id, s.workspace_id, s.first_name, s.last_name, s.father_initial, s.email, s.phone, s.date_of_birth, s.is_paid, NULL as private_notes, s.created_at, u.username as username";

        return Database::queryOne("SELECT $fields FROM student_profiles s LEFT JOIN users u ON s.user_id = u.id WHERE s.id = ?", [$id]);
    }

    public function getStudentsInGroup(string $groupId, string $role = 'teacher'): array
    {
        $fields = ($role === 'teacher')
            ? "s.id, s.user_id, s.workspace_id, s.first_name, s.last_name, s.father_initial, s.email, s.phone, s.date_of_birth, s.is_paid, s.private_notes, s.created_at"
            : "s.id, s.user_id, s.workspace_id, s.first_name, s.last_name, s.father_initial, s.email, s.phone, s.date_of_birth, s.is_paid, NULL as private_notes, s.created_at";

        return Database::query("
            SELECT $fields
            FROM student_profiles s
            INNER JOIN group_enrollments ge ON s.id = ge.student_id
            WHERE ge.group_id = ? AND ge.status = 'active'
            ORDER BY s.first_name ASC, s.last_name ASC
        ", [$groupId]);
    }

    public function getStudentsForGuardian(string $guardianId, string $role = 'parent'): array
    {
        $fields = ($role === 'teacher')
            ? "s.id, s.user_id, s.workspace_id, s.first_name, s.last_name, s.father_initial, s.email, s.phone, s.date_of_birth, s.is_paid, s.private_notes, s.created_at"
            : "s.id, s.user_id, s.workspace_id, s.first_name, s.last_name, s.father_initial, s.email, s.phone, s.date_of_birth, s.is_paid, NULL as private_notes, s.created_at";

        return Database::query("
            SELECT $fields
            FROM student_profiles s
            INNER JOIN guardian_student_links gsl ON s.id = gsl.student_id
            WHERE gsl.guardian_id = ? AND gsl.status = 'active'
            ORDER BY s.first_name ASC
        ", [$guardianId]);
    }

    public function getGuardiansForStudent(string $studentId): array
    {
        return Database::query("
            SELECT g.*
            FROM guardian_profiles g
            INNER JOIN guardian_student_links gsl ON g.id = gsl.guardian_id
            WHERE gsl.student_id = ? AND gsl.status = 'active'
        ", [$studentId]);
    }

    public function togglePaid(string $studentId, ?int $forceStatus = null): bool
    {
        $now = date('Y-m-d H:i:s');
        if ($forceStatus !== null) {
            return Database::execute(
                "UPDATE student_profiles SET is_paid = ?, updated_at = ? WHERE id = ?",
                [$forceStatus ? 1 : 0, $now, $studentId]
            );
        }

        $current = Database::queryOne("SELECT is_paid FROM student_profiles WHERE id = ?", [$studentId]);
        $newStatus = ($current && (int)$current['is_paid'] === 1) ? 0 : 1;

        return Database::execute(
            "UPDATE student_profiles SET is_paid = ?, updated_at = ? WHERE id = ?",
            [$newStatus, $now, $studentId]
        );
    }

    public function updatePrivateNotes(string $studentId, string $notes): bool
    {
        $now = date('Y-m-d H:i:s');
        return Database::execute(
            "UPDATE student_profiles SET private_notes = ?, updated_at = ? WHERE id = ?",
            [$notes, $now, $studentId]
        );
    }

    public function update(string $studentId, array $data): bool
    {
        $now = date('Y-m-d H:i:s');
        $result = Database::execute(
            "UPDATE student_profiles SET first_name = ?, last_name = ?, father_initial = ?, email = ?, phone = ?, updated_at = ? WHERE id = ?",
            [
                $data['first_name'],
                $data['last_name'],
                $data['father_initial'] ?? null,
                $data['email'] ?? null,
                $data['phone'] ?? null,
                $now,
                $studentId
            ]
        );

        $student = Database::queryOne("SELECT user_id, email, first_name, last_name, phone FROM student_profiles WHERE id = ?", [$studentId]);
        $username = isset($data['username']) ? trim($data['username']) : null;
        $password = isset($data['password']) ? trim($data['password']) : null;

        if ($student && !empty($student['user_id'])) {
            $userUpdates = [
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'email' => $data['email'] ?? $student['email'],
                'phone' => $data['phone'] ?? $student['phone'],
                'updated_at' => $now,
            ];
            $sql = "UPDATE users SET first_name = :first_name, last_name = :last_name, email = :email, phone = :phone, updated_at = :updated_at";
            if ($username !== null && $username !== '') {
                $sql .= ", username = :username";
                $userUpdates['username'] = $username;
            }
            if ($password !== null && $password !== '') {
                $sql .= ", password_hash = :password_hash";
                $userUpdates['password_hash'] = password_hash($password, PASSWORD_DEFAULT);
            }
            $sql .= " WHERE id = :id";
            $userUpdates['id'] = $student['user_id'];
            Database::execute($sql, $userUpdates);
        } elseif (($username !== null && $username !== '') || ($password !== null && $password !== '')) {
            $newUserId = 'usr_' . bin2hex(random_bytes(6));
            $passHash = password_hash($password ?: 'elev123', PASSWORD_DEFAULT);
            $userEmail = !empty($data['email']) ? $data['email'] : ($username ? $username . '@elev.ro' : $studentId . '@elev.ro');
            Database::execute(
                "INSERT INTO users (id, email, username, password_hash, role, first_name, last_name, phone, is_active, created_at, updated_at) VALUES (?, ?, ?, ?, 'student', ?, ?, ?, 1, ?, ?)",
                [$newUserId, $userEmail, $username ?: null, $passHash, $data['first_name'], $data['last_name'], $data['phone'] ?? null, $now, $now]
            );
            Database::execute("UPDATE student_profiles SET user_id = ? WHERE id = ?", [$newUserId, $studentId]);
        }

        return $result;
    }

    public function create(array $data): string
    {
        $id = 'stu_' . bin2hex(random_bytes(6));
        $now = date('Y-m-d H:i:s');
        Database::execute(
            "INSERT INTO student_profiles (id, user_id, workspace_id, first_name, last_name, father_initial, email, phone, is_paid, private_notes, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
            [
                $id,
                $data['user_id'] ?? null,
                $data['workspace_id'] ?? 'ws_radu_teodorescu',
                $data['first_name'],
                $data['last_name'],
                $data['father_initial'] ?? null,
                $data['email'] ?? null,
                $data['phone'] ?? null,
                isset($data['is_paid']) ? (int)$data['is_paid'] : 1,
                $data['private_notes'] ?? null,
                $now,
                $now
            ]
        );
        return $id;
    }
}
