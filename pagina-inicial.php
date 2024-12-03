<?php
include_once 'config.php';

$db = new Database();
$conn = $db->getConnection();

session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_SESSION['usuario_logado'])) {
        $usuarioLogado = json_decode($_SESSION['usuario_logado']);
        $usuario_id = $usuarioLogado->id;
    } else {
        echo "Usuário não autenticado.";
        exit();
    }

    $descricao = $_POST['conteudo'];
    $imagem = '';

    if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] === UPLOAD_ERR_OK) {
        $imagem = 'uploads/' . basename($_FILES['imagem']['name']);
        move_uploaded_file($_FILES['imagem']['tmp_name'], $imagem);
    }

    $sql = "INSERT INTO posts (usuario_id, imagem, descricao) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$usuario_id, $imagem, $descricao]);

    header('Location: pagina-inicial.php');
    exit;
}

// Recuperando os posts do banco de dados
$sql = "SELECT * FROM posts";
$stmt = $conn->prepare($sql);
$stmt->execute();
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Verifique se há posts antes de exibir
if ($posts === false) {
    $posts = [];
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tela Inicial</title>
    <link rel="stylesheet" href="bootstrap-5.3.3-dist/bootstrap-5.3.3-dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="bootstrap-icons-1.11.3/font/bootstrap-icons.min.css">
    <style>
        .post-img {
            width: 600px;
            height: 400px;
            object-fit: contain;
        }
    </style>
</head>

<body>

    <?php include('barra-lateral.php'); ?>

    <!-- POSTS -->
    <div class="d-grid gap-2 col-6 center-content" style="margin-left: 620px;">
        <button class="btn btn-primary btn-publi" type="button" data-bs-toggle="modal" data-bs-target="#modalPubli"
            style="box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); border-radius: 8px;">Criar publicação</button>
        <div id="postContainer">
            <?php if (!empty($posts)): ?>
                <?php foreach ($posts as $post): ?>
                    <?php
                    $sqlUser = "SELECT nome, imagem_perfil FROM usuarios WHERE id = ?";
                    $stmtUser = $conn->prepare($sqlUser);
                    $stmtUser->execute([$post['usuario_id']]);
                    $user = $stmtUser->fetch(PDO::FETCH_ASSOC);
                    $nomeUsuario = $user['nome'];
                    $imagemPerfil = $user['imagem_perfil'];
                    ?>
                    <div class="post" style="box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); border-radius: 8px;">
                        <div class="post-header">
                            <img src="<?= !empty($imagemPerfil) ? $imagemPerfil : 'https://via.placeholder.com/40'; ?>"
                                alt="Profile picture" class="profile-img">
                            <strong id="nomeUsuario">
                                <a href="perfil.php?id=<?= $post['usuario_id']; ?>"
                                    style="text-decoration: none; color: inherit;">
                                    <?= htmlspecialchars($nomeUsuario); ?>
                                </a>
                            </strong>
                        </div>
                        <img src="<?= $post['imagem']; ?>" alt="Post image" class="post-img"
                            onclick="openPostModal(<?= $post['id']; ?>, '<?= $post['imagem']; ?>', '<?= htmlspecialchars($post['descricao']); ?>')">

                        <div class="post-actions d-flex justify-content-between">
                            <div>
                                <button class="btn btn-outline-danger" onclick="curtirPost(<?= $post['id']; ?>)">
                                    <i class="bi bi-heart"></i>
                                </button>
                                <button class="btn btn-outline-dark"><i class="bi bi-send"></i></button>
                                <strong id="curtidas_<?= $post['id']; ?>"><?= $post['curtidas']; ?> curtidas</strong>
                            </div>
                            <div>
                                <button class="btn btn-outline-warning"><i class="bi bi-bookmark"></i></button>
                            </div>
                        </div>
                        <div class="post-caption">
                            <strong><?= htmlspecialchars($nomeUsuario); ?></strong>
                            <?= htmlspecialchars($post['descricao']); ?>
                        </div>
                        <div class="comment-section" id="post_<?= $post['id']; ?>">
                            <div class="existing-comments">
                                <?php
                                $sqlComments = "SELECT c.content, u.nome AS autor
                                        FROM comments c
                                        JOIN usuarios u ON c.user_id = u.id
                                        WHERE c.post_id = ?
                                        ORDER BY c.created_at ASC
                                        LIMIT 2";
                                $stmtComments = $conn->prepare($sqlComments);
                                $stmtComments->execute([$post['id']]);
                                $comments = $stmtComments->fetchAll(PDO::FETCH_ASSOC);

                                if (!empty($comments)):
                                    foreach ($comments as $comment): ?>
                                        <p><strong><?= htmlspecialchars($comment['autor']); ?>:</strong>
                                            <?= htmlspecialchars($comment['content']); ?></p>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <p>Não há comentários para exibir.</p>
                                <?php endif; ?>
                            </div>
                            <form onsubmit="adicionarComentario(event, <?= $post['id']; ?>)" id="form_<?= $post['id']; ?>">
                                <input type="hidden" name="post_id" value="<?= $post['id']; ?>">
                                <textarea name="content" rows="1" placeholder="Adicione um comentário..." required></textarea>
                                <button type="submit" class="btn btn-primary">Comentar</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Não há posts para exibir no momento.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Modal para exibir a imagem maior e os comentários -->
    <div class="modal fade" id="modalImagem" tabindex="-1" aria-labelledby="modalImagemLabel" aria-hidden="true">
        <div class="modal-dialog" style="width: 90%; max-width: none;">
            <div class="modal-content" style="min-height: 900px;">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalImagemLabel">Post</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body d-flex" style="position: relative;">
                    <div style="width: 70%; height: 800px; overflow: hidden; display: flex; align-items: center;">
                        <img id="imagemMaior" src="" alt="Imagem Postada" class="img-fluid"
                            style="width: 100%; height: auto; object-fit: cover;">
                    </div>

                    <div style="border-left: 2px solid #ccc; height: 800px; margin-left: 10px;"></div>

                    <div id="comentarios" class="ms-4"
                        style="width: 25%; max-height: 800px; display: flex; flex-direction: column;">
                        <div id="descricaoPost" style="margin-bottom: 15px; font-weight: bold;"></div>
                        <div class="d-flex justify-content-between align-items-center">
                            <h5>Comentários:</h5>
                            <button type="button" class="btn btn-danger" id="btnDeletarPost"
                                style="display: none; height: 38px; margin-top: -5px;">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                        <hr style="margin: 10px 0;">
                        <div id="listaComentarios" style="flex-grow: 1; overflow-y: auto;"></div>
                        <textarea id="novoComentario" class="form-control mt-2" rows="2"
                            placeholder="Adicione um comentário" style="width: 100%; margin: 0 10px;"></textarea>
                        <button type="button" class="btn btn-primary mt-2" id="btnAdicionarComentario"
                            style="width: 100%; margin: 0 10px;">Adicionar Comentário</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Criar Publicação -->
    <div class="modal fade" id="modalPubli" tabindex="-1" aria-labelledby="modalPubliLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalPubliLabel" style="font-size: 1.5rem;">Criar publicação</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="publiForm" method="POST" enctype="multipart/form-data">
                        <div class="mb-4">
                            <label for="imagem" class="form-label" style="font-size: 1.2rem;">Carregar Imagem *</label>
                            <input type="file" class="form-control form-control-lg" name="imagem" id="imagem"
                                accept="image/*" onchange="previewImage(event); checkButtonState();" />
                            <div class="mt-2" style="font-size: 0.9rem; color: gray;">
                                (Apenas arquivos de imagem, até 5MB)
                            </div>
                            <div class="d-flex justify-content-center mt-3">
                                <img id="imagePreview" src="" alt="Preview" class="img-fluid"
                                    style="display: none; width: 600px; height: 400px; object-fit: contain;" />
                            </div>
                        </div>
                        <div class="mb-4">
                            <label for="conteudo" class="form-label" style="font-size: 1.2rem;">Descrição da postagem
                                *</label>
                            <textarea class="form-control form-control-lg" name="conteudo" id="conteudo" rows="3"
                                placeholder="Escreva sua publicação" oninput="checkButtonState();"></textarea>
                        </div>
                        <div class="d-flex justify-content-center">
                            <button type="submit" class="btn btn-primary btn-lg" id="btnPublicar" style="margin: 15px;"
                                disabled>Publicar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de edição de comentário -->
    <div class="modal fade" id="modalEditarComentario" tabindex="-1" aria-labelledby="modalEditarComentarioLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalEditarComentarioLabel">Editar Comentário</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <textarea class="form-control" id="novoConteudoComentario" rows="4"></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="salvarEdicaoComentario">Salvar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de confirmação de exclusão -->
    <div class="modal fade" id="modalConfirmacaoDelete" tabindex="-1" aria-labelledby="modalConfirmacaoDeleteLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalConfirmacaoDeleteLabel">Confirmar Exclusão</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Tem certeza de que deseja deletar o post?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Não</button>
                    <button type="button" class="btn btn-danger" id="confirmarDeletar">Sim</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function previewImage(event) {
            const input = event.target;
            const imagePreview = document.getElementById('imagePreview');
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    imagePreview.src = e.target.result;
                    imagePreview.style.display = 'block';
                };
                reader.readAsDataURL(input.files[0]);
            }
        }

        function checkButtonState() {
            const imagemInput = document.getElementById('imagem').files.length > 0;
            const conteudoInput = document.getElementById('conteudo').value.trim().length > 0;
            const btnPublicar = document.getElementById('btnPublicar');
            btnPublicar.disabled = !(imagemInput && conteudoInput);
        }

        function curtirPost(postId) {
            const url = 'serviços/posts/curtir_post.php';

            const xhr = new XMLHttpRequest();
            xhr.open('POST', url, true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onload = function () {
                if (xhr.status === 200) {
                    const novaCurtida = xhr.responseText;
                    document.getElementById('curtidas_' + postId).innerText = novaCurtida + ' curtidas';
                }
            };
            xhr.send('postId=' + postId);
        }

        function openPostModal(postId, imageUrl, description) {
            const modalImage = document.getElementById('imagemMaior');
            const modalDescription = document.getElementById('descricaoPost');
            const modalComments = document.getElementById('listaComentarios');

            modalImage.src = imageUrl;
            modalDescription.textContent = description;

            modalComments.innerHTML = '';

            fetch('serviços/comentarios/carregar_comentarios.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `postId=${postId}`
            })
                .then(response => response.text())
                .then(html => {
                    modalComments.innerHTML = html;
                })
                .catch(error => {
                    console.error('Erro ao carregar comentários:', error);
                    modalComments.innerHTML = '<p>Erro ao carregar comentários.</p>';
                });

            const addCommentButton = document.getElementById('btnAdicionarComentario');
            addCommentButton.onclick = function () {
                addComment(postId);
            };

            const modal = new bootstrap.Modal(document.getElementById('modalImagem'));
            modal.show();
        }

        function adicionarComentario(event, postId) {
            event.preventDefault();

            const form = document.getElementById('form_' + postId);
            const formData = new FormData(form);

            fetch('serviços/comentarios/adicionar_comentario.php', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const commentSection = document.getElementById('post_' + postId).querySelector('.existing-comments');
                        const novoComentario = `<p><strong>Você:</strong> ${formData.get('content')}</p>`;
                        commentSection.innerHTML += novoComentario;

                        location.reload();
                        form.querySelector('textarea').value = '';
                    } else {
                        alert('Erro ao adicionar comentário.');
                    }
                })
                .catch(error => console.error('Erro:', error));
        }

        function abrirModalEditarComentario(comentarioId, conteudoAtual) {
            const modalEditarComentario = new bootstrap.Modal(document.getElementById('modalEditarComentario'));
            modalEditarComentario.show();

            document.getElementById('novoConteudoComentario').value = conteudoAtual;

            document.getElementById('salvarEdicaoComentario').onclick = function () {
                const novoConteudo = document.getElementById('novoConteudoComentario').value.trim();

                if (novoConteudo) {
                    fetch('serviços/comentarios/editar_comentario.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: `comentario_id=${comentarioId}&novo_texto=${encodeURIComponent(novoConteudo)}`
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                alert('Comentário atualizado com sucesso!');
                                modalEditarComentario.hide();
                                location.reload();
                            } else {
                                alert(data.message || 'Erro ao atualizar o comentário.');
                            }
                        })
                        .catch(error => console.error('Erro ao atualizar o comentário:', error));
                } else {
                    alert('O conteúdo do comentário não pode estar vazio.');
                }
            };
        }

        function abrirModalDeletarComentario(comentarioId) {
            const modalConfirmacaoDelete = new bootstrap.Modal(document.getElementById('modalConfirmacaoDelete'));
            modalConfirmacaoDelete.show();

            document.getElementById('confirmarDeletar').onclick = function () {
                fetch('serviços/comentarios/deletar_comentario.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `comentario_id=${comentarioId}`
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            location.reload();
                        } else {
                            alert(data.message || 'Erro ao deletar o comentário.');
                        }
                    })
                    .catch(error => console.error('Erro ao deletar comentário:', error));
            };
        }

        function addComment(postId) {
            const commentText = document.getElementById('novoComentario').value.trim();
            if (commentText) {
                fetch('serviços/comentarios/adicionar_comentario.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `post_id=${postId}&content=${encodeURIComponent(commentText)}`
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            document.getElementById('novoComentario').value = '';
                            openPostModal(postId, data.image, data.description);
                        } else {
                            alert('Erro ao adicionar comentário.');
                        }
                    })
                    .catch(error => {
                        console.error('Erro ao adicionar comentário:', error);
                        alert('Erro ao adicionar comentário.');
                    });
            }
        }
    </script>

    <script src="bootstrap-5.3.3-dist/bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>