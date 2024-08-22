<?php
// Conexão com o banco de dados
require_once('../configuration.php');

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo 'Erro na conexão: ' . $e->getMessage();
    exit();
}

// Função para obter categorias
function getCategories($pdo) {
    $stmt = $pdo->query('SELECT * FROM categorias');
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Função para obter fotos
function getPhotos($pdo) {
    $stmt = $pdo->query('SELECT * FROM fotos');
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Função para obter clientes
function getClients($pdo) {
    $stmt = $pdo->query('SELECT * FROM cliente');
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Verificar se o formulário de upload de foto foi enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['fileUpload']) && isset($_POST['photoName']) && isset($_POST['selectCategory']) && isset($_POST['photoValue'])) {
    $photoName = trim($_POST['photoName']);
    $categoryId = intval($_POST['selectCategory']);
    $photoValue = floatval($_POST['photoValue']);

    // Verificar se o arquivo foi enviado
    if ($_FILES['fileUpload']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../uploads/';
        $uploadFile = $uploadDir . basename($_FILES['fileUpload']['name']);

        // Mover o arquivo para o diretório de uploads
        if (move_uploaded_file($_FILES['fileUpload']['tmp_name'], $uploadFile)) {
            // Preparar a consulta SQL para inserir a foto
            $stmt = $pdo->prepare('INSERT INTO fotos (nome, caminho, categoria_id, valor) VALUES (:nome, :caminho, :categoria_id, :valor)');
            $stmt->bindParam(':nome', $photoName);
            $stmt->bindParam(':caminho', $uploadFile);
            $stmt->bindParam(':categoria_id', $categoryId);
            $stmt->bindParam(':valor', $photoValue);

            try {
                $stmt->execute();
                // Redirecionar para a mesma página para evitar reenvio de formulário
                header('Location: index.php');
                exit();
            } catch (PDOException $e) {
                $error = 'Erro ao salvar a foto: ' . $e->getMessage();
            }
        } else {
            $error = 'Erro ao mover o arquivo para o diretório de uploads.';
        }
    } else {
        $error = 'Erro no upload do arquivo.';
    }
}

// Verificar se o formulário de criação de categoria foi enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['newCategoryName']) && !empty($_POST['newCategoryName'])) {
    $categoryName = trim($_POST['newCategoryName']);
    
    // Preparar a consulta SQL para inserir a nova categoria
    $stmt = $pdo->prepare('INSERT INTO categorias (nome) VALUES (:nome)');
    $stmt->bindParam(':nome', $categoryName);
    
    try {
        $stmt->execute();
        // Redirecionar para a mesma página para evitar reenvio de formulário
        header('Location: index.php');
        exit();
    } catch (PDOException $e) {
        $error = 'Erro ao criar a categoria: ' . $e->getMessage();
    }
}

// Obter os dados necessários para exibir
$categories = getCategories($pdo);
$photos = getPhotos($pdo);
$clients = getClients($pdo);
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Willian Drone - Administrador</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="icon" href="img/icon.ico">
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <div class="sidebar">
        <div class="text-center my-4">
            <img src="../img/logo.jpg" alt="Logo" class="img-fluid rounded-circle" width="100">
        </div>
        <div class="menu-items">
            <a class="active" href="index.php">Home</a>
            <a href="login/login.php">Sair</a>
        </div>
    </div>
    <div class="content">
        <header class="mb-4">
            <h1>Painel de Administração</h1>
        </header>
        <section>
            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">Subir Fotos</div>
                        <div class="card-body">
                            <form id="uploadPhotoForm" method="post" enctype="multipart/form-data">
                                <div class="mb-3">
                                    <label for="photoName" class="form-label">Nome da Foto</label>
                                    <input type="text" class="form-control" id="photoName" name="photoName" placeholder="Insira o nome da foto" required>
                                </div>
                                <div class="mb-3">
                                    <label for="fileUpload" class="form-label">Selecionar arquivo</label>
                                    <input type="file" class="form-control" id="fileUpload" name="fileUpload" required>
                                </div>
                                <div class="mb-3">
                                    <label for="selectCategory" class="form-label">Selecionar Categoria</label>
                                    <select class="form-select" id="selectCategory" name="selectCategory">
                                        <?php foreach ($categories as $category): ?>
                                            <option value="<?= $category['id']; ?>"><?= htmlspecialchars($category['nome']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="photoValue" class="form-label">Valor da Foto</label>
                                    <input type="number" class="form-control" id="photoValue" name="photoValue" placeholder="Insira o valor da foto" required>
                                </div>
                                <button type="submit" class="btn btn-primary">Subir Foto</button>
                            </form>
                            <?php if (isset($error)): ?>
                                <div class="alert alert-danger mt-3"><?= htmlspecialchars($error); ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">Criar Nova Categoria</div>
                        <div class="card-body">
                            <?php if (isset($error)): ?>
                                <div class="alert alert-danger"><?= htmlspecialchars($error); ?></div>
                            <?php endif; ?>
                            <form id="createCategoryForm" method="post">
                                <div class="mb-3">
                                    <label for="newCategoryName" class="form-label">Nome da Categoria</label>
                                    <input type="text" class="form-control" id="newCategoryName" name="newCategoryName" placeholder="Insira o nome da categoria" required>
                                </div>
                                <button type="submit" class="btn btn-primary">Criar Categoria</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <div class="table-responsive mt-4">
                <h2>Gerenciar Fotos</h2>
                <div class="row mb-3">
                    <div class="col-md-4">
                        <input type="text" class="form-control" id="filterPhotoName" placeholder="Filtrar por nome da foto">
                    </div>
                    <div class="col-md-4">
                        <select class="form-select" id="filterCategory">
                            <option value="">Todas as Categorias</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?= $category['id']; ?>"><?= htmlspecialchars($category['nome']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <table class="table table-bordered" id="photosTable">
                    <thead>
                        <tr>
                            <th><input type="checkbox" id="selectAll"></th>
                            <th>ID</th>
                            <th>Nome da Foto</th>
                            <th>Categoria</th>
                            <th>Valor</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($photos as $photo): ?>
                            <tr>
                                <td><input type="checkbox" class="photoCheckbox"></td>
                                <td><?= htmlspecialchars($photo['id']); ?></td>
                                <td><?= htmlspecialchars($photo['nome']); ?></td>
                                <td><?= htmlspecialchars($photo['categoria']); ?></td>
                                <td contenteditable="true" class="editable"><?= htmlspecialchars($photo['valor']); ?></td>
                                <td><button class="btn btn-success save-btn">Salvar</button></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <button id="sendSelectedPhotos" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#sendPhotoModal">Enviar Selecionadas</button>
                <button id="deleteSelectedPhotos" class="btn btn-danger">Excluir Selecionadas</button>
            </div>
            <div class="table-responsive mt-4">
                <h2>Gerenciar Clientes</h2>
                <table class="table table-bordered" id="clientsTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nome</th>
                            <th>Email</th>
                            <th>Telefone</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($clients as $client): ?>
                            <tr>
                                <td><?= htmlspecialchars($client['id']); ?></td>
                                <td><?= htmlspecialchars($client['nome']); ?></td>
                                <td><?= htmlspecialchars($client['email']); ?></td>
                                <td><?= htmlspecialchars($client['telefone']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    <!-- Modal para enviar fotos -->
    <div class="modal fade" id="sendPhotoModal" tabindex="-1" aria-labelledby="sendPhotoModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="sendPhotoModalLabel">Enviar Fotos</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="sendPhotosForm">
                        <div class="mb-3">
                            <label for="clientSelect" class="form-label">Selecionar Cliente</label>
                            <select class="form-select" id="clientSelect">
                                <?php foreach ($clients as $client): ?>
                                    <option value="<?= $client['id']; ?>"><?= htmlspecialchars($client['nome']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="sendMessage" class="form-label">Mensagem</label>
                            <textarea class="form-control" id="sendMessage" rows="3" placeholder="Digite uma mensagem opcional"></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Enviar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // JavaScript para manipulação do formulário de envio e outros scripts
        document.getElementById('selectAll').addEventListener('change', function() {
            const checked = this.checked;
            document.querySelectorAll('.photoCheckbox').forEach(cb => cb.checked = checked);
        });

        document.getElementById('deleteSelectedPhotos').addEventListener('click', function() {
            const selected = Array.from(document.querySelectorAll('.photoCheckbox'))
                .filter(cb => cb.checked)
                .map(cb => cb.closest('tr').cells[1].textContent); // Supondo que a coluna ID seja a segunda
            // Lógica para excluir fotos selecionadas
            if (selected.length > 0) {
                // Enviar IDs para o servidor para exclusão
                console.log('Excluir fotos com IDs:', selected);
            }
        });

        document.getElementById('sendPhotosForm').addEventListener('submit', function(event) {
            event.preventDefault();
            // Lógica para enviar fotos
            const clientId = document.getElementById('clientSelect').value;
            const message = document.getElementById('sendMessage').value;
            console.log('Enviar fotos para o cliente:', clientId, 'Mensagem:', message);
            // Enviar dados para o servidor
        });
    </script>
</body>

</html>
