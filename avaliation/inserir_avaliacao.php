<?php
session_start();

// Verifica se tem login, se não, manda login.php
if (!isset($_SESSION['userId'])) {
    header('Location: login.php');
    exit();
}

require 'conexao.php'; 

// Verifica se o formulário (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $produto_id = $_POST['produtoId']; 
    $nota = $_POST['nota']; 
    $comentario = $_POST['comentario']; 
    $usuario_id = $_SESSION['userId']; 

    // Verifica se o usuário já avaliou 
    $stmt_check = $oCon->prepare("SELECT * FROM avaliacoes WHERE AVACLIENTE = ? AND AVAPRODUTO = ?");
    $stmt_check->bind_param("ii", $usuario_id, $produto_id);
    $stmt_check->execute();
    $result = $stmt_check->get_result();

    // Se já houver avaliação, atualiza a nota e o comentário, caso contrário, insere uma nova avaliação
    if ($result->num_rows > 0) {
        $stmt_update = $oCon->prepare("
            UPDATE avaliacoes 
            SET AVANOTA = ?, AVACOMENTARIOS = ? 
            WHERE AVACLIENTE = ? AND AVAPRODUTO = ?
        ");
        $stmt_update->bind_param("isii", $nota, $comentario, $usuario_id, $produto_id);
        $stmt_update->execute(); // Atualiza a avaliação
        $stmt_update->close();
    } else {
        $stmt_insert = $oCon->prepare("
            INSERT INTO avaliacoes (AVACLIENTE, AVAPRODUTO, AVANOTA, AVACOMENTARIOS) 
            VALUES (?, ?, ?, ?)
        ");
        $stmt_insert->bind_param('iiis', $usuario_id, $produto_id, $nota, $comentario);
        $stmt_insert->execute(); // Insere a nova avaliação
        $stmt_insert->close();
    }
    $stmt_check->close();

    // Verifica se o usuário enviou uma imagem
    if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] === UPLOAD_ERR_OK) {
        $imagem_temp = $_FILES['imagem']['tmp_name']; 
        $imagem_nome = 'img/' . $usuario_id . '_' . $produto_id . '.jpg'; // Novo nome
        if (move_uploaded_file($imagem_temp, $imagem_nome)) {
            
        } else {
            echo "Erro ao salvar a imagem."; 
        }
    }

    mysqli_close($oCon);
    header('Location: consultar_avaliacoes.php');
}
?>
