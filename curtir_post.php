<?php
include_once 'config.php';

$db = new Database();
$conn = $db->getConnection();

// Verificando se o ID do post foi enviado
if (isset($_POST['postId'])) {
    $postId = $_POST['postId'];

    // Atualizando o número de curtidas no banco
    $sql = "UPDATE posts SET curtidas = curtidas + 1 WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$postId]);

    // Recuperando o novo número de curtidas
    $sql = "SELECT curtidas FROM posts WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$postId]);
    $post = $stmt->fetch(PDO::FETCH_ASSOC);

    // Retornando o novo número de curtidas
    echo $post['curtidas'];
}
?>