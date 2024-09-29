<?php
session_start();

class Cadastro { 

    public function registrarUsuario($username, $email, $senha, $confirma_senha) {
        if ($senha !== $confirma_senha) {
            $_SESSION['erro'] = "As senhas não coincidem!";
            header("Location: cadastro.html.php");
            exit();
        }

        $_SESSION['usuarios'][] = [
            'username' => $username,
            'email' => $email,
            'senha' => password_hash($senha, PASSWORD_DEFAULT)
        ];
        header("Location: login.html.php");
        exit();
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $senha = $_POST['senha'];
    $confirma_senha = $_POST['confirma_senha'];

    $cadastro = new Cadastro();
    $cadastro->registrarUsuario($username, $email, $senha, $confirma_senha);
}
