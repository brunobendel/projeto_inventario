<?php

//ini_set('session.gc_maxlifetime', 3600);	

//session_set_cookie_params(18000);

//set_time_limit(2800);

setlocale(LC_ALL, 'pt_BR', 'pt_BR.utf-8', 'pt_BR.utf-8', 'portuguese');

date_default_timezone_set('America/Sao_Paulo');

/**
 * @ Váriaveis  globais
 */
define('PATH_APP', dirname(__FILE__));

/**
 * 	Define Ambiente da Aplicação
 */
defined('APPLICATION_ENV')
	|| define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production'));

/**
 * @ Conexão com Banco de Dados
 * 
 */
define('HOSTNAME', (APPLICATION_ENV == 'production' ? '10.104.129.13' : '10.104.129.18'));
define('DB_SERVICE', (APPLICATION_ENV == 'production' ? 'ORA' : 'ORAT'));

define('DB_USER', 	  'BR_FAM');
define('DB_PASSWORD', (APPLICATION_ENV == 'production' ? 'KIaVk9x3qna4re5' : 'KIaVk9x3qna4re5'));

define('PCF_HOSTNAME', 	'br02sp01d');
define('PCF_SID', (APPLICATION_ENV == 'production' ? 'PCF' : 'PCF'));
define('PCF_USER', 		'LDA3820');
define('PCF_PASSWORD', (APPLICATION_ENV == 'production' ? 'TdOY5dKJg4HfVpi' : 'TdOY5dKJg4HfVpi'));

define('DB_CHARSET',  'UTF8');
define('DB_PORT', 	  1521);

define('DEBUG', (APPLICATION_ENV == 'production' ? true : true));

ini_set('display_errors', (APPLICATION_ENV == 'production' ? true : true));
ini_set('display_startup_errors', (APPLICATION_ENV == 'production' ? true : true));

error_reporting(E_ALL);


require_once 'global-functions.php';
