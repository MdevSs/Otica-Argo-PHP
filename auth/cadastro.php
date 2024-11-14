<?php
// Configurações de CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');

// Responde a requisições OPTIONS para CORS
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Verifica se o método da requisição é POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Lê e decodifica o JSON do corpo da requisição
    $inputData = json_decode(file_get_contents("php://input"), true);

    // Verifica se os dados necessários estão presentes
    if (isset($inputData['txtNome']) && isset($inputData['txtCPF']) && isset($inputData['txtEmail']) && isset($inputData['txtSenha']) && isset($inputData['dtNasc'])) {
        // Conecta ao banco de dados
        $oServidor = "localhost";
        $oUsuario = "root";
        $oSenha = "";
        $oBanco = "prj2dsb";

        $oCon = new mysqli($oServidor, $oUsuario, $oSenha, $oBanco);

        // Verifica a conexão
        if ($oCon->connect_error) {
            echo json_encode(["status" => "erro", "mensagem" => "Falha na conexão: " . $oCon->connect_error]);
            exit();
        }

        // Dados do formulário
        $nome = $inputData['txtNome'];
        $cpf = $inputData['txtCPF'];
        $email = $inputData['txtEmail'];
        $nasc = $inputData['dtNasc'];
        $senha = md5($inputData['txtSenha']); // Criptografa a senha com MD5

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

        // Executa a consulta
        $stmt->bind_param('sssss', $nome, $cpf, $email, $senha, $nasc);
        if ($stmt->execute()) {
            // Seleciona todos os dados do usuário cadastrado
            $selectStmt = $oCon->prepare("
                SELECT `USRID`, `USRNOME`, `USREMAIL`, `USRCPF`, `USRDTNASC`, `USRNIVELACESSO`, `USRBLOQUEADO` 
                FROM `usuarios` 
                WHERE USREMAIL = ?
            ");
            $selectStmt->bind_param('s', $email);
            $selectStmt->execute();
            $result = $selectStmt->get_result();
            $userData = $result->fetch_assoc();

            echo json_encode([
                "status" => "sucesso", 
                "mensagem" => "Cadastro realizado com sucesso!",
            ]);

            $selectStmt->close();
        } else {
            echo json_encode(["status" => "erro", "mensagem" => "Erro ao cadastrar: " . $stmt->error]);
        }

        // Fecha a conexão
        $stmt->close();
        $oCon->close();
    } else {
        echo json_encode(["status" => "erro", "mensagem" => "Dados incompletos."]);
    }
    exit();
}
?>