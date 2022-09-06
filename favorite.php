<?php
  require('function.php');
  require('auth.php');
  $title = 'お気に入り一覧';
  debugStart();

  $current_page = (!empty($_GET['p']) && is_numeric($_GET['p'])) ? (int)$_GET['p'] : 1;

  $u_id = $_SESSION['u_id'];
  $record_span = 5;//$record_spanを要修正
  $current_min_record = ($current_page - 1) * $record_span;

  $order = (!empty($_GET['order']) && is_numeric($_GET['order'])) ? $_GET['order'] : 0;
  $future = (!empty($_GET['future']) && is_numeric($_GET['future'])) ? $_GET['future'] : 0;
  $past_flag = (!empty($_GET['past_flag'])) ? true : false;

  $record = getFavEvent($u_id,$current_page,$order,$future,$record_span,$past_flag);

  $category_list = getCategory();
  $area_list = getArea();
  $age_list = getAge();

  require('head.php');
?>
  <body>
    <?php require('header.php'); ?>
    
    <main class="main site_width">
      <h2 class="heading col_2_style_heading">お気に入り一覧</h2>
      <?php if(!empty($record['data']) && !empty($record['total_record'])){ ?>
        <p class="research_result"><?php echo $record['total_record']; ?>件のイベントがあります&nbsp;|&nbsp;<?php echo sanitize($current_page); ?>ページ目&nbsp;|&nbsp;<?php echo $current_min_record + 1; ?>&nbsp;-&nbsp;<?php echo count($record['data']) + $current_min_record; ?>件</p>
      <?php }else{ ?>
        <p class="research_result">お気に入りイベントはありません</p>
        <p class="research_result"><a href="index.php" class="link">こちら</a>からイベントを探しましょう</p>
      <?php } ?>
      <div class="col_2_style">
        <section class="col_2_main_wrapper">
          <div class="event_list">
          <?php
            if(!empty($record)){
              foreach($record['data'] as $key => $val){
          ?>
            <div class="event_info_row">
              <div class="event_list_img_wrapper">
                <img src="<?php echo (!empty($val['pic1'])) ? sanitize($val['pic1']) : 'img/no_image.png'; ?>" alt="イベント" class="event_list_img">
              </div>
              <div class="event_list_wrapper">
                <?php if(!empty($val['participants'])){ ?>
                <p class="event_indicate"><?php echo $val['participants']; ?>&nbsp;人参加予定！！</p>
                <?php } ?>
                <h3 class="event_info_title"><?php echo sanitize($val['e_name']); ?></h3>
                <p class="event_info"><?php echo ((int)$val['c_id'] === 99) ? 'その他': $category_list[($val['c_id'] - 1)]['category']; ?></p>
                <p class="event_info">地域:&nbsp;<?php echo $area_list[($val['area_id'] - 1)]['area']; ?></p>
                <p class="event_info">開催:<?php datetime2Calendar($val['start']); ?>&nbsp;〜&nbsp;<?php datetime2Calendar($val['finish']); ?></p>
                <div class="event_info_command_wrapper">
                  <p class="event_info_command"><a href="event_detail.php<?php echo (!empty(keepGETparam())) ? keepGETparam().'&e_id='.$val['e_id'] : '?e_id='.$val['e_id']; ?>" class="menu_link">詳細をみる</a></p>
                  <p class="event_info_command"><a href="delete_favorite.php?e_id=<?php echo $val['e_id']; ?>" class="menu_link">お気に入りから削除</a></p>
                </div>
              </div>
            </div>
          <?php
              }
            }
          ?>
          </div>
          <?php pagenation('join.php',$current_page,$record['total_page'],5); ?>
          <div class="btn_wrapper margin_top">
            <a href="mypage.php" class="btn btn_normal btn_gray">戻る</a>
          </div>
        </section>
        <?php require('sidebar.php'); ?>
      </div>
    </main>
<?php
  require('footer.php');
  debugFinish();
?>