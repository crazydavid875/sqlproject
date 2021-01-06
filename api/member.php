<?php

$table = "member";


if($_SERVER['REQUEST_METHOD'] === 'GET'){//GET(SELECT),POST(INSERT),DELETE(DELETE),PATCH(UPDATE)
    
    $result = Select($route->getParameter(2));
    http_response_code($result['code']);
    echo json_encode($result['value']);
    
}
else if($_SERVER['REQUEST_METHOD'] === 'POST'){
    
    $data = (array)json_decode(trim(file_get_contents('php://input'),"[]")) ;
    if($route->getParameter(2)=='login'){
        $result = Login($data);
    }
    else    $result = Insert($data);
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
function Select($id){
    global $sql;
    global $table;
    $response['code'] = 200;
    $response['value'] = '';
    $index = 0;
    $where = '1';
    if($id!=''){
        $where = "id = ".$id;
    }
    
    $result = $sql->query("SELECT *  
    FROM $table  WHERE $where ");
    
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
function Login($data){
    global $sql;
    global $table;
    $response['code'] = 200;
    $response['value'] = '';
    $index = 0;

    $where =" account = '".$data['uid']."'";

    
    $result = $sql->query("SELECT id,isManager  
    FROM $table  WHERE $where ");
    
    if(!$result) {
        $response['value'] = $sql->error;
        $response['code']=400;
        return $response;
    }
    $response['value'] = [];
    if($row = $result->fetch_assoc()){
        
        $response['value'] = $row;
        $response['code']=200;
        $index++;
    }

    if($index == 0){
        $uid = $data["uid"];
        $query = "INSERT INTO $table (account,ismanager) VALUES('$uid',0)";
        $result = $sql->query($query);
        $response['value'][0]['id'] = $sql->insert_id;
        $response['value'][0]['isManager'] = false;
    }
    
    return $response;
}
function Insert($data){
    global $sql;
    global $table;
    $response['code'] = 200;
    $response['value'] = '';
    
    $keys = array_keys($data);
    $keystr =  sprintf("`%s`\n",implode("`,`",$keys));
    $valstr =  sprintf("'%s'",implode("','",$data));        
    $query = "INSERT INTO $table ($keystr) VALUES($valstr)";
    
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
    $squence = [];
    for($i = 0;$i<count($keys);$i++){
        $squence[$i] = sprintf("`%s`='%s'",$keys[$i],$data[$keys[$i]]);
    }
    $str =  implode(",",$squence);
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