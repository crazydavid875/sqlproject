<?php


$table = "game";


if($_SERVER['REQUEST_METHOD'] === 'GET'){//GET(SELECT),POST(INSERT),DELETE(DELETE),PATCH(UPDATE)
    $method = $route->getParameter(2);

    $result = searchSpecial($route->getParameter(2),$route->getParameter(3));
    
    http_response_code($result['code']);

    echo json_encode($result['value']);
    
}
else if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $data = (array)json_decode(trim(file_get_contents('php://input'),"[]")) ;
    $result = searchname($data);
    http_response_code($result['code']);
    echo json_encode($result['value']);
    
}
function searchSpecial($method,$count){
    global $sql;
    global $table;
    $response['code'] = 200;
    $response['value'] = '';
    $index = 0;
    $limt = '';
    $order = '';
    $where = '1';
    if($method=='hot'){
        $order = "order by soldOutNumber desc";
    }
    if($method=='recommend'){
        $where = "recommend = 1";
    }
    if($method=='starest'){
        $order = "order by star desc";
    }
    if($count!=''){
        $limt = "limit ".$count;
    }
    
    $result = $sql->query("SELECT game.id,game.name,price,picture,description,tag.name as tag,
    COALESCE(sum(havelist.quantity),0)  as soldOutNumber ,
    COALESCE(TRUNCATE(avg(review.star),1),0)  as star
    FROM $table   
    JOIN tag ON game.tagId=tag.id 
    LEFT OUTER join havelist on havelist.gameid=game.id
    left OUTER join review on review.gameid = game.id 
    WHERE $where 
    group by game.id $order $limt");
    
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

function searchname($data){
    global $sql;
    global $table;
    $response['code'] = 200;
    $response['value'] = '';
    $index = 0;
    $where = '';
    if(!isset($data['name']) && !isset($data['tag']) ){
        $where = '1';
    }
    else{
        $where = [];
        if(isset($data['name'])){
            $where[0] = dename($data['name']);
        }
        if(isset($data['tag'])){
            
            $tag =implode(',',$data['tag']);
            $where[1] = " tagid in ($tag)"; 
            
        }
        $where = implode(" and ",$where);
    }
    
    $result = $sql->query("SELECT game.id,game.name,price,picture,description,tag.name as tag,
    COALESCE(sum(havelist.quantity),0)  as soldOutNumber ,
    COALESCE(TRUNCATE(avg(review.star),1),0)  as star
    FROM $table   
    JOIN tag ON game.tagId=tag.id 
    LEFT OUTER join havelist on havelist.gameid=game.id
    left OUTER join review on review.gameid = game.id 
    WHERE $where 
    group by game.id ");
    
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
function dename($name){
    $where = [];
        $name = explode(' ',$name);
        for($i = 0;$i<count($name);$i++){
            $iname = explode('+',$name[$i]);
            if(count($iname)>1){
                $and =[];
                for($j = 0;$j<count($iname);$j++){
                    $andname = $iname[$j];
                    $and[$j] = "game.name like '%$andname%'";
                }
                $where[$i] = implode(" and ",$and);
            }
            else
                $where[$i] = "game.name like '%".$iname[0]."%'";
        }
        return implode(" or ",$where);
}