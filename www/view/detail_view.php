<?php header("X-FRAME-OPTIONS: DENY"); ?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <?php include VIEW_PATH . 'templates/head.php'; ?>
  <title>購入詳細</title>
  <link rel="stylesheet" href="<?php print(h(STYLESHEET_PATH . 'admin.css')); ?>">
</head>
<body>
  <?php 
  include VIEW_PATH . 'templates/header_logined.php'; 
  ?>

  <div class="container">
    <h1>購入詳細</h1>

    <?php include VIEW_PATH . 'templates/messages.php'; ?>

    <table class="table table-bordered text-center">
      <thead class="thead-light">
        <tr>
          <th>注文番号</th>
          <th>購入日時</th>
          <th>合計金額</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td><?php print(h($purchase_id)); ?></td>
          <td><?php print(h($created)); ?></td>
          <td><?php print(h($total)); ?></td>
        </tr>
      </tbody>
    </table>
  </div>

  <div class="container">
    <?php if(count($detail) > 0) {?>
      <table class="table table-bordered text-center">
        <thead class="thead-light">
          <tr>
            <th>商品名</th>
            <th>価格</th>
            <th>購入数</th>
            <th>小計</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach($detail as $value) { ?>
            <tr>
              <td><?php print(h($value['name'])); ?></td>
              <td><?php print(h($value['price'])); ?></td>
              <td><?php print(h($value['amount'])); ?></td>
              <td><?php print(h(number_format($value['price'] * $value['amount']))); ?></td>
            </tr>
          <?php } ?>
        </tbody>
      </table>
    <?php }else{ ?>
      <p>商品はありません</p>
    <?php } ?>
  </div>
</body>
</html>