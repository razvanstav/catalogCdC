<?php

namespace App\Repositories;

use App\Support\Database;

class AssessmentRepository
{
    public function getForGroup(string $groupId): array
    {
        return Database::query("
            SELECT a.*, g.name as group_name, l.title as lesson_title, l.start_time as lesson_start, l.end_time as lesson_end
            FROM assessments a
            INNER JOIN groups g ON a.group_id = g.id
            LEFT JOIN lessons l ON a.lesson_id = l.id
            WHERE a.group_id = ?
            ORDER BY a.assessment_date DESC, a.created_at DESC
        ", [$groupId]);
    }

    public function all(): array
    {
        return Database::query("
            SELECT a.*, g.name as group_name, l.title as lesson_title, l.start_time as lesson_start, l.end_time as lesson_end
            FROM assessments a
            INNER JOIN groups g ON a.group_id = g.id
            LEFT JOIN lessons l ON a.lesson_id = l.id
            ORDER BY a.assessment_date DESC, a.created_at DESC
        ");
    }

    public function findById(string $id): ?array
    {
        return Database::queryOne("
            SELECT a.*, g.name as group_name, l.title as lesson_title, l.start_time as lesson_start, l.end_time as lesson_end
            FROM assessments a
            INNER JOIN groups g ON a.group_id = g.id
            LEFT JOIN lessons l ON a.lesson_id = l.id
            WHERE a.id = ?
        ", [$id]);
    }

    public function getForLesson(string $lessonId): array
    {
        return Database::query("
            SELECT a.*, g.name as group_name
            FROM assessments a
            INNER JOIN groups g ON a.group_id = g.id
            WHERE a.lesson_id = ?
            ORDER BY a.created_at DESC
        ", [$lessonId]);
    }

    public function create(array $data): string
    {
        $id = 'asm_' . bin2hex(random_bytes(6));
        $now = date('Y-m-d H:i:s');
        Database::execute(
            "INSERT INTO assessments (id, group_id, lesson_id, title, assessment_type, max_score, assessment_date, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
            [
                $id,
                $data['group_id'],
                $data['lesson_id'] ?? null,
                $data['title'],
                $data['assessment_type'] ?? 'test',
                $data['max_score'] ?? 5.0,
                $data['assessment_date'],
                $now
            ]
        );
        return $id;
    }

    public function getResultsForAssessment(string $assessmentId, string $role = 'teacher'): array
    {
        $notesField = ($role === 'teacher') ? "r.private_teacher_notes" : "NULL as private_teacher_notes";
        $wherePublished = ($role === 'teacher') ? "" : "AND r.is_published = 1";

        return Database::query("
            SELECT r.id, r.assessment_id, r.student_id, r.score, $notesField, r.published_feedback, r.is_published,
                   s.first_name, s.last_name, s.father_initial
            FROM assessment_results r
            INNER JOIN student_profiles s ON r.student_id = s.id
            WHERE r.assessment_id = ? $wherePublished
            ORDER BY s.first_name ASC, s.last_name ASC
        ", [$assessmentId]);
    }

    public function getResultsForStudent(string $studentId, string $role = 'teacher'): array
    {
        $notesField = ($role === 'teacher') ? "r.private_teacher_notes" : "NULL as private_teacher_notes";
        $wherePublished = ($role === 'teacher') ? "" : "AND r.is_published = 1";

        return Database::query("
            SELECT r.id, r.assessment_id, r.student_id, r.score, $notesField, r.published_feedback, r.is_published,
                   a.title as assessment_title, a.assessment_type, a.max_score, a.assessment_date,
                   g.name as group_name, g.color_tag as group_color
            FROM assessment_results r
            INNER JOIN assessments a ON r.assessment_id = a.id
            INNER JOIN groups g ON a.group_id = g.id
            WHERE r.student_id = ? $wherePublished
            ORDER BY a.assessment_date DESC
        ", [$studentId]);
    }

    public function saveResult(string $assessmentId, string $studentId, float $score, ?string $feedback, ?string $privateNotes, bool $isPublished = true): bool
    {
        $existing = Database::queryOne("SELECT id FROM assessment_results WHERE assessment_id = ? AND student_id = ?", [$assessmentId, $studentId]);
        $now = date('Y-m-d H:i:s');
        if ($existing) {
            return Database::execute(
                "UPDATE assessment_results SET score = ?, private_teacher_notes = ?, published_feedback = ?, is_published = ?, updated_at = ? WHERE id = ?",
                [$score, $privateNotes, $feedback, $isPublished ? 1 : 0, $now, $existing['id']]
            );
        } else {
            $id = 'res_' . bin2hex(random_bytes(6));
            return Database::execute(
                "INSERT INTO assessment_results (id, assessment_id, student_id, score, private_teacher_notes, published_feedback, is_published, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)",
                [
                    $id,
                    $assessmentId,
                    $studentId,
                    $score,
                    $privateNotes,
                    $feedback,
                    $isPublished ? 1 : 0,
                    $now,
                    $now
                ]
            );
        }
    }
}
