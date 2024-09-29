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
                     <form action="Cadastro.php" method="POST">
                        <div class="mb-3 input-group">
                           <span class="input-group-text"><i class="fas fa-user"></i></span>
                           <input type="text" class="form-control" id="exampleInputUsername1" name="username" placeholder="Username" required>
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
                        <a href="login.html.php" class="card-link">Já tem uma conta? Faça login</a>
                     </form>
                  </div>
               </div>
            </div>
         </div>
      </div>
      <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
   </body>
</html>