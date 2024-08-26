<?php
require_once('security/security.php');

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$user_logged_in = isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
$user_id = $user_logged_in ? $_SESSION['user_id'] : null;

// Exemplo de uso da ID do usuário
if ($user_logged_in) {
    echo "ID do Usuário: " . htmlspecialchars($user_id);
}

require_once('configuration.php');

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo 'Erro na conexão: ' . $e->getMessage();
    exit();
}

// Verificar se o formulário foi enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $mensagem = $_POST['message'];
    
    // Preparar e executar a consulta SQL
    $stmt = $pdo->prepare("INSERT INTO mensagem (mensagem, user_id) VALUES (:mensagem, :user_id)");
    $stmt->bindParam(':mensagem', $mensagem);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    
    if ($stmt->execute()) {
        echo "Mensagem enviada com sucesso!";
    } else {
        echo "Erro: " . $stmt->errorInfo()[2];
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Contrate Nossos Serviços - Willian Drone</title>
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
                            <a class="nav-link active" href="contato.php">Contrate Nossos Serviços</a>
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
    <div class="container mt-custom">
        <article class="bg-light p-4 rounded shadow-sm">
            <h2 class="mb-4">Entre em Contato Conosco</h2>
            <p class="lead">Tem alguma dúvida ou precisa de ajuda? Estamos aqui para você!</p>

            <div class="mb-4">
                <h4>Informações de Contato</h4>
                <p><strong>Telefone:</strong> (34) 99267-8282</p>
                <p><strong>Email:</strong> <a href="mailto:willianmenezesdrone@gmail.com">willianmenezesdrone@gmail.com</a></p>
            </div>

            <div>
                <h4>Preencha o formulário abaixo e entraremos em contato com você o mais breve possível.</h4>
            </div>
        </article>
    </div>

    <!-- Seção de Contato -->
    <section class="mt-4">
        <div class="formulario">
            <div class="col-md-8">
                <div class="card shadow-lg border-light rounded-3">
                    <div class="card-body">
                        <h2 class="card-title mb-4">Entre em Contato</h2>
                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="message" class="form-label">Mensagem</label>
                                <textarea class="form-control border-success" id="message" name="message" rows="4" placeholder="Sua mensagem" required></textarea>
                            </div>
                            <button type="submit" class="btn btn-success btn-lg">Enviar</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <footer class="bg-dark text-white text-center py-3">
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
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
