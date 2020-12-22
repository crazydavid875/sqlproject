<?php

$route = new Router(Request::uri()); //搭配 .htaccess 排除資料夾名稱後解析 URL
$route->getParameter(1); // 從 http://127.0.0.1/game/aaa/bbb 取得 aaa 字串之意
$query = new Query($sql);
// 用參數決定載入某頁並讀取需要的資料
switch($route->getParameter(1)){
    case "game":
        include('api/game.php');
        break;
    case "reply":
        include('api/reply.php');
        break;
    case "review":
        include('api/review.php');
        break;
    default:
        include('api/default.php'); 
        break;
}