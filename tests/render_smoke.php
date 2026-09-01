<?php

declare(strict_types=1);

define('APP_ROOT', dirname(__DIR__));
require_once APP_ROOT . '/app/Support/Autoloader.php';
\App\Support\Autoloader::register(APP_ROOT);
require_once APP_ROOT . '/app/Support/Helpers.php';
\App\Support\Session::start();

set_error_handler(static function (int $severity, string $message, string $file, int $line): bool {
    if (!(error_reporting() & $severity)) {
        return false;
    }
    throw new ErrorException($message, 0, $severity, $file, $line);
});

$now = date('Y-m-d H:i:s');
$student = [
    'id' => 'stu_matei', 'first_name' => 'Matei', 'last_name' => 'Popescu', 'father_initial' => 'A.',
    'email' => 'matei@example.test', 'phone' => '0700000000', 'private_notes' => 'Răspunde bine la explicații vizuale.'
];
$student2 = [
    'id' => 'stu_sofia', 'first_name' => 'Sofia', 'last_name' => 'Popescu', 'father_initial' => 'M.',
    'email' => 'sofia@example.test', 'phone' => '0700000001', 'private_notes' => ''
];
$students = [$student, $student2];
$groups = [
    ['id' => 'grp_math', 'name' => 'Matematică — clasa a VII-a', 'type' => 'tutoring_group', 'description' => 'Consolidare și pregătire.', 'color_tag' => '#4A77DA'],
    ['id' => 'grp_logic', 'name' => 'Atelier de logică', 'type' => 'workshop', 'description' => 'Probleme aplicate.', 'color_tag' => '#258B5A'],
];
$schedules = [
    ['id' => 'sch_1', 'group_id' => 'grp_math', 'group_name' => 'Matematică — clasa a VII-a', 'group_color' => '#4A77DA', 'day_of_week' => 2, 'start_time' => '17:00', 'end_time' => '18:30', 'room_or_link' => 'Cabinet 2'],
    ['id' => 'sch_2', 'group_id' => 'grp_logic', 'group_name' => 'Atelier de logică', 'group_color' => '#258B5A', 'day_of_week' => 5, 'start_time' => '16:30', 'end_time' => '18:00', 'room_or_link' => 'Online'],
];
$lessons = [
    ['id' => 'les_1', 'group_id' => 'grp_math', 'group_name' => 'Matematică — clasa a VII-a', 'title' => 'Ecuații și probleme', 'lesson_date' => date('Y-m-d'), 'start_time' => '17:00', 'end_time' => '18:30', 'lesson_notes' => 'Recapitulare și exerciții ghidate.'],
    ['id' => 'les_2', 'group_id' => 'grp_logic', 'group_name' => 'Atelier de logică', 'title' => 'Strategii de rezolvare', 'lesson_date' => date('Y-m-d', strtotime('+2 days')), 'start_time' => '16:30', 'end_time' => '18:00', 'lesson_notes' => 'Lucru în perechi.'],
];
$assignments = [
    ['id' => 'asg_1', 'group_id' => 'grp_math', 'group_name' => 'Matematică — clasa a VII-a', 'title' => 'Fișa 4 — ecuații', 'description' => 'Rezolvă exercițiile 1–8.', 'assigned_date' => date('Y-m-d'), 'due_date' => date('Y-m-d', strtotime('+5 days'))],
];
$materials = [
    ['id' => 'mat_1', 'group_id' => 'grp_math', 'group_name' => 'Matematică — clasa a VII-a', 'title' => 'Sinteză de teorie', 'file_type' => 'pdf', 'url' => 'https://example.test/material'],
];
$assessments = [
    ['id' => 'asm_1', 'title' => 'Verificare — ecuații', 'assessment_date' => date('Y-m-d')],
];
$results = [
    ['student_id' => 'stu_matei', 'assessment_title' => 'Verificare — ecuații', 'assessment_date' => date('Y-m-d'), 'group_name' => 'Matematică — clasa a VII-a', 'score' => 9.2, 'max_score' => 10, 'published_feedback' => 'Ai argumentat foarte clar.', 'private_teacher_notes' => 'De exersat atenția la semne.'],
];
$feedbacks = [
    ['first_name' => 'Matei', 'last_name' => 'Popescu', 'content' => 'A explicat foarte bine colegilor pașii rezolvării.', 'category' => 'progress', 'created_at' => $now],
];
$goals = [
    ['id' => 'goal_1', 'title' => 'Să verific rezultatul după fiecare problemă', 'target_date' => date('Y-m-d', strtotime('+14 days')), 'is_completed' => 0],
    ['id' => 'goal_2', 'title' => 'Să rezolv independent trei exerciții', 'target_date' => date('Y-m-d'), 'is_completed' => 1],
];
$announcements = [
    ['id' => 'ann_1', 'title' => 'Programul de vineri', 'content' => "Ședința începe la 16:30.\nVă rog să aveți fișa tipărită.", 'group_name' => 'Atelier de logică', 'created_at' => $now],
];
$attendance = [
    ['status' => 'present', 'note' => '', 'lesson_title' => 'Ecuații și probleme', 'lesson_date' => date('Y-m-d'), 'group_name' => 'Matematică — clasa a VII-a'],
    ['status' => 'late', 'note' => '10 minute', 'lesson_title' => 'Recapitulare', 'lesson_date' => date('Y-m-d', strtotime('-7 days')), 'group_name' => 'Matematică — clasa a VII-a'],
];
$guardian = ['first_name' => 'Radu', 'last_name' => 'Popescu', 'relationship' => 'Tată', 'email' => 'radu@example.test', 'phone' => '0700000002'];
$messages = [
    ['sender_role' => 'parent', 'content' => 'Bună ziua! Am văzut tema pentru joi.', 'sent_at' => $now],
    ['sender_role' => 'teacher', 'content' => 'Bună ziua! Mulțumesc. Matei poate începe cu primele patru exerciții.', 'sent_at' => $now],
];
$conversationsTeacher = [[
    'id' => 'conv_1', 'guardian_first_name' => 'Radu', 'guardian_last_name' => 'Popescu', 'guardian_phone' => '0700000002',
    'student_first_name' => 'Matei', 'student_last_name' => 'Popescu', 'messages' => $messages,
]];
$conversationsParent = [[
    'id' => 'conv_1', 'teacher_first_name' => 'Ana', 'teacher_last_name' => 'Marinescu', 'teacher_phone' => '0700000003',
    'teacher_title' => 'Profesoară de matematică', 'messages' => $messages,
]];
$digest = [
    'student' => $student,
    'attendanceRate' => 92,
    'recentResults' => $results,
    'completedGoalsCount' => 1,
    'recentFeedbacks' => $feedbacks,
    'upcomingAssignments' => $assignments,
];
$teacherProfile = ['title' => 'Profesoară de matematică', 'phone' => '0700000003', 'bio' => 'Învățare calmă, structurată și bazată pe explicații clare.'];

$teacherUser = ['id' => 'usr_teacher', 'role' => 'teacher', 'first_name' => 'Ana', 'last_name' => 'Marinescu', 'email' => 'ana@example.test', 'title' => 'Matematică'];
$parentUser = ['id' => 'usr_parent', 'role' => 'parent', 'first_name' => 'Radu', 'last_name' => 'Popescu', 'email' => 'radu@example.test', 'guardian_id' => 'gua_1', 'children' => [$student, $student2], 'active_student_id' => $student['id']];
$studentUser = ['id' => 'usr_student', 'role' => 'student', 'first_name' => 'Matei', 'last_name' => 'Popescu', 'email' => 'matei@example.test', 'student_id' => $student['id']];

$cases = [
    ['auth/login', [], 'layouts/auth', null, '/login'],
    ['teacher/dashboard', compact('groups', 'students', 'schedules', 'lessons', 'assignments', 'feedbacks'), 'layouts/main', $teacherUser, '/teacher/dashboard'],
    ['teacher/groups', compact('groups'), 'layouts/main', $teacherUser, '/teacher/groups'],
    ['teacher/group_detail', ['group' => $groups[0], 'enrolledStudents' => $students, 'allStudents' => $students, 'schedules' => $schedules, 'lessons' => $lessons, 'assignments' => $assignments, 'materials' => $materials], 'layouts/main', $teacherUser, '/teacher/groups/grp_math'],
    ['teacher/students', compact('students'), 'layouts/main', $teacherUser, '/teacher/students'],
    ['teacher/student_detail', ['student' => $student, 'groups' => $groups, 'guardians' => [$guardian], 'attendance' => $attendance, 'results' => $results, 'feedbacks' => $feedbacks, 'goals' => $goals], 'layouts/main', $teacherUser, '/teacher/students/stu_matei'],
    ['teacher/attendance', ['groups' => $groups, 'selectedGroupId' => 'grp_math', 'lessons' => $lessons, 'selectedLessonId' => 'les_1', 'students' => $students, 'recordMap' => ['stu_matei' => ['status' => 'present', 'note' => '']]], 'layouts/main', $teacherUser, '/teacher/attendance'],
    ['teacher/lessons', compact('groups', 'lessons'), 'layouts/main', $teacherUser, '/teacher/lessons'],
    ['teacher/assignments', compact('groups', 'assignments', 'materials'), 'layouts/main', $teacherUser, '/teacher/assignments'],
    ['teacher/assessments', ['groups' => $groups, 'selectedGroupId' => 'grp_math', 'assessments' => $assessments, 'selectedAssessmentId' => 'asm_1', 'students' => $students, 'resultMap' => ['stu_matei' => $results[0]]], 'layouts/main', $teacherUser, '/teacher/assessments'],
    ['teacher/feedback', compact('students', 'feedbacks'), 'layouts/main', $teacherUser, '/teacher/feedback'],
    ['teacher/announcements', compact('groups', 'announcements'), 'layouts/main', $teacherUser, '/teacher/announcements'],
    ['teacher/conversations', ['conversations' => $conversationsTeacher, 'activeConv' => $conversationsTeacher[0]], 'layouts/main', $teacherUser, '/teacher/conversations'],
    ['teacher/calendar', compact('groups', 'schedules'), 'layouts/main', $teacherUser, '/teacher/calendar'],
    ['teacher/reports', ['students' => $students, 'selectedStudentId' => 'stu_matei', 'digest' => $digest], 'layouts/main', $teacherUser, '/teacher/reports'],
    ['teacher/settings', ['teacher' => $teacherProfile], 'layouts/main', $teacherUser, '/teacher/settings'],
    ['parent/dashboard', ['child' => $student, 'digest' => $digest, 'groups' => $groups], 'layouts/main', $parentUser, '/parent/dashboard'],
    ['parent/timetable', ['child' => $student, 'groups' => $groups, 'schedules' => $schedules], 'layouts/main', $parentUser, '/parent/timetable'],
    ['parent/attendance', ['child' => $student, 'records' => $attendance], 'layouts/main', $parentUser, '/parent/attendance'],
    ['parent/assignments', ['child' => $student, 'assignments' => $assignments], 'layouts/main', $parentUser, '/parent/assignments'],
    ['parent/results', ['child' => $student, 'results' => $results], 'layouts/main', $parentUser, '/parent/results'],
    ['parent/feedback', ['child' => $student, 'feedbacks' => $feedbacks], 'layouts/main', $parentUser, '/parent/feedback'],
    ['parent/goals', ['child' => $student, 'goals' => $goals], 'layouts/main', $parentUser, '/parent/goals'],
    ['parent/announcements', ['child' => $student, 'announcements' => $announcements], 'layouts/main', $parentUser, '/parent/announcements'],
    ['parent/conversations', ['conversations' => $conversationsParent, 'activeConv' => $conversationsParent[0]], 'layouts/main', $parentUser, '/parent/conversations'],
    ['parent/no_child', [], 'layouts/main', $parentUser, '/parent/dashboard'],
    ['student/dashboard', ['student' => $student, 'groups' => $groups, 'assignments' => $assignments, 'feedbacks' => $feedbacks, 'goals' => $goals, 'nextLesson' => $lessons[0]], 'layouts/main', $studentUser, '/student/dashboard'],
    ['student/timetable', ['student' => $student, 'groups' => $groups, 'schedules' => $schedules], 'layouts/main', $studentUser, '/student/timetable'],
    ['student/assignments', ['student' => $student, 'assignments' => $assignments], 'layouts/main', $studentUser, '/student/assignments'],
    ['student/materials', ['student' => $student, 'materials' => $materials], 'layouts/main', $studentUser, '/student/materials'],
    ['student/results', ['student' => $student, 'results' => $results], 'layouts/main', $studentUser, '/student/results'],
    ['student/feedback', ['student' => $student, 'feedbacks' => $feedbacks], 'layouts/main', $studentUser, '/student/feedback'],
    ['student/goals', ['student' => $student, 'goals' => $goals], 'layouts/main', $studentUser, '/student/goals'],
    ['student/announcements', ['student' => $student, 'announcements' => $announcements], 'layouts/main', $studentUser, '/student/announcements'],
    ['errors/403', [], 'layouts/main', $teacherUser, '/forbidden'],
    ['errors/404', [], 'layouts/main', $teacherUser, '/missing'],
    ['errors/500', [], 'layouts/main', $teacherUser, '/error'],
];

$failures = [];
foreach ($cases as [$view, $data, $layout, $user, $uri]) {
    \App\Support\Session::destroy();
    \App\Support\Session::start();
    if ($user) {
        \App\Support\Session::setUser($user);
        if (($user['role'] ?? null) === 'parent') {
            \App\Support\Session::setActiveStudentId($student['id']);
        }
    }
    $_SERVER['REQUEST_URI'] = $uri;
    try {
        ob_start();
        \App\Support\View::render($view, $data, $layout);
        $html = (string)ob_get_clean();
        if (!str_contains($html, '<!DOCTYPE html>') || !str_contains($html, '</html>')) {
            throw new RuntimeException('HTML document incomplete.');
        }
        $outputDirectory = getenv('SMOKE_OUTPUT_DIR');
        if ($outputDirectory) {
            if (!is_dir($outputDirectory)) {
                mkdir($outputDirectory, 0777, true);
            }
            $filename = str_replace('/', '__', $view) . '.html';
            file_put_contents(rtrim($outputDirectory, '/') . '/' . $filename, $html);
        }
        echo "PASS  {$view}\n";
    } catch (Throwable $exception) {
        while (ob_get_level() > 0) ob_end_clean();
        $failures[] = $view . ': ' . $exception->getMessage();
        echo "FAIL  {$view}: {$exception->getMessage()}\n";
    }
}

restore_error_handler();
if ($failures) {
    exit(1);
}

echo "\nAll view smoke tests passed.\n";
