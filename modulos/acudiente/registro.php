<?php
include("../../config/conexion.php");
if (session_status() === PHP_SESSION_NONE) session_start();
 
// Redirigir si no hay sesión activa
if (!isset($_SESSION['usuario'])) {
    header("Location: ../../index.php?modal=login");
    exit;
}
 
$usuario_id = (int) $_SESSION['usuario']['id'];
$mensaje    = "";
 
$resultado = $conn->query(
    "SELECT * FROM acudientes
     WHERE usuario_id = $usuario_id
     LIMIT 1"
);

$acudiente = $resultado ? $resultado->fetch_assoc() : null;
$esEdicion = (bool)$acudiente;

/*
Si es la primera vez que entra,
traer datos desde usuarios
*/
if (!$acudiente) {

    $usuario = $conn->query(
        "SELECT nombres, apellidos, tipo_doc, numero_doc
         FROM usuarios
         WHERE id = $usuario_id
         LIMIT 1"
    );

    if ($usuario && $usuario->num_rows > 0) {
        $acudiente = $usuario->fetch_assoc();
    }
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombres     = $_POST['nombres'];
    $apellidos   = $_POST['apellidos'];
    $tipo_doc    = $_POST['tipo_doc'];
    $numero_doc  = $_POST['numero_doc'];
    $telefono    = $_POST['telefono'];
    $correo      = $_POST['correo'];
    $direccion   = $_POST['direccion'];
    $municipio   = $_POST['municipio'];
    $departamento = $_POST['departamento'];
 
    if ($esEdicion) {
        $sql = "UPDATE acudientes SET
                    nombres='$nombres', apellidos='$apellidos',
                    tipo_doc='$tipo_doc', numero_doc='$numero_doc',
                    telefono='$telefono', correo='$correo',
                    direccion='$direccion', municipio='$municipio',
                    departamento='$departamento'
                WHERE usuario_id = $usuario_id";
        $conn->query($sql);
        $mensaje = "Tus datos fueron actualizados correctamente.";
    } else {
        $sql = "INSERT INTO acudientes
                (usuario_id,nombres,apellidos,tipo_doc,numero_doc,
                 telefono,correo,direccion,municipio,departamento)
                VALUES
                ($usuario_id,'$nombres','$apellidos','$tipo_doc','$numero_doc',
                 '$telefono','$correo','$direccion','$municipio','$departamento')";
        $conn->query($sql);
        header("Location: ../estudiante/registro.php");
        exit;
    }
}
 
include("../../templates/header.php");
?>
 
<main class="page-shell">
  <div class="container">
    <section class="page-hero">
      <h2 class="h3 fw-bold">Datos del acudiente</h2>
      <p><?php echo $esEdicion
          ? 'Consulta y edita tu información cuando lo necesites.'
          : 'Completa tu perfil para continuar con el registro del estudiante.'; ?>
      </p>
      <?php if ($esEdicion): ?>
        <div class="d-flex gap-2 mt-3">
          <a class="btn btn-outline-secondary" href="../estudiante/lista.php">Ver estudiantes</a>
          <a class="btn btn-success"           href="../estudiante/registro.php">Registrar estudiante</a>
        </div>
      <?php endif; ?>
    </section>
 
    <?php if ($mensaje): ?>
      <div class="alert alert-success"><?php echo htmlspecialchars($mensaje); ?></div>
    <?php endif; ?>
 
    <div class="row justify-content-center">
      <div class="col-lg-8">
        <div class="module-card card">
          <div class="card-header">
            <h3 class="h5 mb-0">Información personal</h3>
          </div>
          <div class="card-body">
            <form method="POST" class="row g-3">


              <div class="col-md-6">
                <label class="form-label">Nombres</label>
                <input class="form-control" name="nombres"
                       value="<?php echo htmlspecialchars($acudiente['nombres'] ?? ''); ?>" required>
              </div>
              <div class="col-md-6">
                <label class="form-label">Apellidos</label>
                <input class="form-control" name="apellidos"
                       value="<?php echo htmlspecialchars($acudiente['apellidos'] ?? ''); ?>" required>
              </div>
              <div class="col-md-4">
                <label class="form-label">Tipo de documento</label>
                <select class="form-select" name="tipo_doc">
                  <option value="">Selecciona</option>
                  <option value="CC" <?php echo ($acudiente['tipo_doc']??'')==='CC'?'selected':''; ?>>Cédula</option>
                  <option value="TI" <?php echo ($acudiente['tipo_doc']??'')==='TI'?'selected':''; ?>>Tarjeta de identidad</option>
                  <option value="CE" <?php echo ($acudiente['tipo_doc']??'')==='CE'?'selected':''; ?>>Cédula extranjera</option>
                </select>
              </div>
              <div class="col-md-4">
                <label class="form-label">Número de documento</label>
                <input class="form-control" name="numero_doc"
                       value="<?php echo htmlspecialchars($acudiente['numero_doc'] ?? ''); ?>">
              </div>
              <div class="col-md-4">
                <label class="form-label">Teléfono</label>
                <input class="form-control" name="telefono"
                       value="<?php echo htmlspecialchars($acudiente['telefono'] ?? ''); ?>">
              </div>
              <div class="col-12">
                <label class="form-label">Correo electrónico</label>
                <input type="email" class="form-control" name="correo"
                       value="<?php echo htmlspecialchars($acudiente['correo'] ?? ''); ?>">
              </div>
              <div class="col-12">
                <label class="form-label">Dirección</label>
                <input class="form-control" name="direccion"
                       value="<?php echo htmlspecialchars($acudiente['direccion'] ?? ''); ?>">
              </div>
              <div class="col-md-6">
                <label class="form-label">Departamento</label>
                <select class="form-select" id="departamento" name="departamento"
                        data-current="<?php echo htmlspecialchars($acudiente['departamento'] ?? ''); ?>" required>
                  <option value="">Cargando...</option>
                </select>
              </div>
              <div class="col-md-6">
                <label class="form-label">Municipio</label>
                <select class="form-select" id="municipio" name="municipio"
                        data-current="<?php echo htmlspecialchars($acudiente['municipio'] ?? ''); ?>"
                        required disabled>
                  <option value="">Selecciona primero un departamento</option>
                </select>
              </div>
              <div class="col-12 d-flex justify-content-end">
                <button class="btn btn-success px-4">
                  <?php echo $esEdicion ? 'Guardar cambios' : 'Continuar al estudiante'; ?>
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</main>
<script>

const departamento = document.getElementById("departamento");
const municipio = document.getElementById("municipio");

const departamentoActual =
    departamento.dataset.current || "";

const municipioActual =
    municipio.dataset.current || "";

let ciudadesData = {};

Promise.all([
    fetch("../../assets/data/departments.json"),
    fetch("../../assets/data/cities.json")
])
.then(async ([depRes, cityRes]) => {

    const departamentos = await depRes.json();
    ciudadesData = await cityRes.json();

    console.log("Departamentos:", departamentos);
    console.log("Ciudades:", ciudadesData);

    departamento.innerHTML =
        '<option value="">Seleccione un departamento</option>';

    departamentos.forEach(dep => {

        const option = document.createElement("option");

        option.value = dep;
        option.textContent = dep;

        if(dep === departamentoActual){
            option.selected = true;
        }

        departamento.appendChild(option);

    });

    if(departamentoActual && ciudadesData[departamentoActual]){

        municipio.innerHTML =
            '<option value="">Seleccione un municipio</option>';

        ciudadesData[departamentoActual].forEach(mun => {

            const option = document.createElement("option");

            option.value = mun;
            option.textContent = mun;

            if(mun === municipioActual){
                option.selected = true;
            }

            municipio.appendChild(option);

        });

        municipio.disabled = false;
    }

});

departamento.addEventListener("change", function(){

    municipio.innerHTML =
        '<option value="">Seleccione un municipio</option>';

    const dep = this.value;

    if(ciudadesData[dep]){

        ciudadesData[dep].forEach(mun => {

            const option = document.createElement("option");

            option.value = mun;
            option.textContent = mun;

            municipio.appendChild(option);

        });

        municipio.disabled = false;

    } else {

        municipio.disabled = true;

    }

});

</script>

<?php include("../../templates/footer.php"); ?>