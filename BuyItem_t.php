<?php
session_start();
$host = 'localhost';
$username = 'codecamp41107';
$password = 'codecamp41107';
$dbname = 'codecamp41107';
$charset = 'utf8';

//MySQL用のDSN文字列
$dsn = 'mysql:dbname='.$dbname.';host='.$host.';charset='.$charset;

$user_name = '';
$user_id = '';
$amount = '';
$total = '';
$img_dir = './img/';
$err_msg = array();
$item_id = '';

//ログインページusersテーブルのユーザーIDをセッションで受け取る
if(isset($_SESSION['user_name']) === TRUE){
    $user_name = $_SESSION['user_name'];
}else{
    //ログインしていない場合はログインページへリダイレクト
    header('Location: LoginPage.php');
    exit;
}
//ログアウト処理
if(isset($_POST['logout']) === TRUE){
    header('Location: Logout.php');
    exit;
}
//cartsテーブルのユーザーIDをセッションで受け取る
if(isset($_SESSION['user_id']) === TRUE){
    $user_id = $_SESSION['user_id'];
}
try{
    //データベースに接続
    $dbh = new PDO($dsn,$username,$password,array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4'));
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    //Cart.phpからPOSTで値を取得する
    if(isset($_POST['buy']) === TRUE){
        //SELECT文でデータ取得
        $sql = 'SELECT cart.item_id, item_name, price, image, status, stock, amount
                FROM carts 
                INNER JOIN items
                    ON carts.item_id = items.id
                where user_id = ?';
        
        $stmt = $dbh->prepare($sql);
        $stmt->bindValue(1, $user_id, PDO::PARAM_INT);
        $stmt->execute();
        $data = $stmt->fetchAll();
        if(count($data) > 0){
            foreach($data as $value){
                $item_id    = $value['item_id'];
                $item_name  = $value['item_name'];
                $price      = $value['price'];
                $image      = $value['image'];
                $status     = $value['status'];
                $stock      = $value['stock'];
                $amount     = $value['amount'];
                if ($status !== 1) {
                    エラーメッセージ
                }
                if ($amount > $stock) {
                    エラーメッセージ
                }
            }
            
            if (count($err_msg) === 0) {
                try {
                    トランザクション
                    foreach($data as $value) {在庫数の減産処理
                        $item_id    = $value['item_id'];
                        $amount     = $value['amount'];
                    }
                    カード情報の削除
                    トランザクションcommit
                } catch(PDOException $e) {
                    トランザクションrollback
                }
            }

        }else{
            $err_msg[] = '商品がありません';
        }
    }
}catch(PDOException $e){
    $err_msg['db_connect'] = 'DBエラー：' .$e->getMessage();
}

?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>商品詳細</title>
    <link rel="stylesheet" href="./css/html5reset-1.6.1.css" />
    <link rel="stylesheet" href="./css/BuyItem.css" />
</head>
<body>
    <header>
        <img src="./img/logo.jpg">
        <form class="btn" method="post">
            <input type="submit" name="cart" value="カート" formaction="Cart.php">
            <input type="submit" name="logout" value="ログアウト"/>
        </form>
    </header>
    <main>
        <div>
            <p><img src=""></img></p>
            <p>製品名</p>
            <p>ブランド</p>
            <p>ムーブメント</p>
            <p>価格</p>
            <a href="Cart.php">カートへ戻る</a>
        </div>
    </main>
    <footer>
        <p><small>Copyright &copy; WatchShop All Rights Reserved.</small>
    </footer>
</body>
</html>