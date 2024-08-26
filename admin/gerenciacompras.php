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

// Processar a edição da compra
if (isset($_POST['edit_compra_id'])) {
    $id = filter_input(INPUT_POST, 'edit_compra_id', FILTER_VALIDATE_INT);
    $cliente_id = filter_input(INPUT_POST, 'edit_cliente_id', FILTER_VALIDATE_INT);
    $foto_id = filter_input(INPUT_POST, 'edit_foto_id', FILTER_VALIDATE_INT);
    $valor_total = filter_input(INPUT_POST, 'edit_valor_total', FILTER_SANITIZE_STRING);
    $categoria_id = filter_input(INPUT_POST, 'edit_categoria_id', FILTER_VALIDATE_INT);
    $status = filter_input(INPUT_POST, 'edit_status', FILTER_SANITIZE_STRING);

    $sql = "UPDATE compras SET cliente_id = :cliente_id, foto_id = :foto_id, valor_total = :valor_total, categoria_id = :categoria_id, status = :status WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':cliente_id', $cliente_id);
    $stmt->bindParam(':foto_id', $foto_id);
    $stmt->bindParam(':valor_total', $valor_total);
    $stmt->bindParam(':categoria_id', $categoria_id);
    $stmt->bindParam(':status', $status);
    $stmt->bindParam(':id', $id);

    try {
        $stmt->execute();
        header('Location: gerenciacompras.php');
        exit();
    } catch (PDOException $e) {
        $error = 'Erro ao atualizar a compra: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES);
    }
}

// Processar a exclusão da compra
if (isset($_GET['delete_id'])) {
    $id = filter_input(INPUT_GET, 'delete_id', FILTER_VALIDATE_INT);

    $sql = "DELETE FROM compras WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id', $id);

    try {
        $stmt->execute();
        header('Location: gerenciacompras.php');
        exit();
    } catch (PDOException $e) {
        $error = 'Erro ao excluir a compra: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES);
    }
}

// Buscar compras com filtros
$sql = "SELECT c.*, cl.nome AS cliente_nome, cl.celular AS cliente_celular, cl.email AS cliente_email 
        FROM compras c
        LEFT JOIN clientes cl ON c.cliente_id = cl.id
        WHERE 1=1";
$params = [];

if (!empty($_GET['cliente_nome'])) {
    $sql .= " AND cl.nome LIKE :cliente_nome";
    $params[':cliente_nome'] = '%' . htmlspecialchars($_GET['cliente_nome']) . '%';
}

if (!empty($_GET['cliente_celular'])) {
    $sql .= " AND cl.celular LIKE :cliente_celular";
    $params[':cliente_celular'] = '%' . htmlspecialchars($_GET['cliente_celular']) . '%';
}

if (!empty($_GET['cliente_email'])) {
    $sql .= " AND cl.email LIKE :cliente_email";
    $params[':cliente_email'] = '%' . htmlspecialchars($_GET['cliente_email']) . '%';
}

if (!empty($_GET['data_compra'])) {
    $sql .= " AND DATE(c.data_compra) = :data_compra";
    $params[':data_compra'] = htmlspecialchars($_GET['data_compra']);
}

if (!empty($_GET['foto_id'])) {
    $sql .= " AND c.foto_id = :foto_id";
    $params[':foto_id'] = htmlspecialchars($_GET['foto_id']);
}

if (!empty($_GET['status'])) {
    $sql .= " AND c.status = :status";
    $params[':status'] = htmlspecialchars($_GET['status']);
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$compras = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Processar a exibição do formulário de edição
$compraParaEditar = null;
if (isset($_GET['edit_id'])) {
    $editId = filter_input(INPUT_GET, 'edit_id', FILTER_VALIDATE_INT);
    $sql = "SELECT * FROM compras WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id', $editId);
    $stmt->execute();
    $compraParaEditar = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Willian Drone - Gerenciar Compras</title>
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
            <a class="active" href="gerenciacompras.php">Gerenciar Compras</a>
            <a href="artigos.php">Artigos</a>
            <a href="login/login.php">Sair</a>
        </div>
    </div>
    <div class="content">
        <header class="mb-4">
            <h1>Gerenciamento de Compras</h1>
        </header>

        <!-- Mensagem de Erro -->
        <?php if ($error): ?>
        <div class="alert alert-danger" role="alert">
            <?php echo htmlspecialchars($error, ENT_QUOTES); ?>
        </div>
        <?php endif; ?>

        <!-- Formulário de Filtro -->
        <form method="GET" action="gerenciacompras.php" class="mb-4">
            <div class="row">
                <div class="col-md-3 mb-2">
                    <input type="text" name="cliente_nome" class="form-control" placeholder="Nome do Cliente" value="<?php echo htmlspecialchars($_GET['cliente_nome'] ?? '', ENT_QUOTES); ?>">
                </div>
                <div class="col-md-3 mb-2">
                    <input type="text" name="cliente_celular" class="form-control" placeholder="Celular do Cliente" value="<?php echo htmlspecialchars($_GET['cliente_celular'] ?? '', ENT_QUOTES); ?>">
                </div>
                <div class="col-md-3 mb-2">
                    <input type="email" name="cliente_email" class="form-control" placeholder="Email do Cliente" value="<?php echo htmlspecialchars($_GET['cliente_email'] ?? '', ENT_QUOTES); ?>">
                </div>
                <div class="col-md-3 mb-2">
                    <input type="date" name="data_compra" class="form-control" value="<?php echo htmlspecialchars($_GET['data_compra'] ?? '', ENT_QUOTES); ?>">
                </div>
                <div class="col-md-3 mb-2">
                    <input type="number" name="foto_id" class="form-control" placeholder="ID da Foto" value="<?php echo htmlspecialchars($_GET['foto_id'] ?? '', ENT_QUOTES); ?>">
                </div>
                <div class="col-md-3 mb-2">
                    <select name="status" class="form-control">
                        <option value="">Status</option>
                        <option value="Pendente" <?php echo (isset($_GET['status']) && $_GET['status'] == 'Pendente') ? 'selected' : ''; ?>>Pendente</option>
                        <option value="Aprovado" <?php echo (isset($_GET['status']) && $_GET['status'] == 'Aprovado') ? 'selected' : ''; ?>>Aprovado</option>
                        <option value="Rejeitado" <?php echo (isset($_GET['status']) && $_GET['status'] == 'Rejeitado') ? 'selected' : ''; ?>>Rejeitado</option>
                    </select>
                </div>
                <div class="col-md-12 mb-2">
                    <button type="submit" class="btn btn-primary">Filtrar</button>
                </div>
            </div>
        </form>

        <!-- Tabela de Compras -->
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Cliente</th>
                    <th>Celular</th>
                    <th>Email</th>
                    <th>Data da Compra</th>
                    <th>Foto ID</th>
                    <th>Valor Total</th>
                    <th>Categoria ID</th>
                    <th>Status</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($compras as $compra): ?>
                <tr>
                    <td><?php echo htmlspecialchars($compra['id'], ENT_QUOTES); ?></td>
                    <td><?php echo htmlspecialchars($compra['cliente_nome'], ENT_QUOTES); ?></td>
                    <td><?php echo htmlspecialchars($compra['cliente_celular'], ENT_QUOTES); ?></td>
                    <td><?php echo htmlspecialchars($compra['cliente_email'], ENT_QUOTES); ?></td>
                    <td><?php echo htmlspecialchars($compra['data_compra'], ENT_QUOTES); ?></td>
                    <td><?php echo htmlspecialchars($compra['foto_id'], ENT_QUOTES); ?></td>
                    <td><?php echo htmlspecialchars($compra['valor_total'], ENT_QUOTES); ?></td>
                    <td><?php echo htmlspecialchars($compra['categoria_id'], ENT_QUOTES); ?></td>
                    <td><?php echo htmlspecialchars($compra['status'], ENT_QUOTES); ?></td>
                    <td>
                        <a href="?edit_id=<?php echo htmlspecialchars($compra['id'], ENT_QUOTES); ?>" class="btn btn-warning btn-sm">Editar</a>
                        <a href="?delete_id=<?php echo htmlspecialchars($compra['id'], ENT_QUOTES); ?>" class="btn btn-danger btn-sm" onclick="return confirm('Tem certeza que deseja excluir esta compra?')">Excluir</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Modal de Edição -->
        <div class="modal fade" id="editCompraModal" tabindex="-1" aria-labelledby="editCompraModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editCompraModalLabel">Editar Compra</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form method="POST" action="gerenciacompras.php">
                        <div class="modal-body">
                            <input type="hidden" name="edit_compra_id" value="<?php echo htmlspecialchars($compraParaEditar['id'] ?? '', ENT_QUOTES); ?>">
                            <div class="mb-3">
                                <label for="edit_cliente_id" class="form-label">Cliente ID</label>
                                <input type="number" class="form-control" id="edit_cliente_id" name="edit_cliente_id" value="<?php echo htmlspecialchars($compraParaEditar['cliente_id'] ?? '', ENT_QUOTES); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="edit_foto_id" class="form-label">Foto ID</label>
                                <input type="number" class="form-control" id="edit_foto_id" name="edit_foto_id" value="<?php echo htmlspecialchars($compraParaEditar['foto_id'] ?? '', ENT_QUOTES); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="edit_valor_total" class="form-label">Valor Total</label>
                                <input type="text" class="form-control" id="edit_valor_total" name="edit_valor_total" value="<?php echo htmlspecialchars($compraParaEditar['valor_total'] ?? '', ENT_QUOTES); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="edit_categoria_id" class="form-label">Categoria ID</label>
                                <input type="number" class="form-control" id="edit_categoria_id" name="edit_categoria_id" value="<?php echo htmlspecialchars($compraParaEditar['categoria_id'] ?? '', ENT_QUOTES); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="edit_status" class="form-label">Status</label>
                                <select class="form-select" id="edit_status" name="edit_status" required>
                                    <option value="Pendente" <?php echo (isset($compraParaEditar['status']) && $compraParaEditar['status'] == 'Pendente') ? 'selected' : ''; ?>>Pendente</option>
                                    <option value="Aprovado" <?php echo (isset($compraParaEditar['status']) && $compraParaEditar['status'] == 'Aprovado') ? 'selected' : ''; ?>>Aprovado</option>
                                    <option value="Rejeitado" <?php echo (isset($compraParaEditar['status']) && $compraParaEditar['status'] == 'Rejeitado') ? 'selected' : ''; ?>>Rejeitado</option>
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-primary">Salvar alterações</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts do Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Script para abrir o modal de edição
        document.addEventListener('DOMContentLoaded', function () {
            const editId = new URLSearchParams(window.location.search).get('edit_id');
            if (editId) {
                const modal = new bootstrap.Modal(document.getElementById('editCompraModal'));
                modal.show();
            }
        });
    </script>
</body>
</html>
