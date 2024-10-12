<?php
declare(strict_types=1);

$data = [];
$data['titulo'] = "Calcula notas";

// Comprobamos envío
if (isset($_POST['enviar'])) {

  // Saneamos input
  $data['texto'] = filter_var($_POST['input'], FILTER_SANITIZE_SPECIAL_CHARS);

  // Verificamos que no hay errores
  $errores = checkForm($_POST['input']);
  if (count($errores) > 0) {

    // Mostramos errores
    $data['errores'] = $errores;
  } else {

    // Procesamos
    $data['resultado'] = procesarTexto($_POST['input']);
  }
}

/**
 * Función de validación del formulario
 * @param string $datos array que contiene los datos ya saneados.
 * @return array array que contiene los errores.
 */
function checkForm(string $datos): array
{
  $errores = [];
  if (empty($datos)) {
    $errores['texto'] = 'Error: este campo es obligatorio';
  } else {
    $descodificado = json_decode(trim($datos), true);
    if (is_null($descodificado)) {
      $errores['texto'][] = 'Error: introduzca un bloque en formato JSON válido';
    } else {
      foreach ((array)$descodificado as $asignatura => $alumnado) {
        if ((!is_string($asignatura)) || (mb_strlen(trim($asignatura)) < 1)) {
          $errores['texto'][] = "Error: el formato de texto de la asignatura '$asignatura' no es de un tipo válido";
        }
        if (!is_array($alumnado)) {
          $errores['texto'][] = "Error: cada asignatura debe contener un conjunto de alumnos ('$alumnado' no es un array)";
        } else {
          foreach ($alumnado as $alumno => $nota) {
            if (!is_string($alumno) || (mb_strlen(trim($alumno)) < 1)) {
              $errores['texto'][] = "Error: el formato de texto del nombre del alumno '$alumno' no es de un tipo válido";
            }
            if (!is_numeric($nota)) {
              $errores['texto'][] = "Error: el formato numérico de la nota '$nota' del alumno '$alumno' no es de un tipo válido";
            } else if ($nota > 10 || $nota < 0) {
              $errores['texto'][] = "Error: la nota '$nota' del alumno '$alumno' no está comprendida entre 0-10";
            }
          } //foreach $alumnado
        }
      } //foreach $asignaturas
    }
  }
  return $errores;
}

/**
 * Función que procesa los datos y realiza los cálculos
 * @param string $input
 * @return array
 */
function procesarTexto(string $datos): array
{

}

/**
 * Función que recoge un array con los cálculos hechos y devuelve otro con el listado.
 * @param array $datos
 * @return array
 */
function mostrarListado(array $datos): array
{

}

// Cargamos las vistas
include 'views/templates/header.php';
include 'views/calculos-notas.tomas.view.php';
include 'views/templates/footer.php';