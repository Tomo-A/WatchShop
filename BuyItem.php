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
$item_name = '';
$img_dir = './img/';
$img = '';
$price = '';
$status = '';
$brand = '';
$err_msg = array();
$item_id = '';
$stock = '';

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
        $sql = 'SELECT
                    carts.user_id,
                    carts.item_id,
                    carts.amount,
                    items.item_name,
                    items.price,
                    items.img,
                    items.brand,
                    items.status,
                    items.stock
                FROM
                	carts 
                	INNER JOIN items
                    ON carts.item_id = items.item_id where user_id = ?';
        $stmt = $dbh->prepare($sql);
        $stmt->bindValue(1, $user_id, PDO::PARAM_INT);
        $stmt->execute();
        $data = $stmt->fetchAll();
        if(count($data) > 0){
            foreach($data as $value){
                $item_id = $value['item_id'];
                $item_name = $value['item_name'];
                $price = $value['price'];
                $img = $value['img'];
                $brand = $value['brand'];
                $status = $value['status'];
                $stock = $value['stock'];
                $amount = $value['amount'];
                $total += $value['price'] * $value['amount'];
                if($status !== 1){
                    $err_msg[] = '公開ステータスが公開ではありません';
                }
                if($amount > $stock){
                    $err_msg[] = '在庫数が足りません';
                }
            }

            if(count($err_msg) === 0){
                try{
                    //トランザクション開始
                    $dbh->beginTransaction();
                    
                    foreach($data as $value){
                        $item_id = $value['item_id'];
                        $amount = $value['amount'];
                        $sql = 'UPDATE 
                                    items
                                SET
                                    stock = stock - ?,
                                    updatedate = now()
                                where item_id = ?';
                        $stmt = $dbh->prepare($sql);
                        $stmt->bindValue(1, $amount, PDO::PARAM_INT);
                        $stmt->bindValue(2, $item_id, PDO::PARAM_INT);
                        $stmt->execute();
                    }
                    //カート情報の削除
                    $sql = 'DELETE from
                            carts
                            where user_id = ?';
                    $stmt = $dbh->prepare($sql);
                    $stmt->bindValue(1, $user_id, PDO::PARAM_INT);
                    $stmt->execute();
                    
                    //コミット処理
                    $dbh->commit();
                }catch(PDOException $e){
                    //ロールバック処理
                    $dbh->rollback();
                    throw $e;
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
        <?php foreach($err_msg as $err) { ?>
        <p><?php print $err;?></p>
        <?php } ?>
        <?php if(count($err_msg) === 0) { ?>
        <p class="result">下記商品を購入しました</p>
        <p class="total">合計金額：<?php print number_format($total);?></p>
        <?php foreach($data as $value) { ?>
        <div>
            <p><img src="<?php print $img_dir . $value['img'];?>"></img></p>
            <p>商品名：<?php print htmlspecialchars($value['item_name'],ENT_QUOTES);?></p>
            <p>ブランド：<?php print htmlspecialchars($value['brand'],ENT_QUOTES);?></p>
            <p>数量：<?php print htmlspecialchars($amount,ENT_QUOTES);?></p>
            <p>値段：<?php print number_format($value['price']);?></p>
        </div>
        <?php } ?>
        <?php } ?>
        <p class="backcart"><a href="Cart.php">カートへ戻る</a></p>
    </main>
    <footer>
        <p><small>Copyright &copy; WatchShop All Rights Reserved.</small>
    </footer>
</body>
</html>