<?php
include_once 'config.php';

header('Content-Type: application/json');

$db = new Database();
$conn = $db->getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['postId'])) {
    $postId = $_POST['postId'];

    $sql = "SELECT c.id, c.content, c.user_id AS comentario_user_id, u.nome AS autor
            FROM comments c
            JOIN usuarios u ON c.user_id = u.id
            WHERE c.post_id = ?
            ORDER BY c.created_at ASC";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$postId]);
    $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Usuário logado
    session_start();
    $usuarioLogado = json_decode($_SESSION['usuario_logado']);
    $userId = $usuarioLogado->id;

    foreach ($comments as $comment) {
        // Verifica se o comentário pertence ao usuário logado
        $showEditDeleteButtons = $comment['comentario_user_id'] === $userId ? 'block' : 'none';

        echo '<div class="comentario d-flex align-items-center mb-3" data-id="' . $comment['id'] . '">
                <div class="d-flex justify-content-between w-100"> <!-- A linha foi ajustada para ocupar toda a largura -->
                    <p class="mb-0" style="flex-grow: 1;"><strong>' . htmlspecialchars($comment['autor']) . ':</strong> ' . htmlspecialchars($comment['content']) . '</p>
                    <div class="dropdown" style="display: ' . $showEditDeleteButtons . ';"> <!-- Dropdown à direita, visível só para o autor -->
                        <button class="btn btn-link dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                        </button>
                        <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                            <li><a class="dropdown-item" href="#" onclick="abrirModalEditarComentario(' . $comment['id'] . ', \'' . htmlspecialchars($comment['content']) . '\')"><i class="bi bi-pencil"></i> Editar</a></li>
                            <li><a class="dropdown-item" href="#" onclick="abrirModalDeletarComentario(' . $comment['id'] . ')"><i class="bi bi-trash"></i> Deletar</a></li>
                        </ul>
                    </div>
                </div>
              </div>
              <div style="border-bottom: 1px solid rgba(0, 0, 0, 0.1); margin-bottom: 10px; margin-top: -10px"></div>';
    }
}
?>