<?php
  require('function.php');
  $title = 'イベント詳細';
  debugStart();

  $e_id = (!empty($_GET['e_id']) && is_numeric($_GET['e_id'])) ? (int)$_GET['e_id'] : NULL;

  //イベント情報の取得
  if(!empty($e_id)){
    $event = getEventDetail($e_id);
    if(empty($event)){
      debug('イベント詳細が取得できない');
      header('Location:index.php');
      exit();
    }

  }else{
    debug('GETパラメータが不正な値');
    header('Location:index.php');
    exit();
  }

  //ログインしているか確認する
  if(!empty($_SESSION['login_date']) && time() <= $_SESSION['login_date'] + $_SESSION['login_limit']){
    debug('期限内ログインユーザー');
    $u_id = $_SESSION['u_id'];
    $userInfo = getUserProperty($u_id);
    $user_age = $userInfo['age_id'];
    $user_gender = $userInfo['gender'];

    $member_flag = true;

    //イベント参加者かどうか確認する
    $join_flag = is_JoinUser($e_id,$u_id);

    //お気に入りイベントかどうか確認する
    $fav_flag = is_Favorite($e_id,$u_id);
  }else{
    debug('未ログインユーザー または 期限切れログインユーザー');
    $member_flag = false;
    $join_flag = false;
  }

  $age_list = getAge();
  $area_list = getArea();
  $category_list = getCategory();

  $title = $event['e_name'];

  if(!empty($_POST['pre_join']) || !empty($_POST['pre_contact'])){
    debug('POST送信を確認');
    debug('確認のための送信');
  }
  if(!empty($_POST['undo'])){
    debug('POST送信を確認');
    debug('やり直し');
  }

  //参加確定
  if(!empty($_POST['join'])){
    debug('POST送信を確認');
    debug('参加確定');
    try{
      //参加者名簿に挿入
      $dbh = dbConnect();
      $sql = 'INSERT INTO join_list (o_id,j_id,e_id,create_date) VALUES (:o_id,:j_id,:e_id,:create_date)';
      $data = array(
        ':o_id' => $event['o_id'],
        ':j_id' => $u_id,
        ':e_id' => $e_id,
        ':create_date' => date('Y-m-d H:i:s')
      );

      $stmt = queryPost($dbh,$sql,$data);
      if($stmt){
        //参加人数の情報を更新する
        //capa_flag = 0 かつ capa - participants = 1のとき、capa_over = 1にする
        if(empty($event['capa_flag']) && ($event['capa'] - $event['participants'] === 1)){
          $sql = 'UPDATE event SET participants = :participants,capa_over = 1 WHERE e_id = :e_id AND delete_flag = 0';
        }else{
          $sql = 'UPDATE event SET participants = :participants WHERE e_id = :e_id AND delete_flag = 0';
        }
        $data = array(
          ':participants' => $event['participants'] + 1,
          ':e_id' => $e_id
        );

        $stmt = queryPost($dbh,$sql,$data);
        if($stmt){
          debug('参加申し込み完了');
          $_SESSION['success'] = SUC11;
          debug('ページをリロードする');
          header('Location:'.$_SERVER['PHP_SELF'].keepGETparam());
          exit();
        }
        
      }else{
        debug('クエリ失敗');
        appendErrMsg('common',ERR01);
      }
    }catch (Exception $e){
      debug('エラー発生: '.$e->getMessage());
      appendErrMsg('common',ERR01);
    }
  }


  //連絡確定
  if(!empty($_POST['contact'])){
    debug('POST送信を確認');
    debug('連絡確定');
    //既に連絡掲示板があるか確認する
    $b_id = searchBoard($event['o_id'],$u_id);
    //連絡掲示板がない場合は新たに作る
    if(empty($b_id)){
      try{
        $dbh = dbConnect();
        $sql = 'INSERT INTO board (u_id1,u_id2,last_time,create_date) VALUES (:u_id1,:u_id2,:last_time,:create_date)';
        $data = array(
          ':u_id1' => $event['o_id'],
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
          appendErrMsg('common',ERR01);
        }
      }catch (Exception $e){
        debug('エラー発生: '.$e->getMessage());
        appendErrMsg('common',ERR01);
      }

    }else{
      debug('掲示板へ移動');
      header('Location:messenger.php?b_id='.$b_id);
      exit();
    }
  }


  require('head.php');
?>
  <body>
    <p class="js_success" style="display: none;"><?php getSuccess(); ?></p>
    <?php require('header.php'); ?>
    <main class="main site_width">
    <?php if(empty($_POST['pre_join']) && empty($_POST['pre_contact'])){ ?>
      <section class="event_page_wrapper">
        <div class="msg_area"><?php errPrint('common'); ?></div>
        <div class="event_wrapper">
          <div class="event_detail_header">
            <span class="category_wrapper">カテゴリー:&nbsp;<a href="index.php?c_id=<?php echo $event['c_id']; ?>" class="link category"><?php echo ((int)$event['c_id'] === 99) ? 'その他' : $category_list[($event['c_id'] - 1)]['category']; ?></a></span>
          <?php if($member_flag){ ?>
            <span class="event_detail_header_text">主催:&nbsp;<a href="<?php echo 'organizer.php?e_id='.$e_id.'&o_id='.$event['o_id']; ?>" class="link event_detail_header_link"><?php echo sanitize($event['name']); ?></a>&nbsp;さん</span>
          <?php } ?>
          <?php if($member_flag && $event['o_id'] !== $u_id){ ?>
            <i class="fa-solid fa-star js_favorite <?php if(!empty($fav_flag)) echo 'favorite'; ?>" data-fav_event="<?php echo $e_id; ?>"></i>
          <?php } ?>
          </div>
          <h2 class="heading event_heading"><?php echo sanitize($event['e_name']); ?></h2>
          <div class="join_status_wrapper">
            <p class="join_status_text">
              <?php if($event['participants'] > 0){ ?>
              現在&nbsp;<?php echo $event['participants']; ?>人&nbsp;参加予定
              <?php }else{ ?>
              現在&nbsp;参加者募集中！
              <?php } ?>
            </p>
            <div class="join_status_bar_wrapper">
              <p class="join_status_text">
                申し込み状況:
              </p>
              <div class="join_status_bar">
                <div class="js_join_status" data-rate="<?php echo $event['participants'] / sanitize($event['capa']) * 100; ?>"></div>
              </div>
              </div>
            </div>
          <div class="event_detail_img_wrapper">
            <div class="main_img_area_wrapper" style="<?php if(empty($event['pic1'])) echo 'margin-right: 0; width: 100%;'; ?>">
              <img src="<?php echo (!empty($event['pic1'])) ? sanitize($event['pic1']) : 'img/no_image.png'; ?>" alt="" class="js_main_img">
            </div>
            <?php if(!empty($event['pic1'])){ ?>
            <div class="sub_img_area_wrapper">
              <div class="sub_img_wrapper">
                <img src="<?php echo sanitize($event['pic1']); ?>" alt="" class="js_sub_img">
              </div>
              <?php if(!empty($event['pic2'])){ ?>
              <div class="sub_img_wrapper">
                <img src="<?php echo sanitize($event['pic2']); ?>" alt="" class="js_sub_img">
              </div>
              <?php } ?>
              <?php if(!empty($event['pic3'])){ ?>
              <div class="sub_img_wrapper">
                <img src="<?php echo sanitize($event['pic3']); ?>" alt="" class="js_sub_img">
              </div>
              <?php } ?>
            </div>
            <?php } ?>
          </div>
          <div class="event_detail_row">
            <p class="event_detail_heading">イベントの内容</p>
            <p class="event_detail_value_area"><?php echo nl2br(sanitize($event['comment'])); ?></p>
          </div>
          <div class="event_detail_row">
            <p class="event_detail_heading"><?php echo (empty($event['capa_flag'])) ? '定員' : '参加目安'; ?></p>
            <p class="event_detail_value"><?php echo sanitize($event['capa']); ?>人</p>
            <?php if(empty($event['capa_flag']) && $event['capa'] - $event['participants'] > 0 && $event['participants'] > 0){ ?>
            <p class="text_centered emphasis">あと&nbsp;<?php echo sanitize($event['capa']) - $event['participants']; ?>人&nbsp;参加できます!</p>
            <?php } ?>
          </div>
          <div class="event_detail_row">
            <p class="event_detail_heading">日時</p>
            <p class="event_detail_value"><?php datetime2Calendar($event['start']); ?>&nbsp;&nbsp;〜&nbsp;&nbsp;<?php datetime2Calendar($event['finish']); ?></p>
          </div>
          <div class="event_detail_row">
            <p class="event_detail_heading">開催地域</p>
            <p class="event_detail_value"><?php echo $area_list[($event['area_id'] - 1)]['area']; ?></p>
          </div>
          <div class="event_detail_row">
            <p class="event_detail_heading">集合場所</p>
            <?php if(empty($event['place_flag']) || $join_flag){ ?>
            <p class="event_detail_value"><?php echo sanitize($event['place']); ?></p>
            <?php }else{ ?>
            <p class="event_detail_value">参加される方にのみ公開されます</p>
            <?php } ?>
          </div>
          <div class="event_detail_row">
            <p class="event_detail_heading">参加費</p>
            <p class="event_detail_value"><?php echo (!empty($event['fee'])) ? number_format(sanitize($event['fee'])).'円' : '無料'; ?></p>
          </div>
          <div class="event_detail_row">
            <p class="event_detail_heading">対象</p>
            <p class="event_detail_value">
            <?php
              switch($event['gender']){
                case 0:
                  echo '全員';
                  break;
                case 1:
                  echo '女性限定';
                  break;
                case 2:
                  echo '男性限定';
                  break;
              }
            ?>
            </p>
          </div>
          <div class="event_detail_row last_form_row">
            <p class="event_detail_heading">年齢</p>
            <?php if(empty($event['age_min']) && empty($event['age_max'])){ ?>
            <p class="event_detail_value">全年齢</p>
            <?php }else{ ?>
            <p class="event_detail_value">
              <?php if(!empty($event['age_min'])) echo $age_list[($event['age_min'] - 1)]['age']; ?><?php echo (!empty($event['age_max'])) ? '&nbsp' : ''; ?>〜<?php echo (!empty($event['age_min'])) ? '&nbsp' : ''; ?><?php if(!empty($event['age_max'])) echo $age_list[($event['age_max'] - 1)]['age']; ?>
            </p>
            <?php } ?>
          </div>
        </div>
        <form action="" method="post" class="form_wrapper">
        <?php if(empty($member_flag)){ ?>
          <div class="btn_wrapper">
            <a href="login.php" class="btn btn_big btn_active margin_top">ログインして申し込む</a>
          </div>
        <?php }else{ ?>
          <?php if(strtotime($event['start']) > time() && $event['o_id'] !== $u_id && !$join_flag && empty($event['capa_over']) && ((empty($event['gender']) || ((!empty($event['gender']) && $user_gender === $event['gender'])))) && ((empty($event['age_min']) && empty($event['age_max'])) || (($event['age_min'] <= $user_age && $event['age_max'] > 0 && $user_age > 0 && $event['age_max'] >= $user_age) || ($event['age_min'] <= $user_age && (int)$event['age_max'] === 0)))){ ?>
          <div class="btn_wrapper">
            <input type="submit" value="参加する" name="pre_join" class="btn btn_big btn_active">
          </div>
          <?php } ?>
          <?php if($event['o_id'] !== $u_id){ ?>
          <div class="btn_wrapper">
            <input type="submit" value="主催者に連絡する" name="pre_contact" class="btn btn_big btn_active margin_top">
          </div>
          <?php }elseif(time() < strtotime($event['start'])){ ?>
          <div class="btn_wrapper">
            <a href="event_edit.php?e_id=<?php echo $e_id; ?>" class="btn btn_big btn_active">編集する</a>
          </div>
          <?php } ?>
          <?php if(!empty($join_flag) && time() < strtotime($event['finish'])){ ?>
          <div class="btn_wrapper">
            <a href="cancel.php?e_id=<?php echo $e_id; ?>" class="btn btn_big btn_gray margin_top">キャンセルする</a>
          </div>
          <?php } ?>
        <?php } ?>
          <div class="btn_wrapper">
            <a class="btn btn_big btn_gray margin_top" href="
            <?php
              switch(basename($_SERVER['HTTP_REFERER'])){
                case 'organizer_event.php'.keepGETparam(array('e_id')):
                  echo 'organizer_event.php';
                  break;
                case 'organize.php'.keepGETparam(array('e_id')):
                  echo 'organize.php';
                  break;
                case 'join.php'.keepGETparam(array('e_id')):
                  echo 'join.php';
                  break;
                case 'favorite.php'.keepGETparam(array('e_id')):
                  echo 'favorite.php';
                  break;
                case 'mypage.php';
                  echo 'mypage.php';
                  break;
                default:
                  echo 'index.php';
                  break;
              }
              if(!empty(keepGETparam(array('e_id')))){
                echo keepGETparam(array('e_id'));
              }
            ?>
            ">戻る</a>
          </div>
        </form>
      </section>
    <?php }else{ ?>
      <h2 class="heading"><?php echo sanitize($event['e_name']); ?></h2>
      <section class="form_width">
        <div class="event_simple_img_wrapper">
          <img src="<?php echo (!empty($event['pic'])) ? sanitize($event['pic']) : 'img/no_image_square.png'; ?>" alt="アイコン" class="member_check_img">
        </div>
        <div class="event_simple_wrapper">
          <p>イベント主催者</p>
          <p><?php echo sanitize($event['name']); ?>&nbsp;さん</p>
        </div>
        <div class="event_simple_wrapper">
          <p class="event_simple_info">開始:&nbsp;<?php datetime2Calendar($event['start']); ?></p>
          <p class="event_simple_info">終了:&nbsp;<?php datetime2Calendar($event['finish']); ?></p>
          <p class="event_simple_info">開催地域:&nbsp;<?php echo $area_list[($event['area_id'] - 1)]['area']; ?></p>
        </div>
        <?php if(!empty($_POST['pre_join'])){ ?>
        <div class="introduction">
          <p>こちらのイベントに参加します。</p>
          <p>よろしいでしょうか。</p>
        </div>
        <?php }else{ ?>
        <div class="introduction">
          <p>こちらのイベントの主催者に</p>
          <p>連絡します。</p>
          <p >よろしいでしょうか。</p>
        </div>
        <?php } ?>
        <form action="" method="post" class="form_wrapper">
          <div class="btn_wrapper">
          <?php if(!empty($_POST['pre_join'])){ ?>
            <input type="submit" value="参加する" name="join" class="btn btn_big btn_final">
          <?php }else{ ?>
            <input type="submit" value="連絡する" name="contact" class="btn btn_big btn_final">
          <?php } ?>
          </div>
          <div class="btn_wrapper">
            <input type="submit" value="戻る" name="undo" class="btn btn_big btn_gray margin_top">
          </div>
        </form>
      </section>
    <?php } ?>
    </main>
<?php
  require('footer.php');
  debugFinish();
?>