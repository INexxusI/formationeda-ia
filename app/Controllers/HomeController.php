<?php
namespace App\Controllers;

use Core\DB;

class HomeController {
    public function index(): void {
        $title = 'Prépa – Accueil (Minimal JRPG)';

        // 1) Récupérer une question
        $pdo = DB::pdo();
        $stmt = $pdo->query("SELECT * FROM questions_validated ORDER BY RAND() LIMIT 1");
        $question = $stmt->fetch();

        if (!$question) {
            $question = [
                'id' => 0,
                'stem' => "<p><em>Aucune question en base (ajoute-en via phpMyAdmin).</em></p>",
                'answer' => json_encode(['type' => 'choice', 'choices' => [], 'correct_index' => -1]),
            ];
        }

        // 2) Préparer les variables pour la vue
        $answer = json_decode($question['answer'], true);
        $status = $_GET['status'] ?? null;
        $msg    = $_GET['msg'] ?? null;

        // 3) Rendu : vue -> buffer -> layout
        ob_start();
        // IMPORTANT: chemin de la vue (index.php dans /app/Views/home/)
        require BASE_PATH . '/app/Views/home/index.php';
        $content = ob_get_clean();

        // Layout principal (utilise $title et $content)
        require BASE_PATH . '/app/Views/layouts/base.php';
    }
}
