<?php
include_once 'config.php';

header('Content-Type: application/json');

$db = new Database();
$conn = $db->getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postId = $_POST['post_id'] ?? null;
    $novaDescricao = $_POST['descricao'] ?? null;
    $novaImagem = $_FILES['imagem'] ?? null;

    if ($postId && $novaDescricao) {
        try {
            // Processar o upload da nova imagem, se fornecida
            $novoCaminhoImagem = null;
            if ($novaImagem) {
                $pastaUploads = 'uploads/posts/';
                if (!file_exists($pastaUploads)) {
                    mkdir($pastaUploads, 0777, true);
                }
                $nomeArquivo = uniqid() . '-' . basename($novaImagem['name']);
                $novoCaminhoImagem = $pastaUploads . $nomeArquivo;
                if (!move_uploaded_file($novaImagem['tmp_name'], $novoCaminhoImagem)) {
                    echo json_encode(['success' => false, 'message' => 'Erro ao salvar a nova imagem.']);
                    exit;
                }
            }

            // Atualizar o post no banco de dados
            $sql = "UPDATE posts SET descricao = :descricao";
            if ($novoCaminhoImagem) {
                $sql .= ", imagem = :imagem";
            }
            $sql .= " WHERE id = :post_id";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':descricao', $novaDescricao);
            if ($novoCaminhoImagem) {
                $stmt->bindParam(':imagem', $novoCaminhoImagem);
            }
            $stmt->bindParam(':post_id', $postId);

            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Post atualizado com sucesso.']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Erro ao atualizar o post.']);
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