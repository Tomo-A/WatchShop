<?php
session_start();
$host = 'localhost';
$username = 'codecamp41107';
$password = 'codecamp41107';
$dbname = 'codecamp41107';
$charset = 'utf8';

//MySQL用のDSN文字列
$dsn = 'mysql:dbname='.$dbname.';host='.$host.';charset='.$charset;

$item_id = '';
$img_dir = './img/';
$err_msg = array();
$data = array();

//ログインページからユーザーIDをセッションで受け取る
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

try{
    //データベースに接続
    $dbh = new PDO($dsn,$username,$password, array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4'));
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    
    if(isset($_GET['item_id']) === TRUE){
        $item_id = $_GET['item_id'];    
    }
    if($item_id !== ''){
        $sql = 'SELECT
                item_name,
                price,
                img,
                brand,
                movement,
                gender
            FROM
                items
            where item_id = ?';
        $stmt = $dbh->prepare($sql);
        $stmt->bindValue(1, $item_id, PDO::PARAM_INT);
        $stmt->execute();
        $data = $stmt->fetchAll();
    }
}catch(PDOException $e){
    $err_msg[] = 'データ取得に失敗しました';
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>商品詳細</title>
    <link rel="stylesheet" href="./css/html5reset-1.6.1.css" />
    <link rel="stylesheet" href="./css/ItemDetail.css" />
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
        <?php foreach($data as $value) { ?>
        <div>
            <p><img src="<?php print $img_dir . $value['img']?>"></img></p>
            <p>製品名：<?php print htmlspecialchars($value['item_name'],ENT_QUOTES);?></p>
            <p>ブランド：<?php print htmlspecialchars($value['brand'], ENT_QUOTES);?></p>
            <p>ムーブメント：<?php print htmlspecialchars($value['movement'], ENT_QUOTES);?></p>
            <p>価格：<?php print htmlspecialchars($value['price'], ENT_QUOTES);?></p>
        </div>
        <?php } ?>
        <a href="ItemList.php">商品一覧ページへ戻る</a>
    </main>
    <footer>
        <p><small>Copyright &copy; WatchShop All Rights Reserved.</small>
    </footer>
</body>
</html>