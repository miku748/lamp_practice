<?php
require_once '../conf/const.php';
require_once MODEL_PATH . 'functions.php';
require_once MODEL_PATH . 'user.php';
require_once MODEL_PATH . 'item.php';

session_start();

if(is_logined() === false){
  redirect_to(LOGIN_URL);
}

$db = get_db_connect();
//ログインしているユーザーを識別して、その人のusersテーブルの情報を取得して、返す。
$user = get_login_user($db);
//itemsテーブルのステータスが１のレコードを全て取得
$items = get_open_items($db);

//トークンの生成
$token = get_csrf_token();

include_once VIEW_PATH . 'index_view.php';