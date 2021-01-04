
<?php

$table = "game";


if($_SERVER['REQUEST_METHOD'] === 'GET'){//GET(SELECT),POST(INSERT),DELETE(DELETE),PATCH(UPDATE)
    $result = Select($route->getParameter(3));
    
    http_response_code($result['code']);

    echo json_encode($result['value']);
    
}
function Select($game_id){
    global $sql;
    global $table;
    $response['code'] = 200;
    $response['value'] = '';
    $index = 0;
    $where = '1';
    if($game_id!=''){
        $where = "game.id = ".$game_id;
    }
    
    $result = $sql->query("SELECT game.id,game.name,soldOutNumber,price,picture,description,tag.name as tag 
    FROM $table JOIN tag ON game.tagId=tag.id WHERE $where ");
    
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