<?php

class View {
    public static function render($view, $data = []) {
        extract($data); // Convierte las claves del array en variables
        $viewPath = "app/views/$view.php";
        if (file_exists($viewPath)) {
            require_once $viewPath;
        } else {
            die("La vista '$view' no existe.");
        }
    }
}
