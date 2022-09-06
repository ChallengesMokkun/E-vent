<?php
  require('function.php');
  $title = '主催イベント一覧';
  debugStart();

  $current_page = (!empty($_GET['p']) && is_numeric($_GET['p'])) ? (int)$_GET['p'] : 1;

  $record_span = 5;//$record_spanを要修正
  $current_min_record = ($current_page - 1) * $record_span;

  $o_id = (!empty($_GET['o_id']) && is_numeric($_GET['o_id'])) ? $_GET['o_id'] : NULL;
  if(empty($o_id)){
    debug('GETパラメータが不正な値');
    debug('トップページへ移動');
    header('Location:index.php');
    exit();
  }

  

  $order = (!empty($_GET['order']) && is_numeric($_GET['order'])) ? $_GET['order'] : 0;
  $future = (!empty($_GET['future']) && is_numeric($_GET['future'])) ? $_GET['future'] : 0;
  $c_id = (!empty($_GET['c_id']) && is_numeric($_GET['c_id'])) ? $_GET['c_id'] : 0;
  $area_id = (!empty($_GET['area_id']) && is_numeric($_GET['area_id'])) ? $_GET['area_id'] : 0;
  $gender = (!empty($_GET['gender']) && is_numeric($_GET['gender'])) ? $_GET['gender'] : 0;
  $age_id = (!empty($_GET['age_id']) && is_numeric($_GET['age_id'])) ? $_GET['age_id'] : 0;
  $past_flag = (!empty($_GET['past_flag'])) ? true : false;
  $capa_over = (!empty($_GET['capa_over'])) ? true : false;


  $record = getOrganizerEvent($o_id,$current_page,$order,$future,$c_id,$area_id,$gender,$age_id,$record_span,$past_flag,$capa_over);

  $category_list = getCategory();
  $area_list = getArea();
  $age_list = getAge();

  require('head.php');
?>
  <body>
    <p class="js_success" style="display: none;"><?php getSuccess(); ?></p>
    <?php require('header.php'); ?>
    <main class="main site_width">
    <h2 class="heading col_2_style_heading">イベント一覧</h2>
      <?php if(!empty($record['data']) && !empty($record['total_record'])){ ?>
        <p class="research_result"><?php echo $record['total_record']; ?>件の予定が見つかりました&nbsp;|&nbsp;<?php echo sanitize($current_page); ?>ページ目&nbsp;|&nbsp;<?php echo $current_min_record + 1; ?>&nbsp;-&nbsp;<?php echo count($record['data']) + $current_min_record; ?>件</p>
      <?php }else{ ?>
        <p class="research_result">イベントが見つかりませんでした。</p>
      <?php } ?>
      <div class="col_2_style">
        <section class="col_2_main_wrapper">
          <div class="event_list">
          <?php
            if(!empty($record)){
              foreach($record['data'] as $key => $val){
          ?>
            <a href="event_detail.php<?php echo (!empty(keepGETparam())) ? keepGETparam().'&e_id='.$val['e_id'] : '?e_id='.$val['e_id']; ?>" class="event_list_row event_link">
              <div class="event_list_img_wrapper">
                <img src="<?php echo (!empty($val['pic1'])) ? sanitize($val['pic1']) : 'img/no_image.png'; ?>" alt="イベント" class="event_list_img">
              </div>
              <div class="event_list_wrapper">
                <?php if(!empty($val['participants'])){ ?>
                <p class="event_indicate"><?php echo $val['participants']; ?>&nbsp;人参加予定！！</p>
                <?php }else{ ?>
                <p class="event_indicate">参加者募集中！</p>
                <?php } ?>
                <h3 class="event_info_title"><?php echo sanitize($val['e_name']); ?></h3>
                <p class="event_info"><?php echo ((int)$val['c_id'] === 99) ? 'その他': $category_list[($val['c_id'] - 1)]['category']; ?></p>
                <p class="event_info">地域:&nbsp;<?php echo $area_list[($val['area_id'] - 1)]['area']; ?></p>
                <p class="event_info">開催:<?php datetime2Calendar($val['start']); ?>&nbsp;〜&nbsp;<?php datetime2Calendar($val['finish']); ?></p>
                <p class="event_info"><?php echo (mb_strlen($val['comment']) > 60) ? mb_substr(preg_replace('/<br>+/','',sanitize($val['comment'])),0,60,'UTF-8').'...' : sanitize($val['comment']); ?></p>
                <p class="event_guide">続きをみる&gt;&gt;</p>
              </div>
            </a>
          <?php
              }
            }
          ?>
          </div>
          <?php pagenation('organizer_event.php',$current_page,$record['total_page'],5); ?>
          <div class="btn_wrapper margin_top">
            <a href="index.php" class="btn btn_big btn_gray margin_top">トップページに戻る</a>
          </div>
        </section>
        <?php require('sidebar.php'); ?>
      </div>  
    </main>
<?php
  require('footer.php');
  debugFinish();
?>