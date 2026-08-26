<?php

include("../../config/conexion.php");

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* Verificar sesión */
if (!isset($_SESSION['usuario'])) {
    header("Location: ../../index.php?modal=login");
    exit;
}

$usuario_id = (int) $_SESSION['usuario']['id'];

$mensaje = "";
$error = "";


/* =========================================================
   PROCESAR REGISTRO
========================================================= */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $nombres = trim($_POST['nombres'] ?? '');
    $apellidos = trim($_POST['apellidos'] ?? '');
    $tipo_documento = trim($_POST['tipo_documento'] ?? '');
    $numero_documento = trim($_POST['numero_documento'] ?? '');
    $grado = trim($_POST['grado'] ?? '');
    $especialidad = trim($_POST['especialidad'] ?? '');

    if (
        empty($nombres) ||
        empty($apellidos) ||
        empty($tipo_documento) ||
        empty($numero_documento) ||
        empty($grado) ||
        empty($especialidad)
    ) {

        $error = "Por favor completa todos los datos del estudiante.";

    } else {

        /* =====================================================
           CARPETA DE DOCUMENTOS
        ===================================================== */

        $carpeta = __DIR__ . "/documentos/";

        if (!file_exists($carpeta)) {
            mkdir($carpeta, 0777, true);
        }


        /* =====================================================
           DOCUMENTOS
        ===================================================== */

        $documentos = [
            "compromiso"      => "Compromiso del Aprendiz",
            "tratamiento"     => "Tratamiento de Datos",
            "imagen"          => "Autorización de Imagen",
            "doc_aprendiz"    => "Documento Aprendiz",
            "registro_civil"  => "Registro Civil",
            "carta"           => "Carta Juramentada",
            "doc_acudiente"   => "Documento Acudiente",
            "eps"             => "Certificado EPS"
        ];


        $archivosGuardados = [];


        foreach ($documentos as $campo => $nombreDocumento) {

            if (!isset($_FILES[$campo])) {
                $error = "Falta el documento: $nombreDocumento";
                break;
            }

            $archivo = $_FILES[$campo];

            if ($archivo['error'] !== UPLOAD_ERR_OK) {
                $error = "Error al cargar: $nombreDocumento";
                break;
            }


            /* Verificar extensión */

            $extension = strtolower(
                pathinfo($archivo['name'], PATHINFO_EXTENSION)
            );

            if ($extension !== "pdf") {
                $error = "$nombreDocumento debe estar en formato PDF.";
                break;
            }


            /* Verificar MIME */

            $finfo = finfo_open(FILEINFO_MIME_TYPE);

            $mime = finfo_file(
                $finfo,
                $archivo['tmp_name']
            );

            finfo_close($finfo);

            if ($mime !== "application/pdf") {
                $error = "$nombreDocumento no es un PDF válido.";
                break;
            }


            /* Máximo 5 MB */

            if ($archivo['size'] > 5 * 1024 * 1024) {
                $error = "$nombreDocumento supera los 5 MB.";
                break;
            }


            /* Nombre único */

            $nombreFinal =
                $campo . "_" .
                $usuario_id . "_" .
                time() . "_" .
                uniqid() .
                ".pdf";


            $rutaCompleta = $carpeta . $nombreFinal;


            if (!move_uploaded_file(
                $archivo['tmp_name'],
                $rutaCompleta
            )) {

                $error = "No se pudo guardar $nombreDocumento.";
                break;
            }


            $archivosGuardados[$campo] =
                $nombreFinal;
        }


        /* =====================================================
           GUARDAR EN BASE DE DATOS
        ===================================================== */

        if (empty($error)) {

            $sql = "INSERT INTO estudiantes (

                usuario_id,

                nombres,
                apellidos,
                tipo_documento,
                numero_documento,
                grado,
                especialidad,

                compromiso,
                tratamiento,
                imagen,
                doc_aprendiz,
                registro_civil,
                carta,
                doc_acudiente,
                eps

            ) VALUES (

                ?,
                ?, ?, ?, ?, ?, ?,
                ?, ?, ?, ?, ?, ?, ?, ?

            )";


            $stmt = $conn->prepare($sql);


            if (!$stmt) {

                $error =
                    "Error al preparar el registro: " .
                    $conn->error;

            } else {

                $stmt->bind_param(

                    "isssssssssssssss",

                    $usuario_id,

                    $nombres,
                    $apellidos,
                    $tipo_documento,
                    $numero_documento,
                    $grado,
                    $especialidad,

                    $archivosGuardados['compromiso'],
                    $archivosGuardados['tratamiento'],
                    $archivosGuardados['imagen'],
                    $archivosGuardados['doc_aprendiz'],
                    $archivosGuardados['registro_civil'],
                    $archivosGuardados['carta'],
                    $archivosGuardados['doc_acudiente'],
                    $archivosGuardados['eps']

                );


                if ($stmt->execute()) {

                    $mensaje =
                        "El estudiante fue registrado correctamente.";

                } else {

                    $error =
                        "No se pudo registrar el estudiante: " .
                        $stmt->error;
                }


                $stmt->close();
            }
        }
    }
}


/* =========================================================
   HTML
========================================================= */

?>

<!DOCTYPE html>
<html lang="es">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1">

<title>Registro de Estudiantes</title>

<link
href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
rel="stylesheet">

<style>

body {
    font-family: Arial, sans-serif;
    background: #f5f7fb;
    padding: 30px;
}

.container-form {
    max-width: 900px;
    margin: auto;
    background: white;
    padding: 35px;
    border-radius: 18px;
    box-shadow: 0 10px 35px rgba(0,0,0,.08);
}

h1 {
    text-align: center;
    color: #101b93;
    font-weight: bold;
    margin-bottom: 30px;
}

fieldset {
    margin-bottom: 25px;
    border: 1px solid #ddd;
    padding: 25px;
    border-radius: 15px;
}

legend {
    font-weight: bold;
    color: #101b93;
    padding: 0 10px;
}

.form-control,
.form-select {
    border-radius: 10px;
    padding: 12px;
}

.btn-registrar {
    width: 100%;
    padding: 14px;
    background: #101b93;
    color: white;
    border: none;
    border-radius: 10px;
    font-size: 17px;
    font-weight: bold;
}

.btn-registrar:hover {
    background: #00084f;
}

</style>

</head>

<body>

<div class="container-form">

<h1>Registro de Estudiantes</h1>


<?php if ($mensaje): ?>

<div class="alert alert-success">
    <?= htmlspecialchars($mensaje) ?>
</div>

<?php endif; ?>


<?php if ($error): ?>

<div class="alert alert-danger">
    <?= htmlspecialchars($error) ?>
</div>

<?php endif; ?>


<form method="POST"
      enctype="multipart/form-data">


<!-- =====================================================
     DATOS DEL ESTUDIANTE
===================================================== -->

<fieldset>

<legend>Información del Estudiante</legend>


<label class="form-label">
    Nombres
</label>

<input
    type="text"
    name="nombres"
    class="form-control"
    required
>


<label class="form-label mt-3">
    Apellidos
</label>

<input
    type="text"
    name="apellidos"
    class="form-control"
    required
>


<label class="form-label mt-3">
    Tipo de Documento
</label>

<select
    name="tipo_documento"
    class="form-select"
    required
>

<option value="">
    Seleccione
</option>

<option value="TI">
    Tarjeta de Identidad
</option>

<option value="CC">
    Cédula de Ciudadanía
</option>

</select>


<label class="form-label mt-3">
    Número de Documento
</label>

<input
    type="text"
    name="numero_documento"
    class="form-control"
    required
>


<label class="form-label mt-3">
    Grado
</label>

<select
    name="grado"
    class="form-select"
    required
>

<option value="">
    Seleccione
</option>

<option>6°</option>
<option>7°</option>
<option>8°</option>
<option>9°</option>
<option>10°</option>
<option>11°</option>

</select>


<label class="form-label mt-3">
    Especialidad
</label>

<select
    name="especialidad"
    class="form-select"
    required
>

<option value="">
    Seleccione
</option>

<option value="Técnico en Desarrollo de Software">
    Técnico en Desarrollo de Software
</option>

<option value="Técnico en Producción Agropecuaria">
    Técnico en Producción Agropecuaria
</option>

</select>

</fieldset>


<!-- =====================================================
     DOCUMENTOS DEL APRENDIZ
===================================================== -->

<fieldset>

<legend>
    Documentos del Aprendiz
</legend>


<label class="form-label">
    Compromiso del Aprendiz
</label>

<input
    type="file"
    name="compromiso"
    accept=".pdf"
    class="form-control"
    required
>


<label class="form-label mt-3">
    Tratamiento de Datos para Menor de Edad
</label>

<input
    type="file"
    name="tratamiento"
    accept=".pdf"
    class="form-control"
    required
>


<label class="form-label mt-3">
    Autorización para Uso de Imagen
</label>

<input
    type="file"
    name="imagen"
    accept=".pdf"
    class="form-control"
    required
>


<label class="form-label mt-3">
    Documento de Identidad del Aprendiz
</label>

<input
    type="file"
    name="doc_aprendiz"
    accept=".pdf"
    class="form-control"
    required
>


<label class="form-label mt-3">
    Registro Civil del Aprendiz
</label>

<input
    type="file"
    name="registro_civil"
    accept=".pdf"
    class="form-control"
    required
>

</fieldset>


<!-- =====================================================
     DOCUMENTOS DEL ACUDIENTE
===================================================== -->

<fieldset>

<legend>
    Documentos del Acudiente
</legend>


<label class="form-label">
    Carta Juramentada
</label>

<input
    type="file"
    name="carta"
    accept=".pdf"
    class="form-control"
    required
>


<label class="form-label mt-3">
    Documento de Identidad del Acudiente
</label>

<input
    type="file"
    name="doc_acudiente"
    accept=".pdf"
    class="form-control"
    required
>


<label class="form-label mt-3">
    Certificado EPS
</label>

<input
    type="file"
    name="eps"
    accept=".pdf"
    class="form-control"
    required
>

</fieldset>


<button
    type="submit"
    class="btn-registrar"
>
    Registrar Estudiante
</button>


</form>

</div>

</body>

</html>