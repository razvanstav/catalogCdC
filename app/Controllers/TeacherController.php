<?php

namespace App\Controllers;

use App\Repositories\GroupRepository;
use App\Repositories\StudentRepository;
use App\Repositories\AttendanceRepository;
use App\Repositories\AssignmentRepository;
use App\Repositories\AssessmentRepository;
use App\Repositories\FeedbackRepository;
use App\Repositories\AnnouncementRepository;
use App\Repositories\ConversationRepository;
use App\Repositories\GoalRepository;
use App\Repositories\UserRepository;
use App\Repositories\ReportRepository;
use App\Services\FamilyService;
use App\Support\Request;
use App\Support\Response;
use App\Support\Session;
use App\Support\View;

class TeacherController
{
    private GroupRepository $groupRepo;
    private StudentRepository $studentRepo;
    private AttendanceRepository $attendanceRepo;
    private AssignmentRepository $assignmentRepo;
    private AssessmentRepository $assessmentRepo;
    private FeedbackRepository $feedbackRepo;
    private AnnouncementRepository $announcementRepo;
    private ConversationRepository $conversationRepo;
    private GoalRepository $goalRepo;
    private UserRepository $userRepo;
    private ReportRepository $reportRepo;
    private FamilyService $familyService;

    public function __construct()
    {
        $this->groupRepo = new GroupRepository();
        $this->studentRepo = new StudentRepository();
        $this->attendanceRepo = new AttendanceRepository();
        $this->assignmentRepo = new AssignmentRepository();
        $this->assessmentRepo = new AssessmentRepository();
        $this->feedbackRepo = new FeedbackRepository();
        $this->announcementRepo = new AnnouncementRepository();
        $this->conversationRepo = new ConversationRepository();
        $this->goalRepo = new GoalRepository();
        $this->userRepo = new UserRepository();
        $this->reportRepo = new ReportRepository();
        $this->familyService = new FamilyService();
    }

    public function dashboard(): void
    {
        $groups = $this->groupRepo->all();
        $students = $this->studentRepo->all('teacher');
        $schedules = $this->groupRepo->getSchedules();
        $lessons = $this->attendanceRepo->getLessons();
        $recentLessons = $this->attendanceRepo->getRecentChronologicalLessons(6);
        $currentWeekSchedule = $this->groupRepo->getCurrentWeekSchedule();
        $assignments = $this->assignmentRepo->all();
        $feedbacks = $this->feedbackRepo->all();

        View::render('teacher/dashboard', [
            'groups' => $groups,
            'students' => $students,
            'schedules' => $schedules,
            'lessons' => $lessons,
            'recentLessons' => $recentLessons,
            'currentWeekSchedule' => $currentWeekSchedule,
            'assignments' => $assignments,
            'feedbacks' => $feedbacks,
        ]);
    }

    public function groups(): void
    {
        $groups = $this->groupRepo->all();
        View::render('teacher/groups', ['groups' => $groups]);
    }

    public function groupDetail(string $id): void
    {
        $group = $this->groupRepo->findById($id);
        if (!$group) Response::notFound();

        $enrolledStudents = $this->studentRepo->getStudentsInGroup($id, 'teacher');
        $allStudents = $this->studentRepo->all('teacher');
        $enrolledIds = array_flip(array_column($enrolledStudents, 'id'));
        $unenrolledStudents = array_values(array_filter($allStudents, fn($s) => !isset($enrolledIds[$s['id']])));

        $schedules = $this->groupRepo->getSchedules($id);
        $lessons = $this->attendanceRepo->getLessons($id);
        $assignments = $this->assignmentRepo->getForGroup($id);
        $materials = $this->assignmentRepo->getMaterials($id);

        View::render('teacher/group_detail', [
            'group' => $group,
            'enrolledStudents' => $enrolledStudents,
            'allStudents' => $allStudents,
            'unenrolledStudents' => $unenrolledStudents,
            'schedules' => $schedules,
            'lessons' => $lessons,
            'assignments' => $assignments,
            'materials' => $materials,
        ]);
    }

    public function createGroup(): void
    {
        $name = Request::input('name');
        $type = Request::input('type', 'tutoring_group');
        $description = Request::input('description');
        $colorTag = Request::input('color_tag', '#4A77DA');

        if ($name) {
            $this->groupRepo->create([
                'name' => $name,
                'type' => $type,
                'description' => $description,
                'color_tag' => $colorTag,
            ]);
            Session::flash('success', 'Grupa a fost creată cu succes!');
        }
        Response::redirect('/teacher/groups');
    }

    public function enrollStudent(string $id): void
    {
        $studentId = Request::input('student_id');
        if ($studentId) {
            $this->groupRepo->enrollStudent($id, $studentId);
            Session::flash('success', 'Elevul a fost înscris cu succes în grupă!');
        }
        Response::redirect("/teacher/groups/$id");
    }

    public function updateGroup(string $id): void
    {
        $name = Request::input('name');
        $type = Request::input('type', 'tutoring_group');
        $description = Request::input('description');
        $colorTag = Request::input('color_tag', '#4A77DA');

        if ($name) {
            $this->groupRepo->update($id, [
                'name' => $name,
                'type' => $type,
                'description' => $description,
                'color_tag' => $colorTag,
            ]);
            Session::flash('success', 'Grupa a fost actualizată cu succes!');
        }
        Response::redirect("/teacher/groups/$id");
    }

    public function unenrollStudent(string $id): void
    {
        $studentId = Request::input('student_id');
        if ($studentId) {
            $this->groupRepo->unenrollStudent($id, $studentId);
            Session::flash('success', 'Elevul a fost scos din această grupă.');
        }
        Response::redirect("/teacher/groups/$id");
    }

    public function students(): void
    {
        $students = $this->studentRepo->all('teacher');
        $guardians = $this->userRepo->getAllGuardians();
        $groups = $this->groupRepo->all();
        View::render('teacher/students', [
            'students' => $students,
            'guardians' => $guardians,
            'groups' => $groups,
        ]);
    }

    public function togglePaid(string $id): void
    {
        $this->studentRepo->togglePaid($id);
        Session::flash('success', 'Starea de plată (PAID) a fost actualizată!');
        $returnUrl = Request::input('return_url', '/teacher/dashboard');
        Response::redirect($returnUrl);
    }

    public function studentDetail(string $id): void
    {
        $student = $this->studentRepo->findById($id, 'teacher');
        if (!$student) Response::notFound();

        $groups = $this->groupRepo->getGroupsForStudent($id);
        $guardians = $this->studentRepo->getGuardiansForStudent($id);
        $allGuardians = $this->userRepo->getAllGuardians();
        $attendance = $this->attendanceRepo->getForStudent($id);
        $results = $this->assessmentRepo->getResultsForStudent($id, 'teacher');
        $feedbacks = $this->feedbackRepo->getForStudent($id);
        $goals = $this->goalRepo->getForStudent($id);

        View::render('teacher/student_detail', [
            'student' => $student,
            'groups' => $groups,
            'guardians' => $guardians,
            'allGuardians' => $allGuardians,
            'attendance' => $attendance,
            'results' => $results,
            'feedbacks' => $feedbacks,
            'goals' => $goals,
        ]);
    }

    public function createStudent(): void
    {
        $first = trim(Request::input('first_name') ?? '');
        $last = trim(Request::input('last_name') ?? '');
        $initial = trim(Request::input('father_initial') ?? '');
        $guardianId = Request::input('guardian_id');
        $guardianName = trim(Request::input('guardian_name') ?? '');
        $guardianPhone = trim(Request::input('guardian_phone') ?? '');
        $username = trim(Request::input('username') ?? '');
        $password = trim(Request::input('password') ?? '');
        $email = trim(Request::input('email') ?? '');
        $phone = trim(Request::input('phone') ?? '');
        $groupId = Request::input('group_id');
        $notes = Request::input('private_notes');

        if ($first && $last) {
            if (!$username) {
                $username = strtolower($first . '.' . $last);
            }
            if (!$password) {
                $password = 'elev123';
            }

            $this->userRepo->createStudentWithCredentials([
                'first_name' => $first,
                'last_name' => $last,
                'father_initial' => $initial ? "$initial." : null,
                'username' => $username,
                'password' => $password,
                'email' => $email ?: ($username . '@elev.ro'),
                'phone' => $phone,
                'guardian_id' => $guardianId ?: null,
                'guardian_name' => $guardianName ?: null,
                'guardian_phone' => $guardianPhone ?: null,
                'group_id' => $groupId ?: null,
                'private_notes' => $notes,
            ]);

            Session::flash('success', "Elevul {$first} {$last} a fost înregistrat cu succes! Utilizator login: {$username}, Parolă: {$password}");
        } else {
            Session::flash('error', 'Te rugăm să completezi cel puțin numele și prenumele elevului.');
        }

        Response::redirect('/teacher/students');
    }

    public function updateStudent(string $id): void
    {
        $first = trim(Request::input('first_name') ?? '');
        $last = trim(Request::input('last_name') ?? '');
        $initial = trim(Request::input('father_initial') ?? '');
        $email = trim(Request::input('email') ?? '');
        $phone = trim(Request::input('phone') ?? '');

        if ($first && $last) {
            $this->studentRepo->update($id, [
                'first_name' => $first,
                'last_name' => $last,
                'father_initial' => $initial ?: null,
                'email' => $email ?: null,
                'phone' => $phone ?: null,
            ]);
            Session::flash('success', 'Datele elevului au fost actualizate cu succes!');
        } else {
            Session::flash('error', 'Numele și prenumele sunt obligatorii.');
        }

        Response::redirect("/teacher/students/$id");
    }

    public function updateGuardian(string $id): void
    {
        $studentId = Request::input('return_student_id');
        $first = trim(Request::input('first_name') ?? '');
        $last = trim(Request::input('last_name') ?? '');
        $phone = trim(Request::input('phone') ?? '');
        $email = trim(Request::input('email') ?? '');
        $relationship = trim(Request::input('relationship') ?? 'Părinte');

        if ($first && $last) {
            $this->userRepo->updateGuardian($id, [
                'first_name' => $first,
                'last_name' => $last,
                'phone' => $phone ?: null,
                'email' => $email ?: null,
                'relationship' => $relationship,
            ]);
            Session::flash('success', 'Datele de contact ale părintelui au fost actualizate cu succes!');
        } else {
            Session::flash('error', 'Numele și prenumele părintelui sunt obligatorii.');
        }

        if ($studentId) {
            Response::redirect("/teacher/students/$studentId");
        } else {
            Response::redirect('/teacher/students');
        }
    }

    public function linkGuardianToStudent(string $studentId): void
    {
        $guardianId = Request::input('guardian_id');
        $guardianName = trim(Request::input('guardian_name') ?? '');
        $guardianPhone = trim(Request::input('guardian_phone') ?? '');

        if ($guardianId) {
            $this->userRepo->linkGuardianToStudent($studentId, $guardianId);
            Session::flash('success', 'Părintele a fost asociat elevului!');
        } elseif ($guardianName) {
            $parts = explode(' ', $guardianName, 2);
            $gFirst = $parts[0];
            $gLast = $parts[1] ?? 'Familie';
            $gEmail = 'parinte.' . strtolower($gFirst . '.' . $gLast) . '.' . bin2hex(random_bytes(2)) . '@familie.ro';
            $now = date('Y-m-d H:i:s');
            $uId = 'usr_' . bin2hex(random_bytes(6));
            $gId = 'grd_' . bin2hex(random_bytes(6));

            \App\Support\Database::execute(
                "INSERT INTO users (id, email, username, password_hash, role, first_name, last_name, phone, is_active, created_at, updated_at) VALUES (?, ?, ?, ?, 'parent', ?, ?, ?, 1, ?, ?)",
                [$uId, $gEmail, strtolower($gFirst . '.' . $gLast), password_hash('parinte123', PASSWORD_DEFAULT), $gFirst, $gLast, $guardianPhone ?: null, $now, $now]
            );
            \App\Support\Database::execute(
                "INSERT INTO guardian_profiles (id, user_id, workspace_id, first_name, last_name, phone, email, created_at, updated_at) VALUES (?, ?, 'ws_radu_teodorescu', ?, ?, ?, ?, ?, ?)",
                [$gId, $uId, $gFirst, $gLast, $guardianPhone ?: null, $gEmail, $now, $now]
            );
            $this->userRepo->linkGuardianToStudent($studentId, $gId);
            Session::flash('success', 'Părintele nou a fost înregistrat și asociat elevului!');
        }

        Response::redirect("/teacher/students/$studentId");
    }

    public function updateStudentNotes(string $id): void
    {
        $notes = Request::input('private_notes', '');
        $this->studentRepo->updatePrivateNotes($id, $notes);
        Session::flash('success', 'Notița privată a fost salvată cu succes (Strict Confidențial)!');
        Response::redirect("/teacher/students/$id");
    }

    public function attendance(): void
    {
        $groups = $this->groupRepo->all();
        $selectedLessonId = Request::input('lesson_id');
        $selectedGroupId = Request::input('group_id');
        $date = Request::input('date', 'today');

        if ($selectedLessonId) {
            $lesson = \App\Support\Database::queryOne("SELECT * FROM lessons WHERE id = ?", [$selectedLessonId]);
            if ($lesson) {
                $selectedGroupId = $lesson['group_id'];
                $date = $lesson['lesson_date'];
            }
        }

        if (empty($selectedGroupId)) {
            $selectedGroupId = $groups[0]['id'] ?? '';
        }

        $week = $this->groupRepo->getWeekCalendar($date);

        $lessons = $selectedGroupId
            ? $this->attendanceRepo->getLessonsForGroupInWeek($selectedGroupId, $week['sunday_date'], $week['saturday_date'])
            : [];

        $lessonIdsInWeek = array_column($lessons, 'id');
        if (empty($selectedLessonId) || !in_array($selectedLessonId, $lessonIdsInWeek, true)) {
            $selectedLessonId = $lessons[0]['id'] ?? null;
        }

        $currentLesson = $selectedLessonId ? \App\Support\Database::queryOne("SELECT * FROM lessons WHERE id = ?", [$selectedLessonId]) : null;

        $students = ($selectedLessonId && $selectedGroupId)
            ? $this->attendanceRepo->getStudentsForLesson($selectedLessonId, $selectedGroupId)
            : ($selectedGroupId ? $this->studentRepo->getStudentsInGroup($selectedGroupId, 'teacher') : []);
        $records = $selectedLessonId ? $this->attendanceRepo->getForLesson($selectedLessonId) : [];
        $allStudents = $this->studentRepo->all('teacher');

        $recordMap = [];
        foreach ($records as $r) {
            $recordMap[$r['student_id']] = $r;
        }

        View::render('teacher/attendance', [
            'groups' => $groups,
            'selectedGroupId' => $selectedGroupId,
            'week' => $week,
            'selectedDate' => $date,
            'lessons' => $lessons,
            'selectedLessonId' => $selectedLessonId,
            'currentLesson' => $currentLesson,
            'students' => $students,
            'allStudents' => $allStudents,
            'recordMap' => $recordMap,
        ]);
    }

    public function addGuestToLesson(): void
    {
        $lessonId = Request::input('lesson_id');
        $groupId = Request::input('group_id');
        $studentId = Request::input('student_id');
        $date = Request::input('date', 'today');
        $note = Request::input('note', 'Recuperare / Transfer oră');

        if ($lessonId && $studentId) {
            $this->attendanceRepo->addGuestToLesson($lessonId, $studentId, 'present', $note);
            Session::flash('success', 'Elevul a fost adăugat strict la această ședință!');
        }
        Response::redirect("/teacher/attendance?group_id=$groupId&lesson_id=$lessonId&date=$date");
    }

    public function removeGuestFromLesson(): void
    {
        $lessonId = Request::input('lesson_id');
        $groupId = Request::input('group_id');
        $studentId = Request::input('student_id');
        $date = Request::input('date', 'today');

        if ($lessonId && $studentId) {
            $this->attendanceRepo->removeGuestFromLesson($lessonId, $studentId);
            Session::flash('success', 'Elevul a fost eliminat din această ședință.');
        }
        Response::redirect("/teacher/attendance?group_id=$groupId&lesson_id=$lessonId&date=$date");
    }

    public function saveAttendance(): void
    {
        $lessonId = Request::input('lesson_id');
        $groupId = Request::input('group_id');
        $date = Request::input('date', 'today');
        $statuses = Request::input('status', []);
        $notes = Request::input('notes', []);
        $isPaids = Request::input('is_paid', []);

        if ($lessonId && is_array($statuses)) {
            foreach ($statuses as $studentId => $status) {
                $note = $notes[$studentId] ?? null;
                $isPaid = isset($isPaids[$studentId]) ? 1 : 0;
                $this->attendanceRepo->record($lessonId, $studentId, $status, $note, $isPaid);
            }
            Session::flash('success', 'Prezența și statusul de plată pentru această ședință au fost salvate!');
        }

        Response::redirect("/teacher/attendance?group_id=$groupId&lesson_id=$lessonId&date=$date");
    }

    public function lessons(): void
    {
        $groups = $this->groupRepo->all();
        $lessons = $this->attendanceRepo->getLessons();
        View::render('teacher/lessons', ['groups' => $groups, 'lessons' => $lessons]);
    }

    public function createLesson(): void
    {
        $groupId = Request::input('group_id');
        $title = Request::input('title');
        $date = Request::input('lesson_date');
        $start = Request::input('start_time');
        $end = Request::input('end_time');
        $notes = Request::input('lesson_notes');

        if ($groupId && $title) {
            $this->attendanceRepo->createLesson([
                'group_id' => $groupId,
                'title' => $title,
                'lesson_date' => $date,
                'start_time' => $start,
                'end_time' => $end,
                'lesson_notes' => $notes,
            ]);
            Session::flash('success', 'Ședința a fost programată în calendar!');
        }
        Response::redirect('/teacher/lessons');
    }

    public function assignments(): void
    {
        $groups = $this->groupRepo->all();
        $assignments = $this->assignmentRepo->all();
        $materials = $this->assignmentRepo->getMaterials();

        $submissionsMap = [];
        foreach ($assignments as $asg) {
            $submissionsMap[$asg['id']] = $this->assignmentRepo->getSubmissionsForAssignment($asg['id']);
        }

        View::render('teacher/assignments', [
            'groups' => $groups,
            'assignments' => $assignments,
            'materials' => $materials,
            'submissionsMap' => $submissionsMap,
        ]);
    }

    public function createAssignment(): void
    {
        $groupId = Request::input('group_id');
        $title = Request::input('title');
        $desc = Request::input('description');
        $due = Request::input('due_date');

        $attachmentUrl = null;
        if (!empty($_FILES['attachment']['tmp_name']) && is_uploaded_file($_FILES['attachment']['tmp_name'])) {
            $uploadDir = APP_ROOT . '/public/uploads/assignments/';
            if (!is_dir($uploadDir)) {
                @mkdir($uploadDir, 0777, true);
            }
            $ext = strtolower(pathinfo($_FILES['attachment']['name'], PATHINFO_EXTENSION));
            $safeName = 'asg_' . bin2hex(random_bytes(6)) . '.' . $ext;
            if (move_uploaded_file($_FILES['attachment']['tmp_name'], $uploadDir . $safeName)) {
                $attachmentUrl = '/uploads/assignments/' . $safeName;
            }
        }

        if ($groupId && $title) {
            $this->assignmentRepo->create([
                'group_id' => $groupId,
                'title' => $title,
                'description' => $desc,
                'attachment_url' => $attachmentUrl,
                'due_date' => $due,
            ]);
            Session::flash('success', 'Tema a fost distribuită cu succes elevilor!');
        }
        Response::redirect('/teacher/assignments');
    }

    public function createMaterial(): void
    {
        $groupId = Request::input('group_id');
        $title = Request::input('title');
        $url = Request::input('url');
        $type = Request::input('file_type', 'pdf');

        if ($groupId && $title) {
            $this->assignmentRepo->createMaterial([
                'group_id' => $groupId,
                'title' => $title,
                'url' => $url,
                'file_type' => $type,
            ]);
            Session::flash('success', 'Materialul didactic a fost adăugat!');
        }
        Response::redirect('/teacher/assignments');
    }

    public function assessments(): void
    {
        $groups = $this->groupRepo->all();
        $selectedDate = Request::input('date', date('Y-m-d'));
        $selectedLessonId = Request::input('lesson_id');
        $requestedAsmId = Request::input('assessment_id');
        $selectedGroupId = Request::input('group_id');

        if ($selectedLessonId) {
            $lesson = \App\Support\Database::queryOne("SELECT * FROM lessons WHERE id = ?", [$selectedLessonId]);
            if ($lesson) {
                $selectedGroupId = $lesson['group_id'];
                $selectedDate = $lesson['lesson_date'];
            }
        }

        if ($requestedAsmId) {
            $currentAsm = $this->assessmentRepo->findById($requestedAsmId);
            if ($currentAsm) {
                $selectedGroupId = $currentAsm['group_id'];
                $selectedDate = $currentAsm['assessment_date'];
                if (!empty($currentAsm['lesson_id'])) {
                    $selectedLessonId = $currentAsm['lesson_id'];
                }
            }
        }

        if (empty($selectedGroupId)) {
            $selectedGroupId = $groups[0]['id'] ?? '';
        }

        // Fetch lessons for the chosen date to populate the session selector
        $lessonsForDate = \App\Support\Database::query("
            SELECT l.*, g.name as group_name
            FROM lessons l
            INNER JOIN groups g ON l.group_id = g.id
            WHERE l.lesson_date = ?
            ORDER BY l.start_time ASC
        ", [$selectedDate]);

        $allRecentLessons = \App\Support\Database::query("
            SELECT l.*, g.name as group_name
            FROM lessons l
            INNER JOIN groups g ON l.group_id = g.id
            ORDER BY l.lesson_date DESC, l.start_time ASC
            LIMIT 40
        ");

        $assessments = $selectedGroupId ? $this->assessmentRepo->getForGroup($selectedGroupId) : [];

        // Auto-create assessment if none exists for this group
        if (empty($assessments) && !empty($selectedGroupId)) {
            $defaultAsmId = $this->assessmentRepo->create([
                'group_id' => $selectedGroupId,
                'lesson_id' => $selectedLessonId ?: null,
                'title' => 'Evaluare & Note ' . date('Y'),
                'assessment_type' => 'test',
                'max_score' => 5.0,
                'assessment_date' => $selectedDate,
            ]);
            $assessments = $this->assessmentRepo->getForGroup($selectedGroupId);
            $selectedAssessmentId = $defaultAsmId;
        } else {
            $validIds = array_column($assessments, 'id');
            if ($requestedAsmId && in_array($requestedAsmId, $validIds, true)) {
                $selectedAssessmentId = $requestedAsmId;
            } else {
                $selectedAssessmentId = $assessments[0]['id'] ?? '';
            }
        }

        $students = $selectedGroupId ? $this->studentRepo->getStudentsInGroup($selectedGroupId, 'teacher') : [];
        $results = $selectedAssessmentId ? $this->assessmentRepo->getResultsForAssessment($selectedAssessmentId, 'teacher') : [];

        $resultMap = [];
        foreach ($results as $r) {
            $resultMap[$r['student_id']] = $r;
        }

        View::render('teacher/assessments', [
            'groups' => $groups,
            'selectedGroupId' => $selectedGroupId,
            'assessments' => $assessments,
            'selectedAssessmentId' => $selectedAssessmentId,
            'students' => $students,
            'resultMap' => $resultMap,
            'selectedDate' => $selectedDate,
            'selectedLessonId' => $selectedLessonId,
            'lessonsForDate' => $lessonsForDate,
            'allRecentLessons' => $allRecentLessons,
        ]);
    }

    public function createAssessment(): void
    {
        $lessonId = Request::input('lesson_id');
        $groupId = Request::input('group_id');
        $title = trim(Request::input('title') ?? '');
        $type = Request::input('assessment_type', 'test');
        $maxScore = (float)Request::input('max_score', 5.0);
        $date = Request::input('assessment_date', date('Y-m-d'));

        if ($lessonId) {
            $lesson = \App\Support\Database::queryOne("SELECT * FROM lessons WHERE id = ?", [$lessonId]);
            if ($lesson) {
                $groupId = $lesson['group_id'];
                $date = $lesson['lesson_date'];
                if (empty($title)) {
                    $title = "Evaluare " . $lesson['title'];
                }
            }
        }

        if (empty($title) && !empty($groupId)) {
            $title = "Evaluare " . date('d.m.Y');
        }

        if ($groupId && $title) {
            $id = $this->assessmentRepo->create([
                'group_id' => $groupId,
                'lesson_id' => $lessonId ?: null,
                'title' => $title,
                'assessment_type' => $type,
                'max_score' => $maxScore,
                'assessment_date' => $date,
            ]);
            Session::flash('success', "Evaluarea «{$title}» a fost creată cu succes!");
            Response::redirect("/teacher/assessments?group_id=$groupId&assessment_id=$id&date=$date");
            return;
        }
        Response::redirect('/teacher/assessments');
    }

    public function saveAssessmentResults(): void
    {
        $assessmentId = Request::input('assessment_id');
        $groupId = Request::input('group_id');
        $scores = Request::input('scores', []);
        $feedbacks = Request::input('published_feedback', []);
        $privateNotes = Request::input('private_notes', []);

        if (empty($assessmentId) && !empty($groupId)) {
            $assessmentId = $this->assessmentRepo->create([
                'group_id' => $groupId,
                'title' => 'Evaluare & Note Curs ' . date('Y'),
                'assessment_type' => 'test',
                'max_score' => 5.0,
                'assessment_date' => date('Y-m-d'),
            ]);
        }

        if ($assessmentId) {
            $students = $groupId ? $this->studentRepo->getStudentsInGroup($groupId, 'teacher') : [];
            if (empty($students)) {
                $allKeys = array_unique(array_merge(array_keys($scores), array_keys($feedbacks), array_keys($privateNotes)));
                $students = array_map(fn($id) => ['id' => $id], $allKeys);
            }

            foreach ($students as $student) {
                $sId = $student['id'];
                $scoreVal = $scores[$sId] ?? null;
                $fb = isset($feedbacks[$sId]) && trim($feedbacks[$sId]) !== '' ? trim($feedbacks[$sId]) : null;
                $pn = isset($privateNotes[$sId]) && trim($privateNotes[$sId]) !== '' ? trim($privateNotes[$sId]) : null;

                if ($scoreVal !== '' && $scoreVal !== null) {
                    $score = (float)$scoreVal;
                    $this->assessmentRepo->saveResult($assessmentId, $sId, $score, $fb, $pn, true);
                } elseif ($fb !== null || $pn !== null) {
                    $existing = \App\Support\Database::queryOne("SELECT score FROM assessment_results WHERE assessment_id = ? AND student_id = ?", [$assessmentId, $sId]);
                    $score = $existing ? (float)$existing['score'] : 5.0;
                    $this->assessmentRepo->saveResult($assessmentId, $sId, $score, $fb, $pn, true);
                }
            }
            Session::flash('success', 'Notele și aprecierile elevilor au fost salvate cu succes!');
        }

        Response::redirect("/teacher/assessments?group_id=$groupId&assessment_id=$assessmentId");
    }

    public function feedback(): void
    {
        $students = $this->studentRepo->all('teacher');
        $feedbacks = $this->feedbackRepo->all();
        View::render('teacher/feedback', ['students' => $students, 'feedbacks' => $feedbacks]);
    }

    public function createFeedback(): void
    {
        $studentId = Request::input('student_id');
        $content = Request::input('content');
        $category = Request::input('category', 'progress');

        if ($studentId && $content) {
            $this->feedbackRepo->create($studentId, $content, $category);
            Session::flash('success', 'Aprecierea a fost publicată către familie!');
        }
        Response::redirect('/teacher/feedback');
    }

    public function announcements(): void
    {
        $groups = $this->groupRepo->all();
        $announcements = $this->announcementRepo->all();
        View::render('teacher/announcements', ['groups' => $groups, 'announcements' => $announcements]);
    }

    public function createAnnouncement(): void
    {
        $groupId = Request::input('group_id') ?: null;
        $title = Request::input('title');
        $content = Request::input('content');

        if ($title && $content) {
            $this->announcementRepo->create($title, $content, $groupId);
            Session::flash('success', 'Anunțul a fost transmis!');
        }
        Response::redirect('/teacher/announcements');
    }

    public function conversations(): void
    {
        $user = Session::user();
        $teacherId = $user['teacher_id'] ?? 'tch_radu_teodorescu';
        $conversations = $this->conversationRepo->getForTeacher($teacherId);
        $allGuardians = $this->conversationRepo->getAllGuardiansWithStudents();

        $selectedConvId = Request::input('conv_id', $conversations[0]['id'] ?? '');
        $activeConv = null;
        foreach ($conversations as $c) {
            if ($c['id'] === $selectedConvId) {
                $activeConv = $c;
                break;
            }
        }
        if (!$activeConv && count($conversations) > 0) {
            $activeConv = $conversations[0];
        }

        View::render('teacher/conversations', [
            'conversations' => $conversations,
            'activeConv' => $activeConv,
            'allGuardians' => $allGuardians,
        ]);
    }

    public function startConversation(): void
    {
        $user = Session::user();
        $teacherId = $user['teacher_id'] ?? 'tch_radu_teodorescu';
        $guardianId = Request::input('guardian_id');
        $studentId = Request::input('student_id');
        $content = Request::input('content');

        if ($guardianId) {
            $convId = $this->conversationRepo->findOrCreateConversation($teacherId, $guardianId, $studentId ?: null);
            if (!empty($content)) {
                $this->conversationRepo->sendMessage($convId, 'teacher', $user['id'], $content);
                Session::flash('success', 'Mesajul a fost transmis părintelui!');
            }
            Response::redirect("/teacher/conversations?conv_id=$convId");
            return;
        }

        Session::flash('error', 'Selectează un părinte din listă.');
        Response::redirect('/teacher/conversations');
    }

    public function sendMessage(): void
    {
        $convId = Request::input('conversation_id');
        $content = Request::input('content');
        $user = Session::user();

        if ($convId && $content) {
            $this->conversationRepo->sendMessage($convId, 'teacher', $user['id'], $content);
        }
        Response::redirect("/teacher/conversations?conv_id=$convId");
    }

    public function calendar(): void
    {
        $groups = $this->groupRepo->all();
        $date = Request::input('date', 'today');
        $week = $this->groupRepo->getWeekCalendar($date);
        $schedules = $this->groupRepo->getSchedules();
        View::render('teacher/calendar', [
            'groups' => $groups,
            'week' => $week,
            'schedules' => $schedules,
            'selectedDate' => $date,
        ]);
    }

    public function addScheduleFromCalendar(): void
    {
        $groupId = Request::input('group_id');
        $dayOfWeek = (int)Request::input('day_of_week', 1);
        $startTime = Request::input('start_time', '09:00');
        $durationMinutes = (int)Request::input('duration_minutes', 90);
        $room = Request::input('room_or_link', 'Cabinet didactic');
        $date = Request::input('return_date', 'today');

        if ($groupId && $startTime) {
            $this->groupRepo->addScheduleWithDuration($groupId, $dayOfWeek, $startTime, $durationMinutes, $room);
            Session::flash('success', 'Intervalul de orar a fost salvat și ședințele au fost generate automat!');
        }
        Response::redirect("/teacher/calendar?date=$date");
    }

    public function reports(): void
    {
        $groups = $this->groupRepo->all();
        $students = $this->studentRepo->all('teacher');
        $availableYears = $this->reportRepo->getAvailableYears();

        $activeTab = Request::input('tab', 'annual');
        $selectedYear = Request::input('year', date('Y'));
        $annualOverview = $this->reportRepo->getAnnualOverview($selectedYear);

        $selectedAsmYear = Request::input('asm_year', 'all');
        $selectedAsmGroup = Request::input('asm_group_id', '');
        $assessmentHistory = $this->reportRepo->getAssessmentHistory($selectedAsmYear, $selectedAsmGroup ?: null);

        $startDate = Request::input('start_date', date('Y-01-01'));
        $endDate = Request::input('end_date', date('Y-12-31'));
        $attGroup = Request::input('att_group_id', '');
        $attendanceIntervalReport = $this->reportRepo->getAttendanceIntervalReport($startDate, $endDate, $attGroup ?: null);

        $selectedStudentId = Request::input('student_id', $students[0]['id'] ?? '');
        $digest = $selectedStudentId ? $this->familyService->getWeeklyDigest($selectedStudentId, 'teacher') : null;

        View::render('teacher/reports', [
            'groups' => $groups,
            'students' => $students,
            'availableYears' => $availableYears,
            'activeTab' => $activeTab,
            'selectedYear' => $selectedYear,
            'annualOverview' => $annualOverview,
            'selectedAsmYear' => $selectedAsmYear,
            'selectedAsmGroup' => $selectedAsmGroup,
            'assessmentHistory' => $assessmentHistory,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'attGroup' => $attGroup,
            'attendanceIntervalReport' => $attendanceIntervalReport,
            'selectedStudentId' => $selectedStudentId,
            'digest' => $digest,
        ]);
    }

    public function settings(): void
    {
        $user = Session::user();
        $teacher = $this->userRepo->getTeacherProfile($user['id']);
        $allUsers = $this->userRepo->getAllUsers();
        $groups = $this->groupRepo->all();
        $students = $this->studentRepo->all('teacher');

        View::render('teacher/settings', [
            'teacher' => $teacher,
            'allUsers' => $allUsers,
            'groups' => $groups,
            'students' => $students,
        ]);
    }

    public function updateSettings(): void
    {
        $user = Session::user();
        $teacher = $this->userRepo->getTeacherProfile($user['id']);
        if ($teacher) {
            $title = Request::input('title', $teacher['title']);
            $phone = Request::input('phone', $teacher['phone']);
            $bio = Request::input('bio', $teacher['bio']);
            $this->userRepo->updateTeacherBio($teacher['id'], $title, $phone, $bio);
            Session::flash('success', 'Profilul didactic a fost actualizat cu succes!');
        }
        Response::redirect('/teacher/settings');
    }

    public function createStudentAccount(): void
    {
        $firstName = Request::input('first_name');
        $lastName = Request::input('last_name');
        $email = Request::input('email');
        $password = Request::input('password', 'parola123');
        $groupId = Request::input('group_id');
        $phone = Request::input('phone');

        if ($firstName && $lastName && $email) {
            $this->userRepo->createStudentUser([
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => $email,
                'password' => $password,
                'group_id' => $groupId,
                'phone' => $phone,
            ]);
            Session::flash('success', "Contul de elev pentru {$firstName} {$lastName} a fost creat cu succes!");
        }
        Response::redirect('/teacher/settings#accounts');
    }

    public function createParentAccount(): void
    {
        $firstName = Request::input('first_name');
        $lastName = Request::input('last_name');
        $email = Request::input('email');
        $password = Request::input('password', 'parola123');
        $studentId = Request::input('student_id');
        $phone = Request::input('phone');
        $relationship = Request::input('relationship', 'legal_guardian');

        if ($firstName && $lastName && $email) {
            $this->userRepo->createParentUser([
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => $email,
                'password' => $password,
                'student_id' => $studentId,
                'phone' => $phone,
                'relationship' => $relationship,
            ]);
            Session::flash('success', "Contul de părinte pentru {$firstName} {$lastName} a fost creat cu succes!");
        }
        Response::redirect('/teacher/settings#accounts');
    }

    public function resetPassword(): void
    {
        $userId = Request::input('user_id');
        $newPassword = Request::input('new_password', 'parola123');

        if ($userId && $newPassword) {
            $this->userRepo->resetPassword($userId, $newPassword);
            Session::flash('success', "Parola contului a fost resetată la: {$newPassword}");
        }
        Response::redirect('/teacher/settings#accounts');
    }

    public function deleteUser(): void
    {
        $userId = Request::input('user_id');
        if ($userId) {
            $this->userRepo->deleteUserAccount($userId);
            Session::flash('success', 'Contul a fost șters din sistem.');
        }
        Response::redirect('/teacher/settings#accounts');
    }

    public function toggleVacationMode(): void
    {
        $message = Request::input('vacation_message');
        $isVacation = \App\Support\Settings::toggleVacationMode(null, $message);
        if ($isVacation) {
            Session::flash('success', '🌴 Modul Vacanță a fost activat! Ședințele recurente și activitatea sunt în pauză.');
        } else {
            Session::flash('success', '▶️ Modul Vacanță a fost dezactivat. Cursurile s-au reluat!');
        }
        $returnUrl = Request::input('return_url', '/teacher/dashboard');
        Response::redirect($returnUrl);
    }

    public function addSchedule(string $id): void
    {
        $groupId = $id;
        $dayOfWeek = (int)Request::input('day_of_week', 1);
        $startTime = Request::input('start_time', '16:00');
        $durationMinutes = (int)Request::input('duration_minutes', 0);
        $endTime = Request::input('end_time');
        if ($durationMinutes > 0) {
            $startTs = strtotime("2020-01-01 $startTime");
            $endTime = date('H:i', $startTs + ($durationMinutes * 60));
        } elseif (!$endTime) {
            $endTime = '17:30';
        }

        $room = Request::input('room_or_link', 'Cabinet didactic');
        $returnUrl = Request::input('return_url', "/teacher/groups/$groupId");

        if ($groupId && $startTime && $endTime) {
            $this->groupRepo->addSchedule($groupId, $dayOfWeek, $startTime, $endTime, $room);
            $this->groupRepo->generateRecurringLessons($groupId, 8);
            Session::flash('success', 'Programul săptămânal recurent a fost salvat și ședințele au fost create automat!');
        }
        Response::redirect($returnUrl);
    }

    public function deleteSchedule(string $id, string $scheduleId): void
    {
        $groupId = $id;
        if ($scheduleId) {
            $this->groupRepo->deleteSchedule($scheduleId);
            Session::flash('success', 'Intervalul de orar a fost șters din grupă.');
        }
        Response::redirect("/teacher/groups/$groupId");
    }

    public function generateRecurringLessons(string $id): void
    {
        $groupId = $id;
        $weeks = (int)Request::input('weeks', 4);
        $count = $this->groupRepo->generateRecurringLessons($groupId, $weeks);
        Session::flash('success', "Au fost generate {$count} ședințe recurente pentru următoarele {$weeks} săptămâni!");
        Response::redirect("/teacher/groups/$groupId");
    }
}
