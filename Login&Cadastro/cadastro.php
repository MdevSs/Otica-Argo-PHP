<?php
// Verificar se o formulário foi enviado
if (isset($_POST['txtNome']) && isset($_POST['txtCPF']) && isset($_POST['txtEmail']) && isset($_POST['txtSenha'])) {
 

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
    $cpf =   $_POST['txtCPF'];
    $email = $_POST['txtEmail'];
    $nasc = $_POST['dtNasc'];
    $senha = $_POST['txtSenha'];

    // Preparar a consulta para inserção de dados
    $stmt = $oCon->prepare("
        INSERT INTO usuarios (USRNOME, USRCPF, USREMAIL, USRSENHA, USRDTNASC) 
        VALUES (?, ?, ?, ?, ?)
    ");

    // Verificar se a preparação foi bem-sucedida
    if ($stmt === false) {
        die("Erro na preparação da consulta: " . $oCon->error);
    }

    // Executar a consulta
    $stmt->bind_param('sssss', $nome, $cpf, $email, $senha, $nasc);
    if ($stmt->execute()) {
        echo "Cadastro realizado com sucesso!";
    } else {
        echo "Erro ao cadastrar: " . $stmt->error;
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
    <title>Cadastro</title>
</head>
<body>
    <h2>Cadastro de Usuário</h2>
    <form method="POST" action="">
        <label>Nome</label>
        <input type="text" name="txtNome" required>
        <br><br>
        <label>CPF</label>
        <input type="number" name="txtCPF" required>
        <br><br>
        <label>Email</label>
        <input type="email" name="txtEmail" required>
        <br><br>
        <input type="date" name="dtNasc" required> 
        <br><br>
        <label>Senha</label>
        <input type="password" name="txtSenha" required>
        <br><br>

        <button type="submit">Cadastrar</button>
    </form>
</body>
</html>