<?php

function get_db_connect(){
  // MySQL用のDSN文字列
  $dsn = 'mysql:dbname='. DB_NAME .';host='. DB_HOST .';charset='.DB_CHARSET;
 
  try {
    // データベースに接続
    $dbh = new PDO($dsn, DB_USER, DB_PASS, array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4'));
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    $dbh->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
  } catch (PDOException $e) {
    exit('接続できませんでした。理由：'.$e->getMessage() );
  }
  return $dbh;
}
//１行のレコード行を取得
function fetch_query($db, $sql, $params = array()){
  try{
    //SQL文を実行する準備
    $statement = $db->prepare($sql);
    //SQL文を実行
    $statement->execute($params);
    //レコードの取得 fetch(1行ずつ取得)fetchだと単なる配列
    return $statement->fetch();
  }catch(PDOException $e){
    //$_SESSION['__erros'][] = データ取得に失敗しました。をセッション変数に登録。
    set_error('データ取得に失敗しました。');
  }
  return false;
}
//全てのレコード行を取得
function fetch_all_query($db, $sql, $params = array()){
  try{
    //SQL文を実行する準備
    $statement = $db->prepare($sql);
    //SQL文を実行
    $statement->execute($params);
    //レコードの取得  fetchAll(全データを配列に変換)fetchAllだと配列の配列になったりする
    return $statement->fetchAll();
  }catch(PDOException $e){
    //$_SESSION['__errors'][] = データ取得に失敗しました。 をセッション変数に登録。
    set_error('データ取得に失敗しました。');
  }
  return false;
}
//SQL文の実行のみ  レコードの取得なし関数
function execute_query($db, $sql, $params = array()){
  try{
    //SQL文を実行する準備
    $statement = $db->prepare($sql);
    //SQL文を実行
    //実行結果をそのまま返す
    return $statement->execute($params);
  }catch(PDOException $e){
    //$_SESSION['__errors'][] = 更新に失敗しました。  セッション変数を登録。
    set_error('更新に失敗しました。');
  }
  return false;
}