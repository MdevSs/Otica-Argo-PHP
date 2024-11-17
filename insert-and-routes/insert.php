<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');

// Responde a requisições OPTIONS para CORS
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

include("dbConexao.php");

// Agora que eu troquei pra formData, não precisa de php://input
$oDados = $_POST;

// Checando se "Tabela" existe
if (isset($oDados['TABELA']) && !empty($oDados['TABELA'])) {
    $tblNome = $oDados['TABELA'];
    unset($oDados['TABELA']); 

    // Checando se a imagem existe
    if (isset($_FILES['PRDIMAGE']) && $_FILES['PRDIMAGE']['error'] === UPLOAD_ERR_OK) {
        $oImage = $_FILES['PRDIMAGE'];
        echo insertData($tblNome, $oDados, $oImage);
    } else {
        echo insertData($tblNome, $oDados); 
    }
} else {
    echo json_encode(["status" => "Erro crítico!", "message" => "Sem nome de tabela no array!", $oDados]);
}

// Insert reusable function
function insertData($tblNome, $allData, $oPhoto = null, $oCon = null) {
    if ($oCon === null) {
        $oCon = dbConexao();
        if (!$oCon) {
            echo json_encode(["status" => "Erro crítico", "message" => "Conexão com o banco de dados falhou!"]);
            exit;
        }
    }

    // valores de colunas e chaves do $allData
    $nColunas = implode(", ", array_keys($allData));
    $nValores = "'" . implode("', '", array_map([$oCon, 'real_escape_string'], array_values($allData))) . "'";

    $oQuery = "INSERT INTO $tblNome ($nColunas) VALUES ($nValores)";

    if ($oCon->query($oQuery) === TRUE) {
        $recordID = $oCon->insert_id;

        // Checando se uma imagem foi enviada
        if ($oPhoto !== null) {
            $uploadDir = 'uploads/';

            // Gerando o nome final do arquivo
            $fileExtension = pathinfo($oPhoto['name'], PATHINFO_EXTENSION);
            $imgNome = $recordID . '-' . pathinfo($oPhoto['name'], PATHINFO_FILENAME) . '.' . $fileExtension;
            $imgRota = $uploadDir . $imgNome;

            // Tentando salvar a imagem no servidor
            if (!move_uploaded_file($oPhoto['tmp_name'], $imgRota)) {
                echo json_encode(["status" => "Erro", "message" => "Falhou em salvar a imagem!"]);
                exit;
            }

            // Update para corrigir o nome da imagem no banco
            $updateSql = "UPDATE $tblNome SET PRDIMAGE = '" . $oCon->real_escape_string($imgRota) . "' WHERE PRDID = $recordID";
            if ($oCon->query($updateSql) !== TRUE) {
                echo json_encode(["status" => "Erro", "message" => "Falhou em atualizar o nome do arquivo no banco: " . $oCon->error]);
                return;
            } else {
                $result = json_encode([
                    "status" => "Sucesso",
                    "message" => "Dados e imagens salvos com sucesso!",
                    "ID da consulta: " => $recordID,
                    "Rota da imagem: " => $imgRota
                ]);
            }
        } else {
            $result = json_encode([
                "status" => "success",
                "message" => "Data uploaded successfully",
                "ID da consulta" => $recordID,
                "Query" => $oQuery
            ]);
        }
    } else {
        $result = json_encode(["status" => "error", "message" => $oCon->error]);
    }

    if ($oCon !== null) {
        $oCon->close();
    }

    echo $result;
}
?>