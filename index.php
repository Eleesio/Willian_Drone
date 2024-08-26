<?php
session_start(); // Inicia a sessão para verificar o estado de login

// Conexão com o banco de dados
$servername = "localhost"; // Substitua pelo seu servidor
$username = "root"; // Substitua pelo seu usuário do banco de dados
$password = ""; // Substitua pela sua senha do banco de dados
$dbname = "willian_drone"; // Substitua pelo nome do seu banco de dados

$conn = new mysqli($servername, $username, $password, $dbname);

// Verifica a conexão
if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}

// Consulta SQL para recuperar os cards
$sql = "SELECT titulo, imagem, texto FROM cards";
$result = $conn->query($sql);
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
                <button class="navbar-toggler navbar-light bg-light" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
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
                        <?php if (isset($_SESSION['logged_in']) && $_SESSION['logged_in']): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="login/login.php">Sair</a>
                            </li>
                        <?php else: ?>
                            <li class="nav-item">
                                <a class="nav-link" href="login/login.php">Entrar</a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </nav>
    </header>

    <div id="carouselExampleIndicators" class="carousel slide" data-bs-ride="carousel" data-bs-interval="5000">
        <div class="carousel-indicators">
            <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
            <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="1" aria-label="Slide 2"></button>
            <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="2" aria-label="Slide 3"></button>
        </div>
        <div class="carousel-inner">
            <div class="carousel-item active">
                <img src="img/carrossel 1.jpg" class="d-block w-100" alt="Slide 1">
            </div>
            <div class="carousel-item">
                <img src="img/carrossel 2.jpg" class="d-block w-100" alt="Slide 2">
            </div>
            <div class="carousel-item">
                <img src="img/carrossel 3.jpg" class="d-block w-100" alt="Slide 3">
            </div>
        </div>
        <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Previous</span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Next</span>
        </button>
    </div>
    <div class="container mt-5">
        <article class="bg-white text-dark p-4 rounded shadow-sm">
            <h1 class="text-center mb-4">Bem-vindo ao universo visual de Willian Drone</h1>
            <p>Onde a fotografia aérea encontra um novo patamar. Especialista em capturar a essência do mundo a partir do céu, Willian combina expertise técnica e um olhar artístico para criar imagens aéreas que encantam e inspiram.</p>
        </article>
    </div>
    <div class="container my-5">
        <div class="row">
            <?php
            if ($result->num_rows > 0) {
                // Exibe os cards
                while($row = $result->fetch_assoc()) {
                    echo '<div class="col-md-4 mb-4">';
                    echo '<div class="card h-100">';
                    echo '<img src="' . $row["imagem"] . '" class="card-img-top" alt="' . $row["titulo"] . '">';
                    echo '<div class="card-body">';
                    echo '<h5 class="card-title">' . $row["titulo"] . '</h5>';
                    echo '<p class="card-text">' . $row["texto"] . '</p>';
                    echo '</div>';
                    echo '<div class="card-footer">';
                    echo '<a href="contato.php" class="btn btn-cor">Saiba Mais</a>';
                    echo '</div>';
                    echo '</div>';
                    echo '</div>';
                }
            } else {
                echo "<p>Nenhum card encontrado.</p>";
            }
            $conn->close();
            ?>
        </div>
    </div>

    <div class="container my-5">
        <h2 class="text-center">Confira os nossos vídeos</h2>
        <div class="row justify-content-center">
            <div class="col-md-9">
                <div class="ratio ratio-16x9">
                    <iframe src="https://www.youtube.com/embed/Uw7DvE1geuI" allowfullscreen></iframe>
                </div>
            </div>
        </div>
    </div>

    <div class="container my-5 text-center">
        <h3>Empresas que confiam em nosso trabalho</h3>
        <div class="row justify-content-center">
            <div class="col-md-2">
                <img src="img/empresa 1.jpg" class="img-fluid img-empresa" alt="Empresa 1">
            </div>
            <div class="col-md-2">
                <img src="img/empresa 2.jpg" class="img-fluid img-empresa" alt="Empresa 2">
            </div>
            <div class="col-md-2">
                <img src="img/empresa 3.jpg" class="img-fluid img-empresa" alt="Empresa 3">
            </div>
        </div>
    </div>

    <div class="footer bg-dark text-white">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <p>&copy; 2024 Willian Drone. Todos os direitos reservados.</p>
                    <div class="social-icons">
                        <a href="https://www.youtube.com/@WillianDrone"><img class="icons" src="img/youtube.png" alt="YouTube"></a>
                        <a href="https://www.linkedin.com/in/willian-drone/"><img class="icons" src="img/linkedin.png" alt="LinkedIn"></a>
                        <a href="https://www.facebook.com/WillianDrone"><img class="icons" src="img/facebook.png" alt="Facebook"></a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Compra -->
    <div class="modal fade" id="modalCompra" tabindex="-1" aria-labelledby="modalCompraLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalCompraLabel">Confirmar Compra</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Confirme sua compra:</p>
                    <p><strong>Item:</strong> <span id="modalItem"></span></p>
                    <p><strong>Preço:</strong> R$ <span id="modalPreco"></span></p>
                    <p><strong>Categoria:</strong> <span id="modalCategoria"></span></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="confirmarCompra">Confirmar</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/scripts.js"></script>
</body>

</html>
