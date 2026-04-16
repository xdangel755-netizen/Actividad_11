<?php 
require_once 'php/auth_check.php'; 
$id_usuario = $_SESSION['usuario_id'];
$nombre_usuario = $_SESSION['usuario_nombre'];

// Lógica para elegir el avatar según el usuario
$avatar = "img/admin.jpg"; // Default
if (strtoupper($nombre_usuario) === 'ARIANA') {
    $avatar = "img/ariana.jpg";
} else if (strtoupper($nombre_usuario) === 'ADMIN') {
    $avatar = "img/admin.jpg";
} else if (strtoupper($nombre_usuario) === 'ANGEL') {
    $avatar = "img/yo.jpg";
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial
scale=1.0">
    <title>Panel ABC - Matrícula OnLine</title>
    <!-- FRAMEWORK BOOTSTRAP -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min
.css" rel="stylesheet" integrity="sha384
EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">

    <!-- LIBRERÍA DE ICONOS -->
    <script src="https://kit.fontawesome.com/812c8ee19a.js" crossorigin="anonymous"></script>

    <!-- FAVICON DE LA APLICACIÓN DE MATRICULA -->
    <link rel="shortcut icon" href="img/favicon.ico" type="image/x
icon">

    <!-- FUENTES DE GOOGLE FONTS -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,
200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,5
00;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">

    <!-- LIBRERÍA AJAX -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font
awesome/6.5.1/css/all.min.css">

    <!-- HOJA DE ESTILOS -->
    <link rel="stylesheet" href="css/styles-dashboard.css?v=2">
    <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- LIBRERÍA JQUERY -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <!-- DATATABLES Y EXPORTACIÓN -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">
    
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
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
                    <li id="menu-dashboard" class="active"><a href="#"><i class="fa-solid fa-house"></i> Dashboard</a></li>
                    <li id="menu-estudiantes"><a href="#"><i class="fa-solid fa-users"></i> Estudiantes</a></li>
                    <li id="menu-aulas"><a href="#"><i class="fa-solid fa-chalkboard"></i> Aulas</a></li>
                    <li id="menu-cursos"><a href="#"><i class="fa-solid fa-book"></i> Cursos</a></li>
                    <li id="menu-inscripciones"><a href="#"><i class="fa-solid fa-file-contract"></i> Inscripciones</a></li>
                    <li id="menu-pagos"><a href="#"><i class="fa-solid fa-money-bill-wave"></i> Pagos</a></li>
                    <li id="menu-configuracion"><a href="#"><i class="fa-solid fa-gear"></i> Configuración</a></li>
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

        <main class="main-content">

            <header class="top-header">
                <div class="header-left">
                    <h2>Panel de Administración</h2>
                </div>

                <div class="header-right">
                    <div class="notification-wrapper" style="position: relative; margin-right: 25px;">
                        <i class="fa-solid fa-bell" id="btnNotificaciones" style="cursor: pointer; font-size: 1.2rem; color: #555;"></i>
                        <span class="badge" style="position: absolute; top: -5px; right: -5px; background: red; color: white; border-radius: 50%; padding: 2px 5px; font-size: 0.7rem; display:none;">0</span>
                        
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
                        <input type="text" id="globalSearchInput" placeholder="Buscar en módulo actual...">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </div>

                    <div class="user-dropdown-wrapper" style="position: relative; cursor: pointer;">
                        <div class="user-dropdown" id="btnUserMenu" style="display: flex; align-items: center; gap: 10px;">
                            <img src="<?php echo $avatar; ?>" alt="Usuario" width="50" height="50" style="border-radius: 50%; object-fit: cover;">
                            <h3 style="margin: 0; font-size: 1rem; color: #555;"><?php echo htmlspecialchars($_SESSION['usuario_nombre'] ?? 'Admin'); ?> <i class="fa-solid fa-chevron-down"></i></h3>
                        </div>
                        
                        <div id="dropdownUser" style="display: none; position: absolute; right: 0; top: 60px; background: white; border: 1px solid #ddd; box-shadow: 0 5px 15px rgba(0,0,0,0.1); border-radius: 8px; width: 200px; z-index: 1000;">
                            <a href="#" onclick="$('html, body').animate({ scrollTop: 0 }, 'slow'); Swal.fire('Información', 'Versión de la plataforma: 2.5', 'info'); return false;" style="display: block; padding: 12px 15px; color: #333; text-decoration: none; border-bottom: 1px solid #eee; font-size: 0.9rem;"><i class="fa-solid fa-circle-info" style="margin-right: 8px;"></i> Acerca de</a>
                            <a href="php/logout.php" style="display: block; padding: 12px 15px; color: #e74a3b; text-decoration: none; font-size: 0.9rem;"><i class="fa-solid fa-right-from-bracket" style="margin-right: 8px;"></i> Cerrar sesión</a>
                        </div>
                    </div>
                </div>
            </header>

            <!-- CUERPO DONDE CAMBIARÁ EL CONTENIDO -->
            <div class="content-body" id="main-content-area">

                <!-- MÓDULO DASHBOARD (ESTADÍSTICAS) -->
                <div class="modulo-vista" id="modulo-dashboard">
                    <!-- BIENVENIDA PERSONALIZADA -->
                    <div style="background: linear-gradient(135deg, #2c3e50, #6441a5); border-radius: 16px; padding: 28px 32px; margin-bottom: 28px; display: flex; align-items: center; gap: 20px; box-shadow: 0 8px 24px rgba(100,65,165,0.25);">
                        <img src="<?php echo $avatar; ?>" alt="Avatar" width="70" height="70" style="border-radius: 50%; border: 3px solid #fff; object-fit: cover; flex-shrink: 0;">
                        <div>
                            <p style="color: rgba(255,255,255,0.7); margin: 0; font-size: 0.9rem;" id="saludo-hora">Bienvenido de vuelta</p>
                            <h2 style="color: #fff; margin: 4px 0 6px; font-size: 1.6rem; font-weight: 700;">
                                <?php
                                    $nombre = htmlspecialchars($_SESSION['usuario_nombre'] ?? 'Administrador');
                                    $nombreCapital = ucfirst(strtolower($nombre));
                                    echo "¡Hola, {$nombreCapital}! 👋";
                                ?>
                            </h2>
                            <p style="color: rgba(255,255,255,0.65); margin: 0; font-size: 0.85rem;" id="fecha-hoy"></p>
                        </div>
                        <div style="margin-left: auto; text-align: right; display: none;" class="d-md-block">
                            <i class="fa-solid fa-graduation-cap" style="font-size: 4rem; color: rgba(255,255,255,0.1);"></i>
                        </div>
                    </div>

                    <div class="kpi-container" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px;">
                        <div class="kpi-card glass-panel kpi-primary">
                            <div class="kpi-icon"><i class="fa-solid fa-user-graduate"></i></div>
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

                    <div class="charts-container" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 25px;">
                        <div class="chart-card glass-panel">
                            <h2>Distribución por Género</h2>
                            <div class="chart-wrapper" style="height: 300px; position: relative;">
                                <canvas id="chartGenero"></canvas>
                            </div>
                        </div>
                        <div class="chart-card glass-panel">
                            <h2>Estado de Matrículas</h2>
                            <div class="chart-wrapper" style="height: 300px; position: relative;">
                                <canvas id="chartEstado"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- MÓDULO ESTUDIANTES -->
                <div class="card table-card modulo-vista" id="modulo-estudiantes" style="display: none;">
                    <div class="card-header">
                        <h2>Estudiantes Recientes</h2>
                        <button class="btn btn-primary btn-registrar"><i class="fa-solid fa-plus"></i> Registrar
                            Nuevo</button>
                    </div>

                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Nombre</th>
                                <th>Apellido</th>
                                <th>DNI</th>
                                <th>F. Nacimiento</th>
                                <th>Celular</th>
                                <th>Correo</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="tablaAlumnos">

                        </tbody>
                    </table>


                </div>

                <!-- MÓDULO AULAS -->
                <div class="card table-card modulo-vista" id="modulo-aulas" style="display: none;">
                    <div class="card-header">
                        <h2>Lista de Aulas</h2>
                        <button class="btn btn-primary btn-registrar-aula"><i class="fa-solid fa-plus"></i> Añadir Aula</button>
                    </div>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th style="text-align:center">#</th>
                                <th style="text-align:center">Nivel</th>
                                <th style="text-align:center">Grado</th>
                                <th style="text-align:center">Sección</th>
                                <th style="text-align:center">Vacantes Totales</th>
                                <th style="text-align:center">Vacantes Disponibles</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="tablaAulas">
                        </tbody>
                    </table>
                </div>

                <!-- MÓDULO CURSOS -->
                <div class="card table-card modulo-vista" id="modulo-cursos" style="display: none;">
                    <div class="card-header">
                        <h2>Directorio de Cursos</h2>
                        <button class="btn btn-primary btn-registrar-curso"><i class="fa-solid fa-plus"></i> Crear Curso</button>
                    </div>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th style="text-align:center">#</th>
                                <th style="text-align:center">Nombre del Curso</th>
                                <th style="text-align:center">Aula Asignada</th>
                                <th style="text-align:center">Docente Asignado</th>
                                <th style="text-align:center">Horas Semanales</th>
                                <th style="text-align:center">Créditos</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="tablaCursos">
                        </tbody>
                    </table>
                </div>

                <!-- MÓDULO INSCRIPCIONES -->
                <div class="card table-card modulo-vista" id="modulo-inscripciones" style="display: none;">
                    <div class="card-header">
                        <h2>Inscripciones a Cursos</h2>
                        <button class="btn btn-primary btn-registrar-inscripcion"><i class="fa-solid fa-user-plus"></i> Inscribir Alumno</button>
                    </div>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th style="text-align:center">#</th>
                                <th style="text-align:center">Alumno</th>
                                <th style="text-align:center">Curso</th>
                                <th style="text-align:center">Fecha de Inscripción</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="tablaInscripciones">
                        </tbody>
                    </table>
                </div>

                <!-- MÓDULO PAGOS / RESERVAS -->
                <div class="card table-card modulo-vista" id="modulo-pagos" style="display: none;">
                    <div class="card-header">
                        <h2>Control de Pagos de Matrícula (Aulas)</h2>
                        <button class="btn btn-primary btn-registrar-pago"><i class="fa-solid fa-file-invoice-dollar"></i> Registrar Pago</button>
                    </div>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th style="text-align:center">#</th>
                                <th style="text-align:center">Alumno</th>
                                <th style="text-align:center">Aula</th>
                                <th style="text-align:center">Cod. Pago</th>
                                <th style="text-align:center">Estado</th>
                                <th style="text-align:center">Fecha</th>
                                <th style="text-align:center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="tablaPagos">
                        </tbody>
                    </table>
                </div>

                <!-- MÓDULO CONFIGURACIÓN / USUARIOS -->
                <div class="card table-card modulo-vista" id="modulo-configuracion" style="display: none;">
                    <div class="card-header">
                        <h2>Configuración del Sistema</h2>
                        <button class="btn btn-primary btn-registrar-usuario"><i class="fa-solid fa-user-shield"></i> Añadir Administrador</button>
                    </div>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th style="width: 60px; text-align:center">#</th>
                                <th style="width: 70px; text-align:center">Foto</th>
                                <th style="text-align:center">Usuario (Login)</th>
                                <th style="text-align:center">Estado de Cuenta</th>
                                <th style="text-align:center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="tablaUsuarios">
                        </tbody>
                    </table>
                </div>

            </div>

        </main>

    </div>

    <div id="modalAlumno" class="modal-overlay" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modalTitulo">Registrar Nuevo Alumno</h2>
                <i class="fa-solid fa-xmark btn-cerrar-modal"></i>
            </div>

            <form id="formAlumno">
                <input type="hidden" id="id_alumno" name="id_alumno">
                <input type="hidden" id="opcion" name="opcion" value="1">
                <div class="form-grid">
                    <input type="text" id="dni" name="dni" placeholder="DNI (8 dígitos)" maxlength="8" required>
                    <input type="text" id="nombres" name="nombres" placeholder="Nombres" required>
                    <input type="text" id="apellidos" name="apellidos" placeholder="Apellidos" required>
                    <input type="date" id="fecha_nac" name="fecha_nac" required>
                    <input type="number" id="edad" name="edad" placeholder="Edad" required>
                    <select id="genero" name="genero" required>
                        <option value="">Seleccione Género...</option>
                        <option value="M">Masculino</option>
                        <option value="F">Femenino</option>
                    </select>
                    <select id="estado" name="estado" required>
                        <option value="">Seleccione Estado...</option>
                        <option value="Activo">Activo</option>
                        <option value="Inactivo">Inactivo</option>
                        <option value="En Proceso">En Proceso</option>
                    </select>
                    <input type="text" id="direccion" name="direccion" placeholder="Dirección" required>
                    <input type="text" id="celular" name="celular" placeholder="Celular" maxlength="9" required>
                    <input type="email" id="correo" name="correo" placeholder="Correo Electrónico" required>
                    <input type="text" id="apoderado" name="apoderado" placeholder="Nombre Apoderado" required>
                    <input type="text" id="cel_apoderado" name="cel_apoderado" placeholder="Celular Apoderado"
                        maxlength="9" required>
                    <input type="text" id="username" name="username" placeholder="Nombre de Usuario" required>
                    <input type="password" id="password" name="password" placeholder="Contraseña">
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-cerrar-modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar Datos</button>
                </div>
            </form>
        </div>
    </div>

    <div id="modalAula" class="modal-overlay" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modalTituloAula">Añadir Nueva Aula</h2>
                <i class="fa-solid fa-xmark btn-cerrar-modal"></i>
            </div>
            <form id="formAula">
                <input type="hidden" id="id_aula" name="id_aula">
                <input type="hidden" id="opcion_aula" name="opcion" value="1">
                <div class="form-grid">
                    <input type="text" id="nivel" name="nivel" placeholder="Nivel (Ej. SECUNDARIA)" required>
                    <input type="number" id="grado" name="grado" placeholder="Grado (Ej. 3)" required>
                    <input type="text" id="seccion" name="seccion" placeholder="Sección (Ej. A)" maxlength="1" required>
                    <input type="number" id="vacantes_totales" name="vacantes_totales" placeholder="Vacantes Totales" required>
                    <input type="number" id="vacantes_disponibles" name="vacantes_disponibles" placeholder="Vacantes Disponibles" required>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-cerrar-modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar Datos</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL CURSO -->
    <div id="modalCurso" class="modal-overlay" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modalTituloCurso">Crear Nuevo Curso</h2>
                <i class="fa-solid fa-xmark btn-cerrar-modal"></i>
            </div>
            <form id="formCurso">
                <input type="hidden" id="id_curso" name="id_curso">
                <input type="hidden" id="opcion_curso" name="opcion" value="1">
                <div class="form-grid">
                    <input type="text" id="nombre_curso" name="nombre_curso" placeholder="Nombre del Curso" required>
                    <select id="curso_aula_select" name="id_aula" required style="width: 100%; border: 1px solid #ccc; border-radius: 8px; padding: 10px;">
                        <option value="">Seleccione Aula...</option>
                    </select>
                    <input type="text" id="docente" name="docente" placeholder="Docente Asignado" required>
                    <input type="number" id="horas_semanales" name="horas_semanales" placeholder="Horas Semanales" required>
                    <input type="number" id="creditos" name="creditos" placeholder="Créditos" required>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-cerrar-modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar Datos</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL INSCRIPCION -->
    <div id="modalInscripcion" class="modal-overlay" style="display: none;">
        <div class="modal-content" style="max-width: 500px;">
            <div class="modal-header">
                <h2 id="modalTituloInscripcion">Nueva Inscripción a Curso</h2>
                <i class="fa-solid fa-xmark btn-cerrar-modal"></i>
            </div>
            <form id="formInscripcion">
                <input type="hidden" name="opcion" value="1">
                <div class="form-grid" style="grid-template-columns: 1fr;">
                    <div class="form-group">
                        <label for="id_alumno_select" style="display:block; margin-bottom:5px; font-weight:600;">Seleccionar Alumno:</label>
                        <select id="id_alumno_select" name="id_alumno" class="form-control" style="width:100%; padding:10px; border-radius:8px; border:1px solid #ddd;" required>
                            <option value="">Cargando alumnos...</option>
                        </select>
                    </div>
                    <div class="form-group" style="margin-top:15px;">
                        <label for="id_curso_select" style="display:block; margin-bottom:5px; font-weight:600;">Seleccionar Curso:</label>
                        <select id="id_curso_select" name="id_curso" class="form-control" style="width:100%; padding:10px; border-radius:8px; border:1px solid #ddd;" required>
                            <option value="">Cargando cursos...</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-cerrar-modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Registrar Inscripción</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL PAGO/RESERVA -->
    <div id="modalPago" class="modal-overlay" style="display: none;">
        <div class="modal-content" style="max-width: 500px;">
            <div class="modal-header">
                <h2 id="modalTituloPago">Registrar Pago/Reserva de Aula</h2>
                <i class="fa-solid fa-xmark btn-cerrar-modal"></i>
            </div>
            <form id="formPago">
                <input type="hidden" name="opcion" value="1">
                <div class="form-grid" style="grid-template-columns: 1fr;">
                    <div class="form-group">
                        <label style="display:block; margin-bottom:5px; font-weight:600;">Seleccionar Alumno:</label>
                        <select id="pago_alumno_select" name="id_alumno" class="form-control" style="width:100%; padding:10px; border-radius:8px; border:1px solid #ddd;" required>
                            <option value="">Cargando alumnos...</option>
                        </select>
                    </div>
                    <div class="form-group" style="margin-top:15px;">
                        <label style="display:block; margin-bottom:5px; font-weight:600;">Seleccionar Aula:</label>
                        <select id="pago_aula_select" name="id_aula" class="form-control" style="width:100%; padding:10px; border-radius:8px; border:1px solid #ddd;" required>
                            <option value="">Cargando aulas...</option>
                        </select>
                    </div>
                    <div class="form-group" style="margin-top:15px;">
                        <label style="display:block; margin-bottom:5px; font-weight:600;">Código de Pago:</label>
                        <input type="text" id="cod_pago" name="cod_pago" placeholder="Ej. PAG-004" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-cerrar-modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Registrar Pago</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL USUARIO ADMIN -->
    <div id="modalUsuario" class="modal-overlay" style="display: none;">
        <div class="modal-content" style="max-width: 400px;">
            <div class="modal-header">
                <h2 id="modalTituloUsuario">Añadir Administrador</h2>
                <i class="fa-solid fa-xmark btn-cerrar-modal"></i>
            </div>
            <form id="formUsuario">
                <input type="hidden" id="id_usuario" name="id_usuario">
                <input type="hidden" id="opcion_usuario" name="opcion" value="1">
                <div class="form-grid" style="grid-template-columns: 1fr;">
                    <div class="form-group">
                        <input type="text" id="usuario_username" name="username" placeholder="Nombre de Usuario (Login)" required>
                    </div>
                    <div class="form-group" style="margin-top:10px;">
                        <input type="password" id="usuario_password" name="password" placeholder="Contraseña Segura" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-cerrar-modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar Datos</button>
                </div>
            </form>
        </div>
    </div>
    <!-- MODAL VISTA DETALLADA ALUMNO -->
    <div id="modalDetallesAlumno" class="modal-overlay" style="display: none;">
        <div class="modal-content" style="max-width: 600px;">
            <div class="modal-header">
                <h2>Ficha de Datos del Estudiante</h2>
                <i class="fa-solid fa-xmark btn-cerrar-modal"></i>
            </div>
            <div class="modal-body" style="padding: 20px;">
                <div class="ficha-encabezado" style="display: flex; gap: 20px; align-items: center; border-bottom: 2px solid #f0f0f0; padding-bottom: 15px; margin-bottom: 20px;">
                    <div id="det-avatar" style="width:80px; height:80px; border-radius:10px; border:3px solid #6441a5; background:linear-gradient(135deg,#6441a5,#2c3e50); display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                        <span id="det-iniciales" style="color:#fff; font-size:1.8rem; font-weight:700; letter-spacing:1px;"></span>
                    </div>
                    <div>
                        <h3 id="det-full-name" style="margin-bottom: 5px; color: #1a1a1a;">-</h3>
                        <p id="det-dni" style="margin-bottom: 0; color: #666; font-weight: bold; font-family: monospace;">-</p>
                    </div>
                </div>

                <div class="ficha-secciones" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div class="ficha-col">
                        <h4 style="font-size: 0.9rem; color: #6441a5; text-transform: uppercase; margin-bottom: 10px; border-left: 3px solid #6441a5; padding-left: 8px;">Información Personal</h4>
                        <p><strong>Género:</strong> <span id="det-genero">-</span></p>
                        <p><strong>Edad:</strong> <span id="det-edad">-</span> años</p>
                        <p><strong>F. Nacimiento:</strong> <span id="det-fecha-nac">-</span></p>
                        <p><strong>Estado:</strong> <span id="det-estado" class="status-badge">-</span></p>
                    </div>
                    <div class="ficha-col">
                        <h4 style="font-size: 0.9rem; color: #6441a5; text-transform: uppercase; margin-bottom: 10px; border-left: 3px solid #6441a5; padding-left: 8px;">Contacto y Red</h4>
                        <p><strong>Celular:</strong> <span id="det-celular">-</span></p>
                        <p><strong>Correo:</strong> <span id="det-correo" style="font-size: 0.85rem;">-</span></p>
                        <p><strong>Usuario:</strong> <span id="det-username" style="font-weight: bold; color: #2e3192;">-</span></p>
                    </div>
                </div>

                <div class="ficha-col" style="margin-top: 20px;">
                    <h4 style="font-size: 0.9rem; color: #6441a5; text-transform: uppercase; margin-bottom: 10px; border-left: 3px solid #6441a5; padding-left: 8px;">Apoderado y Dirección</h4>
                    <p><strong>Dirección:</strong> <span id="det-direccion">-</span></p>
                    <p><strong>Apoderado:</strong> <span id="det-apoderado">-</span></p>
                    <p><strong>Celular Apoderado:</strong> <span id="det-cel-apoderado">-</span></p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-cerrar-modal" style="width: 100%;">Cerrar Ficha</button>
            </div>
        </div>
    </div>
    <!-- MODAL VISTA DETALLADA GENERICO (Aulas, Cursos) -->
    <div id="modalDetallesGenerico" class="modal-overlay" style="display: none;">
        <div class="modal-content" style="max-width: 450px;">
            <div class="modal-header">
                <h2 id="det-gen-titulo">Detalles del Registro</h2>
                <i class="fa-solid fa-xmark btn-cerrar-modal"></i>
            </div>
            <div class="modal-body" id="det-gen-body" style="padding: 20px;">
                <!-- El contenido se cargará dinámicamente con JS -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-cerrar-modal" style="width: 100%;">Cerrar</button>
            </div>
        </div>
    </div>

    <!-- ARCHIVO JS-->
    <script src="js/dashboard.js?v=10.7_center"></script>

    <!-- LIBRERIA SWEETALERT2-->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- LIBRERIA BOOTSTRAP-->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM"
        crossorigin="anonymous"></script>
</body>

</html>