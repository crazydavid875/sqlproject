<?php

$table = "game";

/*
GET
"./game" => select all
"./game/{game_id} => select id = game_id
POST
"./game" => INSERT
*/
if($_SERVER['REQUEST_METHOD'] === 'GET'){//GET(SELECT),POST(INSERT),DELETE(DELETE),PATCH(UPDATE)
    $where = '1';
    if($route->getParameter(2)!=''){
        $where = "id = ".$route->getParameter(2);
    }

    $result = $query->Select($table,$where);
    $error = $query->ErrorMsg();
    http_response_code($result['code']);
    if($result['code']==200){
        echo json_encode($result['value']);
    }
    else{
        echo $error;
    }
}
else if($_SERVER['REQUEST_METHOD'] === 'POST'){
    
    $result = $query->Insert($table,$_POST);
    
    $error = $query->ErrorMsg();
    http_response_code($result['code']);
    if($result['code']==200){
        echo json_encode($result['value']);
    }
    else{
        echo $error;
    }
    
}
else if($_SERVER['REQUEST_METHOD'] === 'PATCH'){
    $_PATCH = (array)json_decode(file_get_contents('php://input')) ;
    $id = $route->getParameter(2);
    $result = $query->Update($table,$_PATCH,$id);
    
    $error = $query->ErrorMsg();
    http_response_code($result['code']);
    if($result['code']==200){
        echo json_encode($result['value']);
    }
    else{
        echo $error;
    }
}
else if($_SERVER['REQUEST_METHOD'] === 'DELETE'){
    if($route->getParameter(2)!=''){
        $where = "id = ".$route->getParameter(2);
        $result = $query->Delete($table,$where);
        $error = $query->ErrorMsg();
        http_response_code($result['code']);
        if($result['code']==200){
            echo json_encode($result['value']);
        }
        else{
            echo $error;
        }
    }
    else{
        http_response_code(400);
        echo "please input id";
        
    }
    
}