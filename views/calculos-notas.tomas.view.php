<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Calcula notas</h1>
</div>

<!-- Row con la tabla -->
<div class="row">
  <?php
  if (isset($data['resultado'])) {
    ?>
      <div class="col-12">
          <div class="card shadow mb-4">
              <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                  <h6 class="m-0 font-weight-bold text-primary">Datos asignaturas</h6>
              </div>
              <div class="card-body">
                  <table class="table table-striped">
                      <thead>
                      <tr>
                          <th>Asignatura</th>
                          <th>Nota media</th>
                          <th>Nº de suspensos</th>
                          <th>Nº de aprobados</th>
                          <th>Nota max</th>
                          <th>Nota min</th>
                      </tr>
                      </thead>

                      <tbody>
                      <?php foreach ($data['resultado'] as $asignatura => $datos) { ?>
                          <tr>
                              <td><?php echo ucwords($asignatura) ?></td>
                              <td><?php echo $datos['media']; ?></td>
                              <td><?php echo $datos['aprobados'] ?></td>
                              <td><?php echo $datos['suspensos'] ?></td>
                              <td><?php echo $datos['max']['alumno'] ?>
                                  : <?php echo number_format($datos['max']['nota'], 2, ',') ?></td>
                              <td><?php echo $datos['min']['alumno'] ?>: <?php echo $datos['min']['nota'] ?></td>
                          </tr>
                        <?php
                      }
                      ?>
                      </tbody>
                  </table>
              </div>
          </div>
      </div>
    <?php
  }
  ?>
</div>


<!-- Row con listado de aprobados, con alguna suspensa y con los que no promocionan -->
<div class="row">
  <?php
  if (isset($data['listados'])) {
    ?>

      <div class="col-12 col-lg-6">
          <div class="alert alert-success card shadow mb-4">
              <h6 class="m-0 font-weight-bold">Aprobados</h6>
              <div class="card-body"> <!-- Card con los aprobados -->
                  <ul>
                    <?php
                    foreach ($data['listados']['apruebanTodo'] as $alumno) {
                      ?>
                        <li><?php echo $alumno; ?></li>
                      <?php
                    }
                    ?>
                  </ul>
              </div>
          </div>
      </div>


      <div class="col-12 col-lg-6">
          <div class="alert alert-warning card shadow mb-4">
              <h6 class="m-0 font-weight-bold">Suspenden alguna</h6>
              <div class="card-body"> <!-- Card con los que suspenden alguna -->
                  <ul>
                    <?php
                    foreach ($data['listados']['suspendenAlguna'] as $alumno) {
                      ?>
                        <li><?php echo $alumno; ?></li>
                      <?php
                    }
                    ?>
                  </ul>
              </div>
          </div>
      </div>


      <div class="col-12 col-lg-6">
          <div class="alert alert-danger card shadow mb-4">
              <h6 class="m-0 font-weight-bold">No promocionan</h6>
              <div class="card-body"> <!-- Card con los que no promocionan -->
                  <ul>
                    <?php
                    foreach ($data['listados']['noPromocionan'] as $alumno) {
                      ?>
                        <li><?php echo $alumno; ?></li>
                      <?php
                    }
                    ?>
                  </ul>
              </div>
          </div>
      </div>

    <?php
  }
  ?>
</div>


<!-- Row con el input -->
<div class="row">
    <div class="col-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Calculadora de notas</h6>
            </div>
            <div class="card-body">
                <div class="mb-3 col-12">

                    <!-- Formulario -->
                    <form action="" method="post">
                        <label for="textarea">Inserte un bloque de texto en formato JSON</label>

                        <textarea class="form-control" id="input" name="input"
                                  rows="3"><?php echo $data['texto'] ?? ''; ?></textarea>

                        <!-- Mostramos los errores en caso de que los haya -->
                        <p class="text-danger small">
                          <?php
                          // echo $data['errores']['texto'] ?? '';
                          if (isset($data['errores'])) {
                            foreach ($data['errores']['texto'] as $key => $value) {
                              echo $value . "<br>";
                            }
                          }

                          ?>
                        </p>

                        <input type="submit" value="Enviar" name="enviar" class="btn btn-primary">
                </div>
                </form>

            </div>
        </div>
    </div>
</div>
