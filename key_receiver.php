<?php
  require('function.php');
  //ログインしていたら別ページへ飛ばす
  is_login();
  //認証キーがなければ別ページへ移動させる
  if(empty($_SESSION['auth_key'])){
    debug('認証キーなし');
    debug('トップページに移動');
    header('Location:index.php');
    exit();
  }

  $title = '仮パスワードの発行';
  debugStart();

  if(!empty($_POST)){
    $auth_key = $_POST['auth_key'];

    //バリデーションチェック
    validAuthKey($auth_key,'auth_key');

    if(empty($err_msg)){
      validRetype($auth_key,$_SESSION['auth_key'],'auth_key',INV04);

      if(empty($err_msg)){
        debug('バリデーションチェックOK');
        //仮パスワードに更新+session_unset()+メール送信
        $pass = makeRandLetter();
        $email = $_SESSION['auth_email'];
        debug('仮パスワード: '.$pass);

        try{
          $dbh = dbConnect();
          $sql = 'UPDATE user SET pass = :pass WHERE email = :email AND delete_flag = 0';
          $data = array(
            ':pass' => password_hash($pass,PASSWORD_DEFAULT),
            ':email' => $email
          );

          $stmt = queryPost($dbh,$sql,$data);
          if($stmt){
            debug('仮パスワードに設定');
            session_unset();
            $_SESSION['success'] = SUC06;

            $from = 'challenges.mokkun6@gmail.com';
            $sub = '仮パスワードの発行 | E-vent';
$text = <<<EOT
E-ventご利用者様

日頃からE-ventをご利用いただきましてありがとうございます。
仮パスワードを発行いたしました。
あなたの仮パスワードは
{$pass}
です。

ログインなさいましたら、マイページからパスワードを変更できます。

不明点がございましたらお問い合わせフォームよりお伺いしたします。
今後ともよろしくお願いいたします。


E-ventスタッフ代表
Mokkun
EOT;
            postMail($from,$email,$sub,$text);
            debug('ログインページに移動');
            header('Location:login.php');
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
        debug('invalid: Catch!');
      }

    }else{
      debug('invalid: Catch!');
    }
  }


  require('head.php');
?>
  <body>
    <p class="js_success" style="display: none;"><?php getSuccess(); ?></p>
    <?php require('header.php'); ?>
    
    <main class="main site_width">
      <h2 class="heading">仮パスワードの発行</h2>
      <section class="form_width">
      <?php if(time() <= $_SESSION['auth_limit']){ ?>
      <div class="msg_area emphasis"><?php errPrint('common'); ?></div>
      <div class="introduction">
        <p>ご本人様確認をさせていただきます。</p>
        <p>仮パスワードを発行いたします。</p>
      </div>
      <form action="" method="post" class="form_wrapper">
        <div class="msg_area emphasis"><?php errPrint('auth_key'); ?></div>
        <div class="form_row last_form_row">
          <label>
            <p class="text_centered">認証キー</p>
            <input type="text" name="auth_key" placeholder="認証キー" class="<?php is_err('auth_key'); ?>">
          </label>
        </div>
        <div class="btn_wrapper">
          <input type="submit" value="送信する" class="btn btn_normal btn_final">
        </div>
      </form>
      <?php }else{ ?>
        <div class="introduction">
        <p>認証期限を過ぎました。</p>
        <p><a href="key_sender.php">こちら</a>から再び認証をお願いいたします。</p>
      </div>
      <?php } ?>
      </section>
    </main>
<?php
  require('footer.php');
  debugFinish();
?>