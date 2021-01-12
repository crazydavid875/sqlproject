<?php

$table = "game";


if($_SERVER['REQUEST_METHOD'] === 'GET'){//GET(SELECT),POST(INSERT),DELETE(DELETE),PATCH(UPDATE)
    
    $result = Select($route->getParameter(2));
    
    if($route->getParameter(3)=='price'){
        http_response_code($result['code']);
        include("googlechart.php");
        $chart = new GooglePieChart(500,200);
        $data = $result['value']['price'];
        $labels =  $result['value']['price'];
        $legends =$result['value']['gamename'];
        $chart->setData($data,$labels,$legends);
        $chart->draw();
    }
    else{
        http_response_code($result['code']);
        include("googlechart.php");
        $chart = new GooglePieChart(500,200);
        $data = $result['value']['soldnum'];
        $labels =  $result['value']['soldnum'];
        $legends =$result['value']['gamename'];
        $chart->setData($data,$labels,$legends);
        $chart->draw();
    }
    
    
    //echo json_encode($result['value']);
    
}
function Select($method){
    global $sql;
    global $table;
    global $authmemberid;
    global $isManager;
    $response['code'] = 200;
    $response['value'] = '';
    $index = 0;
    $where = 1;
    if($method=='day'){
        $where = "date(buyDatetime) = date(now()) ";
    }
    
    $query = "SELECT game.id,game.name,price,picture,
    sum(havelist.quantity)  as soldOutNumber ,
    sum(havelist.quantity)*price as soldprice 
    FROM $table   
     join havelist on havelist.gameid=game.id
    left join shoppinglist on havelist.shoppinglistid=shoppinglist.id
    WHERE $where 
    group by game.id
    order by soldOutNumber desc";
    
    $result = $sql->query($query);
    
    if(!$result) {
        $response['value'] = $sql->error;
        $response['code']=400;
        return $response;
    }
    $response['value'] = [];
    $response['value']['soldnum']=[];
    $response['value']['gamename']=[];
    $response['value']['price'] = [];
    while($row = $result->fetch_assoc()){
        $response['value']['soldnum'][$index] = $row['soldOutNumber'];
        $response['value']['price'][$index] = $row['soldprice'];
        $response['value']['gamename'][$index] = $row['name'];
        $index++;
    }
    
    if($index == 0){
        $response['code']=404;
        $response['value'] = "game not found";
    }
    
    return $response;
}