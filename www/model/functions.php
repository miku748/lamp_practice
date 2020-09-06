<?php
//var_dumpするやつ
function dd($var){
  var_dump($var);
  exit();
}
//リダイレクトさせる
function redirect_to($url){
  header('Location: ' . $url);
  exit;
}
//getで送られてきたものがあれば、それを取得する。なければ空で返す。
function get_get($name){
  if(isset($_GET[$name]) === true){
    return $_GET[$name];
  };
  return '';
}
//POSTで送られてきたものがあれば取得する。なければ空で返す。
function get_post($name){
  if(isset($_POST[$name]) === true){
    return $_POST[$name];
  };
  return '';
}
//ファイルの情報取得。情報取れなかったら空で返す。
function get_file($name){
  if(isset($_FILES[$name]) === true){
    return $_FILES[$name];
  };
  return array();
}
//セッション名を引数にして、値があれば値を取得。取得できなかったら空を返す。
function get_session($name){
  //isset関数は、変数に値がセットされていて、かつNULLでないときに、TRUE(真)を戻り値として返します。NULLとは、変数が値を持っていないことをあらわす特別な値です。
  if(isset($_SESSION[$name]) === true){
    return $_SESSION[$name];
  };
  return '';
}
//お好きなセッション変数を新たに登録。名前と値を渡せば作れる。
function set_session($name, $value){
  $_SESSION[$name] = $value;
}
//セッション変数にエラーを登録している。引数にエラーの内容渡している？文字？角かっこ２つあるのはどういう意味になるのか。
function set_error($error){
  $_SESSION['__errors'][] = $error;
}
//エラーを取得しようとする。
function get_errors(){
  //get_sessionで__errorsの引き数渡して、セッション変数$_SESSION['__errors']が存在するかしないかのチェック。get_session($name)は戻り値として、$_SESSION[$name]を返す。それを$errorsに代入。
  $errors = get_session('__errors');
  //$errorsが空の場合、空の配列を返す。
  if($errors === ''){
    return array();
  }
  //$errorsが空でなければ、set_session($name,$value)でセッション変数に$_SESSION['__errors']=array()を登録している。
  set_session('__errors',  array());
  return $errors;
}
//エラーがあるか確認している。$_SESSION['__errors']が存在している"true"かつ、count()関数の返り値が"0出ない"=TRUE?が戻り値として送られる。
function has_error(){
  return isset($_SESSION['__errors']) && count($_SESSION['__errors']) !== 0;
}
//$_SESSION['message'][]=$messageのように、引数の$messageを値として$_SESSION['__message'][]をセッション変数に登録している。
function set_message($message){
  $_SESSION['__messages'][] = $message;
}
//messagesを取得して、$massagesに代入する。
function get_messages(){
  //L51-get_sessionで$_SESSION['__messages']の値を取得して、$messagesに代入する。
  $messages = get_session('__messages');
  //$messagesが空のとき、空の配列を返す。
  if($messages === ''){
    return array();
  }
  //$messagesが空ではないとき、L57-set_sessionで新たに、セッション名$_SESSION['__messages']=値arary()のセッション変数を登録。
  set_session('__messages',  array());
  return $messages;
}
//ログインしているかチェック
function is_logined(){
  //get_sessionで$_SESSION['user_id']の値が空でないときに、返り値を返す。空でないときget_session('user_id') !== ''はTRUE？
  return get_session('user_id') !== '';
}
//拡張子つきの画像パスを取得する
function get_upload_filename($file){
  if(is_valid_upload_image($file) === false){
    return '';
  }
  //画像ファイルかどうかを確認する場合、exif_imagetype関数を使うと便利です。exif_imagetype(ここに画像のパスやURL)。画像に関するMIMEタイプ(拡張子以外の方法で、転送するドキュメントの種類をブラウザに伝える方法)を整数として返す関数
  //この関数は、画像の形式を判別し、以下の定数を返してくれます（画像形式でなければfalseを返す）。
  //IMAGETYPE_GIF,IMAGETYPE_JPEG,IMAGETYPE_PNGなどなど
  // define('PERMITTED_IMAGE_TYPES', array(
  //   IMAGETYPE_JPEG => 'jpg',
  //   IMAGETYPE_PNG => 'png',
  // ));
  //
  $mimetype = exif_imagetype($file['tmp_name']);
  $ext = PERMITTED_IMAGE_TYPES[$mimetype];
  return get_random_string() . '.' . $ext;
}
//ランダムな20文字を取得する
function get_random_string($length = 20){
  //「substr()」は開始位置と文字数を指定することで文字列を切り出すことが出来る。substr( 対象文字列, 開始位置 [, 文字数]);
  //basa_convert(変換したいもの、○進法から、○進法に)変更できる。
  //hash( "sha256", $str)  hash関数は次のように第1パラメータのアルゴリズム指定することで、MD5方式やSHA1方式,SHA2方式のハッシュ値を計算することができます。変換した値を元に戻す方法が用意されていない不可逆性を持つ。
  //uniqid()は現在時刻からユニークなIDを作成する関数。 uniqid($prefix接頭語、TRUEかFALSE)trueを指定するとユニーク性の高いIDを作成します。指定しない場合は、false。引数なしのuniqid()だと、13文字の文字列が生成される。
  return substr(base_convert(hash('sha256', uniqid()), 16, 36), 0, $length);
}
//画像を保存する
function save_image($image, $filename){
  //move_uploaded_file関数はクライアントからのリクエストでアップロードされたファイルの保存場所を変更する際に使用する。
  //move_uploaded_file ( アップロードされたファイル名 , 移動先パス/ファイル名 )
  //$_FILES [ アップロードフォームのinput type=fileの name='値' の値 ] [ アップロードされたファイル情報の項目 ]
  //最初の[]にはHTMLアップロードフォームのinput属性のname値が設定されます。2番目の[]にはアップロードされたファイル情報の項目が入ります。
  //連想配列の値に関しては例として仮の値となり、実際には環境に応じて変わる値です。
  //   Array(
  //     [name] => test.csv             元ファイル名
  //     [type] => text/plain           ファイルタイプ
  //     [tmp_name] => /tmp/php5dkdaFd  サーバーに一時保管されたファイル名
  //     [error] => 0                   エラーコード
  //     [size] => 123                  ファイルのバイト数
  // )

//   ファイルアップロードは以下のような流れで動作します。

// ファイルアップロードフォームでアップロード
// サーバーの一時フォルダに一旦保管
// move_uploaded_file()で指定のパスに移動
// ファイルアップロードフォームでUPされたファイルは、サーバーのテンポラリフォルダに一旦保管されます。
//(/$_FILES [ アップロードフォームのinput type=fileの name='値' の値 ] [ アップロードされたファイル情報の項目(tmp_nameのこと) ])
// そしてmove_uploaded_file()で指定したパスにファイルを移動させます。

//define('IMAGE_DIR', $_SERVER['DOCUMENT_ROOT'] . '/assets/images/' )
//$_SERVER['DOCUMENT_ROOT'] ドキュメントルートのフルパス 例：/var/www/html 

  return move_uploaded_file($image['tmp_name'], IMAGE_DIR . $filename);
}
//画像を消去する
function delete_image($filename){
  //file_exists(チェックしたいファイルまたはディレクトリのパス)で、ファイルまたはディレクトリが存在しているか調べてくれます。
  //存在した場合 → true を返す。存在しない場合 → false を返す
  //ファイルが存在した場合trueを返すので、if文でチェックして、処理を分ける方法が一般的な使い方ですね。
  if(file_exists(IMAGE_DIR . $filename) === true){
    //unlink関数unlink(ファイルパス)で指定したファイルを削除できる。指定したファイルを削除できた場合は、trueを、その他の場合は、falseを返します。
    unlink(IMAGE_DIR . $filename);
    return true;
  }
  return false;
  
}


//文字数チェック
function is_valid_length($string, $minimum_length, $maximum_length = PHP_INT_MAX){
  //文字数の取得。$lengthに代入。
  $length = mb_strlen($string);
  return ($minimum_length <= $length) && ($length <= $maximum_length);
}
//alphanumericは英数字 英数字かどうかチェック
function is_alphanumeric($string){
  //define('REGEXP_ALPHANUMERIC', '/\A[0-9a-zA-Z]+\z/');
  //is_valid_formatは2個関数下でpreg_match($format, $string)バリデーションチェックしている関数
  return is_valid_format($string, REGEXP_ALPHANUMERIC);
}

//整数になっているかのチェック
function is_positive_integer($string){
  //define('REGEXP_POSITIVE_INTEGER', '/\A([1-9][0-9]*|0)\z/');
  //正規表現にマッチしていたら１を、負ければ０を返す。マッチング処理にエラーが発生した場合はFALSEを返します。
  return is_valid_format($string, REGEXP_POSITIVE_INTEGER);
}

//正規表現の型
function is_valid_format($string, $format){
  //preg_match(正規表現、ユーザーから送られてきた正規表現とマッチしているか調べたい情報)
  //マッチする文字列があれば1を、なければ0を、マッチング処理にエラーが発生した場合はFALSEを返します。
  return preg_match($format, $string) === 1;
}

//POST送信されてきた画像のファイル形式が有効なものかチェック
function is_valid_upload_image($image){
  //POST通信でアップロードされたか確認
  //is_uploaded_file関数を使うと、フォームなどでアップロードされたファイルがPOST通信で送信されてきたものかを確認することができます。
  //POST通信でアップされている場合はtrue、それ以外の方法でアップされている場合はfalseを返します。
  //フォームなどを通じてアップロードされる場合、ファイルは仮名で仮フォルダに保存されるため、スーパーグローバル変数$_FILESと組み合わせて使用することが多いです。
  if(is_uploaded_file($image['tmp_name']) === false){
    //セッションにエラーを登録。$_SESSION['__errors'][] = ファイル形式が不正です;を登録。
    set_error('ファイル形式が不正です。');
    return false;
  }
  //exif_imagetype関数で画像の形式を調べる。jpeg,pngとか。gifなのかとか。（画像形式でなければfalseを返す）。
  $mimetype = exif_imagetype($image['tmp_name']);
  //送られてきた画像が、画像形式なものでなければ
  if( isset(PERMITTED_IMAGE_TYPES[$mimetype]) === false ){
    //implode関数とは、配列の要素を好きな区切り印（デリミタ）で区切りながら、結合して文字列にします。この際にデリミタと呼ばれる区切り文字を付加することができます。
    //implode(好きな区切り印、一次元配列)
    //セッションにエラーを登録。$_SESSION['__errors'][] = ファイル形式は' . implode('、', PERMITTED_IMAGE_TYPES) . 'のみ利用可能です。;を登録
    //set_error()はユーザー定義関数
    set_error('ファイル形式は' . implode('、', PERMITTED_IMAGE_TYPES) . 'のみ利用可能です。');
    return false;
  }
  return true;
}
//XSS対策 エスケープ処理
function h($str) {
 return htmlspecialchars($str, ENT_QUOTES, "UTF-8");
}

// トークンの生成 と セッションに$_SESSION['csrf_token'] = $token のセッション変数登録。
function get_csrf_token(){
  // get_random_string()はユーザー定義関数。トークンの生成。
  $token = get_random_string(30);
  // set_session()はユーザー定義関数。セッション登録。
  set_session('csrf_token', $token);
  return $token;
}

// トークンの照合チェック
function is_valid_csrf_token($token){
  if($token === '') {
    return false;
  }
  // get_session()はユーザー定義関数
  //get_session('csrf_token)は$_SESSION['csrf_token']があるか見て、あればそれを返す。
  //get_sessionで返ってきた値と引数で渡した$tokenが等しいかチェックする
  //引数で渡した$tokenはget_csrf_token()で作られているもの
  //等しければTRUE,出なければFALSEを返す
  //===のように=が３つある時は左右を比較して、等しければTRUE,等しくなければFALSEを返す

  return $token === get_session('csrf_token');
}
