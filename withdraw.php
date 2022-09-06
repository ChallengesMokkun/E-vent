<?php
  require('function.php');
  require('auth.php');

  $title = '退会';
  debugStart();

  //退会確認
  if(!empty($_POST['pre_post'])){
    debug('退会確認');
  }
  //入力やり直し
  if(!empty($_POST['undo'])){
    debug('入力やり直し');
  }
  //退会確定
  if(!empty($_POST['post'])){
    debug('POST送信を確認');
    debug('退会確定');

    try{
      //退会処理(論理削除)
      //ユーザーID+主催イベント+参加イベント+お気に入りイベント+参加者名簿(主催・参加両方)
      $u_id = $_SESSION['u_id'];

      $dbh = dbConnect();
      $sql1 = 'UPDATE user SET delete_flag = 1 WHERE u_id = :u_id';
      $sql2 = 'UPDATE event SET delete_flag = 1 WHERE o_id = :u_id';
      $sql3 = 'UPDATE join_list SET delete_flag = 1 WHERE o_id = :u_id';
      $sql4 = 'UPDATE join_list SET delete_flag = 1 WHERE j_id = :u_id';
      $sql5 = 'UPDATE favorite SET delete_flag = 1 WHERE u_id = :u_id';
      $data = array(':u_id' => $u_id);

      $stmt1 = queryPost($dbh,$sql1,$data);
      $stmt2 = queryPost($dbh,$sql2,$data);
      $stmt3 = queryPost($dbh,$sql3,$data);
      $stmt4 = queryPost($dbh,$sql4,$data);
      $stmt5 = queryPost($dbh,$sql5,$data);

      if($stmt1){
        //強制ログアウトさせる
        session_destroy();
        debug('退会完了');
        debug('トップページへ移動');
        header('Location:index.php');
        exit();
      }else{
        debug('クエリ失敗');
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
      <h2 class="heading">退会</h2>
      <section class="form_width">
        <?php if(empty($_POST['pre_post'])){ ?>
        <div class="msg_area"></div>
        <div class="description">
          <p>主催イベントやお気に入りなど、</p>
          <p>すべてのデータが削除されます。</p>
          <p>よろしいでしょうか。</p>
        </div>
        <form action="" method="post" class="form_wrapper">
          <div class="btn_wrapper">
            <input type="submit" value="退会する" name="pre_post" class="btn btn_normal btn_active">
          </div>
          <div class="btn_wrapper margin_top">
            <a href="mypage.php" class="btn btn_normal btn_gray">戻る</a>
          </div>
        </form>

        <?php }else{ ?>
        <div class="msg_area"></div>
        <div class="description">
          <p>退会なさいますと復元できません。</p>
          <p>本当によろしいでしょうか。</p>
        </div>
        <form action="" method="post" class="form_wrapper">
          <div class="btn_wrapper">
            <input type="submit" value="戻る" name="undo" class="btn btn_normal btn_gray">
          </div>
          <div class="btn_wrapper margin_top">
            <input type="submit" value="退会する" name="post" class="btn btn_normal btn_final">
          </div>
        </form>
        <?php } ?>
      </section>
    </main>
<?php
  require('footer.php');
  debugFinish();
?>