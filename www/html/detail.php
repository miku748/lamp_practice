<?php
require_once '../conf/const.php';
require_once MODEL_PATH . 'purchase.php';
require_once MODEL_PATH . 'functions.php';
require_once MODEL_PATH . 'user.php';

session_start();

if(is_logined() === false){
  redirect_to(LOGIN_URL);
}

//PDOを取得
$db = get_db_connect();

//ログインしているユーザーを識別して、その人のusersテーブルの情報を取得して、返す。
$user = get_login_user($db);

//user取ってきて、if文でログイン中のユーザーと今から表示させようとする注文番号のユーザーが同じかどうかチェックするif($user['type'] === )  ?
//POST送信された、注文番号、購入日時、合計金額を取得
$purchase_id = get_post('purchase_id');
$created = get_post('created');
$total = get_post('total');

$detail = get_detail($db, $purchase_id);

//POST送信されたトークンの取得
$token = get_post('token');

//トークンの照合
if(is_valid_csrf_token($token) === false){
  redirect_to(LOGIN_URL);
}

//トークンの破棄
unset($_SESSION['csrf_token']);

include_once '../view/detail_view.php';