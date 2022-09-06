        <aside class="sidebar">
          <h3 class="side_heading">検索メニュー</h3>
          <form action="" method="get" class="side_form_wrapper">
            <?php if(basename($_SERVER['PHP_SELF']) === 'organizer_event.php'){ ?>
            <input type="hidden" name="o_id" value="<?php echo $_GET['o_id']; ?>">
            <?php } ?>
            <div class="form_row">
              <label>
                <p class="text_centered">並び順</p>
                <select name="order" class="form_select_side">
                  <option value="0" class="form_select_item" <?php keepSelectData('order',0,true,true); ?>>開催日が近い順</option>
                  <option value="1" class="form_select_item" <?php keepSelectData('order',1,true,true); ?>>開催日が遠い順</option>
                  <option value="2" class="form_select_item" <?php keepSelectData('order',2,true,true); ?>>人気順</option>
                </select>
              </label>
            </div>
            <div class="form_row">
              <label>
                <p class="text_centered">開催時期</p>
                <select name="future" class="form_select_side">
                  <option value="0" class="form_select_item" <?php keepSelectData('future',0,true,true); ?>>すべて</option>
                  <option value="1" class="form_select_item" <?php keepSelectData('future',1,true,true); ?>>1週間後まで</option>
                  <option value="2" class="form_select_item" <?php keepSelectData('future',2,true,true); ?>>1ヶ月後まで</option>
                  <option value="3" class="form_select_item" <?php keepSelectData('future',3,true,true); ?>>2ヶ月後まで</option>
                  <option value="4" class="form_select_item" <?php keepSelectData('future',4,true,true); ?>>3ヶ月後まで</option>
                  <option value="5" class="form_select_item" <?php keepSelectData('future',5,true,true); ?>>半年後まで</option>
                  <option value="6" class="form_select_item" <?php keepSelectData('future',6,true,true); ?>>1年後まで</option>
                </select>
              </label>
            </div>
            <?php if(basename($_SERVER['PHP_SELF']) !== 'join.php' && basename($_SERVER['PHP_SELF']) !== 'favorite.php'){ ?>
            <div class="form_row">
              <label>
                <p class="text_centered">カテゴリー</p>
                <select name="c_id" class="form_select_side">
                  <option value="0" class="form_select_item" <?php keepSelectData('c_id',0,true,true); ?>>すべて</option>
                  <?php
                    if(!empty($category_list)){
                      foreach($category_list as $key => $val){
                  ?>
                  <option value="<?php echo $val['c_id']; ?>" class="form_select_item" <?php keepSelectData('c_id',$val['c_id'],true,true); ?>><?php echo $val['category']; ?></option>
                  <?php
                      }
                    }
                  ?>
                </select>
              </label>
            </div>
            <div class="form_row">
              <label>
                <p class="text_centered">開催地域</p>
                <select name="area_id" class="form_select_side">
                  <option value="0" class="form_select_item" <?php keepSelectData('area_id',0,true,true); ?>>すべて</option>
                  <?php
                    if(!empty($area_list)){
                      foreach($area_list as $key => $val){
                  ?>
                  <option value="<?php echo $val['area_id']; ?>" class="form_select_item" <?php keepSelectData('area_id',$val['area_id'],true,true); ?>><?php echo $val['area']; ?></option>
                  <?php
                      }
                    }
                  ?>
                </select>
              </label>
            </div>
            <div class="form_row">
              <label>
                <p class="text_centered">対象</p>
                <select name="gender" class="form_select_side">
                  <option value="0" class="form_select_item" 
                  <?php
                    if(basename($_SERVER['PHP_SELF']) !== 'organize.php'){
                      keepSelectData('gender',0,true,true); 
                    }else{
                      if(!empty($method[$key]) && (int)$method[$key] === (int)$num){
                          echo 'selected';
                      }
                    }
                  ?>
                  >
                    全員
                  </option>
                  <option value="1" class="form_select_item" <?php keepSelectData('gender',1,true,true); ?>>女性限定</option>
                  <option value="2" class="form_select_item" <?php keepSelectData('gender',2,true,true); ?>>男性限定</option>
                  <option value="9" class="form_select_item" 
                  <?php
                  if(basename($_SERVER['PHP_SELF']) !== 'organize.php'){
                    keepSelectData('gender',9,true,true);
                  }else{
                    if(!isset($method[$key]) || (int)$method[$key] === 9){
                      echo 'selected';
                    }
                  }
                  ?>
                   >
                    すべて
                  </option>
                </select>
              </label>
            </div>
            <div class="<?php echo (basename($_SERVER['PHP_SELF']) !== 'index.php') ? 'form_row' : 'last_form_row'; ?>">
              <label>
                <p class="text_centered">世代</p>
                <select name="age_id" id="" class="form_select_side">
                  <option value="0" class="form_select_item" <?php keepSelectData('age_id',0,true,true); ?>>すべて</option>
                  <?php
                    if(!empty($age_list)){
                      foreach($age_list as $key => $val){
                  ?>
                  <option value="<?php echo $val['age_id']; ?>" class="form_select_item" <?php keepSelectData('age_id',$val['age_id'],true,true); ?>><?php echo $val['age']; ?></option>
                  <?php
                      }
                    }
                  ?>
                </select>
              </label>
            </div>
            <?php } ?>
            <?php if(basename($_SERVER['PHP_SELF']) !== 'index.php'){ ?>
            <div class="form_row">
              <label>
                <p class="text_centered">過去のイベント</p>
                <input type="checkbox" name="past_flag" <?php keepCheckbox('past_flag',true); ?>>
                表示する
              </label>
            </div>
            <?php } ?>
            <?php if(basename($_SERVER['PHP_SELF']) === 'organizer_event.php' || basename($_SERVER['PHP_SELF']) === 'favorite.php'){ ?>
            <div class="form_row">
              <label>
                <p class="text_centered">定員オーバー</p>
                <input type="checkbox" name="capa_over" <?php keepCheckbox('capa_over',true); ?>>
                表示する
              </label>
            </div>
            <?php } ?>
            <div class="btn_wrapper">
              <input type="submit" value="検索" class="btn btn_small btn_final">
            </div>
          </form>
        </aside>