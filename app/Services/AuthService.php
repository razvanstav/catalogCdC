<?php

namespace App\Services;

use App\Repositories\UserRepository;
use App\Repositories\StudentRepository;
use App\Support\Session;

class AuthService
{
    private UserRepository $userRepo;
    private StudentRepository $studentRepo;

    public function __construct()
    {
        $this->userRepo = new UserRepository();
        $this->studentRepo = new StudentRepository();
    }

    public function login(string $email, string $password): bool
    {
        $user = $this->userRepo->findByEmail($email);
        if (!$user || !password_verify($password, $user['password_hash'])) {
            return false;
        }

        $this->establishSession($user);
        return true;
    }

    public function loginAsDemo(string $role, ?string $studentId = null): void
    {
        $demoEmails = [
            'teacher' => 'prof.radu@indrumar.ro',
            'parent' => 'radu.popescu@familie.ro',
            'student' => 'matei.popescu@elev.ro',
        ];

        $email = $demoEmails[$role] ?? 'prof.radu@indrumar.ro';
        $user = $this->userRepo->findByEmail($email);

        if ($user) {
            $this->establishSession($user, $studentId);
        }
    }

    public function establishSession(array $user, ?string $preferredStudentId = null): void
    {
        Session::regenerate();

        $sessionData = [
            'id' => $user['id'],
            'email' => $user['email'],
            'role' => $user['role'],
            'first_name' => $user['first_name'],
            'last_name' => $user['last_name'],
            'phone' => $user['phone'] ?? null,
        ];

        if ($user['role'] === 'teacher') {
            $prof = $this->userRepo->getTeacherProfile($user['id']);
            $sessionData['teacher_id'] = $prof['id'] ?? null;
            $sessionData['title'] = $prof['title'] ?? 'Profesor';
        } elseif ($user['role'] === 'parent') {
            $guardian = $this->userRepo->getGuardianProfile($user['id']);
            $sessionData['guardian_id'] = $guardian['id'] ?? null;

            // Load associated children
            if ($guardian) {
                $children = $this->studentRepo->getStudentsForGuardian($guardian['id'], 'parent');
                $sessionData['children'] = $children;
                $activeId = $preferredStudentId ?: ($children[0]['id'] ?? null);
                $sessionData['active_student_id'] = $activeId;
                Session::setActiveStudentId($activeId);
            }
        } elseif ($user['role'] === 'student') {
            $student = $this->userRepo->getStudentProfile($user['id']);
            $sessionData['student_id'] = $student['id'] ?? null;
            $sessionData['active_student_id'] = $student['id'] ?? null;
            Session::setActiveStudentId($student['id'] ?? '');
        }

        Session::setUser($sessionData);
    }

    public function logout(): void
    {
        Session::destroy();
    }
}
