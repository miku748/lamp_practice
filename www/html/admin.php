<?php
//定数ファイルの読み込み
require_once '../conf/const.php';
//modelフォルダのfunction.phpファイルの読み込み
require_once MODEL_PATH . 'functions.php';
//modelフォルダのuser.phpを読み込み
require_once MODEL_PATH . 'user.php';
//modelフォルダのitem.phpを読み込み
require_once MODEL_PATH . 'item.php';

//ログインチェックを行う為、セッションを開始する
session_start();

//ログインチェック用関数を利用
if(is_logined() === false){
  //ログインしていない場合はログインページにリダイレクト
  redirect_to(LOGIN_URL);
}

//PDOを取得
$db = get_db_connect();

//PDOを取得してログインユーザーのデータを取得
$user = get_login_user($db);


if(is_admin($user) === false){
  redirect_to(LOGIN_URL);
}

//商品一覧用の商品データを取得
$items = get_all_items($db);
//viewフォルダのadmin.phpの読み込み
include_once VIEW_PATH . '/admin_view.php';
