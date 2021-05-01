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
                <h1>CITIZEN</h1>
                <p>誰でも手が届きやすい価格帯でありながらもハイスペックなモデルを量産することで知られるシチズン。</p>
                <p>メンズ・レディースともに人気が高く、エコドライブを中心に幅広く親しまれています。</p>
                <br>
                <p>セイコーが機械式時計の生産に力を入れる一方で、シチズンは電波ソーラーでなければ消費者には受けない</p>
                <p>というトレンドを切り開いているといえます</p>
                <p>セイコーがアジア・ヨーロッパでシェアが大きいのに比べ、シチズンは北米で大きなシェアを持っています。</p>
                <p>表面加工化技術の「デゥラテクト」を筆頭に、外装加工へ一層注力しているのもシチズンらしい方向性です。</p>
                <br>
                <p>デザインと機能を併せ持つコスパの良さは、スイスメーカーにはない魅力と言えます。</p>
                <a href="ItemList.php">商品一覧へ戻る</a>
            </article>
        </div>
    </main>    
    <footer>
        <p><small>Copyright &copy; WatchShop All Rights Reserved.</small>
    </footer>
</body>
</html>