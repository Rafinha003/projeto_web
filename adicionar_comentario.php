<?php
include_once 'config.php';

session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_SESSION['usuario_logado'])) {
        $usuarioLogado = json_decode($_SESSION['usuario_logado']);
        $user_id = $usuarioLogado->id;
    } else {
        echo json_encode(['success' => false, 'message' => 'Usuário não autenticado.']);
        exit();
    }

    $post_id = $_POST['post_id'];
    $content = trim($_POST['content']);

    if (empty($content)) {
        echo json_encode(['success' => false, 'message' => 'O comentário não pode estar vazio.']);
        exit();
    }

    // Conectar ao banco de dados
    $db = new Database();
    $conn = $db->getConnection();

    // Inserir comentário
    $sql = "INSERT INTO comments (post_id, user_id, content) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$post_id, $user_id, $content]);

    // Retornar dados do post e comentário
    $sqlPost = "SELECT * FROM posts WHERE id = ?";
    $stmtPost = $conn->prepare($sqlPost);
    $stmtPost->execute([$post_id]);
    $post = $stmtPost->fetch();

    echo json_encode([
        'success' => true,
        'image' => $post['imagem'],
        'description' => $post['descricao']
    ]);
}
?>