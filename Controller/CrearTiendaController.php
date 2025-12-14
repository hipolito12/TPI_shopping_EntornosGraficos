<?php

declare(strict_types=1);

include_once(__DIR__ . '/../Model/ProcesarTienda.php'); 

@ini_set('display_errors', '0');
@ini_set('log_errors', '1');

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
  http_response_code(405);
  echo json_encode(['ok' => false, 'message' => 'Método no permitido'], JSON_UNESCAPED_UNICODE);
  exit;
}

function field(string $k): string { return trim((string)($_POST[$k] ?? '')); }

$payload = [
  'nombre'        => field('nombre'),
  'apellido'      => field('apellido'),
  'email'         => field('email'),
  'password'      => (string)($_POST['password'] ?? ''),
  'telefono'      => field('telefono'),
  'sexo'          => field('sexo'),
  'dni'           => field('dni'),
  'cuil'          => field('cuil'),
  'rubro'         => field('rubro'),
  'nombre_local'  => field('nombre_local') ?: field('nombreLocal'),
  'lugar'         => field('lugar')        ?: field('ubicacion'),
];

$errors = [];
$rxNombre = "/^[A-Za-zÁÉÍÓÚÜÄËÏÖÑáéíóúüäëïöñ' ]{2,50}$/u";

if ($payload['nombre'] === '' || !preg_match($rxNombre, $payload['nombre'])) {
  $errors['nombre'] = 'Ingresá un nombre válido (2–50 letras).';
}
if ($payload['apellido'] === '' || !preg_match($rxNombre, $payload['apellido'])) {
  $errors['apellido'] = 'Ingresá un apellido válido (2–50 letras).';
}
if (!filter_var($payload['email'], FILTER_VALIDATE_EMAIL)) {
  $errors['email'] = 'Email inválido.';
}
if (!preg_match("/^(?=.*[A-Z])(?=.*\d).{8,}$/", $payload['password'])) {
  $errors['password'] = 'La contraseña debe tener 8+ caracteres, 1 mayúscula y 1 número.';
}
if ($payload['telefono'] !== '' && !preg_match("/^\d{7,20}$/", $payload['telefono'])) {
  $errors['telefono'] = 'Teléfono inválido (solo dígitos, 7–20).';
}
if ($payload['sexo'] === '') {
  $errors['sexo'] = 'Seleccioná una opción.';
}
if (!preg_match("/^\d{7,8}$/", $payload['dni'])) {
  $errors['dni'] = 'DNI inválido (7–8 dígitos).';
}
if (!preg_match("/^\d{11}$/", $payload['cuil'])) {
  $errors['cuil'] = 'CUIL inválido (11 dígitos).';
}
if (mb_strlen($payload['rubro']) < 2) {
  $errors['rubro'] = 'Indicá el rubro.';
}
if (mb_strlen($payload['nombre_local']) < 2) {
  $errors['nombre_local'] = 'Indicá el nombre del local.';
}
if ($payload['lugar'] === '') {
  $errors['lugar'] = 'Seleccioná una ubicación.';
}


if (!empty($errors)) {
  http_response_code(422);
  echo json_encode(['ok'=>false,'message'=>'Revisá los campos marcados.','errors'=>$errors], JSON_UNESCAPED_UNICODE);
  exit;
}


$dataForModel = [
  'nombre'       => $payload['nombre'] . ' ' . $payload['apellido'],
  'email'        => $payload['email'],
  'contrasena'   => $payload['password'],
  'telefono'     => $payload['telefono'],
  'sexo'         => $payload['sexo'],
  'dni'          => $payload['dni'],
  'cuil'         => $payload['cuil'],
  'rubro'        => $payload['rubro'],
  'nombreLocal'  => $payload['nombre_local'],
  'ubicacion'    => $payload['lugar'],
];

try {
  $id = saveStoreRequest($dataForModel);  

  if (is_int($id) && $id > 0) {
    http_response_code(201);
    echo json_encode(['ok'=>true,'message'=>'Solicitud de tienda enviada exitosamente.','id'=>$id], JSON_UNESCAPED_UNICODE);
    exit;
  }

  
  http_response_code(422);
  echo json_encode([
    'ok'      => false,
    'message' => 'No se pudo procesar la solicitud.',
    'errors'  => ['general' => 'Error al procesar la solicitud. Intente más tarde.']
  ], JSON_UNESCAPED_UNICODE);

} catch (Throwable $e) {
  
  http_response_code(500);
  echo json_encode(['ok'=>false,'message'=>'Error en el servidor. Intentalo más tarde.'], JSON_UNESCAPED_UNICODE);
}
