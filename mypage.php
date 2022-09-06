<?php
  require('function.php');
  require('auth.php');
  $title = 'マイページ';
  debugStart();

  $u_id = $_SESSION['u_id'];

  $user = getUserNameIcon($u_id);
  $join = getJoinEvent($u_id,1,3,0,5,false);
  $board = getMessageAll($u_id,1,0,5);
  $organize = getOrganizerEvent($u_id,1,3,0,0,0,9,0,5,false,true);
  $fav = getFavEvent($u_id,1,3,0,5,true);

  require('head.php');
?>
  <body>
    <p class="js_success" style="display: none;"><?php getSuccess(); ?></p>
    <?php require('header.php'); ?>
    <main class="main site_width">
      <h2 class="heading col_2_style_heading">マイページ</h2>
      <div class="col_2_style">
        <section class="col_2_main_wrapper">
          <div class="mypage_event_list">
            <h3 class="text_centered">参加イベント</h3>
            <?php if(!empty($join['total_record'])){ ?>
              <p class="mypage_event_result">
                最新<?php echo count($join['data']); ?>件&nbsp;/&nbsp;全<?php echo $join['total_record']; ?>件
              </p>
            <?php } ?>
            <article class="mypage_info_box <?php if(!empty($join['total_record'])) echo 'event_title_box'; ?>">
            <?php
              if(!empty($join['total_record'])){
                foreach($join['data'] as $key => $val){
            ?>
              <p class="mypage_event_title_wrapper"><a href="event_detail.php?e_id=<?php echo $val['e_id']; ?>" class="event_link"><?php echo sanitize($val['e_name']); ?></a></p>
              <?php } ?>
              <p class="mypage_to_link_wrapper"><a href="join.php" class="menu_link"><?php echo ($join['total_record'] < 6) ? '詳細へ' : '一覧へ'; ?></a></p>
            <?php }else{ ?>
              <p class="text_centered">予定はありません</p>
              <p class="text_centered"><a href="index.php" class="link">こちら</a>からイベントを探しましょう</p>
            <?php } ?>
            </article>
            <h3 class="text_centered">連絡掲示板</h3>
            <?php if(!empty($board['total_record'])){ ?>
              <p class="mypage_event_result">
                最新<?php echo count($board['data']); ?>件&nbsp;/&nbsp;全<?php echo $board['total_record']; ?>件
              </p>
            <?php } ?>
            <article class="mypage_info_box">
            <?php if(!empty($board['total_record'])){ ?>
              <table>
                <tbody>
                  <tr class="table_heading">
                    <th>最終送信</th>
                    <th>お相手</th>
                    <th>メッセージ</th>
                  </tr>
                <?php foreach($board['data'] as $key => $val){ ?>
                  <tr>
                    <td><a class="table_link" href="messenger.php?b_id=<?php echo $val['b_id']; ?>"><?php datetime2daytime($val['last_time']); ?></a></td>
                    <td class="board_name_cell"><a class="table_link" href="messenger.php?b_id=<?php echo $val['b_id']; ?>"><?php echo sanitize($val['member']['name']); ?>&nbsp;さん</a></td>
                    <td class="board_msg_cell"><a class="table_link" href="messenger.php?b_id=<?php echo $val['b_id']; ?>">
                      <?php
                        if(!empty($val['text']['msg'])){
                          if(mb_strlen($val['text']['msg']) > 17){
                            echo mb_substr(preg_replace('/<br>+/','',sanitize($val['text']['msg'])),0,17,'UTF-8').'...';
                          }else{
                            echo sanitize($val['text']['msg']);
                          }
                        }else{
                          echo '(まだやりとりはありません)';
                        }
                      ?>
                    </a></td>
                  </tr>
                  <?php } ?>
                </tbody>
              </table>
              <p class="mypage_to_link_wrapper"><a href="board.php" class="menu_link"><?php echo ($board['total_record'] < 6) ? '詳細へ' : '一覧へ'; ?></a></p>
            <?php }else{ ?>
              <p class="text_centered">まだやりとりはありません</p>
            <?php } ?>
            </article>
            <h3 class="text_centered">主催イベント</h3>
            <?php if(!empty($organize['total_record'])){ ?>
              <p class="mypage_event_result">
                最新<?php echo count($organize['data']); ?>件&nbsp;/&nbsp;全<?php echo $organize['total_record']; ?>件
              </p>
            <?php } ?>
            <article class="mypage_info_box <?php if(!empty($organize['total_record'])) echo 'event_title_box'; ?>">
            <?php
              if(!empty($organize['total_record'])){
                foreach($organize['data'] as $key => $val){
            ?>
              <p class="mypage_event_title_wrapper"><a href="event_detail.php?e_id=<?php echo $val['e_id']; ?>" class="event_link"><?php echo sanitize($val['e_name']); ?></a></p>
              <?php } ?>
              <p class="mypage_to_link_wrapper"><a href="organize.php" class="menu_link"><?php echo ($organize['total_record'] < 6) ? '詳細へ' : '一覧へ'; ?></a></p>
            <?php }else{ ?>
              <p class="text_centered">予定はありません</p>
              <p class="text_centered"><a href="event_edit.php" class="link">こちら</a>からイベントを登録できます</p>
            <?php } ?>
            </article>
            <h3 class="text_centered">お気に入りイベント</h3>
            <?php if(!empty($fav['total_record'])){ ?>
              <p class="mypage_event_result">
                最新<?php echo count($fav['data']); ?>件&nbsp;/&nbsp;全<?php echo $fav['total_record']; ?>件
              </p>
            <?php } ?>
            <article class="mypage_info_box <?php if(!empty($fav['total_record'])) echo 'event_title_box'; ?>">
            <?php
              if(!empty($fav['total_record'])){
                foreach($fav['data'] as $key => $val){
            ?>
              <p class="mypage_event_title_wrapper"><a href="event_detail.php?e_id=<?php echo $val['e_id']; ?>" class="event_link"><?php echo sanitize($val['e_name']); ?></a></p>
              <?php } ?>
              <p class="mypage_to_link_wrapper"><a href="favorite.php" class="menu_link"><?php echo ($fav['total_record'] < 6) ? '詳細へ' : '一覧へ'; ?></a></p>
            <?php }else{ ?>
              <p class="text_centered">予定はありません</p>
              <p class="text_centered"><a href="index.php" class="link">こちら</a>からイベントを探しましょう</p>
            <?php } ?>
            </article>
          </div>
        </section>
        <aside class="sidebar">
          <div class="member_mypage_img_wrapper">
            <img src="<?php echo (!empty($user['pic'])) ? sanitize($user['pic']) : 'img/no_image_square.png'; ?>" alt="" class="member_mypage_img">
          </div>
          <p class="member_name"><?php echo sanitize($user['name']); ?>&nbsp;さん</p>
          <nav class="mypage_menu_wrapper">
            <ul>
              <li class="mypage_menu"><a href="prof_edit.php" class="menu_link">プロフィール編集</a></li>
              <li class="mypage_menu"><a href="pass_edit.php" class="menu_link">パスワード変更</a></li>
              <li class="mypage_menu"><a href="event_edit.php" class="menu_link">イベント登録</a></li>
              <li class="mypage_menu"><a href="organize.php" class="menu_link">主催イベント一覧</a></li>
              <li class="mypage_menu"><a href="join.php" class="menu_link">参加イベント一覧</a></li>
              <li class="mypage_menu"><a href="favorite.php" class="menu_link">お気に入り一覧</a></li>
              <li class="mypage_menu"><a href="board.php" class="menu_link">連絡掲示板一覧</a></li>
              <li class="mypage_menu"><a href="withdraw.php" class="menu_link">退会</a></li>
            </ul>
          </nav>
        </aside>
      </div>
    </main>
<?php
  require('footer.php');
  debugFinish();
?>