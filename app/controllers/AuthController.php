<?php
require_once 'app/models/User.php';
require_once 'app/helpers/AuthHelper.php';

class AuthController
{
    public function login()
    {
        require_once 'app/views/auth/login.php';
    }

    public function authenticate()
    {
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $email = $_POST['email'];
            $password = $_POST['password'];

            $userModel = new User();
            $user = $userModel->getUserByEmail($email);

            if ($user && $password === $user['password']) { // Verifica la contraseña cifrada
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['role'] = $user['role'];
                header("Location: /cacaorocha/transactions");
                exit;
            } else {
                $_SESSION['error'] = "Credenciales incorrectas.";
                header("Location: /cacaorocha/");
                exit;
            }
        }
    }

    public function logout()
    {
        // Eliminar cookies si existen
        if (isset($_COOKIE['user_id'])) {
            setcookie('user_id', '', time() - 3600, "/");
            setcookie('user_name', '', time() - 3600, "/");
        }

        session_destroy();
        header("Location: /cacaorocha/");
        exit;
    }

    public function reset_password()
    {
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            try {
                $current_password = $_POST['current_password'];
                $new_password = $_POST['new_password'];
                $confirm_password = $_POST['confirm_password'];

                // comprobar que las contraseñas coincidan
                if ($new_password !== $confirm_password) {
                    $_SESSION['error_transactions'] = "Las contraseñas no coinciden.";
                    header("Location: /cacaorocha/transactions");
                    exit;
                }

                $userModel = new User();
                $user = $userModel->getUserById($_SESSION['user_id']);

                if ($user && $current_password === $user['password']) {
                    $userModel->updateUserPassword($_SESSION['user_id'], $new_password);
                    session_destroy();
                    header("Location: /cacaorocha/");
                    exit;
                } else {
                    $_SESSION['error_transactions'] = "Contraseña actual incorrecta.";
                    header("Location: /cacaorocha/transactions");
                    exit;
                }
            } catch (Exception $e) {
                Logger::error("Error en TransactionsController::create - " . $e->getMessage());
                $_SESSION['error_transactions'] = "Ocurrió un error al intentar crear la transacción.";
                header("Location: /cacaorocha/transactions");
                exit;
            }
        }
    }
}
