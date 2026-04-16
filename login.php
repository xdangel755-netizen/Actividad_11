<?php
// Evitar que el navegador guarde en el historial los datos del login
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <!-- Font Awesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Fuentes de Google Fonts-->
    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap"
        rel="stylesheet">

    <!-- Hoja de estilos principal -->
    <link rel="stylesheet" href="CSS/styles-login.css">

    <!-- Link Favicon -->
    <link rel="shortcut icon" href="favicon.ico" type="image/x-icon">

    <!-- Link de Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

    <!-- Libreria JQuery-->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"
        integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
</head>

<body>
    <article class="login-card">
        <h1 class="titulo">Inicio de Sesión</h1>
        <form id="loginForm">
            <input type="text" id="txtUsuario" placeholder="Usuario" required>
            <input type="password" id="txtPassword" placeholder="Contraseña" required>
            <button type="submit">Iniciar Sesión</button>
        </form>
        <div id="mensaje"></div>
    </article>

    <!-- Libreria SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Script de Login -->
    <script src="js/login.js"></script>

    <!-- Limpiar campos al cargar (por retroceso o refresco) -->
    <script>
        $(document).ready(function() {
            // Se asegura de que los campos estén vacíos incluso si se le da al botón "Atrás"
            $('#txtUsuario').val('').blur();
            $('#txtPassword').val('').blur();
            
            // Refuerzo para navegadores que guardan el estado
            window.onpageshow = function(event) {
                document.getElementById('txtUsuario').value = "";
                document.getElementById('txtPassword').value = "";
            };
        });
    </script>
</body>

</html>
