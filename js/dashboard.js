$(document).ready(function () {
    // 1. VARIABLES GLOBALES DE DATATABLES Y GRÁFICOS
    let dtEstudiantes, dtAulas, dtCursos, dtInscripciones, dtPagos, dtUsuarios;
    let chart1 = null;
    let chart2 = null;

    // AÑADIR: Configuración de depuración global
    // AÑADIR: Capturador de errores global para depuración
    window.onerror = function(message, source, lineno, colno, error) {
        console.error("DEBUG GLOBAL:", message, "en", source, "línea:", lineno);
        Swal.fire('Error del Sistema', `Error en script: ${message}\nLínea: ${lineno}`, 'error');
    };

    $.ajaxSetup({
        error: function(jqXHR, textStatus, errorThrown) {
            console.error("DEBUG - Fallo en AJAX:", textStatus, errorThrown);
            if(jqXHR.status === 401 || jqXHR.status === 403) {
                Swal.fire('Sesión Caducada', 'Por favor, inicie sesión nuevamente.', 'error')
                    .then(() => window.location.href = 'login.php');
            }
        }
    });

    // 2. CONFIGURACIÓN BASE DE DATATABLES (Ubicación fija para evitar ReferenceError)
    const dtDom = '<"dt-top"B>rt<"dt-bottom"ip><"clear">';
    const dtLanguage = {
        "decimal": "",
        "emptyTable": "No se encontraron datos para mostrar",
        "info": "Registros del _START_ al _END_ (Total: _TOTAL_)",
        "infoEmpty": "Mostrando 0 registros",
        "infoFiltered": " - filtrado de _MAX_ registros en total",
        "thousands": ",",
        "lengthMenu": "Ver _MENU_ por página",
        "loadingRecords": "Cargando...",
        "processing": "Procesando...",
        "search": "Buscar:",
        "zeroRecords": "No se encontraron registros",
        "paginate": { "first": "Primero", "last": "Último", "next": "Siguiente", "previous": "Anterior" }
    };
    const dtButtons = (title) => [
        { extend: 'excelHtml5', text: '<i class="fa-solid fa-file-excel"></i> Excel', title: title, className: 'btn btn-success' },
        { extend: 'pdfHtml5', text: '<i class="fa-solid fa-file-pdf"></i> PDF', title: title, className: 'btn btn-danger', orientation: 'landscape', pageSize: 'A4' },
        { extend: 'csvHtml5', text: '<i class="fa-solid fa-file-csv"></i> CSV', title: title, className: 'btn btn-secondary' },
        { extend: 'print', text: '<i class="fa-solid fa-print"></i> Imprimir', title: title, className: 'btn btn-info' }
    ];

    // 3. CAPTURAR EL USUARIO DE LA SESIÓN LOCAL
    let nombreSesion = sessionStorage.getItem('sesion_usuario');
    if(nombreSesion) {
        // Capitalizar la primera letra (ej: angel -> Angel)
        nombreSesion = nombreSesion.charAt(0).toUpperCase() + nombreSesion.slice(1).toLowerCase();
        
        // Actualizar sidebar y header
        $('.user-info h3').text(nombreSesion);
        $('.user-dropdown h3').html(nombreSesion + ' <i class="fa-solid fa-chevron-down"></i>');
    }

    /* ======================================================== 
       LÓGICA DE NAVEGACIÓN ENTRE MÓDULOS (SWITCHER)
       ======================================================== */
    
    function cambiarModulo(idModulo) {
        if (!idModulo) idModulo = 'dashboard';
        
        let cleanId = idModulo.toString().replace('menu-', '').replace('#modulo-', '');
        let selector = '#modulo-' + cleanId;
        
        // REGLA DE ORO: Si el modulo no existe en el HTML, volver al Dashboard
        if ($(selector).length === 0) {
            console.warn(`Módulo ${cleanId} no existe, redirigiendo a dashboard.`);
            cleanId = 'dashboard';
            selector = '#modulo-dashboard';
        }

        let menuId = 'menu-' + cleanId;
        sessionStorage.setItem('active_modulo', cleanId);

        $('.modulo-vista').hide();
        $(selector).fadeIn();
        
        $('.sidebar-nav li').removeClass('active');
        $('#' + menuId).addClass('active');

        console.log("Navegando a módulo:", cleanId);

        switch(selector) {
            case '#modulo-dashboard': cargarDashboard(); break;
            case '#modulo-estudiantes': cargarAlumnos(); break;
            case '#modulo-aulas': cargarAulas(); break;
            case '#modulo-cursos': cargarCursos(); break;
            case '#modulo-inscripciones': cargarInscripciones(); break;
            case '#modulo-pagos': cargarPagos(); break;
            case '#modulo-configuracion': cargarUsuarios(); break;
        }
    }

    function cargarDashboard() {
        $.ajax({
            url: "php/api_dashboard.php",
            type: "GET",
            dataType: "json",
            success: function(respuesta) {
                if(respuesta.exito && respuesta.data) {
                    let d = respuesta.data;
                    
                    // Animación de números para KPIs
                    animarNumero('#kpiTotalAlumnos', d.total_alumnos);
                    animarNumero('#kpiTotalAulas', d.total_aulas);
                    animarNumero('#kpiTotalCursos', d.total_cursos);
                    animarNumero('#kpiAlumnosActivos', d.alumnos_activos);

                    // Gráficos usando datos pre-procesados de la API premium
                    try {
                        let canvas1 = document.getElementById('chartGenero');
                        if (canvas1) {
                            if(chart1) chart1.destroy();
                            chart1 = new Chart(canvas1, {
                                type: 'doughnut',
                                data: {
                                    labels: d.genero_dist.labels,
                                    datasets: [{
                                        data: d.genero_dist.data,
                                        backgroundColor: d.genero_dist.colors,
                                        borderWidth: 2,
                                        borderColor: '#ffffff'
                                    }]
                                },
                                options: { 
                                    responsive: true, 
                                    maintainAspectRatio: false,
                                    plugins: { legend: { position: 'bottom' } }
                                }
                            });
                        }

                        let canvas2 = document.getElementById('chartEstado');
                        if (canvas2) {
                            if(chart2) chart2.destroy();
                            chart2 = new Chart(canvas2, {
                                type: 'pie',
                                data: {
                                    labels: d.estado_dist.labels,
                                    datasets: [{
                                        data: d.estado_dist.data,
                                        backgroundColor: d.estado_dist.colors,
                                        borderWidth: 2,
                                        borderColor: '#ffffff'
                                    }]
                                },
                                options: { 
                                    responsive: true, 
                                    maintainAspectRatio: false,
                                    plugins: { legend: { position: 'bottom' } }
                                }
                            });
                        }
                    } catch (e) {
                        console.error("Error al renderizar gráficos:", e);
                    }
                }
            },
            error: function(e) {
                console.error("Error AJAX Dashboard:", e);
            }
        });
    }

    function animarNumero(id, maxValue) {
        $({ countNum: $(id).text() }).animate({ countNum: maxValue }, {
            duration: 1200,
            easing: 'swing',
            step: function () {
                $(id).text(Math.floor(this.countNum));
            },
            complete: function () {
                $(id).text(this.countNum);
            }
        });
    }

    // Manejador de clics en el sidebar (ÚNICO HANDLER CONSOLIDADO)
    $('.sidebar-nav li').on('click', function(e) {
        e.preventDefault();
        
        let targetId = $(this).attr('id');
        
        // Limpiar buscador al cambiar de modulo
        $('#globalSearchInput').val('');
        
        cambiarModulo(targetId);

        // Si hay una tabla visible, limpiar su búsqueda local
        let activeEl = $('.modulo-vista:visible').find('table');
        if (activeEl.length > 0 && $.fn.DataTable.isDataTable(activeEl)) {
            activeEl.DataTable().search('').draw();
        }
    });


    // ----------------------------------------------------
    // MENÚS DESPLEGABLES (User & Notificaciones)
    // ----------------------------------------------------
    $('#btnNotificaciones').on('click', function(e) {
        e.stopPropagation();
        $('#dropdownUser').fadeOut(100);
        $('#dropdownNotificaciones').fadeToggle(200);
    });

    $('#btnUserMenu').on('click', function(e) {
        e.stopPropagation();
        $('#dropdownNotificaciones').fadeOut(100);
        $('#dropdownUser').fadeToggle(200);
    });

    // Cerrar los dropdowns al hacer clic fuera de ellos
    $(document).on('click', function(e) {
        if (!$(e.target).closest('.notification-wrapper').length) {
            $('#dropdownNotificaciones').fadeOut(100);
        }
        if (!$(e.target).closest('.user-dropdown-wrapper').length) {
            $('#dropdownUser').fadeOut(100);
        }
    });

    // ----------------------------------------------------
    // BUSCADOR UNIVERSAL INTELIGENTE
    // ----------------------------------------------------
    let _searchTimer = null;

    $('#globalSearchInput').on('keyup', function() {
        let busqueda = $(this).val().trim();
        let $moduloActivo = $('.modulo-vista:visible');
        let moduloId = $moduloActivo.attr('id');

        // --- Si estamos en un módulo CON tabla: filtrar directamente ---
        if (moduloId !== 'modulo-dashboard') {
            let $tabla = $moduloActivo.find('table.data-table');
            if ($tabla.length > 0 && $.fn.DataTable.isDataTable($tabla)) {
                $tabla.DataTable().search(busqueda).draw();
            }
            return;
        }

        // --- Si estamos en el Dashboard: búsqueda global inteligente ---
        clearTimeout(_searchTimer);
        if (busqueda.length < 2) return;

        _searchTimer = setTimeout(function() {
            buscarGlobal(busqueda);
        }, 400);
    });

    function buscarGlobal(busqueda) {
        let b = busqueda.toLowerCase();

        let modulos = [
            { api: 'php/api_estudiante.php', modulo: 'estudiantes', campos: ['NOMBRES','APELLIDO','DNI_ALUMNO','CORREO','USERNAME'] },
            { api: 'php/api_curso.php',      modulo: 'cursos',      campos: ['NOMBRE_CURSO','DOCENTE'] },
            { api: 'php/api_aula.php',       modulo: 'aulas',       campos: ['NIVEL','SECCION','GRADO'] }
        ];

        let promesas = modulos.map(function(m) {
            return $.getJSON(m.api).then(function(res) {
                let datos = res.data || [];
                let hits = datos.filter(function(row) {
                    return m.campos.some(function(c) {
                        return row[c] && row[c].toString().toLowerCase().includes(b);
                    });
                });
                return { modulo: m.modulo, hits: hits.length };
            }).catch(function() {
                return { modulo: m.modulo, hits: 0 };
            });
        });

        Promise.all(promesas).then(function(resultados) {
            // Elegir el módulo con más coincidencias
            let mejor = resultados.reduce(function(max, r) {
                return r.hits > max.hits ? r : max;
            }, { modulo: null, hits: 0 });

            if (mejor.hits > 0 && mejor.modulo) {
                // Navegar al módulo
                cambiarModulo(mejor.modulo);
                // Aplicar el filtro después de que DataTable cargue
                setTimeout(function() {
                    let $tabla = $('.modulo-vista:visible').find('table.data-table');
                    if ($tabla.length > 0 && $.fn.DataTable.isDataTable($tabla)) {
                        $tabla.DataTable().search(busqueda).draw();
                    }
                }, 600);
            } else {
                Swal.fire({
                    icon: 'info',
                    title: 'Sin resultados',
                    text: `No se encontraron registros que coincidan con "${busqueda}".`,
                    timer: 2500,
                    showConfirmButton: false
                });
            }
        });
    }

    // 1. ABRIR MODAL PARA NUEVO REGISTRO 
    // Alumno
    $('.btn-registrar').on('click', function () {
        $('#formAlumno')[0].reset(); // Limpiar formulario 
        $('#opcion').val('1'); // Configurar acción: CREAR 
        $('#modalTitulo').text('Registrar Nuevo Alumno');
        $('#password').attr('required', true); // Contraseña obligatoria al crear
        $('#modalAlumno').fadeIn();
    });

    // Aula
    $('.btn-registrar-aula').on('click', function () {
        $('#formAula')[0].reset();
        $('#opcion_aula').val('1');
        $('#modalTituloAula').text('Añadir Nueva Aula');
        $('#modalAula').fadeIn();
    });

    // Curso
    $('.btn-registrar-curso').on('click', function () {
        $('#formCurso')[0].reset();
        $('#opcion_curso').val('1');
        $('#modalTituloCurso').text('Crear Nuevo Curso');
        $('#modalCurso').fadeIn();
    });

    // Inscripción
    $('.btn-registrar-inscripcion').on('click', function () {
        $('#formInscripcion')[0].reset();
        // Cargar combos (esto debería invocar a una API para traer alumnos y cursos)
        $('#modalTituloInscripcion').text('Nueva Inscripción a Curso');
        $('#modalInscripcion').fadeIn();
    });

    // Pago
    $('.btn-registrar-pago').on('click', function () {
        $('#formPago')[0].reset();
        $('#modalTituloPago').text('Registrar Pago/Reserva de Aula');
        $('#modalPago').fadeIn();
    });

    // Usuario (Administrador)
    $('.btn-registrar-usuario').on('click', function () {
        $('#formUsuario')[0].reset();
        $('#opcion_usuario').val('1');
        $('#modalTituloUsuario').text('Añadir Administrador');
        $('#usuario_password').attr('required', true);
        $('#modalUsuario').fadeIn();
    });

    // 2. CERRAR MODALES 
    $('.btn-cerrar-modal').on('click', function () {
        $(this).closest('.modal-overlay').fadeOut();
    });

    // 3. ENVIAR FORMULARIO (CREAR O EDITAR) 
    $('#formAlumno').submit(function (e) {
        e.preventDefault();

        // VALIDACIÓN DE SEGURIDAD (DNI y CELULAR)
        const dni = $('#dni').val();
        const cel = $('#celular').val();
        if (dni.length !== 8 || isNaN(dni)) {
            Swal.fire('Atención', 'El DNI debe tener exactamente 8 caracteres numéricos.', 'warning');
            return;
        }
        if (cel.length !== 9 || isNaN(cel)) {
            Swal.fire('Atención', 'El Celular debe tener exactamente 9 caracteres numéricos.', 'warning');
            return;
        }

        let id = $('#id_alumno').val();
        let opcion = $('#opcion').val();
        let metodo = (opcion == '1') ? 'POST' : 'PUT';
        let url = "php/api_estudiante.php" + (metodo === 'PUT' ? "?id=" + id : "");

        // Convertir form a objeto simple para enviarlo como JSON
        let dataArray = $(this).serializeArray();
        let dataObj = {};
        $.map(dataArray, function(n, i){
            dataObj[n['name']] = n['value'];
        });

        // Mapeo de nombres de campos si es necesario (el formulario usa nombres diferentes a la DB en algunos casos)
        // Pero según api_estudiante.php espera DNI_ALUMNO, NOMBRES, etc. 
        // El formulario usa 'dni', 'nombres', 'apellidos'.
        let payload = {
            "DNI_ALUMNO": dataObj.dni,
            "NOMBRES": dataObj.nombres,
            "APELLIDO": dataObj.apellidos,
            "FECHA_NACIMIENTO": dataObj.fecha_nac,
            "EDAD": dataObj.edad,
            "GENERO": dataObj.genero,
            "DIRECCION": dataObj.direccion,
            "CELULAR": dataObj.celular,
            "CORREO": dataObj.correo,
            "NOMBRE_APODERADO": dataObj.apoderado,
            "CELULAR_APODERADO": dataObj.cel_apoderado,
            "USERNAME": dataObj.username,
            "ESTADO": dataObj.estado
        };

        let btnSubmit = $(this).find('button[type="submit"]');
        btnSubmit.prop('disabled', true).text('Procesando...');

        $.ajax({
            url: url,
            type: metodo,
            contentType: "application/json",
            data: JSON.stringify(payload),
            success: function (respuesta) {
                if (respuesta.exito) {
                    $('#modalAlumno').fadeOut();
                    Swal.fire('¡Éxito!', respuesta.mensaje, 'success');
                    cargarAlumnos(); 
                    cargarNotificaciones();
                } else {
                    Swal.fire('Error', respuesta.mensaje, 'error');
                }
            },
            complete: function() {
                btnSubmit.prop('disabled', false).text(opcion == '1' ? 'Registrar' : 'Guardar Cambios');
            }
        });
    });

    // 4. ELIMINAR ALUMNO
    $(document).on('click', '.btn-borrar-estudiante', function () {
        let idAlumno = $(this).data('id');

        Swal.fire({
            title: '¿Eliminar Alumno?',
            text: "Se borrará permanentemente de la Base de Datos.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, eliminar'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "php/api_estudiante.php?id=" + idAlumno,
                    type: "DELETE",
                    success: function (respuesta) {
                        if (respuesta.exito) {
                            cargarAlumnos(); 
                            cargarNotificaciones();
                            Swal.fire('Eliminado', respuesta.mensaje, 'success');
                        }
                    }
                });
            }
        });
    });

    // 5. EDITAR ALUMNO (Botón de Lápiz) 
    $(document).on('click', '.btn-editar', function () {
        let alumnoData = $(this).data('alumno');
        
        // Cargar datos al formulario 
        $('#id_alumno').val(alumnoData.ID_ALUMNO);
        $('#dni').val(alumnoData.DNI_ALUMNO);
        $('#nombres').val(alumnoData.NOMBRES);
        $('#apellidos').val(alumnoData.APELLIDO);
        $('#fecha_nac').val(alumnoData.FECHA_NACIMIENTO);
        $('#edad').val(alumnoData.EDAD);
        $('#genero').val(alumnoData.GENERO);
        $('#estado').val(alumnoData.ESTADO);
        $('#direccion').val(alumnoData.DIRECCION);
        $('#celular').val(alumnoData.CELULAR);
        $('#correo').val(alumnoData.CORREO);
        $('#apoderado').val(alumnoData.NOMBRE_APODERADO);
        $('#cel_apoderado').val(alumnoData.CELULAR_APODERADO);
        $('#username').val(alumnoData.USERNAME);
        
        $('#opcion').val('2'); // Configurar acción: EDITAR 
        $('#modalTitulo').text('Editar Alumno');
        $('#password').removeAttr('required'); // Contraseña opcional al editar

        $('#modalAlumno').fadeIn();
    });

    // 5.1. VER DETALLES DEL REGISTRO (Click en el ojito)
    $(document).on('click', '.btn-ver-detalles', function () {
        let alum = $(this).data('alumno');
        
        // Llenar la ficha de detalles
        $('#det-full-name').text(`${alum.NOMBRES} ${alum.APELLIDO}`);
        $('#det-dni').text(`DNI: ${alum.DNI_ALUMNO}`);

        // Avatar con iniciales
        let inicialNombre = (alum.NOMBRES || '?').charAt(0).toUpperCase();
        let inicialApellido = (alum.APELLIDO || '?').charAt(0).toUpperCase();
        $('#det-iniciales').text(inicialNombre + inicialApellido);

        $('#det-genero').text(alum.GENERO === 'M' ? 'Masculino' : 'Femenino');
        $('#det-edad').text(alum.EDAD);
        $('#det-fecha-nac').text(alum.FECHA_NACIMIENTO);
        $('#det-celular').text(alum.CELULAR);
        $('#det-correo').text(alum.CORREO);
        $('#det-username').text(alum.USERNAME);
        $('#det-direccion').text(alum.DIRECCION);
        $('#det-apoderado').text(alum.NOMBRE_APODERADO);
        $('#det-cel-apoderado').text(alum.CELULAR_APODERADO);

        // Badge de estado dinámico en la ficha
        let badgeClass = 'status-active';
        if(alum.ESTADO === 'Inactivo') badgeClass = 'status-inactive';
        else if(alum.ESTADO === 'En Proceso') badgeClass = 'status-proceso';
        
        $('#det-estado').text(alum.ESTADO).removeClass('status-active status-inactive status-proceso').addClass(badgeClass);

        $('#modalDetallesAlumno').fadeIn();
    });

    // 5.2. VER DETALLES GENÉRICOS (Aulas, Cursos)
    $(document).on('click', '.btn-ver-detalles-generico', function () {
        let tipo = $(this).data('tipo');
        let item = $(this).data('item');
        let html = "";
        let titulo = "";

        if (tipo === 'aula') {
            titulo = "Detalles de la Aula";
            html = `
                <div class="det-item"><p><strong>Nivel:</strong> <span>${item.NIVEL}</span></p></div>
                <div class="det-item"><p><strong>Grado:</strong> <span>${item.GRADO}</span></p></div>
                <div class="det-item"><p><strong>Sección:</strong> <span>${item.SECCION}</span></p></div>
                <hr>
                <div class="det-item"><p><strong>Vacantes Totales:</strong> <span>${item.VACANTES_TOTALES}</span></p></div>
                <div class="det-item"><p><strong>Vacantes Disponibles:</strong> <span style="color: #2c3e50; font-weight: bold;">${item.VACANTES_DISPONIBLES}</span></p></div>
            `;
        } else if (tipo === 'curso') {
            titulo = "Detalles del Curso";
            html = `
                <div class="det-item"><p><strong>Nombre del Curso:</strong> <span style="text-transform: capitalize;">${item.NOMBRE_CURSO}</span></p></div>
                <div class="det-item"><p><strong>Aula Asignada:</strong> <span>${item.NIVEL} ${item.GRADO} "${item.SECCION}"</span></p></div>
                <div class="det-item"><p><strong>Docente Asignado:</strong> <span style="text-transform: capitalize;">${item.DOCENTE}</span></p></div>
                <hr>
                <div class="det-item"><p><strong>Horas Semanales:</strong> <span>${item.HORAS_SEMANALES} h</span></p></div>
                <div class="det-item"><p><strong>Créditos:</strong> <span>${item.CREDITOS}</span></p></div>
            `;
        }

        $('#det-gen-titulo').text(titulo);
        $('#det-gen-body').html(html);
        $('#modalDetallesGenerico').fadeIn();
    });

    /* ======================================================== 
       FUNCIÓN PARA CARGAR LA TABLA DESDE MYSQL 
       ======================================================== */
    // ==========================================
    // CARGADORES DE MÓDULOS (DataTables AJAX)
    // ==========================================

    function cargarAlumnos() {
        if (!dtEstudiantes) {
            dtEstudiantes = $('#modulo-estudiantes table').DataTable({
                ajax: {
                    url: "php/api_estudiante.php",
                    dataSrc: "data"
                },
                columns: [
                    { 
                        data: null, 
                        render: (data, type, row, meta) => meta.row + 1, 
                        className: "text-center", 
                        width: "50px" 
                    },
                    { data: "NOMBRES", className: "text-center" },
                    { data: "APELLIDO", className: "text-center" },
                    { data: "DNI_ALUMNO", className: "text-center" },
                    { data: "FECHA_NACIMIENTO", className: "text-center" },
                    { data: "CELULAR", className: "text-center" },
                    { data: "CORREO", className: "text-center" },
                    { 
                        data: "ESTADO",
                        className: "text-center",
                        render: function(data) {
                            let cls = 'status-active';
                            if(data === 'Inactivo') cls = 'status-inactive';
                            else if(data === 'En Proceso') cls = 'status-proceso';
                            return `<span class="status-badge ${cls}">${data}</span>`;
                        }
                    },
                    {
                        data: null,
                        className: "action-icons text-center",
                        render: function(data, type, row) {
                            let json = JSON.stringify(row).replace(/'/g, "&apos;");
                            return `
                                <i class="fa-solid fa-pen-to-square btn-editar" data-alumno='${json}' title="Editar"></i>
                                <i class="fa-solid fa-eye btn-ver-detalles" data-alumno='${json}' title="Ver Ficha"></i> 
                                <i class="fa-solid fa-trash btn-borrar-estudiante" data-id="${row.ID_ALUMNO}" title="Eliminar"></i>
                            `;
                        }
                    }
                ],
                language: dtLanguage,
                dom: dtDom,
                buttons: dtButtons('Lista de Estudiantes'),
                pageLength: 5,
                responsive: true,
                order: [[0, 'asc']]
            });
        } else {
            dtEstudiantes.ajax.reload(null, false);
        }
    }

    function cargarAulas() {
        if (!dtAulas) {
            dtAulas = $('#modulo-aulas table').DataTable({
                ajax: {
                    url: "php/api_aula.php",
                    dataSrc: "data"
                },
                columns: [
                    { 
                        data: null, 
                        render: (data, type, row, meta) => meta.row + 1, 
                        className: "text-center", 
                        width: "50px" 
                    },
                    { data: "NIVEL", className: "text-center" },
                    { data: "GRADO", className: "text-center" },
                    { data: "SECCION", className: "text-center" },
                    { data: "VACANTES_TOTALES", className: "text-center" },
                    { 
                        data: "VACANTES_DISPONIBLES",
                        className: "text-center",
                        render: (data) => `<span style="font-weight:bold; color:#2c3e50">${data}</span>` 
                    },
                    {
                        data: null,
                        className: "action-icons text-center",
                        render: function(data, type, row) {
                            let json = JSON.stringify(row).replace(/'/g, "&apos;");
                            return `
                                <i class="fa-solid fa-pen-to-square btn-editar-aula" data-aula='${json}'></i>
                                <i class="fa-solid fa-eye btn-ver-detalles-generico" data-tipo="aula" data-item='${json}'></i>
                                <i class="fa-solid fa-trash btn-borrar-aula" data-id="${row.ID_AULA}"></i>
                            `;
                        }
                    }
                ],
                language: dtLanguage,
                dom: dtDom,
                buttons: dtButtons('Lista de Aulas'),
                pageLength: 5,
                responsive: true,
                order: [[0, 'asc']]
            });
        } else {
            dtAulas.ajax.reload(null, false);
        }
    }

    function cargarCursos() {
        if (!dtCursos) {
            dtCursos = $('#modulo-cursos table').DataTable({
                ajax: {
                    url: "php/api_curso.php",
                    dataSrc: "data"
                },
                columns: [
                    { 
                        data: null, 
                        render: (data, type, row, meta) => meta.row + 1, 
                        className: "text-center", 
                        width: "50px" 
                    },
                    { data: "NOMBRE_CURSO", className: "text-center text-capitalize" },
                    { data: null, render: (row) => `${row.NIVEL} ${row.GRADO} "${row.SECCION}"`, className: "text-center" },
                    { data: "DOCENTE", className: "text-center text-capitalize" },
                    { data: "HORAS_SEMANALES", render: (data) => `${data} h`, className: "text-center" },
                    { data: "CREDITOS", className: "text-center" },
                    {
                        data: null,
                        className: "action-icons text-center",
                        render: function(data, type, row) {
                            let json = JSON.stringify(row).replace(/'/g, "&apos;");
                            return `
                                <i class="fa-solid fa-pen-to-square btn-editar-curso" data-curso='${json}'></i>
                                <i class="fa-solid fa-eye btn-ver-detalles-generico" data-tipo="curso" data-item='${json}'></i>
                                <i class="fa-solid fa-trash btn-borrar-curso" data-id="${row.ID_CURSO}"></i>
                            `;
                        }
                    }
                ],
                language: dtLanguage,
                dom: dtDom,
                buttons: dtButtons('Lista de Cursos'),
                pageLength: 5,
                responsive: true,
                order: [[0, 'asc']]
            });
        } else {
            dtCursos.ajax.reload(null, false);
        }
    }

    function cargarInscripciones() {
        if (!dtInscripciones) {
            dtInscripciones = $('#modulo-inscripciones table').DataTable({
                ajax: {
                    url: "php/api_inscripcion.php",
                    dataSrc: "data"
                },
                columns: [
                    { 
                        data: null, 
                        render: (data, type, row, meta) => meta.row + 1, 
                        className: "text-center", 
                        width: "50px" 
                    },
                    { data: null, render: (row) => `${row.NOMBRES} ${row.APELLIDO}`, className: "text-center" },
                    { data: "NOMBRE_CURSO", className: "text-center" },
                    { data: "FECHA_INSCRIPCION", className: "text-center" },
                    {
                        data: "ID_INSCRIPCION",
                        className: "action-icons",
                        render: (id) => `<i class="fa-solid fa-trash btn-borrar-inscripcion" data-id="${id}"></i>`
                    }
                ],
                language: dtLanguage,
                dom: dtDom,
                buttons: dtButtons('Registro de Inscripciones'),
                pageLength: 5,
                responsive: true,
                order: [[0, 'asc']]
            });
        } else {
            dtInscripciones.ajax.reload(null, false);
        }
    }

    function cargarPagos() {
        if (!dtPagos) {
            dtPagos = $('#modulo-pagos table').DataTable({
                ajax: {
                    url: "php/api_pago.php",
                    dataSrc: "data"
                },
                columns: [
                    { 
                        data: null, 
                        render: (data, type, row, meta) => meta.row + 1, 
                        className: "text-center", 
                        width: "50px" 
                    },
                    { data: null, render: (row) => `${row.NOMBRES} ${row.APELLIDO}`, className: "text-center" },
                    { data: null, render: (row) => `${row.NIVEL} ${row.GRADO} "${row.SECCION}"`, className: "text-center" },
                    { 
                        data: "COD_PAGO", 
                        className: "text-center", 
                        render: (data) => `<span style="font-weight:bold; color:#2c3e50">${data}</span>` 
                    },
                    { 
                        data: "ESTADO_PAGO", 
                        className: "text-center",
                        render: function(data) {
                            let cls = data === 'PAGADO' ? 'status-active' : 'status-proceso';
                            return `<span class="status-badge ${cls}">${data}</span>`;
                        }
                    },
                    { data: "FECHA_RESERVA", className: "text-center" },
                    {
                        data: null,
                        className: "action-icons text-center",
                        render: function(data, type, row) {
                            let json = JSON.stringify(row).replace(/'/g, "&apos;");
                            let btnAprobar = row.ESTADO_PAGO === 'PENDIENTE' ? `<i class="fa-solid fa-check btn-aprobar-pago" data-id="${row.ID_RESERVA}" title="Aprobar" style="color:#1cc88a; cursor:pointer; margin-right:10px;"></i>` : '';
                            return `
                                ${btnAprobar}
                                <i class="fa-solid fa-file-pdf btn-pdf-pago" data-pago='${json}' title="PDF" style="color:#e74a3b; cursor:pointer; margin-right:10px;"></i>
                                <i class="fa-solid fa-trash btn-borrar-pago" data-id="${row.ID_RESERVA}" title="Eliminar"></i>
                            `;
                        }
                    }
                ],
                language: dtLanguage,
                dom: dtDom,
                buttons: dtButtons('Control de Pagos'),
                pageLength: 5,
                responsive: true,
                order: [[0, 'asc']]
            });
        } else {
            dtPagos.ajax.reload(null, false);
        }
    }

    function cargarUsuarios() {
        if (!dtUsuarios) {
            dtUsuarios = $('#modulo-configuracion table').DataTable({
                ajax: {
                    url: "php/api_usuario.php",
                    dataSrc: "data"
                },
                columns: [
                    { 
                        data: null, 
                        render: (data, type, row, meta) => meta.row + 1, 
                        className: "text-center", 
                        width: "50px" 
                    },
                    { 
                        data: "USERNAME",
                        className: "text-center",
                        render: function(data) {
                            if (!data) return '<i class="fa-solid fa-user-circle" style="font-size:30px; color:#ccc;"></i>';
                            let userClean = data.trim().toUpperCase();
                            let avatar = "admin.jpg";
                            if(userClean === 'ARIANA') avatar = "ariana.jpg";
                            else if(userClean === 'ANGEL' || userClean === 'YO') avatar = "yo.jpg";
                            
                            return `<img src="img/${avatar}" 
                                         style="width:35px; height:35px; border-radius:50%; border:2px solid #2c3e50; object-fit: cover;" 
                                         onerror="this.onerror=null; this.outerHTML='<i class=\'fa-solid fa-user-circle\' style=\'font-size:30px; color:#ccc;\'></i>';">`;
                        }
                    },
                    { data: "USERNAME", className: "text-center", style: "font-weight:bold;" },
                    { 
                        data: "ESTADO",
                        className: "text-center",
                        render: function(data) {
                            let cls = (data == '1' || data == 'Activo') ? 'status-active' : 'status-inactive';
                            let txt = (data == '1' || data == 'Activo') ? 'Activo' : 'Inactivo';
                            return `<span class="status-badge ${cls}">${txt}</span>`;
                        }
                    },
                    {
                        data: null,
                        className: "action-icons text-center",
                        render: function(data, type, row) {
                            let json = JSON.stringify(row).replace(/'/g, "&apos;");
                            return `
                                <i class="fa-solid fa-pen-to-square btn-editar-usuario" data-user='${json}' title="Editar"></i>
                                <i class="fa-solid fa-trash btn-borrar-usuario" data-id="${row.ID_USUARIO}" title="Eliminar"></i>
                            `;
                        }
                    }
                ],
                language: dtLanguage,
                dom: dtDom,
                buttons: dtButtons('Control de Usuarios'),
                pageLength: 5,
                responsive: true,
                order: [[0, 'asc']]
            });
        } else {
            dtUsuarios.ajax.reload(null, false);
        }
    }

    function cargarNotificaciones() {
        $.get('php/api_notificaciones.php', function(res) {
            if(res.exito) {
                const badge = $('.badge');
                const oldCount = badge.text();
                const count = res.notificaciones.length;

                // Si el número cambió, le damos un efecto de pulso animado
                if (count > oldCount && count > 0) {
                    badge.addClass('notif-pulse');
                    setTimeout(() => badge.removeClass('notif-pulse'), 1000);
                }

                // Actualizar AMBOS badges (clase .badge y id #notif-count)
                badge.text(count);
                $('#notif-count').text(count);

                // Ocultar/mostrar badge
                if(count > 0) {
                    $('.badge').show();
                } else {
                    $('.badge').hide();
                    $('#notif-count').hide();
                }

                let html = '';
                if(count === 0) {
                    html = '<div style="padding:20px; text-align:center; color:#888;"><i class="fa-solid fa-bell-slash" style="font-size:1.5rem; margin-bottom:8px; display:block;"></i><p>Sin notificaciones nuevas</p></div>';
                } else {
                    res.notificaciones.forEach(n => {
                        let icon = n.icon || 'fa-circle-info';
                        let color = n.color || '#2c3e50';
                        let texto = n.texto || n.MENSAJE || 'Notificación';
                        html += `
                            <div class="notification-item" style="display:flex; align-items:center; gap:12px; padding:12px 15px; border-bottom:1px solid #f0f0f0;">
                                <i class="fa-solid ${icon}" style="color:${color}; font-size:1.1rem; flex-shrink:0;"></i>
                                <div>
                                    <p style="margin:0; font-size:0.85rem; color:#333;">${texto}</p>
                                    <span style="font-size:0.75rem; color:#aaa;">Hace un momento</span>
                                </div>
                            </div>
                        `;
                    });
                }
                // Actualizar el contenedor correcto en el HTML
                $('#lista-notificaciones').html(html);
            }
        }, 'json');
    }

    // ==========================================
    // SECCIÓN DE EVENTOS PARA MODALES Y FORMS
    // ==========================================

    // ABRIR MODAL PARA AULA
    $('.btn-registrar-aula').on('click', function () {
        $('#formAula')[0].reset();
        $('#opcion_aula').val('1'); // CREAR
        $('#modalTituloAula').text('Añadir Nueva Aula');
        $('#modalAula').fadeIn();
    });

    // ABRIR MODAL PARA CURSO
    $('.btn-registrar-curso').on('click', function () {
        $('#formCurso')[0].reset();
        
        // Cargar aulas al select
        $.get('php/api_aula.php', function(respuesta) {
            let select = $('#curso_aula_select');
            select.empty().append('<option value="">Seleccione Aula...</option>');
            $.each(respuesta.data, function(i, aula) {
                select.append(`<option value="${aula.ID_AULA}">${aula.NIVEL} - Grado ${aula.GRADO} "${aula.SECCION}"</option>`);
            });
        }, 'json');

        $('#opcion_curso').val('1'); // CREAR
        $('#modalTituloCurso').text('Crear Nuevo Curso');
        $('#modalCurso').fadeIn();
    });

    // ENVIAR FORMULARIO AULA
    $('#formAula').submit(function (e) {
        e.preventDefault();
        
        let id = $('#id_aula').val();
        let opcion = $('#opcion_aula').val();
        let metodo = (opcion == '1') ? 'POST' : 'PUT';
        let url = "php/api_aula.php" + (metodo === 'PUT' ? "?id=" + id : "");

        let dataArray = $(this).serializeArray();
        let dataObj = {};
        $.map(dataArray, function(n, i){
            dataObj[n['name']] = n['value'];
        });

        let payload = {
            "NIVEL": dataObj.nivel,
            "GRADO": dataObj.grado,
            "SECCION": dataObj.seccion,
            "VACANTES_TOTALES": dataObj.vacantes_totales,
            "VACANTES_DISPONIBLES": dataObj.vacantes_disponibles
        };

        let btnSubmit = $(this).find('button[type="submit"]');
        btnSubmit.prop('disabled', true).text('Procesando...');

        $.ajax({
            url: url,
            type: metodo,
            contentType: "application/json",
            data: JSON.stringify(payload),
            success: function (respuesta) {
                if (respuesta.exito) {
                    $('#modalAula').fadeOut();
                    Swal.fire('¡Éxito!', respuesta.mensaje, 'success');
                    cargarAulas();
                    cargarNotificaciones();
                } else {
                    Swal.fire('Error', respuesta.mensaje, 'error');
                }
            },
            complete: function() {
                btnSubmit.prop('disabled', false).text(opcion == '1' ? 'Añadir' : 'Actualizar');
            }
        });
    });

    // ENVIAR FORMULARIO CURSO
    $('#formCurso').submit(function (e) {
        e.preventDefault();

        let id = $('#id_curso').val();
        let opcion = $('#opcion_curso').val();
        let metodo = (opcion == '1') ? 'POST' : 'PUT';
        let url = "php/api_curso.php" + (metodo === 'PUT' ? "?id=" + id : "");

        let dataArray = $(this).serializeArray();
        let dataObj = {};
        $.map(dataArray, function(n, i){
            dataObj[n['name']] = n['value'];
        });

        let payload = {
            "NOMBRE_CURSO": dataObj.nombre_curso,
            "ID_AULA": dataObj.id_aula,
            "DOCENTE": dataObj.docente,
            "HORAS_SEMANALES": dataObj.horas_semanales,
            "CREDITOS": dataObj.creditos
        };

        let btnSubmit = $(this).find('button[type="submit"]');
        btnSubmit.prop('disabled', true).text('Procesando...');

        $.ajax({
            url: url,
            type: metodo,
            contentType: "application/json",
            data: JSON.stringify(payload),
            success: function (respuesta) {
                if (respuesta.exito) {
                    $('#modalCurso').fadeOut();
                    Swal.fire('¡Éxito!', respuesta.mensaje, 'success');
                    cargarCursos();
                    cargarNotificaciones();
                } else {
                    Swal.fire('Error', respuesta.mensaje, 'error');
                }
            },
            complete: function() {
                btnSubmit.prop('disabled', false).text(opcion == '1' ? 'Crear' : 'Actualizar');
            }
        });
    });

    // ----------------------------------------------------
    // ELIMINAR Y EDITAR AULAS
    // ----------------------------------------------------
    $(document).on('click', '.btn-borrar-aula', function () {
        let fila = $(this).closest('tr');
        let idAula = fila.data('id');

        Swal.fire({
            title: '¿Eliminar Aula?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, eliminar'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "php/api_aula.php?id=" + idAula,
                    type: "DELETE",
                    success: function (respuesta) {
                        if (respuesta.exito) {
                            cargarAulas(); 
                            cargarNotificaciones();
                            Swal.fire('Eliminada', respuesta.mensaje, 'success');
                        }
                    }
                });
            }
        });
    });

    $(document).on('click', '.btn-editar-aula', function () {
        let aulaData = $(this).data('aula');
        
        $('#id_aula').val(aulaData.ID_AULA);
        $('#nivel').val(aulaData.NIVEL);
        $('#grado').val(aulaData.GRADO);
        $('#seccion').val(aulaData.SECCION);
        $('#vacantes_totales').val(aulaData.VACANTES_TOTALES);
        $('#vacantes_disponibles').val(aulaData.VACANTES_DISPONIBLES);
        
        $('#opcion_aula').val('2'); // EDITAR
        $('#modalTituloAula').text('Editar Aula');
        $('#modalAula').fadeIn();
    });

    // ----------------------------------------------------
    // ELIMINAR Y EDITAR CURSOS
    // ----------------------------------------------------
    $(document).on('click', '.btn-borrar-curso', function () {
        let fila = $(this).closest('tr');
        let idCurso = fila.data('id');

        Swal.fire({
            title: '¿Eliminar Curso?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, eliminar'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "php/api_curso.php?id=" + idCurso,
                    type: "DELETE",
                    success: function (respuesta) {
                        if (respuesta.exito) {
                            cargarCursos(); 
                            cargarNotificaciones();
                            Swal.fire('Eliminado', respuesta.mensaje, 'success');
                        }
                    }
                });
            }
        });
    });

    $(document).on('click', '.btn-editar-curso', function () {
        let cursoData = $(this).data('curso');
        
        // Cargar select de aulas antes de abrir
        $.get('php/api_aula.php', function(respuesta) {
            let select = $('#curso_aula_select');
            select.empty().append('<option value="">Seleccione Aula...</option>');
            $.each(respuesta.data, function(i, aula) {
                let selected = (aula.ID_AULA == cursoData.ID_AULA) ? 'selected' : '';
                select.append(`<option value="${aula.ID_AULA}" ${selected}>${aula.NIVEL} - Grado ${aula.GRADO} "${aula.SECCION}"</option>`);
            });
        }, 'json');
        
        $('#id_curso').val(cursoData.ID_CURSO);
        $('#nombre_curso').val(cursoData.NOMBRE_CURSO);
        $('#docente').val(cursoData.DOCENTE);
        $('#horas_semanales').val(cursoData.HORAS_SEMANALES);
        $('#creditos').val(cursoData.CREDITOS);
        
        $('#opcion_curso').val('2'); // EDITAR
        $('#modalTituloCurso').text('Editar Curso');
        $('#modalCurso').fadeIn();
    });


    /* ======================================================== 
       SECCIÓN DE ADMINISTRADORES (Configuración)
       ======================================================== */

    $(document).on('click', '.btn-borrar-usuario', function () {
        let idUser = $(this).data('id');

        Swal.fire({
            title: '¿Eliminar Administrador?',
            icon: 'warning',
            text: 'Esta acción no se puede deshacer.',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, eliminar'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "php/api_usuario.php?id=" + idUser,
                    type: "DELETE",
                    success: function (respuesta) {
                        if (respuesta.exito) {
                            cargarUsuarios(); 
                            Swal.fire('Eliminado', respuesta.mensaje, 'success');
                        }
                    }
                });
            }
        });
    });

    $(document).on('click', '.btn-editar-usuario', function () {
        let userData = $(this).data('user');
        
        $('#id_usuario').val(userData.ID_USUARIO);
        $('#usuario_username').val(userData.USERNAME);
        $('#usuario_password').removeAttr('required'); // No obligatorio al editar
        
        $('#opcion_usuario').val('2'); // EDITAR
        $('#modalTituloUsuario').text('Editar Administrador');
        $('#modalUsuario').fadeIn();
    });


    /* ======================================================== 
       SECCIÓN DE INSCRIPCIONES A CURSOS
       ======================================================== */

    // ABRIR MODAL INSCRIPCIÓN Y CARGAR SELECTS
    $('.btn-registrar-inscripcion').on('click', function () {
        $('#formInscripcion')[0].reset();
        
        // Cargar alumnos activos al select
        $.get('php/api_estudiante.php', function(respuesta) {
            let select = $('#id_alumno_select');
            select.empty().append('<option value="">Seleccione Alumno...</option>');
            $.each(respuesta.data, function(i, alum) {
                let extra = alum.ESTADO !== 'Activo' ? ' (Inactivo)' : '';
                select.append(`<option value="${alum.ID_ALUMNO}">${alum.NOMBRES} ${alum.APELLIDO}${extra}</option>`);
            });
        }, 'json');

        // Cargar cursos al select
        $.get('php/api_curso.php', function(respuesta) {
            let select = $('#id_curso_select');
            select.empty().append('<option value="">Seleccione Curso...</option>');
            $.each(respuesta.data, function(i, curs) {
                select.append(`<option value="${curs.ID_CURSO}">${curs.NOMBRE_CURSO} - ${curs.DOCENTE}</option>`);
            });
        }, 'json');

        $('#modalInscripcion').fadeIn();
    });


    // ENVIAR FORMULARIO INSCRIPCIÓN (ÚNICO HANDLER)
    $(document).on('submit', '#formInscripcion', function (e) {
        e.preventDefault();
        let payload = {
            "ID_ALUMNO": $('#id_alumno_select').val(),
            "ID_CURSO": $('#id_curso_select').val()
        };

        let btnSubmit = $(this).find('button[type="submit"]');
        btnSubmit.prop('disabled', true).text('Procesando...');

        $.ajax({
            url: "php/api_inscripcion.php",
            type: "POST",
            contentType: "application/json",
            data: JSON.stringify(payload),
            success: function (respuesta) {
                if (respuesta.exito) {
                    $('#modalInscripcion').fadeOut();
                    Swal.fire('¡Registrado!', respuesta.mensaje, 'success');
                    cargarInscripciones();
                    cargarNotificaciones();
                } else {
                    Swal.fire('Atención', respuesta.mensaje, 'warning');
                }
            },
            complete: function() {
                btnSubmit.prop('disabled', false).text('Inscribir');
            }
        });
    });

    // ELIMINAR INSCRIPCIÓN
    $(document).on('click', '.btn-borrar-inscripcion', function () {
        let id = $(this).data('id');
        Swal.fire({
            title: '¿Eliminar Inscripción?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, eliminar'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "php/api_inscripcion.php?id=" + id,
                    type: "DELETE",
                    success: function (res) {
                        if (res.exito) {
                            cargarInscripciones();
                            cargarNotificaciones();
                            Swal.fire('Eliminado', res.mensaje, 'success');
                        }
                    }
                });
            }
        });
    });


    /* ======================================================== 
       SECCIÓN DE PAGOS / RESERVAS DE AULAS
       ======================================================== */

    $('.btn-registrar-pago').on('click', function () {
        $('#formPago')[0].reset();
        
        // Cargar alumnos para el select de pagos
        $.get('php/api_estudiante.php', function(respuesta) {
            let select = $('#pago_alumno_select');
            select.empty().append('<option value="">Seleccione Alumno...</option>');
            $.each(respuesta.data, function(i, alum) {
                select.append(`<option value="${alum.ID_ALUMNO}">${alum.NOMBRES} ${alum.APELLIDO}</option>`);
            });
        }, 'json');

        // Cargar aulas para el select de pagos
        $.get('php/api_aula.php', function(respuesta) {
            let select = $('#pago_aula_select');
            select.empty().append('<option value="">Seleccione Aula...</option>');
            $.each(respuesta.data, function(i, aula) {
                select.append(`<option value="${aula.ID_AULA}">${aula.NIVEL} - Grado ${aula.GRADO} "${aula.SECCION}"</option>`);
            });
        }, 'json');

        $('#modalPago').fadeIn();
    });

    $(document).on('submit', '#formPago', function (e) {
        e.preventDefault();
        let payload = {
            "ID_ALUMNO": $('#pago_alumno_select').val(),
            "ID_AULA": $('#pago_aula_select').val(),
            "COD_PAGO": $('#cod_pago').val()
        };

        let btnSubmit = $(this).find('button[type="submit"]');
        btnSubmit.prop('disabled', true).text('Procesando...');

        $.ajax({
            url: "php/api_pago.php",
            type: "POST",
            contentType: "application/json",
            data: JSON.stringify(payload),
            success: function(res) {
                if (res.exito) {
                    $('#modalPago').fadeOut();
                    Swal.fire('¡Registrado!', res.mensaje, 'success');
                    cargarPagos();
                    cargarNotificaciones();
                } else {
                    Swal.fire('Atención', res.mensaje, 'warning');
                }
            },
            complete: function() {
                btnSubmit.prop('disabled', false).text('Registrar Pago');
            }
        });
    });


    $(document).on('click', '.btn-aprobar-pago', function () {
        let id = $(this).data('id');
        Swal.fire({
            title: '¿Confirmar Pago?',
            text: "Se marcará como PAGADO permanentemente.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Sí, Aprobar'
        }).then((result) => {
            if(result.isConfirmed) {
                $.ajax({
                    url: "php/api_pago.php?id=" + id,
                    type: "PUT",
                    success: function(res) {
                        if(res.exito) {
                            cargarPagos();
                            cargarNotificaciones();
                            Swal.fire('Aprobado', res.mensaje, 'success');
                        }
                    }
                });
            }
        });
    });

    $(document).on('click', '.btn-borrar-pago', function () {
        let id = $(this).data('id');
        Swal.fire({
            title: '¿Eliminar Registro?',
            text: "Esta acción no se puede deshacer.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Sí, Eliminar'
        }).then((result) => {
            if(result.isConfirmed) {
                $.ajax({
                    url: "php/api_pago.php?id=" + id,
                    type: "DELETE",
                    success: function(res) {
                        if(res.exito) {
                            cargarPagos();
                            cargarNotificaciones();
                            Swal.fire('Eliminado', res.mensaje, 'success');
                        }
                    }
                });
            }
        });
    });

    // GENERACIÓN DE PDF RECIBO DE PAGO (Usando jsPDF)
    $(document).on('click', '.btn-pdf-pago', function () {
        const item = $(this).data('pago');
        const { jsPDF } = window.jspdf;
        // Formato A5 horizontal (Media hoja A4)
        const doc = new jsPDF('l', 'mm', 'a5');

        // Configuración de Colores (Azul Corporativo #2c3e50)
        const primaryColor = [44, 62, 80]; 
        const secondaryColor = [52, 73, 94]; 

        // 1. Cabecera / Fondo (Reducido para A5)
        doc.setFillColor(...primaryColor);
        doc.rect(0, 0, 210, 40, 'F');
        
        doc.setTextColor(255, 255, 255);
        doc.setFontSize(22);
        doc.setFont("helvetica", "bold");
        doc.text("SISTEMA DE MATRÍCULA", 105, 18, { align: "center" });
        
        doc.setFontSize(12);
        doc.setFont("helvetica", "normal");
        doc.text("RECIBO OFICIAL DE PAGO", 105, 28, { align: "center" });

        // 2. Cuerpo del Documento
        doc.setTextColor(...secondaryColor);
        doc.setFontSize(11);
        doc.setFont("helvetica", "bold");
        doc.text(`CÓDIGO DE RESERVA: ${item.COD_PAGO}`, 15, 50);
        
        doc.setFont("helvetica", "normal");
        doc.setFontSize(9);
        doc.text(`Fecha de Impresión: ${new Date().toLocaleString()}`, 15, 56);
        
        // Cuadro de Información del Alumno (Subido)
        doc.setDrawColor(...primaryColor);
        doc.setLineWidth(0.5);
        doc.rect(15, 65, 180, 35);
        
        doc.setFont("helvetica", "bold");
        doc.text("DATOS DEL ESTUDIANTE", 20, 73);
        doc.setFont("helvetica", "normal");
        doc.text(`Nombre Completo: ${item.NOMBRES} ${item.APELLIDO}`, 20, 81);
        doc.text(`Aula / Grado: ${item.NIVEL} - ${item.GRADO}° "${item.SECCION}"`, 20, 89);
        doc.text(`Estado del Pago: ${item.ESTADO_PAGO}`, 20, 97);

        // 3. Tabla de Conceptos (AutoTable - startY ajustado)
        const monto = item.ESTADO_PAGO === 'PENDIENTE' ? 'S/ 300.00' : 'S/ 300.00';
        const estadoMonto = item.ESTADO_PAGO === 'PENDIENTE' ? '(PENDIENTE)' : '(PAGADO)';

        if (typeof doc.autoTable === 'function') {
            doc.autoTable({
                startY: 105,
                head: [['Descripción del Concepto', 'Monto', 'Operación']],
                body: [
                    ['Matrícula Escolar y Derecho de Vacante', `${monto} ${estadoMonto}`, item.COD_PAGO]
                ],
                headStyles: { fillColor: primaryColor, textColor: [255, 255, 255] },
                alternateRowStyles: { fillColor: [245, 245, 245] },
                margin: { left: 15, right: 15 },
                styles: { fontSize: 9 }
            });
        }

        // 4. Footer / Firma (Ajustado para dar más aire)
        let finalY = (doc.lastAutoTable ? doc.lastAutoTable.finalY : 105) + 18;
        
        doc.line(70, finalY, 140, finalY);
        doc.setFontSize(9);
        doc.text("Firma de Administración", 105, finalY + 5, { align: "center" });
        
        // Guardar PDF
        doc.save(`Recibo_${item.COD_PAGO}.pdf`);
    });

    // SALUDO DINÁMICO SEGÚN HORA DEL DÍA
    function actualizarSaludo() {
        let hora = new Date().getHours();
        let saludo = hora < 12 ? '☀️ Buenos días' : hora < 19 ? '🌤️ Buenas tardes' : '🌙 Buenas noches';
        let fechaHoy = new Date().toLocaleDateString('es-PE', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
        
        $('#saludo-hora').text(saludo);
        $('#fecha-hoy').text(fechaHoy.charAt(0).toUpperCase() + fechaHoy.slice(1));
    }
    actualizarSaludo();

    // --- INICIALIZACIÓN FINAL ---
    const urlParams = new URLSearchParams(window.location.search);
    const moduloParam = urlParams.get('modulo');
    const savedModulo = sessionStorage.getItem('active_modulo') || 'dashboard';

    if (moduloParam) {
        cambiarModulo(moduloParam);
    } else {
        cambiarModulo(savedModulo);
    }

    // Cargar componentes globales por primera vez
    cargarNotificaciones();
    
    // Activar POLLING en tiempo real: Actualizar campanita cada 7 segundos automáticamente
    setInterval(cargarNotificaciones, 7000);

    console.log("SISTEMA LISTO Y CARGADO.");
});
