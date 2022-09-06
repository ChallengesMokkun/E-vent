<?php
  require('function.php');
  require('auth.php');
  $title = 'お気に入り登録・削除';
  debugStart();

  //POST送信され、ログイン期限内のとき
  if(!empty($_POST['e_id']) && !empty($_SESSION['u_id']) && !empty($_SESSION['login_date']) && time() <= $_SESSION['login_date'] + $_SESSION['login_limit']){
    $e_id = $_POST['e_id'];
    $u_id = $_SESSION['u_id'];

    try{
      $fav_flag = is_Favorite($e_id,$u_id);

      if(empty($fav_flag)){
        //お気に入りに追加
        $dbh = dbConnect();
        $sql = 'INSERT INTO favorite (u_id,e_id,create_date) VALUES (:u_id,:e_id,:create_date)';
        $data = array(
          ':u_id' => $u_id,
          ':e_id' => $e_id,
          ':create_date' => date('Y-m-d H:i:s')
        );
        
        $stmt = queryPost($dbh,$sql,$data);
        if($stmt){
          debug('お気に入り登録完了');
        }

      }else{
        //お気に入りから削除(物理削除)
        $dbh = dbConnect();
        $sql = 'DELETE FROM favorite WHERE u_id = :u_id AND e_id = :e_id';
        $data = array(
          ':u_id' => $u_id,
          ':e_id' => $e_id
        );
        
        $stmt = queryPost($dbh,$sql,$data);
        if($stmt){
          debug('お気に入り削除完了');
        }
      }

    }catch (Exception $e){
      debug('エラー発生: '.$e->getMessage());
    }
  }