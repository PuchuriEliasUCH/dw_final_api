<?php
class Auth {
    public static function startSession() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }

    public static function login($user_data) {
        self::startSession();
        $_SESSION['user_id'] = $user_data['id_usuario'];
        $_SESSION['email'] = $user_data['email'];
        $_SESSION['logged_in'] = true;
        return true;
    }

    public static function logout() {
        self::startSession();
        session_unset();
        session_destroy();
        return true;
    }

    public static function isLoggedIn() {
        self::startSession();
        return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
    }

    public static function getAuthUser() {
        self::startSession();
        if (!self::isLoggedIn()) {
            Response::error('Usuario no autenticado. Por favor inicia sesión.', 401);
        }
        
        return [
            'user_id' => $_SESSION['user_id'],
            'email' => $_SESSION['email']
        ];
    }

    public static function requireAuth() {
        if (!self::isLoggedIn()) {
            Response::error('Acceso denegado. Se requiere autenticación.', 401);
        }
    }
}
?>