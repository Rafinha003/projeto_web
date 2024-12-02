<?php
// Cadastro.php
session_start();
require_once 'config.php';

class Cadastro {

    private $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    public function registrarUsuario($nome, $email, $senha, $confirma_senha) {
        if ($senha !== $confirma_senha) {
            $_SESSION['erro'] = "As senhas não coincidem!";
            header("Location: Cadastro.php");
            exit();
        }

        // Verificar se o email ou nome já existe
        $query = "SELECT id FROM usuarios WHERE email = :email OR nome = :nome";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':nome', $nome);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $_SESSION['erro'] = "Email ou nome já estão cadastrados!";
            header("Location: Cadastro.php");
            exit();
        }

        // Inserir novo usuário no banco de dados
        $query = "INSERT INTO usuarios (nome, email, senha) VALUES (:nome, :email, :senha)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':nome', $nome);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':senha', password_hash($senha, PASSWORD_DEFAULT));

        if ($stmt->execute()) {
            header("Location: Login.php");
            exit();
        } else {
            $_SESSION['erro'] = "Erro ao registrar usuário!";
            header("Location: Cadastro.php");
            exit();
        }
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $senha = $_POST['senha'];
    $confirma_senha = $_POST['confirma_senha'];

    $cadastro = new Cadastro();
    $cadastro->registrarUsuario($nome, $email, $senha, $confirma_senha);
}
?>

<!DOCTYPE html>
<html lang="pt-br">
   <head>
      <meta charset="utf-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
      <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
      <link rel="stylesheet" href="style.css">
      <title>Cadastro de usuário</title>
   </head>
   <body class="body-login">
      <div class="container">
         <div class="row justify-content-center">
            <div class="col-md-6">
               <div class="card">
                  <div class="card-header">
                     <i class="fas fa-user-circle"></i> Cadastro
                  </div>
                  <div class="card-body">
                     <?php if (isset($_SESSION['erro'])): ?>
                        <div class="alert alert-danger">
                            <?= htmlspecialchars($_SESSION['erro']) ?>
                        </div>
                        <?php unset($_SESSION['erro']); ?>
                     <?php endif; ?>
                     <form action="Cadastro.php" method="POST">
                        <div class="mb-3 input-group">
                           <span class="input-group-text"><i class="fas fa-user"></i></span>
                           <input type="text" class="form-control" id="exampleInputUsername1" name="nome" placeholder="Username" required>
                        </div>
                        <div class="mb-3 input-group">
                           <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                           <input type="email" class="form-control" id="exampleInputEmail1" name="email" placeholder="Email" required>
                        </div>
                        <div class="mb-3 input-group">
                           <span class="input-group-text"><i class="fas fa-lock"></i></span>
                           <input type="password" class="form-control" id="exampleInputPassword1" name="senha" placeholder="Senha" required>
                        </div>
                        <div class="mb-3 input-group">
                           <span class="input-group-text"><i class="fas fa-lock"></i></span>
                           <input type="password" class="form-control" id="exampleInputConfirmPassword1" name="confirma_senha" placeholder="Confirme sua Senha" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Cadastrar</button>
                        <a href="Login.php" class="card-link">Já tem uma conta? Faça login</a>
                     </form>
                  </div>
               </div>
            </div>
         </div>
      </div>
      <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
   </body>
</html>
