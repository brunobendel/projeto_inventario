<?php

class Database
{
    private static $PATTERNS = '/([0-9]?[0-9])[\.\-\/ ]+([0-1]?[0-9])[\.\-\/ ]+([0-9]{2,4})/';

    protected	$host      	= '';							// Host da base de dados
    protected	$db_service	= '';   						// Serviço do banco de dados
    protected	$db_port   	= 1521;   							// Porta de Serviço do banco de dados
    protected	$password  	= '';          					// Senha do usuário da base de dados
    protected	$user      	= '';   						// Usuário da base de dados
    protected	$charset   	= '';      						// Charset da base de dados
    protected	$conn_data  = '';							// Parâmetro de conexão
    protected	$pdo       	= null; 						// Nossa conexão com o BD
    protected   $error     	= null; 						// Configura o erro
    protected	$debug     	= false; 						// Mostra todos os erros
    public      $last_id   	= null;      					// Último ID inserido

    /**
     * 	Construtor da classe
     *
     * @param string $host
     * @param string $db_service
     * @param string $db_port
     * @param string $password
     * @param string $user
     * @param string $charset
     * @param boolean $debug
     */
    public function __construct($host = null, $db_service = null, $db_port = null, $password = null, $user = null,
                                $charset  = null, $debug    = null, $connect_data = null )
    {

		$this->host		  = ($host 		 == null ? HOSTNAME 	: $host 		);
		$this->db_service = ($db_service == null ? DB_SERVICE 	: $db_service 	);
		$this->db_port    = ($db_port 	 == null ? DB_PORT 		: $db_port		);
		$this->password   = ($password 	 == null ? DB_PASSWORD	: $password		);
		$this->user       = ($user 		 == null ? DB_USER		: $user			);
		$this->charset    = ($charset 	 == null ? DB_CHARSET	: $charset		);
		$this->debug      = ($debug 	 == null ? DEBUG		: $debug		);

        $this->conn_data = ( $connect_data == null ? 'SERVICE_NAME' : $connect_data);

        $this->connect();

    }

    final protected function connect() {

        try {

            $this->pdo = new Oci8("oci:dbname={$this->host}:{$this->db_port}/{$this->db_service};charset={$this->charset}", $this->user, $this->password);

            if ( $this->debug === true ) $this->pdo->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );

            $oracleSession = new OracleSessionInit();
			$oracleSession->postConnect($this->pdo);	

            unset( $this->host     	 );
            unset( $this->db_service );
            unset( $this->db_port 	 );
            unset( $this->password 	 );
            unset( $this->user     	 );
            unset( $this->charset    );

        } catch (Oci8Exception $e) {
            echo $e->getMessage();
			return;
        }
    }

    /**
     * 	Query
     * @param string $stmt
     * @param array $data_array
     */
    public function query( $stmt, $data_array = null, $id = null) {

        $query = $this->pdo->prepare( $stmt );

        if ( $id != null ) {
            $query->bindParam($id, $this->last_id, PDO::PARAM_INT, 11);
        }

        $check_exec = $query->execute( $data_array );

        if ( $check_exec ) {
            return $query;
        } else {

            $error       = $query->errorInfo();
            $this->error = $error[2];

            return false;

        }
    }

    /**
     * Insere os valores e retorna o Último id
     *
     * @param $table
     * @param $values
     * @param $primaryKey
     * @return null|boolean|int
     */
    public function insert( $table, $values, $primaryKey = null ) {

        $SQL = ['INSERT INTO'];

        $SQL[] = $table;

        if (!empty($values)) {
            $columns = array_keys($values);

            $SQL[] = '(' . implode(', ', $columns) . ')';
            $SQL[] = 'VALUES';

            $columnValues = array();

            foreach ($values as $value) {
                $columnValues[] = "'{$value}'";
            }

            $SQL[] = '(' . implode(', ', $columnValues) . ')';
        }

        if (!is_null($primaryKey)) $SQL[] = "RETURNING {$primaryKey} INTO :{$primaryKey}";

        $stmt = implode(' ', $SQL);

        if (!is_null($primaryKey)) {

            $this->query($stmt, null, $primaryKey);
            return $this->last_id;

        } else {
            return $this->query($stmt)->rowCount();
        }

    }

    /**
     * 	Atualiza um linha no banco de dados
     *
     * @param string $table
     * @param string $where_field
     * @param string $where_field_value
     * @param string $values
     */
    public function update( $table, $where_field, $where_field_value, $values ) {

        if (empty($table) || empty($where_field) || empty($where_field_value)) return;

        $SQL  = "UPDATE {$table} SET " ;

        if (!empty($values)) {

            $columnValues = [];

            foreach ($values as $key => $value) {
                $columnValues[] = "{$key} = '{$value}'";
            }

            $SQL .= implode(', ', $columnValues);
        }

        $SQL .= " WHERE {$where_field} = '{$where_field_value}'";

        return $this->query($SQL)->rowCount();

    }

    /**
     * Deleta um registro
     *
     * @param string $table
     * @param string $where_field
     * @param string $where_field_value
     */
    public function delete( $table, $where_field, $where_field_value ) {

        if ( empty($table) || empty($where_field) || empty($where_field_value)  ) {
            return;
        }

        $stmt = "DELETE FROM {$table} WHERE $where_field = '{$where_field_value}'";

        return $this->query($stmt)->rowCount();

    }

    public function beginTransaction()
    {
        $this->pdo->beginTransaction();
    }

    public function commit()
    {
        $this->pdo->commit();
    }

    public function rollback()
    {
        $this->pdo->rollback();
    }
}