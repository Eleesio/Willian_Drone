<?php
session_start();

// Definir constantes
define('MAX_SESSION_TIME', 10800); // Tempo máximo de sessão em segundos (3 horas)

function checkUserSession() {
    // Atualiza o tempo da última atividade
    $_SESSION['last_activity'] = time();

    // Verifica se o usuário está logado
    if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
        
        // Verifica se a última atividade foi definida
        if (isset($_SESSION['last_activity'])) {
            // Calcula o tempo de inatividade
            $inactiveTime = time() - $_SESSION['last_activity'];

            // Se o tempo de inatividade exceder o tempo máximo, desconecte o usuário
            if ($inactiveTime > MAX_SESSION_TIME) {
                // Destrua a sessão e redirecione para a página de login
                session_unset();
                session_destroy();
                header("Location: ../login/login.php");
                exit();
            }
        }
    } else {
        // Se o usuário não estiver logado, redireciona para a página de login
        header("Location: ../login/login.php");
        exit();
    }
}

// Chame a função em todas as páginas que requerem autenticação
checkUserSession();
?>
