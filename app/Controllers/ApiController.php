<?php
namespace App\Controllers;

class ApiController {
    public function check(): void {
        header('Content-Type: application/json; charset=utf-8');
        $input = $_POST['answer'] ?? '';
        $val = str_replace(',', '.', trim((string)$input));
        $ok = (is_numeric($val) && abs((float)$val - 3.0) < 1e-9);
        echo json_encode([
            'ok' => $ok,
            'message' => $ok ? '✅ Correct ! +10 XP' : '❌ Pas tout à fait. Essaie encore.',
            'xp' => $ok ? 10 : 0
        ]);
    }
}

namespace App\Controllers;

use Core\DB;

class ApiController {
    public function check(): void {
        $pdo = DB::pdo();

        $qid   = (int)($_POST['question_id'] ?? 0);
        $choice = isset($_POST['choice']) ? (int)$_POST['choice'] : -1;

        $st = $pdo->prepare("SELECT answer FROM questions_validated WHERE id=?");
        $st->execute([$qid]);
        $row = $st->fetch();

        if (!$row) {
            header('Location: /?status=err&msg=Question%20introuvable'); exit;
        }

        $answer = json_decode($row['answer'], true);
        $isCorrect = 0;

        if (($answer['type'] ?? '') === 'choice') {
            $isCorrect = ($choice === (int)$answer['correct_index']) ? 1 : 0;
        }

        // Feedback dans l'URL (MVP)
        header('Location: /?status=' . ($isCorrect ? 'ok' : 'err') . '&msg=' . ($isCorrect ? 1 : 0));
    }
}
