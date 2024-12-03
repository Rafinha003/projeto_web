<?php
include_once 'config.php';

$db = new Database();
$conn = $db->getConnection();

session_start();

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario_logado'])) {
    http_response_code(403); // Acesso negado
    echo json_encode(['message' => 'Usuário não está logado.']);
    exit();
}

// Obtém o ID do post a ser deletado
$post_id = $_POST['post_id'] ?? null;

if ($post_id) {
    // Verifica se o post pertence ao usuário logado
    $usuarioLogado = json_decode($_SESSION['usuario_logado']);
    $usuario_id = $usuarioLogado->id;

    // Prepara a consulta para verificar se o post pertence ao usuário
    $sql = "SELECT * FROM posts WHERE id = :post_id AND usuario_id = :usuario_id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':post_id', $post_id);
    $stmt->bindParam(':usuario_id', $usuario_id);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        // O post existe e pertence ao usuário, pode ser deletado
        $sqlDelete = "DELETE FROM posts WHERE id = :post_id";
        $stmtDelete = $conn->prepare($sqlDelete);
        $stmtDelete->bindParam(':post_id', $post_id);
        if ($stmtDelete->execute()) {
            echo json_encode(['message' => 'Post deletado com sucesso.']);
        } else {
            http_response_code(500); // Erro interno do servidor
            echo json_encode(['message' => 'Erro ao deletar o post.']);
        }
    } else {
        http_response_code(404); // Post não encontrado
        echo json_encode(['message' => 'Post não encontrado ou não pertence ao usuário.']);
    }
} else {
    http_response_code(400); // Requisição inválida
    echo json_encode(['message' => 'ID do post não fornecido.']);
}