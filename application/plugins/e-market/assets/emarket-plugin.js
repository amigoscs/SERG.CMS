var emarketAjaxRequest;
emarketAjaxRequest = function(method, params, callBack) {
  $.ajax({
		url: '/ajax/plugin/e-market/' + method,
		type: 'POST',
		data: params,
		success: function(data){
			if(callBack) {
				callBack(data);
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
var emarketAddToCart;
emarketAddToCart = function(productID, productCount, cartType, cartID, callBack) {
  var params = {'productId': productID, 'productCount': productCount, 'cartType': cartType, 'cartID': cartID};
  emarketAjaxRequest('addToCart', params, callBack);
}

/*
// изменить количество товара в корзине
productID - id товара (объекта)
productCount - новое количество товара
cartType - тип корзины (корзина (1), лист желаний (2), список сравнения (3))
cartID - id корзины. Если есть, то cartType не учитывается
callBack - функция callback
*/
var emarketChangeCountProduct;
emarketChangeCountProduct = function(productID, productCount, cartType, cartID, callBack) {
  var params = {'productId': productID, 'productCount': productCount, 'cartType': cartType, 'cartID': cartID};
  emarketAjaxRequest('changeCountProduct', params, callBack);
}

/*
// удалить товар из корзины
productID - id товара (объекта)
cartType - тип корзины (корзина (1), лист желаний (2), список сравнения (3))
cartID - id корзины. Если есть, то cartType не учитывается
callBack - функция callback
*/
var emarketDeleteProduct
emarketDeleteProduct = function(productID, cartType, cartID, callBack) {
  var params = {'productId': productID, 'cartType': cartType, 'cartID': cartID};
  emarketAjaxRequest('deleteProduct', params, callBack);
}
