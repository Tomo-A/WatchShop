<?php
$host = 'localhost';
$username = 'codecamp41107';
$password = 'codecamp41107';
$dbname = 'codecamp41107';
$charset = 'utf8';

//MySQL用のDSN文字列
$dsn = 'mysql:dbname='.$dbname.';host='.$host.';charset='.$charset;

$img_dir = './img/';
$data = array();
$err_msg = array();
$result_msg = array();
$new_img_filename = '';
$item_name = '';
$item_id = '';
$price = '';
$stock = '';
$brand = '';
$gender = '';
$movement = '';
$status = '';
$update_stock = '';
$date = date('Y-m-d H:i:s');

try{
    //データベースに接続
    $dbh = new PDO($dsn,$username,$password, array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4'));
    $dbh->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
    $dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    
    //POST値のチェックを行う
    if(isset($_POST['insert']) === TRUE){
        if(isset($_POST['itemname']) === TRUE){
            $item_name = $_POST['itemname'];
        }
        if(isset($_POST['price']) === TRUE){
            $price = $_POST['price'];
        }
        if(isset($_POST['stock']) === TRUE){
            $stock = $_POST['stock'];
        }
        if(isset($_POST['brand']) === TRUE){
            $brand = $_POST['brand'];
        }
        if(isset($_POST['gender']) === TRUE){
            $gender = $_POST['gender'];
        }
        if(isset($_POST['movement']) === TRUE){
            $movement = $_POST['movement'];
        }
        if(isset($_POST['status']) === TRUE){
            $status = $_POST['status'];
        }
        
        //POST値のエラーチェックを行う
        if($item_name === ''){
            $err_msg[] = '商品名を入力してください';
        }else if(mb_ereg_match('^(\s|　)+$',$item_name) === TRUE){
            $err_msg[] = '商品名に全角、半角スペースは受け付けません';
        }
        if($price === ''){
            $err_msg[] = '値段が未入力です';
        }else if(!preg_match('/^[0-9]+$/', $price)){
            $err_msg[] = '値段を正しい形式で入力してください';
        }
        if($stock === ''){
            $err_msg[] = '在庫数が未入力です';
        }else if(!preg_match('/^[0-9]+$/', $stock)){
            $err_msg[] = '在庫数を正しい形式で入力してください';
        }
        if($gender !== '1' && $gender !== '2'){
            $err_msg[] = '性別の値が不正です';
        }
        if($movement !== '1' && $movement !== '2'){
            $err_msg[] = '種類の値が不正です';
        }
        if($status !== '1' && $status !== '2'){
            $err_msg[] = '公開ステータスの値が不正です';
        }
        
        //HTTP POSTでファイルがアップロードされたかどうかチェック
        if(is_uploaded_file($_FILES['new_img']['tmp_name']) === TRUE){
            //画像の拡張子を取得
            $extension = pathinfo($_FILES['new_img']['name'], PATHINFO_EXTENSION);
            //指定の拡張子であるかどうかチェック
            if($extension === 'jpg' || $extension === 'jpeg' || $extension === 'png'){
                //保存する新しいファイル名の生成（ユニークな値を設定する）
                $new_img_filename = sha1(uniqid(mt_rand(),true)). '.' . $extension;
                //同名ファイルが存在するかどうかチェック
                if(is_file($img_dir . $new_img_filename) !== TRUE){
                    //アップロードされたファイルを指定ディレクトリに移動して保存
                    if(move_uploaded_file($_FILES['new_img']['tmp_name'],$img_dir . $new_img_filename) !== TRUE){
                        $err_msg[] = 'ファイルアップロードに失敗しました';
                    }
                }else{
                    $err_msg[] = 'ファイルアップロードに失敗しました。再度お試しください';
                }
            }else{
                $err_msg[] = 'ファイル形式が異なります。画像ファイルはjpegまたはpng形式にしてください';
            }
        }else{
            $err_msg[] = 'ファイルを選択してください';
        }
        
        //エラーが無ければ商品の登録をする
        if(count($err_msg) === 0){
            try{
                //SQLを準備する
                $sql = 'INSERT INTO items(item_name,price,img,status,stock,brand,movement,gender,createdate,updatedate) VALUES (?,?,?,?,?,?,?,?,now(),now())';
                //SQLを実行する準備をする
                $stmt = $dbh->prepare($sql);
                //値をバインド
                $stmt->bindValue(1,$item_name,PDO::PARAM_STR);
                $stmt->bindValue(2,$price,PDO::PARAM_INT);
                $stmt->bindValue(3,$new_img_filename,PDO::PARAM_STR);
                $stmt->bindValue(4,$status,PDO::PARAM_INT);
                $stmt->bindValue(5,$stock,PDO::PARAM_INT);
                $stmt->bindValue(6,$brand,PDO::PARAM_STR);
                $stmt->bindValue(7,$movement,PDO::PARAM_INT);
                $stmt->bindValue(8,$gender,PDO::PARAM_INT);
                //SQLを実行する
                $stmt->execute();
                $result_msg[] = '商品を追加しました';
            }catch(PDOException $e){
                //エラーを投げる
                throw $e;
            }
        }
    }
    //在庫数を変更する
    else if(isset($_POST['update']) === TRUE){
        if(isset($_POST['stock']) === TRUE){
            $update_stock = $_POST['stock'];
        }
        //エラーチェック
        if($update_stock === ''){
            $err_msg[] = '在庫数を入力してください';
        }else if(!preg_match('/^[0-9]+$/', $update_stock)){
            $err_msg[] = '在庫数を正しい形式で入力してください';
        }
        //商品判別の為にitem_idをhiddenで受け取る
        if(isset($_POST['item_id']) === TRUE){
            $item_id = $_POST['item_id'];
        }
        //エラーが無ければ商品テーブルの個数を変更する
        if(count($err_msg) === 0){
            //SQLを準備する
            $sql = 'UPDATE items SET stock = "'.$update_stock.'", updatedate = now() where item_id = "'.$item_id.'"';
            //SQL実行の準備をする
            $stmt = $dbh->prepare($sql);
            //SQLを実行する
            $stmt->execute();
            $result_msg[] = '在庫数を変更しました';
        }
    }
    //公開と非公開を変更する
    else if(isset($_POST['change_status']) === TRUE){
        if(isset($_POST['status']) === TRUE){
            $status = $_POST['status'];
        }
        if($status !== '1' && $status !== '2'){
            $err_msg[] = '公開ステータスが不正な値です';
        }
        //商品を判別するためにitem_idをhiddenで受け取る
        if(isset($_POST['item_id']) === TRUE){
            $item_id = $_POST['item_id'];
        }
        //エラーが無ければitemsテーブルの公開ステータスを変更する
        if(count($err_msg) === 0){
            //SQLを準備する
            $sql = 'UPDATE
                        items 
                    SET 
                        status = "'.$status.'",
                        updatedate = now()
                    where
                        item_id = '.$item_id;
            //SQL実行の準備をする
            $stmt = $dbh->prepare($sql);
            //SQLを実行する
            $stmt->execute();
            $result_msg[] = '公開ステータスを変更しました。';
        }
    }
    
    //商品の削除処理
    else if(isset($_POST['delete']) === TRUE){
        if(isset($_POST['item_id']) === TRUE){
            $item_id = $_POST['item_id'];
        }
        //DELETE文
        $sql = 'DELETE
                FROM 
                    items 
                where
                    item_id = ?';
        //SQL実行の準備
        $stmt = $dbh->prepare($sql);
        //値をバインド
        $stmt->bindValue(1, $item_id, PDO::PARAM_INT);
        $stmt->execute();
    }
    //商品一覧を取得
    $sql = 'SELECT * FROM items';
    //SQLを実行する準備をする
    $stmt = $dbh->prepare($sql);
    //SQLを実行
    $stmt->execute();
    //dataに値を保存する
    $data = $stmt->fetchAll();
}catch(PDOException $e){
    //接続失敗した場合
    $err_msg['db_connect'] = 'DBエラー：' .$e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>商品管理</title>
    <link rel="stylesheet" href="./css/html5reset-1.6.1.css" />
    <link rel="stylesheet" href="./css/AddItem.css" />
</head>
<body>
    <header>
        <img src="./img/logo.jpg">
    </header>
    <?php foreach($result_msg as $result) { 
    print $result;
    } ?>
    <form method="post" enctype="multipart/form-data">
        <p>商品名：<input type="text" name="itemname" value=""></p>
        <p>値段：<input type="text" name="price" value=""></p>
        <p>在庫数：<input type="text" name="stock" value=""></p>
        <p>ブランド：<input type="text" name="brand" value=""></p>
        <p>
            <select name="gender">
                <option value="1">男性向け</option>
                <option value="2">女性向け</option>
            </select>
        </p>
        <p>
            <select name="movement">
                <option value="1">クォーツ</option>
                <option value="2">機械式</option>
            </select>
        </p>
        <p>商品画像：<input type="file" name="new_img" value=""></p>
        <p>
            <select name="status">
                <option value="1">公開</option>
                <option value="2">非公開</option>
            </select>
        </p>
        <p><input type="submit" name="insert" value="商品追加"></p>
    </form>
    <?php foreach($err_msg as $error) { ?>
    <p><?php print $error;?></p>
    <?php } ?>
    <table>
        <tr>
            <th>画像</th>
            <th>商品名</th>
            <th>値段</th>
            <th>在庫数</th>
            <th>ブランド</th>
            <th>性別</th>
            <th>ムーブメント</th>
            <th>ステータス</th>
            <th>削除ボタン</th>
        </tr>
        <?php foreach($data as $value) { ?>
        <tr>
            <td><img src="<?php print $img_dir . $value['img'];?>"></td>
            <td><?php print htmlspecialchars($value['item_name'],ENT_QUOTES);?></td>
            <td><?php print number_format(htmlspecialchars($value['price'],ENT_QUOTES));?>円</td>
            <td>
                <form method="post">
                    <input type="text" name="stock" value="<?php print $value['stock'];?>"/>本&nbsp;&nbsp;<input type="submit" name="update" value="変更">
                    <input type="hidden" name="item_id" value="<?php print $value['item_id'];?>"/>
                </form>
            </td>
            <td><?php print htmlspecialchars($value['brand'], ENT_QUOTES);?></td>
            <td><?php print $value['gender'];?></td>
            <td><?php print $value['status'];?></td>
            <td>
                <form method="post">
                    <?php if((int)$value['status'] === 1) { ?>
                    <input type="submit" name="change_status" value="公開→非公開"/>
                    <input type="hidden" name="status" value="2"/>
                    <?php }else{ ?>
                    <input type="submit" name="change_status" value="非公開→公開"/>
                    <input type="hidden" name="status" value="1"/>
                    <?php } ?>
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
</body>
</html>