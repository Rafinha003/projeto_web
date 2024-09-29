<?php
session_start();

class Login {
    public function autenticarUsuario($login, $senha) {
        if (isset($_SESSION['usuarios'])) {
            foreach ($_SESSION['usuarios'] as $usuario) {
                if (($usuario['username'] === $login || $usuario['email'] === $login) && 
                    password_verify($senha, $usuario['senha'])) {
                    $_SESSION['usuario_logado'] = $usuario;
                    header("Location: index.html");
                    exit();
                }
            }
        }

        // Usuário de teste
        $usuarioTeste = [
            'username' => 'admin',
            'email' => 'admin@example.com',
            'senha' => password_hash('123', PASSWORD_DEFAULT)
        ];

        if (($usuarioTeste['username'] === $login || $usuarioTeste['email'] === $login) &&
            password_verify($senha, $usuarioTeste['senha'])) {
            $_SESSION['usuario_logado'] = $usuarioTeste;
            header("Location: index.html");
            exit();
        }

        return false; 
    }
}

$error = '';
$loginValue = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $login = $_POST['login'];
    $senha = $_POST['senha'];
    $loginValue = $login;

    $loginClass = new Login();
    if (!$loginClass->autenticarUsuario($login, $senha)) {
        $error = "Usuário ou senha inválidos!";
    }
}

if ($error) {
    header("Location: login.html.php?error=" . urlencode($error));
    exit();
}
