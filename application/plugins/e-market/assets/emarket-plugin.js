var emarketAjaxRequest = function(method, params, callBack) {
  $.ajax({
	url: '/ajax/plugin/e-market/emarket?action=' + method,
	type: 'POST',
	data: params,
    dataType: 'json',
    success: function(DATA) {
		if(callBack) {
			callBack(DATA);
		}
	},
    error: function(a, b, c) {
      console.warn(a);
      console.warn(b);
      console.warn(c);
      if(callBack) {
  		callBack({status: 'ERROR'});
  	}
    }
  });
}

/*
// добавить товар в корзину
productID - id товара (объекта)
productCount - количество товара
cartType - тип корзины (корзина (1), лист желаний (2), список сравнения (3))
cartID - id корзины. Если есть, то cartType не учитывается
callBack - функция callback
*/
var emarketAddToCart = function(productID, productCount, cartType, cartID, callBack) {
  var params = {'productId': productID, 'productCount': productCount, 'cartType': cartType, 'cartID': cartID};
  emarketAjaxRequest('add-to-cart', params, callBack);
}

/*
// изменить количество товара в корзине
productID - id товара (объекта)
productCount - новое количество товара
cartType - тип корзины (корзина (1), лист желаний (2), список сравнения (3))
cartID - id корзины. Если есть, то cartType не учитывается
callBack - функция callback
*/
var emarketChangeCountProduct = function(productID, productCount, cartType, cartID, callBack) {
  var params = {'productId': productID, 'productCount': productCount, 'cartType': cartType, 'cartID': cartID};
  emarketAjaxRequest('change-count-product', params, callBack);
}

/*
// удалить товар из корзины
productID - id товара (объекта)
cartType - тип корзины (корзина (1), лист желаний (2), список сравнения (3))
cartID - id корзины. Если есть, то cartType не учитывается
callBack - функция callback
*/
var emarketDeleteProduct = function(productID, cartType, cartID, callBack) {
  var params = {'productId': productID, 'cartType': cartType, 'cartID': cartID};
  emarketAjaxRequest('delete-product', params, callBack);
}

/*
// изменить описание в товаре
productID - id товара (объекта)
cartType - тип корзины (корзина (1), лист желаний (2), список сравнения (3))
cartID - id корзины. Если есть, то cartType не учитывается иначе берется наполняемая с указанным типом
descrText - текст, который надо сохранить
callBack - функция callback
*/
var emarketChangeDescriptionProduct = function(productID, cartType, cartID, descrText, callBack) {
    var params = {'productID': productID, 'cartType': cartType, 'cartID': cartID, 'description': descrText};
    emarketAjaxRequest('change-description-product', params, callBack);
}
