<?php
  require('function.php');
  require('auth.php');
  $title = 'プロフィール編集';
  
  debugStart();

  $u_id = $_SESSION['u_id'];
  $age_list = getAge();
  $area_list = getArea();
  $dbInfo = getUser($u_id);

  //値が送信された時(確認・登録両方)
  if(!empty($_POST['pre_post']) || !empty($_POST['post'])){
    $name = $_POST['name'];
    $age_id = (isset($_POST['age_id'])) ? (int)$_POST['age_id'] : 0;
    $age_flag = (isset($_POST['age_flag'])) ? (int)$_POST['age_flag'] : 0;
    $area_id = (isset($_POST['area_id'])) ? (int)$_POST['area_id'] : 0;
    $area_flag = (isset($_POST['area_flag'])) ? (int)$_POST['area_flag'] : 0;
    $gender = (isset($_POST['gender'])) ? (int)$_POST['gender'] : 0;
    $gender_flag =(isset($_POST['gender_flag'])) ? (int)$_POST['gender_flag'] : 0;
    $email = $_POST['email'];
  }

  //確認するを押して送信した時
  if(!empty($_POST['pre_post'])){
    debug('POST送信を確認');
    debug('確認のための送信');
    debug('pre_post');
    $pic = (!empty($dbInfo['pic'])) ? $dbInfo['pic'] : NULL;
    $pic = (!empty($_FILES['pic']['name'])) ? uploadImg($_FILES['pic'],'pic') : $pic;

    //バリデーションチェック(DBと違っていたらチェックする)
    //ニックネーム
    if($name !== $dbInfo['name']){
      validMaxlen($name,'name',11,MAX02);
      validEnterOkZero($name,'name');
    }
    //メール
    if($email !== $dbInfo['email']){
      validMaxlen($email,'email');
      validTypeEmail($email,'email');
      validEnter($email,'email');
    }

    if(empty($err_msg)){
      if($email !== $dbInfo['email']){
        validDupEmail($email,'email');
      }

      if(empty($err_msg)){
        debug('バリデーション完了');

      }else{
        debug('invalid: Catch!(Dup)');
        unset($_POST['pre_post']);
      }
    }else{
      debug('invalid: Catch!');
      unset($_POST['pre_post']);
    }
  }

  //バリデーション完了後
  //入力やり直し
  if(!empty($_POST['undo'])){
    debug('POST送信を確認');
    debug('入力やり直し');
  }
  //入力確定
  if(!empty($_POST['post'])){
    debug('POST送信を確認');
    debug('入力確定');
    $pic = (!empty($_POST['pic'])) ? $_POST['pic'] : NULL;

    try{
      $dbh = dbConnect();
      $sql = 'UPDATE user SET email = :email, name = :name, age_id = :age_id, age_flag = :age_flag, area_id = :area_id, area_flag = :area_flag, gender = :gender, gender_flag = :gender_flag, pic = :pic WHERE u_id = :u_id AND delete_flag = 0';
      $data = array(
        ':email' => $email,
        ':name' => $name,
        ':age_id' => $age_id,
        ':age_flag' => $age_flag,
        ':area_id' => $area_id,
        ':area_flag' => $area_flag,
        ':gender' => $gender,
        ':gender_flag' => $gender_flag,
        ':pic' => $pic,
        ':u_id' => $u_id
      );

      $stmt = queryPost($dbh,$sql,$data);

      if($stmt){
        debug('プロフ更新完了');
        $_SESSION['success'] = SUC03;
        debug('マイページへ移動');
        header('Location:mypage.php');
        exit();
      }else{
        debug('クエリ失敗');
        appendErrMsg('common',ERR01);
      }

    }catch(Exception $e){
      debug('エラー発生: '.$e->getMessage());
      appendErrMsg('common',ERR01);
    }
  }

  require('head.php');
?>
  <body>
    <?php require('header.php'); ?>
    <main class="main site_width">
      <h2 class="heading">プロフィール編集</h2>
      <section class="form_width">
        <?php if(empty($_POST['pre_post'])){ ?>
        <div class="msg_area emphasis"><?php errPrint('common'); ?></div>
        <form action="" method="post" enctype="multipart/form-data" class="form_wrapper">
          <div class="msg_area emphasis"><?php errPrint('name'); ?></div>
          <div class="form_row">
            <label>
              <p class="text_centered">ニックネーム(表示名)&nbsp;<span class="emphasis">*必須&nbsp;11文字まで</span></p>
              <input type="text" name="name" placeholder="ニックネーム" class="<?php is_err('name'); ?>" value="<?php keepFormData('name'); ?>">
            </label>
          </div>
          <div class="form_row">
            <label>
              <p class="text_centered">年齢</p>
              <select name="age_id" class="form_select">
                <option value="0" class="form_select_item" <?php keepSelectData('age_id',0,true); ?>>回答しない</option>
                <?php
                  if(!empty($age_list)){
                    foreach($age_list as $key => $val){
                ?>
                <option value="<?php echo $val['age_id']; ?>" class="form_select_item" <?php keepSelectData('age_id',$val['age_id'],true); ?>>
                  <?php echo $val['age']; ?>
                </option>
                <?php
                    }
                  }
                ?>
              </select>
            </label>
            <div class="form_radio_wrapper">
              <label>
                <input type="radio" name="age_flag" class="form_input_radio" value="0" <?php keepSelectData('age_flag',0); ?>>公開しない
              </label>
              <label>
                <input type="radio" name="age_flag" class="form_input_radio" value="1" <?php keepSelectData('age_flag',1); ?>>公開する
              </label>
            </div>
          </div>
          <div class="form_row">
            <label>
              <p class="text_centered">お住まい</p>
              <select name="area_id" class="form_select">
                <option value="0" class="form_select_item" <?php keepSelectData('area_id',0,true); ?>>回答しない</option>
                <?php 
                  if(!empty($area_list)){
                    foreach($area_list as $key => $val){
                      if((int)$key === 0){
                        continue;
                      }
                ?>
                <option value="<?php echo $val['area_id']; ?>" class="form_select_item" <?php keepSelectData('area_id',$val['area_id'],true); ?>>
                  <?php echo $val['area']; ?>
                </option>
                <?php
                    }
                  }
                ?>
              </select>
            </label>
            <div class="form_radio_wrapper">
              <label>
                <input type="radio" name="area_flag" class="form_input_radio" value="0" <?php keepSelectData('area_flag',0); ?>>公開しない
              </label>
              <label>
                <input type="radio" name="area_flag" class="form_input_radio" value="1" <?php keepSelectData('area_flag',1); ?>>公開する
              </label>
            </div>
          </div>
          <div class="form_row">
            <p class="text_centered">ジェンダー</p>
            <div class="form_radio_wrapper form_input_radio_value_wrapper">
              <label class="form_input_radio">
                <input type="radio" name="gender" class="form_input_radio form_input_radio_value" value="0" <?php keepSelectData('gender',0); ?>>回答しない
              </label>
              <label class="form_input_radio">
                <input type="radio" name="gender" class="form_input_radio form_input_radio_value" value="1" <?php keepSelectData('gender',1); ?>>女性
              </label>
              <label class="form_input_radio">
                <input type="radio" name="gender" class="form_input_radio form_input_radio_value" value="2" <?php keepSelectData('gender',2); ?>>男性
              </label>
            </div>
            <div class="form_radio_wrapper">
              <label>
                <input type="radio" name="gender_flag" class="form_input_radio" value="0"  <?php keepSelectData('gender_flag',0); ?>>公開しない
              </label>
              <label>
                <input type="radio" name="gender_flag" class="form_input_radio" value="1" <?php keepSelectData('gender_flag',1); ?>>公開する
              </label>
            </div>
          </div>
          <div class="msg_area emphasis"><?php errPrint('email'); ?></div>
          <div class="form_row">
            <label>
              <p class="text_centered">メールアドレス</p>
              <input type="text" name="email" placeholder="メールアドレス" class="<?php is_err('email'); ?>" value="<?php keepFormData('email'); ?>">
            </label>
          </div>
          <div class="msg_area emphasis"><?php errPrint('pic'); ?></div>
          <p class="text_centered">アイコン</p>
          <p class="text_centered"><span class="emphasis">2.5MBまで・jpeg、gif、png、webpいずれかの画像</span></p>
          <div class="js_img_border member_img_area last_form_row" style="<?php if(!empty($dbInfo['pic'])) echo 'border: none;'; ?>">
            <label>
              <input type="hidden" name="MAX_FILE_SIZE" value="2621440">
              <input type="file" name="pic" class="js_img_input member_img_input">
              <img src="<?php if(!empty($dbInfo['pic'])) echo sanitize($dbInfo['pic']); ?>" alt="アイコン" class="js_preview member_img" style="<?php if(empty($dbInfo['pic'])) echo 'display: none;'; ?>">
              ドラッグ＆ドロップ
            </label>
          </div>
          <div class="btn_wrapper">
            <input type="submit" value="更新する" name="pre_post" class="btn btn_normal btn_active">
          </div>
          <div class="btn_wrapper">
            <a href="mypage.php" class="btn btn_normal btn_gray margin_top">戻る</a>
          </div>
        </form>
        
        <?php }else{ ?>
        <div class="introduction">
          <p>こちらの内容を送信します。</p>
          <p>よろしいでしょうか。</p>
        </div>
        <form action="" method="post" enctype="multipart/form-data" class="form_wrapper">
          <div class="check_field_wrapper">
            <div class="check_field_row">
              <p class="text_centered">ニックネーム</p>
              <input type="hidden" name="name" value="<?php keepFormData('name'); ?>">
              <p class="check_text"><?php keepFormData('name'); ?></p>
            </div>
            <div class="check_field_row">
              <p class="text_centered">年齢</p>
              <input type="hidden" name="age_id" value="<?php keepFormData('age_id'); ?>">
              <input type="hidden" name="age_flag" value="<?php keepFormData('age_flag'); ?>">
              <p class="check_text"><?php echo ($age_id === 0) ? '回答しない' : $age_list[($age_id - 1)]['age']; ?></p>
              <p class="check_text_flag"><?php echo ($age_flag === 0) ? '公開しない' : '公開する'; ?></p>
            </div>
            <div class="check_field_row">
              <p class="text_centered">お住まい</p>
              <input type="hidden" name="area_id" value="<?php keepFormData('area_id'); ?>">
              <input type="hidden" name="area_flag" value="<?php keepFormData('area_flag'); ?>">
              <p class="check_text"><?php echo ($area_id === 0) ? '回答しない' : $area_list[($area_id - 1)]['area']; ?></p>
              <p class="check_text_flag"><?php echo ($area_flag === 0) ? '公開しない' : '公開する'; ?></p>
            </div>
            <div class="check_field_row">
              <p class="text_centered">ジェンダー</p>
              <input type="hidden" name="gender" value="<?php keepFormData('gender'); ?>">
              <input type="hidden" name="gender_flag" value="<?php keepFormData('gender_flag'); ?>">
              <p class="check_text">
              <?php 
                switch($gender){
                  case 0:
                    echo '回答しない';
                    break;
                  case 1:
                    echo '女性';
                    break;
                  case 2:
                    echo '男性';
                    break;
                }
              ?>
              </p>
              <p class="check_text_flag"><?php echo ($gender_flag === 0) ? '公開しない' : '公開する'; ?></p>
            </div>
            <div class="check_field_row <?php if(empty($pic)) echo 'last_form_row'; ?>">
              <p class="text_centered">メールアドレス</p>
              <input type="hidden" name="email" value="<?php keepFormData('email'); ?>">
              <p class="check_text"><?php keepFormData('email'); ?></p>
            </div>
            <?php if(!empty($pic)){ ?>
            <div class="check_field_row last_form_row">
              <p class="text_centered">アイコン</p>
              <input type="hidden" name="pic" value="<?php echo sanitize($pic); ?>">
              <div class="member_check_img_wrapper">
                <img src="<?php echo sanitize($pic); ?>" alt="アイコン" class="member_check_img">
              </div>
            </div>
            <?php } ?>
          </div>
          <div class="btn_wrapper">
            <input type="submit" value="送信する" name="post" class="btn btn_normal btn_final">
          </div>
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