<?php
session_start();

$host = 'localhost';
$username = 'codecamp41107';
$dbpassword = 'codecamp41107';
$dbname = 'codecamp41107';
$charset = 'utf8';

//MySQL用のDSN文字列
$dsn = 'mysql:dbname='.$dbname.';host='.$host.';charset='.$charset;

$user_name = '';
$passwd = '';
$gender = '';
$err_msg = array ();

if(isset($_POST['useradd']) === TRUE){
    if(isset($_POST['user_name']) === TRUE){
        $user_name = $_POST['user_name'];
    }
    if(isset($_POST['passwd']) === TRUE){
        $passwd = $_POST['passwd'];
    }
    if(isset($_POST['gender']) === TRUE){
        $gender = $_POST['gender'];
    }
    
    
    if($user_name === ''){
        $err_msg[] = 'ユーザー名を入力してください';
    }else if(mb_strlen($user_name,"UTF-8") < 6){
        $err_msg[] = 'ユーザー名は6文字以上で入力してください';
    }else if(preg_match('/\A(?=.*?[a-z])(?=.*?\d)[a-z\d]{8,100}+\z/i',$user_name) !== 1){
        $err_msg[] = 'ユーザー名を正しい形式で設定してください';
    }
    if($passwd === ''){
        $err_msg[] = 'パスワードを入力してください';
    }else if(mb_strlen($passwd,"UTF-8") < 6){
        $err_msg[] = 'パスワードは6文字以上で入力してください';
    }else if(preg_match('/\A(?=.*?[a-z])(?=.*?\d)[a-z\d]{8,100}+\z/i',$passwd) !== 1){
        $err_msg[] = 'パスワードを正しい形式で設定してください';
    }
    if($gender === ''){
        $err_msg[] = '性別を選択してください';
    }
    //重複チェック
    try{
        $dbh = new PDO($dsn,$username,$dbpassword, array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4'));
        $dbh->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
        $dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES,false);
        
        $sql = 'SELECT count(*) AS cnt FROM users where username = ?';
        $stmt = $dbh->prepare($sql);
        $stmt->bindValue(1,$user_name,PDO::PARAM_STR);
        $stmt->execute();
        $record = $stmt->fetch();
        if($record['cnt'] > 0){
            $err_msg[] = '既に登録済みのユーザー名です';
        }
    }catch(PDOException $e){
        $err_msg['db_connect'] = 'DBエラー：'.$e->getMessage();
    }
    if(count($err_msg) === 0){
        $_SESSION['join'] = $_POST;
        header('Location: ResultAddUser.php');
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>ユーザー登録</title>
    <link rel="stylesheet" href="./css/html5reset-1.6.1.css" />
    <link rel="stylesheet" href="./css/AddUser.css" />
</head>
<body>
    <header>
        <img src="./img/logo.jpg">
    </header>
    <main>
        <div class="box">
            <img src="./img/NoTop.jpg">
            <form class="loginform" method="post">
                <p class="useradd">会員登録</p>
                <p>ユーザー名とパスワードを設定してください</p>
                <p class="pass">※パスワードは半角英数字6字以上にしてください</p>
                <label for="user_name">ユーザーID：</label>
                <input type="text" name="user_name" id="user_name"/><br>
                <label for="passwd">パスワード：</label>
                <input type="password" name="passwd" id="passwd"/><br>
                性別
                <select name="gender">
                    <option value="1">男性</option>
                    <option value="2">女性</option>
                </select><br>
                <input type="submit" name="useradd" value="会員登録"/>
                <?php foreach($err_msg as $value) { ?> 
                <p class="errmsg"><?php print $value;?></p>
                <?php } ?>
            </form>
        </div>
    </main>
    <footer>
        <p><small>Copyright &copy; WatchShop All Rights Reserved.</small>
    </footer>
</body>
</html>