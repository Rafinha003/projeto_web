<?php
include_once 'config.php';

$db = new Database();
$conn = $db->getConnection();

session_start();

$usuarioLogado = json_decode($_SESSION['usuario_logado']);
$usuario_id = $usuarioLogado->id;

$perfilExterno = false;
$usuarioPerfil = $usuarioLogado;

if (isset($_GET['id'])) {
    $perfilExterno = true;
    $idPerfil = $_GET['id'];

    $sql = "SELECT * FROM usuarios WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id', $idPerfil);
    $stmt->execute();
    $usuarioPerfil = $stmt->fetch(PDO::FETCH_OBJ);
}

// Buscar posts do usuário (seja logado ou outro perfil)
$sql = "SELECT * FROM posts WHERE usuario_id = :usuario_id";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':usuario_id', $usuarioPerfil->id);
$stmt->execute();
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
$postCount = count($posts);

echo "<script>var posts = " . json_encode($posts) . ";</script>";
echo "<script>var postCount = $postCount;</script>";
echo "<script>var perfilExterno = " . ($perfilExterno ? 'true' : 'false') . ";</script>";
echo "<script>var usuarioPerfil = " . json_encode($usuarioPerfil) . ";</script>";
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil</title>
    <link rel="stylesheet" href="bootstrap-5.3.3-dist/bootstrap-5.3.3-dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="bootstrap-icons-1.11.3/font/bootstrap-icons.min.css">
    <style>
        .comentario {
            margin-bottom: 10px;
        }

        .comentario p {
            margin: 0;
        }
    </style>
</head>

<body>

    <?php include('barra-lateral.php'); ?>

    <!-- CONTEUDO DO PERFIL -->
    <div class="container profile-content">
        <div class="profile-header text-center">
            <img src="<?= $usuarioLogado->imagem_perfil ? $usuarioLogado->imagem_perfil : 'https://via.placeholder.com/150' ?>"
                alt="Foto do Perfil" style="box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);">
            <h2 id="nomeUsuario">Nome</h2>
            <p id="bioUsuario">Bio do usuário.</p>

            <div class="profile-stats">
                <div>
                    <span id="numPublicacoes">0</span>
                    <p>Publicações</p>
                </div>
                <div>
                    <span>1.2k</span>
                    <p>Seguidores</p>
                </div>
                <div>
                    <span>300</span>
                    <p>Seguindo</p>
                </div>
            </div>

            <button id="btnEditarPerfil" class="btn btn-outline-primary edit-profile-btn" data-bs-toggle="modal"
                data-bs-target="#editarPerfilModal">Editar Perfil</button>
        </div>

        <div class="container mt-5">
            <h2>Galeria de Postagens</h2>
            <div class="row post-gallery" id="postGallery"></div>
        </div>

        <!-- Modal Editar Perfil -->
        <div class="modal fade" id="editarPerfilModal" tabindex="-1" aria-labelledby="editarPerfilModalLabel"
            aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editarPerfilModalLabel">Editar Perfil</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="editarPerfilForm">
                            <div class="mb-3">
                                <label for="nome" class="form-label">Nome</label>
                                <input type="text" class="form-control" id="nome" value="">
                            </div>
                            <div class="mb-3">
                                <label for="bio" class="form-label">Bio</label>
                                <textarea class="form-control" id="bio"></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="imagemPerfil" class="form-label">Imagem de Perfil</label>
                                <input type="file" class="form-control" id="imagemPerfil" accept="image/*">
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="button" class="btn btn-primary" id="salvarAlteracoes">Salvar alterações</button>
                    </div>
                </div>
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
            let usuarioLogado;

            document.addEventListener('DOMContentLoaded', function () {
                usuarioLogado = JSON.parse(sessionStorage.getItem('usuario_logado'));
                const btnEditarPerfil = document.getElementById('btnEditarPerfil');
                if (perfilExterno && btnEditarPerfil) {
                    btnEditarPerfil.style.display = 'none';
                }

                if (typeof usuarioPerfil !== 'undefined' && perfilExterno) {
                    atualizarPerfilNaPagina(usuarioPerfil);

                    if (typeof postCount !== 'undefined') {
                        document.getElementById('numPublicacoes').textContent = postCount;
                    }

                    if (typeof posts !== 'undefined' && posts.length > 0) {
                        carregarGaleriaDePosts(posts);
                    }
                } else if (usuarioLogado) {
                    atualizarPerfilNaPagina(usuarioLogado);

                    if (typeof postCount !== 'undefined') {
                        document.getElementById('numPublicacoes').textContent = postCount;
                    }

                    if (typeof posts !== 'undefined' && posts.length > 0) {
                        carregarGaleriaDePosts(posts);
                    }
                }

                configurarEventos();
            });

            function atualizarPerfilNaPagina(usuario) {
                document.getElementById('nomeUsuario').textContent = usuario.nome || 'Usuário sem nome';
                document.getElementById('bioUsuario').textContent = usuario.bio || 'Nenhuma bio disponível.';
                if (usuario.imagem_perfil) {
                    document.querySelector('.profile-header img').src = usuario.imagem_perfil;
                } else {
                    document.querySelector('.profile-header img').src = 'default-profile.png';
                }
            }

            function configurarEventos() {
                if (!perfilExterno) {
                    document.getElementById('salvarAlteracoes').addEventListener('click', function () {
                        salvarAlteracoesPerfil();
                    });
                } else {
                    document.getElementById('btnEditarPerfil').style.display = 'none';
                }
            }

            function salvarAlteracoesPerfil() {
                const nome = document.getElementById('nome').value;
                const bio = document.getElementById('bio').value;
                const imagemPerfil = document.getElementById('imagemPerfil').files[0];

                const formData = new FormData();
                formData.append('nome', nome);
                formData.append('bio', bio);
                if (imagemPerfil) {
                    formData.append('imagem_perfil', imagemPerfil);
                }

                fetch('serviços/perfil/atualizar_perfil.php', {
                    method: 'POST',
                    body: formData
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            usuarioLogado.nome = nome;
                            usuarioLogado.bio = bio;

                            if (imagemPerfil) {
                                const reader = new FileReader();
                                reader.onloadend = function () {
                                    usuarioLogado.imagem_perfil = reader.result;
                                    sessionStorage.setItem('usuario_logado', JSON.stringify(usuarioLogado));
                                    atualizarPerfilNaPagina(usuarioLogado);
                                };
                                reader.readAsDataURL(imagemPerfil);
                            } else {
                                sessionStorage.setItem('usuario_logado', JSON.stringify(usuarioLogado));
                                atualizarPerfilNaPagina(usuarioLogado);
                            }

                            location.reload();
                        } else {
                            alert('Erro ao salvar as alterações');
                        }
                    })
                    .catch(error => console.error('Erro ao atualizar perfil:', error));
            }

            function carregarGaleriaDePosts(posts) {
                const postGallery = document.getElementById('postGallery');
                posts.forEach(post => {
                    const col = document.createElement('div');
                    col.className = 'col-4 mb-4';
                    col.innerHTML = `
            <img src="${post.imagem}" alt="Post" class="img-fluid" 
            onclick="openModal(${post.id}, '${post.imagem}', '${post.descricao}', ${post.usuario_id})" 
            style="min-width: 400px; min-height: 400px; object-fit: cover; cursor: pointer;">`;
                    postGallery.appendChild(col);
                });
            }

            function openModal(postId, imagem, conteudo, postUsuarioId) {
                const modalImagem = new bootstrap.Modal(document.getElementById('modalImagem'));
                const imagemMaior = document.getElementById('imagemMaior');
                const listaComentarios = document.getElementById('listaComentarios');
                const novoComentario = document.getElementById('novoComentario');
                const descricaoPost = document.getElementById('descricaoPost');
                const btnDeletarPost = document.getElementById('btnDeletarPost');

                imagemMaior.src = imagem;
                descricaoPost.innerHTML = `<p>${conteudo}</p>`;
                listaComentarios.innerHTML = '';

                fetch('serviços/comentarios/carregar_comentarios.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `postId=${postId}`
                })
                    .then(response => response.text())
                    .then(data => {
                        listaComentarios.innerHTML = data;
                    })
                    .catch(error => console.error('Erro ao carregar comentários:', error));

                modalImagem.show();

                btnDeletarPost.style.display = usuarioLogado.id === postUsuarioId ? 'block' : 'none';

                configurarModalEventos(postId, imagem, conteudo, postUsuarioId);
            }

            function configurarModalEventos(postId, imagem, conteudo, postUsuarioId) {
                document.getElementById('btnAdicionarComentario').onclick = function () {
                    adicionarComentario(postId, imagem, conteudo, postUsuarioId);
                };

                document.getElementById('btnDeletarPost').onclick = function () {
                    confirmarDeletarPost(postId);
                };
            }

            function adicionarComentario(postId, imagem, conteudo, postUsuarioId) {
                const comentario = document.getElementById('novoComentario').value.trim();
                if (comentario) {
                    fetch('serviços/comentarios/adicionar_comentario.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: `post_id=${postId}&content=${encodeURIComponent(comentario)}`
                    })
                        .then(response => {
                            if (response.ok) {
                                document.getElementById('novoComentario').value = '';
                                location.reload();
                                openModal(postId, imagem, conteudo, postUsuarioId);
                            } else {
                                alert('Erro ao adicionar comentário.');
                            }
                        })
                        .catch(error => console.error('Erro ao adicionar comentário:', error));
                }
            }

            function confirmarDeletarPost(postId) {
                const modalConfirmacaoDelete = new bootstrap.Modal(document.getElementById('modalConfirmacaoDelete'));
                modalConfirmacaoDelete.show();

                document.getElementById('confirmarDeletar').onclick = function () {
                    deletarPost(postId);
                };
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

            function deletarPost(postId) {
                fetch('serviços/posts/deletar_post.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `post_id=${postId}`
                })
                    .then(response => {
                        if (response.ok) {
                            location.reload();
                        } else {
                            alert('Erro ao deletar o post.');
                        }
                    })
                    .catch(error => console.error('Erro ao deletar o post:', error));
            }

        </script>
    </div>

    <script src="bootstrap-5.3.3-dist/bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>