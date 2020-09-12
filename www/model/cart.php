<?php 
//modelフォルダのfunctions.phpファイルの読み込み
require_once MODEL_PATH . 'functions.php';
//modelフォルダのdb.phpファイルの読み込み
require_once MODEL_PATH . 'db.php';

//cartとitemのuserid結合テーブルからログイン中のuserの情報を取得
function get_user_carts($db, $user_id){
  $sql = "
    SELECT
      items.item_id,
      items.name,
      items.price,
      items.stock,
      items.status,
      items.image,
      carts.cart_id,
      carts.user_id,
      carts.amount
    FROM
      carts
    JOIN
      items
    ON
      carts.item_id = items.item_id
    WHERE
      carts.user_id = :user_id
  ";
  $array = array(':user_id'=>$user_id);
  return fetch_all_query($db, $sql, $array);
}

//itemsテーブルとusresテーブルの結合テーブルから引数のuser_idとitem_idとそれぞれ一致するレコードを取得する
function get_user_cart($db, $user_id, $item_id){
  $sql = "
    SELECT
      items.item_id,
      items.name,
      items.price,
      items.stock,
      items.status,
      items.image,
      carts.cart_id,
      carts.user_id,
      carts.amount
    FROM
      carts
    JOIN
      items
    ON
      carts.item_id = items.item_id
    WHERE
      carts.user_id = :user_id
    AND
      items.item_id = :item_id
  ";
  $array = array(':user_id'=>$user_id, ':item_id'=>$item_id);
//１行のレコードを取得、何か取得失敗があった場合falseが返る
  return fetch_query($db, $sql, $array);

}


function add_cart($db, $user_id, $item_id ) {
  //cartとitemのuserid結合テーブルからログイン中のuserの情報を取得、返り値は１行のレコードを取得、何か取得失敗があった場合falseが返る
  $cart = get_user_cart($db, $user_id, $item_id);
  if($cart === false){
    //cartsに商品追加
    return insert_cart($db, $user_id, $item_id);
  }
  //get_user_cart()で何かレコード取得できてたら、そのカート内商品の個数をupdate_cart_amountで更新する
  return update_cart_amount($db, $cart['cart_id'], $cart['amount'] + 1);
}

//cartsに商品追加
function insert_cart($db, $user_id, $item_id, $amount = 1){
  $sql = "
    INSERT INTO
      carts(
        item_id,
        user_id,
        amount
      )
    VALUES(:item_id, :user_id, :amount)
  ";
//これはinsertで追加だからexecute_queryユーザー定義関数のように実行するだけでよくて、selectのときにはfetch,fetch_allの何かを取得するユーザー定義関数使うのかも
  $array = array(':item_id'=>$item_id, ':user_id'=>$user_id, ':amount'=>$amount);

  return execute_query($db, $sql, $array);
}

//カートの中に入っている個数を変更
function update_cart_amount($db, $cart_id, $amount){
  $sql = "
    UPDATE
      carts
    SET
      amount = :amount
    WHERE
      cart_id = :cart_id
    LIMIT 1
  ";
  $array = array(':amount'=>$amount, ':cart_id'=>$cart_id);
  return execute_query($db, $sql, $array);
}

//カートの中身を削除する
function delete_cart($db, $cart_id){
  $sql = "
    DELETE FROM
      carts
    WHERE
      cart_id = :cart_id
    LIMIT 1
  ";

  $array = array(':cart_id'=>$cart_id);

  return execute_query($db, $sql, $array);
}

//カート内の個数と在庫個数のチェック、在庫数の変更、cartsテーブルから引数がuser_idのレコードを削除する
function purchase_carts($db, $carts){
  //カートの個数と購入数をチェック、カートに商品があるか、公開になっているか、在庫数が足りているか 何かエラーがあればfalseを返す。何もエラーなければtrueを返す。
  if(validate_cart_purchase($carts) === false){
    return false;
  }
  foreach($carts as $cart){
    //在庫数の変更をする。更新
    if(update_item_stock(
        $db, 
        $cart['item_id'], 
        $cart['stock'] - $cart['amount']
      ) === false){
      set_error($cart['name'] . 'の購入に失敗しました。');
    }
  }
  //cartsテーブルから引数がuser_idのレコードを削除する
  delete_user_carts($db, $carts[0]['user_id']);
}

//cartsテーブルから引数がuser_idのレコードを削除する
function delete_user_carts($db, $user_id){
  $sql = "
    DELETE FROM
      carts
    WHERE
      user_id = :user_id
  ";

  $array = array(':user_id'=>$user_id);

  execute_query($db, $sql, $array);
}

//カート内の商品合計金額
function sum_carts($carts){
  $total_price = 0;
  foreach($carts as $cart){
    $total_price += $cart['price'] * $cart['amount'];
  }
  return $total_price;
}

//カートの個数と購入数をチェック、カートに商品があるか、公開になっているか、在庫数が足りているか
function validate_cart_purchase($carts){
  if(count($carts) === 0){
    set_error('カートに商品が入っていません。');
    return false;
  }
  foreach($carts as $cart){
    // item.phpL176 ステータスが1のときtrue、それ以外の時はfalseを返す、
    if(is_open($cart) === false){
      set_error($cart['name'] . 'は現在購入できません。');
    }
    if($cart['stock'] - $cart['amount'] < 0){
      set_error($cart['name'] . 'は在庫が足りません。購入可能数:' . $cart['stock']);
    }
  }
  if(has_error() === true){
    return false;
  }
  return true;
}

