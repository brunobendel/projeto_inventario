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
    $data = new DateTime();
    $user = $_SESSION['user'];
    $date = date('d/m/Y H:i:s');

    $dadosInseridos = array();

    $responseTurno = getTurnoDtinicialDtfinal($data);

    $dateInt = $responseTurno['datainicial'];
    $dateEnd = $responseTurno['datafinal'];
    $quantidadeInseridos = 0;
    $quantidadeNaoInseridos = 0;


    // Loop para iterar sobre todas as entradas no array
    foreach ($_POST['ENDERECO'] as $key => $endereco) {
        $etiqueta = $_POST['ETIQUETA'][$key];
        $material = $_POST['PRODUTO'][$key];
        $quantidade = intval($_POST['QTD'][$key], 10); // Convertido para inteiro

        $verificar = validacaoEtiqueta($etiqueta, $dateInt, $dateEnd);

        $dados = array(
            'User_ID' => $user,
            'Data' => $date,
            'Endereco' => $endereco,
            'Etiqueta' => $etiqueta,
            'Material' => $material,
            'Quantidade' => $quantidade
        );

        if (count($verificar) == 0) {
            $insert = $db->insert('PPP_CONT_INV', $dados);
            $dadosInseridos[] = $dados;
            $quantidadeInseridos++;
            $_SESSION['msg'] = "1|INVENTÁRIADO COM SUCESSO! Registrados: ".$quantidadeInseridos ." etiquetas";
        } else {
            $dadosNaoInseridos[] = $dados;
            $quantidadeNaoInseridos++;
            $_SESSION['msg'] = "0|ERRO AO REGISTRAR!". $quantidadeNaoInseridos ." etiquetas ja registras.";
        }
    }
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
    <link href="css/bootstrap.css" rel="stylesheet">
    <link href="css/font-awesome.min.css" rel="stylesheet">

</head>

<body>

    <div class="container-fluid">

        <div class="row">
            <div class="main">
                <?php
                if (isset($_SESSION['msg'])) {
                    $msg = explode("|", $_SESSION['msg']);
                    $msgClass = isset($msg[2]) ? $msg[2] : ''; // Pega a terceira parte como classe

                    if ($msg[0] == 1) {
                        echo "<div id='msg-container' class='text-center alert alert-success alert-dismissible $msgClass' style='background-color: #00FF7F' role='alert'>";
                    } else {
                        echo "<div id='msg-container' class='text-center alert alert-danger alert-dismissible $msgClass' style='background-color: #DC143C; color: white' role='alert'>";
                    }
                    echo "<button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>";
                    echo $msg[1];
                    $_SESSION['msg'] = null;// ou $_SESSION['msg'] = '';
                    echo "</div>";
                }
                ?>
                <!-- // Adiciona script JavaScript para ocultar o alerta após 5 segundos echo  -->
                <script>
                    setTimeout(function() {
                        document.getElementById('msg-container').style.display = 'none';
                    }, 3000);
                </script>
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <table width="100%">
                            <tr>
                                <td align="left" width="25"><button type="button" id="sair" onClick="window.location.href='index.php?logoff'" class="btn btn-sm btn-info btn-block"> <i class="fa fa-user"></i> Sair </button></td>
                                <td align="center">
                                    <h4><strong><label id="titulo">Inventário Raia</label></strong></h4>
                                </td>
                                <td align="left" width="25"><button type="button" onClick="window.location.href='inv.php'" class="btn btn-sm btn-warning btn-block"><i class="fa fa-mail-reply"></i> Voltar</button></td>
                            </tr>
                            <tr>
                                <td colspan="3" align="center">Conferir quantidade antes de confirmar.</td>
                            </tr>
                        </table><br>
                    </div>
                    <div class="table-responsive">
                        <form action="" method="POST" id="form">
                            <table class="table">

                                <tr>
                                    <td><label>Raia</label></td>
                                    <td><input name="raia" type="text" size="10" id="raia" placeholder="Raia" autofocus></td>
                                </tr>
                                <div>
                                    <table width="100%" id="tbl">

                                    </table>
                                </div>
                                <div class="panel-footer">
                                    <div class="text-center">
                                        <div class="spinner-border" id="spinner" name="spinner" style="display: none;">
                                            <span class="visually-hidden">Loading...</span>
                                        </div>
                                    </div>
                                    <table width="100%">
                                        <tr>
                                            <td><button class="btn btn-large btn-success col-md-11 btn-block" id="button" name="button" type="submit">Confirmar</button></td>
                                        </tr>
                                    </table>
                                </div>
                            </table>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="js/jquery-1.11.3.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="js/ie10-viewport-bug-workaround.js"></script>

    <script>
        $('#raia').on('input', function() {
            $('#form input[type="text"]').keypress(function(event) {
                // Verifica se a tecla pressionada é Enter (código 13)
                if (event.which === 13) {
                    // Impede o envio do formulário
                    event.preventDefault();
                }
            });
            let raia = $('#raia').val().toUpperCase();

            if (raia.length >= 5) {
                $.ajax({
                    type: "POST",
                    url: "server_processing.php",
                    data: {
                        "acao": 'raia',
                        "raia": raia
                    },
                    beforeSend: function() {
                        $('#spinner').show();
                        $("#button").hide();


                    },
                    success: function(data) {
                        let resposse = JSON.parse(data);

                        if (resposse.resp.length > 0) {

                            $('#tbl').empty();
                            $("#tbl").show();


                            $.each(resposse.resp, function(index, response) {

                                console.log(resposse.resp.length);
                                $('#tbl').append(
                                    "<tr align='left'>" +
                                    "<td class='bg-info'><font size='5'>Endereço:</font></td>" +
                                    "<td class='bg-info'><font size='5'>" + response.ENDERECO + "</font></td>" +
                                    "</tr>" +
                                    "<tr align='left'>" +
                                    "<td class='bg-light'><font size='5'>Etiqueta:</font></td>" +
                                    "<td class='bg-light'><font size='5'>" + response.ETIQUETA + "</font></td>" +
                                    "</tr>" +
                                    "<tr align='left'>" +
                                    "<td class='bg-light'><font size='5'>Material:</font></td>" +
                                    "<td class='bg-light'><font size='5'>" + response.PRODUTO + "</font></td>" +
                                    "</tr>" +
                                    "<tr align='left'>" +
                                    "<td class='bg-light'><font size='5'>Quantidade:</font></td>" +
                                    "<td class='bg-light'><font size='5'>" + response.QTD + "</font></td>" +
                                    "</tr>"
                                );
                                $('#form').append(

                                    "<input type='hidden' name='ENDERECO[]' value='" + response.ENDERECO + "'>" +
                                    "<input type='hidden' name='ETIQUETA[]' value='" + response.ETIQUETA + "'>" +
                                    "<input type='hidden' name='PRODUTO[]' value='" + response.PRODUTO + "'>" +
                                    "<input type='hidden' name='QTD[]' value='" + parseInt(response.QTD, 10) + "'>"
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
                        $("#button").show();
                    }

                });
            }
        });
    </script>

    <script src="js/bootstrap.min.js"></script>

</body>

</html>