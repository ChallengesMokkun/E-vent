<?php
  require('function.php');
  require('auth.php');
  $title = '連絡掲示板';
  debugStart();

  $current_page = (!empty($_GET['p']) && is_numeric($_GET['p'])) ? (int)$_GET['p'] : 1;

  $u_id = $_SESSION['u_id'];
  $record_span = 20;
  $current_min_record = ($current_page - 1) * $record_span;

  $order = (!empty($_GET['order']) && is_numeric($_GET['order'])) ? $_GET['order'] : 0;

  $record = getMessageAll($u_id,$current_page,$order,$record_span);

  require('head.php');
?>
  <body>
    <?php require('header.php'); ?>
    <main class="main site_width">
     <h2 class="heading col_2_style_heading">連絡掲示板一覧</h2>
     <?php if(!empty($record['data']) && !empty($record['total_record'])){ ?>
        <p class="research_result"><?php echo $record['total_record']; ?>件のやりとりがあります&nbsp;|&nbsp;<?php echo sanitize($current_page); ?>ページ目&nbsp;|&nbsp;<?php echo $current_min_record + 1; ?>&nbsp;-&nbsp;<?php echo count($record['data']) + $current_min_record; ?>件</p>
      <?php }else{ ?>
        <p class="research_result">まだやりとりはありません。</p>
      <?php } ?>
      <div class="col_2_style">
        <section class="col_2_main_wrapper">
          <?php if(!empty($record['total_record'])){ ?>
          <article class="mypage_info_box">
            <table>
              <tbody>
                <tr class="table_heading">
                  <th>最終送信</th>
                  <th>お相手</th>
                  <th>ニックネーム</th>
                  <th>メッセージ</th>
                </tr>
              <?php foreach($record['data'] as $key => $val){ ?>
                <tr>
                  <td><a class="table_link" href="messenger.php?b_id=<?php echo $val['b_id']; ?>"><?php datetime2daytime($val['last_time']); ?></a></td>
                  <td class="board_img_cell"><a class="table_link" href="messenger.php?b_id=<?php echo $val['b_id']; ?>">
                    <div class="table_icon_img_wrapper">
                      <img src="<?php echo (!empty($val['member']['pic'])) ? sanitize($val['member']['pic']) : 'img/no_image_square.png'; ?>" alt="" class="member_icon_img">
                    </div>
                  </a></td>
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
          </article>
          <?php } ?>
          <?php pagenation('board.php',$current_page,$record['total_page'],5); ?>  
          <div class="btn_wrapper">
            <a href="mypage.php" class="btn btn_normal btn_gray">戻る</a>
          </div>
        </section>
        <aside class="sidebar">
          <h3 class="side_heading">検索メニュー</h3>
          <form action="" method="get" class="side_form_wrapper">
            <div class="form_row">
              <label>
                <p class="text_centered">並び順</p>
                <select name="order" class="form_select_side">
                  <option value="0" class="form_select_item" <?php keepSelectData('order',0,true,true); ?>>日時が新しい順</option>
                  <option value="1" class="form_select_item" <?php keepSelectData('order',1,true,true); ?>>日時が古い順</option>
                </select>
              </label>
            </div>
            <div class="btn_wrapper">
              <input type="submit" value="検索" class="btn btn_small btn_final">
            </div>
          </form>
        </aside>
      </div>
    </main>
<?php
  require('footer.php');
  debugFinish();
?>