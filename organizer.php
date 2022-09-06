<?php
  require('function.php');
  $title = '主催者情報';
  debugStart();

  $e_id = (!empty($_GET['e_id']) && is_numeric($_GET['e_id'])) ? (int)$_GET['e_id'] : NULL;
  $o_id = (!empty($_GET['o_id']) && is_numeric($_GET['o_id'])) ? (int)$_GET['o_id'] : NULL;
  $organizer = getOrganizer($e_id,$o_id);
  if(empty($organizer)){
    debug('GETパラメータが不正な値');
    debug('イベントページへ移動');
    header('Location:event_detail.php?e_id='.$e_id);
    exit();
  }
  $profile = $organizer['o_data'];
  $event_total = $organizer['o_event']['count(*)'];
  $join_total = $organizer['o_join']['count(*)'];

  $age_list = getAge();
  $area_list = getArea();

  require('head.php');
?>
  <body>
    <?php require('header.php'); ?>
    <main class="main site_width">
      <h2 class="heading">主催者情報</h2>
      <section class="form_width">
        <div class="check_field_wrapper">
          <div class="event_simple_img_wrapper">
            <img src="<?php echo (!empty($profile['pic'])) ? sanitize($profile['pic']) : 'img/no_image_square.png'; ?>" alt="アイコン" class="member_check_img">
          </div>
          <div class="event_simple_wrapper">
            <p class="event_simple_info"><?php echo sanitize($profile['name']); ?>&nbsp;さん</p>
          </div>
          <?php if(!empty($event_total) && !empty($join_total)){ ?>
          <div class="check_field_row">
            <p class="text_centered">イベント開催回数</p>
            <p class="check_text"><?php echo $event_total.'&nbsp;回'; ?></p>
          </div>
          <div class="check_field_row">
            <p class="text_centered">イベント参加者(延べ人数)</p>
            <p class="check_text"><?php echo $join_total.'&nbsp;人'; ?></p>
          </div>
          <?php }else{ ?>
          <div class="check_field_row">
            <p class="text_centered">イベント開催</p>
            <p class="check_text">初めて</p>
          </div>
          <?php } ?>
          <div class="check_field_row">
            <p class="text_centered">開催予定のイベント</p>
            <p class="check_text"><a href="organizer_event.php?o_id=<?php echo $o_id; ?>" class="link">イベント一覧</a></p>
          </div>
          <?php if(!empty($profile['age_flag']) && !empty($profile['age_id'])){ ?>
          <div class="check_field_row">
            <p class="text_centered">年齢</p>
            <p class="check_text"><?php echo $age_list[($profile['age_id'] - 1)]['age']; ?></p>
          <?php } ?>
          </div>
          <?php if(!empty($profile['area_flag']) && !empty($profile['area_id'])){ ?>
          <div class="check_field_row">
            <p class="text_centered">お住まい</p>
            <p class="check_text"><?php echo $area_list[($profile['area_id'] - 1)]['area']; ?></p>
          </div>
          <?php } ?>
          <?php if(!empty($profile['gender_flag']) && !empty($profile['gender'])){ ?>
          <div class="check_field_row">
            <p class="text_centered">ジェンダー</p>
            <p class="check_text">
            <?php 
              switch($profile['gender']){
                case 1:
                  echo '女性';
                  break;
                case 2:
                  echo '男性';
                  break;
              }
            ?>
            </p>
          </div>
          <?php } ?>
        </div>
      </section>
    </main>
<?php
  require('footer.php');
  debugFinish();
?>