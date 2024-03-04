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
    $enviar =  true;
    $date = date('d/m/Y H:i:s');
    $etiqueta = $_POST['etiqueta'];
    $quantidade = $_POST['quantidade'];
    $material = $_POST['material'];
    $endereco = $_POST['endereco'];


    $responseTurno = getTurnoDtinicialDtfinal($data);

    $dateInt = $responseTurno['datainicial'];
    $dateEnd = $responseTurno['datafinal'];

    $verificar = validacaoEtiqueta($etiqueta, $dateInt, $dateEnd);

    if (count($verificar) == 0) {
        $dados = array(

            'User_ID' => $user,
            'Data' => $date,
            'Endereco' => $endereco,
            'Etiqueta' => substr($etiqueta, -8),
            'Material' => $material,
            'Quantidade' =>  intval($quantidade)

        );

        $insert = $db->insert('PPP_CONT_INV', $dados);

        if ($insert) {
            $_SESSION['msg'] = "1|INVENTÁRIADO COM SUCESSO!";
        } else {
            $_SESSION['msg'] = "0|ERRO AO REGISTRAR!";
        }
    } else {
        $_SESSION['msg'] = "0|ETIQUETA JA CADASTRADA!";
    }
}


?>

<!DOCTYPE html>
<html lang="en">

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
                    $_SESSION['msg'] = null; // ou $_SESSION['msg'] = '''';
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
                                <td align="left" width="25"><button type="button" id="sair" onClick="window.location.href='index.php?logoff'" class="btn btn-md btn-info btn-block"> <i class="fa fa-user"></i> Sair </button></td>
                                <td align="center">
                                    <h4><strong><label id="titulo">Inventário</label></strong></h4>
                                </td>
                                <td align="left" width="25"><button type="button" onClick="window.location.href='inv.php'" class="btn btn-md btn-warning btn-block"><i class="fa fa-mail-reply"></i> Voltar</button></td>
                            </tr>
                            <tr>
                                <td colspan="3" align="center">Conferir quantidade antes de confirmar.</td>
                            </tr>
                        </table><br>
                    </div>
                    <div class="table-responsive">
                        <div class="text-center">
                            <div class="spinner-border" id="spinner" name="spinner" style="display: none;">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                        <form action="" method="POST" id="form">
                            <table class="table">

                                <tr>
                                    <td><label>Etiqueta</label></td>
                                    <td><input name="etiqueta" type="text" size="10" id="etiqueta" placeholder="Etiqueta" autofocus></td>
                                </tr>
                                <tr>
                                    <td><label>Material</label></td>
                                    <td><input name="material" type="text" size="10" id="material" placeholder="Material"></td>
                                </tr>
                                <tr>
                                    <td><label>Quantidade</label></td>
                                    <td><input name="quantidade" type="text" size="10" id="quantidade" placeholder="Quantidade"></td>
                                </tr>
                                <tr hidden>
                                    <td><input name="endereco" type="text" size="10" id="endereco"></td>
                                </tr>

                                <div class="panel-footer">
                                    <table width="100%">
                                        <tr>
                                            <td><button class="btn btn-lg btn-success col-md-12 btn-block" id="button" name="button" type="submit">Confirmar</button></td>
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
        $('#etiqueta').on('input', function() {
            $('#form input[type="text"]').keypress(function(event) {
                // Verifica se a tecla pressionada é Enter (código 13)
                if (event.which === 13) {
                    // Impede o envio do formulário
                    event.preventDefault();
                }
            });
            let tagNumber = $('#etiqueta').val().toUpperCase();

            if (tagNumber.length >= 8) {

                $('#spinner').show();

                $.ajax({
                    type: "POST",
                    url: "server_processing.php",
                    data: {
                        "acao": 'etiqueta',
                        "tagNumber": tagNumber
                    },
                    beforeSend: function() {
                        $('#spinner').show();
                        $("#material").hide();
                        $("#quantidade").hide();
                        $("#endereco").hide();
                        $('#quantidade').hide();
                        $('#button').hide();

                    },
                    success: function(data) {
                        let resposse = JSON.parse(data);

                        $.each(resposse.resp, function(index, value) {
                            resposse = value;
                        });

                        $("#material").val(resposse.PRODUTO);
                        $("#quantidade").val(resposse.QTD);
                        $("#endereco").val(resposse.ENDERECO);
                        $('#quantidade').focus();
                    },
                    error: function(error) {
                        console.error("Erro na requisição AJAX: ", error);
                    },
                    complete: function() {

                        $("#spinner").hide();
                        $("#material").show();
                        $("#quantidade").show();
                        $("#endereco").show();
                        $('#quantidade').show();
                        $('#button').show();

                    }
                });
            }
        });
    </script>

    <script src="js/bootstrap.min.js"></script>

</body>

</html>