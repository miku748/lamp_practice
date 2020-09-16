<?php 
//modelファイルのdb.phpの読み込み
require_once MODEL_PATH . 'db.php';



//user_idとtotalを取得して、それを引数に渡してpurchaseテーブルにinsertするの関数を作る。
function insert_purchase($db ,$user ,$total_price){
  $sql = "
  INSERT INTO
    purchase(
      user_id,
      total
    )
  VALUES(:user_id, :total)
  ";

  $array = array(':user_id'=>$user['user_id'], ':total'=>$total_price);
  
  return execute_query($db, $sql, $array);

  
}

//L29の$cartsの中から、item_id,price,amountを取得して、それを引数に渡して、detailテーブルにinsertする関数を作る。
//detailはcartsの配列文回さないといけない
function insert_detail($db, $purchase_id, $item_id, $price, $amount){
    $sql = "
  INSERT INTO
    detail(
      purchase_id,
      item_id,
      price,
      amount
    )
  VALUES(:purchase_id, :item_id, :price, :amount)
  ";
$array = array(':purchase_id'=>$purchase_id, ':item_id'=>$item_id, ':price'=>$price, ':amount'=>$amount);
  
  

  return execute_query($db, $sql, $array);
}


function regist_purchase_transaction($db, $user,  $carts, $total_price){
  //購入履歴、購入明細のデータ保存
//user_idとtotalを取得して、それを引数に渡してpurchaseテーブルにinsertするの関数を作る。
//purchaseテーブル書いたらlastinsertID?をして挿入されたpurchase_idを取得する。
//L29の$cartの中から、item_id,price,amountを取得して、それを引数に渡して、detailテーブルにinsertする関数を作る。
$db->beginTransaction();

  if(insert_purchase($db, $user, $total_price) !== false){
    
      $purchase_id = (int)$db->lastInsertId();
      $status = true;
      foreach($carts as $cart) {
        $item_id = $cart['item_id'];
        $price = $cart['price'];
        $amount = $cart['amount'];

        if(insert_detail($db, $purchase_id, $item_id, $price, $amount) === false){
        
          $status = false;
        break;
          
        }
      }
      if ($status === true) {
        $db->commit();
      }
      return $status;
  }
  $db->rollback();
  return false;
}


//購入履歴のpurchaseテーブルを取得
//一般ユーザーは、ログイン中ユーザーの購入履歴を表示
//管理者は全ての購入履歴を表示
//新しい順で表示
//この関数をコントローラーで呼び出す前にユーザータイプを取っておく１か２か
function get_purchase($db, $user){
  $sql = '
    SELECT
      purchase_id,
      created,
      total
    FROM
      purchase
  ';
  //ログインユーザーが一般なら
  if($user['type'] === 2) {
    $sql .= '
      WHERE
        user_id = :user_id
    ';
    $array = array(':user_id'=>$user['user_id']);
  }
  $sql .= '
      ORDER BY created DESC
    ';
  //レコードを取得
  return fetch_all_query($db, $sql, $array);
}


//detailテーブルを取得する
//商品名を表示させたいので、itemsテーブルもジョインしたものを取得したい
//引数の$purchase_idにはコントローラーで$purchase_id=get_post('perchase_id');で取ってきたものを入れる
function get_detail($db, $purchase_id){
  $sql = '
    SELECT
      detail.purchase_id,
      detail.item_id,
      detail.price,
      detail.amount,
      items.name
    FROM
      detail
    JOIN
      items
    ON
      detail.item_id = items.item_id
    WHERE
      purchase_id = :purchase_id
  ';

  $array = array(':purchase_id'=>$purchase_id);

  return fetch_all_query($db, $sql, $array);
}



