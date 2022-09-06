<?php
  require('function.php');
  require('auth.php');
  $title = '参加者へ連絡';

  debugStart();

  $u_id = $_SESSION['u_id'];
  $j_id = (!empty($_GET['j_id']) && is_numeric($_GET['j_id'])) ? $_GET['j_id'] : NULL;
  $e_id = (!empty($_GET['e_id']) && is_numeric($_GET['e_id'])) ? $_GET['e_id'] : NULL;

  $joiner_flag = is_JoinUser($e_id,$j_id);
  if(empty($joiner_flag)){
    debug('GETパラメータが不正な値');
    debug('主催イベント一覧へ移動');
    header('Location:organize.php');
    exit();
  }

  $organizer_flag = is_Organizer($e_id,$u_id);
  if(empty($organizer_flag)){
    debug('GETパラメータが不正な値');
    debug('主催イベント一覧へ移動');
    header('Location:organize.php');
    exit();
  }

  $b_id = searchBoard($j_id,$u_id);
  if(empty($b_id)){
    try{
      $dbh = dbConnect();
      $sql = 'INSERT INTO board (u_id1,u_id2,last_time,create_date) VALUES (:u_id1,:u_id2,:last_time,:create_date)';
      $data = array(
        ':u_id1' => $j_id,
        ':u_id2' => $u_id,
        ':last_time' => date('Y-m-d H:i:s'),
        ':create_date' => date('Y-m-d H:i:s')
      );

      $stmt = queryPost($dbh,$sql,$data);
      if($stmt){
        debug('掲示板作成完了');
        debug('掲示板へ移動');
        header('Location:messenger.php?b_id='.$dbh->lastInsertId());
        exit();

      }else{
        debug('クエリ失敗');
        debug('主催イベント一覧へ移動');
        header('Location:organize.php');
        exit();
      }
    }catch (Exception $e){
      debug('エラー発生: '.$e->getMessage());
      debug('主催イベント一覧へ移動');
      header('Location:organize.php');
      exit();
    }

  }else{
    debug('掲示板へ移動');
    header('Location:messenger.php?b_id='.$b_id);
    exit();
  }
