<?php
/*
 * dbConn
 * SQL class
 * 
 */

class dbConn {
    static $charset = 'utf8';
    static $conn; // mysqli
    //protected $stmt;
    protected static $query_count = 0;
    protected static $db_host, $db_user, $db_pw, $db_name;
    public static $affected_rows;    
    public static $conn_count = 0;    
    private static $is_init = false;
    /*
     * __construct() - set DB connection properties to use on first query
     */
    public function __construct($db_host='localhost',$db_user='root',$db_pw=null,$db_name=null){
        self::$db_host = $db_host;
        self::$db_user = $db_user;
        self::$db_pw = $db_pw;
        self::$db_name = $db_name;
    }
    
    /*
     * Init function to establish credentials if this class is being used by calling the functions statically
     */
    private static function _init() {
        if (!self::$is_init) {
            self::$is_init = true;
            
            if(defined('DB_HOST') && defined('DB_USER') && defined('DB_PW') && defined('DB_NAME')){
                self::$db_host = DB_HOST;
                self::$db_user = DB_USER;
                self::$db_pw = DB_PW;
                self::$db_name = DB_NAME;
            }
        }
    }
    /*
     * Invoke protected functions without using dbConn as a new object
     */
    public static function __callStatic($method, $args) {
        self::_init();
        return self::$method(...$args);
    }    
    
    /*
     * db_connect() - connect to DB server
     * @returns 
     * mysqli object
     */
    public static function db_connect(){
        if(!self::$conn){
            //mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
            //mysqli_report(MYSQLI_REPORT_STRICT | MYSQLI_REPORT_ALL); // shows missing indexes
            mysqli_report(MYSQLI_REPORT_ALL & ~MYSQLI_REPORT_INDEX);

            //$conn = mysqli_connect(self::$db_host, self::$db_user, self::$db_pw, self::$db_name);
            $conn = new mysqli(self::$db_host, self::$db_user, self::$db_pw, self::$db_name);
            $conn->set_charset(self::$charset);

            // check connection - MOVE THIS TO ALTERNATE ERROR HANDLING
            if ($conn->connect_errno) {
                printf("Connect failed: %s\n", $conn->connect_error);
                exit();
            }
            
            self::$conn_count++;
            
            self::$conn = $conn;
        //} else {
            //trigger_error('Database connection already established', E_USER_NOTICE);
        }
        return self::$conn;
    }
    
    // ---------------------------------------------------------------------- //
    
    /* establish connection */
    /*
    public static function sql_start() {
        // establish connection to DB
        if($this->$mysqli){
            //mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
            //mysqli_report(MYSQLI_REPORT_STRICT | MYSQLI_REPORT_ALL); // shows missing indexes
            mysqli_report(MYSQLI_REPORT_ALL & ~MYSQLI_REPORT_INDEX);

            $mysqli = new mysqli(DB_HOST, DB_USER, DB_PW, DB_NAME);

            // set charset
            $mysqli->set_charset($this->charset);

            // check connection
            if ($mysqli->connect_errno) {
                printf("Connect failed: %s\n", $mysqli->connect_error);
                exit();
            }
        }
        $this->mysqli = $mysqli;
    }
    */
    
    /*
     * query_count() - return query count
     * @returns 
     * return $query_count int
     */
    public static function query_count(): int {
        return self::$query_count;
    }
    /*
     * last_id() - return last insert id
     */
    public static function last_id(): int {
        return self::$conn->insert_id;
    }
    /*
     * query() - query DB with parameterized or plain SQL if no parameters
     * @returns 
     * result in assoc array or if no results, the number of affected rows
     */
    //public function execute(array $values, ?string $types = null): mysqli_result {
    public static function query($sql, $params = [], $types = null){ //: mysqli_result {
        // get or establish connection
        $conn = self::db_connect();
        self::$query_count++;
        
        if($params){
            $stmt = $conn->prepare($sql);

            if(is_null($types)) {
                $types = str_repeat("s",count($params));
            }

            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $result = $stmt->get_result();
        } else {
            $result = $conn->query($sql);
        }
        
        //var_dump($result);
        
        /*
        // Get assoc array of results
        if($result and $result->num_rows > 0) {
            $results = [];
            while($row = $result->fetch_assoc()) {
                $results[] = $row;
            }
            return $results ?: null;
        } else {
            return $conn->affected_rows;
        }
        */
                
        // Get assoc array of results
        if($result){ // and $result->num_rows > 0){
            // Check whether $result is a mysqli::get_result() or mysqli::query that doesn't return
            // mysqli object on DELETE/etc
            //var_dump($result);exit;
            if(is_a($result,'mysqli_result')){
                if($result->num_rows > 0){
                    return $result->fetch_all(MYSQLI_ASSOC);                    
                } else {
                    //return null; // old function would return null on empty result set
                    return [];
                }
            } else {
                // query didn't produce a result set, such as SELECT, SHOW, DESCRIBE or EXPLAIN
                self::$affected_rows = $conn->affected_rows;
                return $conn->affected_rows;
            }
        } else {
            if($conn->errno){
                printf("Query error: %s<br/>", $conn->errno);
            } else {
                self::$affected_rows = $conn->affected_rows;
                return $conn->affected_rows;
            }
        }
        
        /*
        if($this->stmt){
            foreach($values as $k => $v){
                
            }
            */
            /*
            $this->stmt->bind_param( $ident , ...$vars );
            $this->parameters = $parameters;
            $this->stmt->execute();
            $result = $this->stmt->get_result();
            return $result;
            */
        /*
        } else {
            echo 'SQL Error in execute';
        }
        */
    }
    
    /*
     * Transactions
     */
    public static function begin_transaction(){
        self::$conn->begin_transaction();
    }
    
    public static function rollback(){
        self::$conn->rollback();
    }

    public static function commit(){
        self::$conn->commit();
    }
    
    /*
     * Return affected rows in last query
     */
    public function affected_rows(){
        return self::$conn->affected_rows;
    }
    
    public function selected_db(){
        return self::$db_name;
    }
    
    // for backwards compatibility with old code
    /*
    public function sql_query(string $sql, array $values){ //: ?array {
        $this->prepare($sql);
        return $this->execute($values);
    }
    */
    
    
    /* OLD ---- peform query */
    /*
    public function sql_query($query) {
        $mysqli = $this->mysqli;

        // place results in array 
        if($result = $mysqli->query($query)){
           $this->affected_rows = $mysqli->affected_rows;

            //if(is_a($result, 'mysqli') && $result->num_rows){
            if($result->num_rows){
               // fetch associative array
               for($results = array(); $row = $result->fetch_assoc(); $results[] = $row);

               $result->free();
               return $results;
           }
        } else {
            $this->mysql_error = $mysqli->error;
            echo 'SQL Error: <br/>'.$mysqli->error;
        }

    }
    */
}
