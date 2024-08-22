<?php
// Incluir o arquivo de configuração para conectar ao banco de dados
require_once '../../configuration.php';

session_start();

// Criar uma conexão com o banco de dados
$conn = new mysqli($host, $user, $password, $db);

// Verificar a conexão
if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}

// Variável de erro
$login_error = '';

// Verificar se o formulário de login foi enviado
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    // Obter dados do formulário
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Verificar se o usuário existe no banco de dados
    $sql = "SELECT * FROM users_admin WHERE email = ? AND senha = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $email, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Usuário encontrado, redirecionar para a página de administração
        $_SESSION['admin_email'] = $email;
        header("Location: ../index.php");
        exit();
    } else {
        // Usuário não encontrado, exibir erro
        $login_error = "Email ou senha incorretos.";
    }

    // Fechar a declaração
    $stmt->close();
}

// Fechar a conexão com o banco de dados
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="icon" href="img/icon.ico">
    <link rel="stylesheet" href="css/login.css">
</head>
<body>
    <main class="container">
        <h1>-Administração-<br />Willian Drone</h1>
        <input type="radio" id="tab-login" name="tabs" checked />
        <nav class="tabs">
            <section class="form-login">
                <div class="form-login-container">
                    <h2>Iniciar Sessão</h2>
                    <?php if ($login_error): ?>
                        <p style="color: red;"><?php echo $login_error; ?></p>
                    <?php endif; ?>
                    <form method="post" action="">
                        <input type="email" name="email" placeholder="Email" required />
                        <input type="password" name="password" placeholder="Senha" required />
                        <button type="submit" name="login" class="btn">Entrar</button>
                    </form>
                </div>
            </section>
            <div class="tag-bg-selected"></div>
        </nav>
    </main>
</body>
</html>
