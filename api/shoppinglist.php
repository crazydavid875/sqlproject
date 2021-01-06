<?php


$table = "shoppinglist";

if($route->getParameter(2)=="cart"){
    if($_SERVER['REQUEST_METHOD'] === 'GET'){//GET(SELECT),POST(INSERT),DELETE(DELETE),PATCH(UPDATE)
        
        $result = SelectCart($route->getParameter(3));
            
        
        http_response_code($result['code']);

        echo json_encode($result['value']);
        
    }
    else if($_SERVER['REQUEST_METHOD'] === 'POST'){
        $data = (array)json_decode(trim(file_get_contents('php://input'),"[]")) ;
        $result = InsertCart($data);
        http_response_code($result['code']);
        echo json_encode($result['value']);
        
    }
    else if($_SERVER['REQUEST_METHOD'] === 'PATCH'){
        $_PATCH =  (array)json_decode(trim(file_get_contents('php://input'),"[]")) ;
        $id = $route->getParameter(3);
        $result = UpdateCart($_PATCH,$id);

        
        http_response_code($result['code']);
        echo json_encode($result['value']);
    }
    else if($_SERVER['REQUEST_METHOD'] === 'DELETE'){
        if($route->getParameter(3)!=''){
            $where = "id = ".$route->getParameter(3);
            $result = DeleteCart($where);
            
            http_response_code($result['code']);
            echo json_encode($result['value']);
        }
        else{
            http_response_code(400);
            echo "please input id";
            
        }
        
    }
}
else {
    if($_SERVER['REQUEST_METHOD'] === 'GET'){//GET(SELECT),POST(INSERT),DELETE(DELETE),PATCH(UPDATE)
        
        $result = Select($route->getParameter(2));
            
        
        http_response_code($result['code']);

        echo json_encode($result['value']);
        
    }
    else if($_SERVER['REQUEST_METHOD'] === 'POST'){
        $data = (array)json_decode(trim(file_get_contents('php://input'),"[]")) ;
        $result = Insert($data);
        http_response_code($result['code']);
        echo json_encode($result['value']);
        
    }
    else if($_SERVER['REQUEST_METHOD'] === 'PATCH'){
        $_PATCH =  (array)json_decode(trim(file_get_contents('php://input'),"[]")) ;
        $id = $route->getParameter(2);
        $result = Update($_PATCH,$id);

        
        http_response_code($result['code']);
        echo json_encode($result['value']);
    }
    else if($_SERVER['REQUEST_METHOD'] === 'DELETE'){
        if($route->getParameter(2)!=''){
            $where = "id = ".$route->getParameter(2);
            $result = Delete($where);
            
            http_response_code($result['code']);
            echo json_encode($result['value']);
        }
        else{
            http_response_code(400);
            echo "please input id";
            
        }
        
    }
}
function SelectCart($id){
    global $sql;
    global $table;
    global $authmemberid;
    global $isManager;
    $response['code'] = 200;
    $response['value'] = '';
    $index = 0;
    $where = "shoppinglist.stateid=0 and memberid ='$authmemberid' ";
    if($id!=''){
        $where .= " AND havelist.id = $id  ";
    }
    $showData = "havelist.id,havelist.gameid,game.name,game.price,
    quantity";
    $query = "SELECT   $showData
    FROM havelist  
    LEFT JOIN shoppinglist ON havelist.shoppinglistId=shoppinglist.id 
    LEFT JOIN game ON game.id = havelist.gameid WHERE $where ";

    $result = $sql->query($query);
    
    if(!$result) {
        $response['value'] = $sql->error;
        $response['code']=400;
        return $response;
    }
    $response['value'] = [];
    while($row = $result->fetch_assoc()){
        $response['value'][$index] = $row;
        $index++;
    }
    
    if($index == 0){
        $response['code']=404;
        $response['value'] = "game not found";
    }
    
    return $response;
}
function InsertCart($data){
    global $sql;
    global $table;
    global $authmemberid;
    global $isManager;
    $response['code'] = 200;
    $response['value'] = '';
    $now =  date("Y-m-d H:i:s");
    $keys = array_keys($data);
    $keystr =  sprintf("`%s`\n",implode("`,`",$keys));
    $valstr =  sprintf("'%s'",implode("','",$data));   
    $result = $sql->query("SELECT * FROM shoppinglist where stateid = 0 and memberid = '$authmemberid'");
    if($result->num_rows<=0){
        $query = "INSERT INTO shoppinglist (stateid,buyDatetime,memberid) VALUES(0,'$now','$authmemberid')";
        $result = $sql->query($query);
        if(!$result) {
            $response['value'] = $sql->error;
            $response['code'] = 400;
            return $response;
        }
        
    }
    $query = "INSERT INTO havelist (shoppingListId,$keystr) 
    SELECT (SELECT id from shoppinglist WHERE stateid = 0 ),$valstr FROM DUAL";
    
    $result = $sql->query($query);
    if(!$result) {
        $response['value'] = $sql->error;
        $response['code'] = 400;
        return $response;
    }
    $response['value'] = $sql->insert_id;
    return $response;
}
function UpdateCart($data,$id){
    global $sql;
    global $table;
    $response['code'] = 200;
    $response['value'] = '';
    $keys = array_keys($data);
    $squence = [];
    for($i = 0;$i<count($keys);$i++){
        $squence[$i] = sprintf("`%s`='%s'",$keys[$i],$data[$keys[$i]]);
    }
    $str =  implode(",",$squence);
    
    $query = "UPDATE havelist SET $str where id=$id ";

    $result = $sql->query($query);
    if(!$result) {
        $response['value'] = $sql->error;
        $response['code'] = 400;
        return $response;
    }
    if($sql->affected_rows==0){
        $response['code'] = 200;
        $response['value'] ="not thing change";
    }
    
    return $response;
}

function DeleteCart($where){
    global $sql;
    global $table;
    $response['code'] = 200;
    $response['value'] = '';
    $result = $sql->query("DELETE FROM havelist WHERE $where");

    if(!$result) {
        $response['value'] = $sql->error;
        $response['code']=400;
        return $response;
    }
    if($sql->affected_rows==0){
        $response['code'] = 200;
        $response['value'] ="not thing change";
    }
    
    
    return $response;
}

function Select($id){
    global $sql;
    global $table;
    global $authmemberid;
    global $isManager;
    $response['code'] = 200;
    $response['value'] = '';
    $index = 0;
    $where = 'stateid <>0';
    if(!$isManager){
        $response['value'] = "you dont have permission";
        $response['code']=433;
    }
    if($id!=''){
        $where = "$table.id = ".$id;
    }
    $showData = "*";
    $query = "SELECT  $showData
    FROM shoppinglist  WHERE $where  ";
    
    $result = $sql->query($query);
    
    if(!$result) {
        $response['value'] = $sql->error;
        $response['code']=400;
        return $response;
    }
    $response['value'] = [];
    while($row = $result->fetch_assoc()){
        $response['value'][$index] = $row;
        $index++;
    }
    
    if($index == 0){
        $response['code']=404;
        $response['value'] = "list not found";
    }
    
    return $response;
}
function Insert($data){
    global $sql;
    global $table;
    global $authmemberid;
    global $isManager;
    $response['code'] = 200;
    $response['value'] = '';
    
    $keys = array_keys($data);
    $keystr =  sprintf("`%s`\n",implode("`,`",$keys));
    $valstr =  sprintf("'%s'",implode("','",$data));        
    $now =  date("Y-m-d H:i:s");
    if(isset($data['stateid'])&&$data['stateid']==1){
        $keystr.= ",buyDatetime";
        $valstr.= "'$now'";
    }
    $query = "INSERT INTO $table ($keystr,memberid) VALUES($valstr,'$authmemberid')";
    
    
    $result = $sql->query($query);
    if(!$result) {
        $response['value'] = $sql->error;
        $response['code'] = 400;
        return $response;
    }
    $response['value'] = $sql->insert_id;
    return $response;
}
function Update($data,$id){
    global $sql;
    global $table;
    $response['code'] = 200;
    $response['value'] = '';
    $keys = array_keys($data);
    $now =  date("Y-m-d H:i:s");
    $squence = [];
    for($i = 0;$i<count($keys);$i++){
        $squence[$i] = sprintf("`%s`='%s'",$keys[$i],$data[$keys[$i]]);
    }
    $str =  implode(",",$squence);
    if(isset($data['stateid'])&&$data['stateid']==1){
        $str.="datetime='$now'";
    }
    $query = "UPDATE $table SET $str where id=$id ";

    $result = $sql->query($query);
    if(!$result) {
        $response['value'] = $sql->error;
        $response['code'] = 400;
        return $response;
    }
    if($sql->affected_rows==0){
        $response['code'] = 200;
        $response['value'] ="not thing change";
    }
    
    return $response;
}

function Delete($where){
    global $sql;
    global $table;
    $response['code'] = 200;
    $response['value'] = '';
    $result = $sql->query("DELETE FROM $table WHERE $where");

    if(!$result) {
        $response['value'] = $sql->error;
        $response['code']=400;
        return $response;
    }
    if($sql->affected_rows==0){
        $response['code'] = 200;
        $response['value'] ="not thing change";
    }
    
    
    return $response;
}