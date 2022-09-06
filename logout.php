<?php
  require('function.php');
  $title = 'ログアウト';

  debugStart();
  session_destroy();
  debug('ログアウト完了。');
  debug('ログインページに移動');
  header('Location:login.php');
  exit();