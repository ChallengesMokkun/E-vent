<?php
  //エラーログの設定
  ini_set('log_errors','on');
  ini_set('error_log','err_php.log');

  //セッションの設定
  session_save_path('/var/tmp');
  ini_set('session.gc_maxlifetime',60*60*24*30);
  ini_set('session.cookie_lifetime',60*60*24*30);

  session_start();
  session_regenerate_id();

  //デバッグフラグ
  $debug_flag = false;

  //定数
  //エラー
  //上限系
  const MAX01 = '255文字以内でご入力ください。';
  const MAX02 = '11文字以内でご入力ください。';
  const MAX03 = '20文字以内でご入力ください。';
  const MAX04 = '500文字以内でご入力ください。';
  const MAX05 = '長期間の設定はできません。';
  //下限系
  const MIN01 = '8文字以上でご入力ください。';
  //形式系
  const TYP01 = 'Email形式でご入力ください。';
  const TYP02 = '使用できない文字・記号を除いてご入力ください。';
  const TYP03 = '半角数字でご入力ください。';
  const TYP04 = 'パスワード形式でご入力ください。';
  const TYP05 = '認証キーの形式とは異なります。';
  const TYP06 = '5桁以内の整数でご入力ください。';
  //入力必須系
  const ABS01 = '入力必須ですのでご入力ください。';
  const ABS02 = 'いずれかのカテゴリーを選択してください。';
  const ABS03 = 'いずれかの地域を選択してください。';
  const ABS04 = '空白文字だけでは送信できません。';
  //非論理系
  const LOG01 = '正しい日時を入力してください。';
  const LOG02 = '正しく上限・下限を設定してください。';
  const LOG03 = '正しく2度ご入力ください。';
  const LOG04 = '現在とは異なるものを設定してください。';
  //無効系
  const INV01 = 'どちらかが異なるか、登録されていません。';
  const INV02 = '現在のパスワードが異なります。';
  const INV03 = '登録されていないか、異なります。';
  const INV04 = '認証キーが異なります。';
  //画像アップロード系
  const IMG01 = '画像のサイズ上限を超えています。';
  const IMG02 = '非対応の画像形式です。';
  const IMG03 = '画像のアップロードができませんでした。';
  const IMG04 = 'その他のエラーが発生しました。';
  //エラーその他
  const DUP01 = 'このメールアドレスは登録できません。';
  const ERR01 = '接続エラーが発生しました。';
  //成功メッセージ
  const SUC01 = 'ユーザー登録しました。';
  const SUC02 = 'ログインしました。';
  const SUC03 = 'プロフィールを更新しました。';
  const SUC04 = 'パスワードを変更しました。';
  const SUC05 = '認証キーを発行しました。';
  const SUC06 = '仮パスワードを発行しました。';
  const SUC07 = 'お問い合わせを受け付けました。';
  const SUC08 = 'イベントを登録しました。';
  const SUC09 = 'イベントを削除しました。';
  const SUC10 = 'イベント情報を更新しました。';
  const SUC11 = 'このイベントに参加申し込みしました。';
  const SUC12 = 'イベント参加をキャンセルしました。';
  //その他
  const WEEK = ['日','月','火','水','木','金','土'];

  //エラーメッセージ格納
  $err_msg = array();


  //エラーログ関数
  function debug($str){
    global $debug_flag;
    if(!empty($debug_flag)){
      error_log('デバッグ: '.$str);
    }
  }
  //エラーログ開始関数
  function debugStart(){
    global $title;
    debug('--------------------------------------------------------------------------------------------------------');
    debug('デバッグ開始');
    debug($title.': '.basename($_SERVER['PHP_SELF']));
    debug('セッションID: '.session_id());
    debug('セッションの中身: '.print_r($_SESSION,true));
    debug('--------------------------------------------------------------------------------------------------------');
    error_log('');
  }

  //エラーログ終了関数
  function debugFinish(){
    error_log('');
    debug('--------------------------------------------------------------------------------------------------------');
    debug('デバッグ終了');
    debug('--------------------------------------------------------------------------------------------------------');
    error_log('');
  }

  //エラーメッセージ追加関数
  function appendErrMsg($key,$err_code){
    global $err_msg;
    $err_msg[$key] = $err_code;
  }
  //エラーメッセージ表示関数
  function errPrint($key){
    global $err_msg;
    if(!empty($err_msg[$key])){
      echo $err_msg[$key];
    }
  }
  //エラーメッセージ有無確認関数(エラーがあればerrクラスを追加する)
  function is_err($key){
    global $err_msg;
    if(!empty($err_msg[$key])){
      echo 'err';
    }
  }

  //フォーマット関数
  //input[type="datetime-local"]からDATETIMEへフォーマット
  function local2Datetime($datetime){
    return mb_substr($datetime,0,10,'UTF-8').' '.mb_substr($datetime,11,5,'UTF-8').':00';
  }
  //DATETIMEからinput[type="datetime-local"]へフォーマット
  function datetime2Local($datetime){
    return mb_substr($datetime,0,10,'UTF-8').'T'.mb_substr($datetime,11,5,'UTF-8');
  }
  //input[type="datetime-local"]からY/n/j(曜日)G:iへフォーマット
  function local2Calendar($datetime){
    $date = strtotime(sanitize(local2Datetime($datetime)));
    echo date('Y/n/j',$date).'('.WEEK[date('w',$date)].')'.date('G:i',$date);
  }
  //DATETIMEからY/n/j(曜日)G:iへフォーマット
  function datetime2Calendar($datetime){
    $date = strtotime(sanitize($datetime));
    echo date('Y/n/j',$date).'('.WEEK[date('w',$date)].')'.date('G:i',$date);
  }
  //DATETIMEからn/j G:iへフォーマット
  function datetime2daytime($datetime){
    $date = strtotime(sanitize($datetime));
    echo date('n/j G:i',$date);
  }

  //バリデーション関数
  //最大文字数チェック
  function validMaxlen($str,$key,$max = 255,$err_code = MAX01){
    if(mb_strlen($str) > $max){
      appendErrMsg($key,$err_code);
    }
  }
  //最小文字数チェック
  function validMinlen($str,$key,$min = 8,$err_code = MIN01){
    if(mb_strlen($str) < $min){
      appendErrMsg($key,$err_code);
    }
  }
  //再入力チェック
  function validRetype($str1,$str2,$key,$err_code = LOG03){
    if($str1 !== $str2){
      appendErrMsg($key,$err_code);
    }
  }
  //異なる値入力チェック
  function validDiff($str1,$str2,$key){
    if($str1 === $str2){
      appendErrMsg($key,LOG04);
    }
  }
  //入力必須チェック(0・空白を認めない)
  function validEnter($str,$key,$err_code = ABS01){
    if(empty($str)){
      appendErrMsg($key,$err_code);
    }
  }
  //入力必須チェック(0を認める・空白を認めない)
  function validEnterOkZero($str,$key,$err_code = ABS01){
    if(!isset($str) || mb_strlen(preg_replace('/　|\s+/','',$str)) === 0){
      appendErrMsg($key,$err_code);
    }
  }
  //時刻チェック(現在時刻)
  function validDatetime($str,$key){
    if(!empty($str)){
      if(strtotime($str) <= time()){
        appendErrMsg($key,LOG01);
      }
    }
  }
  //時間チェック(イベント開催時間)
  function validTimeFlow($str1,$str2,$key){
    if(!empty($str1) && !empty($str2)){
      if(strtotime($str2) <= strtotime($str1)){
        appendErrMsg($key,LOG01);
      }
    }
  }
  //時間チェック(イベント開催期間)
  function validTimeLength($str1,$str2,$key){
    if(!empty($str1) && !empty($str2)){
      if(strtotime($str2) - strtotime($str1) > 60*60*24*31){
        appendErrMsg($key,MAX05);
      }
    }
  }
  //年齢制限チェック
  function validAgeLimit($str1,$str2,$key){
    if($str2 !== 0){
      if($str1 > $str2){
        appendErrMsg($key,LOG02);
      }
    }
  }
  //Email形式チェック
  function validTypeEmail($str,$key){
    if(!preg_match('/^[a-z0-9._+^~-]+@[a-z0-9.-]+$/i',$str)){
      appendErrMsg($key,TYP01);
    }
  }
  //Email重複チェック
  function validDupEmail($str,$key){
    try{
      $dbh = dbConnect();
      $sql = 'SELECT count(*) FROM user WHERE email = :email AND delete_flag = 0'; //tmp_テーブル名を変える
      $data = array(':email' => $str);

      $stmt = queryPost($dbh,$sql,$data);
      $result = $stmt->fetch(PDO::FETCH_ASSOC);

      if(!empty(array_shift($result))){
        appendErrMsg($key,DUP01);
      }
    }catch (Exception $e){
      debug('エラー発生: '.$e->getMessage());
      appendErrMsg('common',ERR01);
    }
  }

  //パスワード形式チェック
  function validTypePass($str,$key,$err_code = TYP02){
    if(!preg_match('/^[a-zA-Z0-9!?-_;:!&#%=<>\\\*\?\+\$\|\^\.\(\)\[\]]+$/',$str)){
      appendErrMsg($key,$err_code);
    }
  }

  //現在のパスワードチェック(パスワード変更時)
  function validPassword($u_id,$str,$key){
    try{
      $dbh = dbConnect();
      $sql = 'SELECT pass FROM user WHERE u_id = :u_id AND delete_flag = 0';
      $data = array(':u_id' => $u_id);

      $stmt = queryPost($dbh,$sql,$data);
      $result = $stmt->fetch(PDO::FETCH_ASSOC);

      if(!empty($result)){
        if(!password_verify($str,array_shift($result))){
          appendErrMsg('old_pass',INV02);
        }

      }else{
        debug('クエリ失敗');
        appendErrMsg('common',ERR01);
      }
      
    }catch (Exception $e){
      debug('エラー発生: '.$e->getMessage());
      appendErrMsg('common',ERR01);
    }
  }

  //認証キーチェック
  function validAuthKey($str,$key){
    if(!preg_match('/\A[a-z\d]{16}+\z/i',$str)){
      appendErrMsg($key,TYP05);
    }
  }

  //数値チェック
  function validIntRange($str,$key,$min = 0,$max = 99999){
    if(isset($str) && is_int($str)){
      $str = (int)$str;
      if($str < $min || $str > $max){
        appendErrMsg($key,TYP06);
      }
    }
  }
  //半角数字チェック
  function validByteNum($str,$key){
    if(isset($str)){
      if(!preg_match('/^\d+$/',$str)){
        appendErrMsg($key,TYP03);
      }
    }
  }


  //入力保持系
  //無害化関数
  function sanitize($str){
    return htmlspecialchars($str,ENT_QUOTES);
  }

  //成功メッセージ取得関数
  function getSuccess(){
    if(!empty($_SESSION['success'])){
      $data = $_SESSION['success'];
      unset($_SESSION['success']);
      echo $data;
    }
  }

  //フォーム入力保持関数(テキスト・DBと照合する場合)
  function keepFormData($key,$method_flag = false){
    if($method_flag){
      $method = $_GET;
    }else{
      $method = $_POST;
    }

    global $dbInfo;
    global $err_msg;
  
    if(!empty($dbInfo)){
      if(!empty($err_msg[$key])){
        if(isset($method[$key])){
          echo sanitize($method[$key]);
        }else{
          echo sanitize($dbInfo[$key]);
        }

      }else{
        if(isset($method[$key]) && $method[$key] !== $dbInfo[$key]){
          echo sanitize($method[$key]);
        }else{
          echo sanitize($dbInfo[$key]);
        }
      }

    }else{
      if(isset($method[$key])){
        echo sanitize($method[$key]);
      }
    }
  }
  //フォーム入力保持関数(datetime-local・DBと照合する場合)
  function keepTimeData($key){
    global $dbInfo;
    global $err_msg;

    if(!empty($dbInfo)){
      if(!empty($err_msg[$key])){
        if(!empty($_POST[$key])){
          echo sanitize($_POST[$key]);
        }else{
          echo datetime2Local(sanitize($dbInfo[$key]));
        }

      }else{
        if(!empty($_POST[$key]) && $_POST[$key] !== datetime2Local($dbInfo[$key])){
          echo sanitize($_POST[$key]);
        }else{
          echo datetime2Local(sanitize($dbInfo[$key]));
        }
      }

    }else{
      if(!empty($_POST[$key])){
        echo sanitize($_POST[$key]);
      }
    }
  }
  //選択保持関数(option/radio)
  //option・radioの値がPOST(またはGET)送信されてないときに0を選択済みにしておく
  function keepSelectData($key,$num,$input_flag = false,$method_flag = false){
    global $dbInfo;
    global $err_msg;

    if(!empty($dbInfo)){
      //DBと照合する場合
      if(isset($_POST[$key]) && (int)$_POST[$key] === (int)$num){
        if($input_flag){
          echo 'selected';
        }else{
          echo 'checked';
        }
      }elseif(!isset($_POST[$key]) && (int)$dbInfo[$key] ===(int)$num){
        if($input_flag){
          echo 'selected';
        }else{
          echo 'checked';
        }
      }
    }else{
      //DBと照合しない場合
      if($method_flag){
        $method = $_GET;
  
      }else{
        $method = $_POST;
      }
  
      if($num === 0){
        if(!isset($method[$key]) || (int)$method[$key] === 0){
          if($input_flag){
            echo 'selected';
          }else{
            echo 'checked';
          }
        }
      }else{
        if(!empty($method[$key]) && (int)$method[$key] === (int)$num){
          if($input_flag){
            echo 'selected';
          }else{
            echo 'checked';
          }
        }
      }
    }
  }

  //選択保持関数(checkbox)
  function keepCheckbox($key,$method_flag = false){
    if($method_flag){
      $method = $_GET;

    }else{
      $method = $_POST;
    }

    if(!empty($method[$key])){
      echo 'checked';
    }
  }

  //GETパラメータ保持関数
  function keepGETparam($del_key = array()){
    if(!empty($_GET)){
      $str = '?';
      foreach($_GET as $key => $val){
        if(!in_array($key,$del_key,true)){
          $str .= $key.'='.$val.'&';
        }
      }
      return mb_substr($str,0,-1,'UTF-8');
    }
  }

  //ログイン確認関数(ログイン認証不要ページ)
  //会員でないことを確かめる(会員なら別ページに移動させる)
  function is_login(){
    if(!empty($_SESSION['login_date']) && time() <= $_SESSION['login_date'] + $_SESSION['login_limit']){
      debug('期限内ログインユーザー');
      header('Location:mypage.php');
      exit();
    }elseif(!empty($_SESSION['login_date'])){
      debug('期限切れログインユーザー');
      header('Location:login.php');
      exit();
    }
  }

  //認証キー・仮パスワードの生成
  function makeRandLetter(){
    $str = '';
    $Letters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
    for($i = 0; $i < 16; $i++){
      $str .= $Letters[mt_rand(0,61)];
    }
    return $str;
  }

  //画像系
  //画像アップロード関数(バリデーション〜アップロード)
  function uploadImg($file,$key){
    if(isset($file['error']) && is_int($file['error'])){
      try{
        switch($file['error']){
          case UPLOAD_ERR_OK:
            break;
          case UPLOAD_ERR_NO_FILE:
            break;
          case UPLOAD_ERR_INI_SIZE:
            throw new RuntimeException(IMG01);
          case UPLOAD_ERR_FORM_SIZE:
            throw new RuntimeException(IMG01);
          default:
            throw new RuntimeException(IMG04);
        }

        if(!empty($file['name'])){
          $type = @exif_imagetype($file['tmp_name']);
          if(!in_array($type,[IMAGETYPE_GIF,IMAGETYPE_JPEG,IMAGETYPE_PNG,IMAGETYPE_WEBP],true)){
            throw new RuntimeException(IMG02);
          }
  
          $path ='uploads/'.sha1_file($file['tmp_name']).image_type_to_extension($type);
          if(!move_uploaded_file($file['tmp_name'],$path)){
            throw new RuntimeException(IMG03);
          }
  
          chmod($path,0644);
  
          return $path;
        }
      }catch (RuntimeException $e){
        debug('アップロード失敗');
        debug('エラー内容: '.$e->getMessage());
        appendErrMsg($key,$e->getMessage());
      }
    } 
  }

  //メール送信
  function postMail($from,$to,$sub,$text){
    mb_language('Japanese');
    mb_internal_encoding('UTF-8');

    if(mb_send_mail($to,$sub,$text,'From: '.$from)){
      debug('メール送信完了');
    }else{
      debug('メール送信失敗');
    }
  }

  //ページネーション
  function pagenation($page_url,$current_page,$total_page,$page_span = 5){
    if($total_page < $page_span){
      $min_page = 1;
      $max_page = $total_page;
    }elseif($current_page - 1 < ($page_span - 1) / 2){
      $min_page = 1;
      $max_page = $page_span;
    }elseif(($total_page - $current_page) < ($page_span - 1) / 2){
      $min_page = $total_page - ($page_span - 1);
      $max_page = $total_page;
    }else{
      $min_page = $current_page - ($page_span - 1) / 2;
      $max_page = $current_page + ($page_span - 1) / 2;
    }
          echo '<div class="page_wrapper">';
            echo '<ul class="page_list">';
            if($current_page !== 1){
              echo '<li class="page">';
                echo '<a href="'.$page_url.keepGETparam(array('p'));
                echo (!empty(keepGETparam(array('p')))) ? '&' : '?';
                echo 'p='.'1'.'"'.' class="page_link">';
                echo '&lt;';
                echo '</a>';
             echo '</li>';
            }
      if($total_page > 1){
        for($i = $min_page;$i <= $max_page;$i++){
            echo '<li class="page';
            echo ($i === $current_page) ? ' active_page">' : '">';
              echo '<a href="'.$page_url.keepGETparam(array('p'));
              echo (!empty(keepGETparam(array('p')))) ? '&' : '?';
              echo 'p='.$i.'" class="page_link';
              echo ($i === $current_page) ? ' active_page_link">' :'">';
              echo $i;
              echo '</a>';
            echo '</li>';
        }
      }
            if($total_page > 1 && $current_page !== (int)$total_page){
              echo '<li class="page">';
                echo '<a href="'.$page_url.keepGETparam(array('p'));
                echo (!empty(keepGETparam(array('p')))) ? '&' : '?';
                echo 'p='.$total_page.'"'.' class="page_link">';
                echo '&gt;';
                echo '</a>';
              echo '</li>';
            }
            echo '</ul>';
          echo '</div>';
  }




  //DB接続系
  //DB接続準備関数
  function dbConnect(){
    global $debug_flag;
    $dsn = 'mysql:dbname=データベース名;host=ホスト名;charset=utf8';//各情報を代入する
    $user = 'ユーザー名';//各情報を代入する
    $password = 'パスワード';//各情報を代入する
    $options = array(
      PDO::ATTR_ERRMODE => (!empty($debug_flag)) ? PDO::ERRMODE_EXCEPTION : PDO::ERRMODE_SILENT,
      PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
      PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
    );

    $dbh = new PDO($dsn,$user,$password,$options);
    return $dbh;
  }
  //DB接続関数
  function queryPost($dbh,$sql,$data){
    $stmt = $dbh->prepare($sql);
    if(!$stmt->execute($data)){
      debug('クエリ失敗');
      debug('失敗したSQL: '.$sql);
      return 0;
    }
    debug('クエリ成功');
    return $stmt;
  }


  //--------------------------------------------------------------------------------------------------------
  //ユーザー取得関数(プロフィール系)
  function getUser($u_id){
    try{
      $dbh = dbConnect();
      $sql = 'SELECT email,name,age_id,age_flag,area_id,area_flag,gender,gender_flag,pic FROM user WHERE u_id = :u_id AND delete_flag = 0';
      $data = array(
        ':u_id' => $u_id
      );

      $stmt = queryPost($dbh,$sql,$data);
      if($stmt){
        return $stmt->fetch(PDO::FETCH_ASSOC);
      }else{
        debug('クエリ失敗');
        return false;
      }

    }catch (Exception $e){
      debug('エラー発生: '.$e->getMessage());
      debug('ユーザー情報を取得できない。');
    }
  }

  //ユーザー取得関数(メールアドレスとニックネーム)
  function getUserAddress($u_id){
    try{
      $dbh = dbConnect();
      $sql = 'SELECT name,email FROM user WHERE u_id = :u_id AND delete_flag = 0';
      $data = array(':u_id' => $u_id);

      $stmt = queryPost($dbh,$sql,$data);
      if($stmt){
        return $stmt->fetch(PDO::FETCH_ASSOC);
      }else{
        debug('クエリ失敗');
        return false;
      }

    }catch (Exception $e){
      debug('エラー発生: '.$e->getMessage());
      debug('メールアドレスを取得できない');
    }
  }

  //ユーザー取得関数(genderとage)
  function getUserProperty($u_id){
    try{
      $dbh = dbConnect();
      $sql = 'SELECT age_id,gender FROM user WHERE u_id = :u_id AND delete_flag = 0';
      $data = array(':u_id' => $u_id);

      $stmt = queryPost($dbh,$sql,$data);
      if($stmt){
        return $stmt->fetch(PDO::FETCH_ASSOC);
      }else{
        debug('クエリ失敗');
        return false;
      }
    }catch (Exception $e){
      debug('エラー発生: '.$e->getMessage());
      debug('ユーザー情報を取得できない');
    }
  }

  //ユーザー取得関数(名前とアイコン)
  function getUserNameIcon($u_id){
    try{
      $dbh = dbConnect();
      $sql = 'SELECT name,pic FROM user WHERE u_id = :u_id AND delete_flag = 0';
      $data = array(':u_id' => $u_id);

      $stmt = queryPost($dbh,$sql,$data);
      if($stmt){
        return $stmt->fetch(PDO::FETCH_ASSOC);
      }else{
        debug('クエリ失敗');
        return false;
      }

    }catch (Exception $e){
      debug('エラー発生: '.$e->getMessage());
    }
  }

  //ユーザー取得関数(主催者情報)
  function getOrganizer($e_id,$o_id){
    $organizer_flag = is_Organizer($e_id,$o_id);
    if($organizer_flag){
      try{
        $dbh = dbConnect();
        $sql = 'SELECT name,age_id,age_flag,area_id,area_flag,gender,gender_flag,pic FROM user WHERE u_id = :o_id AND delete_flag = 0';
        $data = array(':o_id' => $o_id);
  
        $stmt = queryPost($dbh,$sql,$data);
        if($stmt){
          $result['o_data'] = $stmt->fetch(PDO::FETCH_ASSOC);
          
          $sql = 'SELECT count(*) FROM event WHERE o_id = :o_id AND start <= :start AND delete_flag = 0';
          $data = array(
            ':o_id' => $o_id,
            ':start' => date('Y-m-d H:i:s')
          );

          $stmt = queryPost($dbh,$sql,$data);
          if($stmt){
            $result['o_event'] = $stmt->fetch(PDO::FETCH_ASSOC);
  
            $sql = 'SELECT count(*) FROM join_list WHERE o_id = :o_id AND create_date <= :create_date AND delete_flag = 0';
            $data = array(
              ':o_id' => $o_id,
              ':create_date' => date('Y-m-d H:i:s')
            );

            $stmt = queryPost($dbh,$sql,$data);
            if($stmt){
              $result['o_join'] = $stmt->fetch(PDO::FETCH_ASSOC);
              return $result;
  
            }else{
              return false;
            }
  
          }else{
            return false;
          }
  
        }else{
          return false;
        }
      }catch (Exception $e){
        debug('エラー発生: '.$e->getMessage());
      }
    }else{
      return false;
    }
  }

  //イベント主催者確認関数
  function is_Organizer($e_id,$o_id){
    try{
      $dbh = dbConnect();
      $sql = 'SELECT count(*) FROM event WHERE delete_flag = 0 AND e_id = :e_id AND o_id = :o_id';
      $data = array(
        ':e_id' => $e_id,
        ':o_id' => $o_id
      );

      $stmt = queryPost($dbh,$sql,$data);
      $result = $stmt->fetch(PDO::FETCH_ASSOC);

      if(!empty(array_shift($result))){
        return true;
      }else{
        return false;
      }

    }catch (Exception $e){
      debug('エラー発生: '.$e->getMessage());
    }
  }

  //イベント参加確認関数
  function is_JoinUser($e_id,$u_id){
    try{
      $dbh = dbConnect();
      $sql = 'SELECT count(*) FROM join_list WHERE delete_flag = 0 AND e_id = :e_id AND j_id = :j_id';
      $data = array(
        ':e_id' => $e_id,
        ':j_id' => $u_id
      );

      $stmt = queryPost($dbh,$sql,$data);
      $result = $stmt->fetch(PDO::FETCH_ASSOC);

      if(!empty(array_shift($result))){
        return true;
      }else{
        return false;
      }

    }catch (Exception $e){
      debug('エラー発生: '.$e->getMessage());
    }
  }
  
  
  //イベント取得関数(編集可能な自分のイベントを1つ取得・e_idとo_idで照合)
   function getMyEditEvent($e_id,$u_id){
    try{
      $dbh = dbConnect();
      $sql = 'SELECT
              e_name,
              c_id,
              comment,
              start,
              finish,
              area_id,
              capa,
              capa_flag,
              fee,
              place,
              place_flag,
              gender,
              age_min,
              age_max,
              pic1,
              pic2,
              pic3
              FROM event WHERE e_id = :e_id AND o_id = :o_id AND delete_flag = 0';
      $data = array(
        ':e_id' => $e_id,
        ':o_id' => $u_id
      );

      $stmt = queryPost($dbh,$sql,$data);

      if($stmt){
        return $stmt->fetch(PDO::FETCH_ASSOC);
      }else{
        debug('イベント情報を取得できない。');
        return false;
      }


    }catch (Exception $e){
      debug('エラー発生: '.$e->getMessage());
      debug('イベント情報を取得できない。');
    }
  }

  //イベント取得関数(イベント検索)
  function getEventAll($current_page,$order,$future,$c_id,$area_id,$gender,$age_id,$record_span){
    $current_min_record = ($current_page - 1) * $record_span;
    $record = array();
    
    try{
      //条件に合うイベント総数・総ページ数を取得する
      $dbh = dbConnect();
      $sql = 'SELECT count(*) FROM event WHERE delete_flag = 0 AND capa_over = 0 AND start > :start';
      $data = array(':start' => date('Y-m-d H:i:s',time() - 60*60*6));
      //各条件
      //ジェンダー
      if((int)$gender !== 9){
        $sql .= ' AND gender = :gender';
        $data[':gender'] = $gender;
      }
      //時期
      if(!empty($future)){
        switch($future){
          case 1:
            $day = 7;
            break;
          case 2:
            $day = 30;
            break;
          case 3:
            $day = 60;
            break;
          case 4:
            $day = 90;
            break;
          case 5:
            $day = 180;
            break;
          case 6:
            $day = 365;
            break;
        }

        $daytime = 60*60*24;
        $period = $daytime * $day;
        $sql .= ' AND start <= :future';
        $data[':future'] = date('Y-m-d H:i:s',time() + $period);
      }

      //カテゴリー
      if(!empty($c_id)){
        $sql .= ' AND c_id = :c_id';
        $data[':c_id'] = $c_id;
      }

      //地域
      if(!empty($area_id)){
        $sql .= ' AND area_id = :area_id';
        $data[':area_id'] = $area_id;
      }

      //年齢
      if(!empty($age_id)){
        $sql .= ' AND ((age_min <= :age_min AND age_max > 0 AND age_max >= :age_max) OR (age_min <= :age_min AND age_max = 0))';
        $data += array(':age_min' => $age_id,':age_max' => $age_id);
      }
      
      //並び順
      switch($order){
        case 0:
          $sql .= ' ORDER BY start ASC';
          break;
        case 1:
          $sql .= ' ORDER BY start DESC';
          break;
        case 2:
          $sql .= ' ORDER BY participants/capa DESC';
          break;
      }

      $stmt = queryPost($dbh,$sql,$data);
      if($stmt){
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $record['total_record'] = array_shift($result);
        $record['total_page'] = ceil($record['total_record'] / $record_span);
      }else{
        debug('クエリ失敗');
        return false;
      }


      //条件に合うイベント全てを取得する
      $sql = 'SELECT e_id,e_name,c_id,comment,area_id,start,finish,participants,pic1 FROM event WHERE delete_flag = 0 AND start > :start AND capa_over = 0';

      //各条件
      //ジェンダー
      if((int)$gender !== 9){
        $sql .= ' AND gender = :gender';
      }
      //時期
      if(!empty($future)){
        switch($future){
          case 1:
            $day = 7;
            break;
          case 2:
            $day = 30;
            break;
          case 3:
            $day = 60;
            break;
          case 4:
            $day = 90;
            break;
          case 5:
            $day = 180;
            break;
          case 6:
            $day = 365;
            break;
        }

        $daytime = 60*60*24;
        $period = $daytime * $day;
        $sql .= ' AND start <= :future';
      }

      //カテゴリー
      if(!empty($c_id)){
        $sql .= ' AND c_id = :c_id';
      }

      //地域
      if(!empty($area_id)){
        $sql .= ' AND area_id = :area_id';
      }

      //年齢
      if(!empty($age_id)){
        $sql .= ' AND ((age_min <= :age_min AND age_max > 0 AND age_max >= :age_max) OR (age_min <= :age_min AND age_max = 0))';
      }
      
      //並び順
      switch($order){
        case 0:
          $sql .= ' ORDER BY start ASC';
          break;
        case 1:
          $sql .= ' ORDER BY start DESC';
          break;
        case 2:
          $sql .= ' ORDER BY participants/capa DESC';
          break;
      }


      $sql .= ' LIMIT :record_span OFFSET :current_min_record';

      $stmt = $dbh->prepare($sql);
      $stmt->bindValue(':start',date('Y-m-d H:i:s',time() - 60*60*6));
      $stmt->bindValue(':record_span',$record_span,PDO::PARAM_INT);
      $stmt->bindValue(':current_min_record',$current_min_record,PDO::PARAM_INT);

      //各条件
      //ジェンダー
      if((int)$gender !== 9){
        $stmt->bindValue(':gender',$gender);
      }
      //時期
      if(!empty($future)){
        $stmt->bindValue(':future',date('Y-m-d H:i:s',time() + $period));
      }
      //カテゴリー
      if(!empty($c_id)){
        $stmt->bindValue(':c_id',$c_id);
      }
      //地域
      if(!empty($area_id)){
        $stmt->bindValue(':area_id',$area_id);
      }
      
      //年齢
      if(!empty($age_id)){
        $stmt->bindValue(':age_min',$age_id);
        $stmt->bindValue(':age_max',$age_id);
      }

      $stmt->execute();
      if($stmt){
        $record['data'] = $stmt->fetchAll();
        return $record;
      }else{
        return false;
      }
      
    }catch (Exception $e){
      debug('エラー発生: '.$e->getMessage());
    }
  }

  //イベント参加人数修正関数
  function fixParticipants($e_id){
    try{
      //eventテーブルのparticipantsの数値と、join_listテーブルの$e_idかつdelete_flag = 0のレコード数を取得し、ずれがあればjoin_listテーブルに合わせる
      $dbh = dbConnect();
      $sql = 'SELECT capa,capa_flag,participants,capa_over FROM event WHERE e_id = :e_id AND delete_flag = 0';
      $data = array(':e_id' => $e_id);

      $stmt = queryPost($dbh,$sql,$data);
      if($stmt){
        $e_rst = $stmt->fetch(PDO::FETCH_ASSOC);

        $sql = 'SELECT count(*) FROM join_list WHERE e_id = :e_id AND delete_flag = 0';
        $stmt = queryPost($dbh,$sql,$data);

        $rst = $stmt->fetch(PDO::FETCH_ASSOC);
        if($stmt){
          if($rst['count(*)'] !== $e_rst['participants']){
            //capa_flag = 0 かつ capa > 参加者数 かつ capa_over = 1のとき→participantsとcapa_over = 0に修正
            //capa_flag = 0 かつ capa <= 参加者数 かつ capa_over = 0のとき→participantsとcapa_over = 1に修正
            //それ以外のとき→participantsのみ修正
            if(empty($e_rst['capa_flag']) && $e_rst['capa'] > $rst['count(*)'] && !empty($e_rst['capa_over'])){
              $sql = 'UPDATE event SET participants = :participants,capa_over = 0 WHERE e_id = :e_id AND delete_flag = 0';
            
            }elseif(empty($e_rst['capa_flag']) && $e_rst['capa'] <= $rst['count(*)'] && empty($e_rst['capa_over'])){
              $sql = 'UPDATE event SET participants = :participants,capa_over = 1 WHERE e_id = :e_id AND delete_flag = 0';
            
            }else{
              $sql = 'UPDATE event SET participants = :participants WHERE e_id = :e_id AND delete_flag = 0';
            }

            $data = array(
              ':participants' => $rst['count(*)'],
              ':e_id' => $e_id
            );

            $stmt = queryPost($dbh,$sql,$data);
            if($stmt){
              debug('参加者数修正完了');
            }

          }elseif(empty($e_rst['capa_flag']) && $e_rst['capa'] > $rst['count(*)'] && !empty($e_rst['capa_over'])){
            $sql = 'UPDATE event SET capa_over = 0 WHERE e_id = :e_id AND delete_flag = 0';
            $stmt = queryPost($dbh,$sql,$data);
            if($stmt){
              debug('参加者数修正完了');
            }

          }elseif(empty($e_rst['capa_flag']) && $e_rst['capa'] <= $rst['count(*)'] && empty($e_rst['capa_over'])){
            $sql = 'UPDATE event SET capa_over = 1 WHERE e_id = :e_id AND delete_flag = 0';
            $stmt = queryPost($dbh,$sql,$data);
            if($stmt){
              debug('参加者数修正完了');
            }

          }
        }
      }
    }catch( Exception $e){
      debug('エラー発生: '.$e->getMessage());
    }
  }

  //イベント取得関数(イベント詳細と主催者のニックネーム、アイコン・$e_idのみで照合)
  function getEventDetail($e_id){
    try{
      //参加者数の修正が必要なら修正する
      fixParticipants($e_id);
      //イベントの情報を取得する
      $dbh = dbConnect();
      $sql = 'SELECT
              name,
              pic,
              o_id,
              e_name,
              c_id,
              e.comment,
              start,
              finish,
              e.area_id,
              capa,
              participants,
              capa_flag,
              capa_over,
              fee,
              place,
              place_flag,
              e.gender,
              age_min,
              age_max,
              pic1,
              pic2,
              pic3,
              e.create_date
              FROM event AS e LEFT JOIN user ON o_id = u_id WHERE e_id = :e_id AND e.delete_flag = 0 AND user.delete_flag = 0';

      $data = array(':e_id' => $e_id);

      $stmt = queryPost($dbh,$sql,$data);
      if($stmt){
        return $stmt->fetch(PDO::FETCH_ASSOC);
      }else{
        debug('クエリ失敗');
        return false;
      }

    }catch (Exception $e){
      debug('エラー発生: '.$e->getMessage());
    }
  }

  //イベント取得関数(簡易版・$e_idのみで照合)
  function getEventBrief($e_id){
    try{
      //イベントの情報を取得する
      $dbh = dbConnect();
      $sql = 'SELECT
              name,
              pic,
              e_name,
              start,
              finish,
              participants,
              capa_over,
              place
              FROM event AS e LEFT JOIN user ON o_id = u_id WHERE e_id = :e_id AND e.delete_flag = 0 AND user.delete_flag = 0';

      $data = array(':e_id' => $e_id);

      $stmt = queryPost($dbh,$sql,$data);
      if($stmt){
        return $stmt->fetch(PDO::FETCH_ASSOC);
      }else{
        debug('クエリ失敗');
        return false;
      }

    }catch (Exception $e){
      debug('エラー発生: '.$e->getMessage());
    }
  }

  //イベント取得関数(自分のイベントを1つ取得・e_idとo_idで照合)
  function getMyEventRow($e_id,$u_id){
    try{
      $dbh = dbConnect();
      $sql = 'SELECT
              e_name,
              start,
              finish,
              capa,
              participants,
              place,
              pic1
              FROM event WHERE e_id = :e_id AND o_id = :o_id AND delete_flag = 0';
      $data = array(
        ':e_id' => $e_id,
        ':o_id' => $u_id
      );

      $stmt = queryPost($dbh,$sql,$data);

      if($stmt){
        return $stmt->fetch(PDO::FETCH_ASSOC);
      }else{
        debug('イベント情報を取得できない。');
        return false;
      }


    }catch (Exception $e){
      debug('エラー発生: '.$e->getMessage());
      debug('イベント情報を取得できない。');
    }
  }

  //イベント取得関数(イベント検索・主催ユーザーのイベントを取得する)
  function getOrganizerEvent($o_id,$current_page,$order,$future,$c_id,$area_id,$gender,$age_id,$record_span,$past_flag = false,$capa_over = false){
    $current_min_record = ($current_page - 1) * $record_span;
    $record = array();
    
    try{
      //条件に合うイベント総数・総ページ数を取得する
      $dbh = dbConnect();
      $sql = 'SELECT count(*) FROM event WHERE delete_flag = 0 AND o_id = :o_id';
      $data = array(':o_id' => $o_id);
      //各条件
      //過去のイベントを表示しない
      if(empty($past_flag)){
        $sql .= ' AND start > :start';
        $data[':start'] = date('Y-m-d H:i:s',time() - 60*60*6);
      }

      //定員オーバーを表示しない
      if(empty($capa_over)){
        $sql .= ' AND capa_over = 0';
      }

      //ジェンダー
      if((int)$gender !== 9){
        $sql .= ' AND gender = :gender';
        $data[':gender'] = $gender;
      }

      //時期
      if(!empty($future)){
        switch($future){
          case 1:
            $day = 7;
            break;
          case 2:
            $day = 30;
            break;
          case 3:
            $day = 60;
            break;
          case 4:
            $day = 90;
            break;
          case 5:
            $day = 180;
            break;
          case 6:
            $day = 365;
            break;
        }

        $daytime = 60*60*24;
        $period = $daytime * $day;
        $sql .= ' AND start <= :future';
        $data[':future'] = date('Y-m-d H:i:s',time() + $period);
      }

      //カテゴリー
      if(!empty($c_id)){
        $sql .= ' AND c_id = :c_id';
        $data[':c_id'] = $c_id;
      }

      //地域
      if(!empty($area_id)){
        $sql .= ' AND area_id = :area_id';
        $data[':area_id'] = $area_id;
      }

      //年齢
      if(!empty($age_id)){
        $sql .= ' AND ((age_min <= :age_min AND age_max > 0 AND age_max >= :age_max) OR (age_min <= :age_min AND age_max = 0))';
        $data += array(':age_min' => $age_id,':age_max' => $age_id);
      }
      
      //並び順
      switch($order){
        case 0:
          $sql .= ' ORDER BY start ASC';
          break;
        case 1:
          $sql .= ' ORDER BY start DESC';
          break;
        case 2:
          $sql .= ' ORDER BY participants/capa DESC';
          break;
        case 3:
          $sql .= ' ORDER BY e_id DESC';
          break;
      }

      $stmt = queryPost($dbh,$sql,$data);
      if($stmt){
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $record['total_record'] = array_shift($result);
        $record['total_page'] = ceil($record['total_record'] / $record_span);
      }else{
        debug('クエリ失敗');
        return false;
      }


      //条件に合うイベント全てを取得する
      $sql = 'SELECT e_id,e_name,c_id,comment,area_id,start,finish,participants,place,pic1 FROM event WHERE delete_flag = 0 AND o_id = :o_id';

      //各条件
      //過去のイベントを表示しない
      if(empty($past_flag)){
        $sql .= ' AND start > :start';
      }

      //定員オーバーを表示しない
      if(empty($capa_over)){
        $sql .= ' AND capa_over = 0';
      }

      //ジェンダー
      if((int)$gender !== 9){
        $sql .= ' AND gender = :gender';
      }

      //時期
      if(!empty($future)){
        switch($future){
          case 1:
            $day = 7;
            break;
          case 2:
            $day = 30;
            break;
          case 3:
            $day = 60;
            break;
          case 4:
            $day = 90;
            break;
          case 5:
            $day = 180;
            break;
          case 6:
            $day = 365;
            break;
        }

        $daytime = 60*60*24;
        $period = $daytime * $day;
        $sql .= ' AND start <= :future';
      }

      //カテゴリー
      if(!empty($c_id)){
        $sql .= ' AND c_id = :c_id';
      }

      //地域
      if(!empty($area_id)){
        $sql .= ' AND area_id = :area_id';
      }

      //年齢
      if(!empty($age_id)){
        $sql .= ' AND ((age_min <= :age_min AND age_max > 0 AND age_max >= :age_max) OR (age_min <= :age_min AND age_max = 0))';
      }
      
      //並び順
      switch($order){
        case 0:
          $sql .= ' ORDER BY start ASC';
          break;
        case 1:
          $sql .= ' ORDER BY start DESC';
          break;
        case 2:
          $sql .= ' ORDER BY participants/capa DESC';
          break;
        case 3:
          $sql .= ' ORDER BY e_id DESC';
          break;
      }


      $sql .= ' LIMIT :record_span OFFSET :current_min_record';

      $stmt = $dbh->prepare($sql);
      $stmt->bindValue(':o_id',$o_id);
      $stmt->bindValue(':record_span',$record_span,PDO::PARAM_INT);
      $stmt->bindValue(':current_min_record',$current_min_record,PDO::PARAM_INT);

      //各条件
      //過去のイベントを表示しない
      if(empty($past_flag)){
        $stmt->bindValue(':start',date('Y-m-d H:i:s',time() - 60*60*6));
      }
      //ジェンダー
      if((int)$gender !== 9){
        $stmt->bindValue(':gender',$gender);
      }
      //時期
      if(!empty($future)){
        $stmt->bindValue(':future',date('Y-m-d H:i:s',time() + $period));
      }
      //カテゴリー
      if(!empty($c_id)){
        $stmt->bindValue(':c_id',$c_id);
      }
      //地域
      if(!empty($area_id)){
        $stmt->bindValue(':area_id',$area_id);
      }
      
      //年齢
      if(!empty($age_id)){
        $stmt->bindValue(':age_min',$age_id);
        $stmt->bindValue(':age_max',$age_id);
      }

      $stmt->execute();
      if($stmt){
        $record['data'] = $stmt->fetchAll();
        return $record;
      }else{
        return false;
      }
      
    }catch (Exception $e){
      debug('エラー発生: '.$e->getMessage());
    }
  }

    //イベント取得関数(イベント検索・参加イベントを取得する)
    function getJoinEvent($j_id,$current_page,$order,$future,$record_span,$past_flag = false){
      $current_min_record = ($current_page - 1) * $record_span;
      $record = array();
      
      try{
        //条件に合うイベント総数・総ページ数を取得する
        $dbh = dbConnect();
        $sql = 'SELECT count(*) FROM join_list AS j LEFT JOIN event AS e ON j.e_id = e.e_id WHERE j.delete_flag = 0 AND e.delete_flag = 0 AND j_id = :j_id';
        $data = array(':j_id' => $j_id);
        //各条件
        //過去のイベントを表示しない
        if(empty($past_flag)){
          $sql .= ' AND finish > :finish';
          $data[':finish'] = date('Y-m-d H:i:s',time() - 60*60*6);
        }
  
        //時期
        if(!empty($future)){
          switch($future){
            case 1:
              $day = 7;
              break;
            case 2:
              $day = 30;
              break;
            case 3:
              $day = 60;
              break;
            case 4:
              $day = 90;
              break;
            case 5:
              $day = 180;
              break;
            case 6:
              $day = 365;
              break;
          }
  
          $daytime = 60*60*24;
          $period = $daytime * $day;
          $sql .= ' AND start <= :future';
          $data[':future'] = date('Y-m-d H:i:s',time() + $period);
        }
  
        //並び順
        switch($order){
          case 0:
            $sql .= ' ORDER BY start ASC';
            break;
          case 1:
            $sql .= ' ORDER BY start DESC';
            break;
          case 2:
            $sql .= ' ORDER BY participants/capa DESC';
            break;
          case 3:
            $sql .= ' ORDER BY l_id DESC';
            break;

        }
  
        $stmt = queryPost($dbh,$sql,$data);
        if($stmt){
          $result = $stmt->fetch(PDO::FETCH_ASSOC);
          $record['total_record'] = array_shift($result);
          $record['total_page'] = ceil($record['total_record'] / $record_span);
        }else{
          debug('クエリ失敗');
          return false;
        }
  
  
        //条件に合うイベント全てを取得する
        $sql = 'SELECT e.e_id,e.o_id,e_name,c_id,comment,area_id,start,finish,participants,place,pic1 FROM event AS e RIGHT JOIN join_list AS j ON j.e_id = e.e_id WHERE e.delete_flag = 0 AND j.delete_flag = 0 AND j_id = :j_id';
  
        //各条件
        //過去のイベントを表示しない
        if(empty($past_flag)){
          $sql .= ' AND finish > :finish';
        }
  
        //時期
        if(!empty($future)){
          switch($future){
            case 1:
              $day = 7;
              break;
            case 2:
              $day = 30;
              break;
            case 3:
              $day = 60;
              break;
            case 4:
              $day = 90;
              break;
            case 5:
              $day = 180;
              break;
            case 6:
              $day = 365;
              break;
          }
  
          $daytime = 60*60*24;
          $period = $daytime * $day;
          $sql .= ' AND start <= :future';
        }
  
        //並び順
        switch($order){
          case 0:
            $sql .= ' ORDER BY start ASC';
            break;
          case 1:
            $sql .= ' ORDER BY start DESC';
            break;
          case 2:
            $sql .= ' ORDER BY participants/capa DESC';
            break;
          case 3:
            $sql .= ' ORDER BY l_id DESC';
            break;
        }
  
  
        $sql .= ' LIMIT :record_span OFFSET :current_min_record';
  
        $stmt = $dbh->prepare($sql);
        $stmt->bindValue(':j_id',$j_id);
        $stmt->bindValue(':record_span',$record_span,PDO::PARAM_INT);
        $stmt->bindValue(':current_min_record',$current_min_record,PDO::PARAM_INT);
  
        //各条件
        //過去のイベントを表示しない
        if(empty($past_flag)){
          $stmt->bindValue(':finish',date('Y-m-d H:i:s',time() - 60*60*6));
        }

        //時期
        if(!empty($future)){
          $stmt->bindValue(':future',date('Y-m-d H:i:s',time() + $period));
        }
  
        $stmt->execute();
        if($stmt){
          $record['data'] = $stmt->fetchAll();
          return $record;
        }else{
          return false;
        }
        
      }catch (Exception $e){
        debug('エラー発生: '.$e->getMessage());
      }
    }

    //イベント取得関数(イベント検索・参加イベントを取得する)
    function getFavEvent($u_id,$current_page,$order,$future,$record_span,$past_flag = false){
      $current_min_record = ($current_page - 1) * $record_span;
      $record = array();
      
      try{
        //条件に合うイベント総数・総ページ数を取得する
        $dbh = dbConnect();
        $sql = 'SELECT count(*) FROM favorite AS f LEFT JOIN event AS e ON f.e_id = e.e_id WHERE e.delete_flag = 0 AND u_id = :u_id';
        $data = array(':u_id' => $u_id);
        //各条件
        //過去のイベントを表示しない
        if(empty($past_flag)){
          $sql .= ' AND start > :start';
          $data[':start'] = date('Y-m-d H:i:s',time() - 60*60*6);
        }
  
        //時期
        if(!empty($future)){
          switch($future){
            case 1:
              $day = 7;
              break;
            case 2:
              $day = 30;
              break;
            case 3:
              $day = 60;
              break;
            case 4:
              $day = 90;
              break;
            case 5:
              $day = 180;
              break;
            case 6:
              $day = 365;
              break;
          }
  
          $daytime = 60*60*24;
          $period = $daytime * $day;
          $sql .= ' AND start <= :future';
          $data[':future'] = date('Y-m-d H:i:s',time() + $period);
        }
  
        //並び順
        switch($order){
          case 0:
            $sql .= ' ORDER BY start ASC';
            break;
          case 1:
            $sql .= ' ORDER BY start DESC';
            break;
          case 2:
            $sql .= ' ORDER BY participants/capa DESC';
            break;
          case 3:
            $sql .= ' ORDER BY fav_id DESC';
            break;
        }
  
        $stmt = queryPost($dbh,$sql,$data);
        if($stmt){
          $result = $stmt->fetch(PDO::FETCH_ASSOC);
          $record['total_record'] = array_shift($result);
          $record['total_page'] = ceil($record['total_record'] / $record_span);
        }else{
          debug('クエリ失敗');
          return false;
        }
  
  
        //条件に合うイベント全てを取得する
        $sql = 'SELECT e.e_id,e_name,c_id,area_id,start,finish,participants,pic1 FROM event AS e RIGHT JOIN favorite AS f ON f.e_id = e.e_id WHERE e.delete_flag = 0 AND u_id = :u_id';
  
        //各条件
        //過去のイベントを表示しない
        if(empty($past_flag)){
          $sql .= ' AND start > :start';
        }
  
        //時期
        if(!empty($future)){
          switch($future){
            case 1:
              $day = 7;
              break;
            case 2:
              $day = 30;
              break;
            case 3:
              $day = 60;
              break;
            case 4:
              $day = 90;
              break;
            case 5:
              $day = 180;
              break;
            case 6:
              $day = 365;
              break;
          }
  
          $daytime = 60*60*24;
          $period = $daytime * $day;
          $sql .= ' AND start <= :future';
        }
  
        //並び順
        switch($order){
          case 0:
            $sql .= ' ORDER BY start ASC';
            break;
          case 1:
            $sql .= ' ORDER BY start DESC';
            break;
          case 2:
            $sql .= ' ORDER BY participants/capa DESC';
            break;
          case 3:
            $sql .= ' ORDER BY fav_id DESC';
            break;
        }
  
  
        $sql .= ' LIMIT :record_span OFFSET :current_min_record';
  
        $stmt = $dbh->prepare($sql);
        $stmt->bindValue(':u_id',$u_id);
        $stmt->bindValue(':record_span',$record_span,PDO::PARAM_INT);
        $stmt->bindValue(':current_min_record',$current_min_record,PDO::PARAM_INT);
  
        //各条件
        //過去のイベントを表示しない
        if(empty($past_flag)){
          $stmt->bindValue(':start',date('Y-m-d H:i:s',time() - 60*60*6));
        }

        //時期
        if(!empty($future)){
          $stmt->bindValue(':future',date('Y-m-d H:i:s',time() + $period));
        }
  
        $stmt->execute();
        if($stmt){
          $record['data'] = $stmt->fetchAll();
          return $record;
        }else{
          return false;
        }
        
      }catch (Exception $e){
        debug('エラー発生: '.$e->getMessage());
      }
    }

  //イベント参加者取得関数
  function getJoinerList($e_id,$u_id){
    $organizer_flag = is_Organizer($e_id,$u_id);
    if($organizer_flag){
      try{
        //ユーザー情報とj_idを関連づけて、参加者リストを取得する
        $dbh = dbConnect();
        $sql = 'SELECT j_id,name,age_id,age_flag,area_id,area_flag,gender,gender_flag,pic FROM user AS u RIGHT JOIN join_list AS j ON j_id = u_id WHERE j.delete_flag = 0 AND u.delete_flag = 0 AND o_id = :o_id AND e_id = :e_id';
        $data = array(
          ':o_id' => $u_id,
          ':e_id' => $e_id
        );

        $stmt = queryPost($dbh,$sql,$data);
        if($stmt){
          return $stmt->fetchAll();
        }else{
          return false;
        }

      }catch (Exception $e){
        debug('エラー発生: '.$e->getMessage());
      }
    }else{
      return false;
    }
  }

  //掲示板確認関数(既に掲示板があるかどうか調べる)
  function searchBoard($u_id1,$u_id2){
    try{
      $dbh = dbConnect();
      $sql = 'SELECT b_id FROM board WHERE delete_flag = 0 AND ((u_id1 = :u_id1 AND u_id2 = :u_id2) OR (u_id1 = :u_id2 AND u_id2 = :u_id1))';
      $data = array(
        ':u_id1' => $u_id1,
        ':u_id2' => $u_id2
      );

      $stmt = queryPost($dbh,$sql,$data);
      $result = $stmt->fetchAll();
      if(!empty($result[0])){
        return array_pop($result)['b_id'];
      }else{
        return false;
      }

    }catch (Exception $e){
      debug('エラー発生: '.$e->getMessage());
      appendErrMsg('common',ERR01);
    }
  }

  //掲示板確認関数(自分の掲示板かどうか調べて、自分の掲示板なら自分と相手のIDを取得する)
  function getMsgIDs($u_id,$b_id){
    try{
      $dbh = dbConnect();
      $sql = 'SELECT u_id1,u_id2 FROM board WHERE delete_flag = 0 AND b_id = :b_id AND (u_id1 = :u_id OR u_id2 = :u_id)';
      $data = array(
        ':b_id' => $b_id,
        ':u_id' => $u_id
      );

      $stmt = queryPost($dbh,$sql,$data);
      $result = $stmt->fetch(PDO::FETCH_ASSOC);
      if(!empty($result['u_id1'])){
        return $result;

      }else{
        return false;
      }

    }catch (Exception $e){
      debug('エラー発生: '.$e->getMessage());
    }
  }

  //メッセージ取得関数(メッセンジャー)
  function getMessageDetail($b_id){
    try{
      $dbh = dbConnect();
      $sql = 'SELECT msg,f_id,send_date FROM msg WHERE delete_flag = 0 AND b_id = :b_id ORDER BY send_date DESC';
      $data = array(':b_id' => $b_id);

      $stmt = queryPost($dbh,$sql,$data);
      if($stmt){
        return $stmt->fetchAll();
      }else{
        debug('クエリ失敗');
        return false;
      }

    }catch (Exception $e){
      debug('エラー発生: '.$e->getMessage());
    }
  }

  //メッセージ取得関数(連絡掲示板一覧)
  function getMessageAll($u_id,$current_page,$order,$record_span = 20){
    $current_min_record = ($current_page - 1) * $record_span;
    $record = array();
    try{
      //掲示板の総数と総ページ数を取得する
      $dbh = dbConnect();
      $sql = 'SELECT count(*) FROM board WHERE delete_flag = 0 AND (u_id1 = :u_id OR u_id2 = :u_id) AND last_time > create_date';
      $data = array(':u_id' => $u_id);

      $stmt = queryPost($dbh,$sql,$data);
      if($stmt){
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $record['total_record'] = array_shift($result);
        $record['total_page'] = ceil($record['total_record'] / $record_span);
      }else{
        return false;
      }

      //掲示板を取得する
      $sql = 'SELECT b_id,u_id1,u_id2,last_time FROM board WHERE delete_flag = 0 AND (u_id1 = :u_id OR u_id2 = :u_id) AND last_time > create_date';
     
      switch($order){
        case 0:
          $sql .= ' ORDER BY last_time DESC';
          break;
        case 1:
          $sql .= ' ORDER BY last_time ASC';
          break;
      }

      $sql .= ' LIMIT :record_span OFFSET :current_min_record';

      $stmt = $dbh->prepare($sql);
      $stmt->bindValue(':u_id',$u_id);
      $stmt->bindValue(':record_span',$record_span,PDO::PARAM_INT);
      $stmt->bindValue(':current_min_record',$current_min_record,PDO::PARAM_INT);
      $stmt->execute();

      if($stmt){
        $board = $stmt->fetchAll();
        foreach($board as $key => $val){
          $record['data'][$key]['b_id'] = $val['b_id'];
          $record['data'][$key]['last_time'] = $val['last_time'];
          //相手のIDを特定する
          if($val['u_id1'] !== $u_id){
            $partner_id = $val['u_id1'];
          }else{
            $partner_id = $val['u_id2'];
          }
          $record['data'][$key]['member'] = getUserNameIcon($partner_id);
          
          //メッセージを取得する
          $sql = 'SELECT msg FROM msg WHERE delete_flag = 0 AND b_id = :b_id ORDER BY send_date DESC';
          $data = array(':b_id' => $val['b_id']);

          $stmt = queryPost($dbh,$sql,$data);
          if($stmt){
            $rst = $stmt->fetchAll();
            $record['data'][$key]['text'] = array_shift($rst);
          }
        }
        return $record;

      }else{
        return false;
      }

    }catch (Exception $e){
      debug('エラー発生: '.$e->getMessage());
    } 

  }

  //お気に入り確認関数
  function is_Favorite($e_id,$u_id){
    try{
      $dbh = dbConnect();
      $sql = 'SELECT count(*) FROM favorite WHERE delete_flag = 0 AND u_id = :u_id AND e_id = :e_id';
      $data = array(
        ':u_id' => $u_id,
        ':e_id' => $e_id
      );

      $stmt = queryPost($dbh,$sql,$data);
      $result = $stmt->fetch(PDO::FETCH_ASSOC);
      if(!empty(array_shift($result))){
        return true;
      }else{
        return false;
      }

    }catch (Exception $e){
      debug('エラー発生: '.$e->getMessage());
    }
  }

  //年齢取得関数
  function getAge(){
    try{
      $dbh = dbConnect();
      $sql = 'SELECT age_id,age FROM age WHERE delete_flag = 0';
      $data = array();

      $stmt = queryPost($dbh,$sql,$data);

      if($stmt){
        return $stmt->fetchAll();
      }else{
        return false;
      }

    }catch (Exception $e){
      debug('エラー発生: '.$e->getMessage());
      debug('年齢情報を取得できない。');
    }
  }

  //地域取得関数
  function getArea(){
    try{
      $dbh = dbConnect();
      $sql = 'SELECT area_id,area FROM area WHERE delete_flag = 0';
      $data = array();

      $stmt = queryPost($dbh,$sql,$data);

      if($stmt){
        return $stmt->fetchAll();
      }else{
        return false;
      }

    }catch (Exception $e){
      debug('エラー発生: '.$e->getMessage());
      debug('地域情報を取得できない。');
    }
  }

  //イベントカテゴリー取得関数
  function getCategory(){
    try{
      $dbh = dbConnect();
      $sql = 'SELECT c_id,category FROM category WHERE delete_flag = 0';
      $data = array();

      $stmt = queryPost($dbh,$sql,$data);
      if($stmt){
        return $stmt->fetchAll();
      }else{
        return false;
      }

    }catch (Exception $e){
      debug('エラー発生: '.$e->getMessage());
      debug('カテゴリーを取得できない。');
    }
  }