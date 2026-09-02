<?php

namespace Tests;

use App\Repositories\UserRepository;
use App\Repositories\StudentRepository;
use App\Repositories\AssessmentRepository;
use App\Policies\AuthorizationPolicy;
use App\Services\AuthService;
use App\Support\Csrf;
use App\Support\Session;
use App\Support\Database;

class SecurityAndReBACTest
{
    private AuthService $authService;
    private StudentRepository $studentRepo;
    private AssessmentRepository $assessmentRepo;

    private ?\PDO $testPdo = null;

    public function __construct()
    {
        $this->testPdo = Database::createInMemoryTestPdo();
        Database::setPdo($this->testPdo);
        $this->authService = new AuthService();
        $this->studentRepo = new StudentRepository();
        $this->assessmentRepo = new AssessmentRepository();
    }

    public function __destruct()
    {
        Database::setPdo(null);
    }

    public function runAll(): array
    {
        $results = [];

        $results[] = $this->testInvalidLoginRejection();
        $results[] = $this->testCsrfTokenValidation();
        $results[] = $this->testTeacherPrivateNotesExclusionForParents();
        $results[] = $this->testTeacherPrivateNotesExclusionForStudents();
        $results[] = $this->testGuardianReBACIsolation();
        $results[] = $this->testStudentReBACIsolation();
        $results[] = $this->testSqlInjectionSafety();

        return array_filter($results);
    }

    public function testInvalidLoginRejection(): array
    {
        $res = $this->authService->login('inexistent@test.ro', 'parolagresita');
        return [
            'name' => '1. Respingere autentificare cu credențiale invalide',
            'passed' => $res === false,
            'details' => $res === false ? 'Autentificarea invalidă a fost respinsă corect.' : 'EROARE: S-a acceptat login invalid!',
        ];
    }

    public function testCsrfTokenValidation(): array
    {
        $validToken = Csrf::token();
        $isValid = Csrf::validate($validToken);
        $isFakeValid = Csrf::validate('fake_csrf_attack_token_123');

        return [
            'name' => '2. Protecție CSRF și validare token sesiune',
            'passed' => ($isValid === true && $isFakeValid === false),
            'details' => 'Token-ul legitim a fost validat, atacul CSRF a fost respins cu succes.',
        ];
    }

    public function testTeacherPrivateNotesExclusionForParents(): array
    {
        // Fetch as teacher vs fetch as parent
        $teacherView = $this->studentRepo->findById('stu_matei_popescu', 'teacher');
        $parentView = $this->studentRepo->findById('stu_matei_popescu', 'parent');

        $teacherHasNotes = !empty($teacherView['private_notes']);
        $parentHasNotes = !empty($parentView['private_notes']);

        // Assessment results private notes
        $teacherResults = $this->assessmentRepo->getResultsForStudent('stu_matei_popescu', 'teacher');
        $parentResults = $this->assessmentRepo->getResultsForStudent('stu_matei_popescu', 'parent');

        $teacherHasResultNotes = false;
        foreach ($teacherResults as $tr) {
            if (!empty($tr['private_teacher_notes'])) {
                $teacherHasResultNotes = true;
                break;
            }
        }
        $parentHasResultNotes = false;
        foreach ($parentResults as $pr) {
            if (!empty($pr['private_teacher_notes'])) {
                $parentHasResultNotes = true;
                break;
            }
        }

        $passed = ($teacherHasNotes && !$parentHasNotes && $teacherHasResultNotes && !$parentHasResultNotes);

        return [
            'name' => '3. Izolarea Notițelor Private față de Părinți (Zero Leakage)',
            'passed' => $passed,
            'details' => $passed 
                ? 'Profesorul vede notițele interne; părintele primește strict NULL în proiecția SQL.' 
                : 'EROARE: Notițele private au fost expuse părintelui!',
        ];
    }

    public function testTeacherPrivateNotesExclusionForStudents(): array
    {
        $studentView = $this->studentRepo->findById('stu_matei_popescu', 'student');
        $studentResults = $this->assessmentRepo->getResultsForStudent('stu_matei_popescu', 'student');

        $studentHasResultNotes = false;
        foreach ($studentResults as $sr) {
            if (!empty($sr['private_teacher_notes'])) {
                $studentHasResultNotes = true;
                break;
            }
        }

        $passed = empty($studentView['private_notes']) && !$studentHasResultNotes;

        return [
            'name' => '4. Izolarea Notițelor Private față de Elevi',
            'passed' => $passed,
            'details' => $passed 
                ? 'Elevul nu are acces la notițele interne sau la comentariile confidențiale ale profesorului.' 
                : 'EROARE: Notițele private au fost expuse elevului!',
        ];
    }

    public function testGuardianReBACIsolation(): array
    {
        // Login as Radu Popescu (Father of Matei and Sofia)
        $this->authService->loginAsDemo('parent', 'stu_matei_popescu');

        $canAccessMatei = AuthorizationPolicy::canAccessStudent('stu_matei_popescu');
        $canAccessSofia = AuthorizationPolicy::canAccessStudent('stu_sofia_popescu');
        // Andrei Ionescu is NOT his child
        $canAccessAndrei = AuthorizationPolicy::canAccessStudent('stu_andrei_ionescu');

        $passed = ($canAccessMatei && $canAccessSofia && !$canAccessAndrei);

        return [
            'name' => '5. Regula ReBAC Tutore – Elev (IDOR Protection)',
            'passed' => $passed,
            'details' => $passed 
                ? 'Părintele are acces la copiii săi (Matei, Sofia) și este blocat 403 la alți elevi (Andrei).' 
                : 'EROARE: Părintele a putut accesa datele altui elev neasociat!',
        ];
    }

    public function testStudentReBACIsolation(): array
    {
        // Login as Matei Popescu
        $this->authService->loginAsDemo('student');

        $canAccessSelf = AuthorizationPolicy::canAccessStudent('stu_matei_popescu');
        $canAccessOther = AuthorizationPolicy::canAccessStudent('stu_andrei_ionescu');
        $canViewPrivateNotes = AuthorizationPolicy::canViewPrivateNotes();

        $passed = ($canAccessSelf && !$canAccessOther && !$canViewPrivateNotes);

        return [
            'name' => '6. Regula ReBAC Elev (Strict Self-Access)',
            'passed' => $passed,
            'details' => $passed 
                ? 'Elevul își poate accesa doar propriul cont și nu poate deschide dosarul altui coleg.' 
                : 'EROARE: Elevul a putut accesa dosarul altui elev!',
        ];
    }

    public function testSqlInjectionSafety(): array
    {
        $maliciousInput = "stu_matei_popescu' OR '1'='1";
        $result = $this->studentRepo->findById($maliciousInput, 'student');

        $passed = ($result === null);

        return [
            'name' => '7. Imunitate la SQL Injection prin Prepared Statements',
            'passed' => $passed,
            'details' => $passed 
                ? 'Interogările SQL parametrizate cu PDO neutralizează tentativele de injecție SQL.' 
                : 'EROARE: S-a detectat SQL injection!',
        ];
    }
}
