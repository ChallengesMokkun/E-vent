<?php
  require('function.php');
  //ログインしていたら別ページへ飛ばす
  is_login();

  $title = '認証キーの発行';
  debugStart();

  if(!empty($_POST)){
    $email = $_POST['email'];

    //バリデーションチェック
    validMaxlen($email,'email');
    validTypeEmail($email,'email');
    validEnter($email,'email');

    if(empty($err_msg)){
      try{
        $dbh = dbConnect();
        $sql = 'SELECT count(*) FROM user WHERE email = :email AND delete_flag = 0';
        $data = array(':email' => $email);

        $stmt = queryPost($dbh,$sql,$data);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if(!empty(array_shift($result))){
          debug('バリデーションチェックOK');

          $from = 'challenges.mokkun6@gmail.com';
          $auth_key = makeRandLetter();
          $auth_limit_minutes = 30;
          $sub = '認証キーの発行 | E-vent';
$text = <<<EOT
E-ventご利用者様

日頃からE-ventをご利用いただきましてありがとうございます。
仮パスワード発行のための認証キーを発行いたしました。
あなたの認証キーは
{$auth_key}
です。

ただいまより{$auth_limit_minutes}分以内に認証キーを入力してください。
仮パスワード発行は下記URLから行えます。
http://localhost:8888/05_WEBservice/E_VENT/key_receiver.php

なお時間を過ぎましたら、お手数ですが再び認証キーを発行していただきますようお願いいたします。

不明点がございましたらお問い合わせフォームよりお伺いしたします。
今後ともよろしくお願いいたします。


E-ventスタッフ代表
Mokkun
EOT;
          debug('認証キー: '.$auth_key);
          $_SESSION['auth_key'] = $auth_key;
          $_SESSION['auth_email'] = $email;
          $_SESSION['auth_limit'] = time() + 60 * $auth_limit_minutes;
          $_SESSION['success'] = SUC05;

          postMail($from,$email,$sub,$text);
          header('Location:key_receiver.php');
          exit();

        }else{
          debug('invalid Catch!');
          debug('メアドが違う もしくは クエリ失敗');
          appendErrMsg('email',INV03);
        }

      }catch (Exception $e){
        debug('エラー発生: '.$e->getMessage());
        appendErrMsg('common',ERR01);
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
      <h2 class="heading">認証キーの発行</h2>
      <section class="form_width">
      <div class="msg_area emphasis"><?php errPrint('common'); ?></div>
      <div class="introduction">
        <p>仮パスワードを発行するために</p>
        <p>ご本人様確認をさせていただきます。</p>
        <p>ご登録のあるメールアドレスへ認証キーをお送りします。</p>
      </div>
        <form action="" method="post" class="form_wrapper">
          <div class="form_row last_form_row">
            <label>
              <p class="text_centered">メールアドレス</p>
              <div class="msg_area emphasis"><?php errPrint('email'); ?></div>
              <input type="text" name="email" placeholder="メールアドレス" class="<?php is_err('email'); ?>">
            </label>
          </div>
          <div class="btn_wrapper">
            <input type="submit" value="送信する" class="btn btn_normal btn_active">
          </div>
          <div class="btn_wrapper">
            <a href="login.php" class="btn btn_normal btn_gray margin_top">戻る</a>
          </div>
        </form>
      </section>
    </main>
<?php
require('footer.php');
debugFinish();
?>