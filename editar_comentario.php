<?php
include_once 'config.php';

header('Content-Type: application/json');

$db = new Database();
$conn = $db->getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $comentarioId = $_POST['comentario_id'] ?? null;
    $novoTexto = $_POST['novo_texto'] ?? null;

    if ($comentarioId && $novoTexto) {
        try {
            $sql = "UPDATE comments SET content = :novo_texto WHERE id = :comentario_id";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':novo_texto', $novoTexto);
            $stmt->bindParam(':comentario_id', $comentarioId);

            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Comentário atualizado com sucesso.']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Erro ao atualizar comentário.']);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Erro no servidor: ' . $e->getMessage()]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Dados inválidos fornecidos.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Método não permitido.']);
}
?>