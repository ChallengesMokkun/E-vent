<?php
  require('function.php');
  is_login();

  $title = 'ユーザー登録';
  debugStart();

  $age_list = getAge();
  $area_list = getArea();

  debug('POST: '.print_r($_POST,true));

  //値が送信された時(確認・登録両方)
  if(isset($_POST['pre_post']) || isset($_POST['post'])){
    $email = $_POST['email'];
    $pass = $_POST['pass'];
    $pass_re = $_POST['pass_re'];
    $name = (isset($_POST['name'])) ? $_POST['name'] : NULL;
    $age_id = (isset($_POST['age_id'])) ? (int)$_POST['age_id'] : 0;
    $age_flag = (isset($_POST['age_flag'])) ? (int)$_POST['age_flag'] : 0;
    $area_id = (isset($_POST['area_id'])) ? (int)$_POST['area_id'] : 0;
    $area_flag = (isset($_POST['area_flag'])) ? (int)$_POST['area_flag'] : 0;
    $gender = (isset($_POST['gender'])) ? (int)$_POST['gender'] : 0;
    $gender_flag = (isset($_POST['gender_flag'])) ? (int)$_POST['gender_flag'] : 0;
  }

  //確認するを押して送信した時
  if(!empty($_POST['pre_post'])){
    debug('POST送信を確認');
    debug('確認のための送信');
    debug('pre_post');

    $pic = (!empty($_FILES['pic'])) ? uploadImg($_FILES['pic'],'pic') : NULL;

    //パスワード
    validMaxlen($pass,'pass');
    validMinlen($pass,'pass');
    validTypePass($pass,'pass');
    validRetype($pass,$pass_re,'pass_re');
    validEnter($pass,'pass');
    validEnter($pass_re,'pass_re');
    //ニックネーム
    validMaxlen($name,'name',11,MAX02);
    validEnterOkZero($name,'name');
    //メール
    validMaxlen($email,'email');
    validTypeEmail($email,'email');
    validEnter($email,'email');

    if(empty($err_msg)){
      //メール
      validDupEmail($email,'email');

      if(empty($err_msg)){
        debug('バリデーションチェック完了');

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
  //入力内容を登録する
  if(!empty($_POST['post'])){
    debug('POST送信を確認');
    debug('入力確定');
    $pic = (!empty($_POST['pic'])) ? $_POST['pic'] : NULL;
    
    try{
      $dbh = dbConnect();
      $sql = 'INSERT INTO user (email,pass,name,age_id,age_flag,area_id,area_flag,gender,gender_flag,pic,login_time,create_date) VALUES (:email,:pass,:name,:age_id,:age_flag,:area_id,:area_flag,:gender,:gender_flag,:pic,:login_time,:create_date)';
      $data = array(
        ':email' => $email,
        ':pass' => password_hash($pass,PASSWORD_DEFAULT),
        ':name' => $name,
        ':age_id' => $age_id,
        ':age_flag' => $age_flag,
        ':area_id' => $area_id,
        ':area_flag' => $area_flag,
        ':gender' => $gender,
        ':gender_flag' => $gender_flag,
        ':pic' => $pic,
        ':login_time' => date('Y-m-d H:i:s'),
        ':create_date' => date('Y-m-d H:i:s')
      );

      $stmt = queryPost($dbh,$sql,$data);
      
      if($stmt){
        //ログイン処理
        //ログイン有効期限
        $sesLimit = 60*60;

        $_SESSION['u_id'] = $dbh->lastInsertId();
        $_SESSION['login_date'] = time();
        $_SESSION['login_limit'] = $sesLimit;

        $_SESSION['success'] = SUC01;

        header('Location:mypage.php');
        exit();

      }
    }catch (Exception $e){
      debug('エラー発生: '.$e->getMessage());
      appendErrMsg('common',ERR01);
    }
  }

  require('head.php');
?>
  <body>
    <?php require('header.php'); ?>
    
    <main class="main site_width">
      <h2 class="heading">ユーザー登録</h2>
      <section class="form_width">
        <?php if(empty($_POST['pre_post'])){ ?>
        <div class="msg_area emphasis"><?php errPrint('common'); ?></div>
        <form action="" method="post" enctype="multipart/form-data" class="form_wrapper">
          <div class="form_row">
            <label>
              <p class="text_centered">メールアドレス&nbsp;<span class="emphasis">*</span>必須</p>
              <div class="msg_area emphasis"><?php errPrint('email'); ?></div>
              <input type="text" name="email" placeholder="メールアドレス" class="<?php is_err('email'); ?>" value="<?php keepFormData('email'); ?>">
            </label>
          </div>
          <div class="form_row">
            <label>
              <p class="text_centered">パスワード&nbsp;<span class="emphasis">*</span>必須&nbsp;8文字以上</p>
              <p class="text_centered">半角英数字と!?-_;:!&#%=<>\*?+$|^.()[]が使えます</p>
              <div class="msg_area emphasis"><?php errPrint('pass'); ?></div>
              <input type="password" name="pass" placeholder="パスワード" class="<?php is_err('pass'); ?>" value="<?php keepFormData('pass'); ?>">
            </label>
          </div>
          <div class="form_row">
            <label>
              <p class="text_centered">パスワード再入力&nbsp;<span class="emphasis">*</span>必須</p>
              <div class="msg_area emphasis"><?php errPrint('pass_re'); ?></div>
              <input type="password" name="pass_re" placeholder="パスワード" class="<?php is_err('pass_re'); ?>" value="<?php keepFormData('pass_re'); ?>">
            </label>
          </div>
          <div class="form_row">
            <label>
              <p class="text_centered">ニックネーム(表示名)&nbsp;<span class="emphasis">*</span>必須&nbsp;11文字まで</p>
              <div class="msg_area emphasis"><?php errPrint('name'); ?></div>
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
                <input type="radio" name="age_flag" class=" form_input_radio" value="0" <?php keepSelectData('age_flag',0); ?>>公開しない
              </label>
              <label>
                <input type="radio" name="age_flag" class=" form_input_radio" value="1" <?php keepSelectData('age_flag',1); ?>>公開する
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
                <input type="radio" name="area_flag" class=" form_input_radio" value="0" <?php keepSelectData('area_flag',0); ?>>公開しない
              </label>
              <label>
                <input type="radio" name="area_flag" class=" form_input_radio" value="1" <?php keepSelectData('area_flag',1); ?>>公開する
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
                <input type="radio" name="gender_flag" class="form_input_radio" value="0" <?php keepSelectData('gender_flag',0); ?>>公開しない
              </label>
              <label>
                <input type="radio" name="gender_flag" class="form_input_radio" value="1" <?php keepSelectData('gender_flag',1); ?>>公開する
              </label>
            </div>
          </div>
          <p class="text_centered">アイコン&nbsp;*任意</p>
          <p class="text_centered">2.5MBまで・jpeg、gif、png、webpいずれかの画像</p>
          <div class="msg_area emphasis"><?php errPrint('pic'); ?></div>
          <div class="js_img_border member_img_area last_form_row <?php is_err('pic'); ?>">
            <label>
              <input type="hidden" name="MAX_FILE_SIZE" value="2621440">
              <input type="file" name="pic" class="js_img_input member_img_input">
              <img src="" alt="アイコン" class="js_preview member_img" style="display: none;">
              ドラッグ＆ドロップ
            </label>
          </div>
          <div class="btn_wrapper">
            <input type="submit" value="登録する" name="pre_post" class="btn btn_normal btn_active">
          </div>
        </form>

        <?php }else{ ?>
        <div class="introduction">
          <p>こちらの内容を登録します。</p>
          <p>よろしいでしょうか。</p>
        </div>
        <form action="" method="post" enctype="multipart/form-data" class="form_wrapper">
          <div class="check_field_wrapper">
            <div class="check_field_row">
              <p class="text_centered">メールアドレス</p>
              <input type="hidden" name="email" value="<?php keepFormData('email'); ?>">
              <p class="check_text"><?php keepFormData('email'); ?></p>
            </div>
            <div class="check_field_row">
              <p class="text_centered">パスワード</p>
              <input type="hidden" name="pass" value="<?php keepFormData('pass'); ?>">
              <input type="hidden" name="pass_re" value="<?php keepFormData('pass_re'); ?>">
              <p class="check_text">セキュリティのため表示いたしません</p>
            </div>
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
              <p><?php echo ($_POST['age_flag']) ? '公開する' : '公開しない'; ?></p>
            </div>
            <div class="check_field_row">
              <p class="text_centered">お住まい</p>
              <input type="hidden" name="area_id" value="<?php keepFormData('area_id'); ?>">
              <input type="hidden" name="area_flag" value="<?php keepFormData('area_flag'); ?>">
              <p class="check_text"><?php echo ($area_id === 0) ? '回答しない' : $area_list[($area_id - 1)]['area']; ?></p>
              <p><?php echo ($_POST['area_flag']) ? '公開する' : '公開しない'; ?></p>
            </div>
            <div class="check_field_row <?php if(empty($pic)) echo 'last_form_row'; ?>">
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
              <p><?php echo ($gender_flag) ? '公開する' : '公開しない'; ?></p>
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