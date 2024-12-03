<?php
include_once 'config.php';

session_start();

header('Content-Type: application/json');

// Verifica se o usuário está autenticado
if (!isset($_SESSION['usuario_logado'])) {
    echo json_encode(['success' => false, 'message' => 'Usuário não autenticado.']);
    exit();
}

$usuarioLogado = json_decode($_SESSION['usuario_logado']);
$userId = $usuarioLogado->id;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comentario_id'])) {
    $comentarioId = $_POST['comentario_id'];

    $db = new Database();
    $conn = $db->getConnection();

    // Verifica se o comentário pertence ao usuário logado
    $sqlCheck = "SELECT user_id FROM comments WHERE id = ?";
    $stmtCheck = $conn->prepare($sqlCheck);
    $stmtCheck->execute([$comentarioId]);
    $comentario = $stmtCheck->fetch(PDO::FETCH_ASSOC);

    if (!$comentario || $comentario['user_id'] !== $userId) {
        echo json_encode(['success' => false, 'message' => 'Permissão negada.']);
        exit();
    }

    // Exclui o comentário
    $sql = "DELETE FROM comments WHERE id = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt->execute([$comentarioId])) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro ao excluir o comentário.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Requisição inválida.']);
}
?>