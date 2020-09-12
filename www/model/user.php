<?php
//modelフォルダのfunctions.phpの読み込み
require_once MODEL_PATH . 'functions.php';
//modelファイルのdb.phpの読み込み
require_once MODEL_PATH . 'db.php';

//ログインしているユーザーのusersテーブル情報を取得
function get_user($db, $user_id){
  $sql = "
    SELECT
      user_id, 
      name,
      password,
      type
    FROM
      users
    WHERE
      user_id = :user_id
    LIMIT 1
  ";

  $array = array(':user_id'=>$user_id);
//１行のレコードを取得
  return fetch_query($db, $sql, $array);
}

//usersテーブルの引数nameのレコードを取得
function get_user_by_name($db, $name){
  $sql = "
    SELECT
      user_id, 
      name,
      password,
      type
    FROM
      users
    WHERE
      name = :name
    LIMIT 1
  ";

  $array = array(':name'=>$name);
//１行のレコードを取得
  return fetch_query($db, $sql, $array);
}

//ログインチェック、セッションに$_SESSION['user_id'] = $user['user_id'];を登録。$userを返す。
function login_as($db, $name, $password){
  //usersテーブルの引数nameのレコードを取得
  $user = get_user_by_name($db, $name);
  if($user === false || $user['password'] !== $password){
    return false;
  }
  set_session('user_id', $user['user_id']);
  return $user;
}

//ログインしているユーザーを識別して、その人のusersテーブルの情報を取得して、返す。
function get_login_user($db){
  //$login_user_idに$_SESSION['user_id']を代入
  $login_user_id = get_session('user_id');
//そのユーザーについてのレコードのusersテーブルの情報を取得したものを返す
  return get_user($db, $login_user_id);
}

//ユーザー登録？
function regist_user($db, $name, $password, $password_confirmation) {
  //引数のバリデーションチェック
  if( is_valid_user($name, $password, $password_confirmation) === false){
    return false;
  }
  
  //エラーなければ、usersテーブルのname,passwordカラムに引数の$name,$passwordを追加
  return insert_user($db, $name, $password);
}

//ユーザーのタイプか１かどうかチェックしている？1ならTRUE、そうでなければfalseを返す
function is_admin($user){
  //define('USER_TYPE_ADMIN', 1);
  return $user['type'] === USER_TYPE_ADMIN;
}

//ユーザーの名前の文字、文字数チェック、パスワードチェックしてその値を返す
function is_valid_user($name, $password, $password_confirmation){
  // 短絡評価を避けるため一旦代入。
  $is_valid_user_name = is_valid_user_name($name);
  $is_valid_password = is_valid_password($password, $password_confirmation);
  return $is_valid_user_name && $is_valid_password ;
}

//ユーザーの名前が有効かチェック、文字数、半角英数字。TRUEかFALSEを返す。
function is_valid_user_name($name) {
  $is_valid = true;
  if(is_valid_length($name, USER_NAME_LENGTH_MIN, USER_NAME_LENGTH_MAX) === false){
    set_error('ユーザー名は'. USER_NAME_LENGTH_MIN . '文字以上、' . USER_NAME_LENGTH_MAX . '文字以内にしてください。');
    $is_valid = false;
  }
  if(is_alphanumeric($name) === false){
    set_error('ユーザー名は半角英数字で入力してください。');
    $is_valid = false;
  }
  return $is_valid;
}

//パスワードが有効かチェック、文字数、半角英数字、パスワード照合とセッションにエラー登録。エラーある時はfalse返して、なければtrue返す。
function is_valid_password($password, $password_confirmation){
  $is_valid = true;
  if(is_valid_length($password, USER_PASSWORD_LENGTH_MIN, USER_PASSWORD_LENGTH_MAX) === false){
    set_error('パスワードは'. USER_PASSWORD_LENGTH_MIN . '文字以上、' . USER_PASSWORD_LENGTH_MAX . '文字以内にしてください。');
    $is_valid = false;
  }
  if(is_alphanumeric($password) === false){
    set_error('パスワードは半角英数字で入力してください。');
    $is_valid = false;
  }
  if($password !== $password_confirmation){
    set_error('パスワードがパスワード(確認用)と一致しません。');
    $is_valid = false;
  }
  return $is_valid;
}

//usersテーブルのname,passwordカラムに引数の$name,$passwordを追加
function insert_user($db, $name, $password){
  $sql = "
    INSERT INTO
      users(name, password)
    VALUES (:name, :password);
  ";

  $array = array(':name'=>$name, ':password'=>$password);
//sql文を実行し実行結果をそのまま返す
  return execute_query($db, $sql, $array);
}

