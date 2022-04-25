<?php
include_once(GALAXIA_LIBRARY.'/common/observable.php');
//!! Abstract class representing the base of the API
//! An abstract class representing the API base
/*!
This class is derived by all the API classes so they get the
database connection, database methods and the Observable interface.
*/
class Base extends Observable
{
    public $db;  // The ADODB object used to access the database
    public $num_queries = 0;

    // Constructor receiving a ADODB database object.
    function __construct($db=null)
    {
        if(!$db) {
            // Try to save the day
            global $dbGalaxia;
            if (!isset($dbGalaxia)) {
                // Show the childs class which errored out, but also show we detected it here
                throw new Exception("Invalid db object passed to :'".get_class($this)."' constructor. (detected in: '".__CLASS__."')");
            }
            $this->db = $dbGalaxia;
        } else {
            $this->db = $db;
        }
    }

    // copied from tikilib.php
    function query($query, $values = null, $numrows = -1, $offset = -1, $reporterrors = true)
    {
        $this->convert_query($query);
        if ($numrows == -1 && $offset == -1)
            $result = $this->db->Execute($query, $values,GALAXIA_FETCHMODE);
        else
            $result = $this->db->SelectLimit($query, $numrows, $offset, $values,GALAXIA_FETCHMODE);
        if (!$result && $reporterrors)
            $this->sql_error($query, $values, $result);
        $this->num_queries++;
        return $result;
    }

    function getOne($query, $values = null, $reporterrors = true)
    {
        $this->convert_query($query);
        $result = $this->db->SelectLimit($query, 1, 0,$values,GALAXIA_FETCHMODE);
        if (!$result && $reporterrors)
            $this->sql_error($query, $values, $result);

        $res = $result->fetchRow();
        $this->num_queries++;
        if ($res === false)
            return (NULL); //simulate pears behaviour
        $key = key($res);
        $value = current($res);
        return $value;
    }

    function sql_error($query, $values, $result)
    {
        global $ADODB_LASTDB;

        throw new Exception($ADODB_LASTDB . " error:  " . $this->db->ErrorMsg(). " in query:<br/>" . $query . "<br/>", E_USER_WARNING);
        // only for debugging.
        // print_r($values);
        //echo "<br />";
    }

    // functions to support DB abstraction
    function convert_query(&$query)
    {
        global $ADODB_LASTDB;

        switch ($ADODB_LASTDB) {
        case "oci8":
            $query = preg_replace("/`/", "\"", $query);
            // convert bind variables - adodb does not do that
            $qe = explode("?", $query);
            $query = '';
            for ($i = 0; $i < sizeof($qe) - 1; $i++) {
                $query .= $qe[$i] . ":" . $i;
            }
            $query .= $qe[$i];
            break;
        case "postgres7":
        case "sybase":
            $query = preg_replace("/`/", "\"", $query);
            break;
        }
    }

    function convert_sortmode($sort_mode)
    {
        global $ADODB_LASTDB;

        switch ($ADODB_LASTDB) {
        case "pgsql72":
        case "postgres7":
        case "oci8":
        case "sybase":
            // Postgres needs " " around column names
            //preg_replace("#([A-Za-z]+)#","\"\$1\"",$sort_mode);
            $sort_mode = str_replace("_", "\" ", $sort_mode);
            $sort_mode = "\"" . $sort_mode;
            break;
        case "mysql3":
        case "mysql":
        default:
            $sort_mode = str_replace("_", "` ", $sort_mode);
            $sort_mode = "`" . $sort_mode;
            break;
        }
        return $sort_mode;
    }

    function convert_binary()
    {
        global $ADODB_LASTDB;

        switch ($ADODB_LASTDB) {
        case "pgsql72":
        case "oci8":
        case "postgres7":
            return;
            break;
        case "mysql3":
        case "mysql":
            return "binary";
            break;
        }
    }

    function qstr($string, $quoted = null)
    {
        if (!isset($quoted)) {
            $quoted = get_magic_quotes_gpc();
        }
        return $this->db->qstr($string,$quoted);
    }

    static function tbl($tbl)
    {
        return ' `'.GALAXIA_TABLE_PREFIX.$tbl.'` ';
    }

    static function normalize($name, $version = null)
    {
        $name = str_replace(" ","_",$name);
        $name = preg_replace("/[^0-9A-Za-z\_]/",'',$name);
        return $name;
    }
} //end of class

?>
