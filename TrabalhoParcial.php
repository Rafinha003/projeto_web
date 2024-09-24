<?php

class Postagem {
    protected $postagens; // Array de postagens, cada uma com comentário e link para compartilhar

    // Construtor da classe Postagem
    public function __construct() {
        $this->postagens = [];
    }

    // Adiciona uma nova postagem com seu comentário e link para compartilhar
    public function adicionarPostagem($conteudoPostagem, $comentario, $compartilhar) {
        $this->postagens[] = [
            'conteudo' => $conteudoPostagem,
            'comentario' => $comentario,
            'compartilhar' => $compartilhar
        ];
    }

    // Retorna todas as postagens
    public function getPostagens() {
        return $this->postagens;
    }

    // Exibe o link para compartilhar todas as postagens
    public function share() {
        foreach ($this->postagens as $indice => $postagem) {
            $indiceBase1 = $indice + 1; // Ajusta o índice para começar a partir de 1
            echo "Postagem $indiceBase1: {$postagem['conteudo']} <br>";
            echo "Comentário: {$postagem['comentario']} <br>";
            echo "Link para compartilhar: {$postagem['compartilhar']} <br><br>";
        }
    }

    // Adiciona um comentário a uma postagem específica
    public function adicionarComentario($indiceBase1, $comentario) {
        $indice = $indiceBase1 - 1; // Ajusta o índice para 0 baseado em array
        if (isset($this->postagens[$indice])) {
            $this->postagens[$indice]['comentario'] = $comentario;
        } else {
            echo "Postagem $indiceBase1 não encontrada <br>";
        }
    }
}

class Usuario {
    public $nome;
    public $qntPosts;
    private $postagem; // Instância da classe Postagem

    // Construtor da classe Usuario
    public function __construct($nome, $qntPosts) {
        $this->nome = $nome;
        $this->qntPosts = $qntPosts;
        
        // Cria uma instância da classe Postagem
        $this->postagem = new Postagem();
    }

    public function sharePost() {
        $this->postagem->share();
    }

    public function verPostagens() {
        return $this->postagem->getPostagens();
    }

    // Métodos públicos para interação com a postagem
    public function fazerPostagem($conteudoPostagem, $comentario, $compartilhar) {
        $this->postagem->adicionarPostagem($conteudoPostagem, $comentario, $compartilhar);
        echo "Postagem adicionada: $conteudoPostagem <br>";
    }

    public function fazerComentario($indiceBase1, $comentario) {
        $this->postagem->adicionarComentario($indiceBase1, $comentario);
        echo "Comentário atualizado para a postagem $indiceBase1: $comentario <br>";
    }

    // Métodos protegidos que podem ser chamados dentro da própria classe
    protected function deletePost() {
        echo "Postagem deletada <br>";
    }

    protected function updatePost() {
        echo "Postagem atualizada <br>";
    }

    public function comentario() {
        echo "Seu comentário aqui <br>";
    }

    // Métodos públicos para interagir com os métodos protegidos
    public function performDelete() {
        $this->deletePost();
    }

    public function performUpdate() {
        $this->updatePost();
    }
}

// Exemplo de uso
$usuario = new Usuario("João", 10);

$usuario->fazerPostagem("Minha primeira postagem", "Comentário inicial", "http://linkparacompartilhar.com");
$usuario->fazerPostagem("Minha segunda postagem", "Outro comentário", "http://outrolink.com");
echo "<br>";

$usuario->sharePost(); // Exemplo de chamada de método para compartilhar todas as postagens
echo "Postagens: <br>";
foreach ($usuario->verPostagens() as $indice => $postagem) {
    $indiceBase1 = $indice + 1; // Ajusta o índice para começar a partir de 1
    echo "Postagem $indiceBase1: {$postagem['conteudo']} <br>";
    echo "Comentário: {$postagem['comentario']} <br>";
    echo "Link para compartilhar: {$postagem['compartilhar']} <br><br>";
}

// Adicionar um novo comentário a uma postagem existente
$usuario->fazerComentario(1, "Comentário atualizado"); // Atualiza o comentário da primeira postagem

// Chamar métodos para deletar e atualizar postagem
$usuario->performUpdate(); // Exemplo de chamada de método para atualizar postagem
echo "-------------------------------------------";
echo "<br>";

$usuario->sharePost(); // Exemplo de chamada de método para compartilhar todas as postagens

?>
