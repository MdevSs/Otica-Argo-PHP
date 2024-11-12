<?php
if($_SERVER['REQUEST_METHOD'] === 'POST'){
header('Content-Type: application/json');

// Verificar se o formulário foi enviado
if (isset($_POST['txtNome']) && isset($_POST['txtCPF']) && isset($_POST['txtEmail']) && isset($_POST['txtSenha'])) {
    // Conecta ao banco de dados
    $oServidor = "localhost";
    $oUsuario = "DesenvolvedorB";
    $oSenha = "B1scoito!";
    $oBanco = "PRJ2DSB";


    $oCon = new mysqli($oServidor, $oUsuario, $oSenha, $oBanco);

    // Verifica a conexão
    if ($oCon->connect_error) {
        echo json_encode(["status" => "erro", "mensagem" => "Falha na conexão: " . $oCon->connect_error]);
        exit();
    }

    // Dados do formulário
    $nome = $_POST['txtNome'];
    $cpf = $_POST['txtCPF'];
    $email = $_POST['txtEmail'];
    $nasc = $_POST['dtNasc'];
    $senha = md5($_POST['txtSenha']); // Criptografa a senha com MD5

    // Prepara a consulta para inserção de dados
    $stmt = $oCon->prepare("
        INSERT INTO usuarios (USRNOME, USRCPF, USREMAIL, USRSENHA, USRDTNASC) 
        VALUES (?, ?, ?, ?, ?)
    ");

    // Verifica se a preparação foi bem-sucedida
    if ($stmt === false) {
        echo json_encode(["status" => "erro", "mensagem" => "Erro na preparação da consulta: " . $oCon->error]);
        exit();
    }

    // Executar a consulta
    $stmt->bind_param('sssss', $nome, $cpf, $email, $senha, $nasc);
    if ($stmt->execute()) {
        // Selecionar todos os dados do usuário cadastrado
        $selectStmt = $oCon->prepare("SELECT `USRID`, `USRNOME`, `USREMAIL`, `USRCPF`, `USRDTNASC`, `USRNIVELACESSO`, `USRBLOQUEADO` FROM `usuarios` WHERE USREMAIL = ?");
        $selectStmt->bind_param('s', $email);
        $selectStmt->execute();
        $result = $selectStmt->get_result();
        $userData = $result->fetch_assoc();

        echo json_encode([
            "status" => "sucesso", 
            "mensagem" => "Cadastro realizado com sucesso!",
            "dados_usuario" => $userData
        ]);

        $selectStmt->close();
    } else {
        echo json_encode(["status" => "erro", "mensagem" => "Erro ao cadastrar: " . $stmt->error]);
    }

    // Fechar a conexão
    $stmt->close();
    $oCon->close();
} else {
    echo json_encode(["status" => "erro", "mensagem" => "Dados incompletos."]);
}
exit();
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