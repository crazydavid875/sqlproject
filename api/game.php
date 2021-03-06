<?php

$table = "game";


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
function Select($game_id){
    global $sql;
    global $table;
    global $authmemberid;
    global $isManager;
    $response['code'] = 200;
    $response['value'] = '';
    $index = 0;

    $where = '1';
    if($game_id!=''){
        $where = "game.id = ".$game_id;
    }
    $hasGame = "";
    if(!$isManager){
        $hasGame = ", (SELECT EXISTS(SELECT havelist.gameid 
        FROM havelist   
        join shoppinglist on shoppinglist.id=havelist.shoppinglistid
        join shoppingliststate on shoppingliststate.id = shoppinglist.stateid
        WHERE havelist.gameid ='$game_id' and shoppinglist.memberid = '$authmemberid' 
        and shoppingliststate.name='訂單完成' ) ) as hasGame";
    }
    $query = "SELECT game.id,game.name,price,picture,description,COALESCE(tag.name,'') as tag,
    COALESCE(sum(havelist.quantity),0)  as soldOutNumber $hasGame,
    COALESCE(round(avg(review.star),1),0)  as star,game.recommend
    FROM $table   
    left JOIN tag ON game.tagId=tag.id 
    LEFT OUTER join havelist on havelist.gameid=game.id
    left OUTER join review on review.gameid = game.id 
    WHERE $where 
    group by game.id";
    
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
    global $sql;
    global $table;
    $response['code'] = 200;
    $response['value'] = '';
    if(isset($data["tag"])){
        $tag =  $data['tag']; 
        $query = "INSERT INTO tag (name)
        SELECT '$tag'    
        WHERE NOT EXISTS(SELECT name
        FROM tag tg
        WHERE tg.name = '$tag')";
        $result = $sql->query($query);
        $queryselect = "SELECT id from tag where name='$tag'";
        $result = $sql->query($queryselect);
        $row= $result->fetch_assoc();
        $data["tag"] = $row["id"];
        $data = replace_key($data, "tag", "tagid");
    }
    $keys = array_keys($data);
    $keystr =  sprintf("`%s` ",implode("`,`",$keys));
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
function replace_key($array, $old_key, $new_key) {
    $keys = array_keys($array);
    if (false === $index = array_search($old_key, $keys)) {
        throw new Exception(sprintf('Key "%s" does not exit', $old_key));
    }
    $keys[$index] = $new_key;
    return array_combine($keys, array_values($array));
}
function Update($data,$id){
    global $sql;
    global $table;
    $response['code'] = 200;
    $response['value'] = '';
    if(isset($data["tag"])){
        $tag =  $data['tag']; 
        $query = "INSERT INTO tag (name)
        SELECT '$tag'    
        WHERE NOT EXISTS(SELECT name
        FROM tag tg
        WHERE tg.name = '$tag')";
        $result = $sql->query($query);
        $queryselect = "SELECT id from tag where name='$tag'";
        $result = $sql->query($queryselect);
        $row= $result->fetch_assoc();
        $data["tag"] = $row["id"];
        $data = replace_key($data, "tag", "tagid");
    }
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