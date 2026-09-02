<?php

namespace App\Repositories;

use App\Support\Database;

class ConversationRepository
{
    public function getForTeacher(string $teacherId): array
    {
        $convs = Database::query("
            SELECT c.*, g.first_name as guardian_first_name, g.last_name as guardian_last_name, g.phone as guardian_phone,
                   s.first_name as student_first_name, s.last_name as student_last_name
            FROM conversations c
            INNER JOIN guardian_profiles g ON c.guardian_id = g.id
            LEFT JOIN student_profiles s ON c.student_id = s.id
            WHERE c.teacher_id = ?
            ORDER BY c.updated_at DESC
        ", [$teacherId]);

        foreach ($convs as &$c) {
            $c['messages'] = $this->getMessages($c['id']);
        }
        return $convs;
    }

    public function getForGuardian(string $guardianId): array
    {
        $convs = Database::query("
            SELECT c.*, t.title as teacher_title, u.first_name as teacher_first_name, u.last_name as teacher_last_name, u.phone as teacher_phone,
                   s.first_name as student_first_name, s.last_name as student_last_name
            FROM conversations c
            INNER JOIN teacher_profiles t ON c.teacher_id = t.id
            INNER JOIN users u ON t.user_id = u.id
            LEFT JOIN student_profiles s ON c.student_id = s.id
            WHERE c.guardian_id = ?
            ORDER BY c.updated_at DESC
        ", [$guardianId]);

        foreach ($convs as &$c) {
            $c['messages'] = $this->getMessages($c['id']);
        }
        return $convs;
    }

    public function getForStudent(string $studentId): array
    {
        $convs = Database::query("
            SELECT c.*, t.title as teacher_title, u.first_name as teacher_first_name, u.last_name as teacher_last_name, u.phone as teacher_phone,
                   s.first_name as student_first_name, s.last_name as student_last_name
            FROM conversations c
            INNER JOIN teacher_profiles t ON c.teacher_id = t.id
            INNER JOIN users u ON t.user_id = u.id
            LEFT JOIN student_profiles s ON c.student_id = s.id
            WHERE c.student_id = ?
            ORDER BY c.updated_at DESC
        ", [$studentId]);

        foreach ($convs as &$c) {
            $c['messages'] = $this->getMessages($c['id']);
        }
        return $convs;
    }

    public function getOrCreateForStudent(string $studentId): array
    {
        $convs = $this->getForStudent($studentId);
        if (!empty($convs)) {
            return $convs;
        }

        $teacher = Database::queryOne("SELECT id FROM teacher_profiles LIMIT 1");
        if (!$teacher) {
            return [];
        }

        $link = Database::queryOne("SELECT guardian_id FROM guardian_student_links WHERE student_id = ? AND status = 'active' LIMIT 1", [$studentId]);
        $guardianId = $link['guardian_id'] ?? 'grd_direct_' . $studentId;

        $convId = 'cnv_' . bin2hex(random_bytes(6));
        $now = date('Y-m-d H:i:s');
        Database::execute(
            "INSERT INTO conversations (id, teacher_id, guardian_id, student_id, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?)",
            [$convId, $teacher['id'], $guardianId, $studentId, $now, $now]
        );

        return $this->getForStudent($studentId);
    }

    public function getMessages(string $conversationId): array
    {
        return Database::query("
            SELECT * FROM messages 
            WHERE conversation_id = ? 
            ORDER BY sent_at ASC
        ", [$conversationId]);
    }

    public function sendMessage(string $conversationId, string $senderRole, string $senderId, string $content): string
    {
        $id = 'msg_' . bin2hex(random_bytes(6));
        $now = date('Y-m-d H:i:s');
        Database::execute(
            "INSERT INTO messages (id, conversation_id, sender_role, sender_id, content, is_read, sent_at) VALUES (?, ?, ?, ?, ?, 1, ?)",
            [$id, $conversationId, $senderRole, $senderId, $content, $now]
        );
        Database::execute("UPDATE conversations SET updated_at = ? WHERE id = ?", [$now, $conversationId]);
        return $id;
    }

    public function findOrCreateConversation(string $teacherId, string $guardianId, ?string $studentId = null): string
    {
        $existing = Database::queryOne("
            SELECT id FROM conversations
            WHERE teacher_id = ? AND guardian_id = ?
        ", [$teacherId, $guardianId]);

        if ($existing) {
            return $existing['id'];
        }

        $id = 'cnv_' . bin2hex(random_bytes(6));
        $now = date('Y-m-d H:i:s');
        Database::execute("
            INSERT INTO conversations (id, teacher_id, guardian_id, student_id, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?)
        ", [$id, $teacherId, $guardianId, $studentId, $now, $now]);

        return $id;
    }

    public function getAllGuardiansWithStudents(): array
    {
        return Database::query("
            SELECT g.id as guardian_id, g.first_name as guardian_first_name, g.last_name as guardian_last_name, g.phone as guardian_phone, g.email as guardian_email,
                   s.id as student_id, s.first_name as student_first_name, s.last_name as student_last_name
            FROM guardian_profiles g
            LEFT JOIN guardian_student_links gsl ON g.id = gsl.guardian_id
            LEFT JOIN student_profiles s ON gsl.student_id = s.id
            ORDER BY g.first_name ASC, g.last_name ASC
        ");
    }
}
