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

// Função para obter fotos com categorias
function getPhotos($pdo) {
    $stmt = $pdo->query('
        SELECT fotos.*, categorias.nome AS categoria_nome
        FROM fotos
        LEFT JOIN categorias ON fotos.categoria_id = categorias.id
    ');
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Função para obter clientes
function getClients($pdo) {
    $stmt = $pdo->query('SELECT * FROM clientes');
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Função para obter uma foto por ID
function getPhotoById($pdo, $id) {
    $stmt = $pdo->prepare('
        SELECT fotos.*, categorias.nome AS categoria_nome
        FROM fotos
        LEFT JOIN categorias ON fotos.categoria_id = categorias.id
        WHERE fotos.id = :id
    ');
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Função para excluir uma foto por ID
function deletePhoto($pdo, $id) {
    $stmt = $pdo->prepare('DELETE FROM fotos WHERE id = :id');
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    return $stmt->execute();
}

// Função para atualizar uma foto
function updatePhoto($pdo, $id, $name, $categoryId, $value, $filePath = null) {
    if ($filePath) {
        // Atualiza com o novo caminho do arquivo
        $stmt = $pdo->prepare('UPDATE fotos SET nome = :nome, categoria_id = :categoria_id, valor = :valor, caminho = :caminho WHERE id = :id');
        $stmt->bindParam(':caminho', $filePath);
    } else {
        // Atualiza sem mudar o arquivo
        $stmt = $pdo->prepare('UPDATE fotos SET nome = :nome, categoria_id = :categoria_id, valor = :valor WHERE id = :id');
    }
    $stmt->bindParam(':nome', $name);
    $stmt->bindParam(':categoria_id', $categoryId);
    $stmt->bindParam(':valor', $value);
    $stmt->bindParam(':id', $id);
    return $stmt->execute();
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

// Verificar se o ID da foto para exclusão foi fornecido
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $photoId = intval($_GET['delete']);
    if (deletePhoto($pdo, $photoId)) {
        // Redirecionar para a mesma página após exclusão
        header('Location: index.php');
        exit();
    } else {
        $error = 'Erro ao excluir a foto.';
    }
}

// Verificar se o ID da foto para edição foi fornecido
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $photoId = intval($_GET['edit']);
    $photo = getPhotoById($pdo, $photoId);
}

// Verificar se o formulário de edição de foto foi enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['editPhotoId']) && isset($_POST['editPhotoName']) && isset($_POST['editSelectCategory']) && isset($_POST['editPhotoValue'])) {
    $photoId = intval($_POST['editPhotoId']);
    $photoName = trim($_POST['editPhotoName']);
    $categoryId = intval($_POST['editSelectCategory']);
    $photoValue = floatval($_POST['editPhotoValue']);

    $filePath = null;
    if (isset($_FILES['editFileUpload']) && $_FILES['editFileUpload']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../uploads/';
        $filePath = $uploadDir . basename($_FILES['editFileUpload']['name']);

        // Mover o arquivo para o diretório de uploads
        if (move_uploaded_file($_FILES['editFileUpload']['tmp_name'], $filePath)) {
            // Atualizar a foto com o novo arquivo
            updatePhoto($pdo, $photoId, $photoName, $categoryId, $photoValue, $filePath);
        } else {
            $error = 'Erro ao mover o arquivo para o diretório de uploads.';
        }
    } else {
        // Atualizar a foto sem mudar o arquivo
        updatePhoto($pdo, $photoId, $photoName, $categoryId, $photoValue);
    }

    try {
        // Redirecionar para a mesma página após edição
        header('Location: index.php');
        exit();
    } catch (PDOException $e) {
        $error = 'Erro ao atualizar a foto: ' . $e->getMessage();
    }
}

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
            <a href="gerenciaclientes.php">Gerenciar Clientes</a>
            <a href="gerenciacompras.php">Gerenciar Compras</a>
            <a href="artigos.php">Artigos</a>
            <a href="login/login.php">Sair</a>
        </div>
    </div>
    <div class="content">
        <header class="mb-4">
            <h1>Área do Administrador</h1>
        </header>
        <section>
            <h2>Adicionar Foto</h2>
            <div class="row">
                <div class="col-md-6">
                    <div class="card mb-4">
                        <div class="card-header">Adicionar Nova Foto</div>
                        <div class="card-body">
                            <form method="post" enctype="multipart/form-data">
                                <div class="mb-3">
                                    <label for="photoName" class="form-label">Nome da Foto</label>
                                    <input type="text" class="form-control" id="photoName" name="photoName" placeholder="Insira o nome da foto" required>
                                </div>
                                <div class="mb-3">
                                    <label for="selectCategory" class="form-label">Selecionar Categoria</label>
                                    <select class="form-select" id="selectCategory" name="selectCategory">
                                        <option value="">Selecione uma Categoria</option>
                                        <?php foreach ($categories as $category): ?>
                                            <option value="<?= htmlspecialchars($category['id']); ?>"><?= htmlspecialchars($category['nome']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="fileUpload" class="form-label">Imagem da Foto</label>
                                    <input type="file" class="form-control" id="fileUpload" name="fileUpload" required>
                                </div>
                                <div class="mb-3">
                                    <label for="photoValue" class="form-label">Valor da Foto</label>
                                    <input type="number" class="form-control" id="photoValue" name="photoValue" step="0.01" placeholder="Insira o valor da foto" required>
                                </div>
                                <button type="submit" class="btn btn-primary">Adicionar Foto</button>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">Criar Categoria</div>
                        <div class="card-body">
                            <form method="post">
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
        </section>
        <section>
            <h2>Gerenciar Fotos</h2>
            <div class="row mb-3">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="filterPhotoName" class="form-label">Filtrar por Nome</label>
                        <input type="text" class="form-control" id="filterPhotoName" placeholder="Digite o nome da foto">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="filterCategory" class="form-label">Filtrar por Categoria</label>
                        <select class="form-select" id="filterCategory">
                            <option value="">Todas as Categorias</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?= htmlspecialchars($category['nome']); ?>"><?= htmlspecialchars($category['nome']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
            <table class="table table-striped" id="photosTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nome</th>
                        <th>Categoria</th>
                        <th>Valor</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($photos as $photo): ?>
                        <tr>
                            <td><?= htmlspecialchars($photo['id']); ?></td>
                            <td><?= htmlspecialchars($photo['nome']); ?></td>
                            <td><?= htmlspecialchars($photo['categoria_nome'] ?? 'Sem Categoria'); ?></td>
                            <td><?= htmlspecialchars($photo['valor']); ?></td>
                            <td>
                                <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editPhotoModal" data-id="<?= htmlspecialchars($photo['id']); ?>" data-name="<?= htmlspecialchars($photo['nome']); ?>" data-category="<?= htmlspecialchars($photo['categoria_id']); ?>" data-value="<?= htmlspecialchars($photo['valor']); ?>">Editar</button>
                                <a href="index.php?delete=<?= htmlspecialchars($photo['id']); ?>" class="btn btn-danger btn-sm" onclick="return confirm('Tem certeza que deseja excluir esta foto?');">Excluir</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>
        <section>
            <h2>Gerenciar Clientes</h2>
            <table class="table table-striped">
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
        </section>

        <!-- Modal de Edição de Foto -->
        <div class="modal fade" id="editPhotoModal" tabindex="-1" aria-labelledby="editPhotoModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editPhotoModalLabel">Editar Foto</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="editPhotoForm" method="post" enctype="multipart/form-data">
                            <input type="hidden" id="editPhotoId" name="editPhotoId">
                            <div class="mb-3">
                                <label for="editPhotoName" class="form-label">Nome da Foto</label>
                                <input type="text" class="form-control" id="editPhotoName" name="editPhotoName" required>
                            </div>
                            <div class="mb-3">
                                <label for="editSelectCategory" class="form-label">Selecionar Categoria</label>
                                <select class="form-select" id="editSelectCategory" name="editSelectCategory">
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?= $category['id']; ?>"><?= htmlspecialchars($category['nome']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="editFileUpload" class="form-label">Alterar Imagem da Foto</label>
                                <input type="file" class="form-control" id="editFileUpload" name="editFileUpload">
                            </div>
                            <div class="mb-3">
                                <label for="editPhotoValue" class="form-label">Valor da Foto</label>
                                <input type="number" class="form-control" id="editPhotoValue" name="editPhotoValue" step="0.01" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Atualizar Foto</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('filterPhotoName').addEventListener('input', function () {
            filterPhotos();
        });
        document.getElementById('filterCategory').addEventListener('change', function () {
            filterPhotos();
        });

        function filterPhotos() {
            const photoName = document.getElementById('filterPhotoName').value.toLowerCase();
            const category = document.getElementById('filterCategory').value;
            const rows = document.querySelectorAll('#photosTable tbody tr');

            rows.forEach(row => {
                const nameCell = row.cells[1].textContent.toLowerCase();
                const categoryCell = row.cells[2].textContent;

                const matchesName = nameCell.includes(photoName);
                const matchesCategory = category === '' || categoryCell === category;

                row.style.display = matchesName && matchesCategory ? '' : 'none';
            });
        }

        // Script para preencher o modal de edição
        document.getElementById('editPhotoModal').addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget; // Botão que abriu o modal
            var photoId = button.getAttribute('data-id');
            var photoName = button.getAttribute('data-name');
            var categoryId = button.getAttribute('data-category');
            var photoValue = button.getAttribute('data-value');

            var modal = this;
            modal.querySelector('#editPhotoId').value = photoId;
            modal.querySelector('#editPhotoName').value = photoName;
            modal.querySelector('#editSelectCategory').value = categoryId;
            modal.querySelector('#editPhotoValue').value = photoValue;
        });
    </script>
</body>
</html>
