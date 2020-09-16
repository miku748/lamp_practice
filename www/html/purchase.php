<?php
require_once '../conf/const.php';
require_once MODEL_PATH . 'user.php';
require_once MODEL_PATH . 'purchase.php';

session_start();

if(is_logined() === false){
  redirect_to(LOGIN_URL);
}



$db = get_db_connect();

//ログインしているユーザーを識別して、その人のusersテーブルの情報を取得して、返す。
$user = get_login_user($db);

//purchaseテーブルを取得する
$purchase = get_purchase($db, $user);

//トークン作成
// このget_csrf_token()では戻り値$tokenが返される。そしたらそれを受け止める変数が必要。$token作ってそれに入れてあげる。
$token = get_csrf_token();

include_once '../view/purchase_view.php';