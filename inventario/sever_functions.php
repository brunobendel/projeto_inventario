<?php

// $data = json_decode(file_get_contents('php://input'));
// echo json_encode($data);

function transferTag3($tagNumber, $address, $equipment = '')
{
	$db = new Database();
	$SQL = "BEGIN PCF.LOAD_MOVREQUEST2@BR02SP01D_MWS(" .

		"'<?xml version=\"1.0\" encoding=\"UTF-8\"?>" .

		"<MOVREQUEST>" .

		"<ETIQUETA>{$tagNumber}</ETIQUETA>" .

		"<EQUIPAMENTO>{$equipment}</EQUIPAMENTO>" .

		"<ENDERECO_DESTINO>{$address}</ENDERECO_DESTINO>" .

		"<SETUP></SETUP>" .

		"<BARCODE_A></BARCODE_A>" .

		"<BARCODE_B></BARCODE_B>" .

		"</MOVREQUEST>');" .

		"END;";
	return  $SQL;
	return $query = (bool)$db->query($SQL);
}
function transferTag($tagNumber, $address, $equipment = '', $user = '')
{

	$db = new Database;
	$SQL = " BEGIN INTERFACE_MOVREQUEST@BR_FAM('{$tagNumber}','{$equipment}','{$address}','{$user}'); END;";

	return $query = (bool)$db->query($SQL);
}
function transferTag2($tagNumber, $address, $equipment = '')
{

	$db = new Database;
	$SQL = " BEGIN INTERFACE_MOVREQUEST@BR_FAM('{$tagNumber}','{$equipment}','{$address}'); END;";

	return $query = (bool)$db->query($SQL);
}
function finalizeTag($equipment, $date, $userid, $qty = 'Z')
{
	$db = new Database();
	$SQL =  "BEGIN PCF.PCF_INTERFACE.LOAD_CARREQUEST@BR02SP01D_MWS(" .
		"'<?xml version=\"1.0\" encoding=\"UTF-8\"?>" .
		"<CARREQUEST>" .
		"<ID>{$equipment}</ID>" .
		"<DATE_TIME>{$date}</DATE_TIME>" .
		"<USR>{$userid}</USR>" .
		"<QTY>{$qty}</QTY>" .
		"</CARREQUEST>', :V_RESULT); " .
		"END;";

	return $query = (bool)$db->query($SQL, null, 'V_RESULT');
}

function finalizeTag2($equipment, $date, $userid, $qty = 'Z')
{
	$db = new Database();
	$SQL =  "BEGIN INTERFACE_CARREQUEST@BR_FAM('{$equipment}', '{$date}', '{$userid}', '{$qty}'); END;";

	return $query = (bool)$db->query($SQL);
}

function getUserName($user)
{
	$db = new Database();
	$SQL = "SELECT
					substr(FNC_NOME, 0, instr(FNC_NOME,' ')) NOME
				FROM FAM_FUNCIONARIO@BR_FAM
				WHERE FNC_USERID = '{$user}'";

	$query = $db->query($SQL);

	return $query->fetch(PDO::FETCH_OBJ)->NOME;
}

function getLastTag($equipment)
{
	$db = new Database();
	$SQL = "SELECT
					*
				FROM
					(
						SELECT 
							UN.idmovun ETIQUETA,UN.movunqty QTD, P.code PRODUTO
						FROM 
							TBLMOVUN@BR02SP01D_MWS UN
							INNER JOIN tblproduct@BR02SP01D_MWS P
							ON P.idproduct = UN.idproduct
						WHERE
							UN.AUXFIELD2 = '{$equipment}'
							AND P.code <> 'Vazio'
						ORDER BY
							UN.IDMOVUN DESC
					)
				WHERE ROWNUM = 1";

	$query = $db->query($SQL);
	return $query->fetch(PDO::FETCH_OBJ);
}

function statusAddress($address)
{
	$status = 1;
	$db = new Database();
	$SQL = "SELECT  
					W.code DEPOSITO,
					A.code ENDERECO,
					UN.idmovun ETIQUETA,
					P.code PRODUTO,
					TO_CHAR(UN.dttimestamp,'DD/MM/YYYY HH24:MI:SS') DATA,
					UN.auxfield2 AUX2,
					UN.movunqty QTD,
					B.code UN
				FROM
					tblmovun@BR02SP01D_MWS UN 
					INNER JOIN tbladdress@BR02SP01D_MWS A
					ON A.idaddress = UN.idaddress 
					INNER JOIN tblproduct@BR02SP01D_MWS P
					ON P.idproduct = UN.idproduct
					INNER JOIN tblwarehouse@BR02SP01D_MWS W
					ON W.idwarehouse = A.idwarehouse
					LEFT JOIN tblbunit@BR02SP01D_MWS B
					ON B.idbunit = P.idbunit
				WHERE
					A.CODE = '{$address}' AND
					
					UN.MOVUNQTY >= 

					(
						CASE
							WHEN P.code <> 'Vazio' THEN 1 
							WHEN P.code = 'Vazio' AND UN.DTOUT IS NULL THEN 0
							WHEN P.code = 'Vazio' AND UN.DTOUT IS NOT NULL THEN 1
						END
					)
				ORDER BY
					DATA ASC ";

	$query = $db->query($SQL);

	$qty = capacityAddress($address);

	if (count($query->fetchAll(PDO::FETCH_OBJ)) == $qty) {
		$status = 0;
	}

	return $status;
}

function capacityAddress($address)
{
	$db = new Database();
	$SQL = "SELECT
					DISTINCT(W.CODE) DEPOSITO,
					C.qtyequipaddress CAPACIDADE,
					A.extcode EXT,
					A.flgenable STATUS
				FROM
					tbladdress@BR02SP01D_MWS A
					INNER JOIN tblwarehouse@BR02SP01D_MWS W
					ON W.idwarehouse = A.idwarehouse
					INNER JOIN ctbladdress@BR02SP01D_MWS C
					ON C.idaddress = A.idaddress  
				WHERE
					A.CODE = '{$address}'";

	$query = $db->query($SQL);
	return $query->fetch(PDO::FETCH_OBJ)->CAPACIDADE;
}
function getLastTagAddress($address)
{
	$db = new Database();
	$SQL = "SELECT  
					W.code DEPOSITO,
					A.code ENDERECO,
					UN.idmovun ETIQUETA,
					P.code PRODUTO,
					TO_CHAR(UN.dttimestamp,'DD/MM/YYYY HH24:MI:SS') DATA,
					UN.auxfield2 AUX2,
					UN.movunqty QTD,
					B.code UN
				FROM
					tblmovun@BR02SP01D_MWS UN 
					INNER JOIN tbladdress@BR02SP01D_MWS A
					ON A.idaddress = UN.idaddress 
					INNER JOIN tblproduct@BR02SP01D_MWS P
					ON P.idproduct = UN.idproduct
					INNER JOIN tblwarehouse@BR02SP01D_MWS W
					ON W.idwarehouse = A.idwarehouse
					LEFT JOIN tblbunit@BR02SP01D_MWS B
					ON B.idbunit = P.idbunit
				WHERE
					A.CODE = '{$address}' AND B.code = 'PNEU_B1' AND
					
					UN.MOVUNQTY >= 

					(
						CASE
							WHEN P.code <> 'Vazio' THEN 1 
							WHEN P.code = 'Vazio' AND UN.DTOUT IS NULL THEN 0
							WHEN P.code = 'Vazio' AND UN.DTOUT IS NOT NULL THEN 1
						END
					)
				ORDER BY
					DATA ASC ";

	$query = $db->query($SQL);

	return $query->fetch(PDO::FETCH_OBJ);
}

function tagByMov($tagNumber, $codMov)
{
	if ($codMov != null) {
		$codMov = "AND TP.code ='" . $codMov . "'";
	} else {
		$codMov = "";
	}
	$db = new Database();
	$SQL = "SELECT  
				MEV.idmovun ETIQUETA,  
				MEV.idmovev MOV,  
				(TP.code || ' - ' || TP.name) TIPO_MOV,  
				TO_CHAR(MEV.dttimestamp,'DD/MM/YYYY HH24:MI:SS') DATA,  
				A.code ENDERECO,  
				W.code DEPOSITO,  
				P.code PRODUTO,  
				CASE TP.i_o  
				WHEN 1 THEN '' || MEV.movqty  
				WHEN 2 THEN '-' || MEV.movqty  
				END QTD,  
				MEV.auxfield2 AUX_2,  
				R.name RECURSO,  
				R.code COD_RECURSO,  
				U.nickname USUARIO,  
				U.code NSA,  
				B.code UN  
			FROM  
				TBLMOVEV@BR02SP01D_MWS MEV INNER JOIN tblmovtype@BR02SP01D_MWS TP ON TP.idmovtype = MEV.idmovtype  
				INNER JOIN tbladdress@BR02SP01D_MWS A ON A.idaddress = MEV.idaddress  
				LEFT JOIN tblwarehouse@BR02SP01D_MWS W ON W.idwarehouse = A.idwarehouse  
				LEFT JOIN tblproduct@BR02SP01D_MWS P ON P.idproduct = MEV.idproduct  
				LEFT JOIN tblbunit@BR02SP01D_MWS B ON B.idbunit = P.idbunit  
				LEFT JOIN tblresource@BR02SP01D_MWS R ON R.idresource = MEV.idresource  
				LEFT JOIN tbluser@BR02SP01D_MWS U ON U.iduser = MEV.iduser  
			WHERE  
				MEV.idmovun = {$tagNumber} {$codMov}
			ORDER BY  
				MEV.idmovev DESC";

	$query = $db->query($SQL);

	return $query->fetch(PDO::FETCH_OBJ);
}

function getTagInfo($tagNumber)
{
	$db = new Database();
	$SQL = "    SELECT  
					W.code DEPOSITO,
					A.code ENDERECO,
					UN.idmovun ETIQUETA,
					P.code PRODUTO,
					TO_CHAR(UN.dttimestamp,'DD/MM/YYYY HH24:MI:SS') DATA,
					UN.auxfield2 AUX2,
					UN.movunqty QTD,
					B.code UN
				FROM
					tblmovun@BR02SP01D_MWS UN 
					INNER JOIN tbladdress@BR02SP01D_MWS A
					ON A.idaddress = UN.idaddress 
					INNER JOIN tblproduct@BR02SP01D_MWS P
					ON P.idproduct = UN.idproduct
					INNER JOIN tblwarehouse@BR02SP01D_MWS W
					ON W.idwarehouse = A.idwarehouse
					LEFT JOIN tblbunit@BR02SP01D_MWS B
					ON B.idbunit = P.idbunit
				WHERE
					UN.idmovun = '{$tagNumber}' AND
					UN.MOVUNQTY >=
				
					(
						CASE
							WHEN P.code <> 'Vazio' THEN 1 
							WHEN P.code = 'Vazio' AND UN.DTOUT IS NULL THEN 0
							WHEN P.code = 'Vazio' AND UN.DTOUT IS NOT NULL THEN 1
						END
					)";


	$query = $db->query($SQL);
	return $query->fetchAll(PDO::FETCH_OBJ);
}

function getRaiaInfo($raia)
{
	$db = new Database();
	$SQL = "SELECT  
				W.code DEPOSITO,
				A.code ENDERECO,
				UN.idmovun ETIQUETA,
				P.code PRODUTO,
				TO_CHAR(UN.dttimestamp,'DD/MM/YYYY HH24:MI:SS') DATA,
				UN.auxfield2 AUX2,
				UN.movunqty QTD,
				B.code UN
			FROM
				tblmovun@BR02SP01D_MWS UN 
				INNER JOIN tbladdress@BR02SP01D_MWS A
				ON A.idaddress = UN.idaddress 
				INNER JOIN tblproduct@BR02SP01D_MWS P
				ON P.idproduct = UN.idproduct
				INNER JOIN tblwarehouse@BR02SP01D_MWS W
				ON W.idwarehouse = A.idwarehouse
				LEFT JOIN tblbunit@BR02SP01D_MWS B
				ON B.idbunit = P.idbunit
			WHERE
				A.CODE = '{$raia}' AND
				UN.MOVUNQTY >=
				(
					CASE
						WHEN P.code <> 'Vazio' THEN 1 
						WHEN P.code = 'Vazio' AND UN.DTOUT IS NULL THEN 0
						WHEN P.code = 'Vazio' AND UN.DTOUT IS NOT NULL THEN 1
					END
				)";


	$query = $db->query($SQL);
	return $query->fetchAll(PDO::FETCH_OBJ);
}
function consulta($date,$material,$turnoInt,$turnoEnd)
{
	$db = new Database();
	$SQL = "SELECT
				*
			FROM
				PPP_CONT_INV
			WHERE
				DATA >= TO_DATE('{$date} {$turnoInt}','DD/MM/YYYY HH24:MI:SS')AND
				DATA <= TO_DATE('{$date} {$turnoEnd}','DD/MM/YYYY HH24:MI:SS') AND
				MATERIAL LIKE '{$material}%'
			ORDER BY
        		DATA ASC";


	// echo $SQL;
	$query = $db->query($SQL);
	return $query->fetchAll(PDO::FETCH_OBJ);
}
function getTurnoDtinicialDtfinal($hora)
{
    if( $hora->format('H:i:s')>='06:45:00' && $hora->format('H:i:s') <='15:14:59'){
        $data_inicial = $hora->format('d/m/Y') . ' 06:45:00';
        $data_final = $hora->format('d/m/Y') . ' 15:14:59';
        $turno = 1;
    }
    else{
        if( $hora->format('H:i:s')>='15:15:00' && $hora->format('H:i:s') <='23:29:59'){ 
            $data_inicial = $hora->format('d/m/Y') . ' 15:15:00';
            $data_final = $hora->format('d/m/Y') . ' 23:29:59';
            $turno = 2;
        }
        else{
            if( $hora->format('H:i:s')>='23:30:00' && $hora->format('H:i:s') <='23:59:59'){
                $data_inicial = $hora->format('d/m/Y') . ' 23:30:00';
                $hora->add(new DateInterval('P1D'));
                $data_final = $hora->format('d/m/Y') . ' 06:44:59';
                $turno = 3;
            }
            else{
                if($hora->format('H:i:s')>='00:00:00' && $hora->format('H:i:s') <='06:44:59'){
                    $data_final = $hora->format('d/m/Y') . ' 06:44:59';
                    $hora->sub(new DateInterval('P1D'));
                    $data_inicial = $hora->format('d/m/Y') . ' 23:30:00';
                    $turno = 3;
                }
            }
        }      
    }

    $response = array(
        'turno'         => $turno,
        'datainicial'   => $data_inicial,
        'datafinal'     => $data_final
    );

    return $response;
}

function validacaoEtiqueta($etiqueta,$dateInt,$dateEnd)
{
	$db = new Database();
	$SQL = "SELECT
				*
			FROM
				PPP_CONT_INV
			WHERE
				DATA >= TO_DATE('{$dateInt}','DD/MM/YYYY HH24:MI:SS') AND
				DATA <= TO_DATE('{$dateEnd}','DD/MM/YYYY HH24:MI:SS') AND
				ETIQUETA = {$etiqueta}";


	$query = $db->query($SQL);
	return $query->fetchAll(PDO::FETCH_OBJ);
}