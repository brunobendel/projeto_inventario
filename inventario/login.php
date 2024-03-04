<?php

require_once 'auth_login.php';

session_cache_expire(1);

session_start();


if ($_POST) {

	$username = strtoupper($_POST['usuario']);
	$password = $_POST['senha'];
	$permission = [
		'AC28952' => 'ok',
		'ZA40244' => 'ok'
	];


	if (valida_ldap($username, $password)) {

		$relatorio = 'relatorio.php';
		$_SESSION['user'] = $username;

		$_SESSION['logado_inv'] = true;
		header('Location: index.php');
	} else {
		$_SESSION['msg'] = "Usuário ou senha incorreto.";
	}
}
?>

<!DOCTYPE html>
<html lang="pt">

<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="description" content="">
	<meta name="author" content="">
	<link href="img/logo.png" rel="shortcut icon" />

	<title>INVENTÁRIO | login</title>

    <!-- Bootstrap CSS -->
    <link href="css/bootstrap.css" rel="stylesheet" >
	<link href="css/font-awesome.min.css" rel="stylesheet">
	<link href="css/login_style.css" rel="stylesheet">

</head>

<body onload="frmlogin.usuario.focus()">

	<div class="container-fluid">

		<?php
		if (isset($_SESSION['msg'])) {
			echo "<div class='alert alert-danger alert-dismissible'  role='alert'>
							<button type='button' class='close' data-dismiss='alert' aria-label='Close'><span aria-hidden='true'>&times;</span></button>";

			echo $_SESSION['msg'];
			unset($_SESSION['msg']);

			echo "</div>";
		}

		?>
		<form id="frmlogin" action="login.php" method="POST">
			<div class="panel panel-default">
				<div class="panel-heading">
					<table width="100%">
						<tr>
							<td align="center"><strong>
									<h1 class="logo-name">INVENTÁRIO</h1>
								</strong></td>
						</tr>
					</table>
				</div><br>
				<table class="table">
					<tr>
						<td><label>&nbsp;Usuário</label></td>
						<td><input type="text" size="15" maxlength="7" id="usuario" name="usuario" placeholder="Usuário" required></td>
					</tr>
					<tr>
						<td><label>&nbsp;Senha</label></td>
						<td><input type="password" size="15" id="senha" name="senha" placeholder="Senha" required></td>
					</tr>
				</table>
				<div class="panel-footer">
					<table width="100%">
						<tr>
							<td><button class="btn btn-large btn-primary col-md-11 btn-block" type="submit">Entrar</button></td>
						</tr>
					</table>
				</div>
			</div>
		</form>
	</div>

	<script src="js/bootstrap.min.js"></script>
	<script src="js/ie10-viewport-bug-workaround.js"></script><!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->

	<script src="js/jquery.min.js"></script>

	<script type="text/javascript">
		$(document).ready(function() {

			$(".alert").delay(3000).slideUp(200, function() {
				$(this).slideUp(500);
			});

			$("#usuario").on('keypress', function() {
				if ($(this).val().length >= 7) {
					$("#senha").focus();
				}
			});
		});
	</script>

</body>

</html>