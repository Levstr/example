
function implode( glue, pieces ) {	// Join array elements with a string
	return ( ( pieces instanceof Array ) ? pieces.join ( glue ) : pieces );
}

$(function() {
	// работа с корзиной
	var max_count = 100;
	var cart_cookie_name = 'cart_inf';
	var goods_incart_str = getCookie(cart_cookie_name);
	function addGoodToCart(val) {
		var goods_incart = getCookie(cart_cookie_name).split(',');
		var ar_len = goods_incart.length;
		if (ar_len) {
			if(ar_len < max_count) {
				// надо добавить элемент в массив
				goods_incart.push(val.toString());
			}
			setCookie (cart_cookie_name, goods_incart,"/");
			return 1;
		} else {
			goods_incart.push(val.toString());
			setCookie (cart_cookie_name, val,"/");
			return 1;
		}
		return 0;
	}
	function delGoodFromCart (val) {
		var cartinf = getCookie(cart_cookie_name);
		if (cartinf) {
			var arr_dat = cartinf.split(',');
			var ar_len = arr_dat.length;
			var k = -1;
			for(var i=0; i<ar_len; i++) {
				if(arr_dat[i]==val) {
					k=i;
					break;
				}
			}
			// надо удалить элемент из массива
			if(k!=-1)
				arr_dat.splice(k,1);
			setCookie (cart_cookie_name, implode(',', arr_dat), "/");
			return 1;
		} 
		return 1;
	}
	function delGoodTotalFromCart (val) {
		var cartinf = getCookie(cart_cookie_name);
		if (cartinf) {
			var arr_dat = cartinf.split(',');
			arr_dat = $.grep(arr_dat, function( n, i ) {
				return ( n != val );
			});
			setCookie (cart_cookie_name, implode(',', arr_dat), "/");
			return 1;
		} 
		return 1;
	}
	
	function isCartEmpty() {
		var goods_incart = getCookie(cart_cookie_name);
		if(!goods_incart.length) {
			$('#cart_foot .cart_show').hide();
		} else {
			$('#cart_foot .cart_show').show();
		}
	}


	// корзина в подвале
    $('#cart_foot .open_cart').click(function(e) { 
		 e.stopPropagation(); 
		if($('#cart_foot .open_cart').hasClass('open')) {				
				$(this).removeClass('open');
				$('#cart_foot .open_cart span').text('Свернуть');
				$('#cart_foot .cart_body').slideDown(200);
			} else {				
				$(this).addClass('open');
				$('#cart_foot .open_cart span').text('Развернуть');
				$('#cart_foot .cart_body').slideUp(200);
		}
		isCartEmpty();
    	return false;
   	});
	
	var slider_cart = $('#cart_list').bxSlider({ 
		slideWidth: 301,
		maxSlides: 3,
		moveSlides: 3,
		infiniteLoop : false,
		pager : false,
		hideControlOnEnd: true
	});
	
	function footCartShow() {
		 slider_cart.reloadSlider();
	}
	
	function flyToCart (val, src) {
		if(src=='pop') {
			var img = $('.popup.popup_goods .image_b img');
		} else {
			var obj_good = $('.good_list.good_list'+val);
			var img = obj_good.find('.img img');
		}		
		flyToElement($(img), $('#cart_foot .cart_head h3'), function(){ 
			addToCart(val);
			addGoodToCart(val);
			isCartEmpty();
			});
	}
	
	function addToCart(val) {
		var goods_incart = getCookie(cart_cookie_name).split(',');
		if(goods_incart.length && $.inArray(val.toString(),goods_incart)!=-1) { // такой товар уже был добавлен ранее
			var obj = $('.slide.slide'+val).eq(0).find('.count span');
			if(obj) {
				var quantity = parseInt(obj.text(), 10);
				quantity++;
				$('.slide.slide'+val).find('.count span').text(quantity);
				$('.slide.slide'+val).find('.price_text_it .prit').text(quantity*parseInt($('.slide.slide'+val).eq(0).find('.price').text(), 10));
			}
		} else {	
			var obj_good = 0;
			var good_name = '';
			var price_good = 0;
			if($.isNumeric( val )) {
				obj_good = $('.good_list.good_list'+val);
				good_name = obj_good.find('.name'+val).text();
				price_good = obj_good.find('.price b').text();
			} else {
				var obj_arr = val.split('_'); // гарнир
				if(obj_arr.length == 3) {
					obj_good = $('.good_list.good_list'+obj_arr[0]);
					if(obj_arr[1]>0) {
						var garnish_obj = $('.popup.popup_goods .garnish .garn_list.garn'+obj_arr[1]);
						good_name = obj_good.find('.name'+obj_arr[0]).text()+' + гарнир "' + garnish_obj.find('.garn_name span').text()+'" ('+obj_arr[2]+' шт)';
						price_good = parseInt(obj_good.find('.price b').text(), 10) + parseInt(garnish_obj.find('.price').text(), 10)*parseInt(obj_arr[2], 10);
					} else { // выбран пункт "Без гарнира".... надо это ,чтоб не показывать весь список гарниров, когда листаем в корзине popup
						good_name = obj_good.find('.name'+obj_arr[0]).text()
						price_good = obj_good.find('.price b').text();
					}
				}
			}		

			$('#cart_list').append('<div class="slide slide'+val+'"><table><tr><td class="img"><img src="'+obj_good.find('.img img').attr('src')+'" /></td><td><table><tr><td colspan="2"><div class="good_name"><a href="" class="cart_popup popup-click-1" name="'+val+'">'+good_name+'</a></div></td></tr><tr><td><table><tr><td class="price_text"><b class="price">'+price_good+'</b> <span class="ruble">Р</span></td><td class="count_td"><div class="count"><span>1</span> шт</div><button class="but_cart minus" name="'+val+'"></button><button class="but_cart plus" name="'+val+'"></button></td></tr></table></td><td class="price_text price_text_it"><b class="prit">'+price_good+'</b> <span class="ruble">Р</span></td></tr></table></td></tr></table></div>');
			
			footCartShow();
		}
		ItogoCartCount();
	}
	
	// визуальное отображение
	/*
	src - источник, откуда добавляем в корзину: list - если из списка блюд, pop - если из всплывающего окна
	*/
	function addItemToCart(val, src) {			
		
		// открыть корзину, если она закрыта
		if($('#cart_foot .open_cart').hasClass('open')) {				
			$('#cart_foot .open_cart').removeClass('open');
			$('#cart_foot .open_cart span').text('Свернуть');
			$('#cart_foot .cart_body').slideDown(200, function() {
				flyToCart (val, src);
			});
		} else {		
			flyToCart (val, src);
		}
	}
	
	// добавление в корзину из списка
	$('.good_list button').click(function(e){
		e.stopPropagation(); 
		var good = parseInt($(this).attr('name'), 10);
		if(good > 0) {
			addItemToCart(good, 'list');			
		}
		return false;
	});
	// добавление в корзину из popup
	$(document).on("click", ".popup.popup_goods button.order_but" , function(e) {
		e.stopPropagation(); 
		var good = $(this).attr('name');
		if(!$(this).hasClass('dinner_but')) {
			// к блюду может прилагаться гарнир
			if($(this).parent().find("input[type='radio']").length) {
				var garnish = $(this).parent().find("input[type='radio']:checked").val();
				var garnish_co = parseInt($(this).parent().find(".td_count").text(), 10);
				if(garnish >= 0) {					
					good = good+'_'+garnish+'_'+(garnish_co > 0 ? garnish_co : 1);
				}
			}
			addItemToCart(good, 'pop');	
		} else {
			addDinnerToCart(good, 'pop');	
		}
		return false;
	});
	
	
	function ItogoCartCount() {
		var price_itogo = 0;
		$('.cart_body .slide:not(.bx-clone)').each(function() {
			price_itogo += parseInt($(this).find('.count').children('span').text(), 10) * parseInt($(this).find('.price').text(), 10);
		});		
		$('.cart_body .itogo span').text(price_itogo);
	}
	
	$(document).on("click", ".slide .but_cart.plus" , function() {
		var obj = $(this).parent().find('.count span');
		var curcount = parseInt(obj.text(), 10);
		if(curcount<max_count) {
			var good = $(this).attr('name');
			addGoodToCart(good);
			curcount++;
			$('.slide.slide'+good).find('.count span').text(curcount);
			$('.slide.slide'+good).find('.price_text_it').children('.prit').text(curcount*parseInt($('.slide.slide'+good).eq(0).find('.price').text(), 10));
			ItogoCartCount();
		}
		isCartEmpty();
		return false;
	});
	
	$(document).on("click", ".slide .but_cart.minus" , function() {
		var obj = $(this).parent().find('.count span');
		var curcount = parseInt(obj.text(), 10);
		var good = $(this).attr('name');
		if(curcount>1) {				
			delGoodFromCart(good);
			curcount--;
			$('.slide.slide'+good).find('.count span').text(curcount);
			$('.slide.slide'+good).find('.price_text_it').children('.prit').text(curcount*parseInt($('.slide.slide'+good).eq(0).find('.price').text(), 10));			
		} else { // удаление из корзины
			delGoodTotalFromCart(good);
			$('#cart_list').find('.slide.slide'+good).remove();
			footCartShow();
		}
		ItogoCartCount();
		isCartEmpty();
		return false;
	});
	
	// страница корзины
	function ItogoPageCartCount() {
		var price_itogo = 0;
		$('.table_cart tr.goods_row').each(function() {
			if (!$(this).hasClass("delivery_row")) { // доставку добавляем отдельно
				price_itogo += parseInt($(this).find('.td_count').text(), 10) * parseInt($(this).find('.td_price_one').text(), 10);
			}
		});		
				
		// доставка
		var deliv_minsum = parseInt(getCookie('deliv_minsum'), 10);
		var deliv_price = parseInt(getCookie('deliv_price'), 10);
		if(deliv_minsum && deliv_price) {
			if(parseInt(price_itogo, 10) < deliv_minsum) {
				if(!$('.table_cart tr.goods_row.delivery_row').length) {
					$('.table_cart').append('<tr class="goods_row delivery_row"><td>Стоимость доставки</td><td>&nbsp;</td><td>&nbsp;</td><td><b class="prit">'+deliv_price+'</b> <span class="ruble">Р</span></td></tr>');
				}
				price_itogo += deliv_price;
			} else { // достигнута минимальная сумма бесплатной доставки
				$('.table_cart tr.goods_row.delivery_row').remove();
			}
		}
		
		$('.cart_left .itogo span').text(price_itogo);
	}
	function isPageCartEmpty() {
		var goods_incart = getCookie(cart_cookie_name);
		if(!goods_incart.length) {
			$('.cart_left').html('Ваша корзина пуста');
		}
	}
	$(document).on("click", ".table_cart .but_cart.plus" , function() {
		var obj = $(this).siblings('.td_count');
		var curcount = parseInt(obj.text(), 10);
		if(curcount<max_count) {
			var good = $(this).attr('name');
			addGoodToCart(good);
			curcount++;
			obj.text(curcount);
			$(this).parent().parent().find('.prit').text(curcount*parseInt($(this).siblings('.td_price_one').text(), 10));
			ItogoPageCartCount();
		}
		isPageCartEmpty();
		return false;
	});
	
	$(document).on("click", ".table_cart .but_cart.minus" , function() {
		var obj = $(this).siblings('.td_count');
		var curcount = parseInt(obj.text(), 10);
		var good = $(this).attr('name');
		if(curcount>1) {				
			delGoodFromCart(good);
			curcount--;
			obj.text(curcount);
			$(this).parent().parent().find('.prit').text(curcount*parseInt($(this).siblings('.td_price_one').text(), 10));
		} else { // удаление из корзины
			delGoodTotalFromCart(good);
			$(this).parent('td').parent('tr').remove();
		}
		ItogoPageCartCount();
		isPageCartEmpty();
		return false;
	});
	
	
	$('#cart_foot button.cart_page').click(function(){
		window.location.href='/cart.htm';
	});
	
	// отправка данных - заказ из корзины
	$('.form_order_cart').each(function () {
		var form = $(this);
		form.find('button.submit').removeAttr("disabled", "disabled");
		form.find('button.submit').click(function() { 
			 var proceed = true;
	        form.find('.error').text('');  
	        form.find('input').each(function(){
	           $(this).removeClass('err');	        
	           var field_name = $(this).attr('name');
	           if(form.find('.error.'+field_name).length)   {  // есть еще и необязательные поля, для которых не нужна проверка
				var error_text = '';
				if(field_name=='user_name') {
					if(!$.trim($(this).val()) || $(this).val().length < 3) {
	                	error_text = 'Заполните, пожалуйста, имя';
						$(this).addClass('err'); 
	                } 
				} else if(field_name=='user_phone') {
					if(!$.trim($(this).val()) || $(this).val().length < 6) {
	                	error_text = 'Заполните, пожалуйста, телефон';
						$(this).addClass('err'); 
	                } 
                }
				if(error_text.length) {
					form.find('.error.'+field_name).text(error_text);  
	                proceed = false; //set do not proceed flag
				}			

	            }
	        });
		
		
		 if(proceed) //everything looks good! proceed...
        {	$(this).attr('disabled', 'disabled');
            //get input field values data to be sent to server
            post_data = {
                'user_name'   : form.find('input[name=user_name]').val(),
                'user_tel'    : form.find('input[name=user_phone]').val(),
                'user_street'    : form.find('input[name=user_street]').val(),
                'user_build'    : form.find('input[name=user_build]').val(),
                'user_app'    : form.find('input[name=user_app]').val(),
                'user_ent'    : form.find('input[name=user_ent]').val(),
                'user_floor'    : form.find('input[name=user_floor]').val(),
                'user_count'    : form.find('select[name=user_count]').val(),
                'user_day'    : form.find('select[name=user_day]').val(),
                'user_month'    : form.find('select[name=user_month]').val(),
                'user_hour'    : form.find('select[name=user_hour]').val(),
                'user_minute'    : form.find('select[name=user_minute]').val()
            };
           
            //Ajax post data to server
            $.post('js/order.php', post_data, function(response){  
                if(response.type == 'error'){ //load json data from server and output message    
                    form.find('.error_text').text(response.text);
					form.find('button.submit').removeAttr("disabled", "disabled");
                }else{
                    //reset values in all input fields
                    form.html('');
					setCookie (cart_cookie_name, '',"/"); // чистим корзину
					form.parent().children('.cart_data').remove();
					form.html(response.text);
                }
            }, 'json');
        }
		});
	});	
});