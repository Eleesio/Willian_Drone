<?php
// Incluir o arquivo de configuração para conectar ao banco de dados
require_once '../configuration.php';

session_start();

// Criar uma conexão com o banco de dados
$conn = new mysqli($host, $user, $password, $db);

// Verificar a conexão
if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}

// Variáveis de erro e sucesso
$login_error = '';
$register_error = '';
$register_success = '';

// Verificar se o formulário de login foi enviado
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    // Obter dados do formulário
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Verificar se o usuário existe no banco de dados
    $sql = "SELECT id, email FROM clientes WHERE email = ? AND senha = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $email, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Usuário encontrado
        $user = $result->fetch_assoc();
        
        // Armazenar dados na sessão
        $_SESSION['logged_in'] = true;
        $_SESSION['user_id'] = $user['id']; // Armazena a ID do usuário
        $_SESSION['email'] = $email;

        header("Location: ../index.php");
        exit();
    } else {
        // Usuário não encontrado
        $login_error = "Email ou senha incorretos.";
    }

    // Fechar a declaração
    $stmt->close();
}

// Verificar se o formulário de registro foi enviado
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['register'])) {
    // Obter dados do formulário
    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $celular = $_POST['celular'];
    $password = $_POST['password'];

    // Verificar se o email já está registrado
    $sql = "SELECT * FROM clientes WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Email já está registrado
        $register_error = "O email já está registrado.";
    } else {
        // Inserir o novo usuário no banco de dados
        $sql = "INSERT INTO clientes (nome, email, celular, senha) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssss", $nome, $email, $celular, $password);

        if ($stmt->execute()) {
            $register_success = "Cadastro realizado com sucesso! Você pode agora fazer login.";
        } else {
            $register_error = "Erro ao cadastrar. Tente novamente.";
        }
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
        <h1>Bem vindo!</h1>
        <p>
            Entre na sua conta para acessar suas filmagens aéreas, gerenciar projetos e solicitar novos serviços. Com a Willian Drone, suas ideias ganham vida através de imagens deslumbrantes capturadas de uma perspectiva única. Faça login e continue a elevar o nível das suas produções conosco.
        </p>
        <input type="radio" id="tab-login" name="tabs" checked />
        <input type="radio" id="tab-register" name="tabs" />
        <nav class="tabs">
            <section class="form-login">
                <div class="form-login-welcome">
                    <h2>Já tem uma conta?</h2>
                    <p>Clique no botão abaixo para entrar</p>
                    <label for="tab-login" class="tab-login btn">Iniciar Sessão</label>
                </div>
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
            <section class="form-register">
                <div class="form-register-welcome">
                    <h2>Ainda não possui uma conta?</h2>
                    <p>Clique no botão abaixo para cadastrar-se</p>
                    <label for="tab-register" class="tab-register btn">Cadastrar</label>
                </div>
                <div class="form-register-container">
                    <h2>Cadastrar-se</h2>
                    <?php if ($register_error): ?>
                        <p style="color: red;"><?php echo $register_error; ?></p>
                    <?php endif; ?>
                    <?php if ($register_success): ?>
                        <p style="color: green;"><?php echo $register_success; ?></p>
                    <?php endif; ?>
                    <form method="post" action="">
                        <input type="text" name="nome" placeholder="Nome" required />
                        <input type="text" name="celular" placeholder="Celular" required />
                        <input type="email" name="email" placeholder="Email" required />
                        <input type="password" name="password" placeholder="Senha" required />
                        <button type="submit" name="register" class="btn">Concluir Cadastro</button>
                    </form>
                </div>
            </section>
            <div class="tag-bg-selected"></div>
        </nav>
    </main>
</body>
</html>
