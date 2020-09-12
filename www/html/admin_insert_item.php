<?php
require_once '../conf/const.php';
require_once MODEL_PATH . 'functions.php';
require_once MODEL_PATH . 'user.php';
require_once MODEL_PATH . 'item.php';

session_start();

if(is_logined() === false){
  redirect_to(LOGIN_URL);
}
//hidenで送られたトークンの取得
$token = get_post('token');
//トークンの照合チェック
//is_valid_csrf_token()でtrueかfalseが返ってくる
//trueの時はis_valid_csrf_token($token) === flseまで読み込まれるが、falseでは無いのでL17のif文の処理は行われずジャンプして下の次の処理に続いていく。
//falseのときはif文の処理が行われてリダイレクトされる
//この書き方でtrue,falseどちらも調べられている。
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

$name = get_post('name');
$price = get_post('price');
$status = get_post('status');
$stock = get_post('stock');

$image = get_file('image');

if(regist_item($db, $name, $price, $stock, $status, $image)){
  set_message('商品を登録しました。');
}else {
  set_error('商品の登録に失敗しました。');
}


redirect_to(ADMIN_URL);