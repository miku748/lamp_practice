<?php
require_once '../conf/const.php';
require_once MODEL_PATH . 'functions.php';
require_once MODEL_PATH . 'user.php';
require_once MODEL_PATH . 'item.php';
require_once MODEL_PATH . 'cart.php';
require_once MODEL_PATH . 'purchase.php';

session_start();

if(is_logined() === false){
  redirect_to(LOGIN_URL);
}

//トークンの取得
$token = get_post('token');

//トークンの照合
if(is_valid_csrf_token($token) === false){
  redirect_to(LOGIN_URL);
}

//トークンの破棄
unset($_SESSION['csrf_token']);

$db = get_db_connect();
//ログインしているユーザーを識別して、その人のusersテーブルの情報を取得して、返す。
$user = get_login_user($db);
//cartとitemのuserid結合テーブルからログイン中のuserの情報を取得
$carts = get_user_carts($db, $user['user_id']);

//カート内の個数と在庫個数のチェック、在庫数の変更、cartsテーブルから引数がuser_idのレコードを削除する
if(purchase_carts($db, $carts) === false){
  set_error('商品が購入できませんでした。');
  redirect_to(CART_URL);
} 

$total_price = sum_carts($carts);

//購入履歴、購入明細のデータ保存
//user_idとtotalを取得して、それを引数に渡してpurchaseテーブルにinsertするの関数を作る。
//purchaseテーブル書いたらlastinsertID?をして挿入されたpurchase_idを取得する。
//L29の$cartの中から、item_id,price,amountを取得して、それを引数に渡して、detailテーブルにinsertする関数を作る。
if(regist_purchase_transaction($db, $user, $carts, $total_price) === false){
  set_error('購入履歴を登録できませんでした。');
}
include_once '../view/finish_view.php';