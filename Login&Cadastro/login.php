<?php
session_start();
if (isset($_POST['txtNome']) && isset($_POST['txtSenha'])) {
 
    // Conectar ao banco de dados
    $oServidor = "localhost";
    $oUsuario = "DesenvolvedorB";
    $oSenha = "B1scoito!";
    $oBanco = "PRJ2DSB";

    $oCon = new mysqli($oServidor, $oUsuario, $oSenha, $oBanco);

    // Verificar a conexão
    if (!$oCon) {
        die("Falha na conexão: " . mysqli_connect_error());
    }

    // Obter dados do formulário
    $nome =  $_POST['txtNome'];
    $senha =  $_POST['txtSenha'];

    // Preparar a consulta para evitar SQL Injection
    $stmt = $oCon->prepare("
        SELECT USRNOME, USRNIVELACESSO 
        FROM usuarios
        WHERE USRNOME = ?
        AND USRSENHA = ?
    ");

    // Verificar se a preparação foi bem-sucedida
    if ($stmt === false) {
        die("Erro na preparação da consulta: " . $oCon->error);
    }

    // Executar a consulta
    $stmt->bind_param('ss', $nome, $senha); // 'ss' corresponde a dois parâmetros de string
    $stmt->execute();
    $result = $stmt->get_result();

    // Verificar se o usuário foi encontrado
    if ($oRow = $result->fetch_assoc()) {
        $_SESSION['USRNOME'] = $oRow['USRNOME'];
        $_SESSION['USRNIVELACESSO '] = $oRow['USRNIVELACESSO '];

        // Verificar o nível do usuário
        if ($oRow['USRNIVELACESSO'] == 1 || $oRow['USRNIVELACESSO'] == 2) { // Supondo que o nível 1 é para acesso ao menu funcionario
            header('Location: caminho_menufuncionario');
            exit();
        } else { // Se o nivel for qualquer um diferente de 1 ou 2
            header('Location: caminho_menucliente');
        }
    } else { // Se o usuario nao for encontrado, ele vai ser direcionado ao cadastro
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
                <span>Nome ou Email</span>
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
