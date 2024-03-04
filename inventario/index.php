<?php
require_once 'config.php';

session_start();

if (!isset($_SESSION['logado_inv'])) {
    header('Location:login.php');
}

if (isset($_GET['logoff'])) {
    session_destroy();
    header('Location:login.php');
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
    <link href="img/logo.png" rel="shortcut icon" />

    <!-- Bootstrap CSS -->
    <link href="css/bootstrap.css" rel="stylesheet" >

    <link href="css/font-awesome.min.css" rel="stylesheet">

</head>

<body>
    <div class="container-fluid">

        <div class="row">

            <?php

            if (isset($_SESSION['msg'])) {

                $msg = explode("|", $_SESSION['msg']);

                $alertClass = ($msg[0] == 1) ? 'alert-success' : 'alert-danger';
                echo "<div class='text-center alert $alertClass alert-dismissible' role='alert'>";
                echo "<button type='button' class='close' data-dismiss='alert' aria-label='Close'><span aria-hidden='true'>&times;</span></button>";

                echo $msg[1];
                unset($_SESSION['msg']);

                echo "</div>";
            }

            ?>

            <div class="panel-heading">
                <div class="row">
                    <div class="col-md-4 col-12 mb-2 mb-md-0">
                        <button type="button" id="sair" onClick="window.location.href='index.php?logoff'" class="btn btn-md btn-info btn-block">
                            <i class="fa fa-user"></i> SAIR
                        </button>
                    </div>
                    <div class="col-md-4 col-12 text-center">
                        <h4><strong><label id="titulo">MENU</label></strong></h4>
                    </div>
                </div>
            </div>

        </div>
        <div class="container mt-5">
            <div class="row">
                <div class="col-sm-12 d-flex align-items-center justify-content-center">
                    <div class="text-center">
                        <a href="inv.php" class="text-decoration-none">
                            <button type="button" id="mov" class="btn btn-lg btn-info col-md-8 btn-block mb-3"> 
                                <i class="fa fa-file"></i> Inventário
                            </button>
                        </a>
                        <a href="pesquisa.php" class="text-decoration-none">
                            <button type="button" id="mov" class="btn btn-lg btn-info col-md-8 btn-block mb-3">
                                <i class="fa fa-search"></i> Check Inventário
                            </button>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="js/jquery-1.11.3.min.js"></script>
    <script type="text/javascript"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="js/ie10-viewport-bug-workaround.js"></script>

	<script src="js/bootstrap.min.js"></script>

</body>

</html>