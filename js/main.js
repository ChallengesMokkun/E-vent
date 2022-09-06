//フッター下部固定
$(function(){
  var $footer = $('.footer'),
      margin_top = 80;
  if(window.innerHeight > $footer.outerHeight() + $footer.offset().top){
    $footer.css({'position':'fixed','top':(window.innerHeight - $footer.outerHeight() - margin_top) + 'px'});
  }
});

//画像のライブプレビュー
$(function(){
  var $img_border = $('.js_img_border'),
      $img_input = $('.js_img_input');
  
  $img_border.on('dragover',function(e){
    e.stopPropagation;
    e.preventDefault;
    $(this).css({'border':'3px dotted #ccc'});
  });
  $img_border.on('dragleave',function(e){
    e.stopPropagation;
    e.preventDefault;
    $(this).css({'border':'1px solid #000'});
  });

  $img_input.on('change',function(){
    var file = this.files[0],
        $preview = $(this).siblings('.js_preview'),
        fileReader = new FileReader();

    $img_border.css({'border':'none'});
    
    fileReader.onload = function(event){
      $preview.attr('src',event.target.result).show();
    }

    fileReader.readAsDataURL(file);
  });
});

//成功メッセージの表示・非表示
$(function(){
  var $success = $('.js_success');
  if($success.text().replace(/[\s　]+/g,'').length){
    $success.slideToggle('slow');
    setTimeout(function(){
      $success.slideToggle('slow');
    },3000);
  }
});

//文字数カウント
$(function(){
  var $textarea = $('.js_text_area') || null;

  if($textarea !== null && $textarea !== undefined){
    $textarea.on('keyup',function(){
      var $count = $(this).siblings('.js_text_count'),
          $num = $count.find('.js_text_num'),
          $limit = $count.find('.js_text_num_limit');
      
      $num.text($(this).val().length);
  
      if($(this).val().length > $limit.text()){
        $count.css('color','#f00');
        $(this).css('background-color','rgba(255, 0, 0, 0.5)');
      }else{
        $count.css('color','#000');
        $(this).css('background-color','#ddd');
      }
    });
  }
});

//イベント参加申し込み状況可視化
$(function(){
  var $join_status = $('.js_join_status') || null,
      pop_rate = $join_status.data('rate') || null;
  
  if(pop_rate !== null && pop_rate !== undefined){
      if(pop_rate > 25 && pop_rate <= 100){
        if(pop_rate >=70){
          $join_status.css('background-color','rgba(255, 0, 0, 0.6)');
        }else if(pop_rate >=30){
          $join_status.css('background-color','rgba(255, 166, 0, 0.6)');
        }
      $join_status.css('width',pop_rate + '%');
      
    }else if(pop_rate > 100){
      $join_status.css('width','100%');
      $join_status.css('background-color','rgba(255, 0, 0, 0.6)');
    }
  }
});

//ajaxお気に入り
$(function(){
  var $fav_star = $('.js_favorite') || null,
      event_id = $fav_star.data('fav_event') || null;
  
  if(event_id !== undefined && event_id !== null){
    $fav_star.on('click',function(){
      $.ajax({
        type: 'POST',
        url: 'ajax_favorite.php',
        data: {e_id : event_id}
      }).done(function(data){
        $fav_star.toggleClass('favorite');
      });
    });
  }
});

//画像の切り替え表示
$(function(){
  var $main_img = $('.js_main_img'),
      $sub_img = $('.js_sub_img');
  $sub_img.on('click',function(){
    $main_img.attr('src',$(this).attr('src'));
  });
});
