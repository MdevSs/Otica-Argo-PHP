<?php
// UPDATE Function está nesta página!
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');

// Responde a requisições OPTIONS para CORS
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}
$oCon = mysqli_connect('localhost', 'root', '', 'prj2dsb');
// $oCon = mysqli_connect('localhost', 'DesenvolvedorB', 'B1scoito!', 'PRJ2DSB');


$json_Decode = file_get_contents('php://input');
$oDados = json_decode($json_Decode, true);

//Testo se o campo com o nome da tabela tem valor
//Se tiver, apague ele do vetor com os valores e colunas da tabela

if($oDados['CODIGO'] == null) {
    echo json_encode(["status" => "error", "message" => "campos CODIGO inexistente no objeto JSON passado"]);
}else {
    if($oDados['TABELA'] != null) {
        $tblNome = strtolower($oDados['TABELA']);
        unset($oDados['TABELA']);
        $cod = $oDados['CODIGO'];
        unset($oDados['CODIGO']);
        echo fnUpdateData($tblNome, $oDados, $cod);
    } else {
        echo json_encode(["status" => "error", "message" => "Sem nome de tabela maluco"]);
    }
}



// Insert reutilizável
function fnUpdateData($tblNome, $allData, $Cod) {
    global $oCon;

    // Colunas e valores de $allData
    // $nColunas = implode(", ", array_keys($allData));
    // $nValores = "'" . implode("', '", array_values($allData)) . "'";
    
    $oQuery = "UPDATE $tblNome SET ";
    
    foreach($allData as $index => $value){
        $oQuery .= "$index = '$value',";
    }

    $oQuery = rtrim($oQuery, ',');


    switch($tblNome)
    {
        case 'usuarios': 
            $oQuery .= "WHERE USRID = $Cod";

            break;
        
        case 'telefones': 
            $oQuery .= "WHERE TELCLIENTE = $Cod";

            break;
        
        case 'produtos': 
            $oQuery .= "WHERE PRDID = $Cod";

            break;

        case 'pedidos': 
            $oQuery .= "WHERE PEDID = $Cod";

            break;

        case 'pedido_produtos': 
            $oQuery .= "WHERE PDPPEDIDO = $Cod";
        
        break;

        case 'categorias': 
            $oQuery .= "WHERE CTGID = $Cod";
    
            break;
        
        case 'produto_categorias': 
            $oQuery .= "WHERE FK_PROD = $Cod";
        
        break;

        case 'enderecos': 
            $oQuery .= "WHERE ENDID = $Cod";
    
            break;
            
        case 'carrinho': 
            $oQuery .= "WHERE CARID = $Cod";
        
        break;

        case 'carrinho_produto': 
            $oQuery .= "WHERE CRPCARRINHO = $Cod";
        
        break;

        case 'cor': 
            $oQuery .= "WHERE CORID = $Cod";

        break;

        case 'marca': 
            $oQuery .= "WHERE MARCAID = $Cod";

        break;

        case 'suporte': 
            $oQuery .= "WHERE SUPID = $Cod";
        
        break;

        case 'formas_pagamento': 
            $oQuery .= "WHERE FRMID = $Cod";
        
        break;

        case 'movimentos': 
            $oQuery .= "WHERE MOVID = $Cod";
        
        break;
    
    }

    $oRes = mysqli_query($oCon, $oQuery);


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