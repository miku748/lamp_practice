<?php
require_once '../conf/const.php';
require_once MODEL_PATH . 'functions.php';
require_once MODEL_PATH . 'user.php';
require_once MODEL_PATH . 'item.php';
require_once MODEL_PATH . 'cart.php';

session_start();

if(is_logined() === false){
  redirect_to(LOGIN_URL);
}

$db = get_db_connect();
//ログインしているユーザーを識別して、その人のusersテーブルの情報を取得して、返す。
$user = get_login_user($db);
//cartとitemのuserid結合テーブルからログイン中のuserの情報を取得
$carts = get_user_carts($db, $user['user_id']);
//カート内の商品合計金額
$total_price = sum_carts($carts);

//トークンの作成
$token = get_csrf_token();

include_once VIEW_PATH . 'cart_view.php';