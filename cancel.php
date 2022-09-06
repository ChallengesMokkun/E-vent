<?php
  require('function.php');
  require('auth.php');
  $title = '参加キャンセル';

  $e_id = (!empty($_GET['e_id']) && is_numeric($_GET['e_id'])) ? (int)$_GET['e_id'] : NULL;
  $u_id = $_SESSION['u_id'];

  $join_flag = is_JoinUser($e_id,$u_id);
  if(!$join_flag){
    debug('GETパラメータが不正な値');
    debug('参加イベント一覧ページに移動');
    header('Location:join.php');
    exit();
  }

  $event = getEventBrief($e_id);
  if(empty($event)){
    debug('イベント詳細が取得できない');
    debug('参加イベント一覧ページに移動');
    header('Location:join.php');
    exit();
  }elseif(time() > strtotime($event['finish'])){
    debug('更新期限切れ');
    debug('参加イベント一覧ページに移動');
    header('Location:join.php');
    exit();
  }
  debugStart();

  $title = 'キャンセル '.$event['e_name'];

  if(!empty($_POST)){
    debug('POST送信を確認');
    debug('参加キャンセル');
    try{
      //参加者名簿から削除(delete_flag = 1)
      $dbh = dbConnect();
      $sql = 'UPDATE join_list SET delete_flag = 1 WHERE e_id = :e_id AND j_id = :j_id';
      $data = array(
        ':e_id' => $e_id,
        ':j_id' => $u_id
      );

      $stmt = queryPost($dbh,$sql,$data);
      if($stmt){
        //参加人数の更新(イベントの参加者人数を1減らす・capa_over = 1なら0にかえる)
        if(!empty($event['capa_over'])){
          $sql = 'UPDATE event SET participants = :participants,capa_over = 0 WHERE e_id = :e_id AND delete_flag = 0';
        }else{
          $sql = 'UPDATE event SET participants = :participants WHERE e_id = :e_id AND delete_flag = 0';
        }
        $data = array(
          ':participants' => $event['participants'] - 1,
          ':e_id' => $e_id
        );

        $stmt = queryPost($dbh,$sql,$data);
        if($stmt){
          debug('キャンセル完了');
          debug('参加イベント一覧ページへ移動');
          $_SESSION['success'] = SUC12;
          header('Location:join.php');
          exit();

        }else{
          debug('エラー発生: '.$e->getMessage());
          appendErrMsg('common',ERR01);
        }
        
      }else{
        debug('エラー発生: '.$e->getMessage());
        appendErrMsg('common',ERR01);
      }

    }catch (Exception $e){
      debug('エラー発生: '.$e->getMessage());
      appendErrMsg('common',ERR01);
    }
  }

  require('head.php');
?>
  <body>
    <?php require('header.php'); ?>
    <main class="main site_width">
      <div class="msg_area"><?php errPrint('common'); ?></div>
      <h2 class="heading">参加キャンセル</h2>
      <section class="form_width">
        <div class="event_simple_img_wrapper">
          <img src="<?php echo (!empty($event['pic'])) ? sanitize($event['pic']) : 'img/no_image_square.png'; ?>" alt="アイコン" class="member_check_img">
        </div>
        <div class="event_simple_wrapper">
          <p class="event_simple_info">イベント主催者</p>
          <p class="event_simple_info"><?php echo sanitize($event['name']); ?>&nbsp;さん</p>
        </div>
        <div class="event_simple_wrapper">
          <p class="event_simple_info">開始:&nbsp;<?php datetime2Calendar($event['start']); ?></p>
          <p class="event_simple_info">終了:&nbsp;<?php datetime2Calendar($event['finish']); ?></p>
          <p class="event_simple_info">開催地域:&nbsp;オンライン</p>
        </div>
        <div class="introduction">
          <p>イベント参加をキャンセルします。</p>
          <p>よろしいでしょうか。</p>
        </div>
        <form action="" method="post" class="form_wrapper">
          <div class="btn_wrapper">
            <input type="submit" value="キャンセル" name="cancel" class="btn btn_big btn_final">
          </div>
          <div class="btn_wrapper">
            <a href="join.php" class="btn btn_big btn_gray margin_top">戻る</a>
          </div>
        </form>
      </section>
    </main>
<?php
  require('footer.php');
  debugFinish();
?>