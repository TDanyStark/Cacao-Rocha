<?php
require_once 'config/config.php';
require_once 'app/middleware/AuthMiddleware.php';
require_once 'app/helpers/Logger.php';

// Capturar errores y registrar logs
set_error_handler(function ($severity, $message, $file, $line) {
    Logger::error("Error: [$severity] $message en $file línea $line");
});

set_exception_handler(function ($exception) {
    Logger::error("Excepción no manejada: " . $exception->getMessage());
});

register_shutdown_function(function () {
    $error = error_get_last();
    if ($error) {
        Logger::error("Fatal error: {$error['message']} en {$error['file']} línea {$error['line']}");
    }
});

// Manejo de autenticación
redirectIfAuthenticated();
redirectIfNotAuthenticated();

// Obtener la URL amigable
$url = isset($_GET['url']) ? rtrim($_GET['url'], '/') : 'auth/login';
$url = explode('/', $url);

$controllerName = ucfirst($url[0]) . 'Controller';
$method = isset($url[1]) ? $url[1] : 'index';

// Verificar si el controlador existe
$controllerPath = "app/controllers/$controllerName.php";
if (file_exists($controllerPath)) {
    require_once $controllerPath;
    $controller = new $controllerName();
    
    // Manejo de peticiones POST
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        if (!empty($data)) {
            call_user_func([$controller, $method], $data);
        } else {
            echo json_encode(["error" => "No se enviaron datos"]);
        }
    } else {
        call_user_func([$controller, $method]);
    }
} else {
    echo json_encode(["error" => "Página no encontrada"]);
}
