<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Error - Página no encontrada</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        (function() {
            const theme = window.matchMedia("(prefers-color-scheme: dark)").matches ? "dark" : "light";
            document.documentElement.setAttribute("data-bs-theme", theme);
        })();
    </script>
    <style>
        body {
            height: 100vh;
            overflow: hidden;
            display: flex;
            align-items: center;
            background-color: #f8f9fa;
        }

        .error-container {
            max-width: 800px;
            padding: 2rem;
        }

        .error-code {
            font-size: 150px;
            font-weight: 700;
            line-height: 1;
            background: linear-gradient(120deg, #4f46e5, #0ea5e9);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 0;
        }

        .error-img {
            max-width: 100%;
            height: auto;
        }

        .floating {
            animation: floating 3s ease-in-out infinite;
        }

        @keyframes floating {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
            100% { transform: translateY(0px); }
        }

        .home-button {
            padding: 12px 30px;
            font-size: 1.1rem;
            font-weight: 500;
            transition: all 0.3s;
            box-shadow: 0 4px 6px rgba(50, 50, 93, 0.11), 0 1px 3px rgba(0, 0, 0, 0.08);
        }

        .home-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 7px 14px rgba(50, 50, 93, 0.1), 0 3px 6px rgba(0, 0, 0, 0.08);
        }

        .particles {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            overflow: hidden;
        }

        .particle {
            position: absolute;
            border-radius: 50%;
            background: linear-gradient(120deg, #4f46e5, #0ea5e9);
            opacity: 0.3;
        }

        .error-details {
            margin-top: 1.5rem;
        }

        [data-bs-theme="dark"] body {
            background-color: #212529;
        }

        [data-bs-theme="dark"] .error-code {
            background: linear-gradient(120deg, #6366f1, #38bdf8);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
    </style>
</head>

<body>
    <div class="particles" id="particles"></div>
    <div class="container">
        <div class="row justify-content-center align-items-center">
            <div class="col-md-10">
                <div class="card border-0 bg-transparent">
                    <div class="card-body p-0">
                        <div class="row g-0">
                            <div class="col-lg-6 d-flex align-items-center justify-content-center p-5">
                                <div class="error-container text-center text-lg-start">
                                    <h1 class="error-code mb-2">Error</h1>
                                    <h2 class="display-6 fw-bold mb-3">¡Ups! Ha ocurrido un error</h2>
                                    <p class="lead text-muted mb-4">Contacte al administrador</p>
                                    <div class="error-details">
                                        <div class="d-grid gap-2 d-md-block">
                                            <a href="/cacaorocha" class="btn btn-primary btn-lg home-button">
                                                <i class="fas fa-home me-2"></i> Volver al inicio
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6 d-none d-lg-block p-4">
                                <div class="floating text-center">
                                    <svg class="error-img" viewBox="0 0 500 500" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M416.1,175.8c-13.3-44.4-35.2-80.5-65.5-107.4C313.5,37.4,265,22.3,208.1,22.3c-43.9,0-84.7,12.4-118.4,36.8
                                            C56,82.9,32.3,119.7,19.5,165c-8.8,31.1-10.8,65.2-5.6,97.4c4.4,27.7,14.5,53.5,29.3,74.9c19.4,28.1,47.1,46.1,80.7,52.3
                                            c22.9,4.2,47.4,3.8,71.7-1.3c17.3-3.6,33.9-9.9,49.2-18.5c38,26,75.7,31.9,117.2,18.6c17.7-5.7,33.8-14.5,48.1-26.4
                                            c28.2-23.6,50.5-59.6,62.1-100.4C482.8,220.4,429.3,223.2,416.1,175.8z" fill="#f8f9fa"/>
                                        <ellipse cx="175.1" cy="175.1" rx="25.1" ry="25.1" fill="#4f46e5"/>
                                        <ellipse cx="317.9" cy="175.1" rx="25.1" ry="25.1" fill="#4f46e5"/>
                                        <path d="M246.5,293.4c-41.6,0-61.4-20.5-62.5-21.8c-2.9-3.4-2.5-8.5,0.9-11.4c3.4-2.9,8.5-2.5,11.4,0.9
                                            c0.4,0.4,15.5,17.3,50.2,17.3c33.7,0,49.3-16.9,49.5-17.3c2.9-3.4,8-3.8,11.4-0.9c3.4,2.9,3.8,8,0.9,11.4
                                            C307.1,273,286.8,293.4,246.5,293.4z" fill="#4f46e5"/>
                                        <path d="M89.3,179.7c-1.2,0-2.3-0.2-3.5-0.7c-4.4-1.9-6.4-7-4.4-11.4c16.9-39.5,47.5-68.1,86.3-80.7
                                            c4.6-1.5,9.5,1,11,5.6c1.5,4.6-1,9.5-5.6,11c-33.6,10.9-60,35.3-74.5,69.2C95.8,177.7,92.7,179.7,89.3,179.7z" fill="#4f46e5"/>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Crear partículas para el fondo
        document.addEventListener('DOMContentLoaded', function() {
            const particlesContainer = document.getElementById('particles');
            const numberOfParticles = 30;
            
            for (let i = 0; i < numberOfParticles; i++) {
                const particle = document.createElement('div');
                particle.classList.add('particle');
                
                // Tamaño aleatorio
                const size = Math.random() * 20 + 5;
                particle.style.width = `${size}px`;
                particle.style.height = `${size}px`;
                
                // Posición aleatoria
                const posX = Math.random() * 100;
                const posY = Math.random() * 100;
                particle.style.left = `${posX}%`;
                particle.style.top = `${posY}%`;
                
                // Animación aleatoria
                const duration = Math.random() * 20 + 10;
                const delay = Math.random() * 5;
                particle.style.animation = `floating ${duration}s ease-in-out ${delay}s infinite`;
                
                particlesContainer.appendChild(particle);
            }
        });
    </script>
</body>

</html>