<?php
declare(strict_types=1);

$data = [];

// Comprobamos que hubo envío
// if (isset($_POST)) { arreglo Breixiño
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST)) {

  // Saneamos input
  $data['texto'] = filter_var($_POST['input'], FILTER_SANITIZE_SPECIAL_CHARS);

  // Verificamos que no hay errores en los datos recibidos por POST
  $errores = checkForm($_POST['input']);

  if (count($errores) > 0) {
    // Mostramos errores si los hay
    $data['errores'] = $errores;
  } else {

    // Descodificamos el input
    $descodificado = json_decode(trim($_POST['input']), true);

    // Procesamos la información recibida acumulando los retornos de cada función
    // ya en su posición apropiada del set de resultados (y listados).
    $data['resultado'] = procesarTexto($descodificado);
    $data['listados'] = mostrarListado($descodificado);
  }
}
// $data['errores']['texto'] = "Por favor, introduzca un bloque de texto en formato JSON";

/**
 * Función de validación del formulario.
 * @param string $datos array que contiene los datos ya saneados, validados y sin errores.
 * @return array array que contiene solamente los errores.
 */
function checkForm(string $datos): array
{
  $errores = [];

  if (empty($datos)) {
    $errores['texto'][] = 'Error: este campo es obligatorio';
  } else {

    $descodificado = json_decode(trim($datos), true);

    if (is_null($descodificado)) { // OJO! json_decode() devuelve nulos si no reconoce el formato
      $errores['texto'][] = 'Error: introduzca un bloque en formato JSON válido';
    } else {

      if (!is_array($descodificado)) {
        $errores['texto'][] = 'Error: el contenido del JSON debe ser un conjunto de asignaturas, alumnos y notas';
      } else {

        if (mb_strlen($datos) < 10) {
          $errores['texto'][] = "Por favor, introduzca un bloque de texto en formato JSON";
        } else {

          // Iteración sobre las asignaturas
          foreach ($descodificado as $asignatura => $alumnado) {
            if ((!is_string($asignatura)) || (mb_strlen(trim($asignatura)) < 1)) {
              $errores['texto'][] = "Error: el formato de texto de la asignatura '$asignatura' no es válido";
            }
            if (!is_array($alumnado)) {
              $errores['texto'][] = "Error: cada asignatura debe contener un conjunto de alumnos ('$alumnado' no es un array)";
            } else {

              // Iteración sobre los alumnos
              foreach ($alumnado as $alumno => $boletinNotas) {
                if (!is_string($alumno) || (mb_strlen(trim($alumno)) < 1)) {
                  $errores['texto'][] = "Error: el formato de texto del nombre del alumno '$alumno' no es válido";
                }
                if (!is_array($boletinNotas)) {
                  $errores['texto'][] = "Error: el alumno '$alumno' debe tener un conjunto de notas";
                } else {
                  // Iteración sobre los boletines de notas
                  foreach ($boletinNotas as $nota) {
                    if (!is_numeric($nota)) {
                      $errores['texto'][] = "Error: el formato numérico de la nota '$nota' de '$alumno' no es válido";
                    } else if ($nota > 10 || $nota < 0) {
                      $errores['texto'][] = "Error: la nota '$nota' del alumno '$alumno' no está comprendida entre 0-10";
                    }
                  }// foreach $nota
                }
              } //foreach $alumnado

            }
          } //foreach $asignaturas

        }
      }
    }
  }
  return $errores;
}

/**
 * Función que procesa los datos y realiza los cálculos necesarios.
 * @param string $datos
 * @return array
 */
function procesarTexto(array $datos): array
{
  $resultado = [];

  // Iteración sobre las asignaturas
  foreach ($datos as $asignatura => $alumnado) {
    // Inicializamos las variables necesarias antes del bucle de alumnos
    // para poder asignarles datos dentro y mostrarlos fuera.
    $sumaNotas = 0;
    $numAlumnos = 0;
    $suspensos = 0;
    $aprobados = 0;
    $max = ['alumno' => '', 'nota' => -1];
    $min = ['alumno' => '', 'nota' => 11];

    // Iteración sobre los alumnos
    foreach ($alumnado as $alumno => $boletinNotas) {
      $mediaAlumno = calcularMedia($boletinNotas);
      $resultado[$asignatura]['alumnos'][$alumno] = $mediaAlumno;
      $sumaNotas += $mediaAlumno;
      $numAlumnos++;

      if ($mediaAlumno >= 5) {
        $aprobados++;
      } else {
        $suspensos++;
      }

      if ($mediaAlumno > $max['nota']) {
        $max = ['alumno' => $alumno, 'nota' => number_format($mediaAlumno), 2, ','];
      }

      if ($mediaAlumno < $min['nota']) {
        $min = ['alumno' => $alumno, 'nota' => number_format($mediaAlumno), 2, ','];
      }

      $mediaAsignatura = ($numAlumnos > 0) ? $sumaNotas / $numAlumnos : -1;

      // Asignamos todos los resultados al array a devolver
      $resultado[$asignatura]['media'] = number_format(round($mediaAsignatura, 1), 2, ',');
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
  $listados = [
      'apruebanTodo' => [], // Alumnos que han aprobado todas. (div verde)
      'suspendenAlguna' => [], // Alumnos que han suspendido al menos una asignatura. (div amarillo)
      'promocionan' => [],// Alumnos que promocionan (alumnos que han suspendido como máximo una asignatura). (div azul)
      'noPromocionan' => [] // Alumnos que no promocionan (alumnos que han suspendido 2 o más asignaturas). (div rojo)
  ];

  $boletin = [];

  // Recopilamos la información de cada alumno en todas las asignaturas
  foreach ($datos as $asignatura => $alumnado) {
    foreach ($alumnado as $alumno => $nota) {
      $media = calcularMedia($nota);
      if (!isset($boletin[$alumno])) {
        $boletin[$alumno] = ['suspensas' => 0];
      }

      if ($media <= 5) {
        $boletin[$alumno]['suspensas']++;
      }
    }
  }

  // Clasificamos a los alumnos según sus resultados
  foreach ($boletin as $alumno => $notas) {

    if ($notas['suspensas'] == 0) {
      // Aprueban todas
      $listados['apruebanTodo'][] = $alumno;
      $listados['promocionan'][] = $alumno;
    } else {
      // Suspenden 1 o mas
      $listados['suspendenAlguna'][] = $alumno;

      if ($notas['suspensas'] > 1) {
        // Suspenden 2 o mas (no promocionan)
        $listados['noPromocionan'][] = $alumno;
      } else {
        // Suspenden solamente 1 (promocionan)
        $listados['promocionan'][] = $alumno;
      }
    }
  }

  // Ordenamos los listados alfabéticamente
  sort($listados['apruebanTodo']);
  sort($listados['suspendenAlguna']);
  sort($listados['promocionan']);
  sort($listados['noPromocionan']);

  return $listados;
}

/**
 * Función que calcula la media de un array numérico.
 * @param array $datos
 * @return float
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