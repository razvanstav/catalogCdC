<?php

use App\Support\Router;
use App\Support\Response;
use App\Support\Session;

// Home / Landing redirects
Router::get('/', function () {
    if (Session::user()) {
        $role = Session::role();
        Response::redirect('/' . $role . '/dashboard');
    }
    Response::redirect('/login');
});

// Authentication & Demo Switcher
Router::get('/login', 'AuthController@showLogin');
Router::post('/login', 'AuthController@login', ['csrf']);
Router::get('/demo/{role}', 'AuthController@demoSwitch');
Router::get('/demo/{role}/{studentId}', 'AuthController@demoSwitch');
Router::get('/logout', 'AuthController@logout');

// ==========================================
// TEACHER PORTAL (Protected by auth + teacher role)
// ==========================================
Router::get('/teacher/dashboard', 'TeacherController@dashboard', ['teacher']);
Router::get('/teacher/groups', 'TeacherController@groups', ['teacher']);
Router::post('/teacher/groups', 'TeacherController@createGroup', ['teacher', 'csrf']);
Router::get('/teacher/groups/{id}', 'TeacherController@groupDetail', ['teacher']);
Router::post('/teacher/groups/{id}/edit', 'TeacherController@updateGroup', ['teacher', 'csrf']);
Router::post('/teacher/groups/{id}/enroll', 'TeacherController@enrollStudent', ['teacher', 'csrf']);
Router::post('/teacher/groups/{id}/unenroll', 'TeacherController@unenrollStudent', ['teacher', 'csrf']);

Router::get('/teacher/students', 'TeacherController@students', ['teacher']);
Router::post('/teacher/students', 'TeacherController@createStudent', ['teacher', 'csrf']);
Router::get('/teacher/students/{id}', 'TeacherController@studentDetail', ['teacher']);
Router::post('/teacher/students/{id}/notes', 'TeacherController@updateStudentNotes', ['teacher', 'csrf']);
Router::post('/teacher/students/{id}/toggle-paid', 'TeacherController@togglePaid', ['teacher', 'csrf']);

Router::get('/teacher/attendance', 'TeacherController@attendance', ['teacher']);
Router::post('/teacher/attendance', 'TeacherController@saveAttendance', ['teacher', 'csrf']);
Router::post('/teacher/attendance/add-guest', 'TeacherController@addGuestToLesson', ['teacher', 'csrf']);
Router::post('/teacher/attendance/remove-guest', 'TeacherController@removeGuestFromLesson', ['teacher', 'csrf']);

Router::get('/teacher/lessons', 'TeacherController@lessons', ['teacher']);
Router::post('/teacher/lessons', 'TeacherController@createLesson', ['teacher', 'csrf']);

Router::get('/teacher/assignments', 'TeacherController@assignments', ['teacher']);
Router::post('/teacher/assignments', 'TeacherController@createAssignment', ['teacher', 'csrf']);
Router::post('/teacher/materials', 'TeacherController@createMaterial', ['teacher', 'csrf']);

Router::get('/teacher/assessments', 'TeacherController@assessments', ['teacher']);
Router::post('/teacher/assessments', 'TeacherController@createAssessment', ['teacher', 'csrf']);
Router::post('/teacher/assessments/results', 'TeacherController@saveAssessmentResults', ['teacher', 'csrf']);

Router::get('/teacher/feedback', 'TeacherController@feedback', ['teacher']);
Router::post('/teacher/feedback', 'TeacherController@createFeedback', ['teacher', 'csrf']);

Router::get('/teacher/announcements', 'TeacherController@announcements', ['teacher']);
Router::post('/teacher/announcements', 'TeacherController@createAnnouncement', ['teacher', 'csrf']);

Router::get('/teacher/conversations', 'TeacherController@conversations', ['teacher']);
Router::post('/teacher/conversations/message', 'TeacherController@sendMessage', ['teacher', 'csrf']);
Router::post('/teacher/conversations/start', 'TeacherController@startConversation', ['teacher', 'csrf']);

Router::get('/teacher/calendar', 'TeacherController@calendar', ['teacher']);
Router::post('/teacher/calendar/schedule', 'TeacherController@addScheduleFromCalendar', ['teacher', 'csrf']);
Router::get('/teacher/reports', 'TeacherController@reports', ['teacher']);
Router::get('/teacher/settings', 'TeacherController@settings', ['teacher']);
Router::post('/teacher/settings', 'TeacherController@updateSettings', ['teacher', 'csrf']);
Router::post('/teacher/settings/toggle-vacation', 'TeacherController@toggleVacationMode', ['teacher', 'csrf']);
Router::post('/teacher/settings/create-student-account', 'TeacherController@createStudentAccount', ['teacher', 'csrf']);
Router::post('/teacher/settings/create-parent-account', 'TeacherController@createParentAccount', ['teacher', 'csrf']);
Router::post('/teacher/settings/reset-password', 'TeacherController@resetPassword', ['teacher', 'csrf']);
Router::post('/teacher/settings/delete-user', 'TeacherController@deleteUser', ['teacher', 'csrf']);
Router::post('/teacher/groups/{id}/schedules', 'TeacherController@addSchedule', ['teacher', 'csrf']);
Router::post('/teacher/groups/{id}/schedules/{scheduleId}/delete', 'TeacherController@deleteSchedule', ['teacher', 'csrf']);
Router::post('/teacher/groups/{id}/generate-lessons', 'TeacherController@generateRecurringLessons', ['teacher', 'csrf']);

// ==========================================
// PARENT PORTAL (Protected by auth + parent role)
// ==========================================
Router::get('/parent/dashboard', 'ParentController@dashboard', ['parent']);
Router::get('/parent/child/{studentId}', 'ParentController@switchChild', ['parent']);
Router::get('/parent/timetable', 'ParentController@timetable', ['parent']);
Router::get('/parent/attendance', 'ParentController@attendance', ['parent']);
Router::get('/parent/assignments', 'ParentController@assignments', ['parent']);
Router::get('/parent/results', 'ParentController@results', ['parent']);
Router::get('/parent/feedback', 'ParentController@feedback', ['parent']);
Router::get('/parent/goals', 'ParentController@goals', ['parent']);
Router::get('/parent/announcements', 'ParentController@announcements', ['parent']);
Router::get('/parent/conversations', 'ParentController@conversations', ['parent']);
Router::post('/parent/conversations/message', 'ParentController@sendMessage', ['parent', 'csrf']);

// ==========================================
// STUDENT PORTAL (Protected by auth + student role)
// ==========================================
Router::get('/student/dashboard', 'StudentController@dashboard', ['student']);
Router::get('/student/timetable', 'StudentController@timetable', ['student']);
Router::get('/student/assignments', 'StudentController@assignments', ['student']);
Router::post('/student/assignments/{id}/submit', 'StudentController@submitAssignment', ['student', 'csrf']);
Router::get('/student/materials', 'StudentController@materials', ['student']);
Router::get('/student/results', 'StudentController@results', ['student']);
Router::get('/student/feedback', 'StudentController@feedback', ['student']);
Router::get('/student/goals', 'StudentController@goals', ['student']);
Router::get('/student/goals/toggle/{id}', 'StudentController@toggleGoal', ['student']);
Router::post('/student/goals', 'StudentController@addGoal', ['student', 'csrf']);
Router::get('/student/announcements', 'StudentController@announcements', ['student']);
