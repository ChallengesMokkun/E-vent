<?php
  require('function.php');
  require('auth.php');
  $title = '連絡掲示板';

  $u_id = $_SESSION['u_id'];
  $b_id = (!empty($_GET['b_id']) && is_numeric($_GET['b_id'])) ? $_GET['b_id'] : NULL;
  if(empty($b_id)){
    debug('GETパラメータが不正な値');
    debug('マイページに移動');
    header('Location:mypage.php');
    exit();
  }
  $msg_ids = getMsgIDs($u_id,$b_id);
  if(empty($msg_ids)){
    debug('GETパラメータが不正な値');
    debug('マイページに移動');
    header('Location:mypage.php');
    exit();
  }

  debugStart();

  //相手のidを取得する
  if($msg_ids['u_id1'] !== $u_id){
    $partner_id = $msg_ids['u_id1'];
  }else{
    $partner_id = $msg_ids['u_id2'];
  }

  //ユーザー情報の取得
  $my_info = getUserNameIcon($u_id);
  $partner_info = getUserNameIcon($partner_id);
  //メッセージの取得
  $message = getMessageDetail($b_id);

  $title = (!empty($partner_info)) ? sanitize($partner_info['name']).'&nbsp;さん' : $title;
  
  if(!empty($_POST)){
    debug('POST送信を確認');

    $msg = $_POST['msg'];
    //バリデーション
    validMaxlen($msg,'common',500,MAX04);
    validEnterOkZero($msg,'common',ABS04);

    if(empty($err_msg)){
      debug('バリデーションチェックOK');

      try{
        $dbh = dbConnect();
        $sql = 'UPDATE board SET last_time = :last_time WHERE delete_flag = 0 AND b_id = :b_id';
        $data = array(
          ':last_time' => date('Y-m-d H:i:s'),
          ':b_id' => $b_id
        );

        $stmt = queryPost($dbh,$sql,$data);
        if($stmt){
          $sql = 'INSERT INTO msg (msg,b_id,f_id,t_id,send_date,create_date) VALUES (:msg,:b_id,:f_id,:t_id,:send_date,:create_date)';
          $data = array(
            ':msg' => $msg,
            ':b_id' => $b_id,
            ':f_id' => $u_id,
            ':t_id' => $partner_id,
            ':send_date' => date('Y-m-d H:i:s'),
            ':create_date' => date('Y-m-d H:i:s')
          );
  
          $stmt = queryPost($dbh,$sql,$data);
          if($stmt){
            debug('メッセージ送信完了');
            debug('ページ更新');
            header('Location:messenger.php?b_id='.$b_id);
          }else{
            debug('クエリ失敗');
            appendErrMsg('common',ERR01);
          }

        }else{
          debug('クエリ失敗');
          appendErrMsg('common',ERR01);
        }

      }catch (Exception $e){
        debug('エラー発生: '.$e->getMessage());
        appendErrMsg('common',ERR01);
      }
      
    }else{
      debug('invalid: Catch!');
    }
  }

  require('head.php');
?>
  <body>
    <?php require('header.php'); ?>
    <main class="main site_width">
      <section class="event_info_list_wrapper">
        <div class="messenger_wrapper">
          <div class="member_prof_area">
            <div class="member_prof_img_wrapper">
              <img src="<?php echo (!empty($partner_info['pic'])) ? sanitize($partner_info['pic']) : 'img/no_image_square.png'; ?>" alt="お相手" class="member_mypage_img">
            </div>
            <p class="member_name"><?php echo sanitize($partner_info['name']); ?>&nbsp;さん</p>
          </div>
          <form method="post" action="" class="messenger_area">
            <div class="messenger_form_wrapper">
              <textarea name="msg" class="messenger_textarea"></textarea>
            </div>
            <div class="btn_wrapper btn_messenger_wrapper">
              <input type="submit" value="送信" class="btn btn_small btn_final btn_messenger">
            </div>
          </form>
          <div class="message_history_area">
            <div class="msg_area emphasis"><?php errPrint('common'); ?></div>
          <?php
            if(isset($message[0]['msg'])){
              foreach($message as $key => $val){
                if($val['f_id'] === $partner_id){
          ?>
            <div class="msg_left_wrapper">
              <div class="msg_left">
                <div class="member_icon_img_wrapper">
                  <img src="<?php echo (!empty($partner_info['pic'])) ? sanitize($partner_info['pic']) : 'img/no_image_square.png'; ?>" alt="" class="member_icon_img">
                </div>
                <div class="msg_text">
                  <p class="msg_text_left"><?php echo nl2br(sanitize($val['msg'])); ?></p>
                  <p class="msg_date_left"><?php echo datetime2daytime($val['send_date']); ?></p>
                </div>
              </div>
            </div>
          <?php }else{ ?>
            <div class="msg_right_wrapper">
              <div class="msg_right">
                <div class="msg_text">
                  <p class="msg_text_right"><?php echo nl2br(sanitize($val['msg'])); ?></p>
                  <p class="msg_date_right"><?php echo datetime2daytime($val['send_date']); ?></p>
                </div>
                <div class="member_icon_img_wrapper">
                  <img src="<?php echo (!empty($my_info['pic'])) ? sanitize($my_info['pic']) : 'img/no_image_square.png'; ?>" alt="" class="member_icon_img">
                </div>
              </div>
            </div>
          <?php
                }
              }
            }else{
          ?>
            <p class="text_centered">やりとりを始めましょう</p>
          <?php } ?>
          </div>
        </div>
        <div class="btn_wrapper">
          <a href="board.php" class="btn btn_normal btn_gray">戻る</a>
        </div>
      </section>
    </main>
<?php
  require('footer.php');
  debugFinish();
?>