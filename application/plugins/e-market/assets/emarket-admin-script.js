$(document).ready(function(e) {
  // выделить все элементы
  $('button[name="checked_all"]').on('click', function(){
    allCheckbox = $('.product-list').find('input[type="checkbox"]');
    allCheckbox.each(function(indx, element){
      if(!element.checked){
        element.checked = true;
      }
    })
  });

  // снять выделение со всех элементов
  $('button[name="unchecked_all"]').on('click', function(){
    allCheckbox = $('.product-list').find('input[type="checkbox"]');
    allCheckbox.each(function(indx, element){
      if(element.checked){
        element.checked = false;
      }
    })
  });
  // удалить выделенные объекты
  $('button[name="delete_objects"]').on('click', function(){
    var allIdObj = [];
    allCheckbox = $('.product-list').find('input[type="checkbox"]:checked');
    allCheckbox.each(function(index, element){
      allIdObj[allIdObj.length] = $(element).data('id');
    });
    if(!allIdObj.length){
      noty_info('error', 'Выберите объекты', 'center');
      return false;
    }

    noty_comfirm('Удалить выделенные объекты?', 'Да', 'Отмена', function(r){
      if(r){
            add_loader();
            $.ajax({
              url: '/admin/e-market/ajax_request?request=delete_objects',
              type: 'POST',
              data: { elements: allIdObj },
              success: function(data){
                var Res = JSON.parse(data);
                console.log(data);
                if(Res.status == 'OK') {
                  noty_info('success', Res.info, 'center');
                  setTimeout(function(){
                    window.location.reload();
                  }, 1000);
                } else {
                  noty_info('error', Res.info, 'center');
                }
                  remove_loader();
              }
          });
      }
  });
  });

  // изменить статус видимости
  $('.product-list').on('click', 'button[name="change_status"]', function() {
    var objID = $(this).data('id');
    var tableTR = $(this).closest('tr');
    add_loader();
    $.ajax({
      url: '/admin/e-market/ajax_request?request=change_status_object',
      type: 'POST',
      data: { element: objID },
      success: function(data){
        var Res = JSON.parse(data);
        if(Res.status == 'OK') {
          tableTR.removeAttr('class').addClass(Res.new_class);
          noty_info('success', Res.info, 'topRight');
        } else {
          noty_info('error', Res.info, 'center');
        }
        remove_loader();
      }
  });
  });
});
