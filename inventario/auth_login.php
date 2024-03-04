<?php
   
	/**
	 * Funзгo de validaзгo no AD via protocolo LDAP
	 * valida_ldap("servidor", "domнniousuбrio", "senha");
	 */
	
	function valida_ldap($usr, $pwd) {
		
		$ldaprdn = 'cn=user,ou=group,dc=domain,dc=com';
		$sdn = 'cn=user,ou=group,dc=domain,dc=com';
		
		$dominio = "la"; 								//Dominio Ex: @gmail.com
		$ldap_server = "la.ad.goodyear.com"; 			//IP ou nome do servidor
		$auth_user = $dominio."\\".$usr;
		$auth_pass = $pwd;
		
		$srdn = '';
		
		if (!($connect = @ldap_connect($ldap_server))) {
			return FALSE;
		}
		
		if (!($bind = @ldap_bind($connect, $auth_user, $auth_pass))) {
			return FALSE;
		} else {
			$filter="uid=*";
			$justthese = array("uid");
			
			$sr=ldap_read($connect, $srdn, $filter, $justthese);
			$entry = ldap_get_entries($connect, $sr);
			
			return TRUE;
		}
		
		ldap_close($connect);
	}    
?>