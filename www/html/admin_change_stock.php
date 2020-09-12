<?php
require_once '../conf/const.php';
require_once MODEL_PATH . 'functions.php';
require_once MODEL_PATH . 'user.php';
require_once MODEL_PATH . 'item.php';

session_start();

if(is_logined() === false){
  redirect_to(LOGIN_URL);
}

//hiddenで送られてきた$tokenの取得
$token = get_post('token');

//トークンの照合チェック
if(is_valid_csrf_token($token) === false){
  redirect_to(LOGIN_URL);
}

//トークンの破棄
unset($_SESSION['csrf_token']);


$db = get_db_connect();
//ログインしているユーザーを識別して、その人のusersテーブルの情報を取得して、返す。
$user = get_login_user($db);

//ユーザーのタイプか１かどうかチェックしている？1ならTRUE、そうでなければfalseを返す
if(is_admin($user) === false){
  redirect_to(LOGIN_URL);
}

$item_id = get_post('item_id');
$stock = get_post('stock');

if(update_item_stock($db, $item_id, $stock)){
  set_message('在庫数を変更しました。');
} else {
  set_error('在庫数の変更に失敗しました。');
}

redirect_to(ADMIN_URL);