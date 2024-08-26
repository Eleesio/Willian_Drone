<?php
session_start();

// Simulação de login (essa parte normalmente vem de um sistema de autenticação)
$cliente_id = 4; // Isso deve vir do banco de dados após a autenticação

if ($cliente_id) {
    $_SESSION['cliente_id'] = $cliente_id;
} else {
    echo "Falha no login";
    exit;
}

require_once('configuration.php');

$success_message = "";
$qr_code = "";
$expiration_time = "";
$cliente_id = isset($_SESSION['cliente_id']) ? $_SESSION['cliente_id'] : null;

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Buscar categorias
    $stmt = $pdo->prepare("SELECT * FROM categorias");
    $stmt->execute();
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Processar o formulário de compra
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['foto_id']) && isset($_POST['quantidade'])) {
            $foto_id = $_POST['foto_id'];
            $quantidade = $_POST['quantidade'];

            if ($cliente_id !== null && $foto_id && $quantidade > 0) {
                // Inserir na tabela compras
                $stmt = $pdo->prepare("INSERT INTO compras (cliente_id, foto_id, quantidade) VALUES (:cliente_id, :foto_id, :quantidade)");
                $stmt->execute([
                    ':cliente_id' => $cliente_id,
                    ':foto_id' => $foto_id,
                    ':quantidade' => $quantidade
                ]);

                // Gerar o QR Code (exemplo, substitua com sua lógica real)
                $valor = 10.00 * $quantidade; // Suponha que o preço é R$ 10,00 por foto
                $qr_code = "https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=PIX_CODE_$valor";

                // Calcular tempo de expiração
                $expiration_time = date('Y-m-d H:i:s', strtotime('+24 hours'));

                $success_message = "Compra realizada com sucesso! Confira o QR Code para pagamento.";
            } else {
                $success_message = "Dados da compra não foram enviados corretamente.";
            }
        } else {
            $success_message = "Dados da compra não foram enviados corretamente.";
        }
    }
} catch (PDOException $e) {
    $success_message = 'Erro ao conectar ao banco de dados: ' . $e->getMessage();
}
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
    <style>
        html, body {
            height: 100%;
            margin: 0;
            display: flex;
            flex-direction: column;
        }
        .content {
            flex: 1;
        }
        .list-group-item {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 10px;
            background-color: #f8f9fa;
            transition: background-color 0.3s, box-shadow 0.3s;
        }
        .list-group-item:hover {
            background-color: #e9ecef;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .card {
            border: none;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            transition: transform 0.3s;
        }
        .card:hover {
            transform: scale(1.02);
        }
        .card-img-top {
            border-bottom: 1px solid #ddd;
            object-fit: cover;
            height: 200px;
            width: 100%;
        }
        .card-body {
            padding: 15px;
        }
        .card-title {
            font-size: 1.25rem;
            font-weight: bold;
        }
        .card-text {
            color: #6c757d;
        }
        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
        }
        .btn-primary:hover {
            background-color: #0056b3;
            border-color: #004085;
        }
        .mt-custom-eventos {
            margin-top: 15vh;
        }
        a {
            text-decoration: none;
            color: #343a40;
        }
        #addToCartBtn {
            display: none;
            background-color: #ffc107;
            color: #343a40;
            text-align: center;
            padding: 10px 20px;
            font-size: 16px;
            cursor: pointer;
            border: none;
            border-radius: 5px;
            width: auto;
            float: right;
        }
        .title-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .modal-content img {
            max-width: 100%;
            height: auto;
        }
    </style>
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
                        <?php if ($cliente_id): ?>
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
    
    <div class="content">
        <div class="container mt-custom-eventos">
            <div class="title-container">
                <h1 class="mb-4">Últimos Eventos</h1>
                <button id="addToCartBtn" class="btn btn-warning">Adicionar ao Carrinho</button>
            </div>
            
            <!-- HTML para o sucesso da compra -->
            <?php if ($success_message): ?>
                <div class="alert alert-info" role="alert">
                    <?php echo htmlspecialchars($success_message); ?>
                    <?php if ($qr_code): ?>
                        <button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#qrCodeModal">Ver QR Code</button>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            
            <div class="list-group">
                <?php foreach ($categories as $category): ?>
                    <?php
                    $stmt = $pdo->prepare("SELECT * FROM fotos WHERE categoria_id = :categoria_id");
                    $stmt->execute(['categoria_id' => $category['id']]);
                    $fotos = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    ?>
                    <div class="list-group-item d-flex justify-content-between align-items-center">
                        <a href="#evento<?php echo $category['id']; ?>" data-bs-toggle="collapse" class="flex-grow-1"><?php echo htmlspecialchars($category['nome']); ?></a>
                        <div class="form-check">    
                            <input class="form-check-input" type="checkbox" id="evento<?php echo $category['id']; ?>Checkbox">
                        </div>
                    </div>
                    <div class="collapse" id="evento<?php echo $category['id']; ?>">
                        <div class="card card-body mt-2">
                            <div class="row">
                                <?php foreach ($fotos as $foto): ?>
                                    <div class="col-md-4 mb-3">
                                        <div class="card">
                                            <img src="img/<?php echo htmlspecialchars($foto['caminho']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($foto['nome']); ?>">
                                            <div class="card-body">
                                                <h5 class="card-title"><?php echo htmlspecialchars($foto['nome']); ?></h5>
                                                <p class="card-text">R$ <?php echo number_format($foto['preco'], 2, ',', '.'); ?></p>
                                                <form method="POST">
                                                    <input type="hidden" name="foto_id" value="<?php echo $foto['id']; ?>">
                                                    <div class="d-flex align-items-center">
                                                        <input type="number" name="quantidade" min="1" value="1" class="form-control me-2">
                                                        <button class="btn btn-primary" type="submit">Comprar</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    
    <!-- Modal QR Code -->
    <div class="modal fade" id="qrCodeModal" tabindex="-1" aria-labelledby="qrCodeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="qrCodeModalLabel">QR Code para Pagamento</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Você tem até 24 horas para realizar o pagamento.</p>
                    <img src="<?php echo htmlspecialchars($qr_code); ?>" alt="QR Code">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                </div>
            </div>
        </div>
    </div>
    
    <footer class="mt-auto bg-dark text-white text-center py-3">
        <div class="container">
            <p>&copy; <?php echo date("Y"); ?> Willian Drone. Todos os direitos reservados.</p>
        </div>
    </footer>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Se houver uma mensagem de sucesso, abra o modal
            <?php if ($success_message && $qr_code): ?>
                var qrCodeModal = new bootstrap.Modal(document.getElementById('qrCodeModal'));
                qrCodeModal.show();
            <?php endif; ?>
        });
    </script>
</body>
</html>
