<?php
require_once 'config.php';
require_once 'sever_functions.php';

session_start();

if (!isset($_SESSION['logado_inv'])) {
    header('Location:login.php');
}

if (isset($_GET['logoff'])) {
    session_destroy();
    header('Location:login.php');
}

if ($_POST) {
    $db   = new Database();
    $user = $_SESSION['user'];
    $date = date('d/m/Y H:i:s');

    $CURED_SHIFT = array(
        1 => array('START' => '06:45:00', 'FINISH' => '15:14:59'),
        2 => array('START' => '15:15:00', 'FINISH' => '23:29:59'),
        3 => array('START' => '23:30:00', 'FINISH' => '06:44:59')
    );
}

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>INVENTÁRIO</title>
    <link rel="shortcut icon" href="logo.png" type="image/x-icon" />

    <!-- Bootstrap CSS -->
    <link href="css/bootstrap.css" rel="stylesheet" >
    <link href="css/font-awesome.min.css" rel="stylesheet">
    <!-- Inclua a biblioteca Clipboard.js -->
    <script src="js/clipboard.min.js"></script>

</head>

<body>

    <div class="container-fluid">

        <div class="row">
            <div class="main">

                <?php

                if (isset($_SESSION['msg'])) {

                    $msg = explode("|", $_SESSION['msg']);

                    if ($msg[0] == 1) {
                        echo "<div class='text-center alert alert-success alert-dismissible' style='background-color: #00FF7F' role='alert'>";
                    } else {
                        echo "<div class='text-center alert alert-danger alert-dismissible' style='background-color: #DC143C;color: white'  role='alert'>";
                    }
                    echo "<button type='button' class='close' data-dismiss='alert' aria-label='Close'><span aria-hidden='true'>&times;</span></button>";

                    echo $msg[1];
                    unset($_SESSION['msg']);

                    echo "</div>";
                }

                ?>
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <table width="100%">
                            <tr>
                                <td align="left" width="25"><button type="button" id="sair" onClick="window.location.href='index.php?logoff'" class="btn btn-sm btn-info btn-block"> <i class="fa fa-user"></i> Sair </button></td>
                                <td align="center">
                                    <h4><strong><label id="titulo">Check-Inventário</label></strong></h4>
                                </td>
                                <td align="left" width="25"><button type="button" onClick="window.location.href='index.php'" class="btn btn-sm btn-warning btn-block"><i class="fa fa-mail-reply"></i> Voltar</button></td>
                            </tr>
                            <tr>
                                <td colspan="3" align="center">Consultar Endereço ou Equipamento, para visualizar material MWS</td>
                            </tr>
                        </table><br>
                    </div>
                    <div class="row">
                        <div class="col-lg-12">
                            <div id="" class="panel">
                                <form method="POST">
                                    <div class="panel-body">
                                        <div class="col-md-4">
                                            <label for="data">Selecione uma data:</label>
                                            <div id="dt-inicio">
                                                <input type="date" class="form-control" name="date" id="date" required>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <label>Matérial</label>
                                            <div>
                                                <select class="form-control" name="material" id="material" required>
                                                    <option value="BE">BE</option>
                                                    <option value="SW">SW</option>
                                                    <option value="LN">LN</option>
                                                    <option value="TR">TR</option>
                                                    <option value="PL">PL</option>
                                                    <option value="PX">PX</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <label for="turno">Turno:</label>
                                            <div>
                                                <select type="text" class="form-control" id="turno" name="turno" require>
                                                    <option value="1">1º Turno</option>
                                                    <option value="2">2º Turno</option>
                                                    <option value="3">3º Turno</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-sm-2">
                                            <div class="text-center">
                                                <div class="spinner-border" id="spinner" name="spinner" style="display: none;">
                                                    <span class="visually-hidden">Loading...</span>
                                                </div>
                                            </div>
                                            <br>
                                            <button type="button" id="btn-pesquisar" name="btn-pesquisar" class="btn btn-success btn-labeled form-control" style="width: 110px;">
                                                <i class="btn-label fa fa-search"></i>Pesquisar
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <br>
                    <div class="table-responsive" id="tabela" style="display: none;">
                        <div class="d-flex">
                            <button class="btn btn-light me-2" type="button" id="btnExport" value=" Export Table data into Excel ">Export Table data into Excel</button>
                            <!-- Botão para copiar a tabela -->
                            <button class="btn btn-light" id="btnCopiarTabela" data-clipboard-target="#tbl">Copy to Clipboard</button>
                            <br>
                        </div>
                        <table class="table table-striped" id="tbl" name="tbl">
                            <thead>
                                <tr>
                                    <th>Usuario</th>
                                    <th>Data</th>
                                    <th>Endereço</th>
                                    <th>Etiqueta</th>
                                    <th>Material</th>
                                    <th>Quantidade</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="js/jquery-1.11.3.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="js/ie10-viewport-bug-workaround.js"></script>


    <script>
        $(document).ready(function() {
            // Inicialize o Clipboard.js
            var clipboard = new ClipboardJS('#btnCopiarTabela');

            // Trate o sucesso ou falha da cópia
            clipboard.on('success', function(e) {
                console.info('Texto copiado: ', e.text);
                e.clearSelection(); // Limpa a seleção, útil para feedback visual
            });

            clipboard.on('error', function(e) {
                console.error('Falha ao copiar texto: ', e.action);
            });


            $("#btnExport").click(function(e) {
                var a = document.createElement('a');
                var data_type = 'data:application/vnd.ms-excel';
                var table_div = document.getElementById('tbl');
                var table_html = table_div.outerHTML.replace(/ /g, '%20');
                a.href = data_type + ', ' + table_html;
                a.download = 'filename.xls';
                a.click();
                e.preventDefault();
            });

            $('#btn-pesquisar').click(function() {

                $('#tbl tbody tr').empty();
                let material = $('#material').val().toUpperCase();
                let date = $('#date').val();
                let turno = $('#turno').val();

                $.ajax({
                    type: "POST",
                    url: "server_processing.php",
                    data: {
                        "acao": 'consulta',
                        'material': material,
                        'date': date,
                        'turno': turno,
                    },
                    beforeSend: function() {
                        $('#spinner').show();
                        $("#btn-pesquisar").hide();


                    },
                    success: function(retorno) {
                        console.log(retorno)
                        let response = JSON.parse(retorno);

                        if (response.resp.length > 0) {

                            $.each(response.resp, function(index, value) {

                                $('#tbl').append(
                                    "<tr>" +
                                    "<td>" + value.USER_ID + "</font></td>" +
                                    "<td>" + value.DATA + "</font></td>" +
                                    "<td>" + value.ENDERECO + "</font></td>" +
                                    "<td>" + value.ETIQUETA + "</font></td>" +
                                    "<td>" + value.MATERIAL + "</font></td>" +
                                    "<td>" + value.QUANTIDADE + "</font></td>" +
                                    "</tr>"
                                );
                            });

                        } else {
                            $('#tbl').empty();
                            $('#tbl').append(
                                "<tr align='left'>" +
                                "<td class='btn-info'><font size='5'>Nenhum item encontrado.</font></td>" +
                                "</tr>")
                        }

                    },
                    error: function(error) {

                        console.error("Erro na requisição AJAX: ", error);
                    },
                    complete: function() {
                        $("#spinner").hide();
                        $("#btn-pesquisar").show();
                        $("#tabela").show();
                    }
                });

            })
        });
    </script>

    <script src="js/bootstrap.min.js"></script>
</body>

</html>