$(document).ready(function () {
    // 0. CAPTURAR EL USUARIO DE LA SESIÓN LOCAL
    let nombreSesion = sessionStorage.getItem('sesion_usuario');
    if(nombreSesion) {
        // Capitalizar la primera letra (ej: angel -> Angel)
        nombreSesion = nombreSesion.charAt(0).toUpperCase() + nombreSesion.slice(1).toLowerCase();
        
        // Actualizar banner, sidebar y header
        $('.banner-content h2').text('Bienvenido al panel, ' + nombreSesion);
        $('.user-info h3').text(nombreSesion);
        $('.user-dropdown h3').html(nombreSesion + ' <i class="fa-solid fa-chevron-down"></i>');
    }

    /* ======================================================== 
       FUNCIÓN PARA CARGAR DASHBOARD (INICIO) 
       ======================================================== */
    let chart1 = null;
    let chart2 = null;

    function cargarDashboard() {
        $.ajax({
            url: "php/crud-dashboard.php",
            type: "GET",
            dataType: "json",
            success: function(respuesta) {
                if(respuesta.exito) {
                    let d = respuesta.datos;
                    
                    // Animación de números para KPIs
                    animarNumero('#kpiTotalAlumnos', d.total_alumnos);
                    animarNumero('#kpiTotalAulas', d.total_aulas);
                    animarNumero('#kpiTotalCursos', d.total_cursos);
                    animarNumero('#kpiAlumnosActivos', d.alumnos_activos);

                    // Preparar datos para Gráfico 1 (Género)
                    let labelsG = [];
                    let dataG = [];
                    let coloresG = [];
                    d.generos.forEach(g => {
                        labelsG.push(g.GENERO === 'M' ? 'Masculino' : (g.GENERO === 'F' ? 'Femenino' : 'Otro'));
                        dataG.push(g.CANTIDAD);
                        if(g.GENERO === 'M') coloresG.push('#3498DB');       // Azul para Masculino
                        else if(g.GENERO === 'F') coloresG.push('#E84393');  // Rosa para Femenino
                        else coloresG.push('#F39C12');                        // Naranja para Otro
                    });

                    // Preparar datos para Gráfico 2 (Estado)
                    let labelsE = [];
                    let dataE = [];
                    let coloresE = [];
                    d.estados.forEach(e => {
                        labelsE.push(e.ESTADO);
                        dataE.push(e.CANTIDAD);
                        if(e.ESTADO === 'Activo') coloresE.push('#2ECC71');
                        else if(e.ESTADO === 'Inactivo') coloresE.push('#D63031');
                        else coloresE.push('#F1C40F');
                    });

                    // Renderizar Gráficos
                    if(chart1) chart1.destroy();
                    chart1 = new Chart(document.getElementById('chartGenero'), {
                        type: 'doughnut',
                        data: {
                            labels: labelsG,
                            datasets: [{
                                data: dataG,
                                backgroundColor: coloresG,
                                borderWidth: 0
                            }]
                        },
                        options: { responsive: true, maintainAspectRatio: false }
                    });

                    if(chart2) chart2.destroy();
                    chart2 = new Chart(document.getElementById('chartEstado'), {
                        type: 'pie',
                        data: {
                            labels: labelsE,
                            datasets: [{
                                data: dataE,
                                backgroundColor: coloresE,
                                borderWidth: 0
                            }]
                        },
                        options: { responsive: true, maintainAspectRatio: false }
                    });
                }
            }
        });
    }

    function animarNumero(id, maxValue) {
        $({ countNum: 0 }).animate({ countNum: maxValue }, {
            duration: 1500,
            easing: 'swing',
            step: function () {
                $(id).text(Math.floor(this.countNum));
            },
            complete: function () {
                $(id).text(this.countNum);
            }
        });
    }

    cargarDashboard();

    function cargarNotificaciones() {
        $.ajax({
            url: "php/api_notificaciones.php",
            type: "GET",
            dataType: "json",
            success: function(res) {
                if (res.exito) {
                    let lista = $('#lista-notificaciones');
                    let badge = $('.notification-wrapper .badge');
                    let countSpan = $('#notif-count');
                    
                    lista.empty();
                    countSpan.text(res.total);
                    
                    if (res.total > 0) {
                        badge.text(res.total).show();
                        res.notificaciones.forEach(n => {
                            lista.append(`
                                <div style="padding: 10px; border-bottom: 1px solid #eee; font-size: 0.85rem; display: flex; align-items: center; gap: 10px;">
                                    <i class="fa-solid ${n.icon}" style="color: ${n.color}; width: 20px; text-align: center;"></i>
                                    <span>${n.texto}</span>
                                </div>
                            `);
                        });
                    } else {
                        badge.hide();
                        lista.append('<div style="padding: 20px; text-align: center; color: #888; font-size: 0.85rem;">No hay notificaciones nuevas</div>');
                    }
                }
            }
        });
    }

    cargarNotificaciones();

    // Actualizar notificaciones cada 5 minutos
    setInterval(cargarNotificaciones, 300000);

    // Activar estados MPAs en sidebar
    // En este archivo sabemos que estamos en dashboard.html, los enlaces funcionarán por el href

    // ==========================================
    // SECCIÓN DE HEADER / NAVEGACIÓN SUPERIOR
    // ==========================================
    
    // Notificaciones Toggle
    $('#btnNotificaciones').on('click', function(e) {
        e.stopPropagation();
        $('#dropdownNotificaciones').toggle();
        $('#dropdownUser').hide();
    });

    // Menú de Usuario Toggle
    $('#btnUserMenu').on('click', function(e) {
        e.stopPropagation();
        $('#dropdownUser').toggle();
        $('#dropdownNotificaciones').hide();
    });

    // Cerrar dropdowns al hacer clic fuera
    $(document).on('click', function() {
        $('#dropdownNotificaciones').hide();
        $('#dropdownUser').hide();
    });

    // ==========================================
    // SECCIÓN DE BÚSQUEDA GLOBAL (UNIVERSAL)
    // ==========================================
    
    function ejecutarBusquedaManual() {
        let busqueda = $('#globalSearchInput').val().trim();
        if(busqueda.length > 0) {
            // Si hay resultados en el dropdown, tomar el primero
            let primerResultado = $('.search-result-item').first();
            if(primerResultado.length > 0) {
                primerResultado.click();
            } else {
                // Si no hay resultados cargados aún, por defecto ir a estudiantes
                window.location.href = `dashboard2.php?modulo=estudiantes&search=${encodeURIComponent(busqueda)}`;
            }
        }
    }

    // Búsqueda en tiempo real (Live Search)
    $('#globalSearchInput').on('input', function() {
        let q = $(this).val().trim();
        let container = $('#searchResultsContainer');

        if (q.length < 2) {
            container.hide().empty();
            return;
        }

        $.ajax({
            url: "php/api_universal_search.php",
            type: "GET",
            data: { q: q },
            success: function(res) {
                container.empty();
                if (res.exito && res.resultados.length > 0) {
                    
                    // Agrupar resultados por tipo para las etiquetas de categoría
                    let categorias = {
                        estudiante: "Estudiantes",
                        aula: "Aulas",
                        curso: "Cursos"
                    };

                    let agrupados = {};
                    res.resultados.forEach(r => {
                        if(!agrupados[r.tipo]) agrupados[r.tipo] = [];
                        agrupados[r.tipo].push(r);
                    });

                    // Renderizar agrupados
                    Object.keys(agrupados).forEach(cat => {
                        container.append(`<div class="search-category-label">${categorias[cat]}</div>`);
                        agrupados[cat].forEach(item => {
                            let itemHtml = `
                                <div class="search-result-item" data-tipo="${item.tipo}" data-id="${item.id}" data-titulo="${item.titulo}">
                                    <div class="search-result-icon"><i class="fa-solid ${item.icon}"></i></div>
                                    <div class="search-result-info">
                                        <h4>${item.titulo}</h4>
                                        <p>${item.subtitulo}</p>
                                    </div>
                                </div>
                            `;
                            container.append(itemHtml);
                        });
                    });
                    container.fadeIn(200);
                } else {
                    container.append('<div class="search-no-results">Sin coincidencias</div>');
                    container.fadeIn(200);
                }
            }
        });
    });

    // Manejar clic en un resultado
    $(document).on('click', '.search-result-item', function() {
        let tipo = $(this).data('tipo');
        let titulo = $(this).data('titulo');
        
        let modulo = "estudiantes";
        if(tipo === 'aula') modulo = "aulas";
        if(tipo === 'curso') modulo = "cursos";

        window.location.href = `dashboard2.php?modulo=${modulo}&search=${encodeURIComponent(titulo)}`;
    });

    // Cerrar resultados al perder foco
    $(document).on('click', function(e) {
        if (!$(e.target).closest('.search-bar').length) {
            $('#searchResultsContainer').fadeOut(200);
        }
    });

    $('#globalSearchInput').on('keypress', function(e) {
        if(e.which === 13) { // Tecla Enter
            ejecutarBusquedaManual();
        }
    });

    $('#btnGlobalSearch').on('click', function() {
        ejecutarBusquedaManual();
    });
});
