<?php

namespace App\Repositories;

use App\Support\Database;

class AssignmentRepository
{
    public function getForGroup(string $groupId): array
    {
        return Database::query("
            SELECT a.*, g.name as group_name, g.color_tag as group_color
            FROM assignments a
            INNER JOIN groups g ON a.group_id = g.id
            WHERE a.group_id = ?
            ORDER BY a.due_date ASC
        ", [$groupId]);
    }

    public function getForStudent(string $studentId): array
    {
        return Database::query("
            SELECT a.*, g.name as group_name, g.color_tag as group_color
            FROM assignments a
            INNER JOIN groups g ON a.group_id = g.id
            INNER JOIN group_enrollments ge ON g.id = ge.group_id
            WHERE ge.student_id = ? AND ge.status = 'active'
            ORDER BY a.due_date ASC
        ", [$studentId]);
    }

    public function all(): array
    {
        return Database::query("
            SELECT a.*, g.name as group_name, g.color_tag as group_color,
                   (SELECT COUNT(*) FROM assignment_submissions WHERE assignment_id = a.id) as submissions_count,
                   (SELECT COUNT(*) FROM group_enrollments WHERE group_id = a.group_id AND status = 'active') as total_students
            FROM assignments a
            INNER JOIN groups g ON a.group_id = g.id
            ORDER BY a.due_date ASC
        ");
    }

    public function create(array $data): string
    {
        $id = 'asg_' . bin2hex(random_bytes(6));
        $now = date('Y-m-d H:i:s');
        Database::execute(
            "INSERT INTO assignments (id, group_id, title, description, attachment_url, assigned_date, due_date, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
            [
                $id,
                $data['group_id'],
                $data['title'],
                $data['description'],
                $data['attachment_url'] ?? null,
                $data['assigned_date'] ?? $now,
                $data['due_date'],
                $now
            ]
        );
        return $id;
    }

    public function submitAssignment(string $assignmentId, string $studentId, ?string $text = null, ?string $fileUrl = null, ?string $fileName = null, ?string $fileType = null): string
    {
        $existing = Database::queryOne(
            "SELECT id FROM assignment_submissions WHERE assignment_id = ? AND student_id = ?",
            [$assignmentId, $studentId]
        );
        $now = date('Y-m-d H:i:s');

        if ($existing) {
            Database::execute(
                "UPDATE assignment_submissions SET submission_text = ?, file_url = COALESCE(?, file_url), file_name = COALESCE(?, file_name), file_type = COALESCE(?, file_type), updated_at = ? WHERE id = ?",
                [$text, $fileUrl, $fileName, $fileType, $now, $existing['id']]
            );
            return $existing['id'];
        } else {
            $id = 'sub_' . bin2hex(random_bytes(6));
            Database::execute(
                "INSERT INTO assignment_submissions (id, assignment_id, student_id, submission_text, file_url, file_name, file_type, status, submitted_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, 'submitted', ?, ?)",
                [$id, $assignmentId, $studentId, $text, $fileUrl, $fileName, $fileType, $now, $now]
            );
            return $id;
        }
    }

    public function getSubmission(string $assignmentId, string $studentId): ?array
    {
        return Database::queryOne(
            "SELECT * FROM assignment_submissions WHERE assignment_id = ? AND student_id = ?",
            [$assignmentId, $studentId]
        );
    }

    public function getSubmissionsForAssignment(string $assignmentId): array
    {
        return Database::query("
            SELECT sub.*, s.first_name, s.last_name, s.father_initial, s.email
            FROM assignment_submissions sub
            INNER JOIN student_profiles s ON sub.student_id = s.id
            WHERE sub.assignment_id = ?
            ORDER BY sub.submitted_at DESC
        ", [$assignmentId]);
    }

    public function getForStudentWithSubmissions(string $studentId): array
    {
        return Database::query("
            SELECT a.*, g.name as group_name, g.color_tag as group_color,
                   sub.id as submission_id, sub.submission_text, sub.file_url, sub.file_name, sub.file_type, sub.submitted_at, sub.status as submission_status
            FROM assignments a
            INNER JOIN groups g ON a.group_id = g.id
            INNER JOIN group_enrollments ge ON g.id = ge.group_id
            LEFT JOIN assignment_submissions sub ON a.id = sub.assignment_id AND sub.student_id = ?
            WHERE ge.student_id = ? AND ge.status = 'active'
            ORDER BY a.due_date ASC
        ", [$studentId, $studentId]);
    }

    public function getMaterials(?string $groupId = null): array
    {
        if ($groupId) {
            return Database::query("
                SELECT m.*, g.name as group_name
                FROM learning_materials m
                INNER JOIN groups g ON m.group_id = g.id
                WHERE m.group_id = ?
                ORDER BY m.created_at DESC
            ", [$groupId]);
        }
        return Database::query("
            SELECT m.*, g.name as group_name
            FROM learning_materials m
            INNER JOIN groups g ON m.group_id = g.id
            ORDER BY m.created_at DESC
        ");
    }

    public function getMaterialsForStudent(string $studentId): array
    {
        return Database::query("
            SELECT m.*, g.name as group_name
            FROM learning_materials m
            INNER JOIN groups g ON m.group_id = g.id
            INNER JOIN group_enrollments ge ON g.id = ge.group_id
            WHERE ge.student_id = ? AND ge.status = 'active'
            ORDER BY m.created_at DESC
        ", [$studentId]);
    }

    public function createMaterial(array $data): string
    {
        $id = 'mat_' . bin2hex(random_bytes(6));
        $now = date('Y-m-d H:i:s');
        Database::execute(
            "INSERT INTO learning_materials (id, group_id, lesson_id, title, url, file_type, created_at) VALUES (?, ?, ?, ?, ?, ?, ?)",
            [
                $id,
                $data['group_id'],
                $data['lesson_id'] ?? null,
                $data['title'],
                $data['url'] ?? null,
                $data['file_type'] ?? 'pdf',
                $now
            ]
        );
        return $id;
    }
}
