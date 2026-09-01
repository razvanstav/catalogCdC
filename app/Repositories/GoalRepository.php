<?php

namespace App\Repositories;

use App\Support\Database;

class GoalRepository
{
    public function getForStudent(string $studentId): array
    {
        return Database::query("
            SELECT * FROM learning_goals 
            WHERE student_id = ? 
            ORDER BY is_completed ASC, created_at DESC
        ", [$studentId]);
    }

    public function toggle(string $goalId): bool
    {
        $goal = Database::queryOne("SELECT * FROM learning_goals WHERE id = ?", [$goalId]);
        if (!$goal) return false;

        $newCompleted = $goal['is_completed'] ? 0 : 1;
        $completedAt = $newCompleted ? date('Y-m-d H:i:s') : null;

        return Database::execute(
            "UPDATE learning_goals SET is_completed = ?, completed_at = ? WHERE id = ?",
            [$newCompleted, $completedAt, $goalId]
        );
    }

    public function create(string $studentId, string $title, ?string $targetDate = null): string
    {
        $id = 'gol_' . bin2hex(random_bytes(6));
        $now = date('Y-m-d H:i:s');
        Database::execute(
            "INSERT INTO learning_goals (id, student_id, title, target_date, is_completed, created_at) VALUES (?, ?, ?, ?, 0, ?)",
            [$id, $studentId, $title, $targetDate, $now]
        );
        return $id;
    }
}
