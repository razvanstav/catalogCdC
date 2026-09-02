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

            // Find or associate workspace for this teacher
            $ws = \App\Support\Database::queryOne("SELECT id, name FROM workspaces WHERE owner_id = ? LIMIT 1", [$user['id']]);
            if (!$ws) {
                $firstWs = \App\Support\Database::queryOne("SELECT id, name FROM workspaces WHERE owner_id = 'usr_teacher_radu' OR id = 'ws_radu_teodorescu' LIMIT 1");
                if ($firstWs && ($user['id'] === 'usr_teacher_radu' || ($user['username'] ?? '') === 'profesor')) {
                    $ws = $firstWs;
                    \App\Support\Database::execute("UPDATE workspaces SET owner_id = ? WHERE id = ?", [$user['id'], $ws['id']]);
                } else {
                    $wsId = 'ws_' . bin2hex(random_bytes(6));
                    $wsName = 'Cabinet Didactic — Prof. ' . $user['first_name'] . ' ' . $user['last_name'];
                    $now = date('Y-m-d H:i:s');
                    \App\Support\Database::execute(
                        "INSERT INTO workspaces (id, name, owner_id, created_at, updated_at) VALUES (?, ?, ?, ?, ?)",
                        [$wsId, $wsName, $user['id'], $now, $now]
                    );
                    $ws = ['id' => $wsId, 'name' => $wsName];
                }
            }
            $sessionData['workspace_id'] = $ws['id'] ?? null;
            $sessionData['workspace_name'] = $ws['name'] ?? 'Cabinet Didactic';
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
