    <header class="header">
      <div class="header_wrapper site_width">
        <h1 class="logo"><a href="index.php" class="logo_link">E-vent</a></h1>
        <nav class="menu">
          <ul class="header_menu_wrapper">
            <li class="menu_item"><a href="what.php" class="header_menu_link">WHAT</a></li>
            <li class="menu_item"><a href="about.php" class="header_menu_link">ABOUT</a></li>
            <li class="menu_item"><a href="inquiry.php" class="header_menu_link">お問い合わせ</a></li>

            <?php if(!empty($_SESSION['login_date']) && time() <= $_SESSION['login_date'] + $_SESSION['login_limit']){ ?>
            <li class="menu_item"><a href="logout.php" class="header_menu_link">ログアウト</a></li>
            <li class="menu_item"><a href="mypage.php" class="header_menu_link">マイページ</a></li>
            <?php }else{ ?>
            <li class="menu_item"><a href="login.php" class="header_menu_link">ログイン</a></li>
            <li class="menu_item"><a href="signup.php" class="header_menu_link">会員登録</a></li>
            <?php } ?>
            
          </ul>
        </nav>
      </div>
    </header>