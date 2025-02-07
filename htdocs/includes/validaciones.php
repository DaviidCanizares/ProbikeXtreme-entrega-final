<?php
// Función para validar un correo electrónico
function validarEmail($email) {
    if (filter_var($email, FILTER_VALIDATE_EMAIL) !== false) {
        return ['valido' => true, 'mensaje' => ''];
    }
    return ['valido' => false, 'mensaje' => 'El correo electrónico no es válido.'];
}

// Función para validar un número de teléfono
function validarTelefono($telefono) {
    if (preg_match('/^[0-9]{9}$/', $telefono)) {
        return ['valido' => true, 'mensaje' => ''];
    }
    return ['valido' => false, 'mensaje' => 'El número de teléfono debe tener exactamente 9 dígitos.'];
}

// Función para validar una fecha (formato: YYYY-MM-DD)
function validarFecha($fecha) {
    $fechaObj = DateTime::createFromFormat('Y-m-d', $fecha);
    if ($fechaObj && $fechaObj->format('Y-m-d') === $fecha) {
        return ['valido' => true, 'mensaje' => ''];
    }
    return ['valido' => false, 'mensaje' => 'La fecha no es válida. Usa el formato YYYY-MM-DD.'];
}

// Función para validar nombres y apellidos
function validarTexto($texto, $minLength = 2, $maxLength = 50) {
    $texto = trim($texto); // Eliminar espacios al inicio y al final
    if (preg_match('/^[\p{L}\s]+$/u', $texto) && strlen($texto) >= $minLength && strlen($texto) <= $maxLength) {
        return ['valido' => true, 'mensaje' => ''];
    }
    return ['valido' => false, 'mensaje' => "El texto debe contener solo letras y espacios, y tener entre $minLength y $maxLength caracteres."];
}

// Función para validar una contraseña
function validarPassword($password, $minLength = 8, $maxLength = 50) {
    if (strlen($password) >= $minLength && strlen($password) <= $maxLength) {
        return ['valido' => true, 'mensaje' => ''];
    }
    return ['valido' => false, 'mensaje' => "La contraseña debe tener entre $minLength y $maxLength caracteres."];
}

// Función para limpiar datos de entrada (prevención de XSS)
function limpiarEntrada($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}
?>
