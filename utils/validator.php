<?php
// utils/Validator.php - Validaciones
class Validator
{
    public static function email($email)
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }

    public static function dni($dni)
    {
        return preg_match('/^\d{8}$/', $dni);
    }

    public static function ruc($ruc)
    {
        return preg_match('/^\d{11}$/', $ruc);
    }

    public static function telefono($telefono)
    {
        return preg_match('/^\d{9}$/', $telefono);
    }

    public static function required($value)
    {
        return !empty(trim($value));
    }

    public static function minLength($value, $min)
    {
        return strlen(trim($value)) >= $min;
    }

    public static function maxLength($value, $max)
    {
        return strlen(trim($value)) <= $max;
    }

    public static function validateRegistrationData($data, $type)
    {
        $errors = [];

        // Validaciones comunes
        if (!self::email($data['email'] ?? '')) {
            $errors[] = 'Email inválido';
        }

        if (!self::minLength($data['password'] ?? '', 6)) {
            $errors[] = 'Password debe tener al menos 6 caracteres';
        }

        // Validaciones específicas por tipo
        switch ($type) {
            case 'donante':
                if (!self::required($data['nombre_completo'] ?? '')) {
                    $errors[] = 'Nombre completo es requerido';
                }
                if (!self::dni($data['dni'] ?? '')) {
                    $errors[] = 'DNI inválido';
                }
                if (empty($data['fecha_nacimiento'])) {
                    $errors[] = 'Fecha de nacimiento es requerida';
                }
                break;

            case 'organizacion':
                if (!self::required($data['razon_social'] ?? '')) {
                    $errors[] = 'Razón social es requerida';
                }
                if (!self::ruc($data['ruc'] ?? '')) {
                    $errors[] = 'RUC inválido';
                }
                if (!self::telefono($data['telefono'] ?? '')) {
                    $errors[] = 'Teléfono inválido';
                }
                if (!self::required($data['direccion'] ?? '')) {
                    $errors[] = 'Dirección es requerida';
                }
                break;
        }

        return $errors;
    }
}
