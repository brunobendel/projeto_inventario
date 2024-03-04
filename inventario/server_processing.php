<?php

use Symfony\Component\Mime\Part\Multipart\FormDataPart;

session_start();

require_once 'config.php';
require_once 'sever_functions.php';

$db = new Database();
$data = new DateTime();

if (!isset($_POST['acao']) && !isset($_GET['acao'])) {
	echo "Falta de Parametro!";
	exit;
}
if (isset($_GET['acao'])) {
	$acao = $_GET['acao'];
} else {
	$acao = $_POST['acao'];
}

switch ($acao) {

		// BUSCAR GT E QTD DO EQUIPAMENTO-----------------------------------------------------------------//
	case 'equipamento':

		$equipamento = strtoupper($_POST['cod']);

		$row = getLastTag($equipamento);

		$data_const = null;
		$address 	= null;
		$color 		= null;

		if (!empty($row)) {
			$dt_atual = new DateTime('now');
			$data_const = tagByMov($row->ETIQUETA, '0300');
			$address    = tagByMov($row->ETIQUETA, null);
			if ($address) {
				$address = $address->ENDERECO;
			}

			$dt_ini = DateTime::createFromFormat('d/m/Y H:i:s', $data_const->DATA);
			$dt_fim = $dt_ini->add(new DateInterval('P6D'));

			if ($dt_atual > $dt_fim) {
				$data_const = 'PNEU VENCIDO! Data Const:' . $dt_fim->format('d/m/Y');
				$color = 'label-danger';
			} else {
				$data_const = 'VALIDADE: ' . $dt_fim->format('d/m/Y');
				$color = 'label-warning';
			}
		}

		$response = [];

		echo json_encode([
			'resp' => $response = $row,
			'd_const' => $data_const,
			'color' => $color,
			'address' => $address
		]);

		break;

		// BUSCAR EQUIPAMMENTO, GT E QTD DO ENDEREÇO---------------------------------------------------------//
	case 'endereco':

		$address = strtoupper($_POST['cod']);

		$row = getLastTagAddress($address);
		$data_const = null;
		$color = null;

		if (!empty($row)) {
			$dt_atual = new DateTime('now');
			$data_const = tagByMov($row->ETIQUETA, '0300');
			$dt_ini = DateTime::createFromFormat('d/m/Y H:i:s', $data_const->DATA);
			$dt_fim = $dt_ini->add(new DateInterval('P6D'));

			if ($dt_atual > $dt_fim) {
				$data_const = 'PNEU VENCIDO! Data Const:' . $dt_fim->format('d/m/Y');
				$color = 'label-danger';
			} else {
				$data_const = 'VALIDADE: ' . $dt_fim->format('d/m/Y');
				$color = 'label-warning';
			}
		}

		$response = [];

		echo json_encode([
			'resp' => $response = $row,
			'd_const' => $data_const,
			'color' => $color
		]);

		break;

		// TRANSFERENCIA ------------------------------------------------------------------------------------//
	case 'transf':

		$response = [];
		$enviar =  true;
		$user	= $_SESSION['user'];
		$name 	= getUserName($user);
		$equipamento = strtoupper($_POST['equipamento']);
		$endereco 	 = strtoupper($_POST['endereco']);

		if (strlen($equipamento) > 5) {
			$equipamento = substr($equipamento, 5);
		}

		if (strlen($endereco) > 5) {
			$endereco = substr($endereco, 5);
		}

		//CONSULTA ENDERECO A SER TRANSFERIDO.
		$address = statusAddress($endereco);

		if ($address == 0) {
			$response = [
				'status' => false,
				'msg' => 'ENDEREÇO SEM CAPACIDADE!'
			];
		}

		//CONSULTA ETIQUETA EQUIPAMENTO.
		$row = getLastTag($equipamento);

		if ($row) {

			$etiqueta = $row->ETIQUETA;
			$produto  = $row->PRODUTO;

			$dados = [
				'ETIQUETA'    	=> $etiqueta,
				'DATA'			=> $data->format('d/m/Y H:i:s'),
				'EQUIPAMENTO'   => $equipamento,
				'ENDERECO'		=> $endereco,
				'USER_ID'		=> $user,
				'USER_NAME'		=> $name,
				'PRODUTO'      	=> $produto,
				'TIPO_MOV'		=> 'TRANSF'
			];

			$mws = transferTag($etiqueta, $endereco, $equipamento);

			if ($mws == 1) {

				$insert = $db->insert('TBL_LOGS_TRANSFER_MWS@BR_DIVB1', $dados);

				if ($insert) {
					$response = [
						'status' => true,
						'msg' => 'TRANSFERIDO COM SUCESSO!'
					];
				} else {
					$response = [
						'status' => false,
						'msg' => 'ERRO AO REGISTRA LOGS!'
					];
				}
			} else {
				$response = [
					'status' => false,
					'msg' => 'ERRO AO TRANSFERIR PARA MWS!'
				];
			}
		} else {
			$response = [
				'status' => false,
				'msg' => 'ERRO AO CONSULTAR DADOS!'
			];
		}

		echo json_encode($response);

		break;

		// INFO POR ETIQUETA ------------------------------------------------------------------------------------//
	case 'etiqueta':

		$response = [];
		$tagNumber = strtoupper($_POST['tagNumber']);
		$tagNumber = substr($tagNumber, -8);

		$row = getTagInfo($tagNumber);

		echo json_encode([
			'resp' => $response = $row,
		]);

		break;
	case 'raia':

		$response = [];
		$raia = strtoupper($_POST['raia']);
		$raia = substr($raia, -5);

		$row = getRaiaInfo($raia);

		echo json_encode([
			'resp' => $response = $row,
		]);

		break;
		
	case 'consulta':
		
		$response = [];
		$date = DateTime::createFromFormat('Y-m-d', $_POST['date']);
		
		$material = strtoupper($_POST['material']);

		$turno = intval($_POST['turno'],10);
		if ($turno == 1){
			$turnoInt = '06:45:00';
			$turnoEnd = '15:14:59';
		}elseif($turno == 2){
			$turnoInt = '15:15:00';
			$turnoEnd = '23:29:59';
		}else{
			$turnoInt = '23:30:00';
			$turnoEnd = '06:44:59';
		}

		$row = consulta($date->format('d/m/Y'),$material,$turnoInt,$turnoEnd);

		echo json_encode([
			'resp' => $response = $row,
		]);

		break;
}
