<?php

namespace App\Controllers;

use App\Repositories\StudentRepository;
use App\Repositories\GroupRepository;
use App\Repositories\AttendanceRepository;
use App\Repositories\AssignmentRepository;
use App\Repositories\AssessmentRepository;
use App\Repositories\FeedbackRepository;
use App\Repositories\AnnouncementRepository;
use App\Repositories\GoalRepository;
use App\Repositories\ConversationRepository;
use App\Policies\AuthorizationPolicy;
use App\Support\Request;
use App\Support\Response;
use App\Support\Session;
use App\Support\View;

class StudentController
{
    private StudentRepository $studentRepo;
    private GroupRepository $groupRepo;
    private AttendanceRepository $attendanceRepo;
    private AssignmentRepository $assignmentRepo;
    private AssessmentRepository $assessmentRepo;
    private FeedbackRepository $feedbackRepo;
    private AnnouncementRepository $announcementRepo;
    private GoalRepository $goalRepo;
    private ConversationRepository $conversationRepo;

    public function __construct()
    {
        $this->studentRepo = new StudentRepository();
        $this->groupRepo = new GroupRepository();
        $this->attendanceRepo = new AttendanceRepository();
        $this->assignmentRepo = new AssignmentRepository();
        $this->assessmentRepo = new AssessmentRepository();
        $this->feedbackRepo = new FeedbackRepository();
        $this->announcementRepo = new AnnouncementRepository();
        $this->goalRepo = new GoalRepository();
        $this->conversationRepo = new ConversationRepository();
    }

    private function getStudent(): ?array
    {
        $user = Session::user();
        $studentId = $user['student_id'] ?? null;
        if (!$studentId) return null;

        return $this->studentRepo->findById($studentId, 'student');
    }

    public function dashboard(): void
    {
        $student = $this->getStudent();
        if (!$student) Response::notFound();

        $groups = $this->groupRepo->getGroupsForStudent($student['id']);
        $assignments = $this->assignmentRepo->getForStudent($student['id']);
        $feedbacks = $this->feedbackRepo->getForStudent($student['id']);
        $goals = $this->goalRepo->getForStudent($student['id']);
        $lessons = $this->attendanceRepo->getLessons();
        $nextLesson = $lessons[0] ?? null;

        View::render('student/dashboard', [
            'student' => $student,
            'groups' => $groups,
            'assignments' => $assignments,
            'feedbacks' => $feedbacks,
            'goals' => $goals,
            'nextLesson' => $nextLesson,
        ]);
    }

    public function timetable(): void
    {
        $student = $this->getStudent();
        if (!$student) Response::notFound();

        $groups = $this->groupRepo->getGroupsForStudent($student['id']);
        $schedules = $this->groupRepo->getSchedules();

        $groupIds = array_flip(array_column($groups, 'id'));
        $studentSchedules = array_filter($schedules, fn($s) => isset($groupIds[$s['group_id']]));

        View::render('student/timetable', [
            'student' => $student,
            'groups' => $groups,
            'schedules' => $studentSchedules,
        ]);
    }

    public function assignments(): void
    {
        $student = $this->getStudent();
        if (!$student) Response::notFound();

        $assignments = $this->assignmentRepo->getForStudentWithSubmissions($student['id']);
        View::render('student/assignments', [
            'student' => $student,
            'assignments' => $assignments,
        ]);
    }

    public function submitAssignment(string $assignmentId): void
    {
        $student = $this->getStudent();
        if (!$student) Response::notFound();

        $text = Request::input('submission_text', '');
        $fileUrl = null;
        $fileName = null;
        $fileType = null;

        if (!empty($_FILES['solution_file']['tmp_name']) && is_uploaded_file($_FILES['solution_file']['tmp_name'])) {
            $uploadDir = APP_ROOT . '/public/uploads/submissions/';
            if (!is_dir($uploadDir)) {
                @mkdir($uploadDir, 0777, true);
            }
            $originalName = $_FILES['solution_file']['name'];
            $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
            $safeName = 'sub_' . bin2hex(random_bytes(6)) . '.' . $ext;
            if (move_uploaded_file($_FILES['solution_file']['tmp_name'], $uploadDir . $safeName)) {
                $fileUrl = '/uploads/submissions/' . $safeName;
                $fileName = $originalName;
                $fileType = $ext;
            }
        }

        if ($text || $fileUrl) {
            $this->assignmentRepo->submitAssignment($assignmentId, $student['id'], $text, $fileUrl, $fileName, $fileType);
            Session::flash('success', 'Rezolvarea ta a fost trimisă cu succes profesoarei!');
        } else {
            Session::flash('error', 'Te rugăm să atașezi un fișier sau să scrii rezolvarea temei.');
        }

        Response::redirect('/student/assignments');
    }

    public function materials(): void
    {
        $student = $this->getStudent();
        if (!$student) Response::notFound();

        $materials = $this->assignmentRepo->getMaterialsForStudent($student['id']);
        View::render('student/materials', [
            'student' => $student,
            'materials' => $materials,
        ]);
    }

    public function results(): void
    {
        $student = $this->getStudent();
        if (!$student) Response::notFound();

        // Strict role = 'student' (zero private teacher notes)
        $results = $this->assessmentRepo->getResultsForStudent($student['id'], 'student');
        View::render('student/results', [
            'student' => $student,
            'results' => $results,
        ]);
    }

    public function feedback(): void
    {
        $student = $this->getStudent();
        if (!$student) Response::notFound();

        $feedbacks = $this->feedbackRepo->getForStudent($student['id']);
        View::render('student/feedback', [
            'student' => $student,
            'feedbacks' => $feedbacks,
        ]);
    }

    public function goals(): void
    {
        $student = $this->getStudent();
        if (!$student) Response::notFound();

        $goals = $this->goalRepo->getForStudent($student['id']);
        View::render('student/goals', [
            'student' => $student,
            'goals' => $goals,
        ]);
    }

    public function toggleGoal(string $id): void
    {
        $this->goalRepo->toggle($id);
        Session::flash('success', 'Obiectivul a fost actualizat!');
        Response::redirect('/student/goals');
    }

    public function addGoal(): void
    {
        $student = $this->getStudent();
        $title = Request::input('title');
        $date = Request::input('target_date');

        if ($student && $title) {
            $this->goalRepo->create($student['id'], $title, $date ?: null);
            Session::flash('success', 'Obiectivul personal a fost adăugat!');
        }
        Response::redirect('/student/goals');
    }

    public function announcements(): void
    {
        $student = $this->getStudent();
        if (!$student) Response::notFound();

        $announcements = $this->announcementRepo->getForStudent($student['id']);
        View::render('student/announcements', [
            'student' => $student,
            'announcements' => $announcements,
        ]);
    }

    public function attendance(): void
    {
        $student = $this->getStudent();
        if (!$student) Response::notFound();

        $records = $this->attendanceRepo->getForStudent($student['id']);
        View::render('student/attendance', [
            'student' => $student,
            'records' => $records,
        ]);
    }

    public function conversations(): void
    {
        $student = $this->getStudent();
        if (!$student) Response::notFound();

        $conversations = $this->conversationRepo->getOrCreateForStudent($student['id']);
        $activeConv = $conversations[0] ?? null;

        View::render('student/conversations', [
            'student' => $student,
            'conversations' => $conversations,
            'activeConv' => $activeConv,
        ]);
    }

    public function sendMessage(): void
    {
        $convId = Request::input('conversation_id');
        $content = Request::input('content');
        $user = Session::user();

        if ($convId && $content) {
            $this->conversationRepo->sendMessage($convId, 'student', $user['id'], $content);
        }
        Response::redirect('/student/conversations');
    }
}
