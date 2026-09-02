<?php

namespace App\Repositories;

use App\Support\Database;

class AttendanceRepository
{
    public function getForLesson(string $lessonId): array
    {
        return Database::query("
            SELECT a.*, s.first_name, s.last_name, s.father_initial, a.is_paid as is_paid
            FROM attendance_records a
            INNER JOIN student_profiles s ON a.student_id = s.id
            WHERE a.lesson_id = ?
            ORDER BY s.first_name ASC, s.last_name ASC
        ", [$lessonId]);
    }

    public function getStudentsForLesson(string $lessonId, string $groupId): array
    {
        // 1. Get regularly enrolled students with their lesson-specific attendance if exists
        $enrolled = Database::query("
            SELECT s.id, s.user_id, s.workspace_id, s.first_name, s.last_name, s.father_initial, s.email, s.phone, s.date_of_birth, s.private_notes, 0 as is_guest,
                   COALESCE(a.is_paid, 1) as is_paid, a.status as attendance_status, a.note as attendance_note
            FROM student_profiles s
            INNER JOIN group_enrollments ge ON s.id = ge.student_id
            LEFT JOIN attendance_records a ON a.student_id = s.id AND a.lesson_id = ?
            WHERE ge.group_id = ? AND ge.status = 'active'
            ORDER BY s.first_name ASC, s.last_name ASC
        ", [$lessonId, $groupId]);

        $enrolledMap = [];
        foreach ($enrolled as $st) {
            $enrolledMap[$st['id']] = true;
        }

        // 2. Get any guest students who have an attendance record for this lesson but are not in the group
        $guestRecords = Database::query("
            SELECT s.id, s.user_id, s.workspace_id, s.first_name, s.last_name, s.father_initial, s.email, s.phone, s.date_of_birth, s.private_notes, 1 as is_guest,
                   COALESCE(a.is_paid, 1) as is_paid, a.status as attendance_status, a.note as attendance_note
            FROM attendance_records a
            INNER JOIN student_profiles s ON a.student_id = s.id
            WHERE a.lesson_id = ?
        ", [$lessonId]);

        $results = $enrolled;
        foreach ($guestRecords as $g) {
            if (!isset($enrolledMap[$g['id']])) {
                $results[] = $g;
            }
        }

        return $results;
    }

    public function addGuestToLesson(string $lessonId, string $studentId, string $status = 'present', ?string $note = 'Recuperare / Transfer oră', int $isPaid = 1): bool
    {
        return $this->record($lessonId, $studentId, $status, $note, $isPaid);
    }

    public function removeGuestFromLesson(string $lessonId, string $studentId): bool
    {
        return Database::execute("DELETE FROM attendance_records WHERE lesson_id = ? AND student_id = ?", [$lessonId, $studentId]);
    }

    public function getForStudent(string $studentId): array
    {
        return Database::query("
            SELECT a.*, l.title as lesson_title, l.lesson_date, l.start_time, l.end_time, g.name as group_name
            FROM attendance_records a
            INNER JOIN lessons l ON a.lesson_id = l.id
            INNER JOIN groups g ON l.group_id = g.id
            WHERE a.student_id = ?
            ORDER BY l.lesson_date DESC
        ", [$studentId]);
    }

    public function record(string $lessonId, string $studentId, string $status, ?string $note = null, int $isPaid = 1): bool
    {
        $existing = Database::queryOne("SELECT id FROM attendance_records WHERE lesson_id = ? AND student_id = ?", [$lessonId, $studentId]);
        $now = date('Y-m-d H:i:s');
        if ($existing) {
            return Database::execute(
                "UPDATE attendance_records SET status = ?, note = ?, is_paid = ?, updated_at = ? WHERE id = ?",
                [$status, $note, $isPaid, $now, $existing['id']]
            );
        } else {
            $id = 'att_' . bin2hex(random_bytes(6));
            return Database::execute(
                "INSERT INTO attendance_records (id, lesson_id, student_id, status, note, is_paid, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
                [$id, $lessonId, $studentId, $status, $note, $isPaid, $now, $now]
            );
        }
    }

    public function getLessons(?string $groupId = null): array
    {
        if ($groupId) {
            return Database::query("
                SELECT l.*, g.name as group_name, g.color_tag as group_color
                FROM lessons l
                INNER JOIN groups g ON l.group_id = g.id
                WHERE l.group_id = ?
                ORDER BY l.lesson_date DESC, l.start_time DESC
            ", [$groupId]);
        }
        return Database::query("
            SELECT l.*, g.name as group_name, g.color_tag as group_color
            FROM lessons l
            INNER JOIN groups g ON l.group_id = g.id
            ORDER BY l.lesson_date DESC, l.start_time DESC
        ");
    }

    public function getLessonsForGroupInWeek(string $groupId, string $startDate, string $endDate): array
    {
        return Database::query("
            SELECT l.*, g.name as group_name, g.color_tag as group_color
            FROM lessons l
            INNER JOIN groups g ON l.group_id = g.id
            WHERE l.group_id = ? AND l.lesson_date >= ? AND l.lesson_date <= ?
            ORDER BY l.lesson_date ASC, l.start_time ASC
        ", [$groupId, $startDate, $endDate]);
    }

    public function getRecentChronologicalLessons(int $limit = 6): array
    {
        $today = date('Y-m-d');
        // Retrieve lessons that just took place (past/today) first, then upcoming if needed
        $past = Database::query("
            SELECT l.*, g.name as group_name, g.color_tag as group_color
            FROM lessons l
            INNER JOIN groups g ON l.group_id = g.id
            WHERE l.lesson_date <= ?
            ORDER BY l.lesson_date DESC, l.start_time DESC
            LIMIT ?
        ", [$today, $limit]);

        if (count($past) < $limit) {
            $remaining = $limit - count($past);
            $upcoming = Database::query("
                SELECT l.*, g.name as group_name, g.color_tag as group_color
                FROM lessons l
                INNER JOIN groups g ON l.group_id = g.id
                WHERE l.lesson_date > ?
                ORDER BY l.lesson_date ASC, l.start_time ASC
                LIMIT ?
            ", [$today, $remaining]);
            return array_merge($past, $upcoming);
        }

        return $past;
    }

    public function createLesson(array $data): string
    {
        $id = 'les_' . bin2hex(random_bytes(6));
        $now = date('Y-m-d H:i:s');
        Database::execute(
            "INSERT INTO lessons (id, group_id, title, lesson_date, start_time, end_time, lesson_notes, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
            [
                $id,
                $data['group_id'],
                $data['title'],
                $data['lesson_date'],
                $data['start_time'],
                $data['end_time'],
                $data['lesson_notes'] ?? null,
                $now
            ]
        );
        return $id;
    }
}
