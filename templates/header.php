<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Sistema de Matrículas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="/matriculas/assets/css/style.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark" style="background-color:#101b93;">
        <div class="container">
            <a class="navbar-brand fw-bold" href="/matriculas/index.php">ApplyGo</a>
            <button class="navbar-toggler" type="button"
                    data-bs-toggle="collapse" data-bs-target="#menu">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="menu">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="/matriculas/index.php">Inicio</a>
                    </li>
                    <?php if (isset($_SESSION['usuario'])): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="/matriculas/dashboard.php">Panel</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/matriculas/logout.php">Cerrar sesión</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="/matriculas/index.php?modal=login">Iniciar sesión</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/matriculas/index.php?modal=registro">Registrarse</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
