<?php

declare(strict_types=1);

require_once __DIR__ . '/../app/Support/Autoloader.php';
\App\Support\Autoloader::register(dirname(__DIR__));
require_once __DIR__ . '/../app/Support/Helpers.php';

use App\Support\Database;

echo "\n=======================================================\n";
echo "🧹 CURĂȚARE BAZĂ DE DATE — RESET LA ZERO PENTRU TESTE\n";
echo "=======================================================\n\n";

$pdo = Database::connect();

// 1. Tabele de golit complet
$tablesToEmpty = [
    'attendance_records',
    'assessment_results',
    'assessments',
    'published_feedbacks',
    'learning_goals',
    'assignment_submissions',
    'assignments',
    'learning_materials',
    'lessons',
    'group_schedules',
    'group_enrollments',
    'groups',
    'guardian_student_links',
    'student_profiles',
    'guardian_profiles',
    'messages',
    'conversations',
    'announcements',
];

foreach ($tablesToEmpty as $table) {
    try {
        $pdo->exec("DELETE FROM $table");
        echo "  ✓ Curățat tabel: $table\n";
    } catch (\Throwable $e) {
        echo "  ! Notă la tabelul $table: " . $e->getMessage() . "\n";
    }
}

// 2. Curățare utilizatori (ștergem toți elevii și părinții de test)
$pdo->exec("DELETE FROM users WHERE role != 'teacher'");
echo "  ✓ Șterși toți utilizatorii elevi și părinți de test\n";

// 3. Asigurăm existența contului principal de profesor
$now = date('Y-m-d H:i:s');
$hash = password_hash('parola123', PASSWORD_DEFAULT);

$teacherExists = Database::queryOne("SELECT id FROM users WHERE role = 'teacher' LIMIT 1");
if (!$teacherExists) {
    $pdo->prepare("
        INSERT INTO users (id, email, username, password_hash, role, first_name, last_name, phone, is_active, created_at, updated_at)
        VALUES (?, ?, ?, ?, 'teacher', ?, ?, ?, 1, ?, ?)
    ")->execute([
        'usr_teacher_radu',
        'prof.radu@indrumar.ro',
        'profesor',
        $hash,
        'Radu',
        'Teodorescu',
        '0722111222',
        $now,
        $now
    ]);
    echo "  ✓ Creat cont profesor nou: prof.radu@indrumar.ro / profesor (parolă: parola123)\n";
} else {
    $pdo->prepare("
        UPDATE users 
        SET username = 'profesor', password_hash = ?, is_active = 1, updated_at = ?
        WHERE id = ?
    ")->execute([$hash, $now, $teacherExists['id']]);
    echo "  ✓ Actualizat cont profesor existent: username = profesor (parolă: parola123)\n";
}

$teacherId = Database::queryOne("SELECT id FROM users WHERE role = 'teacher' LIMIT 1")['id'];

// 4. Asigurăm workspace-ul profesorului
$wsExists = Database::queryOne("SELECT id FROM workspaces LIMIT 1");
if (!$wsExists) {
    $pdo->prepare("
        INSERT INTO workspaces (id, name, owner_id, created_at, updated_at)
        VALUES (?, ?, ?, ?, ?)
    ")->execute([
        'ws_radu_teodorescu',
        'Cabinet Didactic — Prof. Radu Teodorescu',
        $teacherId,
        $now,
        $now
    ]);
    echo "  ✓ Creat workspace didactic curat\n";
}

// 5. Asigurăm profilul profesorului
$profExists = Database::queryOne("SELECT id FROM teacher_profiles WHERE user_id = ?", [$teacherId]);
if (!$profExists) {
    $pdo->prepare("
        INSERT INTO teacher_profiles (id, user_id, title, phone, bio, created_at, updated_at)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ")->execute([
        'tch_radu_teodorescu',
        $teacherId,
        'Profesor Matematică & Mentor Robotică',
        '0722111222',
        'Cabinet didactic.',
        $now,
        $now
    ]);
    echo "  ✓ Creat profil didactic profesor\n";
}

// 6. Reset setări sistem (vacanță dezactivată)
try {
    $pdo->exec("DELETE FROM system_settings WHERE key = 'vacation_mode'");
} catch (\Throwable $e) {}

echo "\n-------------------------------------------------------\n";
echo "📊 SITUAȚIE BAZĂ DE DATE DUPĂ CURĂȚARE:\n";
echo "  • Elevi: 0\n";
echo "  • Grupe: 0\n";
echo "  • Orar / Ședințe: 0\n";
echo "  • Evaluări / Note: 0\n";
echo "  • Părinți: 0\n";
echo "  • Cont Profesor activ: prof.radu@indrumar.ro (sau username: profesor) / parola: parola123\n";
echo "-------------------------------------------------------\n";
echo "🎉 BAZA DE DATE ESTE CURATĂ ȘI PREGĂTITĂ PENTRU TESTE!\n";
echo "=======================================================\n\n";
