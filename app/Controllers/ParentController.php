<?php

namespace App\Controllers;

use App\Repositories\StudentRepository;
use App\Repositories\GroupRepository;
use App\Repositories\AttendanceRepository;
use App\Repositories\AssignmentRepository;
use App\Repositories\AssessmentRepository;
use App\Repositories\FeedbackRepository;
use App\Repositories\AnnouncementRepository;
use App\Repositories\ConversationRepository;
use App\Repositories\GoalRepository;
use App\Services\FamilyService;
use App\Policies\AuthorizationPolicy;
use App\Support\Request;
use App\Support\Response;
use App\Support\Session;
use App\Support\View;

class ParentController
{
    private StudentRepository $studentRepo;
    private GroupRepository $groupRepo;
    private AttendanceRepository $attendanceRepo;
    private AssignmentRepository $assignmentRepo;
    private AssessmentRepository $assessmentRepo;
    private FeedbackRepository $feedbackRepo;
    private AnnouncementRepository $announcementRepo;
    private ConversationRepository $conversationRepo;
    private GoalRepository $goalRepo;
    private FamilyService $familyService;

    public function __construct()
    {
        $this->studentRepo = new StudentRepository();
        $this->groupRepo = new GroupRepository();
        $this->attendanceRepo = new AttendanceRepository();
        $this->assignmentRepo = new AssignmentRepository();
        $this->assessmentRepo = new AssessmentRepository();
        $this->feedbackRepo = new FeedbackRepository();
        $this->announcementRepo = new AnnouncementRepository();
        $this->conversationRepo = new ConversationRepository();
        $this->goalRepo = new GoalRepository();
        $this->familyService = new FamilyService();
    }

    private function getActiveChild(): ?array
    {
        $user = Session::user();
        $guardianId = $user['guardian_id'] ?? null;
        if (!$guardianId) return null;

        $children = $this->studentRepo->getStudentsForGuardian($guardianId, 'parent');
        if (empty($children)) return null;

        $activeId = Session::activeStudentId();
        foreach ($children as $c) {
            if ($c['id'] === $activeId) return $c;
        }

        // Default to first child
        Session::setActiveStudentId($children[0]['id']);
        return $children[0];
    }

    public function switchChild(string $studentId): void
    {
        if (!AuthorizationPolicy::canAccessStudent($studentId)) {
            Response::forbidden('Nu aveți permisiunea de a comuta pe acest elev.');
        }

        Session::setActiveStudentId($studentId);
        Session::flash('success', 'Profilul activ al copilului a fost comutat.');
        Response::redirect('/parent/dashboard');
    }

    public function dashboard(): void
    {
        $child = $this->getActiveChild();
        if (!$child) {
            View::render('parent/no_child', [], 'layouts/main');
            return;
        }

        $digest = $this->familyService->getWeeklyDigest($child['id'], 'parent');
        $groups = $this->groupRepo->getGroupsForStudent($child['id']);

        View::render('parent/dashboard', [
            'child' => $child,
            'digest' => $digest,
            'groups' => $groups,
        ]);
    }

    public function timetable(): void
    {
        $child = $this->getActiveChild();
        if (!$child) Response::notFound();

        $groups = $this->groupRepo->getGroupsForStudent($child['id']);
        $schedules = $this->groupRepo->getSchedules();

        $groupIds = array_flip(array_column($groups, 'id'));
        $childSchedules = array_filter($schedules, fn($s) => isset($groupIds[$s['group_id']]));

        View::render('parent/timetable', [
            'child' => $child,
            'groups' => $groups,
            'schedules' => $childSchedules,
        ]);
    }

    public function attendance(): void
    {
        $child = $this->getActiveChild();
        if (!$child) Response::notFound();

        $records = $this->attendanceRepo->getForStudent($child['id']);
        View::render('parent/attendance', [
            'child' => $child,
            'records' => $records,
        ]);
    }

    public function assignments(): void
    {
        $child = $this->getActiveChild();
        if (!$child) Response::notFound();

        $assignments = $this->assignmentRepo->getForStudentWithSubmissions($child['id']);
        View::render('parent/assignments', [
            'child' => $child,
            'assignments' => $assignments,
        ]);
    }

    public function results(): void
    {
        $child = $this->getActiveChild();
        if (!$child) Response::notFound();

        // Strict role = 'parent' (no private notes)
        $results = $this->assessmentRepo->getResultsForStudent($child['id'], 'parent');
        View::render('parent/results', [
            'child' => $child,
            'results' => $results,
        ]);
    }

    public function feedback(): void
    {
        $child = $this->getActiveChild();
        if (!$child) Response::notFound();

        $feedbacks = $this->feedbackRepo->getForStudent($child['id']);
        View::render('parent/feedback', [
            'child' => $child,
            'feedbacks' => $feedbacks,
        ]);
    }

    public function goals(): void
    {
        $child = $this->getActiveChild();
        if (!$child) Response::notFound();

        $goals = $this->goalRepo->getForStudent($child['id']);
        View::render('parent/goals', [
            'child' => $child,
            'goals' => $goals,
        ]);
    }

    public function announcements(): void
    {
        $child = $this->getActiveChild();
        if (!$child) Response::notFound();

        $announcements = $this->announcementRepo->getForStudent($child['id']);
        View::render('parent/announcements', [
            'child' => $child,
            'announcements' => $announcements,
        ]);
    }

    public function conversations(): void
    {
        $user = Session::user();
        $guardianId = $user['guardian_id'] ?? null;
        $conversations = $this->conversationRepo->getForGuardian($guardianId);
        $activeConv = $conversations[0] ?? null;

        View::render('parent/conversations', [
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
            $this->conversationRepo->sendMessage($convId, 'parent', $user['id'], $content);
        }
        Response::redirect('/parent/conversations');
    }

    public function materials(): void
    {
        $child = $this->getActiveChild();
        if (!$child) Response::notFound();

        $materials = $this->assignmentRepo->getMaterialsForStudent($child['id']);
        View::render('parent/materials', [
            'child' => $child,
            'materials' => $materials,
        ]);
    }
}
