<?php

namespace Database\Seeds;

use PDO;

class DemoSeeder
{
    public static function seed(PDO $pdo): void
    {
        $now = date('Y-m-d H:i:s');
        $hash = password_hash('parola123', PASSWORD_DEFAULT);

        // 1. Workspace
        $stmt = $pdo->prepare("INSERT OR IGNORE INTO workspaces (id, name, owner_id, created_at, updated_at) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute(['ws_radu_teodorescu', 'Cabinet Didactic — Prof. Radu Teodorescu', 'usr_teacher_radu', $now, $now]);

        // 2. Users
        $users = [
            ['usr_teacher_radu', 'prof.radu@indrumar.ro', $hash, 'teacher', 'Radu', 'Teodorescu', '0722111222', null, 1, $now, $now],
            ['usr_guardian_radu_popescu', 'radu.popescu@familie.ro', $hash, 'parent', 'Radu', 'Popescu', '0722334455', null, 1, $now, $now],
            ['usr_guardian_cristina_ionescu', 'cristina.ionescu@familie.ro', $hash, 'parent', 'Cristina', 'Ionescu', '0722445566', null, 1, $now, $now],
            ['usr_student_matei', 'matei.popescu@elev.ro', $hash, 'student', 'Matei', 'Popescu', null, null, 1, $now, $now],
            ['usr_student_sofia', 'sofia.popescu@elev.ro', $hash, 'student', 'Sofia', 'Popescu', null, null, 1, $now, $now],
            ['usr_student_andrei', 'andrei.ionescu@elev.ro', $hash, 'student', 'Andrei', 'Ionescu', null, null, 1, $now, $now],
        ];
        $stmt = $pdo->prepare("INSERT OR IGNORE INTO users (id, email, password_hash, role, first_name, last_name, phone, avatar_url, is_active, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        foreach ($users as $u) {
            $stmt->execute($u);
        }

        // 3. Teacher Profile
        $stmt = $pdo->prepare("INSERT OR IGNORE INTO teacher_profiles (id, user_id, title, phone, bio, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            'tch_radu_teodorescu',
            'usr_teacher_radu',
            'Profesor Matematică & Mentor Robotică',
            '0722111222',
            'Cadru didactic cu peste 12 ani de experiență. Creez un mediu sigur și stimulativ pentru progresul fiecărui elev.',
            $now,
            $now
        ]);

        // 4. Students
        $students = [
            ['stu_matei_popescu', 'usr_student_matei', 'ws_radu_teodorescu', 'Matei', 'Popescu', 'R.', 'matei.popescu@elev.ro', '0722001101', '2011-04-12', 1, 'Înțelege rapid noțiunile de geometrie în spațiu. Recomandat să lucrăm la redactarea mai riguroasă a demonstrațiilor la examen.', $now, $now],
            ['stu_sofia_popescu', 'usr_student_sofia', 'ws_radu_teodorescu', 'Sofia', 'Popescu', 'R.', 'sofia.popescu@elev.ro', '0722001102', '2013-09-20', 1, 'Foarte pasionată de algoritmi și asamblarea senzorilor. Progresează excelent la C++.', $now, $now],
            ['stu_andrei_ionescu', 'usr_student_andrei', 'ws_radu_teodorescu', 'Andrei', 'Ionescu', 'M.', 'andrei.ionescu@elev.ro', '0722001103', '2011-02-15', 0, 'Atenție la calculele cu radicali. Ritm de lucru bun.', $now, $now],
            ['stu_elena_dumitrescu', null, 'ws_radu_teodorescu', 'Elena', 'Dumitrescu', 'V.', 'elena.d@elev.ro', '0722001104', '2011-06-18', 1, 'Creativă la rezolvarea ecuațiilor. Necesită încurajare pentru a vorbi mai des la tablă.', $now, $now],
            ['stu_david_radu', null, 'ws_radu_teodorescu', 'David', 'Radu', 'D.', 'david.r@elev.ro', '0722001105', '2011-11-05', 1, 'Foarte bun la calcul algebric.', $now, $now],
            ['stu_daria_stan', null, 'ws_radu_teodorescu', 'Daria', 'Stan', 'C.', 'daria.s@elev.ro', '0722001106', '2011-08-30', 0, 'Implicare activă la fiecare oră.', $now, $now],
            ['stu_mihai_gheorghiu', null, 'ws_radu_teodorescu', 'Mihai', 'Gheorghiu', 'G.', 'mihai.g@elev.ro', '0722001107', '2010-01-22', 1, 'Punctaj mare la testul inițial.', $now, $now],
            ['stu_mara_vasilescu', null, 'ws_radu_teodorescu', 'Mara', 'Vasilescu', 'A.', 'mara.v@elev.ro', '0722001108', '2010-05-14', 1, 'Gândire analitică solidă.', $now, $now],
        ];
        $stmt = $pdo->prepare("INSERT OR IGNORE INTO student_profiles (id, user_id, workspace_id, first_name, last_name, father_initial, email, phone, date_of_birth, is_paid, private_notes, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        foreach ($students as $s) {
            $stmt->execute($s);
        }

        // 5. Guardians
        $guardians = [
            ['grd_radu_popescu', 'usr_guardian_radu_popescu', 'ws_radu_teodorescu', 'Radu', 'Popescu', 'radu.popescu@familie.ro', '0722334455', 'Tată', $now, $now],
            ['grd_cristina_ionescu', 'usr_guardian_cristina_ionescu', 'ws_radu_teodorescu', 'Cristina', 'Ionescu', 'cristina.ionescu@familie.ro', '0722445566', 'Mamă', $now, $now],
        ];
        $stmt = $pdo->prepare("INSERT OR IGNORE INTO guardian_profiles (id, user_id, workspace_id, first_name, last_name, email, phone, relationship, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        foreach ($guardians as $g) {
            $stmt->execute($g);
        }

        // 6. Guardian Links
        $links = [
            ['lnk_grd_matei', 'grd_radu_popescu', 'stu_matei_popescu', 'active', $now],
            ['lnk_grd_sofia', 'grd_radu_popescu', 'stu_sofia_popescu', 'active', $now],
            ['lnk_grd_andrei', 'grd_cristina_ionescu', 'stu_andrei_ionescu', 'active', $now],
        ];
        $stmt = $pdo->prepare("INSERT OR IGNORE INTO guardian_student_links (id, guardian_id, student_id, status, created_at) VALUES (?, ?, ?, ?, ?)");
        foreach ($links as $l) {
            $stmt->execute($l);
        }

        // 7. Groups
        $groups = [
            ['grp_cls_7b', 'ws_radu_teodorescu', 'Clasa a VII-a B (Matematică Școală)', 'school_class', 'Programa de geometrie și algebră clasa a VII-a', '#3B82F6', $now, $now],
            ['grp_med_eval', 'ws_radu_teodorescu', 'Pregătire Evaluare Națională (Grupă Meditații)', 'tutoring_group', 'Pregătire aprofundată teste tip examen', '#4A77DA', $now, $now],
            ['grp_rob_cpp', 'ws_radu_teodorescu', 'Atelier Robotică & Algoritmi C++', 'workshop', 'Proiectare roboți mobili și algoritmi Arduino/C++', '#059669', $now, $now],
            ['grp_indiv_mate', 'ws_radu_teodorescu', 'Pregătire Concursuri & Excelență', 'individual_lesson', 'Lecții 1-la-1 pentru olimpiade școlare', '#D97706', $now, $now],
        ];
        $stmt = $pdo->prepare("INSERT OR IGNORE INTO groups (id, workspace_id, name, type, description, color_tag, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        foreach ($groups as $grp) {
            $stmt->execute($grp);
        }

        // 8. Group Enrollments
        $enrollments = [
            ['enr_1', 'grp_cls_7b', 'stu_matei_popescu', $now, 'active'],
            ['enr_2', 'grp_cls_7b', 'stu_andrei_ionescu', $now, 'active'],
            ['enr_3', 'grp_cls_7b', 'stu_elena_dumitrescu', $now, 'active'],
            ['enr_4', 'grp_cls_7b', 'stu_david_radu', $now, 'active'],
            ['enr_5', 'grp_cls_7b', 'stu_daria_stan', $now, 'active'],
            ['enr_6', 'grp_med_eval', 'stu_matei_popescu', $now, 'active'],
            ['enr_7', 'grp_med_eval', 'stu_mihai_gheorghiu', $now, 'active'],
            ['enr_8', 'grp_med_eval', 'stu_mara_vasilescu', $now, 'active'],
            ['enr_9', 'grp_rob_cpp', 'stu_sofia_popescu', $now, 'active'],
            ['enr_10', 'grp_rob_cpp', 'stu_andrei_ionescu', $now, 'active'],
            ['enr_11', 'grp_indiv_mate', 'stu_matei_popescu', $now, 'active'],
        ];
        $stmt = $pdo->prepare("INSERT OR IGNORE INTO group_enrollments (id, group_id, student_id, enrolled_at, status) VALUES (?, ?, ?, ?, ?)");
        foreach ($enrollments as $e) {
            $stmt->execute($e);
        }

        // 9. Schedules
        $schedules = [
            ['sch_1', 'grp_cls_7b', 1, '08:00', '08:50', 'Sala 204'],
            ['sch_2', 'grp_cls_7b', 3, '09:00', '09:50', 'Sala 204'],
            ['sch_3', 'grp_cls_7b', 5, '10:00', '10:50', 'Sala 204'],
            ['sch_4', 'grp_med_eval', 2, '16:30', '18:30', 'Cabinet Didactic'],
            ['sch_5', 'grp_med_eval', 4, '16:30', '18:30', 'Cabinet Didactic'],
            ['sch_6', 'grp_rob_cpp', 6, '10:00', '12:30', 'Laborator Robotică'],
            ['sch_7', 'grp_indiv_mate', 4, '18:45', '20:00', 'Cabinet Didactic'],
        ];
        $stmt = $pdo->prepare("INSERT OR IGNORE INTO group_schedules (id, group_id, day_of_week, start_time, end_time, room_or_link) VALUES (?, ?, ?, ?, ?, ?)");
        foreach ($schedules as $s) {
            $stmt->execute($s);
        }

        // 10. Lessons
        $lessons = [
            ['les_1', 'grp_cls_7b', 'Teorema lui Thales și Aplicații Practice', date('Y-m-d', strtotime('-2 days')), '08:00', '08:50', 'Am prezentat noțiunea de segmente proporționale și exemple geometrice.', $now],
            ['les_2', 'grp_med_eval', 'Rezolvare Varianta Examen 2025: Geometrie Plană', date('Y-m-d', strtotime('-1 days')), '16:30', '18:30', 'Lucru pe echipe și verificare barem.', $now],
            ['les_3', 'grp_rob_cpp', 'Programarea Senzorilor Ultrasonici în C++', date('Y-m-d', strtotime('+3 days')), '10:00', '12:30', 'Calibrare senzori HC-SR04 și testare motoare.', $now],
        ];
        $stmt = $pdo->prepare("INSERT OR IGNORE INTO lessons (id, group_id, title, lesson_date, start_time, end_time, lesson_notes, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        foreach ($lessons as $les) {
            $stmt->execute($les);
        }

        // 11. Attendance
        $attendance = [
            ['att_1', 'les_1', 'stu_matei_popescu', 'present', 'Activ la tablă', $now, $now],
            ['att_2', 'les_1', 'stu_andrei_ionescu', 'present', '', $now, $now],
            ['att_3', 'les_1', 'stu_elena_dumitrescu', 'present', '', $now, $now],
            ['att_4', 'les_1', 'stu_david_radu', 'late', 'A întârziat 10 minute', $now, $now],
            ['att_5', 'les_1', 'stu_daria_stan', 'excused', 'Învoire medicală', $now, $now],
            ['att_6', 'les_2', 'stu_matei_popescu', 'present', 'A rezolvat problema de geometrie', $now, $now],
            ['att_7', 'les_2', 'stu_mihai_gheorghiu', 'present', '', $now, $now],
            ['att_8', 'les_2', 'stu_mara_vasilescu', 'present', '', $now, $now],
        ];
        $stmt = $pdo->prepare("INSERT OR IGNORE INTO attendance_records (id, lesson_id, student_id, status, note, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?)");
        foreach ($attendance as $att) {
            $stmt->execute($att);
        }

        // 12. Assignments
        $assignments = [
            ['asg_1', 'grp_cls_7b', 'Fișa de Lucru: Teorema lui Thales (ex. 1-8)', 'Rezolvați exercițiile din fișa distribuită. Desenați clar figurile geometrice.', date('Y-m-d H:i:s', strtotime('-2 days')), date('Y-m-d H:i:s', strtotime('+4 days')), $now],
            ['asg_2', 'grp_med_eval', 'Varianta 3 Model Evaluare Națională 2025', 'Rezolvați Subiectul I și Subiectul II pe foaie tipizat.', date('Y-m-d H:i:s', strtotime('-1 days')), date('Y-m-d H:i:s', strtotime('+5 days')), $now],
            ['asg_3', 'grp_rob_cpp', 'Algoritm de ocolire obstacole cu senzor ultrasonic', 'Scrieți funcția getDistance() și bucla loop() în mediul Arduino IDE.', date('Y-m-d H:i:s'), date('Y-m-d H:i:s', strtotime('+6 days')), $now],
        ];
        $stmt = $pdo->prepare("INSERT OR IGNORE INTO assignments (id, group_id, title, description, assigned_date, due_date, created_at) VALUES (?, ?, ?, ?, ?, ?, ?)");
        foreach ($assignments as $asg) {
            $stmt->execute($asg);
        }

        // 13. Learning Materials
        $materials = [
            ['mat_1', 'grp_cls_7b', 'les_1', 'Sinteză Teorie: Teorema lui Thales & Reciproca', 'https://exemplu-resurse.ro/thales.pdf', 'pdf', $now],
            ['mat_2', 'grp_med_eval', 'les_2', 'Barem Oficial & Formule Geometrie Plană', 'https://exemplu-resurse.ro/barem_2025.pdf', 'pdf', $now],
            ['mat_3', 'grp_rob_cpp', 'les_3', 'Tutorial Video: Conectare Senzori HC-SR04', 'https://youtube.com/watch?v=exemplu', 'video', $now],
        ];
        $stmt = $pdo->prepare("INSERT OR IGNORE INTO learning_materials (id, group_id, lesson_id, title, url, file_type, created_at) VALUES (?, ?, ?, ?, ?, ?, ?)");
        foreach ($materials as $mat) {
            $stmt->execute($mat);
        }

        // 14. Assessments
        $assessments = [
            ['asm_1', 'grp_cls_7b', 'Test Sumativ: Rapoarte și Teorema lui Thales', 'test', 10.0, date('Y-m-d', strtotime('-5 days')), $now],
            ['asm_2', 'grp_med_eval', 'Simulare Test Evaluare Națională - Algebră', 'test', 10.0, date('Y-m-d', strtotime('-3 days')), $now],
        ];
        $stmt = $pdo->prepare("INSERT OR IGNORE INTO assessments (id, group_id, title, assessment_type, max_score, assessment_date, created_at) VALUES (?, ?, ?, ?, ?, ?, ?)");
        foreach ($assessments as $asm) {
            $stmt->execute($asm);
        }

        // 15. Assessment Results
        $results = [
            ['res_1', 'asm_1', 'stu_matei_popescu', 9.50, 'A ezitat puțin la problema 3b, dar a corectat singur.', 'Lucrare foarte bună! Demonstrație clară și desen geometric impecabil.', 1, $now, $now],
            ['res_2', 'asm_1', 'stu_andrei_ionescu', 8.50, 'Atenție la calculele algebrice.', 'Evoluție frumoasă, a abordat curajos problema de baraj.', 1, $now, $now],
            ['res_3', 'asm_1', 'stu_elena_dumitrescu', 9.00, 'Bravo!', 'Răspunsuri foarte bine argumentate.', 1, $now, $now],
            ['res_4', 'asm_2', 'stu_matei_popescu', 9.80, 'Pregătit excelent.', 'Rezultat excepțional! Felicitări pentru rigoare.', 1, $now, $now],
        ];
        $stmt = $pdo->prepare("INSERT OR IGNORE INTO assessment_results (id, assessment_id, student_id, score, private_teacher_notes, published_feedback, is_published, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        foreach ($results as $res) {
            $stmt->execute($res);
        }

        // 16. Published Feedbacks
        $feedbacks = [
            ['pf_1', 'stu_matei_popescu', 'Matei a arătat o implicare deosebită în rezolvarea problemelor de geometrie la tabla de clasă!', 'progress', $now],
            ['pf_2', 'stu_sofia_popescu', 'Sofia a calibrat senzorul ultrasonic cu o precizie excelentă în cadrul atelierului de robotică.', 'progress', $now],
            ['pf_3', 'stu_andrei_ionescu', 'Andrei a progresat mult la simplificarea fracțiilor algebrice!', 'encouragement', $now],
        ];
        $stmt = $pdo->prepare("INSERT OR IGNORE INTO published_feedbacks (id, student_id, content, category, created_at) VALUES (?, ?, ?, ?, ?)");
        foreach ($feedbacks as $fb) {
            $stmt->execute($fb);
        }

        // 17. Announcements
        $announcements = [
            ['ann_1', null, 'Programul Consultațiilor și Ședințelor Didactice', "Stimați părinți și dragi elevi,\nÎn această săptămână consultațiile individuale se vor desfășura conform orarului afișat.", $now],
            ['ann_2', 'grp_rob_cpp', 'Materiale necesare pentru ședința de sâmbătă', "Avem rugămintea să aduceți laptopurile cu mediul Arduino IDE instalat.", $now],
        ];
        $stmt = $pdo->prepare("INSERT OR IGNORE INTO announcements (id, group_id, title, content, created_at) VALUES (?, ?, ?, ?, ?)");
        foreach ($announcements as $ann) {
            $stmt->execute($ann);
        }

        // 18. Conversations
        $conversations = [
            ['conv_1', 'tch_radu_teodorescu', 'grd_radu_popescu', 'stu_matei_popescu', $now, $now],
            ['conv_2', 'tch_radu_teodorescu', 'grd_cristina_ionescu', 'stu_andrei_ionescu', $now, $now],
        ];
        $stmt = $pdo->prepare("INSERT OR IGNORE INTO conversations (id, teacher_id, guardian_id, student_id, updated_at, created_at) VALUES (?, ?, ?, ?, ?, ?)");
        foreach ($conversations as $c) {
            $stmt->execute($c);
        }

        // 19. Messages
        $messages = [
            ['msg_1', 'conv_1', 'parent', 'usr_guardian_radu_popescu', 'Bună ziua, domnule profesor! Vă mulțumim pentru feedback-ul oferit lui Matei.', 1, date('Y-m-d H:i:s', strtotime('-1 days'))],
            ['msg_2', 'conv_1', 'teacher', 'usr_teacher_radu', 'Cu mare drag! Matei lucrează cu seriozitate și are o evoluție foarte frumoasă.', 1, date('Y-m-d H:i:s', strtotime('-18 hours'))],
        ];
        $stmt = $pdo->prepare("INSERT OR IGNORE INTO messages (id, conversation_id, sender_role, sender_id, content, is_read, sent_at) VALUES (?, ?, ?, ?, ?, ?, ?)");
        foreach ($messages as $m) {
            $stmt->execute($m);
        }

        // 20. Learning Goals
        $goals = [
            ['gol_1', 'stu_matei_popescu', 'Să rezolv 15 probleme de geometrie din culegere fără erori de calcul', date('Y-m-d', strtotime('+14 days')), 1, date('Y-m-d H:i:s', strtotime('-1 days')), $now],
            ['gol_2', 'stu_matei_popescu', 'Să obțin peste 9.50 la simularea de examen de la finalul lunii', date('Y-m-d', strtotime('+30 days')), 0, null, $now],
            ['gol_3', 'stu_sofia_popescu', 'Să programez robotul să parcurgă un labirint simplu', date('Y-m-d', strtotime('+20 days')), 0, null, $now],
        ];
        $stmt = $pdo->prepare("INSERT OR IGNORE INTO learning_goals (id, student_id, title, target_date, is_completed, completed_at, created_at) VALUES (?, ?, ?, ?, ?, ?, ?)");
        foreach ($goals as $g) {
            $stmt->execute($g);
        }
    }
}
