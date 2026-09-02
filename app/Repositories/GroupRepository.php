<?php

namespace App\Repositories;

use App\Support\Database;

class GroupRepository
{
    public function all(?string $workspaceId = null): array
    {
        $wsId = $workspaceId ?? (function_exists('workspace_id') ? workspace_id() : null);
        if ($wsId) {
            return Database::query("SELECT * FROM groups WHERE workspace_id = ? ORDER BY name ASC", [$wsId]);
        }
        return Database::query("SELECT * FROM groups ORDER BY name ASC");
    }

    public function findById(string $id): ?array
    {
        return Database::queryOne("SELECT * FROM groups WHERE id = ?", [$id]);
    }

    public function getGroupsForStudent(string $studentId): array
    {
        return Database::query("
            SELECT g.* 
            FROM groups g
            INNER JOIN group_enrollments ge ON g.id = ge.group_id
            WHERE ge.student_id = ? AND ge.status = 'active'
            ORDER BY g.name ASC
        ", [$studentId]);
    }

    public function create(array $data): string
    {
        $id = 'grp_' . bin2hex(random_bytes(6));
        $now = date('Y-m-d H:i:s');
        $wsId = $data['workspace_id'] ?? (function_exists('workspace_id') ? workspace_id() : null) ?? 'ws_radu_teodorescu';
        Database::execute(
            "INSERT INTO groups (id, workspace_id, name, type, description, color_tag, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
            [
                $id,
                $wsId,
                $data['name'],
                $data['type'] ?? 'tutoring_group',
                $data['description'] ?? null,
                $data['color_tag'] ?? '#4A77DA',
                $now,
                $now
            ]
        );
        return $id;
    }

    public function getSchedules(?string $groupId = null): array
    {
        if ($groupId) {
            return Database::query("SELECT * FROM group_schedules WHERE group_id = ? ORDER BY day_of_week, start_time", [$groupId]);
        }
        $wsId = function_exists('workspace_id') ? workspace_id() : null;
        if ($wsId) {
            return Database::query("
                SELECT s.*, g.name as group_name, g.color_tag as group_color
                FROM group_schedules s
                INNER JOIN groups g ON s.group_id = g.id
                WHERE g.workspace_id = ?
                ORDER BY s.day_of_week, s.start_time
            ", [$wsId]);
        }
        return Database::query("
            SELECT s.*, g.name as group_name, g.color_tag as group_color
            FROM group_schedules s
            INNER JOIN groups g ON s.group_id = g.id
            ORDER BY s.day_of_week, s.start_time
        ");
    }

    public function getCurrentWeekSchedule(): array
    {
        $today = new \DateTimeImmutable('today');
        $currentDow = (int)$today->format('N'); // 1 = Mon, 7 = Sun
        
        // Săptămâna începe Duminică (7) și se termină Sâmbătă (6)
        $daysBack = ($currentDow === 7) ? 0 : $currentDow;
        $sunday = $today->modify("-{$daysBack} days");

        $allSchedules = $this->getSchedules();
        $schedulesByDow = [];
        foreach ($allSchedules as $s) {
            $dow = (int)$s['day_of_week'];
            $schedulesByDow[$dow][] = $s;
        }

        $dowOrder = [7, 1, 2, 3, 4, 5, 6];
        $dayNames = [
            7 => 'Duminică',
            1 => 'Luni',
            2 => 'Marți',
            3 => 'Miercuri',
            4 => 'Joi',
            5 => 'Vineri',
            6 => 'Sâmbătă',
        ];

        $weekDays = [];
        for ($i = 0; $i < 7; $i++) {
            $dateObj = $sunday->modify("+{$i} days");
            $dow = $dowOrder[$i];
            $dateStr = $dateObj->format('Y-m-d');
            $isToday = ($dateStr === $today->format('Y-m-d'));
            $isPast = ($dateStr < $today->format('Y-m-d'));

            $lessonsForDay = Database::query("
                SELECT l.*, g.name as group_name, g.color_tag as group_color
                FROM lessons l
                INNER JOIN groups g ON l.group_id = g.id
                WHERE l.lesson_date = ?
                ORDER BY l.start_time ASC
            ", [$dateStr]);

            $daySchedules = $schedulesByDow[$dow] ?? [];

            $weekDays[] = [
                'day_of_week' => $dow,
                'day_name' => $dayNames[$dow],
                'date' => $dateStr,
                'formatted_date' => $dateObj->format('d.m.Y'),
                'is_today' => $isToday,
                'is_past' => $isPast,
                'schedules' => $daySchedules,
                'lessons' => $lessonsForDay,
            ];
        }

        return $weekDays;
    }

    public function getWeekCalendar(string $dateInWeek = 'today'): array
    {
        try {
            $baseDate = new \DateTimeImmutable($dateInWeek);
        } catch (\Throwable $e) {
            $baseDate = new \DateTimeImmutable('today');
        }

        $today = new \DateTimeImmutable('today');
        $currentDow = (int)$baseDate->format('N'); // 1 = Mon, 7 = Sun
        $daysBack = ($currentDow === 7) ? 0 : $currentDow;
        $sunday = $baseDate->modify("-{$daysBack} days");
        $saturday = $sunday->modify('+6 days');

        $allSchedules = $this->getSchedules();
        $schedulesByDow = [];
        foreach ($allSchedules as $s) {
            $dow = (int)$s['day_of_week'];
            $schedulesByDow[$dow][] = $s;
        }

        $dowOrder = [7, 1, 2, 3, 4, 5, 6];
        $dayNames = [
            7 => 'Duminică',
            1 => 'Luni',
            2 => 'Marți',
            3 => 'Miercuri',
            4 => 'Joi',
            5 => 'Vineri',
            6 => 'Sâmbătă',
        ];

        $now = date('Y-m-d H:i:s');
        $days = [];

        for ($i = 0; $i < 7; $i++) {
            $dateObj = $sunday->modify("+{$i} days");
            $dow = $dowOrder[$i];
            $dateStr = $dateObj->format('Y-m-d');
            $isToday = ($dateStr === $today->format('Y-m-d'));
            $isPast = ($dateStr < $today->format('Y-m-d'));

            $daySchedules = $schedulesByDow[$dow] ?? [];

            // Instanțiere automată pentru grupele programate în ziua respectivă
            foreach ($daySchedules as $sch) {
                $gId = $sch['group_id'];
                $startTime = $sch['start_time'];
                $endTime = $sch['end_time'];
                $exists = Database::queryOne(
                    "SELECT id FROM lessons WHERE group_id = ? AND lesson_date = ? AND start_time = ?",
                    [$gId, $dateStr, $startTime]
                );
                if (!$exists) {
                    $lesId = 'les_' . bin2hex(random_bytes(6));
                    $dayName = $dayNames[$dow];
                    $title = "Ședință {$dayName} — {$sch['group_name']}";
                    Database::execute(
                        "INSERT INTO lessons (id, group_id, title, lesson_date, start_time, end_time, lesson_notes, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
                        [$lesId, $gId, $title, $dateStr, $startTime, $endTime, $sch['room_or_link'] ?? 'Cabinet didactic', $now]
                    );
                }
            }

            // Preluăm instanțele de ședințe create pentru ziua curentă
            $lessonsForDay = Database::query("
                SELECT l.*, g.name as group_name, g.color_tag as group_color,
                       (SELECT COUNT(*) FROM group_enrollments ge WHERE ge.group_id = l.group_id AND ge.status = 'active') as student_count,
                       (SELECT COUNT(*) FROM attendance_records ar WHERE ar.lesson_id = l.id) as attendance_count,
                       (SELECT COUNT(*) FROM assessments asm WHERE asm.lesson_id = l.id) as assessment_count
                FROM lessons l
                INNER JOIN groups g ON l.group_id = g.id
                WHERE l.lesson_date = ?
                ORDER BY l.start_time ASC
            ", [$dateStr]);

            $days[] = [
                'day_of_week' => $dow,
                'day_name' => $dayNames[$dow],
                'date' => $dateStr,
                'formatted_date' => $dateObj->format('d.m.Y'),
                'short_date' => $dateObj->format('d M'),
                'is_today' => $isToday,
                'is_past' => $isPast,
                'lessons' => $lessonsForDay,
            ];
        }

        return [
            'sunday_date' => $sunday->format('Y-m-d'),
            'saturday_date' => $saturday->format('Y-m-d'),
            'formatted_range' => $sunday->format('d.m.Y') . ' – ' . $saturday->format('d.m.Y'),
            'prev_week_date' => $sunday->modify('-7 days')->format('Y-m-d'),
            'next_week_date' => $sunday->modify('+7 days')->format('Y-m-d'),
            'today_date' => $today->format('Y-m-d'),
            'is_current_week' => ($today >= $sunday && $today <= $saturday),
            'days' => $days,
        ];
    }

    public function enrollStudent(string $groupId, string $studentId): bool
    {
        $id = 'enr_' . bin2hex(random_bytes(6));
        $now = date('Y-m-d H:i:s');
        return Database::execute(
            "INSERT OR REPLACE INTO group_enrollments (id, group_id, student_id, enrolled_at, status) VALUES (?, ?, ?, ?, 'active')",
            [$id, $groupId, $studentId, $now]
        );
    }

    public function unenrollStudent(string $groupId, string $studentId): bool
    {
        return Database::execute(
            "DELETE FROM group_enrollments WHERE group_id = ? AND student_id = ?",
            [$groupId, $studentId]
        );
    }

    public function update(string $groupId, array $data): bool
    {
        $now = date('Y-m-d H:i:s');
        return Database::execute(
            "UPDATE groups SET name = ?, type = ?, description = ?, color_tag = ?, updated_at = ? WHERE id = ?",
            [
                $data['name'],
                $data['type'] ?? 'tutoring_group',
                $data['description'] ?? null,
                $data['color_tag'] ?? '#4A77DA',
                $now,
                $groupId
            ]
        );
    }

    public function addSchedule(string $groupId, int $dayOfWeek, string $startTime, string $endTime, ?string $roomOrLink = 'Laborator didactic'): string
    {
        $id = 'sch_' . bin2hex(random_bytes(6));
        Database::execute(
            "INSERT INTO group_schedules (id, group_id, day_of_week, start_time, end_time, room_or_link) VALUES (?, ?, ?, ?, ?, ?)",
            [$id, $groupId, $dayOfWeek, $startTime, $endTime, $roomOrLink]
        );
        return $id;
    }

    public function addScheduleWithDuration(string $groupId, int $dayOfWeek, string $startTime, int $durationMinutes, ?string $roomOrLink = 'Laborator didactic'): string
    {
        $startTs = strtotime("2020-01-01 $startTime");
        $endTs = $startTs + ($durationMinutes * 60);
        $endTime = date('H:i', $endTs);

        $schId = $this->addSchedule($groupId, $dayOfWeek, $startTime, $endTime, $roomOrLink);
        $this->generateRecurringLessons($groupId, 8);
        return $schId;
    }

    public function deleteSchedule(string $scheduleId): bool
    {
        return Database::execute("DELETE FROM group_schedules WHERE id = ?", [$scheduleId]);
    }

    public function generateRecurringLessons(string $groupId, int $weeks = 4): int
    {
        $group = $this->findById($groupId);
        if (!$group) return 0;

        $schedules = $this->getSchedules($groupId);
        if (empty($schedules)) return 0;

        $created = 0;
        $now = date('Y-m-d H:i:s');
        $today = new \DateTimeImmutable('today');

        foreach ($schedules as $schedule) {
            $targetDow = (int)$schedule['day_of_week']; // 1 = Luni, 7 = Duminică

            for ($w = 0; $w < $weeks; $w++) {
                $baseDate = $today->modify("+{$w} week");
                $currentDow = (int)$baseDate->format('N');
                $diff = $targetDow - $currentDow;
                if ($diff < 0) {
                    $diff += 7;
                }
                $lessonDateObj = $baseDate->modify("+{$diff} days");
                $lessonDate = $lessonDateObj->format('Y-m-d');

                $exists = Database::queryOne(
                    "SELECT id FROM lessons WHERE group_id = ? AND lesson_date = ? AND start_time = ?",
                    [$groupId, $lessonDate, $schedule['start_time']]
                );

                if (!$exists) {
                    $lessonId = 'les_' . bin2hex(random_bytes(6));
                    $dayName = day_name_ro($targetDow);
                    $title = "Ședință {$dayName} — {$group['name']}";
                    Database::execute(
                        "INSERT INTO lessons (id, group_id, title, lesson_date, start_time, end_time, lesson_notes, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
                        [$lessonId, $groupId, $title, $lessonDate, $schedule['start_time'], $schedule['end_time'], 'Ședință recurentă conform orarului săptămânal.', $now]
                    );
                    $created++;
                }
            }
        }

        return $created;
    }

    public function delete(string $groupId): bool
    {
        return Database::execute("DELETE FROM groups WHERE id = ?", [$groupId]);
    }
}
