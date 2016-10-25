<?php
// страница корзины магазина
class ShopCart extends adminMain {
	private $menuleft;
	public function __construct($mode) {
		global $db;
		global $config;
		 parent::__construct();
		$is_form = $this->getMode($mode);		
	}
	
	// вывод на сайте(на юзерской стороне)
	public function getUserPage($templ='', $section=0) {
		global $db;
		global $config;
		
		$text = '';
		
		$text .= $this->getContent($section);
		return $text;
	}
	
	protected function getContent($section=0) {
		global $db;
		global $config;
		
		$text = '';
		$text .= $this->showCart();
		return $text;
	}
	
	public function  makeTitle($menu_section,$menu_item) {
		global $config;
	
		$title = 'Корзина - '.$config ['SITE_TITLE'];
		return $title;
	}
	
	// корзина для вывода на странице раздела каталога или странице товара
	
	private function showCart() {
		global $db;
		global $config;
		
		$text = $good_list_table = '';
		$good_list = $_COOKIE['cart_inf'];
		if($good_list) {
			$good_arr = explode(',',$good_list);
			if(count($good_arr)) {
				$good_inf = array();
				foreach ($good_arr as $k=>$v) {
					if(!is_numeric($v) && strpos($v,'obed')===false && strpos($v,'_')===false) {
						unset($good_arr[$k]);
					}
				}
				setcookie("cart_inf", implode(',',$good_arr), 0, '/'); 
				foreach ($good_arr as $v) { // разбираем куки - множественные id товаров надо собрать вместе
					if(isset($good_inf[$v])) {
						$good_inf[$v]++;
					} else {
						$good_inf[$v] = 1;
					}
				}
				
				$good_list_table .= '<table class="table_cart">';
				$good_list_table .= '<tr>
										<th>Блюдо</th>
										<th class="weight">Вес</th>
										<th class="qual">Кол-во</th>
										<th class="price">Стоимость</th>
									</tr>';
				$total = 0;
				foreach($good_inf as $k=>$v) {
					if(strpos($k,'obed') === false) {
						$good_id = $good_garnish = $good_garnish_co = 0;						
						if(is_int($k)) {
							$good_id = $k;
						} else {
							$garn_inf = explode('_', $k);
							if(count($garn_inf)==3) {
								$good_id = $garn_inf[0];
								$good_garnish = $garn_inf[1]; // id гарнира 
								$good_garnish_co = ($garn_inf[2] ? $garn_inf[2] : 1 ); // количество порций гарнира
							}
						}
						
						$row = $db->selectRow("select * from shop_goods where id=? limit 1", $k);
						if(count($row)) {
							$good_name = $row['name'];
							$good_price = $row['price'];
							$good_weight = $row['weight'];
							if ($good_garnish) {
								$garn_data = $db->selectRow('select * from shop_garnish where id=?', $good_garnish);
								if(count($garn_data)) {
									$good_name = $row['name'].' + гарнир "' . $garn_data['name'].'" ('.$good_garnish_co.' шт)';
									$good_price = (int)$row['price']+(int)($garn_data['price'])*(int)($good_garnish_co);
									$good_weight = (int)$row['weight']+(int)($garn_data['weight'])*(int)($good_garnish_co);
								}
							}
							
							$good_list_table .= '<tr class="goods_row">
													<td><a href="" class="cart_list popup-click-3" name="'.$k.'">'.$good_name.'</a></td>
													<td>'.$good_weight.' г</td>
													<td><button class="but_cart minus" name="'.$k.'"></button>
														<span class="td_count">'.$v.'</span>
														<span class="td_price_one">'.$good_price.'</span> '.
														'<button class="but_cart plus" name="'.$k.'"></button>
													</td>
													<td><b class="prit">'.($good_price*$v).'</b> <span class="ruble">Р</span></td>
												</tr>';
							$total += ($row['price']*$v);
						}
					} else { // обеды					
						$dinner_number = (int) (str_replace ('obed', '', $k));
						if($dinner_number) {
							$data_dinner = $db->selectRow('select dinner'.$dinner_number.', price'.$dinner_number.' from dinner limit 1');
							if(!empty($data_dinner)) {				
								$good_list_table .= '<tr class="goods_row">
												<td><a href="" class="cart_list popup-click-3" name="'.$k.'">'.$data_dinner['dinner'.$dinner_number].'</a></td>
												<td>&nbsp;</td>
												<td><button class="but_cart minus" name="'.$k.'"></button>
													<span class="td_count">'.$v.'</span>
													<span class="td_price_one">'.$data_dinner['price'.$dinner_number].'</span> '.
													'<button class="but_cart plus" name="'.$k.'"></button>
												</td>
												<td><b class="prit">'.($data_dinner['price'.$dinner_number]*$v).'</b> <span class="ruble">Р</span></td>
											</tr>';
								$total += ($data_dinner['price'.$dinner_number]*$v);
							}
						}						
					}
				}
				
				// доставка -----------
				$delivery_data = $db->selectRow('select delivery_price, delivery_free_minsum from site_options limit 1');
				
				if($delivery_data['delivery_free_minsum'] && $delivery_data['delivery_price'] && $total < (int)($delivery_data['delivery_free_minsum'])) {
					$good_list_table .= '<tr class="goods_row delivery_row">
												<td>Стоимость доставки</td>
												<td>&nbsp;</td>
												<td>&nbsp;</td>
												<td><b class="prit">'.$delivery_data['delivery_price'].'</b> <span class="ruble">Р</span></td>
											</tr>';
					$total += $delivery_data['delivery_price'];
					
					setcookie("deliv_minsum", $delivery_data['delivery_free_minsum'], 0, '/'); 
					setcookie("deliv_price", $delivery_data['delivery_price'], 0, '/'); 
				}
				
				// / доставка --------
				
				$good_list_table .= '</table>';
				$good_list_table .= '<div class="itogo">Итого: <span>'.$total.'</span> <b class="ruble">Р</b></div>';
				
				
				$person_count_select = $this->getPersonCountSelect();
				$date_days_select = $this->getDaysSelect();
				$date_month_select = $this->getMonthSelect();
				$date_hour_select = $this->getHourSelect();
				$date_minutes_select = $this->getMinutesSelect();
				
				$good_list_table = parseSmallTempl ($config ['DIR_USER_TEMPLATES'].'templ.shop_cart_page.php', array('good_list'=>$good_list_table, 'person_count_select'=>$person_count_select, 'date_days_select' =>$date_days_select, 'date_month_select'=>$date_month_select, 'date_hour_select'=>$date_hour_select, 'date_minutes_select'=>$date_minutes_select));
			}
		} else {
			$good_list_table = 'Ваша корзина пуста';
		}
		$text .= parseSmallTempl ($config ['DIR_USER_TEMPLATES'].'templ.shop_cart.php', array('good_list'=>$good_list_table, 'delivery_text'=>getDeliveryComment ()));
		return $text;
	}
	
	// select число персон
	private function getPersonCountSelect() {
		$array = array();
		$pers_count = 10;
		for($i=1; $i<=$pers_count; $i++) {
			$array[$i] = $i;
		}		
		return makeSelect($array, 1);
	}
	
	private function getDaysSelect() {
		$array = array();
		$count = 31;
		for($i=1; $i<=$count; $i++) {
			$day = sprintf("%02d", $i);
			$array[$day] = $day;
		}		
		return makeSelect($array, date('d'));
	}
	
	private function getMonthSelect() {
		global $config;
		return makeSelect($config['DATE_MONTHS'], date('m'));
	}
	
	private function getHourSelect() {
		global $db;
		global $config;
		
		$array = array();
		$param = $db->selectRow('select hour_delivey_start, hour_delivery_stop from site_options limit 1');
		if(count($param)) {
			if($param['hour_delivey_start'] <= $param['hour_delivery_stop']) {
				$from = $param['hour_delivey_start'];
				$to = $param['hour_delivery_stop'];
			} else {
				$from = $param['hour_delivey_stop'];
				$to = $param['hour_delivery_start'];
			}
			for($i=$from; $i<=$to; $i++) {
				$array[$i] = sprintf("%02d", $i);
			}		
		}
		return makeSelect($array, rand($from, $to));
	}
	private function getMinutesSelect() {
		$array = array();
		$count = 55;
		for($i=0; $i<=$count; $i++) {
			if($i%5==0) {
				$minute = sprintf("%02d", $i);
				$array[$minute] = $minute;
			}			
		}		
		return makeSelect($array, 0 );
	}
	
	private function getDeliveryText() {
		global $db;
		global $config;
		return $db->selectCell('select delivery_text from site_options limit 1');
	}	
}

$page = new ShopCart($mode);
?>