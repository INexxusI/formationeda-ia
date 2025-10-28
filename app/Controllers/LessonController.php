<?php
namespace App\Controllers;

class LessonController {
    public function show(): void {
        $title = 'Leçon – Démo Minimal JRPG';
        $defaultProf = $_GET['prof'] ?? 'arielle';
        ob_start();
        require __DIR__ . '/../Views/lesson/show.php';
        $content = ob_get_clean();
        require __DIR__ . '/../Views/layouts/base.php';
    }
}
