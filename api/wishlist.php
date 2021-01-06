<?php


$table = "wishlist";


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
function Select($id){
    global $sql;
    global $table;
    global $authmemberid;
    global $isManager;
    $response['code'] = 200;
    $response['value'] = '';
    $index = 0;
    $where = "memberid ='$authmemberid'";
    if($id!=''){
        $where = "$table.id = ".$id;
    }
    $showData = "$table.id,gameid,game.name,game.price,game.picture
    ,game.description,game.tagid,tag.name as tag,saveDatetime";
    $query = "SELECT   $showData
    FROM wishlist 
    LEFT JOIN game ON game.Id=wishlist.gameid 
    LEFT JOIN tag ON game.tagId = tag.id WHERE $where ";
    
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
function Insert($data){
    global $authmemberid;
    global $isManager;
    global $sql;
    global $table;
    $response['code'] = 200;
    $response['value'] = '';
    
    $keys = array_keys($data);
    $keystr =  sprintf("`%s`\n",implode("`,`",$keys));
    $valstr =  sprintf("'%s'",implode("','",$data));        
    $now =  date("Y-m-d H:i:s");
    $query = "INSERT INTO $table ($keystr,saveDatetime,memberid) VALUES($valstr,$now,'$authmemberid')";
    
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
    $now =  date("Y-m-d H:i:s");
    for($i = 0;$i<count($keys);$i++){
        $squence[$i] = sprintf("`%s`='%s'",$keys[$i],$data[$keys[$i]]);
    }
    $str =  implode(",",$squence).",datetime='$now'";
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