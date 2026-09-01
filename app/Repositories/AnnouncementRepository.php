<?php

namespace App\Repositories;

use App\Support\Database;

class AnnouncementRepository
{
    public function all(): array
    {
        return Database::query("
            SELECT a.*, g.name as group_name, g.color_tag as group_color
            FROM announcements a
            LEFT JOIN groups g ON a.group_id = g.id
            ORDER BY a.created_at DESC
        ");
    }

    public function getForStudent(string $studentId): array
    {
        return Database::query("
            SELECT a.*, g.name as group_name, g.color_tag as group_color
            FROM announcements a
            LEFT JOIN groups g ON a.group_id = g.id
            WHERE a.group_id IS NULL
               OR a.group_id IN (
                   SELECT group_id FROM group_enrollments WHERE student_id = ? AND status = 'active'
               )
            ORDER BY a.created_at DESC
        ", [$studentId]);
    }

    public function create(string $title, string $content, ?string $groupId = null): string
    {
        $id = 'ann_' . bin2hex(random_bytes(6));
        $now = date('Y-m-d H:i:s');
        Database::execute(
            "INSERT INTO announcements (id, group_id, title, content, created_at) VALUES (?, ?, ?, ?, ?)",
            [$id, $groupId, $title, $content, $now]
        );
        return $id;
    }
}
