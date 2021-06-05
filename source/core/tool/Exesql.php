<?php


namespace ng169\tool;
use \ng169\db\Dbsql;

class  Exesql
{

    public static $pre;
    public static $dbinfo;
    public static
    function load($filename,$dbname='main')
    {
         $dbs=include(CONF.'/db.inc.php');
        $dbinfo = self::$dbinfo = $dbs[$dbname];

        if (is_file($filename)) {
            $handel = fopen($filename,'r');
		
            $sqlf   = fread($handel,filesize($filename));
	
            $db     = new Dbsql($dbinfo['dbhost'], $dbinfo['dbuser'], $dbinfo['dbpwd'], $dbinfo['dbname'], $dbinfo['charset']);
			

            self::$pre    = $dbinfo['dbpre'];
            $sqls   = self::handle_sql_string($sqlf);
			
            if (is_array($sqls)) {
                foreach ($sqls as $sql) {
                    if (trim($sql) != '') {
                        $db->exec($sql);
                    }
                }
            }else {
                $db->exec($sqls);
            }
            return true;
        }else {
            return false;
        }
    }
    public static
    function handle_sql_string($sql)
    {
    	
        if (PHP_VERSION > '4.1') {
            $sql = preg_replace("/TYPE=(InnoDB|MyISAM)( DEFAULT CHARSET=[^; ]+)?/", "TYPE=\\1 DEFAULT CHARSET=utf8",
                $sql);
        }
        $sql = str_replace("\r", "\n", $sql);

        
        $sql = str_replace('{dbpre}', self::$pre, $sql);
        $ret = array();
        $num          = 0;
        $queriesarray = explode(";\n", trim($sql));
        unset($sql);
        foreach ($queriesarray as $query) {
            $ret[$num] = '';
            $queries = explode("\n", trim($query));
            $queries = array_filter($queries);
            foreach ($queries as $query) {
                $str1 = substr($query, 0, 1);
                if ($str1 != '#' && $str1 != '-')
                $ret[$num] .= $query;
            }
            $num++;
        }
        return ($ret);
    }


}
?>
