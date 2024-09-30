<?php
session_start();

class Login {
    public function autenticarUsuario($login, $senha) {
        $usuarioTeste = [
            'username' => 'admin',
            'email' => 'admin@example.com',
            'senha' => password_hash('123', PASSWORD_DEFAULT)
        ];
        if (($usuarioTeste['username'] === $login || $usuarioTeste['email'] === $login) &&
            password_verify($senha, $usuarioTeste['senha'])) {
            return json_encode($usuarioTeste);
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
    $usuario = $loginClass->autenticarUsuario($login, $senha);
    if ($usuario) {
        echo "<script>
                sessionStorage.setItem('usuario_logado', '$usuario');
                window.location.href = 'index.html';
              </script>";
        exit();
    } else {
        $error = "Usuário ou senha inválidos!";
    }
}

if ($error) {
    header("Location: login.html.php?error=" . urlencode($error));
    exit();
}
