<!doctype html>
<html lang="es" data-bs-theme="auto">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script>
        (function() {
            const theme = window.matchMedia("(prefers-color-scheme: dark)").matches ? "dark" : "light";
            document.documentElement.setAttribute("data-bs-theme", theme);
        })();
    </script>
    <style>
        /* Imagen para el modo claro */
        .img-light {
            display: block;
        }

        .img-dark {
            display: none;
        }

        /* Cambiar en modo oscuro */
        [data-bs-theme="dark"] .img-light {
            display: none;
        }

        [data-bs-theme="dark"] .img-dark {
            display: block;
        }
    </style>
</head>

<body class="d-flex justify-content-center align-items-center vh-100">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-4">
                <div class="p-4 text-center">
                    <img src="public/img/imagen-oscura.png" alt="Imagen clara" class="img-light img-fluid mx-auto" style="max-width: 100px; height: auto;">
                    <img src="public/img/imagen-clara.png" alt="Imagen oscura" class="img-dark img-fluid mx-auto" style="max-width: 100px; height: auto;">
                </div>
                <div class="card">
                    <div class="card-body">
                        <h3 class="text-center">Iniciar Sesión</h3>
                        <?php 
                        if (isset($_SESSION['error'])): ?>
                            <div class="alert alert-danger"> <?= $_SESSION['error'];
                                                                unset($_SESSION['error']); ?> </div>
                        <?php endif; ?>
                        <form action="/cacaorocha/auth/authenticate" method="POST">
                            <div class="mb-3">
                                <label for="email" class="form-label">Correo Electrónico</label>
                                <input type="email" name="email" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Contraseña</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Ingresar</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>