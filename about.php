<?php
// Iniciar a sessão
session_start();

// Verificar se o usuário está logado
$user_logged_in = isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Willian Drone</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="icon" href="img/icon.ico">
    <link rel="stylesheet" href="css/estilo.css">
</head>

<body>

    <header>
        <nav class="navbar navbar-expand-lg navbar-light">
            <div class="container-fluid">
                <a class="navbar-brand" href="index.php">
                    <img src="img/logo-Branca.png" alt="Logo">
                </a>
                <button class="navbar-toggler bg-light" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon bg-light"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item">
                            <a class="nav-link" href="index.php">Página Inicial</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="contato.php">Contrate Nossos Serviços</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="about.php">Sobre Mim</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="evento.php">Eventos</a>
                        </li>
                        <?php if ($user_logged_in): ?>
                            <!-- Usuário está logado -->
                            <li class="nav-item">
                                <a class="nav-link" href="login/logout.php">Sair</a>
                            </li>
                        <?php else: ?>
                            <!-- Usuário não está logado -->
                            <li class="nav-item">
                                <a class="nav-link" href="login/login.html">Entrar</a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </nav>
    </header>

    <section class="hero-section">
        <div class="container">
            <h1>Bem-vindo a Willian Drone</h1>
            <p>Capturando imagens aéreas incríveis com drones para todos os tipos de necessidades.</p>
        </div>
    </section>

    <section class="section-padding">
        <div class="container">
            <div class="row">
                <div class="col-md-3">
                    <img src="img/sobre_mim.png" class="img-thumbnail">
                </div>
                <div class="col-md-8 quem_somos">
                    <div class="mb-4">
                        <h2>Quem Somos</h2>
                        <p>Na Willian Drone, somos apaixonados por capturar momentos únicos e vistas deslumbrantes com nossas imagens aéreas. Com a experiência e tecnologia de ponta, oferecemos fotos de alta qualidade para eventos, imóveis, e muito mais.</p>
                    </div>
                    <div>
                        <h2>O que Fazemos</h2>
                        <p>Nosso serviço inclui a captura de imagens aéreas para uma variedade de propósitos. Seja para fotografia imobiliária, cobertura de eventos, ou projetos publicitários, nossos drones oferecem imagens de alta qualidade que atendem às suas necessidades.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="section-padding">
        <div class="container">
            <h2 class="text-center mb-4">Depoimentos</h2>
            <div class="row">
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body">
                            <blockquote class="blockquote">
                                <p class="mb-0">"A experiência com a Willian Drone foi incrível. As fotos aéreas que recebemos foram de altíssima qualidade e capturaram nosso evento perfeitamente."</p>
                                <p>-Leo Soft</p>
                            </blockquote>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body">
                            <blockquote class="blockquote">
                                <p class="mb-0">"Excelente serviço e imagens deslumbrantes. A Willian Drone superou todas as nossas expectativas."</p>
                                <p>-Ponto Certo</p>
                            </blockquote>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body">
                            <blockquote class="blockquote">
                                <p class="mb-0">"Trabalhar com a Willian Drone foi uma ótima experiência. Recomendo para qualquer necessidade de fotografia aérea."</p>
                                <p>-JR Injetdiesel</p>
                            </blockquote>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="footer bg-dark text-white">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <p>&copy; 2024 Willian Drone. Todos os direitos reservados.</p>
                    <div class="social-icons">
                        <a href="https://www.youtube.com/@WillianDrone"><img class="icons" src="img/youtube.png" alt="YouTube"></a>
                        <a href="https://www.instagram.com/williandrone?utm_source=ig_web_button_share_sheet&igsh=ZDNlZDc0MzIxNw=="><img class="icons" src="img/insta.png" alt="Instagram"></a>
                        <a href="https://wa.link/gkxlw7"><img class="icons" src="img/whats.png" alt="WhatsApp"></a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
