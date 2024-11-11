<?php
session_start();
if (isset($_POST['txtNome']) && isset($_POST['txtSenha'])) {
    require 'conexao.php';

    // Obter dados do formulário
    $nome = $_POST['txtNome'];
    $senha = $_POST['txtSenha'];

    // Preparar a consulta para evitar SQL Injection
    $stmt = $oCon->prepare("
        SELECT USRID, USRNOME, USRNIVELACESSO 
        FROM usuarios
        WHERE USRNOME = ? AND USRSENHA = ?
    ");

    // Verificar se a preparação foi bem-sucedida
    if ($stmt === false) {
        die("Erro na preparação da consulta: " . $oCon->error);
    }

    // Executar a consulta
    $stmt->bind_param('ss', $nome, $senha);
    $stmt->execute();
    $result = $stmt->get_result();

    // Verificar se o usuário foi encontrado
    if ($oRow = $result->fetch_assoc()) {
        $_SESSION['userId'] = $oRow['USRID'];
        $_SESSION['USRNOME'] = $oRow['USRNOME'];
        $_SESSION['USRNIVELACESSO'] = $oRow['USRNIVELACESSO'];

        // Redireciona para a página index.php após login bem-sucedido
        header('Location: index.htm');
        exit();
    } else {
        // Redireciona para o cadastro se o usuário não for encontrado
        header('Location: cadastro.php');
    }

    // Fechar a conexão
    $stmt->close();
    mysqli_close($oCon);
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
</head>
<body>
<form action="" method="POST">
    <label>
        <span>Nome</span>
        <input type="text" name="txtNome" required>
    </label>

    <label>
        <span>Senha</span>
        <input type="password" name="txtSenha" required>
    </label>

    <button type="submit"><strong>Validar</strong></button>
</form>
</body>
</html>
