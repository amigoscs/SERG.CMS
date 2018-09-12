$(document).ready(function(e) {
  $('body').on('submit.COMMENTS', 'form.comments-form.form-ajax', function(){
    var actionUrl = $(this).attr('action');
    var formValues = $(this).serialize();
    $.ajax({
      url: '/ajax/plugin/comments?action=newcomment',
      type: 'POST',
      data: formValues,
      success: function(data){
          var DATA = JSON.parse(data);
          // закроем форму
          $('form.comments-form').slideUp(300, function(){
            $('form.comments-form').remove();
            // если комментариев нет, то удалим информацию об этом
            if($('.comments-not-found').length){
              $('.comments-not-found').remove();
            }
          });
          if(DATA.status == 'OK'){
            $('#comment_response').addClass('response-true complite').text(DATA.info);
          }else{
            $('#comment_response').addClass('response-true error').text(DATA.info);
          }
          //console.log(DATA);
  	}
    });
    return false;
  });

  $('body').on('click.COMMENTS', 'a.create-ansver', function(){
    var parentCommentID = $(this).data('id');
    var parentInput = $(this).closest('li');

    var childContentForm = parentInput.children('.comment-answer');
    $('form.comments-form input.fpp').val(parentCommentID);
    $('form.comments-form').appendTo(childContentForm);
    return false;
  });

  $('body').on('click.COMMENTS', '.stop-answer', function(){
      $('form.comments-form input.fpp').val('0');
    $('form.comments-form').appendTo($('#commet_form_container'));
    return false;
  });


});
