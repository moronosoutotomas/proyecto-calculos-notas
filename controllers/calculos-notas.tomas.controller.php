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
    $data['listados'] = mostrarListado($data['resultado']);
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


  // iteración asignaturas
  foreach ($descodificado as $asignatura => $alumnado) {
    $mediaAsignatura = 0;
    $sumaNotas = 0;
    $numAlumnos = 0;
    $suspensos = 0;
    $aprobados = 0;
    $max = ['alumno' => '', 'nota' => -1];
    $min = ['alumno' => '', 'nota' => 11];

    // iteración alumnos
    foreach ($alumnado as $alumno => $boletinNotas) {
      $mediaAlumno = calcularMedia($boletinNotas);
      $resultado[$asignatura]['alumnos'][$alumno] = $mediaAlumno;
      $sumaNotas += $mediaAlumno;
      $numAlumnos++;

      if ($mediaAlumno > 5) {
        $aprobados++;
      } else {
        $suspensos++;
      }

      if ($mediaAlumno > $max['nota']) {
        $max = ['alumno' => $alumno, 'nota' => number_format($mediaAlumno), 2, ','];
      } else if ($mediaAlumno < $min['nota']) {
        $min = ['alumno' => $alumno, 'nota' => number_format($mediaAlumno), 2, ','];
      }
      $mediaAsignatura = ($numAlumnos > 0) ? $sumaNotas / $numAlumnos : -1;

      // asignamos resultados por alumno
      $resultado[$asignatura]['media'] = number_format($mediaAsignatura, 2, ',');
      $resultado[$asignatura]['aprobados'] = $aprobados;
      $resultado[$asignatura]['suspensos'] = $suspensos;
      $resultado[$asignatura]['max'] = $max;
      $resultado[$asignatura]['min'] = $min;
    }
  } // foreach asignaturas

//  var_dump($resultado);
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
      'apruebanTodo' => [],
      'suspendenAlguna' => [],
      'noPromocionan' => []
  ];

  $alumnosAsignaturas = [];

  // Primero, recopilamos la información de cada alumno en todas las asignaturas
  foreach ($datos as $asignatura => $infoAsignatura) {
    foreach ($infoAsignatura['alumnos'] as $alumno => $nota) {
      if (!isset($alumnosAsignaturas[$alumno])) {
        $alumnosAsignaturas[$alumno] = ['aprobadas' => 0, 'suspensas' => 0];
      }

      if ($nota >= 5) {
        $alumnosAsignaturas[$alumno]['aprobadas']++;
      } else {
        $alumnosAsignaturas[$alumno]['suspensas']++;
      }
    }
  }

  // Ahora clasificamos a los alumnos según sus resultados
  foreach ($alumnosAsignaturas as $alumno => $info) {
    if ($info['suspensas'] == 0) {
      $listados['apruebanTodo'][] = $alumno;
    } elseif ($info['suspensas'] <= 2) {
      $listados['suspendenAlguna'][] = $alumno;
    } else {
      $listados['noPromocionan'][] = $alumno;
    }
  }

  // Ordenamos los listados alfabéticamente
  sort($listados['apruebanTodo']);
  sort($listados['suspendenAlguna']);
  sort($listados['noPromocionan']);

  return $listados;
}

/**
 * Función que calcula la media de un array numérico dado comprobando antes si es numérico
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