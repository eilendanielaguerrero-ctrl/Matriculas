<?php

$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $carpeta = "documentos/";

    if (!file_exists($carpeta)) {
        mkdir($carpeta, 0777, true);
    }

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

    foreach ($documentos as $campo => $nombreDocumento) {

        if (!isset($_FILES[$campo])) {
            die("Falta el documento: $nombreDocumento");
        }

        $archivo = $_FILES[$campo];

        if ($archivo['error'] != 0) {
            die("Error al cargar: $nombreDocumento");
        }

        $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));

        if ($extension != "pdf") {
            die("$nombreDocumento debe estar en formato PDF.");
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $archivo['tmp_name']);
        finfo_close($finfo);

        if ($mime != "application/pdf") {
            die("$nombreDocumento no es un PDF válido.");
        }

        if ($archivo['size'] > 5 * 1024 * 1024) {
            die("$nombreDocumento supera los 5 MB.");
        }

        $nombreFinal = $campo . "_" . time() . ".pdf";

        move_uploaded_file(
            $archivo['tmp_name'],
            $carpeta . $nombreFinal
        );
    }

    $mensaje = "Registro realizado correctamente.";
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Registro de Estudiantes</title>

<style>

body{
    font-family: Arial, sans-serif;
    background:#f5f5f5;
    padding:20px;
}

.container{
    max-width:900px;
    margin:auto;
    background:white;
    padding:25px;
    border-radius:10px;
    box-shadow:0 0 10px rgba(0,0,0,.1);
}

h1{
    text-align:center;
    color:#0d6efd;
}

fieldset{
    margin-bottom:20px;
    border:1px solid #ccc;
    padding:20px;
    border-radius:8px;
}

legend{
    font-weight:bold;
    color:#0d6efd;
}

input, select{
    width:100%;
    padding:10px;
    margin-top:5px;
    margin-bottom:15px;
}

button{
    width:100%;
    padding:12px;
    background:#0d6efd;
    color:white;
    border:none;
    border-radius:5px;
    font-size:16px;
    cursor:pointer;
}

button:hover{
    background:#0b5ed7;
}

.exito{
    background:#d1e7dd;
    color:#0f5132;
    padding:10px;
    margin-bottom:15px;
    border-radius:5px;
}

</style>
</head>
<body>

<div class="container">

<h1>Registro de Estudiantes</h1>

<?php
if($mensaje!=""){
    echo "<div class='exito'>$mensaje</div>";
}
?>

<form method="POST" enctype="multipart/form-data">

<fieldset>
<legend>Información del Estudiante</legend>

<label>Nombres</label>
<input type="text" name="nombres" required>

<label>Apellidos</label>
<input type="text" name="apellidos" required>

<label>Tipo de Documento</label>
<select name="tipo_documento" required>
    <option value="">Seleccione</option>
    <option>Tarjeta de Identidad</option>
    <option>Cédula de Ciudadanía</option>
</select>

<label>Número de Documento</label>
<input type="text" name="numero_documento" required>

<label>Grado</label>
<select name="grado" required>
    <option value="">Seleccione</option>
    <option>6°</option>
    <option>7°</option>
    <option>8°</option>
    <option>9°</option>
    <option>10°</option>
    <option>11°</option>
</select>

</fieldset>

<fieldset>
<legend>Documentos del Aprendiz</legend>

<label>Compromiso del Aprendiz (PDF)</label>
<input type="file" name="compromiso" accept=".pdf" required>

<label>Tratamiento de Datos Menor de Edad (PDF)</label>
<input type="file" name="tratamiento" accept=".pdf" required>

<label>Autorización para Uso de Imagen (PDF)</label>
<input type="file" name="imagen" accept=".pdf" required>

<label>Documento de Identidad del Aprendiz (PDF)</label>
<input type="file" name="doc_aprendiz" accept=".pdf" required>

<label>Registro Civil del Aprendiz (PDF)</label>
<input type="file" name="registro_civil" accept=".pdf" required>

</fieldset>

<fieldset>
<legend>Documentos del Acudiente</legend>

<label>Carta Juramentada (PDF)</label>
<input type="file" name="carta" accept=".pdf" required>

<label>Documento de Identidad del Acudiente (PDF)</label>
<input type="file" name="doc_acudiente" accept=".pdf" required>

<label>Certificado EPS (PDF)</label>
<input type="file" name="eps" accept=".pdf" required>

</fieldset>

<button type="submit">
Registrar Estudiante
</button>

</form>

</div>

</body>
</html>