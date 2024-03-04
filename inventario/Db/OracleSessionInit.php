<?php

class OracleSessionInit
{
    /** @var string[] */
    protected $_defaultSessionVars = [
        'NLS_TIME_FORMAT'           => 'HH24:MI:SS',
        'NLS_DATE_FORMAT'           => 'DD/MM/YYYY',
        'NLS_TIMESTAMP_FORMAT'      => 'DD/MM/YYYY HH24:MI:SS',
        'NLS_TIMESTAMP_TZ_FORMAT'   => 'DD/MM/YYYY HH24:MI:SS TZH:TZM',
        'NLS_NUMERIC_CHARACTERS'    => '.,',
    ];

    /**
     * @param string[] $oracleSessionVars
     */
    public function __construct(array $oracleSessionVars = [])
    {
        $this->_defaultSessionVars = array_merge($this->_defaultSessionVars, $oracleSessionVars);
    }

    /**
     * @return void
     */
    public function postConnect($event)
    {
        if (! count($this->_defaultSessionVars)) {
            return;
        }

        $vars = [];
        foreach (array_change_key_case($this->_defaultSessionVars, CASE_UPPER) as $option => $value) {
            if ($option === 'CURRENT_SCHEMA') {
                $vars[] = $option . ' = ' . $value;
            } else {
                $vars[] = $option . " = '" . $value . "'";
            }
        }

        $SQL = 'ALTER SESSION SET ' . implode(' ', $vars);

        $event->query($SQL);

    }
}