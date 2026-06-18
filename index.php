<?php
session_start();
include("config/conexion.php");
 
$error_login    = "";
$error_registro = "";
 
/* ── REGISTRO ─────────────────────────────── */
if (isset($_POST['registrar'])) {
    if ($_POST['password'] !== $_POST['confirm_password']) {
        $error_registro = "Las contraseñas no coinciden";
    } else {
        $nombres    = $_POST['nombres'];
        $apellidos  = $_POST['apellidos'];
        $tipo_doc   = $_POST['tipo_doc'];
        $numero_doc = $_POST['numero_doc'];
        $password   = password_hash($_POST['password'], PASSWORD_DEFAULT);
 
        $check = $conn->query("INSERT INTO usuarios
    (nombres,apellidos,tipo_doc,numero_doc,password,rol)
    VALUES ('$nombres','$apellidos','$tipo_doc','$numero_doc','$password','acudiente')");

/* Obtener ID generado */
$usuario_id = $conn->insert_id;

/* Crear sesión automáticamente */
$_SESSION['usuario'] = [
    'id' => $usuario_id,
    'nombres' => $nombres,
    'apellidos' => $apellidos,
    'tipo_doc' => $tipo_doc,
    'numero_doc' => $numero_doc,
    'rol' => 'acudiente'
];

/* Enviar al módulo de acudiente */
header("Location: modulos/acudiente/registro.php");
exit;
        }
    }

 
/* ── LOGIN ────────────────────────────────── */
if (isset($_POST['login'])) {
    $numero_doc = $_POST['numero_doc_login'];
    $password   = $_POST['password_login'];
    $result     = $conn->query("SELECT * FROM usuarios WHERE numero_doc='$numero_doc'");
 
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['usuario'] = $user;
            header("Location: dashboard.php");
            exit;
        } else {
            $error_login = "Contraseña incorrecta";
        }
    } else {
        $error_login = "Documento no registrado";
    }
}
 
/* ── REDIRECCIÓN SI YA ESTÁ LOGUEADO ──────── */
if (isset($_SESSION['usuario'])) {
    header("Location: dashboard.php");
    exit;
}
 
include("templates/header.php");
?>
 
<!-- Botones de acceso -->
<div class="container mt-5 text-center">
    <h1 class="fw-bold">Sistema de Matrículas</h1>
    <p class="text-muted">Accede o regístrate para continuar</p>
    <button class="btn btn-success me-2"
            data-bs-toggle="modal" data-bs-target="#modalRegistro">Registrarse</button>
    <button class="btn btn-outline-success"
            data-bs-toggle="modal" data-bs-target="#modalLogin">Iniciar Sesión</button>
</div>
 
<!-- Especialidades técnicas -->
<div class="container mt-5">
    <div class="row text-center mb-4">
        <h2 class="fw-bold">Nuestras Especialidades Técnicas</h2>
        <p class="text-muted">Formación articulada con el SENA</p>
    </div>
    <div class="row">
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <h4 class="card-title text-success">💻 Técnico en Desarrollo de Software</h4>
                    <p>Forma estudiantes con capacidades para diseñar, desarrollar y mantener
                       aplicaciones web y móviles usando lenguajes modernos y bases de datos.</p>
                    <ul>
                        <li>Programación estructurada y orientada a objetos</li>
                        <li>Desarrollo de aplicaciones web</li>
                        <li>Bases de datos</li>
                        <li>Lógica de programación</li>
                        <li>Proyectos de software</li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <h4 class="card-title text-success">🌱 Técnico en Producción Agropecuaria</h4>
                    <p>Fortalece competencias en producción agrícola y pecuaria, promoviendo el
                       desarrollo sostenible y las buenas prácticas del sector.</p>
                    <ul>
                        <li>Producción agrícola sostenible</li>
                        <li>Manejo de cultivos</li>
                        <li>Producción pecuaria</li>
                        <li>Cuidado del medio ambiente</li>
                        <li>Tecnificación del campo</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
 
<!-- Modal Registro -->
<div class="modal fade" id="modalRegistro">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5>Registro</h5>
                    <button class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <?php if ($error_registro): ?>
                        <div class="alert alert-danger"><?= $error_registro ?></div>
                    <?php endif; ?>
                    <input class="form-control mb-3" name="nombres"   placeholder="Nombres"   required>
                    <input class="form-control mb-3" name="apellidos" placeholder="Apellidos" required>
                    <select class="form-control mb-3" name="tipo_doc" required>
                        <option value="">Tipo de documento</option>
                        <option value="CC">Cédula</option>
                        <option value="TI">Tarjeta de identidad</option>
                        <option value="CE">Cédula extranjera</option>
                    </select>
                    <input class="form-control mb-3" name="numero_doc" placeholder="Número de documento" required>
                    <input type="password" class="form-control mb-3" name="password"
                           id="password" placeholder="Contraseña" required>
                    <input type="password" class="form-control mb-2" name="confirm_password"
                           id="confirm_password" placeholder="Confirmar contraseña" required>
                    <small id="mensajePassword"></small>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-success" name="registrar">Registrarse</button>
                </div>
            </form>
        </div>
    </div>
</div>
 
<!-- Modal Login -->
<div class="modal fade" id="modalLogin">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5>Iniciar Sesión</h5>
                    <button class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <?php if ($error_login): ?>
                        <div class="alert alert-danger"><?= $error_login ?></div>
                    <?php endif; ?>
                    <input class="form-control mb-3" name="numero_doc_login" placeholder="Documento" required>
                    <input type="password" class="form-control mb-3"
                           name="password_login" placeholder="Contraseña" required>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-success" name="login">Ingresar</button>
                </div>
            </form>
        </div>
    </div>
</div>
 
<script>
    const password        = document.getElementById("password");
    const confirmPassword = document.getElementById("confirm_password");
    const mensaje         = document.getElementById("mensajePassword");
    const params          = new URLSearchParams(window.location.search);
 
    function validarPassword() {
        if (!confirmPassword.value) return;
        if (password.value !== confirmPassword.value) {
            mensaje.textContent = "❌ No coinciden";
            mensaje.style.color = "red";
        } else {
            mensaje.textContent = "✅ Coinciden";
            mensaje.style.color = "green";
        }
    }
 
    if (password && confirmPassword) {
        password.addEventListener("keyup", validarPassword);
        confirmPassword.addEventListener("keyup", validarPassword);
    }
 
    const modal = params.get("modal");
    if (modal === "login")    new bootstrap.Modal(document.getElementById("modalLogin")).show();
    if (modal === "registro") new bootstrap.Modal(document.getElementById("modalRegistro")).show();
</script>
 
<?php include("templates/footer.php"); ?>
 

