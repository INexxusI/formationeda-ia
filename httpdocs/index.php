<?php
// PUBLIC FRONT CONTROLLER
declare(strict_types=1);

// On définit le chemin de base (un niveau au-dessus de httpdocs)
define('BASE_PATH', dirname(__DIR__)); // contient app/, config/, storage/

// ----- AUTOLOADER (PSR-4 simplifié) -----
spl_autoload_register(function ($class) {
    $prefixes = [
        'App\\'  => BASE_PATH . '/app/',
        'Core\\' => BASE_PATH . '/app/Core/',
    ];
    foreach ($prefixes as $prefix => $dir) {
        $len = strlen($prefix);
        if (strncmp($prefix, $class, $len) !== 0) continue;
        $relative = substr($class, $len);
        $file = $dir . str_replace('\\', '/', $relative) . '.php';
        if (file_exists($file)) {
            require $file;
            return;
        }
    }
});

// ----- CHARGER CONFIG -----
$config = require BASE_PATH . '/config/app.php';

// ----- ROUTEUR -----
use Core\Router;
$router = new Router;

// Définition des routes principales
$router->get('/', 'HomeController@index');          // page d’accueil
$router->get('/lesson', 'LessonController@show');   // page de démo / leçon
$router->post('/api/check', 'ApiController@check'); // vérifie la réponse

// ----- DISPATCH -----
$uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$router->dispatch($_SERVER['REQUEST_METHOD'] ?? 'GET', $uri);
