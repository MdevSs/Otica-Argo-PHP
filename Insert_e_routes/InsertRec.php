<?php
// INSERT Function está nesta página!

include("dbConexao.php");

$json_Decode = file_get_contents('php://input');
$oDados = json_decode($json_Decode, true);

//Testo se o campo com o nome da tabela tem valor
//Se tiver, apague ele do vetor com os valores e colunas da tabela
if($oDados['TABELA'] != null) {
    $tblNome = $oDados['TABELA'];
    unset($oDados['TABELA']);
    echo insertData($tblNome, $oDados);
} else {
    echo json_encode(["status" => "error", "message" => "Sem nome de tabela maluco"]);
}

// Insert reutilizável
function insertData($tblNome, $allData, $oCon = null) {
    if ($oCon === null) {
        $oCon = dbConexao();
    }

    // Colunas e valores de $allData
    $nColunas = implode(", ", array_keys($allData));
    $nValores = "'" . implode("', '", array_values($allData)) . "'";

    $oQuery = "INSERT INTO $tblNome ($nColunas) VALUES ($nValores)";

    if ($oCon->query($oQuery) === TRUE) {
        //Pegando o ID da consulta para casos especiais, e retornando ele ao JS.
        $result = json_encode(["status" => "Sucesso", "ID" => $oCon->insert_id]);
    } else {
        $result = json_encode(["status" => "ERRO!", "Mensagem" => $oCon->error]);
    }

    if ($oCon !== null) {
        $oCon->close();
    }

    return $result;
}
?>