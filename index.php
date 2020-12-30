<?php
session_start();
require('class/Router.php'); 
require('class/Request.php');

require('connection/connect.php');
require('route.php');   // 路由: 決定要去哪一頁，讀取該頁面需要的資料組合介面