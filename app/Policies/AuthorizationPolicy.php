<?php

namespace App\Policies;

use App\Support\Session;
use App\Repositories\StudentRepository;
use App\Repositories\GroupRepository;

class AuthorizationPolicy
{
    public static function canViewPrivateNotes(): bool
    {
        return Session::role() === 'teacher';
    }

    public static function canAccessStudent(string $studentId): bool
    {
        $role = Session::role();
        $user = Session::user();

        if ($role === 'teacher') {
            return true;
        }

        if ($role === 'parent') {
            $guardianId = $user['guardian_id'] ?? null;
            if (!$guardianId) return false;

            $studentRepo = new StudentRepository();
            $allowedChildren = $studentRepo->getStudentsForGuardian($guardianId, 'parent');
            foreach ($allowedChildren as $c) {
                if ($c['id'] === $studentId) return true;
            }
            return false;
        }

        if ($role === 'student') {
            $ownStudentId = $user['student_id'] ?? null;
            return $ownStudentId === $studentId;
        }

        return false;
    }

    public static function canAccessGroup(string $groupId): bool
    {
        $role = Session::role();
        if ($role === 'teacher') {
            return true;
        }

        $activeStudentId = Session::activeStudentId();
        if (!$activeStudentId) return false;

        $groupRepo = new GroupRepository();
        $enrolledGroups = $groupRepo->getGroupsForStudent($activeStudentId);
        foreach ($enrolledGroups as $g) {
            if ($g['id'] === $groupId) return true;
        }
        return false;
    }
}
