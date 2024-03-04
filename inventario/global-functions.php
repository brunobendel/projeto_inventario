<?php

    /**
     * Calcula diferença entre 2 Time Zone
     * @param $zone1    'America/Sao_Paulo' 
     * @param $zone2    'America/New_York'
     * @return number
     */
    function time_zone_diff( $zone1, $zone2 ) 
    {
        
        if ( empty($zone1) || empty($zone2) ) return 0;
        
        $dt1 = new DateTime();
        $dt1->setTimezone(new DateTimeZone($zone1));
        
        $dt2 = clone $dt1;
        $dt2->setTimezone(new DateTimeZone($zone2));
        
        return ( substr($dt2->format(DateTime::ISO8601),20,2) - substr($dt1->format(DateTime::ISO8601),20,2) );
        
    }

	/**
	 *  Verifica as chaves dos arrays
	 *  Verifica se as chaves dos arrays existe e se a mesma tem algum valor
	 *
	 *  @param	array $array
	 *  @param 	string $key
	 *  @return	string | null
	 *
	 */

	function check_array( $array, $key )
	{
		if ( isset( $array[ $key ] ) && ! empty(  $array[ $key ] ) ) {
			return $array[ $key ];
		}
		
		return null;
	}

    /**
     * Função para carregar todas as classes
     * @param string $classname
     *
     */
    function autoloadMultipleDirectory($className){

        $arrayDir = [
            '/Db',
        ];

        foreach ($arrayDir as $directory) {
            $iterator = new RecursiveDirectoryIterator(PATH_APP . $directory);
            foreach (new RecursiveIteratorIterator($iterator) as $file) {
                if ($className === pathinfo($file, PATHINFO_FILENAME)) {
                    $fileName = $file;
                    break;
                }
            }
            (isset($fileName)) ? include_once($fileName) : null;
        }
    }

    spl_autoload_register('autoloadMultipleDirectory');


	/** 
	 * Retorno a classe CSS para mudar a cor do Texto
	 */
	function getColorClassText( $valor, $tipo ) {
		
		$color = $tipo .'-success';
		
		if ( $valor < 91 ) {
			$color = $tipo .'-danger';
		} else {
			if ( $valor >= 91 && $valor < 95 ) {
				$color = $tipo .'-warning';
			}
		}
		
		return $color;
	}

	/**
	 * Retorna a URL atual
	 */
	function getURL()
	{
		$whichprotocol = $_SERVER['HTTPS'] == 'on' ? 'https' : 'http';
		return $whichprotocol.'://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
	}
	
	/**
	 * Retorno as horas do turno informado
	 * @param	Turno int
	 * @return Array
	 */
	function getHourShift( $turno )
	{
		switch ( $turno )
		{
			case 1:	return array(6,7,8,9,10,11,12,13,14,15); 	break;
			case 2:	return array(15,16,17,18,19,20,21,22,23); 	break;
			case 3:	return array(23,0,1,2,3,4,5,6); 			break;
		}
	}
	
	/**
	 * Defini hora de início do turno
	 * @param 	int 	$turno
	 * @return	Array 	String
	 */
	function getHourShiftStart( $turno )
	{
		switch ($turno)
		{
			case 1: return array('START' => '06:45:00', 'FINISH' => '15:14:59'); break;
			case 2: return array('START' => '15:15:00', 'FINISH' => '23:29:59'); break;
			case 3: return array('START' => '23:30:00', 'FINISH' => '06:44:59'); break;
		}
		
	}
	
	/**
	 * Retorna o Turno atual de trabalho
	 * Ou turno anterior/próximo correspondente
	 * ao turno recebido como parâmetro
	 */
	function getShift($tipo = null, $shift = null)
	{
		if ( $tipo == null && $shift == null ) {
			
			$dt = new DateTime();
			
			if ( $dt >= new DateTime('06:45:00') && $dt <= new DateTime('15:14:59') ) {
				return 1;
			} else {
				if ( $dt > new DateTime('15:15:00') && $dt <= new DateTime('23:29:59') ) {
					return 2;
				} else {
					if ( $dt > new DateTime('23:30:00') && $dt <= new DateTime('23:59:59') ) {
						return 3;
					} else {
						if ( $dt >= new DateTime('00:00:00') && $dt <= new DateTime('06:44:59') ){
							return 3;
						}
					}
				}
			}
			
		} else {
			
			switch ( $tipo )
			{
				case 'previous':
					
						if ( $shift == 1) 
							return 3;
						else 
							return (--$shift);
						
					break;
					
				case 'next':
					
					if ( $shift == 3)
						return 1;
					else
						return (++$shift);
						
					break;
			}
			
		}
			
	}
	
	/**
	 * 	Funcão para calculo de Data em Decimal
	 * 
	 *  @param $decimalTime - Recebe data em decimal
	 */
	function decTimeToInterval($decimalTime)
	{
		$hours = floor($decimalTime);
		$decimalTime -= $hours;
		$minutes = floor($decimalTime * 60);
		$decimalTime -= ($minutes/60);
		$seconds = floor($decimalTime * 3600);
	
		$interval = new DateInterval("PT{$hours}H{$minutes}M{$seconds}S");
		return $interval;
	}


	/**
	 * 	Funcão para converter hora em decimal
	 *
	 *  @param $hour - Recebe hora formato H:i:s
	 */
	function HoursToDecimal( $time ) 
	{
		$hms = explode(':', $time);
		return ($hms[0] + ($hms[1]/60) + ($hms[2]/3600));
		
	}
	
	/**
	 * Procurar nome do usuário no AD
	 * @param	String	UserId
	 **/
	function get_ad_username( $userid )
	{
		if ( empty($userid) ) return;
		
		$URL = 'http://go.goodyear.com/phone/display.cfm?userid=';
		
		$contents = file_get_contents( $URL . strtoupper( $userid ) );
		
		$first_step = explode( '<h3 class="yellow">' , $contents);
		$second_step = explode("</h3>" , $first_step[1] );
		
		return $second_step[0];
		
	}

	/**
	 * Procurar Nome e Email do usuário no AD
	 * @param	String	UserId
	 **/
	function get_ad_info( $userid )
	{
		if ( empty($userid) ) return;
		
		$URL = 'http://go.goodyear.com/phone/display.cfm?userid=';
		
		$contents = file_get_contents( $URL . strtoupper( $userid ) );
		
		$first_step = explode( '<h3 class="yellow">' , $contents);
		$second_step = explode("</h3>" , $first_step[1] );
		
		$result = preg_match_all("/[a-z0-9]+[_a-z0-9.-]*[a-z0-9]+@[a-z0-9-]+(.[a-z0-9-]+)*(.[a-z]{2,4})/i", $contents, $matches);
		
		if ($result) {
			foreach(array_unique($matches[0]) as $value) {
				$email = $value;
			}
		}
		
		return array( 
				'NOME' => $second_step[0],
				'EMAIL'=> $email
		);
		
	}
	
	/**
	 * Remover Tags HTML
	 * @param $str
	 * @return string
	 */
	function htmlToPlainText($str){
	
		$str = str_replace('&nbsp;', ' ', $str);
		$str = html_entity_decode($str, ENT_QUOTES | ENT_COMPAT , 'UTF-8');
		$str = html_entity_decode($str, ENT_HTML5, 'UTF-8');
		$str = html_entity_decode($str);
		$str = htmlspecialchars_decode($str);
		$str = strip_tags($str);
		
		return $str;
	}
	
	/**
	 * Retorno a URL atual do navegador
	 * @param string $trim_query_string
	 * @return string|mixed
	 */
	function currentUrl( $trim_query_string = false ) {

		$pageURL = (isset( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] == 'on') ? "https://" : "http://";
		$pageURL .= $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];
		
		if( ! $trim_query_string ) {
			return $pageURL;
		} else {
			$url = explode( '?', $pageURL );
			return $url[0];
		}
		
	}

	/**
	 * Altera o nome da índice do array
	 *
	 * @param $array
	 * @param $old_key
	 * @param $new_key
	 * @return array
	 */
	function change_key( $array, $old_key, $new_key )
	{
		
		if( ! array_key_exists( $old_key, $array ) )
			return $array;
			
			$keys = array_keys( $array );
			$keys[ array_search( $old_key, $keys ) ] = $new_key;
			
			return array_combine( $keys, $array );
	}

	function url_get_contents($url, $useragent='cURL', $headers=false, $follow_redirects=true, $debug=false) {
	    
	    $result = '';
	    
	    $ch = curl_init();
	    
	    curl_setopt($ch, CURLOPT_URL,$url);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
	    curl_setopt($ch, CURLOPT_USERAGENT, $useragent);
	    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	    
	    if ($headers == true){
	        curl_setopt($ch, CURLOPT_HEADER,1);
	    }
	    
	    if ($headers=='headers only') {
	        curl_setopt($ch, CURLOPT_NOBODY ,1);
	    }
	    
	    if ($follow_redirects == true) {
	        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	    }
	    
	    if ($debug == true) {
	        $result['contents']= curl_exec($ch);
	        $result['info']    = curl_getinfo($ch);
	    } else {
	        $result = curl_exec($ch);
	    }
	    
	    curl_close($ch);
	    
	    return $result;
	}
	
	/**
	 * Função para retonar última modifica no arquivo
	 * @param String $file
	 * @return String Data
	 */
	function remoteFileDateTime( $file ) {
	    $h = get_headers($file, 1);
	    if (stristr($h[0], '200')) {
	        foreach($h as $k=>$v) {
	            if(strtolower(trim($k))=="last-modified")
	                return Date('d/m/Y H:i:s', strtotime($v));
	        }
	    }
	}
	
	function getBrowserLanguage() {
	    
	    $languages = $_SERVER["HTTP_ACCEPT_LANGUAGE"];
	    
	    $languages = str_replace( ' ', '', $languages );
	    $languages = explode( ",", $languages );
	    
	    return $languages[0];
	    
	}
	