<?php
require_once('../configuration.php');

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo 'Erro na conexão: ' . $e->getMessage();
    exit();
}

// Processar a edição do cliente
if (isset($_POST['edit_cliente_id'])) {
    $id = $_POST['edit_cliente_id'];
    $nome = $_POST['edit_nome'];
    $email = $_POST['edit_email'];
    $celular = $_POST['edit_celular'];
    $data_criacao = $_POST['edit_data_criacao'];
    $senha = $_POST['edit_senha']; // Senha opcional

    $sql = "UPDATE clientes SET nome = :nome, email = :email, celular = :celular, data_criacao = :data_criacao";
    
    // Adiciona a atualização da senha se fornecida
    if (!empty($senha)) {
        $sql .= ", senha = :senha";
    }
    
    $sql .= " WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':nome', $nome);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':celular', $celular);
    $stmt->bindParam(':data_criacao', $data_criacao);
    
    // Adiciona a senha se fornecida
    if (!empty($senha)) {
        $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
        $stmt->bindParam(':senha', $senhaHash);
    }

    $stmt->bindParam(':id', $id);

    try {
        $stmt->execute();
        header('Location: gerenciaclientes.php');
        exit();
    } catch (PDOException $e) {
        $error = 'Erro ao atualizar o cliente: ' . $e->getMessage();
    }
}

// Processar a exclusão do cliente
if (isset($_GET['delete_id'])) {
    $id = $_GET['delete_id'];

    $sql = "DELETE FROM clientes WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id', $id);

    try {
        $stmt->execute();
        header('Location: gerenciaclientes.php');
        exit();
    } catch (PDOException $e) {
        $error = 'Erro ao excluir o cliente: ' . $e->getMessage();
    }
}

// Buscar clientes com filtros
$sql = "SELECT * FROM clientes WHERE 1=1";
$params = [];

if (!empty($_GET['nome'])) {
    $sql .= " AND nome LIKE :nome";
    $params[':nome'] = '%' . $_GET['nome'] . '%';
}

if (!empty($_GET['email'])) {
    $sql .= " AND email LIKE :email";
    $params[':email'] = '%' . $_GET['email'] . '%';
}

if (!empty($_GET['celular'])) {
    $sql .= " AND celular LIKE :celular";
    $params[':celular'] = '%' . $_GET['celular'] . '%';
}

if (!empty($_GET['data_criacao'])) {
    $dataCriacao = $_GET['data_criacao'];
    $sql .= " AND DATE(data_criacao) = :data_criacao";
    $params[':data_criacao'] = $dataCriacao;
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Processar a exibição do formulário de edição
$clienteParaEditar = null;
if (isset($_GET['edit_id'])) {
    $editId = $_GET['edit_id'];
    $sql = "SELECT * FROM clientes WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id', $editId);
    $stmt->execute();
    $clienteParaEditar = $stmt->fetch(PDO::FETCH_ASSOC);
}
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
            <a href="index.php">Home</a>
            <a class="active" href="gerenciaclientes.php">Gerenciar Clientes</a>
            <a href="gerenciacompras.php">Gerenciar Compras</a>
            <a href="artigos.php">Artigos</a>
            <a href="login/login.php">Sair</a>
        </div>
    </div>
    <div class="content">
        <header class="mb-4">
            <h1>Gerenciamento de Clientes</h1>
        </header>

        <!-- Formulário de Filtro -->
        <form method="GET" action="gerenciaclientes.php" class="mb-4">
            <div class="row">
                <div class="col-md-3 mb-2">
                    <input type="text" name="nome" class="form-control" placeholder="Nome" value="<?php echo htmlspecialchars($_GET['nome'] ?? '', ENT_QUOTES); ?>">
                </div>
                <div class="col-md-3 mb-2">
                    <input type="email" name="email" class="form-control" placeholder="Email" value="<?php echo htmlspecialchars($_GET['email'] ?? '', ENT_QUOTES); ?>">
                </div>
                <div class="col-md-3 mb-2">
                    <input type="text" name="celular" class="form-control" placeholder="Celular" value="<?php echo htmlspecialchars($_GET['celular'] ?? '', ENT_QUOTES); ?>">
                </div>
                <div class="col-md-3 mb-2">
                    <input type="date" name="data_criacao" class="form-control" placeholder="Data de Criação" value="<?php echo htmlspecialchars($_GET['data_criacao'] ?? '', ENT_QUOTES); ?>">
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Filtrar</button>
        </form>

        <!-- Tabela de Clientes -->
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nome</th>
                    <th>Email</th>
                    <th>Celular</th>
                    <th>Data de Criação</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($clientes as $cliente): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($cliente['id'], ENT_QUOTES); ?></td>
                        <td><?php echo htmlspecialchars($cliente['nome'], ENT_QUOTES); ?></td>
                        <td><?php echo htmlspecialchars($cliente['email'], ENT_QUOTES); ?></td>
                        <td><?php echo htmlspecialchars($cliente['celular'], ENT_QUOTES); ?></td>
                        <td><?php echo htmlspecialchars($cliente['data_criacao'], ENT_QUOTES); ?></td>
                        <td>
                            <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editModal" data-id="<?php echo htmlspecialchars($cliente['id'], ENT_QUOTES); ?>" data-nome="<?php echo htmlspecialchars($cliente['nome'], ENT_QUOTES); ?>" data-email="<?php echo htmlspecialchars($cliente['email'], ENT_QUOTES); ?>" data-celular="<?php echo htmlspecialchars($cliente['celular'], ENT_QUOTES); ?>" data-data-criacao="<?php echo htmlspecialchars($cliente['data_criacao'], ENT_QUOTES); ?>">Editar</button>
                            <a href="?delete_id=<?php echo htmlspecialchars($cliente['id'], ENT_QUOTES); ?>" class="btn btn-danger btn-sm" onclick="return confirm('Tem certeza que deseja excluir este cliente?');">Excluir</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Modal de Edição -->
        <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editModalLabel">Editar Cliente</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form method="POST" action="gerenciaclientes.php">
                            <input type="hidden" name="edit_cliente_id" id="edit_cliente_id">
                            <div class="mb-3">
                                <label for="edit_nome" class="form-label">Nome</label>
                                <input type="text" class="form-control" id="edit_nome" name="edit_nome" required>
                            </div>
                            <div class="mb-3">
                                <label for="edit_email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="edit_email" name="edit_email" required>
                            </div>
                            <div class="mb-3">
                                <label for="edit_celular" class="form-label">Celular</label>
                                <input type="text" class="form-control" id="edit_celular" name="edit_celular" required>
                            </div>
                            <div class="mb-3">
                                <label for="edit_data_criacao" class="form-label">Data de Criação</label>
                                <input type="date" class="form-control" id="edit_data_criacao" name="edit_data_criacao" required>
                            </div>
                            <div class="mb-3">
                                <label for="edit_senha" class="form-label">Senha (opcional)</label>
                                <input type="password" class="form-control" id="edit_senha" name="edit_senha">
                            </div>
                            <button type="submit" class="btn btn-primary">Salvar Alterações</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const editModal = document.getElementById('editModal');
            editModal.addEventListener('show.bs.modal', function(event) {
                const button = event.relatedTarget;
                const clienteId = button.getAttribute('data-id');
                const nome = button.getAttribute('data-nome');
                const email = button.getAttribute('data-email');
                const celular = button.getAttribute('data-celular');
                const dataCriacao = button.getAttribute('data-data-criacao');
                
                const modalId = editModal.querySelector('#edit_cliente_id');
                const modalNome = editModal.querySelector('#edit_nome');
                const modalEmail = editModal.querySelector('#edit_email');
                const modalCelular = editModal.querySelector('#edit_celular');
                const modalDataCriacao = editModal.querySelector('#edit_data_criacao');
                
                modalId.value = clienteId;
                modalNome.value = nome;
                modalEmail.value = email;
                modalCelular.value = celular;
                modalDataCriacao.value = dataCriacao;
            });
        });
    </script>
</body>
</html>
