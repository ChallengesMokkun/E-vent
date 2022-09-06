<?php
  require('function.php');
  $title = 'お問い合わせフォーム';
  debugStart();

  //会員かどうか確かめる
  if(!empty($_SESSION['login_date']) && time() <= $_SESSION['login_date'] + $_SESSION['login_limit']){
    debug('期限内ログインユーザー');
    $u_id = $_SESSION['u_id'];
    $user = getUserAddress($u_id);
    $user_name = $user['name'];
    $user_email = $user['email'];

    $member_flag = true;
  }else{
    debug('未ログインユーザー または 期限切れログインユーザー');
    $member_flag = false;
  }

  //値が送信された時(確認・登録両方)
  if(!empty($_POST['pre_post']) || !empty($_POST['post'])){
    $name = (!empty($_POST['name'])) ? $_POST['name'] : NULL;
    $email = $_POST['email'];
    $subject = $_POST['subject'];
    $comment = $_POST['comment'];
  }

  if(!empty($_POST['pre_post'])){
    debug('POST送信を確認');
    debug('確認のための送信');
    debug('pre_post');

    //バリデーションチェック
    if(empty($member_flag)){
      //名前
      if(!empty($name)){
        validMaxlen($name,'name');
      }
      //メール
      validMaxlen($email,'email');
      validTypeEmail($email,'email');
      validEnter($email,'email');
    }

    //件名
    validMaxlen($subject,'subject',20,MAX03);
    validEnter($subject,'subject');

    //内容
    validMaxlen($comment,'comment',500,MAX04);
    validEnter($comment,'comment');

    if(empty($err_msg)){
      debug('バリデーションチェックOK');

    }else{
      debug('invalid: Catch!');
      unset($_POST['pre_post']);
    }
  }

  //バリデーションチェック後
  //入力やり直し
  if(!empty($_POST['undo'])){
    debug('POST送信を確認');
    debug('入力やり直し');
  }

  //入力確定
  if(!empty($_POST['post'])){
    debug('POST送信を確認');
    debug('入力確定');

    try{
      $dbh = dbConnect();
      $sql = 'INSERT INTO inquiry (name,email,subject,comment,create_date) VALUES (:name,:email,:subject,:comment,:create_date)';
      $data = array(
        ':name' => $name,
        ':email' => $email,
        ':subject' => $subject,
        ':comment' => $comment,
        ':create_date' => date('Y-m-d H:i:s')
      );

      $stmt = queryPost($dbh,$sql,$data);
      if($stmt){
        $from = 'challenges.mokkun6@gmail.com';
        $sub = 'お問い合わせを受け付けました | E-vent';
$text_user = <<<EOT
E-ventご利用者
{$name} 様

日頃からE-ventをご利用いただきましてありがとうございます。
お問い合わせを受け付けました。
回答が必要な場合は、3営業日内に回答いたします。

確認のため、お問い合せ内容を以下に記載します。
-----------------------------------
件名:
{$subject}

内容:
{$comment}
-----------------------------------
以上です。

今後ともよろしくお願いいたします。


E-ventスタッフ代表
Mokkun
EOT;

$text_visiter = <<<EOT
E-ventご訪問者
{$name} 様

E-ventにご訪問いただきましてありがとうございます。
お問い合わせを受け付けました。
回答が必要な場合は、3営業日内に回答いたします。

確認のため、お問い合せ内容を以下に記載します。
-----------------------------------
件名:
{$subject}

内容:
{$comment}
-----------------------------------
以上です。

今後ともよろしくお願いいたします。


E-ventスタッフ代表
Mokkun
EOT;


$text_visiter_anonymous = <<<EOT
E-ventご訪問者様

E-ventにご訪問いただきましてありがとうございます。
お問い合わせを受け付けました。
回答が必要な場合は、3営業日内に回答いたします。

確認のため、お問い合せ内容を以下に記載します。
-----------------------------------
件名:
{$subject}

内容:
{$comment}
-----------------------------------
以上です。

今後ともよろしくお願いいたします。


E-ventスタッフ代表
Mokkun
EOT;

        if($member_flag){
          $text = $text_user;
        }elseif(!empty($name)){
          $text = $text_visiter;
        }else{
          $text = $text_visiter_anonymous;
        }

        postMail($from,$email,$sub,$text);

        $_SESSION['success'] = SUC07;
        debug('トップページに移動');
        header('Location:index.php');
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


  require('head.php');
?>
  <body>
    <?php require('header.php'); ?>
    
    <main class="main site_width">
      <h2 class="heading">お問い合わせフォーム</h2>
      <section class="form_width">
      <?php if(empty($_POST['pre_post'])){ ?>
        <div class="msg_area emphasis"><?php errPrint('common'); ?></div>
        <form action="" method="post" class="form_wrapper">
          <?php if(empty($member_flag)){ ?>
          <div class="introduction">
            <p>お問い合わせを受け付けます</p>
            <p>回答が必要な場合は</p>
            <p>下記のメールアドレス宛に</p>
            <p>回答いたします。</p>
          </div>
          <div class="form_row">
            <label>
              <p class="text_centered">お名前&nbsp;*任意</p>
              <div class="msg_area emphasis"><?php errPrint('name'); ?></div>
              <input type="text" name="name" placeholder="お名前" class="<?php is_err('name'); ?>" value="<?php keepFormData('name'); ?>">
            </label>
          </div>
          <div class="form_row">
            <label>
              <p class="text_centered">メールアドレス&nbsp;<span class="emphasis">*</span>必須</p>
              <div class="msg_area emphasis"><?php errPrint('email'); ?></div>
              <input type="text" name="email" placeholder="メールアドレス" class="<?php is_err('email'); ?>" value="<?php keepFormData('email'); ?>">
            </label>
          </div>

          <?php }else{ ?>
          <div class="introduction">
            <p>お問い合わせを受け付けます</p>
            <p>回答が必要な場合は</p>
            <p>ご登録のメールアドレス宛に</p>
            <p>回答いたします。</p>
          </div>
          <input type="hidden" name="name" value="<?php echo $user_name; ?>">
          <input type="hidden" name="email" value="<?php echo $user_email; ?>">
          <?php } ?>

          <div class="form_row">
            <label>
              <p class="text_centered">件名&nbsp;<span class="emphasis">*</span>必須&nbsp;20文字まで</p>
              <div class="msg_area emphasis"><?php errPrint('subject'); ?></div>
              <input type="text" name="subject" placeholder="件名" class="js_text_area <?php is_err('subject'); ?>" value="<?php keepFormData('subject'); ?>">
              <p class="js_text_count"><span class="js_text_num">0</span>/<span class="js_text_num_limit">20</span>文字</p>
            </label>
          </div>
          <div class="form_row last_form_row">
            <label>
              <p class="text_centered">内容&nbsp;<span class="emphasis">*</span>必須&nbsp;500文字まで</p>
              <div class="msg_area emphasis"><?php errPrint('comment'); ?></div>
              <textarea name="comment" class="form_textarea js_text_area <?php is_err('comment'); ?>"><?php echo (!empty($_POST['comment'])) ? sanitize($_POST['comment']) : ''; ?></textarea>
              <p class="js_text_count"><span class="js_text_num">0</span>/<span class="js_text_num_limit">500</span>文字</p>
            </label>
          </div>
          <div class="btn_wrapper">
            <input type="submit" value="送信する" name="pre_post" class="btn btn_normal btn_active">
          </div>
          <div class="btn_wrapper">
            <a href="index.php" class="btn btn_normal btn_gray margin_top">戻る</a>
          </div>
        </form>

        <?php }else{ ?>
        <div class="introduction">
          <p>こちらの内容で送信します。</p>
          <p>よろしいでしょうか。</p>
        </div>
        <form action="" method="post" class="form_wrapper">
        <div class="check_field_wrapper">
          <?php if(empty($member_flag)){ ?>
            <?php if(!empty($name)){ ?>
            <div class="check_field_row">
            <p class="text_centered">お名前</p>
            <p class="check_text"><?php keepFormData('name'); ?></p>
            <?php } ?>
          </div>
          <div class="check_field_row">
            <p class="text_centered">メールアドレス</p>
            <p class="check_text"><?php keepFormData('email'); ?></p>
          </div>
          <?php } ?>

          <input type="hidden" name="name" value="<?php keepFormData('name'); ?>">
          <input type="hidden" name="email" value="<?php keepFormData('email'); ?>">
          <div class="check_field_row">
            <p class="text_centered">件名</p>
            <input type="hidden" name="subject" value="<?php keepFormData('subject'); ?>">
            <p class="check_text"><?php keepFormData('subject'); ?></p>
          </div>
          <div class="check_field_row">
            <p class="text_centered">内容</p>
            <input type="hidden" name="comment" value="<?php keepFormData('comment'); ?>">
            <p class="check_textarea"><?php echo nl2br(sanitize($comment)); ?></p>
          </div>
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