<?php
include_once 'config.php';

$db = new Database();
$conn = $db->getConnection();

session_start();
$usuarioLogado = json_decode($_SESSION['usuario_logado']);
$usuario_id = $usuarioLogado->id;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome'];
    $bio = $_POST['bio'];
    $imagemPerfil = isset($_FILES['imagem_perfil']) ? $_FILES['imagem_perfil'] : null;

    // Atualizar dados no banco
    $sql = "UPDATE usuarios SET nome = :nome, bio = :bio";
    if ($imagemPerfil) {
        $imagemPath = 'uploads/' . basename($imagemPerfil['name']);
        move_uploaded_file($imagemPerfil['tmp_name'], $imagemPath);
        $sql .= ", imagem_perfil = :imagem_perfil";
    }
    $sql .= " WHERE id = :id";
    $stmt = $conn->prepare($sql);

    $stmt->bindParam(':nome', $nome);
    $stmt->bindParam(':bio', $bio);
    if ($imagemPerfil) {
        $stmt->bindParam(':imagem_perfil', $imagemPath);
    }
    $stmt->bindParam(':id', $usuario_id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false]);
    }
}
?>
