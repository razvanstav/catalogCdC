<?php

namespace App\Services;

use App\Repositories\StudentRepository;
use App\Repositories\AttendanceRepository;
use App\Repositories\AssessmentRepository;
use App\Repositories\AssignmentRepository;
use App\Repositories\FeedbackRepository;
use App\Repositories\GoalRepository;

class FamilyService
{
    private StudentRepository $studentRepo;
    private AttendanceRepository $attendanceRepo;
    private AssessmentRepository $assessmentRepo;
    private AssignmentRepository $assignmentRepo;
    private FeedbackRepository $feedbackRepo;
    private GoalRepository $goalRepo;

    public function __construct()
    {
        $this->studentRepo = new StudentRepository();
        $this->attendanceRepo = new AttendanceRepository();
        $this->assessmentRepo = new AssessmentRepository();
        $this->assignmentRepo = new AssignmentRepository();
        $this->feedbackRepo = new FeedbackRepository();
        $this->goalRepo = new GoalRepository();
    }

    public function getWeeklyDigest(string $studentId, string $role = 'parent'): ?array
    {
        $student = $this->studentRepo->findById($studentId, $role);
        if (!$student) return null;

        $attendance = $this->attendanceRepo->getForStudent($studentId);
        $totalAtt = count($attendance);
        $presentCount = count(array_filter($attendance, fn($a) => $a['status'] === 'present'));
        $attRate = $totalAtt > 0 ? (int)round(($presentCount / $totalAtt) * 100) : 100;

        $feedbacks = $this->feedbackRepo->getForStudent($studentId);
        $results = $this->assessmentRepo->getResultsForStudent($studentId, $role);
        $assignments = $this->assignmentRepo->getForStudent($studentId);
        $goals = $this->goalRepo->getForStudent($studentId);
        $completedGoals = count(array_filter($goals, fn($g) => $g['is_completed'] == 1));

        return [
            'student' => $student,
            'attendanceRate' => $attRate,
            'recentFeedbacks' => array_slice($feedbacks, 0, 3),
            'recentResults' => array_slice($results, 0, 4),
            'upcomingAssignments' => array_slice($assignments, 0, 3),
            'completedGoalsCount' => $completedGoals,
            'totalGoalsCount' => count($goals),
        ];
    }
}
