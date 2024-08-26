<?php
require_once('../configuration.php');

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo 'Erro na conexão: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES);
    exit();
}

$error = null;

// Função para lidar com o upload de arquivos
function uploadFile($fileInputName) {
    if (isset($_FILES[$fileInputName]) && $_FILES[$fileInputName]['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES[$fileInputName]['tmp_name'];
        $fileName = $_FILES[$fileInputName]['name'];
        $fileNameCmps = explode(".", $fileName);
        $fileExtension = strtolower(end($fileNameCmps));
        $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
        $uploadFileDir = '../uploads/';
        $dest_path = $uploadFileDir . $newFileName;

        if (move_uploaded_file($fileTmpPath, $dest_path)) {
            return $dest_path;
        } else {
            return false;
        }
    }
    return false;
}

// Adicionar novo card
if (isset($_POST['add_card'])) {
    $titulo = filter_input(INPUT_POST, 'titulo', FILTER_SANITIZE_STRING);
    $texto = filter_input(INPUT_POST, 'texto', FILTER_SANITIZE_STRING);

    $imagemPath = uploadFile('imagem');

    if ($imagemPath) {
        $sql = "INSERT INTO cards (titulo, imagem, texto) VALUES (:titulo, :imagem, :texto)";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':titulo', $titulo);
        $stmt->bindParam(':imagem', $imagemPath);
        $stmt->bindParam(':texto', $texto);

        try {
            $stmt->execute();
            header('Location: artigos.php');
            exit();
        } catch (PDOException $e) {
            $error = 'Erro ao adicionar o card: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES);
        }
    } else {
        $error = 'Erro ao fazer upload da imagem.';
    }
}

// Editar card
if (isset($_POST['edit_card_id'])) {
    $id = filter_input(INPUT_POST, 'edit_card_id', FILTER_VALIDATE_INT);
    $titulo = filter_input(INPUT_POST, 'edit_titulo', FILTER_SANITIZE_STRING);
    $texto = filter_input(INPUT_POST, 'edit_texto', FILTER_SANITIZE_STRING);

    $imagemPath = null;
    if ($_FILES['edit_imagem']['error'] === UPLOAD_ERR_OK) {
        $imagemPath = uploadFile('edit_imagem');
    } else {
        $imagemPath = filter_input(INPUT_POST, 'edit_imagem_old', FILTER_SANITIZE_URL);
    }

    $sql = "UPDATE cards SET titulo = :titulo, imagem = :imagem, texto = :texto WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':titulo', $titulo);
    $stmt->bindParam(':imagem', $imagemPath);
    $stmt->bindParam(':texto', $texto);
    $stmt->bindParam(':id', $id);

    try {
        $stmt->execute();
        header('Location: artigos.php');
        exit();
    } catch (PDOException $e) {
        $error = 'Erro ao atualizar o card: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES);
    }
}

// Excluir card
if (isset($_GET['delete_id'])) {
    $id = filter_input(INPUT_GET, 'delete_id', FILTER_VALIDATE_INT);

    // Buscar o caminho da imagem para exclusão
    $sql = "SELECT imagem FROM cards WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    $card = $stmt->fetch(PDO::FETCH_ASSOC);

    $imagemPath = $card['imagem'];

    // Excluir o card do banco de dados
    $sql = "DELETE FROM cards WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id', $id);

    try {
        $stmt->execute();
        // Remover o arquivo de imagem do servidor
        if ($imagemPath && file_exists($imagemPath)) {
            unlink($imagemPath);
        }
        header('Location: artigos.php');
        exit();
    } catch (PDOException $e) {
        $error = 'Erro ao excluir o card: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES);
    }
}

// Buscar cards
$sql = "SELECT * FROM cards";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$cards = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Processar a exibição do formulário de edição e adição
$cardParaEditar = null;
if (isset($_GET['edit_id'])) {
    $editId = filter_input(INPUT_GET, 'edit_id', FILTER_VALIDATE_INT);
    $sql = "SELECT * FROM cards WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id', $editId);
    $stmt->execute();
    $cardParaEditar = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Cards</title>
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
            <a href="index.php">Home</a>
            <a href="gerenciaclientes.php">Gerenciar Clientes</a>
            <a class="active" href="artigos.php">Gerenciar Cards</a>
            <a href="artigos.php">Artigos</a>
            <a href="login/login.php">Sair</a>
        </div>
    </div>
    <div class="content">
        <header class="mb-4">
            <h1>Gerenciar Cards da Página Inicial</h1>
        </header>

        <!-- Mensagem de Erro -->
        <?php if ($error): ?>
        <div class="alert alert-danger" role="alert">
            <?php echo htmlspecialchars($error, ENT_QUOTES); ?>
        </div>
        <?php endif; ?>

        <!-- Botão para Adicionar Novo Card -->
        <button type="button" class="btn btn-primary mb-4" data-bs-toggle="modal" data-bs-target="#addCardModal">
            Adicionar Novo Card
        </button>

        <!-- Tabela de Cards -->
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Título</th>
                    <th>Imagem</th>
                    <th>Texto</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($cards as $card): ?>
                <tr>
                    <td><?php echo htmlspecialchars($card['id'], ENT_QUOTES); ?></td>
                    <td><?php echo htmlspecialchars($card['titulo'], ENT_QUOTES); ?></td>
                    <td><img src="<?php echo htmlspecialchars($card['imagem'], ENT_QUOTES); ?>" alt="<?php echo htmlspecialchars($card['titulo'], ENT_QUOTES); ?>" class="img-thumbnail" width="100"></td>
                    <td><?php echo htmlspecialchars($card['texto'], ENT_QUOTES); ?></td>
                    <td>
                        <a href="?edit_id=<?php echo htmlspecialchars($card['id'], ENT_QUOTES); ?>" class="btn btn-warning btn-sm">Editar</a>
                        <a href="?delete_id=<?php echo htmlspecialchars($card['id'], ENT_QUOTES); ?>" class="btn btn-danger btn-sm" onclick="return confirm('Tem certeza que deseja excluir este card?')">Excluir</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Modal de Edição -->
        <div class="modal fade" id="editCardModal" tabindex="-1" aria-labelledby="editCardModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editCardModalLabel">Editar Card</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form action="artigos.php" method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="edit_card_id" value="<?php echo htmlspecialchars($cardParaEditar['id'], ENT_QUOTES); ?>">
                            <div class="mb-3">
                                <label for="edit_titulo" class="form-label">Título</label>
                                <input type="text" class="form-control" id="edit_titulo" name="edit_titulo" value="<?php echo htmlspecialchars($cardParaEditar['titulo'], ENT_QUOTES); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="edit_imagem" class="form-label">Imagem</label>
                                <input type="file" class="form-control" id="edit_imagem" name="edit_imagem" accept="image/*">
                                <input type="hidden" name="edit_imagem_old" value="<?php echo htmlspecialchars($cardParaEditar['imagem'], ENT_QUOTES); ?>">
                                <img src="<?php echo htmlspecialchars($cardParaEditar['imagem'], ENT_QUOTES); ?>" alt="Imagem atual" class="img-thumbnail mt-2" width="100">
                            </div>
                            <div class="mb-3">
                                <label for="edit_texto" class="form-label">Texto</label>
                                <textarea class="form-control" id="edit_texto" name="edit_texto" rows="3" required><?php echo htmlspecialchars($cardParaEditar['texto'], ENT_QUOTES); ?></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">Salvar Alterações</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal de Adição -->
        <div class="modal fade" id="addCardModal" tabindex="-1" aria-labelledby="addCardModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addCardModalLabel">Adicionar Novo Card</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form action="artigos.php" method="POST" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label for="titulo" class="form-label">Título</label>
                                <input type="text" class="form-control" id="titulo" name="titulo" required>
                            </div>
                            <div class="mb-3">
                                <label for="imagem" class="form-label">Imagem</label>
                                <input type="file" class="form-control" id="imagem" name="imagem" accept="image/*" required>
                            </div>
                            <div class="mb-3">
                                <label for="texto" class="form-label">Texto</label>
                                <textarea class="form-control" id="texto" name="texto" rows="3" required></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary" name="add_card">Adicionar Card</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript do Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Script para abrir o modal de edição caso necessário -->
    <?php if ($cardParaEditar): ?>
    <script>
        var editCardModal = new bootstrap.Modal(document.getElementById('editCardModal'), {});
        editCardModal.show();
    </script>
    <?php endif; ?>
</body>
</html>
