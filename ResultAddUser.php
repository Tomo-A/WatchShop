<?php
session_start();
if(!isset($_SESSION['join'])){
    header('Location: AddUser.php');
    exit();
    }
    
$host = 'localhost';
$username = 'codecamp41107';
$dbpassword = 'codecamp41107';
$dbname = 'codecamp41107';
$charset = 'utf8';

//MySQL用のDSN文字列
$dsn = 'mysql:dbname='.$dbname.';host='.$host.';charset='.$charset;

$err_msg = array();
$user_name = $_SESSION['join']['user_name'];
$password = $_SESSION['join']['passwd'];
$gender = (int)$_SESSION['join']['gender'];

try{
    //データベースに接続
    $dbh = new PDO($dsn,$username,$dbpassword, array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4'));
    $dbh->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
    $dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES,false);
    
    //INSERT文を作成
    $sql = 'INSERT INTO users(username,password,gender,createdate,updatedate) VALUES (?,?,?,now(),now())';
    //SQL文を実行する準備
    $stmt = $dbh->prepare($sql);
    //値をバインド
    $stmt->bindValue(1, $user_name, PDO::PARAM_STR);
    $stmt->bindValue(2, $password, PDO::PARAM_STR);
    $stmt->bindValue(3, $gender, PDO::PARAM_INT);
    //SQLを実行
    $stmt->execute();
    
}catch(PDOException $e){
    $err_msg['db_connect'] = 'DBエラー：'.$e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>ユーザー登録完了</title>
    <link rel="stylesheet" href="./css/html5reset-1.6.1.css" />
    <link rel="stylesheet" href="./css/ResultAddUser.css" />
</head>
<body>
     <header>
        <img src="./img/logo.jpg">
    </header>
    <main>
        <div class="box">
            <img src="./img/NoTop.jpg">
            <div class="inbox">
            <p>会員登録が完了しました</p>
            <a href="LoginPage.php" target="_blank">ログインページへ</a>
            </div>
        </div>
    </main>
    <footer>
        <p><small>Copyright &copy; WatchShop All Rights Reserved.</small>
    </footer>
</body>
</html>