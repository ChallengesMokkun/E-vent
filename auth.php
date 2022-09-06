<?php 
  if(!empty($_SESSION['login_date'])){
    if(time() > $_SESSION['login_date'] + $_SESSION['login_limit']){
      debug('期限切れログインユーザー');
      session_destroy();
      if(basename($_SERVER['PHP_SELF']) !== 'login.php'){
        debug('ログインページに移動');
        header('Location:login.php');
        exit();
      }

    }else{
      debug('期限内ログインユーザー');
      $_SESSION['login_date'] = time();
      if(basename($_SERVER['PHP_SELF']) === 'login.php'){
        debug('マイページに移動');
        header('Location:login.php');
        exit();
      }
    }

  }else{
    debug('未ログインユーザー');
    if(basename($_SERVER['PHP_SELF']) !== 'login.php'){
      debug('ログインページに移動');
      header('Location:login.php');
      exit();
    }
  }