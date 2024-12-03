<?php
// Login.php
session_start();
require_once 'config.php';

class Login {
    private $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    public function autenticarUsuario($login, $senha) {
        $query = "SELECT id, nome, email, senha, imagem_perfil, bio FROM usuarios WHERE email = :login OR nome = :login LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':login', $login);
        $stmt->execute();
        
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($usuario && password_verify($senha, $usuario['senha'])) {
            return json_encode([
                'id' => $usuario['id'],
                'nome' => $usuario['nome'],
                'email' => $usuario['email'],
                'imagem_perfil' => $usuario['imagem_perfil'],
                'bio' => $usuario['bio']
            ]);
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
        $_SESSION['usuario_logado'] = $usuario;
        echo "<script>
                sessionStorage.setItem('usuario_logado', '$usuario');
                window.location.href = 'pagina-inicial.php';
              </script>";
        exit();
    } else {
        $error = "Usuário ou senha inválidos!";
        header("Location: Login.php?error=" . urlencode($error));
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <title>Login</title>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-user-circle"></i> Login
                    </div>
                    <div class="card-body">
                        <!-- Mensagem de erro -->
                        <?php if (isset($_GET['error'])): ?>
                            <div class="alert alert-danger">
                                <?= htmlspecialchars($_GET['error']) ?>
                            </div>
                        <?php endif; ?>

                        <!-- Formulário de Login -->
                        <form action="Login.php" method="POST">
                            <!-- Campo de Login -->
                            <div class="mb-3 input-group">
                                <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                <input type="text" class="form-control" name="login" placeholder="Email ou Username" required>
                            </div>

                            <!-- Campo de Senha -->
                            <div class="mb-3 input-group">
                                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                <input type="password" class="form-control" name="senha" placeholder="Senha" required>
                            </div>

                            <!-- Botão de Login -->
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">Login</button>
                            </div>

                            <!-- Link para Cadastro -->
                            <div class="mt-3 text-center">
                                <a href="Cadastro.php" class="card-link">Não tem uma conta? Cadastre-se</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
</body>
</html>