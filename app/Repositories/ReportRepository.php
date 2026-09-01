<?php

namespace App\Repositories;

use App\Support\Database;

class ReportRepository
{
    public function getAvailableYears(): array
    {
        $yearsFromLessons = Database::query("
            SELECT DISTINCT substr(lesson_date, 1, 4) as yr FROM lessons WHERE lesson_date IS NOT NULL AND lesson_date != ''
        ");
        $yearsFromAssessments = Database::query("
            SELECT DISTINCT substr(assessment_date, 1, 4) as yr FROM assessments WHERE assessment_date IS NOT NULL AND assessment_date != ''
        ");
        $yearsFromEnrollments = Database::query("
            SELECT DISTINCT substr(enrolled_at, 1, 4) as yr FROM group_enrollments WHERE enrolled_at IS NOT NULL AND enrolled_at != ''
        ");
        $yearsFromStudents = Database::query("
            SELECT DISTINCT substr(created_at, 1, 4) as yr FROM student_profiles WHERE created_at IS NOT NULL AND created_at != ''
        ");

        $allYears = [];
        foreach (array_merge($yearsFromLessons, $yearsFromAssessments, $yearsFromEnrollments, $yearsFromStudents) as $row) {
            if (!empty($row['yr']) && is_numeric($row['yr'])) {
                $allYears[] = $row['yr'];
            }
        }
        $currentYear = date('Y');
        $allYears[] = $currentYear;
        $allYears[] = (string)((int)$currentYear - 1);
        $allYears[] = (string)((int)$currentYear - 2);
        $uniqueYears = array_unique($allYears);
        rsort($uniqueYears);
        return array_values($uniqueYears);
    }

    public function getAnnualOverview(string $year = 'all'): array
    {
        $yearFilter = ($year !== 'all') ? "AND substr(lesson_date, 1, 4) = '$year'" : "";
        $yearFilterAsm = ($year !== 'all') ? "AND substr(assessment_date, 1, 4) = '$year'" : "";

        if ($year !== 'all') {
            $studentCount = (int)(Database::queryOne("
                SELECT COUNT(DISTINCT student_id) as total
                FROM (
                    SELECT student_id FROM group_enrollments WHERE substr(enrolled_at, 1, 4) <= ? AND status = 'active'
                    UNION
                    SELECT student_id FROM attendance_records ar JOIN lessons l ON ar.lesson_id = l.id WHERE substr(l.lesson_date, 1, 4) = ?
                    UNION
                    SELECT student_id FROM assessment_results ar JOIN assessments a ON ar.assessment_id = a.id WHERE substr(a.assessment_date, 1, 4) = ?
                )
            ", [$year, $year, $year])['total'] ?? 0);
            if ($studentCount === 0) {
                $studentCount = (int)(Database::queryOne("SELECT COUNT(*) as total FROM student_profiles")['total'] ?? 0);
            }
        } else {
            $studentCount = (int)(Database::queryOne("SELECT COUNT(*) as total FROM student_profiles")['total'] ?? 0);
        }

        if ($year !== 'all') {
            $groupCount = (int)(Database::queryOne("
                SELECT COUNT(DISTINCT g.id) as total
                FROM groups g
                LEFT JOIN lessons l ON g.id = l.group_id AND substr(l.lesson_date, 1, 4) = ?
                LEFT JOIN group_enrollments ge ON g.id = ge.group_id AND substr(ge.enrolled_at, 1, 4) <= ? AND ge.status = 'active'
                WHERE l.id IS NOT NULL OR ge.id IS NOT NULL
            ", [$year, $year])['total'] ?? 0);
            if ($groupCount === 0) {
                $groupCount = (int)(Database::queryOne("SELECT COUNT(*) as total FROM groups")['total'] ?? 0);
            }
        } else {
            $groupCount = (int)(Database::queryOne("SELECT COUNT(*) as total FROM groups")['total'] ?? 0);
        }

        if ($year !== 'all') {
            $lessonCount = (int)(Database::queryOne("SELECT COUNT(*) as total FROM lessons WHERE substr(lesson_date, 1, 4) = ?", [$year])['total'] ?? 0);
        } else {
            $lessonCount = (int)(Database::queryOne("SELECT COUNT(*) as total FROM lessons")['total'] ?? 0);
        }

        if ($year !== 'all') {
            $attStats = Database::queryOne("
                SELECT 
                    COUNT(*) as total_records,
                    SUM(CASE WHEN ar.status = 'present' THEN 1 ELSE 0 END) as present_count,
                    SUM(CASE WHEN ar.status = 'late' THEN 1 ELSE 0 END) as late_count,
                    SUM(CASE WHEN ar.status = 'excused' THEN 1 ELSE 0 END) as excused_count,
                    SUM(CASE WHEN ar.status = 'absent' THEN 1 ELSE 0 END) as absent_count,
                    SUM(CASE WHEN ar.is_paid = 1 THEN 1 ELSE 0 END) as paid_count
                FROM attendance_records ar
                INNER JOIN lessons l ON ar.lesson_id = l.id
                WHERE substr(l.lesson_date, 1, 4) = ?
            ", [$year]);
        } else {
            $attStats = Database::queryOne("
                SELECT 
                    COUNT(*) as total_records,
                    SUM(CASE WHEN ar.status = 'present' THEN 1 ELSE 0 END) as present_count,
                    SUM(CASE WHEN ar.status = 'late' THEN 1 ELSE 0 END) as late_count,
                    SUM(CASE WHEN ar.status = 'excused' THEN 1 ELSE 0 END) as excused_count,
                    SUM(CASE WHEN ar.status = 'absent' THEN 1 ELSE 0 END) as absent_count,
                    SUM(CASE WHEN ar.is_paid = 1 THEN 1 ELSE 0 END) as paid_count
                FROM attendance_records ar
                INNER JOIN lessons l ON ar.lesson_id = l.id
            ");
        }

        $totalRecords = (int)($attStats['total_records'] ?? 0);
        $presentCount = (int)($attStats['present_count'] ?? 0) + (int)($attStats['late_count'] ?? 0);
        $paidCount = (int)($attStats['paid_count'] ?? 0);
        $attendanceRate = ($totalRecords > 0) ? round(($presentCount / $totalRecords) * 100, 1) : 100;
        $paidRate = ($totalRecords > 0) ? round(($paidCount / $totalRecords) * 100, 1) : 100;

        if ($year !== 'all') {
            $avgScoreRow = Database::queryOne("
                SELECT AVG(r.score) as avg_score, COUNT(r.id) as total_evaluations
                FROM assessment_results r
                INNER JOIN assessments a ON r.assessment_id = a.id
                WHERE substr(a.assessment_date, 1, 4) = ?
            ", [$year]);
        } else {
            $avgScoreRow = Database::queryOne("
                SELECT AVG(r.score) as avg_score, COUNT(r.id) as total_evaluations
                FROM assessment_results r
            ");
        }
        $avgScore = ($avgScoreRow && $avgScoreRow['avg_score']) ? round((float)$avgScoreRow['avg_score'], 2) : 5.0;
        $totalEvaluations = (int)($avgScoreRow['total_evaluations'] ?? 0);

        $groups = Database::query("
            SELECT g.id, g.name, g.type, g.color_tag,
                   (SELECT COUNT(*) FROM group_enrollments WHERE group_id = g.id AND status = 'active') as student_count,
                   (SELECT COUNT(*) FROM lessons WHERE group_id = g.id $yearFilter) as lesson_count,
                   (SELECT COUNT(*) FROM assessments WHERE group_id = g.id $yearFilterAsm) as assessment_count
            FROM groups g
            ORDER BY g.name ASC
        ");

        return [
            'year' => $year,
            'student_count' => $studentCount,
            'group_count' => $groupCount,
            'lesson_count' => $lessonCount,
            'total_attendance_records' => $totalRecords,
            'present_count' => $presentCount,
            'paid_count' => $paidCount,
            'attendance_rate' => $attendanceRate,
            'paid_rate' => $paidRate,
            'avg_score' => $avgScore,
            'total_evaluations' => $totalEvaluations,
            'groups' => $groups,
        ];
    }

    public function getAssessmentHistory(?string $year = null, ?string $groupId = null): array
    {
        $sql = "
            SELECT a.*, g.name as group_name, g.color_tag as group_color,
                   COUNT(r.id) as evaluated_students_count,
                   AVG(r.score) as avg_score
            FROM assessments a
            INNER JOIN groups g ON a.group_id = g.id
            LEFT JOIN assessment_results r ON a.id = r.assessment_id
            WHERE 1=1
        ";
        $params = [];
        if ($year && $year !== 'all') {
            $sql .= " AND substr(a.assessment_date, 1, 4) = ?";
            $params[] = $year;
        }
        if ($groupId) {
            $sql .= " AND a.group_id = ?";
            $params[] = $groupId;
        }
        $sql .= " GROUP BY a.id ORDER BY a.assessment_date DESC, a.created_at DESC";

        $assessments = Database::query($sql, $params);

        foreach ($assessments as &$asm) {
            $asm['results'] = Database::query("
                SELECT r.*, s.first_name, s.last_name, s.father_initial
                FROM assessment_results r
                INNER JOIN student_profiles s ON r.student_id = s.id
                WHERE r.assessment_id = ?
                ORDER BY s.first_name ASC, s.last_name ASC
            ", [$asm['id']]);
        }

        return $assessments;
    }

    public function getAttendanceIntervalReport(string $startDate, string $endDate, ?string $groupId = null): array
    {
        $params = [$startDate, $endDate];
        $groupFilter = "";
        if ($groupId) {
            $groupFilter = "AND l.group_id = ?";
            $params[] = $groupId;
        }

        $lessons = Database::query("
            SELECT l.*, g.name as group_name
            FROM lessons l
            INNER JOIN groups g ON l.group_id = g.id
            WHERE l.lesson_date >= ? AND l.lesson_date <= ? $groupFilter
            ORDER BY l.lesson_date ASC, l.start_time ASC
        ", $params);

        $stats = Database::queryOne("
            SELECT 
                COUNT(*) as total_records,
                SUM(CASE WHEN ar.status = 'present' THEN 1 ELSE 0 END) as present_count,
                SUM(CASE WHEN ar.status = 'late' THEN 1 ELSE 0 END) as late_count,
                SUM(CASE WHEN ar.status = 'excused' THEN 1 ELSE 0 END) as excused_count,
                SUM(CASE WHEN ar.status = 'absent' THEN 1 ELSE 0 END) as absent_count,
                SUM(CASE WHEN ar.is_paid = 1 THEN 1 ELSE 0 END) as paid_count,
                SUM(CASE WHEN ar.is_paid = 0 THEN 1 ELSE 0 END) as unpaid_count
            FROM attendance_records ar
            INNER JOIN lessons l ON ar.lesson_id = l.id
            WHERE l.lesson_date >= ? AND l.lesson_date <= ? $groupFilter
        ", $params);

        $studentStatsParams = [$startDate, $endDate];
        if ($groupId) {
            $studentStatsParams[] = $groupId;
        }

        $studentStats = Database::query("
            SELECT s.id, s.first_name, s.last_name, s.father_initial, g.name as group_name,
                   COUNT(ar.id) as total_sessions,
                   SUM(CASE WHEN ar.status IN ('present', 'late') THEN 1 ELSE 0 END) as attended_count,
                   SUM(CASE WHEN ar.status = 'excused' THEN 1 ELSE 0 END) as excused_count,
                   SUM(CASE WHEN ar.status = 'absent' THEN 1 ELSE 0 END) as absent_count,
                   SUM(CASE WHEN ar.is_paid = 1 THEN 1 ELSE 0 END) as paid_count,
                   SUM(CASE WHEN ar.is_paid = 0 THEN 1 ELSE 0 END) as unpaid_count
            FROM student_profiles s
            INNER JOIN group_enrollments ge ON s.id = ge.student_id AND ge.status = 'active'
            INNER JOIN groups g ON ge.group_id = g.id
            LEFT JOIN lessons l ON g.id = l.group_id AND l.lesson_date >= ? AND l.lesson_date <= ?
            LEFT JOIN attendance_records ar ON l.id = ar.lesson_id AND s.id = ar.student_id
            WHERE 1=1 " . ($groupId ? "AND g.id = ?" : "") . "
            GROUP BY s.id, g.id
            ORDER BY s.first_name ASC, s.last_name ASC
        ", $studentStatsParams);

        $totalRecords = (int)($stats['total_records'] ?? 0);
        $presentTotal = (int)($stats['present_count'] ?? 0) + (int)($stats['late_count'] ?? 0);
        $attendanceRate = ($totalRecords > 0) ? round(($presentTotal / $totalRecords) * 100, 1) : 100;

        return [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'group_id' => $groupId,
            'lessons' => $lessons,
            'total_lessons' => count($lessons),
            'total_records' => $totalRecords,
            'present_count' => $presentTotal,
            'excused_count' => (int)($stats['excused_count'] ?? 0),
            'absent_count' => (int)($stats['absent_count'] ?? 0),
            'paid_count' => (int)($stats['paid_count'] ?? 0),
            'unpaid_count' => (int)($stats['unpaid_count'] ?? 0),
            'attendance_rate' => $attendanceRate,
            'student_stats' => $studentStats,
        ];
    }
}
