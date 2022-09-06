<?php
  require('function.php');
  require('auth.php');

  $u_id = $_SESSION['u_id'];

  $edit_flag = (!empty($_GET['e_id'])) ? true : false;
  if($edit_flag){
    $e_id = $_GET['e_id'];

    $dbInfo = getMyEditEvent($e_id,$u_id);

    if(empty($dbInfo)){
      debug('GETパラメータが不正な値');
      debug('マイページに移動');
      header('Location:mypage.php');
      exit();
    }elseif(time() > strtotime($dbInfo['start'])){
      debug('更新期限切れ');
      debug('主催イベント一覧ページに移動');
      header('Location:organize.php');
      exit();
    }

  }

  $title = ($edit_flag) ? 'イベント編集' : 'イベント登録';
  debugStart();

  $age_list = getAge();
  $area_list = getArea();
  $category_list = getCategory();

  //値が送信された時(確認・登録両方)
  if(!empty($_POST['pre_post']) || !empty($_POST['pre_delete']) || !empty($_POST['post'])){
    $e_name = $_POST['e_name'];
    $c_id = (!empty($_POST['c_id'])) ? (int)$_POST['c_id'] : 0;
    $comment = $_POST['comment'];
    $start = (!empty($_POST['start'])) ? local2Datetime($_POST['start']) : NULL;
    $finish = (!empty($_POST['finish'])) ? local2Datetime($_POST['finish']) : NULL;
    $area_id = (!empty($_POST['area_id'])) ? (int)$_POST['area_id'] : 0;
    $capa = $_POST['capa'];
    $capa_flag = (isset($_POST['capa_flag'])) ? (int)$_POST['capa_flag'] : 0;
    $fee = (!empty($_POST['fee'])) ? $_POST['fee'] : 0;
    $place = $_POST['place'];
    $place_flag = (isset($_POST['place_flag'])) ? (int)$_POST['place_flag'] : 0;
    $gender = (isset($_POST['gender'])) ? (int)$_POST['gender'] : 0;
    $age_min = (isset($_POST['age_min'])) ? (int)$_POST['age_min'] : 0;
    $age_max = (isset($_POST['age_max'])) ? (int)$_POST['age_max'] : 0;
  }

  //確認
  if(!empty($_POST['pre_post'])){
    debug('POST送信を確認');
    debug('確認のための送信');
    debug('pre_post');

    $pic1 = (!empty($dbInfo['pic1'])) ? $dbInfo['pic1'] : NULL;
    $pic1 = (!empty($_FILES['pic1']['name'])) ? uploadImg($_FILES['pic1'],'pic1') : $pic1;
    $pic2 = (!empty($dbInfo['pic2'])) ? $dbInfo['pic2'] : NULL;
    $pic2 = (!empty($_FILES['pic2']['name'])) ? uploadImg($_FILES['pic2'],'pic2') : $pic2;
    $pic3 = (!empty($dbInfo['pic3'])) ? $dbInfo['pic3'] : NULL;
    $pic3 = (!empty($_FILES['pic3']['name'])) ? uploadImg($_FILES['pic3'],'pic3') : $pic3;

    //画像が送信されたとき
    if(empty($pic1)){
      //$pic1が空の場合
      if(!empty($pic2)){
        //$pic2が空でない時、上に詰める
        $pic1 = $pic2;
        if(!empty($pic3)){
          //$pic3も空でない時、上に詰める
          $pic2 = $pic3;
          $pic3 = NULL;
        }else{
          //$pic1,$pic3が空で、$pic2が空でない
          $pic2 = NULL;
        }
      }elseif(!empty($pic3)){
        //$pic1,$pic2が空で かつ $pic3が空でない時、上に詰める
        $pic1 = $pic3;
        $pic3 = NULL;
      }
    }elseif(empty($pic2) && !empty($pic3)){
      $pic2 = $pic3;
      $pic3 = NULL;
    }

    //バリデーションチェック
    //イベント名
    if(!$edit_flag || ($edit_flag && $e_name !== $dbInfo['e_name'])){
      validMaxlen($e_name,'e_name',20,MAX03);
      validEnter($e_name,'e_name');
    }
    //カテゴリー
    if(!$edit_flag || ($edit_flag && $c_id !== $dbInfo['c_id'])){
      validEnter($c_id,'c_id',ABS02);
    }
    //イベント内容
    if(!$edit_flag || ($edit_flag && $comment !== $dbInfo['comment'])){
      validMaxlen($comment,'comment',500,MAX04);
      validEnter($comment,'comment');
    }
    //start
    if(!$edit_flag || ($edit_flag && $start !== $dbInfo['start'])){
      validDatetime($start,'start');
      validEnter($start,'start');
    }
    //finish
    if(!$edit_flag || ($edit_flag && $finish !== $dbInfo['finish'])){
      validDatetime($finish,'finish');
      validTimeLength($start,$finish,'finish');
      validTimeFlow($start,$finish,'finish');
      validEnter($finish,'finish');
    }
    //開催地域
    if(!$edit_flag || ($edit_flag && $area_id !== $dbInfo['area_id'])){
      validEnter($area_id,'area_id',ABS03);
    }
    //定員
    if(!$edit_flag || ($edit_flag && $capa !== $dbInfo['capa'])){
      validIntRange($capa,'capa',1);
      validMaxlen($capa,'capa',5,TYP06);
      validByteNum($capa,'capa');
      validEnter($capa,'capa');
    }
    //参加費
    if((!$edit_flag && !empty($fee)) || ($edit_flag && $fee !== $dbInfo['fee'])){
      validIntRange($fee,'fee');
      validMaxlen($fee,'fee',5,TYP06);
      validByteNum($fee,'fee');
    }
    //集合場所
    if(!$edit_flag || ($edit_flag && $place !== $dbInfo['place'])){
      validMaxlen($place,'place');
      validEnter($place,'place');
    }
    //年齢制限
    if(!$edit_flag || ($edit_flag && $age_min !== $dbInfo['age_min']) || ($edit_flag && $age_max !== $dbInfo['age_max'])){
      validAgeLimit($age_min,$age_max,'age_max');
    }

    if(empty($err_msg)){
      debug('バリデーションチェックOK');

    }else{
      debug('invalid: Catch!');
      unset($_POST['pre_post']);
    }
  }

  //削除(確認)
  if(!empty($_POST['pre_delete'])){
    debug('POST送信を確認');
    debug('確認のための送信');
    debug('pre_delete');
  }

  //削除(確定)
  if(!empty($_POST['delete'])){
    debug('POST送信を確認');
    debug('削除確定');

    try{
      $dbh = dbConnect();
      $sql = 'UPDATE event SET delete_flag = 1 WHERE e_id = :e_id';
      $data = array(':e_id' => $e_id);

      $stmt = queryPost($dbh,$sql,$data);
      if($stmt){
        debug('イベント削除完了');
        $_SESSION['success'] = SUC09;
        debug('主催イベントページに移動');
        header('Location:organize.php');
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

  //入力やり直し
  if(!empty($_POST['undo'])){
    debug('POST送信を確認');
    debug('入力やり直し');
  }

  //入力確定
  if(!empty($_POST['post'])){
    debug('POST送信を確認');
    debug('入力確定');

    $pic1 = (!empty($_POST['pic1'])) ? $_POST['pic1'] : NULL;
    $pic2 = (!empty($_POST['pic2'])) ? $_POST['pic2'] : NULL;
    $pic3 = (!empty($_POST['pic3'])) ? $_POST['pic3'] : NULL;

    if($edit_flag){
      //イベント編集
      try{
        $dbh = dbConnect();
        $sql = 'UPDATE event SET
                e_name = :e_name,
                c_id = :c_id,
                comment = :comment,
                start = :start,
                finish = :finish,
                area_id = :area_id,
                capa = :capa,
                capa_flag = :capa_flag,
                fee = :fee,
                place = :place,
                place_flag = :place_flag,
                gender = :gender,
                age_min = :age_min,
                age_max = :age_max,
                pic1 = :pic1,
                pic2 = :pic2,
                pic3 = :pic3
                WHERE e_id = :e_id AND o_id = :o_id AND delete_flag = 0';
        $data = array(
          ':e_name' => $e_name,
          ':c_id' => $c_id,
          ':comment' => $comment,
          ':start' => $start,
          ':finish' => $finish,
          ':area_id' => $area_id,
          ':capa' => $capa,
          ':capa_flag' => $capa_flag,
          ':fee' => $fee,
          ':place' => $place,
          ':place_flag' => $place_flag,
          ':gender' => $gender,
          ':age_min' => $age_min,
          ':age_max' => $age_max,
          ':pic1' => $pic1,
          ':pic2' => $pic2,
          ':pic3' => $pic3,
          ':e_id' => $e_id,
          ':o_id' => $u_id
        );

        $stmt = queryPost($dbh,$sql,$data);
        if($stmt){
          debug('イベント更新完了');
          $_SESSION['success'] = SUC10;
          debug('主催イベントページに移動');
          header('Location:organize.php');
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
      //イベント登録
      try{
        $dbh = dbConnect();
        $sql = 'INSERT INTO event (o_id,e_name,c_id,comment,start,finish,area_id,capa,capa_flag,fee,place,place_flag,gender,age_min,age_max,pic1,pic2,pic3,create_date)
                VALUES (:o_id,:e_name,:c_id,:comment,:start,:finish,:area_id,:capa,:capa_flag,:fee,:place,:place_flag,:gender,:age_min,:age_max,:pic1,:pic2,:pic3,:create_date)';
        $data = array(
          ':o_id' => $u_id,
          ':e_name' => $e_name,
          ':c_id' => $c_id,
          ':comment' => $comment,
          ':start' => $start,
          ':finish' => $finish,
          ':area_id' => $area_id,
          ':capa' => $capa,
          ':capa_flag' => $capa_flag,
          ':fee' => $fee,
          ':place' => $place,
          ':place_flag' => $place_flag,
          ':gender' => $gender,
          ':age_min' => $age_min,
          ':age_max' => $age_max,
          ':pic1' => $pic1,
          ':pic2' => $pic2,
          ':pic3' => $pic3,
          ':create_date' => date('Y-m-d H:i:s')
        );

        $stmt = queryPost($dbh,$sql,$data);
        if($stmt){
          debug('イベント登録完了');
          $_SESSION['success'] = SUC08;
          debug('マイページに移動');
          header('Location:mypage.php');
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
  }

  require('head.php');
?>
  <body>
    <?php require('header.php'); ?>
    <main class="main site_width">
      <h2 class="heading"><?php echo ($edit_flag) ? 'イベント編集' : 'イベント登録'; ?></h2>
      <section class="form_width">
      <?php if(empty($_POST['pre_post']) && empty($_POST['pre_delete'])){ ?>
        <div class="msg_area emphasis"><?php errPrint('common'); ?></div>
        <form action="" method="post" enctype="multipart/form-data" class="form_wrapper">
          <div class="form_row">
            <label>
              <p class="text_centered">イベント名&nbsp;<span class="emphasis">*</span>必須&nbsp;20文字まで</p>
              <div class="msg_area emphasis"><?php errPrint('e_name'); ?></div>
              <input type="text" name="e_name" placeholder="イベント名" class="js_text_area <?php is_err('e_name'); ?>" value="<?php keepFormData('e_name'); ?>">
              <p class="js_text_count"><span class="js_text_num">0</span>/<span class="js_text_num_limit">20</span>文字</p>
            </label>
          </div>
          <div class="form_row">
            <label>
              <p class="text_centered">カテゴリー&nbsp;<span class="emphasis">*</span>必須</p>
              <div class="msg_area emphasis"><?php errPrint('c_id'); ?></div>
              <select name="c_id" class="form_select <?php is_err('c_id'); ?>">
                <option value="0" class="form_select_item" <?php keepSelectData('c_id',0,true); ?>>選択してください</option>
                <?php
                  if(!empty($category_list)){
                    foreach($category_list as $key => $val){
                ?>
                <option value="<?php echo $val['c_id']; ?>" class="form_select_item" <?php keepSelectData('c_id',$val['c_id'],true); ?>><?php echo $val['category']; ?></option>
                <?php
                    }
                  }
                ?>
              </select>
            </label>
          </div>
          <div class="form_row">
            <label>
              <p class="text_centered">イベントの内容&nbsp;<span class="emphasis">*</span>必須&nbsp;500文字まで</p>
              <div class="msg_area emphasis"><?php errPrint('comment'); ?></div>
              <textarea name="comment" class="form_textarea js_text_area <?php is_err('comment'); ?>"><?php keepFormData('comment'); ?></textarea>
              <p class="js_text_count"><span class="js_text_num">0</span>/<span class="js_text_num_limit">500</span>文字</p>
            </label>
          </div>
          <div class="form_row">
            <label>
              <p class="text_centered">開始日時&nbsp;<span class="emphasis">*</span>必須</p>
              <div class="msg_area emphasis"><?php errPrint('start'); ?></div>
              <input type="datetime-local" name="start" class="<?php is_err('start'); ?>" min="<?php echo datetime2Local(date('Y-m-d H:i')); ?>" value="<?php keepTimeData('start'); ?>">
            </label>
          </div>
          <div class="form_row">
            <label>
              <p class="text_centered">終了日時&nbsp;<span class="emphasis">*</span>必須</p>
              <div class="msg_area emphasis"><?php errPrint('finish'); ?></div>
              <input type="datetime-local" name="finish" class="<?php is_err('finish'); ?>" min="<?php echo datetime2Local(date('Y-m-d H:i')); ?>" value="<?php keepTimeData('finish'); ?>">
            </label>
          </div>
          <div class="form_row">
            <label>
              <p class="text_centered">開催地域&nbsp;<span class="emphasis">*</span>必須</p>
              <div class="msg_area emphasis"><?php errPrint('area_id'); ?></div>
              <select name="area_id" class="form_select <?php is_err('area_id'); ?>">
                <option value="0" class="form_select_item" <?php keepSelectData('area_id',0,true); ?>>選択してください</option>
                <?php
                  if(!empty($area_list)){
                    foreach($area_list as $key => $val){
                ?>
                <option value="<?php echo $val['area_id']; ?>" class="form_select_item" <?php keepSelectData('area_id',$val['area_id'],true); ?>><?php echo $val['area']; ?></option>
                <?php
                    }
                  }
                ?>
              </select>
            </label>
          </div>
          <div class="form_row">
            <label>
              <p class="text_centered">定員・参加目安&nbsp;<span class="emphasis">*</span>必須&nbsp;半角数字</p>
              <div class="msg_area emphasis"><?php errPrint('capa'); ?></div>
              <input type="text" name="capa" placeholder="人数" class="<?php is_err('capa'); ?>" value="<?php keepFormData('capa'); ?>">
            </label>
            <div class="form_radio_wrapper">
              <label>
                <input type="radio" name="capa_flag" class="form_input_radio" value="0" <?php keepSelectData('capa_flag',0); ?>>定員内で募集する
              </label>
              <label>
                <input type="radio" name="capa_flag" class="form_input_radio" value="1" <?php keepSelectData('capa_flag',1); ?>>定員を超えて募集する
              </label>
            </div>
          </div>
          <div class="form_row">
            <label>
              <p class="text_centered">参加費(円)&nbsp;*任意&nbsp;半角数字</p>
              <div class="msg_area emphasis"><?php errPrint('fee'); ?></div>
              <input type="text" name="fee" placeholder="参加費" class="<?php is_err('fee'); ?>" value="<?php keepFormData('fee'); ?>">
            </label>
          </div>
          <div class="form_row">
            <label>
              <p class="text_centered">集合場所&nbsp;<span class="emphasis">*</span>必須</p>
              <div class="msg_area emphasis"><?php errPrint('place'); ?></div>
              <input type="text" name="place" placeholder="集合場所" class="<?php is_err('place'); ?>" value="<?php keepFormData('place'); ?>">
            </label>
            <div class="form_radio_wrapper">
              <label>
                <input type="radio" name="place_flag" class="form_input_radio" value="0"  <?php keepSelectData('place_flag',0); ?>>全員に公開する
              </label>
              <label>
                <input type="radio" name="place_flag" class="form_input_radio" value="1" <?php keepSelectData('place_flag',1); ?>>参加者以外に公開しない
              </label>
            </div>
          </div>
          <div class="form_row">
            <p class="text_centered">対象</p>
            <div class="form_radio_wrapper form_input_radio_value_wrapper">
              <label class="form_input_radio">
                <input type="radio" name="gender" class="form_input_radio form_input_radio_value" value="0" <?php keepSelectData('gender',0); ?>>誰でも
              </label>
              <label class="form_input_radio">
                <input type="radio" name="gender" class="form_input_radio form_input_radio_value" value="1" <?php keepSelectData('gender',1); ?>>女性限定
              </label>
              <label class="form_input_radio">
                <input type="radio" name="gender" class="form_input_radio form_input_radio_value" value="2" <?php keepSelectData('gender',2); ?>>男性限定
              </label>
            </div>
          </div>
          <div class="form_row">
            <p class="text_centered">年齢制限</p>
            <div class="msg_area emphasis"><?php errPrint('age_max'); ?></div>
            <div class="form_select_wrapper">
              <select name="age_min" class="form_select_half form_select_half_left <?php is_err('age_max'); ?>">
                <option value="0" class="form_select_item" <?php keepSelectData('age_min',0,true); ?>>下限なし</option>
                <?php
                  if(!empty($age_list)){
                    foreach($age_list as $key => $val){
                ?>
                <option value="<?php echo $val['age_id']; ?>" class="form_select_item" <?php keepSelectData('age_min',$val['age_id'],true); ?>><?php echo $val['age']; ?></option>
                <?php
                    }
                  }
                ?>
              </select>
              〜
              <select name="age_max" class="form_select_half form_select_half_right <?php is_err('age_max'); ?>">
                <option value="0" class="form_select_item" <?php keepSelectData('age_max',0,true); ?>>上限なし</option>
                <?php
                  if(!empty($age_list)){
                    foreach($age_list as $key => $val){
                ?>
                <option value="<?php echo $val['age_id']; ?>" class="form_select_item" <?php keepSelectData('age_max',$val['age_id'],true); ?>><?php echo $val['age']; ?></option>
                <?php
                    }
                  }
                ?>
              </select>
            </div>
          </div>
          <p class="text_centered">イメージ写真&nbsp;*任意</p>
          <p class="text_centered"><span class="emphasis">各2.5MBまで・jpeg、gif、png、webpいずれかの画像</span></p>
          <div class="msg_area emphasis"><?php errPrint('pic1'); ?></div>
          <div class="js_img_border event_img_area" style="<?php if(!empty($dbInfo['pic1'])) echo 'border: none;'; ?>">
            <label>
              <input type="hidden" name="MAX_FILE_SIZE" value="2621440">
              <input type="file" name="pic1" class="js_img_input event_img_input">
              <img src="<?php if(!empty($dbInfo['pic1'])) echo sanitize($dbInfo['pic1']); ?>" alt="イメージ写真" class="js_preview event_img" style="<?php if(empty($dbInfo['pic1'])) echo 'display: none;'; ?>">
              ドラッグ＆ドロップ
            </label>
          </div>
          <div class="msg_area emphasis"><?php errPrint('pic2'); ?></div>
          <div class="js_img_border event_img_area" style="<?php if(!empty($dbInfo['pic2'])) echo 'border: none;'; ?>">
            <label>
              <input type="hidden" name="MAX_FILE_SIZE" value="2621440">
              <input type="file" name="pic2" class="js_img_input event_img_input">
              <img src="<?php if(!empty($dbInfo['pic2'])) echo sanitize($dbInfo['pic2']); ?>" alt="イメージ写真" class="js_preview event_img" style="<?php if(empty($dbInfo['pic2'])) echo 'display: none;'; ?>">
              ドラッグ＆ドロップ
            </label>
          </div>
          <div class="msg_area emphasis"><?php errPrint('pic3'); ?></div>
          <div class="js_img_border event_img_area last_form_row"  style="<?php if(!empty($dbInfo['pic3'])) echo 'border: none;'; ?>">
            <label>
              <input type="hidden" name="MAX_FILE_SIZE" value="2621440">
              <input type="file" name="pic3" class="js_img_input event_img_input">
              <img src="<?php if(!empty($dbInfo['pic3'])) echo sanitize($dbInfo['pic3']); ?>" alt="イメージ写真" class="js_preview event_img" style="<?php if(empty($dbInfo['pic3'])) echo 'display: none;'; ?>">
              ドラッグ＆ドロップ
            </label>
          </div>
          <?php if(empty($edit_flag) || (!empty($edit_flag) && time() < strtotime($dbInfo['start']))){ ?>
          <div class="btn_wrapper">
            <input type="submit" value="<?php echo ($edit_flag) ? '更新する' : '登録する'; ?>" name="pre_post" class="btn btn_normal btn_active">
          </div>
            <?php if($edit_flag){ ?>
          <div class="btn_wrapper">
            <input type="submit" value="削除する" name="pre_delete" class="btn btn_normal btn_active margin_top">
          </div> 
          <?php
              }
            }
          ?>
          <div class="btn_wrapper">
            <a href="mypage.php" class="btn btn_normal btn_gray margin_top">戻る</a>
          </div>
        </form>
      <?php }else{ ?>
        <?php if(isset($_POST['pre_post'])){ ?>
        <div class="introduction">
          <p>こちらの内容を送信します。</p>
          <p>よろしいでしょうか。</p>
        </div>
        <?php }else{ ?>
        <div class="introduction">
          <p>イベント削除します。</p>
          <p>よろしいでしょうか。</p>
        </div>
        <?php } ?>
        <form action="" method="post" enctype="multipart/form-data" class="form_wrapper">
          <div class="check_field_wrapper">
            <div class="check_field_row">
              <p class="text_centered">イベント名</p>
              <input type="hidden" name="e_name" value="<?php keepFormData('e_name'); ?>">
              <p class="check_text"><?php keepFormData('e_name'); ?></p>
            </div>
            <div class="check_field_row">
              <p class="text_centered">カテゴリー</p>
              <input type="hidden" name="c_id" value="<?php keepFormData('c_id'); ?>">
              <p class="check_text"><?php echo ((int)$c_id === 99) ? 'その他': $category_list[($c_id - 1)]['category']; ?></p>
            </div>
            <div class="check_field_row">
              <p class="text_centered">イベントの内容</p>
              <input type="hidden" name="comment" value="<?php keepFormData('comment'); ?>">
              <p class="check_textarea"><?php echo nl2br(sanitize($comment)); ?></p>
            </div>
            <div class="check_field_row">
              <p class="text_centered">日時</p>
              <input type="hidden" name="start" value="<?php keepFormData('start'); ?>">
              <input type="hidden" name="finish" value="<?php keepFormData('finish'); ?>">
              <p class="check_text"><?php local2Calendar($_POST['start']); ?>&nbsp;〜&nbsp;<?php local2Calendar($_POST['finish']); ?></p>
            </div>
            <div class="check_field_row">
              <p class="text_centered">開催地域</p>
              <input type="hidden" name="area_id" value="<?php keepFormData('area_id'); ?>">
              <p class="check_text"><?php echo $area_list[($area_id -1)]['area']; ?></p>
            </div>
            <div class="check_field_row">
              <p class="text_centered">定員・参加目安</p>
              <input type="hidden" name="capa" value="<?php keepFormData('capa'); ?>">
              <input type="hidden" name="capa_flag" value="<?php keepFormData('capa_flag'); ?>">
              <p class="check_text"><?php keepFormData('capa'); ?>人</p>
              <p class="check_text_flag"><?php echo ($capa_flag === 0) ? '定員内で募集する' : '定員を超えて募集する'; ?></p>
            </div>
            <div class="check_field_row">
              <input type="hidden" name="fee" value="<?php keepFormData('fee'); ?>">
              <p class="text_centered">参加費</p>
              <p class="check_text"><?php echo (!empty($fee)) ? sanitize($fee) : '無料'; ?></p>
            </div>
            <div class="check_field_row">
              <p class="text_centered">集合場所</p>
              <input type="hidden" name="place" value="<?php keepFormData('place'); ?>">
              <input type="hidden" name="place_flag" value="<?php keepFormData('place_flag'); ?>">
              <p class="check_text"><?php keepFormData('place'); ?></p>
              <p class="check_text_flag"><?php echo ($place_flag === 0) ? '全員に公開する' : '参加者以外に公開しない'; ?></p>
            </div>
            <div class="check_field_row">
              <input type="hidden" name="gender" value="<?php keepFormData('gender'); ?>">
              <p class="text_centered">対象</p>
              <p class="check_text">
                <?php
                  switch($gender){
                    case 0:
                      echo '誰でも';
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
            <div class="check_field_row <?php if(empty($pic1)) echo 'last_form_row'; ?>">
              <p class="text_centered">年齢制限</p>
              <input type="hidden" name="age_min" value="<?php keepFormData('age_min'); ?>">
              <input type="hidden" name="age_max" value="<?php keepFormData('age_max'); ?>">
              <p class="check_text"><?php echo ($age_min === 0) ? '下限なし' : $age_list[($age_min - 1)]['age']; ?>&nbsp;〜&nbsp;<?php echo ($age_max === 0) ? '上限なし' : $age_list[($age_max - 1)]['age']; ?></p>
            </div>
            <?php if(!empty($pic1)){ ?>
            <div class="check_field_row">
              <p class="text_centered">イメージ写真</p>
              <div class="event_check_img_wrapper <?php if(empty($pic2)) echo 'last_form_row'; ?>">
                <input type="hidden" name="pic1" value="<?php echo sanitize($pic1); ?>">
                <img src="<?php if(!empty($pic1)) echo sanitize($pic1); ?>" alt="イメージ写真" class="event_check_img">
              </div>
              <?php if(!empty($pic2)){ ?>
              <div class="event_check_img_wrapper <?php if(empty($pic3)) echo 'last_form_row'; ?>">
                <input type="hidden" name="pic2" value="<?php echo sanitize($pic2); ?>">
                <img src="<?php if(!empty($pic2)) echo sanitize($pic2); ?>" alt="イメージ写真" class="event_check_img">
              </div>
              <?php } ?>
              <?php if(!empty($pic3)){ ?>
              <div class="event_check_img_wrapper last_form_row">
                <input type="hidden" name="pic3" value="<?php echo sanitize($pic3); ?>">
                <img src="<?php if(!empty($pic3)) echo sanitize($pic3); ?>" alt="イメージ写真" class="event_check_img">
              </div>
              <?php } ?>
            </div>
            <?php } ?>
          </div>
          <?php if(!empty($_POST['pre_post'])){ ?>
          <div class="btn_wrapper">
            <input type="submit" value="登録する" name="post" class="btn btn_normal btn_final">
          </div>
          <?php }elseif($edit_flag){ ?>
          <div class="btn_wrapper">
            <input type="submit" value="削除する" name="delete" class="btn btn_normal btn_final">
          </div>
          <?php } ?>
          <div class="btn_wrapper">
            <input type="submit" value="戻る" name="undo" class="btn btn_normal btn_gray margin_top">
          </div>
        </form>
      <?php } ?>
      </section>
    </main>
<?php
  require('footer.php');
  debugFinish();
?>