<?php
  require('function.php');
  require('auth.php');
  $title = 'パスワード変更';
  debugStart();

  if(!empty($_POST)){
    debug('POST送信を確認');
    $old_pass = $_POST['old_pass'];
    $pass = $_POST['pass'];
    $pass_re = $_POST['pass_re'];

    $u_id = $_SESSION['u_id'];
    //バリデーションチェック
    validMaxlen($pass,'pass');
    validMinlen($pass,'pass');
    validTypePass($pass,'pass');
    validRetype($pass,$pass_re,'pass_re');
    validEnter($old_pass,'old_pass');
    validEnter($pass,'pass');
    validEnter($pass_re,'pass_re');
    if(empty($err_msg)){
      validPassword($u_id,$old_pass,'old_pass');
      validDiff($old_pass,$pass,'pass');

      if(empty($err_msg)){
        debug('バリデーションチェックOK');
        //パスワード更新
        try{
          $dbh = dbConnect();
          $sql = 'UPDATE user SET pass = :pass WHERE u_id = :u_id AND delete_flag = 0';
          $data = array(
            ':pass' => password_hash($pass,PASSWORD_DEFAULT),
            ':u_id' => $u_id
          );

          $stmt = queryPost($dbh,$sql,$data);
          if($stmt){
            debug('パスワード変更完了');

            $from = 'challenges.mokkun6@gmail.com';
            $user = getUserAddress($u_id);

            $user_name = $user['name'];
            $to = $user['email'];
            $sub = 'パスワード変更完了のお知らせ | E-vent';
$text = <<<EOT
E-ventご利用者
{$user_name} 様

日頃からE-ventをご利用いただきましてありがとうございます。
パスワード変更が完了しましたことをお知らせいたします。
不明点がございましたらお問い合わせフォームよりお伺いしたします。
今後ともよろしくお願いいたします。


E-ventスタッフ代表
Mokkun
EOT;
            postMail($from,$to,$sub,$text);

            $_SESSION['success'] = SUC04;
            debug('マイページへ移動');
            header('Location:mypage.php');
            exit();

          }

        }catch (Exception $e){
          debug('エラー発生: '.$e->getMessage());
          appendErrMsg('common',ERR01);
        }

      }else{
        debug('invalid: Catch!');
      }
    }else{
      debug('invalid: Catch!');
    }
  }


  require('head.php');
?>
  <body>
    <?php require('header.php'); ?>
    
    <main class="main site_width">
      <h2 class="heading">パスワード変更</h2>
      <section class="form_width">
      <div class="msg_area"><?php errPrint('common'); ?></div>
        <form action="" method="post" class="form_wrapper">
          <div class="form_row">
            <label>
              <p class="text_centered">現在のパスワード</p>
              <div class="msg_area emphasis"><?php errPrint('old_pass'); ?></div>
              <input type="password" name="old_pass" placeholder="パスワード" class="<?php is_err('old_pass'); ?>" value="<?php keepFormData('old_pass'); ?>">
            </label>
          </div>
          <div class="form_row">
            <label>
              <p class="text_centered">新しいパスワード&nbsp;<span class="emphasis">*</span>8文字以上</p>
              <p class="text_centered">半角英数字と!?-_;:!&#%=<>\*?+$|^.()[]が使えます</p>
              <div class="msg_area emphasis"><?php errPrint('pass'); ?></div>
              <input type="password" name="pass" placeholder="新しいパスワード" class="<?php is_err('pass'); ?>" value="<?php keepFormData('pass'); ?>">
            </label>
          </div>
          <div class="form_row  last_form_row">
            <label>
              <p class="text_centered">新しいパスワード再入力&nbsp;<span class="emphasis">*</span>8文字以上</p>
              <div class="msg_area emphasis"><?php errPrint('pass_re'); ?></div>
              <input type="password" name="pass_re" placeholder="新しいパスワード" class="<?php is_err('pass_re'); ?>" value="<?php keepFormData('pass_re'); ?>">
            </label>
          </div>
          <div class="btn_wrapper">
            <input type="submit" value="変更する" class="btn btn_normal btn_final">
          </div>
          <div class="btn_wrapper">
          <a href="mypage.php" class="btn btn_normal btn_gray margin_top">戻る</a>
        </div>
        </form>
      </section>
    </main>
<?php
  require('footer.php');
  debugFinish();
?>