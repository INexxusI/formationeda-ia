<?php
namespace Core;

class Router {
    private array $routes = [ 'GET' => [], 'POST' => [] ];

    public function get(string $path, $handler): void { $this->routes['GET'][$this->normalize($path)] = $handler; }
    public function post(string $path, $handler): void { $this->routes['POST'][$this->normalize($path)] = $handler; }

    public function dispatch(string $method, string $path): void {
        $method = strtoupper($method);
        $path = $this->normalize($path);
        $handler = $this->routes[$method][$path] ?? null;

        if (!$handler) { http_response_code(404); echo "404 Not Found"; return; }

        if (is_callable($handler)) { echo $handler(); return; }

        if (is_string($handler) && str_contains($handler, '@')) {
            [$controller, $action] = explode('@', $handler, 2);
            $fqcn = "App\\Controllers\\{$controller}";
            if (!class_exists($fqcn)) { http_response_code(500); echo "Controller {$fqcn} introuvable."; return; }
            $obj = new $fqcn();
            if (!method_exists($obj, $action)) { http_response_code(500); echo "Méthode {$action} introuvable dans {$fqcn}."; return; }
            $obj->$action(); return;
        }

        http_response_code(500); echo "Handler invalide";
    }

    private function normalize(string $path): string {
        $path = '/' . trim($path, '/');
        return $path === '' ? '/' : $path;
    }
}
