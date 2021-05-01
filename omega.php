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
$gender = '';
if (isset($_GET['gender']) === true) {
    $gender = $_GET['gender'];
}
try{
    //データベースに接続
    $dbh = new PDO($dsn,$username,$password, array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4'));
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    
    //商品一覧データを取得
    $sql = 'SELECT item_id,img,price,stock FROM items where status = 1';
    if ($gender !== '') {
        $sql .= ' AND gender = ' . $gender;
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
    <link rel="stylesheet" href="./css/BrandDetail.css" />
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
                    <option value="">検索カテゴリ(性別)</option>
                    <option value="1">男性向け</option>
                    <option value="2">女性向け</option>
                </select>
                <select name="brand">
                    <option value="">検索カテゴリ(ブランド別)</option>
                    <option value="1">SEIKO</option>
                    <option value="2">CITIZEN</option>
                    <option value="3">ROLEX</option>
                    <option value="4">OMEGA</option>
                    <option value="5">Daniel Wellington</option>
                </select>
                <select name="movement">
                    <option value=>検索カテゴリ(ムーブメント)</option>
                    <option value="1">クォーツ</option>
                    <option value="2">機械式</option>
                </select>
                <select name="price">
                    <option value="">検索カテゴリ(価格別)</option>
                    <option value="1">10000～50000</option>
                    <option value="2">50000～100000</option>
                    <option value="3">100000～200000</option>
                    <option value="4">200000～300000</option>
                    <option value="5">300000～400000</option>
                </select>
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
            <article>
                <h1>OMEGA</h1>
                <p>オメガは1848年に設立されたスイスの高級腕時計ブランドです。</p>
                <p>創業当初から高精度なムーブメントを発表し脚光を浴びました。</p>
                <br>
                <p>その後も1932年のロサンゼルスオリンピックの公式時計に採用されたり</p>
                <p>1948年に発表した「シーマスター」は英国海軍の軍用時計に採用され世界中から</p>
                <p>高く評価されました。</p>
                <p>オメガの主力製品である「スピードマスター」は、アポロ計画の月面着陸に</p>
                <p>携行されたことからムーンウォッチの名で知られています。</p>
                <br>
                <p>その品質の高さと洗練されたデザインから映画でも頻繁に採用され、代表作として</p>
                <p>映画「007」シリーズで登場人物である「ジェームズ・ボンド」の腕時計として</p>
                <p>登場します。</p>
                <a href="ItemList.php">商品一覧へ戻る</a>
            </article>
        </div>
    </main>    
    <footer>
        <p><small>Copyright &copy; WatchShop All Rights Reserved.</small>
    </footer>
</body>
</html>