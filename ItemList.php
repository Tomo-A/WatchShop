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
$img_dir = './img/';
$err_msg = array();
$gender = '';
$brand = '';
$movement = '';
$price = 0;

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
if(isset($_GET['serch']) === TRUE){
    if(isset($_GET['gender']) === TRUE) {
        $gender = $_GET['gender'];
    }
    if(isset($_GET['brand']) === TRUE){
        $brand = $_GET['brand'];
    }
    if(isset($_GET['movement']) === TRUE){
        $movement = $_GET['movement'];
    }
    if(isset($_GET['price']) === TRUE){
        $price = $_GET['price'];
    }
}

try{
    //データベースに接続
    $dbh = new PDO($dsn,$username,$password, array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4'));
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    
    //商品一覧データを取得
    $sql = 'SELECT item_id,img,price,stock FROM items where status = 1';
    if($gender !== ''){
        $sql .= ' AND gender = ' . $gender;
    }
    if($brand !== ''){
        $sql .= ' AND brand = ' . "'$brand'";
    }
    if($movement !== ''){
        $sql .= ' AND movement = ' . $movement;
    }
    if($price !== ''){
        $sql .= ' AND price >= ' . $price;
    }
    //SQLを実行する準備
    $stmt = $dbh->prepare($sql);
    $stmt->execute();
    //$dataに値を挿入
    $data = $stmt->fetchAll();
}catch(PDOException $e){
    $err_msg['db_connect'] = 'DBエラー：' .$e->getMessage();
}

?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>商品一覧</title>
    <link rel="stylesheet" href="./css/html5reset-1.6.1.css" />
    <link rel="stylesheet" href="./css/ItemList.css" />
</head>
<body>
     <header>
        <img src="./img/logo.jpg">
        <h2 class="username">こんにちは<?php print htmlspecialchars($user_name,ENT_QUOTES);?>さん</h2>
        <form class="btn" method="post">
            <input type="submit" name="cart" value="カート" formaction="Cart.php"/>
            <input type="submit" name="logout" value="ログアウト"/>
        </form>
    </header>
    <main>
        <img src="./img/Top.jpg"></img>
            <div class="serch">
                <form>
                <select name="gender">
                    <option value="" disabled selected style='display:none;'>選択してください</option>
                    <option value="1">男性向け</option>
                    <option value="2">女性向け</option>
                </select>
                <select name="brand">
                    <option value="" disabled selected style='display:none;'>選択してください</option>
                    <option value="SEIKO">SEIKO</option>
                    <option value="CITIZEN">CITIZEN</option>
                    <option value="ROLEX">ROLEX</option>
                    <option value="OMEGA">OMEGA</option>
                    <option value="Daniel Wellington">Daniel Wellington</option>
                </select>
                <select name="movement">
                    <option value="" disabled selected style='display:none;'>選択してください</option>
                    <option value="1">クォーツ</option>
                    <option value="2">機械式</option>
                </select>
                <select name="price">
                    <option value="" disabled selected style='display:none;'>選択してください</option>
                    <option value="10000">10000～</option>
                    <option value="50000">50000～</option>
                    <option value="100000">100000～</option>
                    <option value="200000">200000～</option>
                    <option value="500000">500000～</option>
                </select>
                <input type="submit" name="serch" value="検索">
                </form>
            </div>
        <div class="mainFlex">
            <nav>
                <ul>
                    <li><a href="Seiko.php">SEIKO</a></li>
                    <li><a href="citizen.php">CITIZEN</a></li>
                    <li><a href="rolex.php">ROLEX</a></li>
                    <li><a href="omega.php">OMEGA</a></li>
                    <li><a href="DW.php">Daniel Wellington</a></li>
                </ul>
            </nav>
            <div class="flex-container">
                <?php foreach($data as $values) { ?>
                    <div class="flex-item">
                        <a href="ItemDetail.php?item_id=<?php print $values['item_id'];?>"><img src="<?php print $img_dir . $values['img'];?>"></a>
                        <p><?php print number_format(htmlspecialchars($values['price'],ENT_QUOTES));?>円</p>
                        <form method="post" action="Cart.php">
                            <?php if($values['stock'] > 0) { ?>
                                <input type="submit" name="addcart" value="カートに追加">
                                <!--商品判別の為にitem_idをhiddenで取得する-->
                                <input type="hidden" name="item_id" value="<?php print $values['item_id'];?>">
                                <?php } else {?>
                                <span class="sold_out">売り切れ<span><br>
                            <?php } ?>
                        </form>
                    </div>
                <?php } ?>
                </ul>
            </div>
        </div>
    </main>    
    <footer>
        <p><small>Copyright &copy; WatchShop All Rights Reserved.</small>
    </footer>
</body>
</html>