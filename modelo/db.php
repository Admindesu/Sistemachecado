<?php
/**
 * Ejecuta una consulta preparada.
 *
 *   consulta($conexion, "SELECT * FROM cargo WHERE id_cargo = ?", "i", $id)
 *
 * Devuelve un mysqli_result para SELECT, true para INSERT/UPDATE/DELETE
 * exitosos y false si hubo error (el error queda en $conexion->error).
 */
function consulta(mysqli $conexion, string $sql, string $tipos = '', ...$params)
{
    $stmt = $conexion->prepare($sql);
    if (!$stmt) {
        error_log('Error preparando consulta: ' . $conexion->error);
        return false;
    }
    if ($tipos !== '') {
        $stmt->bind_param($tipos, ...$params);
    }
    if (!$stmt->execute()) {
        error_log('Error ejecutando consulta: ' . $stmt->error);
        return false;
    }
    $resultado = $stmt->get_result();
    return $resultado === false ? true : $resultado;
}
