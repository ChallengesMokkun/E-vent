<?php
  require('function.php');
  require('auth.php');
  $title = 'お気に入り削除';

  $e_id = (!empty($_GET['e_id']) && is_numeric($_GET['e_id'])) ? (int)$_GET['e_id'] : NULL;
  $u_id = $_SESSION['u_id'];

  $fav_flag = is_Favorite($e_id,$u_id);
  if(!$fav_flag){
    debug('GETパラメータが不正な値');
    debug('お気に入り一覧ページに移動');
    header('Location:favorite.php');
    exit();
  }

  $event = getEventBrief($e_id);
  if(empty($event)){
    debug('イベント詳細が取得できない');
    debug('お気に入り一覧ページに移動');
    header('Location:favorite.php');
    exit();
  }
  debugStart();

  if(!empty($_POST)){
    debug('POST送信を確認');
    debug('お気に入り削除');
    try{
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
          debug('お気に入り一覧ページに移動');
          header('Location:favorite.php');
          exit();
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
      <h2 class="heading">お気に入り登録の削除</h2>
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
          <p>お気に入り登録を削除します。</p>
          <p>よろしいでしょうか。</p>
        </div>
        <form action="" method="post" class="form_wrapper">
          <div class="btn_wrapper">
            <input type="submit" value="削除する" name="delete" class="btn btn_normal btn_active">
          </div>
          <div class="btn_wrapper">
            <a href="favorite.php" class="btn btn_normal btn_gray margin_top">戻る</a>
          </div>
        </form>
      </section>
    </main>
<?php
  require('footer.php');
  debugFinish();
?>