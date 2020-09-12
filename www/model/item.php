<?php
//modelフォルダのfunctions.phpの読み込み
require_once MODEL_PATH . 'functions.php';
//モデルフォルダのdb.phpの読み込み
require_once MODEL_PATH . 'db.php';

// DB利用
//itemsテーブルを取得 １行
function get_item($db, $item_id){
  $sql = "
    SELECT
      item_id, 
      name,
      stock,
      price,
      image,
      status
    FROM
      items
    WHERE
      item_id = :item_id
  ";

  $array = array(':item_id'=>$item_id);
//レコードを１行取得
  return fetch_query($db, $sql, $array);
}

//itemsテーブル取得のstasusカラムが1のときのレコードを取得 複数行取得
function get_items($db, $is_open = false){
  $sql = '
    SELECT
      item_id, 
      name,
      stock,
      price,
      image,
      status
    FROM
      items
  ';
  if($is_open === true){
    $sql .= '
      WHERE status = 1
    ';
  }
  //レコードを取得
  return fetch_all_query($db, $sql);
}

//itemsテーブルを全て取得
function get_all_items($db){
  return get_items($db);
}

//itemsテーブルのステータスが１のレコードを全て取得
function get_open_items($db){
  return get_items($db, true);
}

//それぞれのエラーチェック
function regist_item($db, $name, $price, $stock, $status, $image){
  //get_uplode_filenameは//拡張子つきの画像パスを取得して、それを返している
  $filename = get_upload_filename($image);
  //それぞれエラーチェック
  if(validate_item($name, $price, $stock, $filename, $status) === false){
    return false;
  }
  //regist_item_transactionはcommitできたらtrue,コミットできずrollbackになったらfalseが返ってくる
  return regist_item_transaction($db, $name, $price, $stock, $status, $image, $filename);
}

//トランザクション処理 商品の追加と画像の保存をする
function regist_item_transaction($db, $name, $price, $stock, $status, $image, $filename){
  $db->beginTransaction();
  if(insert_item($db, $name, $price, $stock, $filename, $status) 
    && save_image($image, $filename)){
    $db->commit();
    return true;
  }
  $db->rollback();
  return false;
  
}

//itemsテーブルに商品を追加する
function insert_item($db, $name, $price, $stock, $filename, $status){
    //   define('PERMITTED_ITEM_STATUSES', array(
    //   'open' => 1,
    //   'close' => 0,
    // ));
  $status_value = PERMITTED_ITEM_STATUSES[$status];
  $sql = "
    INSERT INTO
      items(
        name,
        price,
        stock,
        image,
        status
      )
    VALUES(:name, :price, :stock, :filename, :status_value);
  ";
  //値をバインド
  $array = array(':name'=>$name, ':price'=>$price, ':stock'=>$stock, ':filename'=>$filename, ':status_value'=>$status_value);
  //SQL実行
  return execute_query($db, $sql, $array);
}

//商品のステータス変更をする。更新。
function update_item_status($db, $item_id, $status){
  $sql = "
    UPDATE
      items
    SET
      status = :status
    WHERE
      item_id = :item_id
    LIMIT 1
  ";

  $array = array(':status'=>$status, ':item_id'=>$item_id);
  //SQL文実行
  return execute_query($db, $sql, $array);
}

//在庫数の変更をする。更新。
function update_item_stock($db, $item_id, $stock){
  $sql = "
    UPDATE
      items
    SET
      stock = :stock
    WHERE
      item_id = :item_id
    LIMIT 1
  ";

  $array = array(':stock'=>$stock, ':item_id'=>$item_id);
  
  return execute_query($db, $sql, $array);
}

//商品の削除とトランザクショん処理
function destroy_item($db, $item_id){
  //itemsテーブルからitem_idが一致したものだけ取得 そして$itemに代入
  $item = get_item($db, $item_id);
  if($item === false){
    return false;
  }
  $db->beginTransaction();
  if(delete_item($db, $item['item_id'])
    && delete_image($item['image'])){
    $db->commit();
    return true;
  }
  $db->rollback();
  return false;
}

//商品の削除
function delete_item($db, $item_id){
  $sql = "
    DELETE FROM
      items
    WHERE
      item_id = :item_id
    LIMIT 1
  ";

  $array = array(':item_id'=>$item_id);
  
  return execute_query($db, $sql, $array);
}


// 非DB

// ステータスが1のときtrue、それ以外の時はfalseを返す、
function is_open($item){
  return $item['status'] === 1;
}

//各エラーチェック
function validate_item($name, $price, $stock, $filename, $status){
  $is_valid_item_name = is_valid_item_name($name);
  $is_valid_item_price = is_valid_item_price($price);
  $is_valid_item_stock = is_valid_item_stock($stock);
  $is_valid_item_filename = is_valid_item_filename($filename);
  $is_valid_item_status = is_valid_item_status($status);

  return $is_valid_item_name
    && $is_valid_item_price
    && $is_valid_item_stock
    && $is_valid_item_filename
    && $is_valid_item_status;
}

//文字数チェック
function is_valid_item_name($name){
  $is_valid = true;
  //文字数チェック
  if(is_valid_length($name, ITEM_NAME_LENGTH_MIN, ITEM_NAME_LENGTH_MAX) === false){
    //セッションに$_SESSION['__errors'][] = 商品名は'. ITEM_NAME_LENGTH_MIN . '文字以上、' . ITEM_NAME_LENGTH_MAX . '文字以内にしてください。を登録。
    set_error('商品名は'. ITEM_NAME_LENGTH_MIN . '文字以上、' . ITEM_NAME_LENGTH_MAX . '文字以内にしてください。');
    $is_valid = false;
  }
  return $is_valid;
}

//
function is_valid_item_price($price){
  $is_valid = true;
  if(is_positive_integer($price) === false){
    set_error('価格は0以上の整数で入力してください。');
    $is_valid = false;
  }
  return $is_valid;
}

function is_valid_item_stock($stock){
  $is_valid = true;
  //s_positive_integerは正規表現にマッチしていたら１を、なければ０を返す。マッチング処理にエラーが発生した場合はFALSEを返します。
  if(is_positive_integer($stock) === false){
    set_error('在庫数は0以上の整数で入力してください。');
    $is_valid = false;
  }
  return $is_valid;
}

//画像が有効どうかチェック？
function is_valid_item_filename($filename){
  $is_valid = true;
  if($filename === ''){
    $is_valid = false;
  }
  return $is_valid;
}

//ステータスチェック
function is_valid_item_status($status){
  $is_valid = true;
//   define('PERMITTED_ITEM_STATUSES', array(
//   'open' => 1,
//   'close' => 0,
// ));
//isset(PERMITTED_ITEM_STATUSES[$status])だから$statusのなかにopenかcloseが入って、1か0になる、するとissetなのでTRUEになる。それがなければ,false
  if(isset(PERMITTED_ITEM_STATUSES[$status]) === false){
    $is_valid = false;
  }
  return $is_valid;
}