<?php

session_start();
require_once '../Model/ProcesarDashboardTienda.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idUsuario = $_SESSION['IDusuario'];
    $local = getLocalPorUsuario($idUsuario);
    
    if (!$local) {
        echo json_encode(['ok' => false, 'message' => 'No se encontró el local.'], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    $idLocal = $local['IDlocal'];

    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'eliminar_promocion':
                $idPromocion = $_POST['idPromocion'];
                if (eliminarPromocion($idPromocion, $idLocal)) {
                    echo json_encode(['ok' => true, 'message' => 'Promoción desactivada correctamente.'], JSON_UNESCAPED_UNICODE);
                } else {
                    echo json_encode(['ok' => false, 'message' => 'Error al desactivar la promoción.'], JSON_UNESCAPED_UNICODE);
                }
                exit;

            case 'aceptar_solicitud':
                $usuarioFk = $_POST['usuarioFk'];
                $promoFK = $_POST['promoFK'];
                if (actualizarEstadoSolicitud($usuarioFk, $promoFK, 1)) {
                    echo json_encode(['ok' => true, 'message' => 'Solicitud aceptada.'], JSON_UNESCAPED_UNICODE);
                } else {
                    echo json_encode(['ok' => false, 'message' => 'Error al aceptar la solicitud.'], JSON_UNESCAPED_UNICODE);
                }
                exit;

            case 'rechazar_solicitud':
                $usuarioFk = $_POST['usuarioFk'];
                $promoFK = $_POST['promoFK'];
                if (actualizarEstadoSolicitud($usuarioFk, $promoFK, 2)) {
                    echo json_encode(['ok' => true, 'message' => 'Solicitud rechazada.'], JSON_UNESCAPED_UNICODE);
                } else {
                    echo json_encode(['ok' => false, 'message' => 'Error al rechazar la solicitud.'], JSON_UNESCAPED_UNICODE);
                }
                exit;
        }
    }
}
?>