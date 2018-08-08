<?php

/** 
 * A easy and fast way to execute trasactional and no transactional CRUD queries.
 * @author fyupanquia <fyupanquia@outlook.com>
 * @copyright Copyright (c) 2018, Frank Yupanqui
 * @link https://github.com/fyupanquia/CMB
 * @version 2.0
 * @package CMB
 */
class CMB
{
    private static $connection;
    private static $lastErrorMessage;
    private static $lastErrorMesEsages = [];
    private static $defaultErrorMessages = ["An array of parameters (not empty) was expected to instance a new PDO connection.",//
                                            "The parameter(s): @params were expected to instance a new PDO connection.",//
                                            "It was expected @quantity parameter(s) to instance a new PDO connection.",//
                                            "An array of parameters (not empty) was expected for the method called @method.",//
                                            "The parameter @param was expected for the method called @method.",//
                                            "Each key field must not be numeric, sentence: @description.",//
                                            "Each value field must not be an Array or an Object, sentence: @description.",//
                                            "There is not any @crudtype sentence.",//
                                            "CMB is not connected to any database.",//
                                            "The pdo connection was not defined correctly",//
                                            "It was expected @length parameter(s) @params, sentence @sentence",//
                                            "The param @table (String) must be a not empty String, @fields (Array) and @data (Array) must not be an empty Array, sentence: @sentence",//
                                            "Each value data must not be a empty Array, sentence: @sentence and index: @index",//
                                            "The param @table (String) must be a not empty String and the param @data (Array) must not be an empty Array, sentence @sentence",//
                                            "Each where condition must be a not empty Array, sentence:@sentence and index:@index",//
                                        ];

    private static $privateQuery = "*****";
    private static $strictMode   = false;
    private static $defaultVersion  = 3;
    private static $versions        = [1,2,3];
    private static $crudtypes       = ["INSERT", "UPDATE", "DELETE"];
    private static $connectionParameters = ["DB_SERVER", "DB_USER", "DB_PASSWORD", "DB_NAME"];
    private static $lastErrorMessages = [];

    public function __construct($params=null)
    {
        self::setConnection($params);
    }
    /**
     * Set the strict mode, by default False
     * @return Boolean
     */
    public static function  setStrict($b)
    {
        if (is_bool($b)) {
            self::$strictMode = $b;
        }
    }
    /**
     * Restuns if it is enabled the stric mode
     * @return Boolean
     */
    public static function  getStrict()
    {
        return self::$strictMode;
    }
    private static function getDefaultErrorMessage($i, $args=[])
    {
        return str_replace(array_keys($args), array_values($args), self::$defaultErrorMessages[$i] );
    }
    private static function isPDO($arg = null)
    {
        return ($arg instanceof PDO);
    }
    private static function getCRUDType($index)
    {
        return self::$crudtypes[$index];
    }
    private static function solvePDO($params)
    {   
        $obj = false;
        if ( array_key_exists("pdo",$params) ||  array_key_exists("PDO",$params) ) {
            @$backtrace = (isset($params["backtrace"])) ? $params["backtrace"] : array_pop(debug_backtrace());
            $backtrace["message"] = self::getDefaultErrorMessage(9);
            if (isset($params["pdo"]) || isset($params["PDO"]) ) {
                $pdo = (!isset($params["pdo"]) && isset($params["PDO"])) ? $params["PDO"] : $params["pdo"];
                if( self::isPDO($pdo) ) {
                    $obj = $pdo;
                } else {
                    self::setBackTraceErrorMessage($backtrace);
                }
            } else {
                self::setBackTraceErrorMessage($backtrace);
            }
        } else {
            if (self::isConnected()) {
                $obj = self::getDefaultConnection();
            } else {
                self::setLastErrorMessage(self::getDefaultErrorMessage(8));   
            }
        }
        return $obj;
    }
    private static function solveException($params)
    {   
        extract($params);
        if (isset($exception)) {
            @$backtrace = (isset($backtrace)) ? $backtrace : array_shift($exception->getTrace());
            $sql = (isset($sql)) ? ( isset($private) && $private==true ? self::$privateQuery : $sql ) : self::$privateQuery;
            $backtrace["message"] = "An error was found executing '{$sql}', ".$exception->getMessage();
            self::setBackTraceErrorMessage($backtrace);  
        }  else if(isset($exception)) {
            self::setLastErrorMessage($exception->getMessage());
        } else {
            self::setLastErrorMessage("An unknown error has occurred.");
        }   
    }
    private static function setBackTraceErrorMessage($backtrace)
    {
        extract($backtrace);
        if (isset($message)) {
            if (is_array($message)) {
                self::setLastErrorMessages($message);
                $message = array_pop($message);
            } else {
                //self::addLastErrorMessages($message);
                self::addLastErrorMessages("An error has occurred in {$file} at line {$line} : {$message}.");
            }
            //self::setLastErrorMessage("An error has occurred in {$file} at line {$line} : {$message}.");
        }
    }
    private function setLastErrorMessage($str)
    {
        self::$lastErrorMessage = $str;
    }
    private function setLastErrorMessages($arr)
    {
        self::$lastErrorMessages = $arr;
    }
    private function addLastErrorMessages($msg)
    {
        self::$lastErrorMessages[] = $msg;
    }
    /**
     * @param array $params
        * @param string $DB_SERVER
        * @param string $DB_NAME
        * @param string $DB_USER
        * @param string $DB_PASSWORD
     * @return boolean
     */
    private static function PDOConnection($params) {
    	$pdo        = false;
    	if (is_array($params)) {
            $count_parameters = count(self::$connectionParameters); 
    		if ( count($params)== $count_parameters) {
    			extract($params);
    			if(isset($DB_SERVER) && isset($DB_USER) && isset($DB_PASSWORD) && isset($DB_NAME)){
			        try {
			            $pdo = new PDO("mysql:host={$DB_SERVER};dbname={$DB_NAME}", "{$DB_USER}", "{$DB_PASSWORD}");
			        	$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			        } catch (PDOException $e) {
                        self::addLastErrorMessages($e->getMessage());
			        }
    			} else {
                    self::addLastErrorMessages(self::getDefaultErrorMessage(1, ["@params"=>implode(", ",self::$connectionParameters)]));
                }
    		} else {
                self::addLastErrorMessages(self::getDefaultErrorMessage(2, ["@quantity"=>$count_parameters]));
            }
    	} else {
            self::addLastErrorMessages(self::getDefaultErrorMessage(0, []));
        }
        return $pdo;
    }
    /**
    * Get a new connection, it won't be assigned to static CMB
    * @param $params(Array("DB_SERVER"=>String, "DB_USER"=>String, "DB_PASSWORD"=>String, "DB_NAME"=>String))
    * @return String
    */
    public static function getConnection($params=null)
    {
        return self::PDOConnection($params);
    }
    /**
    * Set a new connection
    * @param $params(Array("DB_SERVER"=>String, "DB_USER"=>String, "DB_PASSWORD"=>String, "DB_NAME"=>String))
    * @return String
    */
    public static function setConnection($params=null)
    {
        self::$connection  = self::PDOConnection($params);
    }
    /**
    * Return last Error Message
    * @return String
    */
    public static function getLastErrorMessage()
    {
        return @array_pop(self::getLastErrorMessages());
    }
    /**
    * Return last Error Messages
    * @return Array
    */
    public static function getLastErrorMessages()
    {
        return self::$lastErrorMessages;
    }
    /**
    * Get status connection
    * @return Boolean
    */
    public static function isConnected()
    {
        return self::isPDO(self::getDefaultConnection());
    }
    /**
    * Get Default PDO Object
    * @return PDO
    */
 	public function getDefaultConnection()
    {
        return self::$connection;
    }
    /**
     * Execute multiple queries, No Transactional
     * @param  $params  (Array("sql"=>String, "fields"=>Array() *, "pdo"=>PDO Object *))
     * @return boolean
     */
    public static function exec($params=null)
    {
    	$d 		= false;
        $method  = "'".__FUNCTION__."'";
        @$backtrace = (isset($params["backtrace"])) ? $params["backtrace"] : array_shift(debug_backtrace());
        if (is_array($params)) {
            extract($params);
        	if (isset($sql) && strlen($sql)) {
                $fields = isset($fields) ? (is_array($fields) ? $fields : null) : null;
                $pdo 	= self::solvePDO($params);
                if ($pdo) {
        	        $query 	= $pdo->prepare($sql);
        	        try {
                        $arrfields = [];
                        $evalFields = self::evalFields($fields,$backtrace);
                        if (!$evalFields["haserror"]) {
                            $d = $query->execute($evalFields["fields"]);
                        }
        	        } catch (Exception  $exception) {
                        self::solveException(compact("exception", "private", "sql", "backtrace"));
        			}
                }
        	} else {
                $backtrace["message"] = self::getDefaultErrorMessage(4, ["@param"=>"'sql'", "@method"=>$method]);
                self::setBackTraceErrorMessage($backtrace);
            }
        } else {
            $backtrace["message"] = self::getDefaultErrorMessage(3, ["@method"=>$method]);
            self::setBackTraceErrorMessage($backtrace);
        }
        return $d;
    }
    /**
     * Execute multiple queries, Transactional
     * @param  $params  (Array("execs"=>Array(Array("sql"=>String, "fields"=>Array *),..), "pdo"=>PDO Object * ))
     * @return boolean
     */
    public static function execT($params=null)
    {
        $d      = false;
        $method  = "'".__FUNCTION__."'";
        @$backtrace = (isset($params["backtrace"])) ? $params["backtrace"] : array_shift(debug_backtrace());
        if (is_array($params)) {
            extract($params);
        	$execs     = isset($execs) ? $execs : [];
            if (is_array($execs) && count($execs)) {
        	   $pdo       = self::solvePDO($params);
               if ($pdo) {
                    $sql = "";
                    $b = 0;
            		try {
        	    		$pdo->beginTransaction();
        	    		foreach ($execs as $i => $exec) {
        	    			if (is_array($exec)){
        	    				if (isset($exec["sql"])) {
                                    $sql = $exec["sql"];
                                    $fields = isset($exec["fields"]) ? $exec["fields"] : null;
                                    $evalFields = self::evalFields($fields,$backtrace);
                                    if (!$evalFields["haserror"]) {
                                        $query 	= $pdo->prepare($sql);
                                        $query->execute($evalFields["fields"]);
                                        $b++;
                                    } else {
                                        $b = 0;
                                        break 1;
                                    }
        	    				}
        	    			}
                        }
                        if ($b) { $d = $pdo->commit(); }
            		} catch (Exception $exception) {
                        if ($b) { $pdo->rollBack(); }
                        self::solveException(compact("exception", "private", "sql", "backtrace"));
            		}
               }
        	} else {
                $backtrace["message"] = self::getDefaultErrorMessage(4, ["@param"=>"'execs'", "@method"=>$method]);
                self::setBackTraceErrorMessage($backtrace);
            }
        } else {
            $backtrace["message"] =  str_replace("!method",$method , self::getDefaultErrorMessage(0));
            $backtrace["message"] = self::getDefaultErrorMessage(3, ["@method"=>$method]);
            self::setBackTraceErrorMessage($backtrace);
        }
        return $d;
    }

    private static function evalFields($_fields,$backtrace)
    {
        $haserror = false;
        $fields = [];
        if (is_array($_fields)) {
            $count = 0;
            foreach ($_fields as $key => $f) {
                if (!is_numeric($key)) {
                    if (is_array($f) || is_object($f)) {
                        $backtrace["message"] = self::getDefaultErrorMessage(6, ["@description"=>"Index ({$count})"]);
                        self::setBackTraceErrorMessage($backtrace);
                        if (self::$strictMode) {
                            $haserror   = true;
                            break 1;
                        }
                    } else {
                        $fields[$key] = $f;
                    }
                } else {
                    $backtrace["message"] = self::getDefaultErrorMessage(5, ["@description"=>"Index ({$count})"]);
                    self::setBackTraceErrorMessage($backtrace);
                    if (self::$strictMode) {
                        $haserror   = true;
                        break 1;
                    }
                }
                $count++;
            }
        }
        return compact("haserror", "fields");
    }


    // --------------------------------------------- BEGIN INSERT SECTION ---------------------------------------------
    public function insert($params, $version =3)
    {
        $version = (is_numeric($version) && in_array($version, self::$versions)) ? $version : self::$defaultVersion;
        $method  = "'".__FUNCTION__."'";
        @$backtrace = (isset($params["backtrace"])) ? $params["backtrace"] : array_shift(debug_backtrace());
        return self::getInsertData(compact("backtrace", "method", "version"), $params);
    }
    private function getInsertData($myself, $params)
    {   
        extract($myself);
        $d = false;
        $method  = isset($method) ? $method : "'".__FUNCTION__."'";
        $backtrace = isset($backtrace) ? $backtrace :  @array_shift(debug_backtrace());
        $version   = isset($version) ? $version : self::$defaultVersion;
        $evalCRUDSentences    = self::evalCRUDSentences(0,$method, $params);
        
        if (is_array($evalCRUDSentences)) {
            extract($evalCRUDSentences);
            $inTransaction = $pdo->inTransaction();
            try {
                $sql_magic = "";
                $_fields = [];
                $stop    = false;
                $execs = [];
                if ($version==3) {
                    if (!$inTransaction) {
                        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                        $pdo->beginTransaction();
                    }
                }
                foreach ($inserts as $iii => $insert) {
                    $evalDataInsertSentence = self::evalDataInsertSentence($iii, $insert);
                    if (is_array($evalDataInsertSentence)) {
                        extract($evalDataInsertSentence);
                        $pre_sql_magic = "";
                        $pre_myfields = [];
                        foreach ($data as $i => $_d) {
                            if (is_array($_d) && @count($_d)) {
                                $mysql_magic = "";
                                $haserror   = false;
                                $_myfields = [];
                                foreach ($fields as $ii => $f) {
                                    if (!is_numeric($f)) {
                                        $val = isset($_d[$f]) ? $_d[$f] : null;
                                        $_key = "{$f}_{$iii}_{$i}";
                                        $mysql_magic .= ":$_key,";
                                        if (is_array($val) || is_object($val)) {
                                            $haserror   = true;
                                            self::addLastErrorMessages(self::getDefaultErrorMessage(6, ["@description"=>"{$iii} and index: {$i}"]));
                                            if(self::$strictMode){ $stop=true; break 3;}
                                            break 1;
                                        } else {
                                            $_myfields["$_key"] = $val;
                                        }
                                    } else {
                                        $haserror   = true;
                                        self::addLastErrorMessages(self::getDefaultErrorMessage(5, ["@description"=>"{$iii} and index: {$i}"]));
                                        if(self::$strictMode){ $stop=true; break 3;}
                                        break 1;
                                    }
                                }
                                if (count($_myfields) && strlen($mysql_magic)) {
                                    if (!$haserror ) {
                                        $pre_myfields   = array_merge($pre_myfields, $_myfields);
                                        $pre_sql_magic .= "(".rtrim($mysql_magic,",")."),";
                                    }
                                }
                            } else {
                                self::addLastErrorMessages(self::getDefaultErrorMessage(12, ["@sentence"=>"{$iii}","@index"=>"{$i}"]));
                                if(self::$strictMode){ $stop=true; break 2;}
                            }
                        }
                        if (count($pre_myfields) && strlen($pre_sql_magic)) {
                            $pre_sql_magic = "INSERT INTO {$table} (".implode(",", $fields).") values ".rtrim($pre_sql_magic,",").";";
                            switch ($version) {
                                case '1':
                                    $sql_magic .= $pre_sql_magic;
                                    $_fields    = array_merge($_fields, $pre_myfields);
                                case '2':
                                    $execs[] = ["sql"=>$pre_sql_magic,"fields"=>$pre_myfields];
                                break;
                                default:
                                    $query = $pdo->prepare($pre_sql_magic);
                                    $query->execute($pre_myfields);
                                    array_push($execs, 1);
                                break;
                            }
                        }
                    } else {
                        if(self::$strictMode){ $stop=true; break 1;}
                    } 
                }
                switch ($version) {
                    case '1':
                        if (strlen($sql_magic) && count($_fields) && !$stop) {
                            $d = self::exec(["sql"=>$sql_magic,"fields"=>$_fields,"pdo"=>$pdo,"backtrace"=>$backtrace,"private"=>true]);
                        }
                    break;
                    case '2':
                        if (count($execs) && !$stop) {
                            $d = self::execT(["execs"=>$execs,"pdo"=>$pdo,"backtrace"=>$backtrace,"private"=>true]);
                        }
                    break;
                    default:
                        if (count($execs) && !$stop) {
                            if (!$inTransaction) {
                                $d = $pdo->commit();
                            } else {
                                $d = true;
                            }
                        }
                    break;
                }                                
            } catch (Exception  $exception) {
                if ($version==3) {
                    $pdo->rollBack();
                }
                self::solveException(compact("exception", "backtrace"));
            }
        }
        return $d;
    }
    private static function evalDataInsertSentence($i ,$insert=null){
        $rsp = false;
        if (is_array($insert) && @count($insert)==3) {
            $table  = $insert[0];
            $fields = $insert[1];
            $data   = $insert[2];
            if (is_string($table) && is_array($fields) && is_array($data) && @strlen($table) && @count($fields) && @count($data)) {
                $rsp = compact("table", "fields", "data");
            } else {
                self::addLastErrorMessages( self::getDefaultErrorMessage(11,["@sentence"=>$i]) );
            }
        } else {
            self::addLastErrorMessages(self::getDefaultErrorMessage(10,["@length"=>3,"@params"=>"@params @table (String), @fields (Array) and @data (Array)","@sentence"=>$i]));
        }
        return $rsp;
    }
    /**
    * Evaluate if $params is an array and also if it contains an key called updates 
    *
    * @access private
    * @param string $icrudtype crud type id
    * @param string $method method's name
    * @param array $params parameter sent to the $method
    * @return array
    */
    private static function evalCRUDSentences($icrudtype, $method, $_params)
    {
        $rsp = false;
        if (is_array($_params)) {
            $params = [];
            foreach ($_params as $key => $p) {
                $params[ strtolower($key) ] = $p;
            }
            extract($params);
            $strCRUDType    = self::getCRUDType($icrudtype);
            $crudtype       = strtolower($strCRUDType)."s";
            //$CRUDTYPE = strtoupper($strCRUDType)."S";

            if (isset($$crudtype) && @is_array($$crudtype) && @count($$crudtype)) {
                $__params = [];
                $__params["{$crudtype}"] = $$crudtype;
                $__params["pdo"] = self::solvePDO($params);
                if ($params["pdo"]) {
                    $rsp   =  $__params;
                }
            } else {
                self::addLastErrorMessages(self::getDefaultErrorMessage(7,["@crudtype"=>$strCRUDType]));
            }
        } else {
            self::addLastErrorMessages(self::getDefaultErrorMessage(3,["@method"=>$method]));
        }
        return $rsp;
    }
    /**
     * Execute multiple insert queries
     * @param  $params  (Array("inserts"=>Array(Array(String,Array(),Array()),..), "pdo"=>PDO Object (*) ))
     * @return boolean
     */
    public function insertMassive($params=null)
    {
        $method     = "'".__FUNCTION__."'";
        @$backtrace = array_shift(debug_backtrace());
        $version    = 1;
        return self::getInsertData(compact("backtrace", "method", "version"), $params); 
    }
    /**
     * Execute multiple insert queries, TRANSACTIONAL
     * @param  $params  (Array("inserts"=>Array(Array(String,Array(),Array()),..), "pdo"=>PDO Object (*) ))
     * @return boolean
     */
    public function insertMassiveT($params=null)
    {
        $method     = "'".__FUNCTION__."'";
        @$backtrace = array_shift(debug_backtrace());
        $version    = 2;
        return self::getInsertData(compact("backtrace", "method", "version"), $params); 
    }
    /**
     * Execute multiple insert queries, TRANSACTIONAL v2
     * @param  $params  (Array("inserts"=>Array(Array(String,Array(),Array()),..), "pdo"=>PDO Object (*) ))
     * @return boolean
     */
    public function insertMassiveTv2($params=null)
    {
        $method     = "'".__FUNCTION__."'";
        @$backtrace = array_shift(debug_backtrace());
        $version    = 3;
        return self::getInsertData(compact("backtrace", "method", "version"), $params); 
    }
    /**
     * Execute multiple delete sentences
     * @param  $params  (Array("deletes"=>Array(Array(String,Array()),..), "pdo"=>PDO Object (*) ))
     * @return boolean
     */
    // ---------------------------------------------- END INSERT SECTION ----------------------------------------------

    // --------------------------------------------- BEGIN DELETE SECTION ---------------------------------------------
    public function delete($params, $version =3)
    {
        $version = (is_numeric($version) && in_array($version, self::$versions)) ? $version : self::$defaultVersion;
        $method  = "'".__FUNCTION__."'";
        @$backtrace = (isset($params["backtrace"])) ? $params["backtrace"] : array_shift(debug_backtrace());
        return self::getDeleteData(compact("backtrace", "method", "version"), $params);
    }
    // --------------------------------------------- END DELETE SECTION ---------------------------------------------

    public function getDeleteData($myself, $params)
    {
        extract($myself);
        $d = false;
        $method  = isset($method) ? $method : "'".__FUNCTION__."'";
        $backtrace = isset($backtrace) ? $backtrace :  @array_shift(debug_backtrace());
        $version   = isset($version) ? $version : self::$defaultVersion;
        $evalCRUDSentences    = self::evalCRUDSentences(2,$method, $params);
        if (is_array($evalCRUDSentences)) {
            extract($evalCRUDSentences);
            try {
                $sql_magic = "";
                $_fields = [];
                $stop = false;
                $execs = [];
                $inTransaction = $pdo->inTransaction();
                if ($version==3) {
                    if (!$inTransaction) {
                        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                        $pdo->beginTransaction();
                    }
                }
                foreach ($deletes as $i => $delete) {
                    $evalDataUpdateSentence = self::evalDataDeleteSentence($i, $delete);
                    if ( is_array($evalDataUpdateSentence) ) {
                        extract($evalDataUpdateSentence);
                        $_sql_magic = "";
                        $c          = 0;
                        $__fields   = [];
                        foreach ($data as $ii => $_d) {
                            if (is_array($_d) && @count($_d)) {
                                $haserror = false;
                                $_mysql_magic = "(";
                                $__myfields     = [];
                                foreach ($_d as $iii => $__d) {
                                    if (!is_numeric($iii)) {
                                        $iii = explode("|",$iii);
                                        $operator = "=";
                                        $field    = "";
                                        if (count($iii)>=2) {
                                            $field    = $iii[0];
                                            $operator = $iii[1];
                                        }else{
                                            $field    = $iii[0];
                                        }
                                        $_key               = "{$field}_{$i}_{$c}";
                                        $_mysql_magic         .= "{$field}{$operator}:$_key AND ";
                                        if (is_array($__d) || is_object($__d)) {
                                            $haserror = true;
                                            self::addLastErrorMessages(self::getDefaultErrorMessage(6, ["@description"=>"{$i} and index: {$ii}"]));
                                            if(self::$strictMode){ $stop=true; break 3;} 
                                            break 1;
                                        } else {
                                            $__myfields[$_key]     = $__d;
                                        }
                                    } else {
                                        $haserror = true;
                                        self::addLastErrorMessages(self::getDefaultErrorMessage(5, ["@description"=>"{$i} and index: {$ii}"]));
                                        if(self::$strictMode){ $stop=true; break 3;}
                                        break 1;
                                    }
                                }
                                if (!$haserror) {
                                    $__fields    = array_merge($__fields, $__myfields);
                                    $_sql_magic .= trim(rtrim(trim($_mysql_magic),"AND")).")OR";
                                    $c++;
                                }
                            } else {
                                self::addLastErrorMessages(self::getDefaultErrorMessage(14, ["@sentence"=>"{$i}","@index"=>"{$ii}"]));
                                if(self::$strictMode){ $stop=true; break 2;}
                            }
                        }

                        if (strlen($_sql_magic) && count($__fields)) {
                            $_sql_magic = "DELETE FROM {$table} WHERE ".rtrim($_sql_magic,"OR").";";
                            switch ($version) {
                                case '1':
                                    $sql_magic .= $_sql_magic;
                                    $_fields    = array_merge($_fields,$__fields);
                                break;
                                case '2':
                                    $execs[] = ["sql"=>$_sql_magic,"fields"=>$__fields];
                                break;
                                default:
                                    $query = $pdo->prepare($_sql_magic);
                                    $query->execute($__fields);
                                    array_push($execs, 1);
                                break;
                            }
                        }
                    }
                }
                switch ($version) {
                    case '1':
                        if (strlen($sql_magic) && !$stop) {
                            $d = self::exec(["sql"=>$sql_magic,"fields"=>$_fields,"pdo"=>$pdo,"backtrace"=>$backtrace,"private"=>true]);
                        }
                        break;
                    case '2':
                        if (count($execs) && !$stop) {
                            $d = self::execT(["execs"=>$execs, "pdo"=>$pdo]);
                        }
                        break;
                    default:
                        if ($execs && !$stop ) {
                            if (!$inTransaction) {
                                $d = $pdo->commit();
                            } else {
                                $d = true;
                            }
                        }
                    break;
                }
            } catch (Exception $exception) {
                if ($version==3) {
                    $pdo->rollBack();
                }
                self::solveException(compact("exception", "backtrace"));
            }    
        }
        return $d;
    }

    private static function evalDataDeleteSentence($i ,$delete=null)
    {
        $rsp = false;
        if (is_array($delete) && @count($delete)==2) {
            $table  = $delete[0];
            $data   = $delete[1];
            if (is_string($table) && is_array($data) && @strlen($table) && @count($data)) {
                $rsp = compact("table", "data");
            } else {
                self::addLastErrorMessages(self::getDefaultErrorMessage(13,["@sentence"=>$i]));
            }
        } else {
            self::addLastErrorMessages(self::getDefaultErrorMessage(10,["@length"=>2,"@params"=>"@table (String) and @data (Array)","@sentence"=>$i]));
        }
        return $rsp;
    }

    public function deleteMassive($params=null)
    {
        $method     = "'".__FUNCTION__."'";
        @$backtrace = array_shift(debug_backtrace());
        $version    = 1;
        return self::update(compact("backtrace", "method", "version"), $params);
    }

    public function deleteMassiveT($params=null)
    {
        $method     = "'".__FUNCTION__."'";
        @$backtrace = array_shift(debug_backtrace());
        $version    = 2;
        return self::update(compact("backtrace", "method", "version"), $params);
    }

    public function deleteMassiveTv2($params=null)
    {
        $method     = "'".__FUNCTION__."'";
        @$backtrace = array_shift(debug_backtrace());
        $version    = 3;
        return self::update(compact("backtrace", "method", "version"), $params);
    }



    // --------------------------------------------- BEGIN UPDATE SECTION ---------------------------------------------
     /**
     * TRANSACTIONAL AND NOT-TRANCTIONAL UPDATE METHOD
     *
     * @access public
     * @param Array $params Array(Array("updates"=>Array(Array("table",Array(Array("fields"=>"values"),Array("where"=>"value")),...),...)))
     * @param Numeric $version update version, default 3
     * @return Array
     */
    public static function update($params,$version=null)
    {   
        $version = (is_numeric($version) && in_array($version, self::$versions)) ? $version : self::$defaultVersion;
        $method  = "'".__FUNCTION__."'";
        @$backtrace = (isset($params["backtrace"])) ? $params["backtrace"] : array_shift(debug_backtrace());
        return self::getUpdateData(compact("backtrace", "method", "version"), $params);
    }
     /**
     * NOT-TRANCTIONAL UPDATE METHOD
     *
     * @access public
     * @param Array $params Array(Array("updates"=>Array(Array("table",Array(Array("fields"=>"values"),Array("where"=>"value")),...),...)))
     * @return Array
     */
    public static function updateMassive($params=null)
    {
        $method     = "'".__FUNCTION__."'";
        @$backtrace = array_shift(debug_backtrace());
        $version    = 1;
        return self::getUpdateData(compact("backtrace", "method", "version"), $params);
    }
     /**
     * TRANCTIONAL UPDATE METHOD
     *
     * @access public
     * @param Array $params Array(Array("updates"=>Array(Array("table",Array(Array("fields"=>"values"),Array("where"=>"value")),...),...)))
     * @return Array
     */
    public static function updateMassiveT($params=null)
    {
        $method     = "'".__FUNCTION__."'";
        @$backtrace = array_shift(debug_backtrace());
        $version    = 2;
        return self::getUpdateData(compact("backtrace", "method", "version"), $params);
    }
     /**
     * TRANCTIONAL UPDATE METHOD
     *
     * @access public
     * @param Array $params Array(Array("updates"=>Array(Array("table",Array(Array("fields"=>"values"),Array("where"=>"value")),...),...)))
     * @return Array
     */
    public static function updateMassiveTv2($params=null)
    {
        $method     = "'".__FUNCTION__."'";
        @$backtrace = array_shift(debug_backtrace());
        $version    = 3;
        return self::getUpdateData(compact("backtrace", "method", "version"), $params);
    }
    /**
     * MAIN UPDATE METHOD
     *
     * @access private
     * @param Array $myself Array("backtrace"=>"", "method"=>"", "version"=>"")
     * @param Array $params Array(Array("updates"=>Array(Array("table",Array(Array("fields"=>"values"),Array("where"=>"value")),...),...)))
     * @return Array
     */
    private static function getUpdateData($myself, $params)
    {   
        extract($myself);
        $d = false;
        $method  = isset($method) ? $method : "'".__FUNCTION__."'";
        $backtrace = isset($backtrace) ? $backtrace :  @array_shift(debug_backtrace());
        $version   = isset($version) ? $version : self::$defaultVersion;
        $evalCRUDSentences    = self::evalCRUDSentences(1,$method, $params);
        if (is_array($evalCRUDSentences)) {
            extract($evalCRUDSentences);
            $sql_magic = "";
            $_fields = [];  // v.1
            $_wheres = [];  // v.1
            $stop = false;
            $inTransaction = $pdo->inTransaction();
            try {
                $execs = []; // v.2, v.3
                if ($version==3) {
                    if (!$inTransaction) {
                        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                        $pdo->beginTransaction();
                    }
                }
                foreach ($updates as $i => $update) {
                    $evalDataUpdateSentence = self::evalDataUpdateSentence($i, $update);
                    if ( is_array($evalDataUpdateSentence) ) {
                        extract($evalDataUpdateSentence);
                        foreach ($dw as $ii => $_dw) {
                            $evalFieldsUpdateSentence = self::evalFieldsUpdateSentence($i, $ii, $_dw);
                                if (is_array($evalFieldsUpdateSentence)) {
                                    extract($evalFieldsUpdateSentence);
                                    $myfields = []; // fields for each sentence
                                    $mywheres = []; // wheres for each sentence
                                    $haserror = false;
                                    $mysql_magic = "UPDATE {$table} SET ";
                                    foreach ($fields as $iii => $val) {
                                        $fixOperatorUpdateSentence = self::fixOperatorUpdateSentence($i, $ii, $iii);
                                        if (is_array($fixOperatorUpdateSentence)) {
                                            extract($fixOperatorUpdateSentence);
                                            $_key      = "{$field}_{$i}_{$ii}";
                                            $mysql_magic .= "{$field}{$operator}:{$_key},";
                                            if (is_array($val) || is_object($val)) {
                                                $haserror = true;
                                                self::addLastErrorMessages(self::getDefaultErrorMessage(6, ["@description"=>"sentence: {$i} and index: {$ii}"]));
                                                if(self::$strictMode){ $stop=true; break 3;}
                                                break 1;
                                            } else {
                                                $myfields["{$_key}"] = $val;
                                            }
                                        } else {
                                            $haserror = true;
                                            if(self::$strictMode){ $stop=true; break 3;}
                                            break 1;
                                        }
                                    }
                                    $mysql_magic = rtrim($mysql_magic,",")." WHERE ";
                                    foreach ($wheres as $iiii => $val) {
                                        $fixOperatorUpdateSentence = self::fixOperatorUpdateSentence($i, $ii, $iiii);
                                        if (is_array($fixOperatorUpdateSentence)) {
                                            extract($fixOperatorUpdateSentence);
                                            $_key      = "{$field}_{$i}_{$ii}_w";
                                            $mysql_magic .= "{$field}{$operator}:{$_key} AND";
                                            if (is_array($val) || is_object($val)) {
                                                $haserror = true;
                                                self::addLastErrorMessages(self::getDefaultErrorMessage(6, ["@description"=>"sentence: {$i} and index: {$ii}"]));
                                                if(self::$strictMode){ $stop=true; break 3;} 
                                                break 1;
                                            } else {
                                               $mywheres["{$_key}"] = $val;
                                            }
                                        } else {
                                            $haserror = true;
                                            if(self::$strictMode){ $stop=true; break 3;}
                                            break 1;
                                        }
                                    }
                                    if (!$haserror) {
                                       $mysql_magic  = rtrim($mysql_magic,"AND").";";
                                       $sql_magic   .= $mysql_magic;
                                       $mix_myfields_mywheres = array_merge($myfields,$mywheres);

                                       switch ($version) {
                                           case '1': // v.1
                                               $_fields     = array_merge($_fields, $myfields);
                                               $_wheres     = array_merge($_wheres, $mywheres);
                                               break;
                                           case '2': // v.2
                                               $execs[] = ["sql"=>$mysql_magic,"fields"=>$mix_myfields_mywheres];
                                           break;
                                           default:
                                                $query = $pdo->prepare($mysql_magic);
                                                $query->execute($mix_myfields_mywheres);
                                                array_push($execs,1);
                                            break;
                                       }

                                    }
                                } else {
                                    if(self::$strictMode){ $stop=true; break 2;}
                                }
                            }
                        } else {
                            if(self::$strictMode){ $stop=true; break 1;}
                        }
                }
                switch ($version) {
                    case '1':
                        if (strlen($sql_magic) && !$stop) {
                            $d = self::exec(["sql"=>$sql_magic,"fields"=>array_merge($_fields,$_wheres),"pdo"=>$pdo,"backtrace"=>$backtrace,"private"=>true]);
                        }
                    break;
                    case '2':
                        if (count($execs) && !$stop) {
                            $d = self::execT(["execs"=>$execs,"pdo"=>$pdo,"backtrace"=>$backtrace,"private"=>true]);
                        }
                    break;
                    default:
                        if (count($execs) && !$stop) {
                            if (!$inTransaction) {
                                $d = $pdo->commit();
                            } else {
                                $d = true;
                            }
                        }
                    break;
                }
            } catch (Exception $exception) {
                if ($version==3) {
                    $pdo->rollBack();
                }
                self::solveException(compact("exception", "backtrace"));
            }
        }
        return $d;
    }
    // BEGIN UPDATE VALIDATIONS
     /**
     * Evaluate if $update is an array and if it contains 2 items, these items have to be a string and an array
     *
     * @access private
     * @param integer $i position of the update sentence
     * @param array $update update sentence
     * @return array
     */
    private static function evalDataUpdateSentence($i ,$update=null)
    {
        $rsp = false;
        if (is_array($update) && @count($update)==2) {
            $table = $update[0];
            $dw    = $update[1];
            if (is_string($table) && is_array($dw) && @strlen($table) && @count($dw)) {
                $rsp = compact("table", "dw");
            } else {
                self::addLastErrorMessages(self::getDefaultErrorMessage(13,["@sentence"=>$i]));
            }
        } else {
            self::addLastErrorMessages(self::getDefaultErrorMessage(10,["@length"=>2,"@params"=>"@table (String) and @data (Array)","@sentence"=>$i]));
        }
        return $rsp;
    }
     /**
     * Evaluate if $_dw has two arrays, an array $fields and an array $wheres
     *
     * @access private
     * @param integer $i position of the update sentence
     * @param integer $ii position of the index sentence
     * @param array $_dw fields and wheres
     * @return array
     */
    private static function evalFieldsUpdateSentence($i, $ii, $_dw)
    {
        $rsp = false;
        if ( is_array($_dw) && @count($_dw)==2) {
            $fields = $_dw[0];
            $wheres = $_dw[1];
            if (is_array($fields) && is_array($wheres) && @count($fields) && @count($wheres)) {
                $rsp = compact("fields", "wheres");
            } else {
                self::addLastErrorMessages(self::getDefaultErrorMessage(10,["@length"=>2,"@params"=>"@fields (Array)  must not be a empty Array and @wheres (Array) must not be a empty Array","@sentence"=>"{$i} and index {$ii}"]));
            }
        } else {
            self::addLastErrorMessages(self::getDefaultErrorMessage(10,["@length"=>2,"@params"=>"@fields (Array)  must not be a empty Array and @wheres (Array) must not be a empty Array","@sentence"=>"{$i} and index {$ii}"]));
        }
        return $rsp;
    }
     /**
     * Return and operator and field for the where sentence
     *
     * @access private
     * @param string $key field of where
     * @return array
     */
    private function fixOperatorUpdateSentence($i, $ii, $key)
    {   
        $rsp = false;
        if (!is_numeric($key)) {
            $key = explode("|",$key);
            $operator = "=";
            $field    = "";
            if(count($key)>=2){
                $field    = $key[0];
                $operator = $key[1];
            }else{
                $field    = $key[0];
            }
            $rsp = compact("field", "operator");
        } else {
            self::addLastErrorMessages(self::getDefaultErrorMessage(5, ["@description"=>"sentence: {$i} and index: {$ii}"]));
        }
        return $rsp;
    }
    // END UPDATE VALIDATIONS
    // --------------------------------------------- END UPDATE SECTION ---------------------------------------------

     /**
     * TRANSACTIONAL UPDATE INSERT AND DELETE
     *
     * @access public
     * @param Array $params (Array(
     *                   "deletes"=>[ 
     *                                  [
     *                                        "table",
     *                                       [ ["where"=>"value"] , ["where2"=>"value"],...]
     *                                   ], ...
     *                               ],
     *                   "updates"=>[ 
     *                                   [
     *                                       "table",
     *                                       [ 
     *                                           [ ["field"=>"value"],["where"=>"value"] ] , ...
     *                                       ]
     *                                   ], ...
     *                               ],
     *                   "inserts"=>[ 
     *                                   [
     *                                       "table",
     *                                       ["field1","field2"],
     *                                       [ ["field1"=>"value","field2"=>"value"] , ...]
     *                                  ], ...
     *                               ]  
     *                   "pdo"=>Object (*)
     *                   )
     *           )
     * @param Numeric $version update version, default 3
     * @return Array
     */
    public static function uid($params)
    {   
        $method  = "'".__FUNCTION__."'";
        @$backtrace = (isset($params["backtrace"])) ? $params["backtrace"] : array_shift(debug_backtrace());
        return self::iudMassiveTv2(compact("backtrace", "method"), $params);
    }
     /**
     * TRANSACTIONAL UPDATE INSERT AND DELETE
     *
     * @access public
     * @param Array $params
     * @param Numeric $version update version, default 3
     * @return Array
     */
    public static function iudMassiveTv2($myself, $params){
        extract($myself);
        $method  = isset($method) ? $method : "'".__FUNCTION__."'";
        $backtrace = isset($backtrace) ? $backtrace :  @array_shift(debug_backtrace());
        $d = false;
        $pdo = self::solvePDO($params);
        try {
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->beginTransaction();

            $evalCRUDSentences    = self::evalCRUDSentences(0,"{$method}", $params);
            if ($evalCRUDSentences) {
                extract($evalCRUDSentences);
                $insert = self::insert(compact("inserts","pdo","backtrace"));
            } else {
                $insert = true;
            }
            
            $evalCRUDSentences    = self::evalCRUDSentences(1,"{$method}", $params);
            if ($evalCRUDSentences) {
                extract($evalCRUDSentences);
                $update = self::update(compact("updates","pdo","backtrace"));
            } else {
                $update = true;
            }
            
            $evalCRUDSentences    = self::evalCRUDSentences(2,"{$method}", $params);
            if ($evalCRUDSentences) {
                extract($evalCRUDSentences);
                $delete = self::delete(compact("deletes","pdo","backtrace"));
            } else {
                $delete = true;
            }

            if ($insert && $update && $delete) {
                $d = $pdo->commit();
            }
        } catch (Exception  $exception) {
            $pdo->rollBack();
            self::solveException(compact("exception", "backtrace"));
        }
        return $d;
    }
}