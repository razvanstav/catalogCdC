<?php

namespace App\Repositories;

use App\Support\Database;

class FeedbackRepository
{
    public function getForStudent(string $studentId): array
    {
        return Database::query("
            SELECT * FROM published_feedbacks 
            WHERE student_id = ? 
            ORDER BY created_at DESC
        ", [$studentId]);
    }

    public function all(): array
    {
        return Database::query("
            SELECT f.*, s.first_name, s.last_name, s.father_initial
            FROM published_feedbacks f
            INNER JOIN student_profiles s ON f.student_id = s.id
            ORDER BY f.created_at DESC
        ");
    }

    public function create(string $studentId, string $content, string $category = 'progress'): string
    {
        $id = 'pf_' . bin2hex(random_bytes(6));
        $now = date('Y-m-d H:i:s');
        Database::execute(
            "INSERT INTO published_feedbacks (id, student_id, content, category, created_at) VALUES (?, ?, ?, ?, ?)",
            [$id, $studentId, $content, $category, $now]
        );
        return $id;
    }
}
