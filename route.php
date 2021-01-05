<?php
$testMode = FALSE;
$authmemberid  = -1;
$isManager = false;
$route = new Router(Request::uri()); //搭配 .htaccess 排除資料夾名稱後解析 URL
$route->getParameter(1); // 從 http://127.0.0.1/game/aaa/bbb 取得 aaa 字串之意

// 用參數決定載入某頁並讀取需要的資料
switch($route->getParameter(1)){
    case "game":
        if($route->getParameter(2) == "search")include('api/gamesearch.php');
        else   include('api/game.php');
        break;
    case "reply":
    
        include('api/reply.php');
        break;
    case "review":

        include('api/review.php');
        break;
    case "tag":
        include('api/tag.php');
        break;
    case "member":
        include('api/member.php');
        break;
    case "wishlist":

        include('api/wishlist.php');
        break;
    case "shoppinglist":
        
        include('api/shoppinglist.php');
        break;
    case "shoppingliststate":

        include('api/shoppingliststate.php');
        break;
    default:
        include('api/default.php'); 
        break;
}
function CheckAuth(){
    global $testMode;
    global $authmemberid;
    if($testMode) return true;
    if (!isset($_SERVER['PHP_AUTH_USER'])||!isset($_SERVER['PHP_AUTH_PW'])) {
        http_response_code(410);
        echo "auth no set";
        return false;
    }
    else {
        $where ="account = ".$_SERVER['PHP_AUTH_USER'].
            "and email = ".$_SERVER['PHP_AUTH_PW'];
        $result = $sql->query("SELECT * FROM $table  WHERE $where ");
        if(!$result) {
            http_response_code(411);
            echo "auth result error";
            return false;
        }

        if($row = $result->fetch_assoc()){
            $authmemberid = $row["id"];
            $index++;
        }
        
        if($index == 0){
            http_response_code(412);
            echo "auth member not found";
            return false;
        }
        return true;
    }
}