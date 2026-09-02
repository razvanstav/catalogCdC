<?php

use App\Support\Csrf;
use App\Support\Session;

// Load .env if present
$envFile = dirname(__DIR__, 2) . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || strpos($line, '#') === 0) continue;
        if (strpos($line, '=') !== false) {
            [$name, $value] = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value);
            putenv("$name=$value");
            $_ENV[$name] = $value;
            $_SERVER[$name] = $value;
        }
    }
}

if (!function_exists('e')) {
    function e(?string $value): string
    {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('csrf_token')) {
    function csrf_token(): string
    {
        return Csrf::token();
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field(): string
    {
        return Csrf::field();
    }
}

if (!function_exists('auth_user')) {
    function auth_user(): ?array
    {
        return Session::user();
    }
}

if (!function_exists('auth_role')) {
    function auth_role(): ?string
    {
        return Session::role();
    }
}

if (!function_exists('workspace_id')) {
    function workspace_id(): ?string
    {
        $u = Session::user();
        return $u['workspace_id'] ?? null;
    }
}

if (!function_exists('workspace_name')) {
    function workspace_name(): string
    {
        $u = Session::user();
        return $u['workspace_name'] ?? 'Cabinet Didactic';
    }
}

if (!function_exists('flash')) {
    function flash(string $key, $default = null)
    {
        return Session::getFlash($key, $default);
    }
}

if (!function_exists('format_date_ro')) {
    function format_date_ro(?string $dateStr): string
    {
        if (!$dateStr) return '';
        $timestamp = strtotime($dateStr);
        if (!$timestamp) return $dateStr;
        return date('d.m.Y', $timestamp);
    }
}

if (!function_exists('format_date_long_ro')) {
    function format_date_long_ro(?string $dateStr): string
    {
        if (!$dateStr) return '';
        $timestamp = strtotime($dateStr);
        if (!$timestamp) return $dateStr;

        $months = [
            1 => 'Ian', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr',
            5 => 'Mai', 6 => 'Iun', 7 => 'Iul', 8 => 'Aug',
            9 => 'Sept', 10 => 'Oct', 11 => 'Nov', 12 => 'Dec'
        ];
        $days = [
            1 => 'Luni', 2 => 'Marți', 3 => 'Miercuri', 4 => 'Joi',
            5 => 'Vineri', 6 => 'Sâmbătă', 7 => 'Duminică'
        ];

        $dow = (int)date('N', $timestamp);
        $dayNum = date('j', $timestamp);
        $monthNum = (int)date('n', $timestamp);

        return ($days[$dow] ?? '') . ', ' . $dayNum . ' ' . ($months[$monthNum] ?? '');
    }
}

if (!function_exists('format_datetime_ro')) {
    function format_datetime_ro(?string $dateStr): string
    {
        if (!$dateStr) return '';
        $timestamp = strtotime($dateStr);
        if (!$timestamp) return $dateStr;
        return date('d.m.Y H:i', $timestamp);
    }
}

if (!function_exists('day_name_ro')) {
    function day_name_ro(int $dayOfWeek): string
    {
        $days = [
            1 => 'Luni',
            2 => 'Marți',
            3 => 'Miercuri',
            4 => 'Joi',
            5 => 'Vineri',
            6 => 'Sâmbătă',
            7 => 'Duminică',
        ];
        return $days[$dayOfWeek] ?? "Ziua $dayOfWeek";
    }
}

if (!function_exists('group_type_label')) {
    function group_type_label(string $type): string
    {
        switch ($type) {
            case 'school_class': return 'Clasă Școală';
            case 'tutoring_group': return 'Grupă Meditații';
            case 'workshop': return 'Atelier / Robotică';
            case 'individual_lesson': return 'Lecție 1-la-1';
            default: return ucfirst(str_replace('_', ' ', $type));
        }
    }
}

if (!function_exists('guardian_relationship_label')) {
    function guardian_relationship_label(?string $relationship): string
    {
        $rel = trim(strtolower((string)$relationship));
        switch ($rel) {
            case 'mother':
            case 'mama':
            case 'mamă':
                return 'Mamă';
            case 'father':
            case 'tata':
            case 'tată':
                return 'Tată';
            case 'legal_guardian':
            case 'legal guardian':
            case 'guardian':
            case 'parent':
            case 'parinte':
            case 'părinte':
            case '':
                return 'Părinte';
            default:
                return ucfirst((string)$relationship);
        }
    }
}

if (!function_exists('feedback_category_label')) {
    function feedback_category_label(string $category): string
    {
        switch ($category) {
            case 'progress': return 'Progres și implicare';
            case 'encouragement': return 'Încurajare';
            case 'attention': return 'Recomandare calmă';
            default: return ucfirst($category);
        }
    }
}

if (!function_exists('attendance_status_label')) {
    function attendance_status_label(string $status): string
    {
        switch ($status) {
            case 'present': return 'Prezent';
            case 'late': return 'Întârziat';
            case 'excused': return 'Învoit';
            case 'absent': return 'Absent';
            default: return ucfirst($status);
        }
    }
}

if (!function_exists('ui_tone_class')) {
    function ui_tone_class(?string $seed): string
    {
        $value = (string)($seed ?? 'indrumar');
        $index = (abs((int)crc32($value)) % 5) + 1;
        return 'tone-' . $index;
    }
}

if (!function_exists('initials')) {
    function initials(?string $firstName, ?string $lastName = null): string
    {
        $first = trim((string)$firstName);
        $last = trim((string)$lastName);
        $result = '';

        if ($first !== '') {
            $result .= function_exists('mb_substr') ? mb_substr($first, 0, 1) : substr($first, 0, 1);
        }
        if ($last !== '') {
            $result .= function_exists('mb_substr') ? mb_substr($last, 0, 1) : substr($last, 0, 1);
        }

        return strtoupper($result ?: 'U');
    }
}

if (!function_exists('attendance_badge_class')) {
    function attendance_badge_class(string $status): string
    {
        switch ($status) {
            case 'present': return 'badge--sage';
            case 'late': return 'badge--amber';
            case 'excused': return 'badge--brand';
            case 'absent': return 'badge--rose';
            default: return 'badge--neutral';
        }
    }
}
