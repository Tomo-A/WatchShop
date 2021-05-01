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
$total = 0;
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
    $dbh = new PDO($dsn,$username,$password, array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4'));
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    
    //カートに商品追加の処理
    if(isset($_POST['addcart']) === TRUE){
        if(isset($_POST['item_id']) === TRUE){
            $item_id = $_POST['item_id'];
        }
        $sql = 'SELECT * FROM carts where user_id = ? and item_id = ?';
        
        $stmt = $dbh->prepare($sql);
        $stmt->bindValue(1, $user_id, PDO::PARAM_INT);
        $stmt->bindValue(2, $item_id, PDO::PARAM_INT);
        $stmt->execute();
        //$dataに値を挿入
        $data = $stmt->fetch();
        
        //$dataが空の場合cartテーブルに追加をする
        if($data === false){
            //データが空の場合
            $sql = 'INSERT INTO carts (user_id,item_id,amount,createdate,updatedate) values (?,?,1,now(),now())';
        }else{
            //データがある場合
            $sql = 'UPDATE carts SET amount = amount + 1, updatedate = now() where user_id = ? and item_id = ?';
        }
        $stmt = $dbh->prepare($sql);
        $stmt->bindValue(1, $user_id, PDO::PARAM_INT);
        $stmt->bindValue(2, $item_id, PDO::PARAM_INT);
        $stmt->execute();
    }
    //個数変更時の処理
    if(isset($_POST['change']) === TRUE){
        if(isset($_POST['item_id']) === TRUE){
            $item_id = $_POST['item_id'];
        }
        if(isset($_POST['amount']) === TRUE){
            $amount = $_POST['amount'];
        }
        if($amount === ''){
            $err_msg[] = '数量が未入力です';
        }else if(!preg_match('/^[1-9][0-9]*$/', $amount)){
            $err_msg[] = '数量を正しい形式で入力してください';
        }
        if(count($err_msg) === 0){
            //UPDATE文
            $sql = 'UPDATE carts SET amount = ?, updatedate = now() where user_id = ? and item_id = ?';
            $stmt = $dbh->prepare($sql);
            $stmt->bindValue(1, $amount, PDO::PARAM_INT);
            $stmt->bindValue(2, $user_id, PDO::PARAM_INT);
            $stmt->bindValue(3, $item_id, PDO::PARAM_INT);
            $stmt->execute();
        }
    }
    //削除の処理
    if(isset($_POST['delete']) === TRUE){
        //item_id
        if(isset($_POST['item_id']) === TRUE){
            $item_id = $_POST['item_id'];
        }
        //DELETE文
        $sql = 'DELETE FROM carts where user_id = ? and item_id = ?';
        $stmt = $dbh->prepare($sql);
        $stmt->bindValue(1, $user_id, PDO::PARAM_INT);
        $stmt->bindValue(2, $item_id, PDO::PARAM_INT);
        $stmt->execute();
    }
    //商品一覧データを取得
    $sql = 'SELECT carts.item_id, img, price, item_name, amount
    FROM items join carts on items.item_id = carts.item_id where user_id = ?';
    //SQLを実行する準備
    $stmt = $dbh->prepare($sql);
    $stmt->bindValue(1, $user_id, PDO::PARAM_INT);
    $stmt->execute();
    //$dataに値を挿入
    $data = $stmt->fetchAll();
    foreach($data as $value){
        $total += $value['price'] * $value['amount'];
    }
}catch(PDOException $e){
    $err_msg['db_connect'] = 'DBエラー：' .$e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>カート</title>
    <link rel="stylesheet" href="./css/html5reset-1.6.1.css" />
    <link rel="stylesheet" href="./css/Cart.css" />
</head>
<body>
     <header>
        <img src="./img/logo.jpg">
        <form class="btn" method="post">
            <input type="submit" name="login" value="カート"/>
            <input type="submit" name="logout" value="ログアウト">
        </form>
    </header>
    <main>
        <div class="mainFlex">
            <div>
                <h1>この商品を購入しますか？</h1>
                <?php foreach($err_msg as $err) { ?>
                    <p class="errmsg"><?php print $err;?></p>
                <?php } ?>
                <form method="post" action="BuyItem.php">
                    <input type="submit" name="buy" value="購入">
                </form>
                <p class="total">合計金額：<?php print number_format($total);?></p>
            </div>
            <table>
                <tr>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                </tr>
            <?php foreach($data as $value) { ?>
                <tr>
                    <td><img src="<?php print $img_dir . $value['img'];?>"></td>
                    <td>値段：<?php print number_format(htmlspecialchars($value['price'],ENT_QUOTES));?></td>
                    <td class="tableprice">
                        <form method="post" class="info">
                        個数：<input type="text" name="amount" value="<?php print $value['amount'];?>">&nbsp;&nbsp;<input type="submit" name="change" value="変更">   
                        <input type="hidden" name="item_id" value="<?php print $value['item_id'];?>">
                        </form>
                    </td>
                    <td>
                        <form method="post">
                        <input type="hidden" name="item_id" value="<?php print $value['item_id'];?>">
                        <input type="submit" name="delete" value="削除">
                        </form>
                    </td>
                </tr>
                <?php } ?>
            </table>
            <a href="ItemList.php">商品一覧へ戻る</a>
        </div>
    </main>
    <footer>
        <p><small>Copyright &copy; WatchShop All Rights Reserved.</small>
    </footer>
</body>
</html>