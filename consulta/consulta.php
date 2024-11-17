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

//PEGA OS VALORES PASSADOS PELO CORPO DA REQUISIÇÃO
$jsonData = file_get_contents('php://input');

$oDados = json_decode($jsonData, true); 

$oCon = mysqli_connect('localhost', 'root', '', 'prj2dsb'); 
  


switch($oDados['tipo']) {
    case 'avaliacao':
        if(isset($oDados['consulta']) == true) {
            $c = $oDados['consulta'];

            if(gettype($c) != "array" || gettype($c) != "object" && $c != '' || !empty($c)) {
                $cSQL = "SELECT USRNOME 'USUARIO', PRDNOME 'PRODUTO', PRDIMAGE 'IMGPATH', AVANOTA 'NOTA', GROUP_CONCAT(AVACOMENTARIOS, ', ') 'COMENTARIOS' FROM AVALIACOES INNER JOIN USUARIOS ON AVACLIENTE = USRID INNER JOIN PRODUTOS ON AVAPRODUTO = PRDID WHERE USRNOME = $c ";
            }
        }else {
            $cSQL = "SELECT USRNOME 'USUARIO', PRDNOME 'PRODUTO', PRDIMAGE 'IMGPATH', AVANOTA 'NOTA', GROUP_CONCAT(AVACOMENTARIOS, ', ') 'COMENTARIOS' FROM AVALIACOES INNER JOIN USUARIOS ON AVACLIENTE = USRID INNER JOIN PRODUTOS ON AVAPRODUTO = PRDID WHERE 1 = 1";  
        }

        if(isset($oDados['search'])) {

            $q = $oDados['search'];
            $sql .= "AND USRNOME LIKE '%$q%'
             OR PRDNOME LIKE '%$q%' 
             OR AVANOTA LIKE '%$q%'  
             OR AVACOMENTARIOS LIKE '%$q%' 
             OR USRID LIKE '%$q%'  
             OR AVAPRODUTO LIKE '%$q%'
             GROUP BY USRNOME, PRDNOME, AVANOTA
             ";

        }

        $oRes = mysqli_query($oCon, $sql);
        
        $oRes = mysqli_fetch_all($oRes, MYSQLI_ASSOC);

        echo json_encode($oRes);
    break;

    case 'home':
        $sql1 = "SELECT PRDID 'ID', PRDNOME 'NOME', PRDVLRUNIT 'VALOR', PRDVLRDESC 'DESCONTO', PRDIMAGE 'IMGPATH' FROM produtos WHERE PRDDESC = 1";
        $oRes = mysqli_query($oCon, $sql1);
        $oQuery1 = mysqli_fetch_all($oRes, MYSQLI_ASSOC);
        
        $sql2 = "SELECT PRDID 'ID', PRDNOME 'NOME', PRDVLRUNIT 'VALOR', PRDIMAGE 'IMGPATH' FROM produtos WHERE PRDDESC = 0";
        $oRes = mysqli_query($oCon, $sql2);
        $oQuery2 = mysqli_fetch_all($oRes, MYSQLI_ASSOC);

        $oRes = [
            'desconto' => $oQuery1,
            'produtos' => $oQuery2
        ];

        echo json_encode($oRes);
        // var_dump($oRes);
    break;
        
    // case 'pesquisa':
    //     $cSQL = "SELECT * FROM PRODUTOS INNER JOIN CATEGORIAS ON CTGID = PRDCATEG WHERE '". $oDados['consulta'] ."' IN (PRDNOME, CTGNOME, PRDDESC)";
    //     // echo $cSQL;
    //     $oRes = mysqli_query($oCon, $cSQL);

    //     $oRes = mysqli_fetch_all($oRes, MYSQLI_ASSOC);

    //     echo json_encode($oRes);
    
    // break;

    case 'carrinho':
        // Codigo ancestral:
        // SELECT PRDNOME, PRDDESCRICAO, CRPVLRUNIT FROM produtos INNER JOIN carrinho_produtos ON CRPPRODUTO = PRDID INNER JOIN carrinho ON CRPCARRINHO = (SELECT CARID WHERE CARCLIENTE = 1);

        // PRECISA COLOCAR OS 'ALIAS' NOS CAMPOS sim

        //Mano, as primeiras letras eu deixei minusculas

        // no BD?

        // eu dei copiar e colar, pra ser mais rapido, são quase a mesma coisa

        if(isset($oDados['consulta']) == true) {
            $c = $oDados['consulta'];

            if(gettype($c) != "array" || gettype($c) != "object" && $c != '' || !empty($c)) {

                $cSQL = "SELECT PRDNOME 'NOME', PRDDESCRICAO, CRPVLRUNIT 'VALOR' FROM produtos INNER JOIN carrinho_produtos ON CRPPRODUTO = PRDID INNER JOIN carrinho ON CRPCARRINHO = (SELECT CARID WHERE CARCLIENTE = " . $oDados['consulta'] . ") WHERE 1=1 "; //Pra q where 1=1?

                // pra na hora daqui embaixo (desce um pouco a cam)
            }
        }else {
            // NESSA PARTE, por ser usaod mais em relatorios, eu vou colocar algumas informações add
            // como nome do usuario? Eu n sei se é necessario. Na duvida é so mais um join memo

            // é mais ilustrativo, para quem tiver vendo o relatorio conseguir relacionar os dados

            $cSQL = "SELECT USRNOME 'USUARIO', PRDNOME 'NOME', PRDDESCRICAO 'DESCRICAO', CRPVLRUNIT 'VALOR' FROM produtos INNER JOIN carrinho_produtos ON CRPPRODUTO = PRDID INNER JOIN carrinho ON CARID = CRPCARRINHO INNER JOIN usuarios ON CARCLIENTE = USRID"; //Esse é o "relatorio". Acho q era assim q tu querias certo?
            // sim Nice porra
        }
        
        if(isset($oDados['search'])) {

            $q = $oDados['search'];
            // tem que arrumar os campos daq, depois eu faço
            // tem que colocar:
            // OR _campo_ LIKE '%$q%'
            $sql .= "AND USRNOME LIKE '%$q%'
             OR PRDNOME LIKE '%$q%' 
             OR PRDESCRICAO LIKE '%$q%'  
             OR CRPVLRUNIT LIKE '%$q%' 
             OR CARID LIKE '%$q%' 
             OR CRPCARRINHO LIKE '%$q%'
             OR USRID LIKE '%$q%'" ;
            
        }

        // nao dar erro? Eu usei aquela consulta e deu erro. N tem a tabela USER
        // entendi, enfim, tem os cases ali encima agora
        //Valeu
        //$cSQL = "SELECT PRDNOME 'nome', PRDDESCRICAO 'descrição', CRPVLRUNIT 'valor' FROM produtos INNER JOIN carrinho_produtos ON CRPPRODUTO = PRDID INNER JOIN carrinho ON CRPCARRINHO = (SELECT CARID WHERE CARCLIENTE = " . $oDados['consulta'] . ")";

        $oRes = mysqli_query($oCon, $cSQL);

        $oRes = mysqli_fetch_all($oRes, MYSQLI_ASSOC);

        echo json_encode($oRes);
    break;
        
    case 'categoria':
        
        $c;

        if(isset($oDados['consulta']) == true) {
            $c = $oDados['consulta'];

            if(gettype($c) != "array" || gettype($c) != "object" && $c != '' || !empty($c)) {

                $sql = "SELECT filho.CTGID, pai.CTGNOME AS 'CATEGORIA PAI', filho.CTGNOME AS 'CATEGORIA' FROM `CATEGORIAS` as filho INNER JOIN CATEGORIAS AS pai ON pai.CTGID = filho.CTGSUBID WHERE CTGID LIKE '%$c%' OR CTGNOME LIKE '%$c%' ";

            }
        }else {

            $sql = "SELECT filho.CTGID, pai.CTGNOME AS 'CATEGORIA PAI', filho.CTGNOME AS 'CATEGORIA' FROM `CATEGORIAS` as filho INNER JOIN CATEGORIAS AS pai ON pai.CTGID = filho.CTGSUBID WHERE 1=1 ";          

        }

        if(isset($oDados['search'])) {

            $q = $oDados['search'];
            
            $sql .= "AND filho.CTGID LIKE '%$q%'
             OR pai.CTGID LIKE '%$q%' 
             OR filho.CTGNOME LIKE '%$q%' 
             OR filho.CTGSUBID LIKE '%$q%' " ;
            
        }

        echo $sql;

        $oRes = mysqli_query($oCon, $sql);
        
        $oRes = mysqli_fetch_all($oRes, MYSQLI_ASSOC);

        echo json_encode($oRes);
    break;

    case 'produto-id':
        $produtoId = intval($oDados['consulta']); // converte para inteiro para evitar essi-qui-eli injection
        $cSQL = "SELECT 
                PRDID AS 'ID', 
                PRDNOME AS 'NOME', 
                PRDDESCRICAO AS 'DESCRICAO', 
                PRDVLRUNIT AS 'VALOR', 
                PRDVLRDESC AS 'DESCONTO', 
                PRDIMAGE AS 'IMGPATH',
                PRDMARCA AS 'MARCA',
                PRDCOR AS 'COR'
                FROM produtos 
                WHERE PRDID = $produtoId";
    
        $oRes = mysqli_query($oCon, $cSQL);
    
        if (!$oRes) {
        // Erro na execução da consulta
            echo json_encode([
                "error" => true,
                "message" => "Erro ao executar a consulta.",
            ]);
            exit;
        }
    
            $oRes = mysqli_fetch_all($oRes, MYSQLI_ASSOC);
        
        if (empty($oRes)) {
            // Produto não encontrado
            echo json_encode([
                "error" => true,
                "message" => "Produto não encontrado.",
            ]);
            exit;
        }
    
        echo json_encode($oRes);
        break;
    
    case 'produto':
        
        // essa variavel aqui, que eu não sei pra que eu criei, mas foi para insano
        $c;


        // testo consulta existe
        if(isset($oDados['consulta']) == true) {
            $c = $oDados['consulta'];
            //...

            // aqui é para saber se é um array, pq tem um desse cases (filtro) ele utiliza um array
            if(gettype($c) != "array" || gettype($c) != "object" && $c != '' || !empty($c)) {


                // consulta com o WHERE do campo ['consulta']
                $sql = "SELECT DISTINCT 
                    PRDID 'ID', 
                    PRDNOME 'NOME', 
                    PRDIMAGE 'IMGPATH',
                    PRDDESCRICAO 'DESCRICAO',  
                    GROUP_CONCAT(CTGNOME SEPARATOR ', ') 'CATEGORIAS',  
                    PRDVLRUNIT 'VALOR', 
                    PRDVLRDESC AS 'VALOR DESCONTO', 
                    MARCANOME 'MARCA', 
                    CORNOME 'COR' FROM produtos 
                LEFT JOIN produto_categorias ON PRDID = FK_PROD 
                LEFT JOIN categorias ON FK_CATEG = CTGID 
                INNER JOIN cor ON CORID = PRDCOR 
                INNER JOIN marca ON MARCAID = PRDMARCA 
                WHERE PRDID = $c ";
                        // aqui '$c'
            }
        }else {
            // senao apenas a consulta sem nenhum WHERE

                $sql = "SELECT DISTINCT 
                PRDID 'ID', 
                PRDIMAGE 'IMGPATH',
                PRDNOME 'NOME', 
                PRDDESCRICAO 'DESCRICAO',  
                GROUP_CONCAT(CTGNOME SEPARATOR ', ') 'CATEGORIAS',  
                PRDVLRUNIT 'VALOR', 
                PRDVLRDESC AS 'VALOR DESCONTO', 
                MARCANOME 'MARCA', 
                CORNOME 'COR' FROM produtos 
            LEFT JOIN produto_categorias ON PRDID = FK_PROD 
            LEFT JOIN categorias ON FK_CATEG = CTGID 
            INNER JOIN cor ON CORID = PRDCOR 
            INNER JOIN marca ON MARCAID = PRDMARCA 
            WHERE 1=1 ";            

        }

        // texto se existe _search_

        if(isset($oDados['search'])) {
            
            $q = $oDados['search'];
            // concateno ao fim das consultas anteriores as condições de pesquisa
            $sql .= "AND PRDID LIKE '%$q%'
             OR PRDNOME LIKE '%$q%' 
             OR PRDDESCRICAO LIKE '%$q%' 
             OR PRDCATEG LIKE '%$q%' 
             OR PRDVLRUNIT LIKE '%$q%' 
             OR PRDVLRDESC LIKE '%$q%'
             OR PRDMARCA LIKE '%$q%' 
             OR MARCAID LIKE '%$q%' 
             OR MARCANOME LIKE '%$q%' 
             OR PRDCOR LIKE '%$q%' 
             OR CORID LIKE '%$q%' 
             OR CORNOME LIKE '%$q%'";
            
        }

        $sql .= "GROUP BY PRDID, PRDNOME, PRDDESCRICAO, PRDVLRUNIT, PRDVLRDESC, MARCANOME, CORNOME;";

        echo $sql;

        $oRes = mysqli_query($oCon, $sql);

        $oRes = mysqli_fetch_all($oRes, MYSQLI_ASSOC);

        echo json_encode($oRes);

    break;

    case 'sac':

        if(isset($oDados['consulta']) == true) {
            $c = $oDados['consulta'];

            if(gettype($c) != "array" || gettype($c) != "object" && $c != '' || !empty($c)) {

                $sql = "SELECT USRNOME, PRDNOME, SUPASSUNTO, SUPDESCR, IFNULL(SUPRESPOSTA, 'SEM RESPOSTA'), DATE_FORMAT(SUPDTABERTURA, '%d/%m/%Y'), IFNULL(DATE_FORMAT(SUPDATARESP, '%d/%m/%Y'), 'SEM RESPOSTA') FROM `SUPORTE` INNER JOIN USUARIOS ON USRID = SUPCLIENTE INNER JOIN PRODUTOS ON SUPPRODUTO = PRDID WHERE USRID = " . $c;
                //A nao, n vi q o de baixo tbm tem funcoes. So falta o group bu memo? tenho quase certeza OMAGA. Okay ent, sussa
                // na real, o GROUP BY são só para funções de grupo SUM, MIM , MAX, AVG
            }
        }else {

                $sql = "SELECT USRNOME, PRDNOME, SUPASSUNTO, SUPDESCR, IFNULL(SUPRESPOSTA, 'SEM RESPOSTA'), DATE_FORMAT(SUPDTABERTURA, '%d/%m/%Y'), IFNULL(DATE_FORMAT(SUPDATARESP, '%d/%m/%Y'), 'SEM RESPOSTA') FROM `SUPORTE` INNER JOIN USUARIOS ON USRID = SUPCLIENTE INNER JOIN PRODUTOS ON SUPPRODUTO = PRDID WHERE 1=1 ";            

        }

        if(isset($oDados['search'])) {

            $q = $oDados['search'];
            
            $sql .= "AND USRNOME LIKE '%$q%' OR PRDNOME LIKE '%$q%' OR SUPDESCR LIKE '%$q%' OR SUPRESPOSTA LIKE '%$q%' OR SUPDTABERTURA LIKE '%$q%' OR SUPDATARESP LIKE '%$q%' OR USRID LIKE '%$q%' OR SUPCLIENTE LIKE '%$q%' OR SUPPRODUTO LIKE '%$q%' OR PRDID LIKE '%$q%'";
            
        }

            $oRes = mysqli_query($oCon, $sql);
            
            $oRes = mysqli_fetch_all($oRes, MYSQLI_ASSOC);

            echo json_encode($oRes);
    break;

    case 'pedidos':
        //Codigo ancestral:
        //SELECT PDPPEDIDO, USRNOME, DATE_FORMAT(PEDDATA, '%d/%m/%Y') as 'DATA', SUM(PDPVLRUNIT) AS 'VALOR', GROUP_CONCAT(PRDNOME SEPARATOR ', ') as 'PRODUTOS' FROM pedido_produtos INNER JOIN produtos ON PDPPRODUTO = PRDID INNER JOIN pedidos ON PDPPEDIDO = PEDID INNER JOIN usuarios ON PEDCLIENTE = USRID WHERE PEDID = 1;
        if(isset($oDados['consulta']) == true) {
            $c = $oDados['consulta'];

            if(gettype($c) != "array" || gettype($c) != "object" && $c != '' || !empty($c)) {

                $sql = "SELECT PDPPEDIDO AS 'CODIGO PEDIDO', USRNOME AS 'USUARIO', DATE_FORMAT(PEDDATA, '%d/%m/%Y') as 'DATA', PEDNOTA AS 'NOTA FISCAL', SUM(PDPVLRUNIT) AS 'VALOR', GROUP_CONCAT(PRDNOME SEPARATOR ', ') as 'PRODUTOS' FROM pedido_produtos INNER JOIN produtos ON PDPPRODUTO = PRDID INNER JOIN pedidos ON PDPPEDIDO = PEDID INNER JOIN usuarios ON PEDCLIENTE = USRID WHERE PEDID = $c ";
            
            }
            else {
                                                                                                                        
                $sql = "SELECT PDPPEDIDO AS 'CODIGO PEDIDO', USRNOME AS 'USUARIO', DATE_FORMAT(PEDDATA, '%d/%m/%Y') as 'DATA', PRDNOTA AS 'NOTA FISCAL', SUM(PDPVLRUNIT) AS 'VALOR', GROUP_CONCAT(PRDNOME SEPARATOR ', ') as 'PRODUTOS' FROM pedido_produtos INNER JOIN produtos ON PDPPRODUTO = PRDID INNER JOIN pedidos ON PDPPEDIDO = PEDID INNER JOIN usuarios ON PEDCLIENTE = USRID WHERE 1=1 ";
            
            }
        }
        if(isset($oDados['search'])) {

            $q = $oDados['search'];
            // FUNFOU? cara, eu vou mandar o zip pra você e vou começar a fazer a documentação, vou aproveitar e coloca no gtihub
            // pq o meu note vai atualizar aqui

            // a query? perdão?
            // CARA?
            //A query ro
            $sql .= "AND PDPPEDIDO LIKE '%$q%' OR USRNOME LIKE '%$q%' OR PEDDATA LIKE '%$q%' OR PRDNOTA LIKE '%$q%' OR PDPVLRUNIT LIKE '%$q%' OR PRDNOME LIKE '%$q%' OR PRDID LIKE '%$q%' OR PEDID LIKE '%$q%' OR PDPPRODUTO LIKE '%$q%' OR PEDCLIENTE LIKE '%$q%' OR USRID LIKE '%$q%' GROUP BY PDPPEDIDO, USRNOME, PEDDATA, PRDNOTA";
            
        }

        $oRes = mysqli_query($oCon, $sql);

        $oRes = mysqli_fetch_all($oRes, MYSQLI_ASSOC);

        echo json_encode($oRes);
    break;

    case 'marca':
        
        $sql = "SELECT * FROM `MARCA`";
    
        if(isset($oDados['search'])) {
            $q = $oDados['search'];
            
            $sql .= "AND MARCAID LIKE '%$q%' OR MARCANOME LIKE '%$q%' " ;
            
        }

        $oRes = mysqli_query($oCon, $sql);
        
        $oRes = mysqli_fetch_all($oRes, MYSQLI_ASSOC);

        echo json_encode($oRes);

    break;

    case 'filtro':

        $sql = "SELECT DISTINCT PRDID 'ID', PRDNOME 'NOME', PRDDESCRICAO 'DESCRICAO', IF(ISNULL(PRDVLRDESC)=0, PRDVLRDESC, PRDVLRUNIT) 'VALOR', marca.MARCANOME 'MARCA', cor.CORNOME FROM produtos INNER JOIN produto_categorias ON produto_categorias.FK_PROD = PRDID INNER JOIN marca ON MARCAID = PRDMARCA INNER JOIN COR ON CORID = PRDCOR WHERE PRDATIVO = 1 ";

        if(isset($oDados['consulta']))
        {
            $f = true;
            foreach($oDados['consulta'] as $index => $value){
                if($f == true) {
                    if($index == 'COR')
                    $sql .= "AND PRDCOR = $value ";
                    else if($index == 'MARCA')
                    $sql .= "AND PRDMARCA = $value ";
                    else
                    $sql .= "AND produto_categorias.FK_CATEG = $value ";

                    $f = false;
                }
                else if($index == 'COR')
                    $sql .= "OR PRDCOR = $value ";
                else if($index == 'MARCA')
                    $sql .= "OR PRDMARCA = $value ";
                else
                    $sql .= "OR produto_categorias.FK_CATEG = $value ";
            }
        }

        
        if(isset($oDados['search'])) {
            $q = $oDados['search'];
            
            $sql .= "AND PRDID LIKE '%$q%' OR PRDDESCRICAO LIKE '%$q%' OR PRDVLRDESC LIKE '%$q%' OR PRDVLRUNIT LIKE '%$q%' OR MARCANOME LIKE '%$q%' OR PRDMARCA LIKE '%$q%' OR CORNOME LIKE '%$q%' OR PRDCOR LIKE '%$q%'" ;
            
        }
        
        echo $sql;

        $oRes = mysqli_query($oCon, $sql);
        
        $oRes = mysqli_fetch_all($oRes, MYSQLI_ASSOC);

        echo json_encode($oRes);
        
    break;

    case 'usuario':

        if(gettype($oDados['consulta']) != "array" || gettype($oDados['consulta']) != "object" && $oDados['consulta'] != '' || isset($oDados['consulta']) || !empty($oDados['consulta'])) {
            $c = $oDados['consulta'];
            $sql = "SELECT USRNOME, USREMAIL, USRCPF, USRDTNASC FROM usuarios WHERE USRID LIKE '%" . $c ."%' ";
        }
            else
            $sql = "SELECT USRNOME, USREMAIL, USRCPF, USRDTNASC FROM usuarios WHERE 1=1  ";

        if(isset($oDados['search'])) {
            $q = $oDados['search'];

            $sql .= "AND USRID LIKE '%$q%' OR USRNOME LIKE '%$q%' OR USREMAIL LIKE '%$q%' OR USRCPF LIKE '%$q%' OR USRDTNASC LIKE '%$q%'";
        
        }

        echo $sql;

        $oRes = mysqli_query($oCon, $sql);

        $oRes = mysqli_fetch_all($oRes, MYSQLI_ASSOC);

        echo json_encode($oRes);
    break;

    case 'whishlist':
        //Codigo ancestral:
        //SELECT USRNOME NOME, PRDNOME PRODUTO, PRDVLRUNIT VALOR FROM wishlist INNER JOIN usuarios ON WL_CLIENTE = USRID INNER JOIN produtos ON WL_PRODUTO = PRDID

        if($oDados['consulta'] == '' || !isset($oDados['consulta']) || empty($oDados['consulta'])) {
            $sql = "SELECT USRNOME 'NOME', PRDNOME 'PRODUTO', PRDIMAGE 'IMGPATH', PRDVLRUNIT 'VALOR' FROM `wishlist` INNER JOIN usuarios ON WL_CLIENTE = USRID INNER JOIN produtos ON WL_PRODUTO = PRDID WHERE 1=1 ";
        }
        else {
            $sql = "SELECT PRDNOME 'PRODUTO', PRDIMAGE 'IMGPATH', PRDVLRUNIT 'VALOR' FROM `wishlist` INNER JOIN usuarios ON WL_CLIENTE = " . $oDados['consulta'] . " INNER JOIN produtos ON WL_PRODUTO = PRDID WHERE 1=1 ";
        }
    
        if(isset($oDados['search'])) {
            $q = $oDados['search'];

            $sql .= "AND USRNOME LIKE '%$q%' OR PRDNOME LIKE '%$q%' OR PRDVLRUNIT LIKE '%$q%' OR USRID LIKE '%$q%' OR PRDID LIKE '%$q%' OR WL_CLIENTE LIKE '%$q%' OR WL_PRODUTO LIKE '%$q%' OR WL_DATE LIKE '%$q%'";
        
        }

        echo $sql;

        $oRes = mysqli_query($oCon, $sql);
        
        $oRes = mysqli_fetch_all($oRes, MYSQLI_ASSOC);

        echo json_encode($oRes);

    break;

    case 'pagamento':
        
        $sql = "SELECT * FROM formas_pagamento where 1=1 ";

        if(isset($oDados['search'])) {
            $q = $oDados['search'];

            $sql .= "AND FRMID LIKE '%$q%' OR FRMTIPO LIKE '%$q%'";
        
        }

        echo $sql;

        $oRes = mysqli_query($oCon, $sql);
        
        $oRes = mysqli_fetch_all($oRes, MYSQLI_ASSOC);

        echo json_encode($oRes);
    break;

    case 'cor':

        $sql = "SELECT * FROM COR WHERE 1=1 ";

        if(isset($oDados['search'])) {
            $q = $oDados['search'];

            $sql .= "AND CORID LIKE '%$q%' OR CORNOME LIKE '%$q%'";
        
        }

        $oRes = mysqli_query($oCon, $sql);
        
        $oRes = mysqli_fetch_all($oRes, MYSQLI_ASSOC);

        echo json_encode($oRes);

    break;

    case 'movimento':
        $sql = "SELECT `MOVID`, 
        CASE 
            WHEN MOVTIPO = 1 THEN 'ENTRADA'
            WHEN MOVTIPO = 2 THEN 'VENDA'
            ELSE 'ERRO'
        END AS TIPO, DATE_FORMAT(`MOVDATA`, '%d/%m/%Y') AS 'DATA', `MOVNRONOTA`, PRDNOME, `MOVQTDE`, `MOVVLRUNIT` FROM `MOVIMENTOS` INNER JOIN PRODUTOS ON PRDID = MOVPRODUTO WHERE 1=1 ";

        if(isset($oDados['search'])) {
            $q = $oDados['search'];

            $sql .= "AND MOVID LIKE '%$q%' OR MOVDATA LIKE '%$q%' OR MOVNRONOTA LIKE '%$q%' OR PRDNOME LIKE '%$q%' OR MOVQTDE LIKE '%$q%' OR MOVVLRUNIT LIKE '%$q%'";
        
        }

        $oRes = mysqli_query($oCon, $sql);
        
        $oRes = mysqli_fetch_all($oRes, MYSQLI_ASSOC);

        echo json_encode($oRes);  

    break;

    case 'telefone':
        /* 
        
            Fixo - 1
            Movel - 2
            Comercial - 3

        */

        $sql = "SELECT USRNOME 'NOME', IF(TELTIPO = 1, 'FIXO', IF(TELTIPO = 2, 'MOVEL', IF(TELTIPO = 3, 'COMERCIAL', 'OUTRO'))) AS 'TIPO', TELNUMERO FROM TELEFONES INNER JOIN USUARIOS ON TELCLIENTE = USRID WHERE 1=1 ";

        if(isset($oDados['search'])) {
            $q = $oDados['search'];

            $sql .= "AND USRNOME LIKE '%$q%' OR TELCLIENTE LIKE '%$q%' OR TELTIPO LIKE '%$q%' OR TELNUMERO LIKE '%$q%'";
           
        }

        if(isset($oDados['consulta']))
            $sql .= "WHERE USRID = ".$oDados['consulta'].";";

        echo '<br>'.$sql;

        $oRes = mysqli_query($oCon, $sql);
        
        $oRes = mysqli_fetch_all($oRes, MYSQLI_ASSOC);

        echo json_encode($oRes);      
    break;

    case 'estoque':
        $sql = "SELECT PRDID, PRDNOME, CTGNOME, ESTQUANT, PRDVLRUNIT, ESTBLOQUEIO FROM PRODUTOS INNER JOIN CATEGORIAS ON PRDCATEG = CTGID INNER JOIN ESTOQUE ON ESTPRODUTO = PRDID";

            $oRes = mysqli_query($oCon, $sql);
            
            $oRes = mysqli_fetch_all($oRes, MYSQLI_ASSOC);

            echo json_encode($oRes);
    break;

    default:
        echo "ERRO: Passe no json um campo chamado 'tipo' cujo valor seja um entre estes:<br> -home <br> -pesquisa <br> -carrinho <br> -produto <br> -sac <br> -pedidos <br> -filtro <br> -usuario <br> -whishlist <br> pagamento <br> -estoque";
    break;
}

?>