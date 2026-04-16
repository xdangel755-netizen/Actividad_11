<?php 
require_once 'php/auth_check.php'; 
$nombre_usuario = $_SESSION['usuario_nombre'];

// Lógica para elegir el avatar según el usuario
$avatar = "Admin.jpg"; // Default
if (strtoupper($nombre_usuario) === 'ARIANA') {
    $avatar = "Ariana.jpg";
} else if (strtoupper($nombre_usuario) === 'ADMIN') {
    $avatar = "Admin.jpg";
} else if (strtoupper($nombre_usuario) === 'ANGEL') {
    $avatar = "Yo.jpg";
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Principal</title>

    <!-- FRAMEWORK BOOTSTRAP -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">

    <!-- LIBRERÍA DE ICONOS -->
    <script src="https://kit.fontawesome.com/812c8ee19a.js" crossorigin="anonymous"></script>

    <!-- FAVICON DE LA APLICACIÓN DE MATRICULA -->
    <link rel="shortcut icon" href="img/favicon.ico" type="image/x-icon">

    <!-- FUENTES DE GOOGLE FONTS -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">

    <!-- LIBRERÍA AJAX (Font Awesome local) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- HOJA DE ESTILOS -->
    <link rel="stylesheet" href="css/styles-dashboard.css?v=2">
    <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- LIBRERÍA JQUERY -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <!-- LIBRERIA SWEETALERT2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
    <div class="containers">
        <aside class="sidebar">
            <div class="sidebar-header">
                <i class="fa-solid fa-graduation-cap"></i>
                <h2>SISTEMA DE MATRÍCULA</h2>
            </div>

            <nav class="sidebar-nav">
                <ul>
                    <li id="menu-dashboard" class="active"><a href="dashboard.php"><i class="fa-solid fa-house"></i> Dashboard</a></li>
                    <li id="menu-estudiantes"><a href="dashboard2.php?modulo=estudiantes"><i class="fa-solid fa-users"></i> Estudiantes</a></li>
                    <li id="menu-aulas"><a href="dashboard2.php?modulo=aulas"><i class="fa-solid fa-chalkboard"></i> Aulas</a></li>
                    <li id="menu-cursos"><a href="dashboard2.php?modulo=cursos"><i class="fa-solid fa-book"></i> Cursos</a></li>
                    <li id="menu-inscripciones"><a href="dashboard2.php?modulo=inscripciones"><i class="fa-solid fa-file-contract"></i> Inscripciones</a></li>
                    <li id="menu-pagos"><a href="dashboard2.php?modulo=pagos"><i class="fa-solid fa-money-bill-wave"></i> Pagos</a></li>
                    <li id="menu-configuracion"><a href="dashboard2.php?modulo=configuracion"><i class="fa-solid fa-gear"></i> Configuración</a></li>
                </ul>
            </nav>

            <div class="sidebar-footer">
                <div class="user-profile">
                    <img src="<?php echo $avatar; ?>" alt="Avatar" width="40" height="40" style="border-radius: 50%; object-fit: cover;">
                    <div class="user-info">
                        <h3 style="font-size: 0.9rem;"><?php echo htmlspecialchars($_SESSION['usuario_nombre'] ?? 'Admin'); ?></h3>
                        <span style="font-size: 0.75rem; color: #888;">Administrador</span>
                    </div>
                </div>
            </div>
        </aside>

        <!-- CONTENIDO PRINCIPAL -->
        <main class="main-content">

            <header class="top-header">
                <div class="header-left">
                    <h2>Panel de Administración</h2>
                </div>

                <div class="header-right">
                    <div class="notification-wrapper" style="position: relative; margin-right: 25px;">
                        <i class="fa-solid fa-bell" id="btnNotificaciones" style="cursor: pointer; font-size: 1.2rem; color: #555;"></i>
                        <span class="badge" style="position: absolute; top: -5px; right: -5px; background: red; color: white; border-radius: 50%; padding: 2px 5px; font-size: 0.7rem;">3</span>
                        
                        <div id="dropdownNotificaciones" style="display: none; position: absolute; right: 0; top: 35px; background: white; border: 1px solid #ddd; box-shadow: 0 5px 15px rgba(0,0,0,0.1); border-radius: 8px; width: 280px; z-index: 1000;">
                            <div style="padding: 10px; border-bottom: 1px solid #eee; font-weight: bold; background: #f9f9f9; border-radius: 8px 8px 0 0; display: flex; justify-content: space-between; align-items: center;">
                                Notificaciones 
                                <span id="notif-count" style="background: red; color: white; padding: 2px 6px; border-radius: 10px; font-size: 0.65rem;">0</span>
                            </div>
                            <div id="lista-notificaciones" style="max-height: 300px; overflow-y: auto;">
                                <div style="padding: 20px; text-align: center; color: #888; font-size: 0.85rem;">Cargando...</div>
                            </div>
                        </div>
                    </div>

                    <div class="search-bar">
                        <input type="text" id="globalSearchInput" placeholder="Buscar alumnos, aulas o cursos...">
                        <i class="fa-solid fa-magnifying-glass" id="btnGlobalSearch"></i>
                        <div id="searchResultsContainer"></div>
                    </div>

                    <div class="user-dropdown-wrapper" style="position: relative; cursor: pointer;">
                        <div class="user-dropdown" id="btnUserMenu" style="display: flex; align-items: center; gap: 10px;">
                            <img src="<?php echo $avatar; ?>" alt="Usuario" width="50" height="50" style="border-radius: 50%; object-fit: cover;">
                            <h3 style="margin: 0; font-size: 1rem; color: #555;"><?php echo htmlspecialchars($_SESSION['usuario_nombre'] ?? 'Admin'); ?> <i class="fa-solid fa-chevron-down"></i></h3>
                        </div>
                        
                        <div id="dropdownUser" style="display: none; position: absolute; right: 0; top: 60px; background: white; border: 1px solid #ddd; box-shadow: 0 5px 15px rgba(0,0,0,0.1); border-radius: 8px; width: 200px; z-index: 1000;">
                            <a href="#" onclick="Swal.fire('Información', 'Versión de la plataforma: 2.5', 'info'); return false;" style="display: block; padding: 12px 15px; color: #333; text-decoration: none; border-bottom: 1px solid #eee; font-size: 0.9rem;"><i class="fa-solid fa-circle-info" style="margin-right: 8px;"></i> Acerca de</a>
                            <a href="php/logout.php" style="display: block; padding: 12px 15px; color: #e74a3b; text-decoration: none; font-size: 0.9rem;"><i class="fa-solid fa-right-from-bracket" style="margin-right: 8px;"></i> Cerrar sesión</a>
                        </div>
                    </div>
                </div>
            </header>

            <div class="content-body" id="main-content-area">
                <!-- MÓDULO INICIO / DASHBOARD PRINCIPAL -->
                <div class="modulo-vista" id="modulo-inicio">
                    <div class="welcome-banner glass-panel">
                        <div class="banner-content">
                            <h2>Bienvenido al panel, <?php echo htmlspecialchars($_SESSION['usuario_nombre'] ?? 'Administrador'); ?></h2>
                            <p>Aquí tienes un resumen visual del estado general de la institución.</p>
                        </div>
                        <i class="fa-solid fa-chart-line banner-icon"></i>
                    </div>

                    <div class="kpi-container">
                        <div class="kpi-card glass-panel kpi-primary">
                            <div class="kpi-icon"><i class="fa-solid fa-users"></i></div>
                            <div class="kpi-data">
                                <h3 id="kpiTotalAlumnos">0</h3>
                                <p>Total Estudiantes</p>
                            </div>
                        </div>
                        <div class="kpi-card glass-panel kpi-secondary">
                            <div class="kpi-icon"><i class="fa-solid fa-chalkboard"></i></div>
                            <div class="kpi-data">
                                <h3 id="kpiTotalAulas">0</h3>
                                <p>Aulas Registradas</p>
                            </div>
                        </div>
                        <div class="kpi-card glass-panel kpi-tertiary">
                            <div class="kpi-icon"><i class="fa-solid fa-book-open"></i></div>
                            <div class="kpi-data">
                                <h3 id="kpiTotalCursos">0</h3>
                                <p>Cursos Disponibles</p>
                            </div>
                        </div>
                        <div class="kpi-card glass-panel kpi-quaternary">
                            <div class="kpi-icon"><i class="fa-solid fa-user-check"></i></div>
                            <div class="kpi-data">
                                <h3 id="kpiAlumnosActivos">0</h3>
                                <p>Alumnos Activos</p>
                            </div>
                        </div>
                    </div>

                    <div class="charts-container">
                        <div class="chart-card glass-panel">
                            <h2>Distribución por Género</h2>
                            <div class="chart-wrapper">
                                <canvas id="chartGenero"></canvas>
                            </div>
                        </div>
                        <div class="chart-card glass-panel">
                            <h2>Estado de Matrículas</h2>
                            <div class="chart-wrapper">
                                <canvas id="chartEstado"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- ARCHIVO JS EXCLUSIVO DE DASHBOARD -->
    <script src="js/inicio.js?v=2"></script>
</body>

</html>
