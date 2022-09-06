<?php
  require('function.php');
  require('auth.php');
  $title = 'ログイン';
  debugStart();

  if(!empty($_POST)){
    debug('POST送信を確認');
    $email = $_POST['email'];
    $pass = $_POST['pass'];
    
    //バリデーションチェック
    //メール
    validMaxlen($email,'email');
    validTypeEmail($email,'email');
    validEnter($email,'email');
    //パスワード
    validMaxlen($pass,'pass');
    validTypePass($pass,'pass',TYP04);
    validMinlen($pass,'pass');
    validEnter($pass,'pass');

    if(empty($err_msg)){
      debug('形式チェックOK');

      try{
        $dbh = dbConnect();
        $sql = 'SELECT u_id,pass FROM user WHERE email = :email AND delete_flag = 0';
        $data = array(':email' => $email);

        $stmt = queryPost($dbh,$sql,$data);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        debug('クエリ結果: '.print_r($result,true));

        if(!empty($result) && password_verify($pass,$result['pass'])){
          debug('バリデーションチェックOK');
          $u_id = $result['u_id'];
          
          //login_timeを更新
          $sql = 'UPDATE user SET login_time = :login_time WHERE u_id = :u_id AND delete_flag = 0';
          $data = array(
            ':login_time' => date('Y-m-d H:i:s'),
            ':u_id' => $u_id
          );
          $stmt = queryPost($dbh,$sql,$data);

          if($stmt){
            //ログイン処理
            $u_id = $result['u_id'];
            $sesLimit = (!empty($_POST['save_login'])) ? 60*60*24*30 : 60*60;

            $_SESSION['u_id'] = $u_id;
            $_SESSION['login_date'] = time();
            $_SESSION['login_limit'] = $sesLimit;

            $_SESSION['success'] = SUC02;
            debug('ログイン成功');
            debug('マイページに移動');
            header('Location:mypage.php');
            exit();
          }else{
            debug('クエリ失敗');
            appendErrMsg('common',ERR01);
          }

        }else{
          debug('メアドかパスワードが違う');
          appendErrMsg('common',INV01);
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
    <p class="js_success" style="display: none;"><?php getSuccess(); ?></p>
    <?php require('header.php'); ?>
    <main class="main site_width">
      <h2 class="heading">ログイン</h2>
      <section class="form_width">
        <div class="msg_area emphasis"><?php errPrint('common'); ?></div>
        <form action="" method="post" class="form_wrapper">
          <div class="form_row">
            <label>
              <p class="text_centered">メールアドレス</p>
              <div class="msg_area emphasis"><?php errPrint('email'); ?></div>
              <input type="text" name="email" placeholder="メールアドレス" class="<?php is_err('email'); ?>" value="<?php keepFormData('email'); ?>">
            </label>
          </div>
          <div class="form_row last_form_row">
            <label>
              <p class="text_centered">パスワード</p>
              <div class="msg_area emphasis"><?php errPrint('pass'); ?></div>
              <input type="password" name="pass" placeholder="パスワード" class="<?php is_err('pass'); ?>" value="<?php keepFormData('pass'); ?>">
            </label>
            <label>
              <input type="checkbox" name="save_login" <?php keepCheckbox('save_login'); ?>>
              ログインしたままにする
            </label>
          </div>
          <div class="btn_wrapper">
            <input type="submit" value="ログイン" class="btn btn_normal btn_final">
          </div>
          <p class="text_centered margin_top">パスワードをお忘れの方は<a href="key_sender.php" class="link">こちら</a></p>
          <p class="text_centered margin_top">新しく会員登録は<a href="signup.php" class="link">こちら</a></p>
        </form>
      </section>
    </main>
<?php
  require('footer.php');
  debugFinish();
?>