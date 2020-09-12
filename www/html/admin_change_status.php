<?php
//定数ファイルの読み込み
require_once '../conf/const.php';
//modelフォルダのfunction.phpの読み込み
require_once MODEL_PATH . 'functions.php';
//modelファイルのuser.phpの読み込み
require_once MODEL_PATH . 'user.php';
//modelファイルのitem.phpの読み込み
require_once MODEL_PATH . 'item.php';

//ログインチェックを行う為、セッションを開始する
session_start();

//ログインチェック用関数を利用
if(is_logined() === false){
  //ログインしていない場合はログインページにリダイレクト
  redirect_to(LOGIN_URL);
}

//トークンの取得
$token = get_post('token');

//トークンの照会チェック
if(is_valid_csrf_token($token) === false){
  redirect_to(LOGIN_URL);
}

//トークンの破棄
unset($_SESSION['csrf_token']);

//PODを取得
$db = get_db_connect();

//PDOを取得してログインユーザーのデータを取得
//ログインしているユーザーを識別して、その人のusersテーブルの情報を取得して、返す。
$user = get_login_user($db);

//ユーザーのタイプか１かどうかチェックしている？1ならTRUE、そうでなければfalseを返す
if(is_admin($user) === false){
  redirect_to(LOGIN_URL);
}

//POST送信されたitem_idを取得
$item_id = get_post('item_id');
//POST送信されたchanges_toを取得
$changes_to = get_post('changes_to');

//$changes_toで取得した値がopenのとき
if($changes_to === 'open'){
  //ステータスのアップデートを行う
  update_item_status($db, $item_id, ITEM_STATUS_OPEN);
  set_message('ステータスを変更しました。');
  //$changes_toで取得した値がcloseのとき
}else if($changes_to === 'close'){
  //ステータスのアップデートを行う
  update_item_status($db, $item_id, ITEM_STATUS_CLOSE);
  set_message('ステータスを変更しました。');
}else {
  set_error('不正なリクエストです。');
}


redirect_to(ADMIN_URL);