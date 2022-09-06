<?php
  require('function.php');
  require('auth.php');
  $title = '参加者一覧';
  debugStart();

  $u_id = $_SESSION['u_id'];
  $e_id = (!empty($_GET['e_id']) || is_numeric($_GET['e_id'])) ? $_GET['e_id'] : NULL;
  $event = getMyEventRow($e_id,$u_id);
  if(empty($event)){
    debug('GETパラメータが不正な値');
    debug('主催イベントページに移動');
    header('Location:organize.php');
    exit();
  }
  $join_list = getJoinerList($e_id,$u_id);
  $age_list = getAge();
  $area_list = getArea();

  require('head.php');
?>
  <body>
    <?php require('header.php'); ?>
    
    <main class="main site_width">
      <section class="event_info_list_wrapper">
        <h2 class="heading">参加者一覧</h2>
        <div class="event_info_list">
          <div class="event_info_row">
            <div class="event_join_list_wrapper">
              <div class="event_list_img_wrapper">
                <img src="<?php echo (!empty($event['pic1'])) ? sanitize($event['pic1']) : 'img/no_image.png'; ?>" alt="イベント" class="event_list_img">
              </div>
              <div class="event_info_join_list_wrapper">
                <h3 class="event_info_title"><?php echo sanitize($event['e_name']); ?></h3>
                <p class="event_info">開催:<?php datetime2Calendar($event['start']); ?>&nbsp;〜&nbsp;<?php datetime2Calendar($event['finish']); ?></p>
                <p class="event_info">集合場所:&nbsp;<?php echo (mb_strlen($event['place']) > 26) ? mb_substr(preg_replace('/<br>+/','',sanitize($event['place'])),0,26,'UTF-8').'...' : sanitize($event['place']); ?></p>
                <div class="event_info_command_wrapper">
                  <p class="event_info_command"><a href="event_detail.php<?php echo (!empty(keepGETparam())) ? keepGETparam().'&e_id='.$val['e_id'] : '?e_id='.$val['e_id']; ?>" class="menu_link">詳細をみる</a></p>
                  <?php if(time() < strtotime($event['start'])){ ?>
                  <p class="event_info_command"><a href="event_edit.php?e_id=<?php echo $e_id; ?>" class="menu_link">編集する</a></p>
                  <?php } ?>
                </div>
              </div>
            </div>
          </div>
          <div class="join_info_wrapper">
            <?php if(!empty($join_list[0])){ ?>
            <p class="join_info text_centered">定員:&nbsp;<?php echo sanitize($event['capa']); ?>人&nbsp;申し込み:&nbsp;<?php echo sanitize($event['participants']); ?>人</p>
            <p class="join_info text_centered">参加者にクリックすると連絡を取ることができます</p>
            <table class="join_list">
              <tbody>
                <tr class="table_heading">
                  <th>通し番号</th>
                  <th>参加者</th>
                  <th>ニックネーム</th>
                  <th>年齢</th>
                  <th>お住まい</th>
                  <th>ジェンダー</th>
                </tr>
              <?php foreach($join_list as $key => $val){ ?>
                <tr>
                  <td><a href="pre_contact_organizer.php?e_id=<?php echo $e_id; ?>&j_id=<?php echo $val['j_id']; ?>" class="table_link"><?php echo $key + 1?></a></td>
                  <td class="join_list_img_cell"><a href="pre_contact_organizer.php?e_id=<?php echo $e_id; ?>&j_id=<?php echo $val['j_id']; ?>" class="table_link">
                    <div class="table_icon_img_wrapper">
                      <img src="<?php echo (!empty($val['pic'])) ? sanitize($val['pic']) : 'img/no_image_square.png'; ?>" alt="" class="member_icon_img">
                    </div>
                  </a></td>
                  <td><a href="pre_contact_organizer.php?e_id=<?php echo $e_id; ?>&j_id=<?php echo $val['j_id']; ?>" class="table_link"><?php echo sanitize($val['name']); ?></a></td>
                  <td><a href="pre_contact_organizer.php?e_id=<?php echo $e_id; ?>&j_id=<?php echo $val['j_id']; ?>" class="table_link">
                    <?php echo (!empty($val['age_id']) && !empty($val['age_flag'])) ? $age_list[($val['age_id'] - 1)]['age'] : ''; ?>
                  </a></td>
                  <td><a href="pre_contact_organizer.php?e_id=<?php echo $e_id; ?>&j_id=<?php echo $val['j_id']; ?>" class="table_link">
                    <?php echo (!empty($val['area_id']) && (!empty($val['area_flag']))) ? $area_list[($val['area_id'] - 1)]['area'] : ''; ?>
                  </a></td>
                  <td><a href="pre_contact_organizer.php?e_id=<?php echo $e_id; ?>&j_id=<?php echo $val['j_id']; ?>" class="table_link">
                    <?php
                      if(!empty($val['gender']) && !empty(['gender_flag'])){
                        switch ($val['gender']){
                          case 1:
                            echo '女性';
                            break;
                          case 2:
                            echo '男性';
                            break;
                        }
                      }
                    ?>
                  </a></td>
                </tr>
              <?php } ?>
              </tbody>
            </table>
            <?php }else{ ?>
            <p class="text_centered">まだ申し込みはありません</p>
            <?php } ?>
          </div>
        </div>
        <div class="btn_wrapper">
          <a href="organize.php" class="btn btn_normal btn_gray">戻る</a>
        </div>
      </section>
    </main>
<?php
  require('footer.php');
  debugFinish();
?>