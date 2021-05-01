<?php
$host = 'localhost';
$username = 'codecamp41107';
$dbpassword = 'codecamp41107';
$dbname = 'codecamp41107';
$charset = 'utf8';

//MySQL用のDSN文字列
$dsn = 'mysql:dbname='.$dbname.';host='.$host.';charset='.$charset;

$user_name = '';
$password = '';
$err_msg = array();
//セッション開始
session_start();

try{
    //DB接続
    $dbh = new PDO($dsn,$username,$dbpassword,array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4'));
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    //データチェック
    if(isset($_POST['login']) ===TRUE){
        if(isset($_POST['username']) === TRUE){
            $user_name = $_POST['username'];
        }
        if(isset($_POST['password']) === TRUE){
            $password = $_POST['password'];
        }
        
        if($user_name === ''){
            $err_msg[] = 'ユーザー名を入力してください';
        }else if(mb_ereg_match('^(\s|　)+$',$user_name) === TRUE){
            $err_msg[] = 'ユーザー名にスペースが入力されています';
        }
        if($password === ''){
            $err_msg[] = 'パスワードを入力してください';
        }
        //エラーが無ければDBから値を取得する
        if(count($err_msg) === 0){
            //DBからユーザー名とパスワードを取得する
            $sql = 'SELECT * FROM users where username = \''.$user_name.'\' AND password = \''.$password.'\'';
            //var_dump($sql);
            //SQL実行の準備をする
            $stmt = $dbh->prepare($sql);
            //SQLを実行
            $stmt->execute();
            //SQLの結果を配列に保存
            $data = $stmt->fetchAll();
            //var_dump($data);
            if(count($data) === 0){
                $err_msg[] = 'ユーザー名もしくはパスワードが違います';
            }else{
                $_SESSION['user_id'] = $data[0]['id'];
                $_SESSION['user_name'] = $data[0]['username'];
            }
            
        }
    }
    //すでにログイン状態の場合は商品一覧へリダイレクト
    if(isset($_SESSION['user_id']) === TRUE){
        header('Location: ItemList.php');
        exit();
    }
}catch(PDOException $e){
    $err_msg['db_connect'] = 'DBエラー：'.$e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>ログイン</title>
    <link rel="stylesheet" href="./css/html5reset-1.6.1.css" />
    <link rel="stylesheet" href="./css/LoginPage.css" />
</head>
<body>
    <header>
        <img src="./img/logo.jpg">
    </header>
    <main>
        <div class="box">
            <img src="./img/NoTop.jpg">
            <form class="loginform" method="post">
                <p>ユーザーIDとパスワードを入力してください</p>
                <label for="username">ユーザーID：</label>
                <input type="text" name="username" id="username"/><br>
                <label for="passwd">パスワード：</label>
                <input type="password" name="password" id="passwd"/><br>
                <p class="add">IDとパスワードをお持ちでない方は<a href="AddUser.php" target="_blank">会員登録へ</a></p>
                <input type="submit" name="login" value="ログイン"/>
                <?php foreach($err_msg as $value) { ?>
                <p class="errmsg"><?php print $value?></p>
                <?php } ?>
            </form>
        </div>
    </main>
    <footer>
        <p><small>Copyright &copy; WatchShop All Rights Reserved.</small>
    </footer>
</body>
</html>