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



