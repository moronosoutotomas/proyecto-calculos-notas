<?php /** @noinspection PhpComposerExtensionStubsInspection */
declare(strict_types=1);

$data = [];

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
    if (is_null($descodificado)) { // json_decode() devuelve nulos si no reconoce el formato
      $errores['texto'][] = 'Error: introduzca un bloque en formato JSON válido';
    } else {

      // iteración asignaturas
      foreach ((array)$descodificado as $asignatura => $alumnado) {
        if ((!is_string($asignatura)) || (mb_strlen(trim($asignatura)) < 1)) {
          $errores['texto'][] = "Error: el formato de texto de la asignatura '$asignatura' no es válido";
        }
        if (!is_array($alumnado)) {
          $errores['texto'][] = "Error: cada asignatura debe contener un conjunto de alumnos ('$alumnado' no es un array)";
        } else {

          // iteración alumnos
          foreach ($alumnado as $alumno => $boletinNotas) {
            if (!is_string($alumno) || (mb_strlen(trim($alumno)) < 1)) {
              $errores['texto'][] = "Error: el formato de texto del nombre del alumno '$alumno' no es válido";
            }

            // iteración boletines de notas
            foreach ($boletinNotas as $nota) {
              if (!is_numeric($nota)) {
                $errores['texto'][] = "Error: el formato numérico de la nota '$nota' de '$alumno' no es válido";
              } else if ($nota > 10 || $nota < 0) {
                $errores['texto'][] = "Error: la nota '$nota' del alumno '$alumno' no está comprendida entre 0-10";
              }
            }// foreach $nota

          } //foreach $alumnado

        }
      } //foreach $asignaturas

    }
  }
  return $errores;
}

/**
 * Función que procesa los datos y realiza los cálculos
 * @param string $datos
 * @return array
 */
function procesarTexto(string $datos): array
{
  $descodificado = json_decode(trim($datos), true);
  $resultado = [];
  $suspensos = 0;
  $aprobados = 0;
  $mediaAsignatura = 0;
  $max = -1;
  $min = 11;

  // iteración asignaturas
  foreach ($descodificado as $asignatura => $alumnado) {

    // iteración alumnos
    foreach ($alumnado as $alumno => $boletinNotas) {
      $mediaAlumno = calcularMedia($boletinNotas);
      $suspensos = 0;
      $aprobados = 0;
      $mediaAlumno < 5 ? $suspensos++ : $aprobados++;

      if ($mediaAlumno > $max) {
        $resultado[$asignatura]['max']['alumno'] = $alumno;
        $resultado[$asignatura]['max']['nota'] = $mediaAlumno;
      } else if ($mediaAlumno < $min) {
        $resultado[$asignatura]['min']['alumno'] = $alumno;
        $resultado[$asignatura]['min']['nota'] = $mediaAlumno;
      }

      // asignamos resultados por alumno
      $resultado[$asignatura]['media'] = number_format($mediaAsignatura, 2, ',');
      $resultado[$asignatura]['aprobados'] = $aprobados;
      $resultado[$asignatura]['suspensos'] = $suspensos;
      $resultado[$asignatura]['max'] = $max;
      $resultado[$asignatura]['min'] = $min;
    }
  } // foreach asignaturas

  return $resultado;
}

/**
 * Función que recoge un array con los cálculos hechos y devuelve otro con el listado.
 * @param array $datos
 * @return array
 */
function mostrarListado(array $datos): array
{

}

/**
 * Función que calcula la media de un array numérico dado comprobando antes si es numérico
 * @param array $datos
 * @return int
 */
function calcularMedia(array $datos): float
{
  $sumaDatos = 0;
  $numDatos = count($datos);

  foreach ($datos as $dato) {
    $sumaDatos += $dato;
  }
  return $sumaDatos / $numDatos;
}

// Cargamos las vistas
include 'views/templates/header.php';
include 'views/calculos-notas.tomas.view.php';
include 'views/templates/footer.php';