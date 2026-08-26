<?php

include("../../config/conexion.php");

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


/* =========================================================
   VERIFICAR SESIÓN
========================================================= */

if (!isset($_SESSION['usuario'])) {

    header("Location: ../../index.php?modal=login");
    exit;

}


/* =========================================================
   VERIFICAR ADMINISTRADOR / INSTRUCTOR
========================================================= */

/*
   Si todavía no tienes configurado el rol "admin",
   puedes dejar temporalmente esta parte comentada.

   Cuando tengas el administrador funcionando,
   se recomienda activarla.
*/

/*
if (
    $_SESSION['usuario']['rol'] !== 'admin' &&
    $_SESSION['usuario']['rol'] !== 'instructor'
) {

    header("Location: ../../dashboard.php");
    exit;

}
*/


/* =========================================================
   ELIMINAR ESTUDIANTE
========================================================= */

if (
    isset($_GET['eliminar']) &&
    is_numeric($_GET['eliminar'])
) {

    $id = (int) $_GET['eliminar'];


    /* Buscar documentos */

    $stmt = $conn->prepare(
        "SELECT compromiso,
                tratamiento,
                imagen,
                doc_aprendiz,
                registro_civil,
                carta,
                doc_acudiente,
                eps
         FROM estudiantes
         WHERE id = ?"
    );

    $stmt->bind_param("i", $id);

    $stmt->execute();

    $resultado = $stmt->get_result();

    $estudianteEliminar =
        $resultado->fetch_assoc();

    $stmt->close();


    /* Eliminar archivos físicos */

    if ($estudianteEliminar) {

        foreach ($estudianteEliminar as $archivo) {

            if (!empty($archivo)) {

                $ruta =
                    __DIR__ .
                    "/../estudiante/documentos/" .
                    $archivo;

                if (file_exists($ruta)) {
                    unlink($ruta);
                }

            }

        }

    }


    /* Eliminar registro */

    $stmt = $conn->prepare(
        "DELETE FROM estudiantes WHERE id = ?"
    );

    $stmt->bind_param("i", $id);

    $stmt->execute();

    $stmt->close();


    header(
        "Location: control.php?eliminado=1"
    );

    exit;
}


/* =========================================================
   CONSULTAR ESTUDIANTE ESPECÍFICO
========================================================= */

$estudiante = null;

if (
    isset($_GET['id']) &&
    is_numeric($_GET['id'])
) {

    $id = (int) $_GET['id'];


    $sql = "

        SELECT

            e.id,

            e.nombres,
            e.apellidos,
            e.tipo_documento,
            e.numero_documento,
            e.grado,
            e.especialidad,
            e.fecha_registro,

            e.compromiso,
            e.tratamiento,
            e.imagen,
            e.doc_aprendiz,
            e.registro_civil,
            e.carta,
            e.doc_acudiente,
            e.eps,

            a.nombres AS acudiente_nombres,
            a.apellidos AS acudiente_apellidos,
            a.tipo_doc AS acudiente_tipo_doc,
            a.numero_doc AS acudiente_numero_doc,
            a.telefono AS acudiente_telefono,
            a.correo AS acudiente_correo,
            a.direccion AS acudiente_direccion,
            a.municipio AS acudiente_municipio,
            a.departamento AS acudiente_departamento

        FROM estudiantes e

        LEFT JOIN acudientes a
            ON e.usuario_id = a.usuario_id

        WHERE e.id = ?

        LIMIT 1

    ";


    $stmt = $conn->prepare($sql);

    $stmt->bind_param("i", $id);

    $stmt->execute();

    $resultado = $stmt->get_result();

    $estudiante =
        $resultado->fetch_assoc();

    $stmt->close();
}


/* =========================================================
   LISTA DE ESTUDIANTES
========================================================= */

$estudiantes = [];

if (!$estudiante) {

    $sql = "

        SELECT

            e.id,
            e.nombres,
            e.apellidos,
            e.grado,
            e.especialidad,
            e.fecha_registro

        FROM estudiantes e

        ORDER BY e.fecha_registro DESC

    ";


    $resultado = $conn->query($sql);


    if ($resultado) {

        while (
            $fila = $resultado->fetch_assoc()
        ) {

            $estudiantes[] = $fila;

        }

    }

}

?>


<!DOCTYPE html>

<html lang="es">

<head>

<meta charset="UTF-8">

<meta
name="viewport"
content="width=device-width, initial-scale=1"
>

<title>
Control de estudiantes
</title>


<link
href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
rel="stylesheet"
>


<style>

/* =========================================================
   GENERAL
========================================================= */

body {

    margin: 0;

    font-family:
        Arial,
        Helvetica,
        sans-serif;

    background: #f4f6fb;

    color: #111;

}


/* =========================================================
   NAVBAR
========================================================= */

.navbar-custom {

    background: #101b93;

    padding:
        22px 6%;

    display: flex;

    justify-content:
        space-between;

    align-items:
        center;

}


.logo {

    color: white;

    font-size: 34px;

    font-weight: bold;

}


.nav-links {

    display: flex;

    gap: 35px;

}


.nav-links a {

    color: white;

    text-decoration: none;

    font-size: 22px;

}


.nav-links a:hover {

    text-decoration: underline;

}


/* =========================================================
   CONTENEDOR
========================================================= */

.page-container {

    padding:
        45px 3%;

}


/* =========================================================
   TITULO
========================================================= */

.page-title {

    font-size: 46px;

    font-weight: bold;

    margin-bottom: 45px;

}


/* =========================================================
   LISTA
========================================================= */

.student-list {

    width: 100%;

}


.student-row {

    background:
        linear-gradient(
            180deg,
            #e0e1e4,
            #cfd0d3
        );

    min-height: 110px;

    display: flex;

    align-items: center;

    justify-content:
        space-between;

    padding:
        0 45px;

    border-bottom:
        2px solid #aaa;

    box-shadow:
        0 5px 15px
        rgba(0,0,0,.10);

}


.student-name {

    color: #555;

    font-size: 32px;

    font-style: italic;

    text-decoration: none;

}


.student-name:hover {

    color: #101b93;

}


.delete-btn {

    border: none;

    background: transparent;

    color: #d33;

    font-size: 35px;

    cursor: pointer;

}


.delete-btn:hover {

    color: #900;

    transform: scale(1.1);

}


/* =========================================================
   DETALLE
========================================================= */

.student-header {

    display: flex;

    justify-content:
        space-between;

    align-items:
        flex-start;

    margin-bottom: 30px;

}


.student-header h1 {

    font-size: 38px;

    font-weight: bold;

}


.specialty {

    font-size: 28px;

    color: #555;

    font-style: italic;

}


/* =========================================================
   GRID
========================================================= */

.detail-grid {

    display: grid;

    grid-template-columns:
        3fr 2fr;

    gap: 45px;

}


/* =========================================================
   TARJETAS
========================================================= */

.detail-card {

    background:
        #d5d6d9;

    border-radius:
        22px;

    padding:
        30px 40px;

    box-shadow:
        0 8px 25px
        rgba(0,0,0,.18);

}


.detail-card h2 {

    font-size: 28px;

    margin-bottom: 30px;

}


.document-section {

    margin-bottom: 30px;

}


.document-section h3 {

    font-size: 26px;

    margin-bottom: 15px;

}


.document-link {

    display: block;

    color: #111;

    font-size: 22px;

    margin-bottom: 12px;

    text-decoration:
        underline;

}


.document-link:hover {

    color: #101b93;

}


.data-item {

    font-size: 22px;

    margin-bottom: 35px;

    font-style: italic;

}


.data-label {

    font-weight: bold;

    font-style: normal;

}


/* =========================================================
   BOTON VOLVER
========================================================= */

.back-btn {

    display: inline-block;

    margin-bottom: 30px;

    padding:
        12px 22px;

    border-radius:
        10px;

    background:
        #101b93;

    color: white;

    text-decoration: none;

    font-size: 18px;

}


.back-btn:hover {

    background:
        #00084f;

    color: white;

}


/* =========================================================
   RESPONSIVE
========================================================= */

@media (max-width: 900px) {

    .detail-grid {

        grid-template-columns: 1fr;

    }

    .student-header {

        flex-direction:
            column;

        gap: 10px;

    }

    .nav-links {

        gap: 15px;

    }

    .nav-links a {

        font-size: 16px;

    }

    .logo {

        font-size: 25px;

    }

}

</style>

</head>


<body>


<!-- =====================================================
     NAVBAR
===================================================== -->

<nav class="navbar-custom">

    <div class="logo">
        ApplyGo
    </div>

    <div class="nav-links">

        <a href="../../index.php">
            Inicio
        </a>

        <a href="../../index.php?modal=login">
            Iniciar sesión
        </a>

        <a href="../../index.php?modal=registro">
            Registrarse
        </a>

    </div>

</nav>


<div class="page-container">


<?php if (!$estudiante): ?>


<!-- =====================================================
     LISTA DE ESTUDIANTES
===================================================== -->

<h1 class="page-title">
    Estudiantes registrados
</h1>


<?php if (isset($_GET['eliminado'])): ?>

<div class="alert alert-success">

    El estudiante fue eliminado correctamente.

</div>

<?php endif; ?>


<div class="student-list">


<?php if (empty($estudiantes)): ?>

<div class="alert alert-info">

    Todavía no hay estudiantes registrados.

</div>

<?php else: ?>


<?php foreach ($estudiantes as $fila): ?>


<div class="student-row">


<a
href="control.php?id=<?= (int)$fila['id'] ?>"
class="student-name"
>

<?= htmlspecialchars(
    $fila['nombres'] .
    " " .
    $fila['apellidos']
) ?>

</a>


<a
href="control.php?eliminar=<?= (int)$fila['id'] ?>"
class="delete-btn"
title="Eliminar estudiante"
onclick="
return confirm(
'¿Estás seguro de que deseas eliminar este estudiante? También se eliminarán sus documentos.'
);
"
>

🗑️

</a>


</div>


<?php endforeach; ?>


<?php endif; ?>

</div>


<?php else: ?>


<!-- =====================================================
     DETALLE DEL ESTUDIANTE
===================================================== -->


<a
href="control.php"
class="back-btn"
>
← Volver a estudiantes
</a>


<div class="student-header">


<div>

<h1>

<?= htmlspecialchars(
    $estudiante['nombres'] .
    " " .
    $estudiante['apellidos']
) ?>

</h1>


<p class="mb-0">

<strong>
Documento:
</strong>

<?= htmlspecialchars(
    $estudiante['numero_documento']
) ?>

&nbsp;&nbsp;

<strong>
Grado:
</strong>

<?= htmlspecialchars(
    $estudiante['grado']
) ?>

</p>

</div>


<div class="specialty">

<?= htmlspecialchars(
    $estudiante['especialidad']
) ?>

</div>


</div>


<div class="detail-grid">


<!-- =====================================================
     DOCUMENTOS
===================================================== -->

<div class="detail-card">


<h2>
Estudiante
</h2>


<div class="document-section">


<h3>
Documentos del estudiante
</h3>


<?php

$documentosEstudiante = [

    'compromiso'
        => 'Formato de compromiso del aprendiz',

    'tratamiento'
        => 'Formato de tratamiento de datos para menor de edad',

    'imagen'
        => 'Formato de autorización para uso de imagen',

    'doc_aprendiz'
        => 'Documento de identidad del aprendiz',

    'registro_civil'
        => 'Registro civil del aprendiz'

];

?>


<?php foreach (
    $documentosEstudiante
    as $campo => $nombre
): ?>


<?php if (
    !empty($estudiante[$campo])
): ?>


<a
href="../estudiante/documentos/<?= urlencode($estudiante[$campo]) ?>"
target="_blank"
class="document-link"
>

<?= htmlspecialchars($nombre) ?>

</a>


<?php endif; ?>


<?php endforeach; ?>


</div>


<div class="document-section">


<h3>
Acudiente
</h3>


<?php

$documentosAcudiente = [

    'carta'
        => 'Formato de carta juramentada',

    'doc_acudiente'
        => 'Documento de identidad del acudiente',

    'eps'
        => 'Certificado de EPS'

];

?>


<?php foreach (
    $documentosAcudiente
    as $campo => $nombre
): ?>


<?php if (
    !empty($estudiante[$campo])
): ?>


<a
href="../estudiante/documentos/<?= urlencode($estudiante[$campo]) ?>"
target="_blank"
class="document-link"
>

<?= htmlspecialchars($nombre) ?>

</a>


<?php endif; ?>


<?php endforeach; ?>


</div>


</div>


<!-- =====================================================
     DATOS DEL ACUDIENTE
===================================================== -->

<div class="detail-card">


<h2>
Datos del acudiente
</h2>


<div class="data-item">

<span class="data-label">
Nombres y apellidos del acudiente:
</span>

<br>

<?= htmlspecialchars(
    ($estudiante['acudiente_nombres'] ?? '') .
    " " .
    ($estudiante['acudiente_apellidos'] ?? '')
) ?>

</div>


<div class="data-item">

<span class="data-label">
Número de documento:
</span>

<br>

<?= htmlspecialchars(
    $estudiante['acudiente_numero_doc'] ?? ''
) ?>

</div>


<div class="data-item">

<span class="data-label">
Número de teléfono:
</span>

<br>

<?= htmlspecialchars(
    $estudiante['acudiente_telefono'] ?? ''
) ?>

</div>


<div class="data-item">

<span class="data-label">
Correo electrónico:
</span>

<br>

<?= htmlspecialchars(
    $estudiante['acudiente_correo'] ?? ''
) ?>

</div>


<div class="data-item">

<span class="data-label">
Dirección:
</span>

<br>

<?= htmlspecialchars(
    $estudiante['acudiente_direccion'] ?? ''
) ?>

</div>


<div class="data-item">

<span class="data-label">
Municipio:
</span>

<br>

<?= htmlspecialchars(
    $estudiante['acudiente_municipio'] ?? ''
) ?>

</div>


<div class="data-item">

<span class="data-label">
Departamento:
</span>

<br>

<?= htmlspecialchars(
    $estudiante['acudiente_departamento'] ?? ''
) ?>

</div>


</div>


</div>


<?php endif; ?>


</div>

</body>

</html>