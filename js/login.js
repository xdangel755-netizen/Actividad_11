$(document).ready(function () {
    $("#loginForm").submit(function (evento) {
        // Esta funcion evita el parpadeo o recarga de pagina
        evento.preventDefault();

        // Captura de datos del usuario y contraseña en una variable
        let usuario = $("#txtUsuario").val();
        let password = $("#txtPassword").val();

        $.ajax({
            url: "php/login.php",
            type: "POST",
            dataType: "json",
            data: {
                user: usuario,
                pass: password,
            },
            beforeSend: function () {
                // 1. Mensaje de carga mientras PHP consulta la Base de Datos
                Swal.fire({
                    title: "Validando credenciales...",
                    html: "Por favor, espere un momento.",
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    },
                });
            },
            success: function (respuesta) {
                if (respuesta.exito) {
                    // Guardamos el nombre de usuario localmente
                    sessionStorage.setItem('sesion_usuario', respuesta.usuario);
                    // IMPORTANTE: Limpiar el último módulo visitado para que siempre abra el Dashboard Principal al iniciar sesión
                    sessionStorage.removeItem('active_modulo');

                    // 2. CASO DE ÉXITO: Animación verde
                    Swal.fire({
                        icon: "success",
                        title: "¡Acceso Concedido!",
                        text: "Redirigiendo al sistema...",
                        showConfirmButton: false,
                        timer: 2000, // El popup se cierra solo en 2 segundos
                    }).then(() => {
                        // Redirigir al dashboard seguro (versión PHP con sesión)
                        window.location.href = "dashboard2.php";
                    });
                } else {
                    // 3. CASO DE ERROR: Animación roja (Credenciales inválidas)
                    Swal.fire({
                        icon: "error",
                        title: "Acceso Denegado",
                        text: "El usuario o la contraseña son incorrectos.",
                        confirmButtonColor: "#d33", // Color rojo para el botón
                    });
                }
            },
            error: function () {
                // 4. CASO DE ERROR CRÍTICO: Falla en el servidor o ruta
                Swal.fire({
                    icon: "error",
                    title: "¡Error de conexión!",
                    text: "No se pudo conectar con el servidor.",
                    confirmButtonColor: "#3085d6",
                });
            },
        });
    });
});
