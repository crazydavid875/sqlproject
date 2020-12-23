<?php
class Query{
    private $sql;
    private $conn_IP = "localhost";
    private $conn_userName = "root",$conn_passwd = "1234";
    private $conn_db = "gameshop";
    private $errorMessage = "";

    public function __construct($sql){
        // $this->connection();
        $this->sql = $sql;
        
    }
    private function connection(){
        
        $this->sql = new mysqli($this->conn_IP,$this->conn_userName,$this->conn_passwd,$this->conn_db);
        if (mysqli_connect_errno()) {
            printf("Connect failed: %s\n", mysqli_connect_error());
            $this->errorMessage = mysqli_connect_error();
            exit();
        }
    }
    public function Select($table,$where){
        $sql = $this->sql;
        $response['code'] = 200;
        $response['value'] = '';
        $index = 0;
        $result = $sql->query("SELECT * FROM $table WHERE $where");

        if(!$result) {
            $this->errorOccur();
            $response['code']=400;
            return $response;
        }
        $response['value'] = [];
        while($row = $result->fetch_assoc()){
            $response['value'][$index] = $row;
            $index++;
        }
        
        if($index == 0)$response['code']=404;
        
        return $response;
    }
    public function Insert($table,$data){
        $sql = $this->sql;
        $response['code'] = 200;
        $response['value'] = '';
        $keys = array_keys($data);
        $keystr =  sprintf("`%s`\n",implode("`,`",$keys));
        $valstr =  sprintf("'%s'",implode("','",$data));        
        $query = "INSERT INTO $table ($keystr) VALUES($valstr)";
        $result = $sql->query($query);
        if(!$result) {
            $this->errorOccur();
            $response['code'] = 400;
            return $response;
        }
        $response['value'] = $sql->insert_id;
        return $response;
    }
    public function Update($table,$data,$id){
        $sql = $this->sql;
        $response['code'] = 200;
        $response['value'] = '';
        $keys = array_keys($data);
        $squence = [];
        for($i = 0;$i<count($keys);$i++){
            $squence[$i] = sprintf("`%s`='%s'",$keys[$i],$data[$keys[$i]]);
        }
        $str =  implode(",",$squence);
        $query = "UPDATE $table SET $str where id=$id ";

        $result = $sql->query($query);
        if(!$result) {
            $this->errorOccur();
            $response['code'] = 400;
            return $response;
        }
        if($sql->affected_rows==0){
            $response['code'] = 200;
            $response['value'] ="not thing change";
        }
        
        return $response;
    }
    
    public function Delete($table,$where){
        $sql = $this->sql;
        $response['code'] = 200;
        $response['value'] = '';
        $result = $sql->query("DELETE FROM $table WHERE $where");

        if(!$result) {
            $this->errorOccur();
            $response['code']=400;
            return $response;
        }
        if($sql->affected_rows==0){
            $response['code'] = 200;
            $response['value'] ="not thing change";
        }
        
        
        return $response;
    }
    public function ErrorMsg(){//to get errormessage outside
        return $this->errorMessage;
    }
    
    private function errorOccur(){//call when error occur
    
        $sql = $this->sql;
        $this->errorMessage = $sql->error;
        
    }

}