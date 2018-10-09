// выгрузка объектов
var expfunc_export_type_object = function(values, sqlOffset, fileName) {
  if(!sqlOffset) {
    sqlOffset = 0;
  }

  if(!fileName) {
    fileName = '';
  }

  $.ajax({
    url: '/admin/exp-csv/ajax/exporttype',
    type: 'POST',
    data: {form_values: values, sql_offset: sqlOffset, file_name: fileName},
    dataType: 'json',
    success: function(DATA) {
      console.log(DATA);
      if(DATA.status == 'OK') {
        if(DATA.flag_update == 'CONTINUE') {
          console.log(DATA.info);
          $('#ui-dialog').append('<p>' + DATA.info + '</p>');
          setTimeout(function() {
            expfunc_export_type_object(values, DATA.offset, DATA.file_name);
          }, 2000)
        } else {
          $('#ui-dialog p.upd-loader').remove();
          $('#ui-dialog').append('<p class="complite-info">' + DATA.info + '</p>');
        }
      } else {
        admin_dialog('<p class="error-info">' + DATA.info + '</p>', 'Stop', 350);
      }
    },
    error: function(a, b, c){
      admin_dialog('Response error', 'Error', 350);
    }
  });
}

// импорт объектов
var expfunc_import = function(FilePath, updOffset, getCountRows) {
  if(!updOffset) {
    updOffset = 0;
  }
  if(!getCountRows) {
    getCountRows = 0;
  }

  $.ajax({
    url: '/admin/exp-csv/ajax/runimport',
    type: 'POST',
    data: {file: FilePath, upd_offset: updOffset, get_count_rows: getCountRows},
    dataType: 'json',
    success: function(DATA) {
      console.log(DATA);
      // если запрашиваются строки, добавим в диалог информацию
      if(getCountRows) {
        $('#ui-dialog').append('<p>' + DATA.info + '</p>');
        return expfunc_import(FilePath, updOffset);
      }

      if(DATA.status == 'OK') {
        // если ответ норм, то следующую партию на обновление
        if(DATA.flag_update == 'CONTINUE') {
          $('#ui-dialog').append('<p>' + DATA.info + '</p>');
          setTimeout(function() {
            console.log(DATA);
            return expfunc_import(FilePath, DATA.offset);
          }, 2000);
        } else {
          $('#ui-dialog p.upd-loader').remove();
          $('#ui-dialog').append('<p class="complite-info">' + DATA.info + '</p>');
          window.onbeforeunload = null;
        }
      } else {
        admin_dialog('<p class="error-info">' + DATA.info + '</p>', 'Error', 350);
        window.onbeforeunload = null;
      }
      return false;
    },
    error: function(a, b, c){
      admin_dialog('Response error', 'Error', 350);
      window.onbeforeunload = null;
    }
  });
}
