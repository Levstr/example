<?php
 //check if its an ajax request, exit if not
    if(!isset($_SERVER['HTTP_X_REQUESTED_WITH']) AND strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
       
        $output = json_encode(array( //create JSON data
            'type'=>'error',
            'text' => 'Sorry Request must be Ajax POST'
        ));
        die($output); //exit script outputting json data
    }
	
require_once('./configs/init.php');

global $db;
global $config;

/*
структура приходящих goods _id:

- int - инфа о товаре из таблицы shop_goods
- "obed".int - инфа об обеде из таблицы shop_dinner
- int_int_int - id товара из таблицы shop_goods "_" id гарнира из таблицы shop_garnish "_" сколько порций гарнира привязано к данному товару 
*/

function getGarnishInf($garnish_id, $garnish_co, $cart_list) {
	global $db;
	global $config;
	
	$data = '';
	
	if (($garnish_id || ($garnish_id===0 && $garnish_co)) && $cart_list) { 			
		if($garnish_id) {
			$garnish_inf  = $db->selectRow('select * from shop_garnish where id=? limit 1', $garnish_id);
			if(count($garnish_inf)) {
				$data = '<h4>Выбранный гарнир:</h4>';
				$data .= '<p class="garn_list"><span class="garn_name">'.$garnish_inf['name'].'</span><span class="price">'.$garnish_inf['price'].'</span><span class="ruble">Р</span></p>';
				$data .= '<p>Количество порций гарнира: <b>'.($garnish_co ? $garnish_co : 1).'</b></p>';
			}
		} else {
			$data = '<h4>Выбранный гарнир:</h4>';
			$data .= '<p class="garn_list"><span class="garn_name">Без гарнира</span></p>';
		}
	} else { // если никакой гарнир не выбран, выводим весь список
		$garnish_inf  = $db->select('select * from shop_garnish order by num');
		if(count($garnish_inf)) {
			$data = '<div class="clear"></div><h4>Гарниры:</h4>';
			$data .= '<p class="garn_list"><input type="radio" name="garn" value="0"  checked="checked" /><span class="garn_name">Без гарнира</span></p>';
			foreach ($garnish_inf as $v) {
				$data .= '<p class="garn_list garn'.$v['id'].'"><input type="radio" name="garn" value="'.$v['id'].'" /><span class="garn_name"><span>'.$v['name'].'</span> ('.$v['weight'].'г)</span><span class="price">'.$v['price'].'</span><span class="ruble">Р</span></p>';
			}
			$data .= '<p>Количество порций: </p><p><button class="but_add but_minus"></button> <span class="td_count">1</span> <button class="but_add but_plus"></button></p>';
		}
	}
	
	return $data;
}

$goods_id = trim($_GET['goods']);
$cart_list = 0;
$result = '';

if(strpos($goods_id,'obed') === false) {
	$garnish_id = $garnish_co = 0;
	if(is_int($goods_id)) {
		$goods_id = @intval($goods_id);
	} else { // пришли данные из корзины типа int_int_int
		$goods_arr = explode('_',$goods_id);
		if(!empty($goods_arr) && is_numeric($goods_arr[0])) {
			$goods_id = @intval($goods_arr[0]);
			$garnish_id = @intval($goods_arr[1]);
			$garnish_co = @intval($goods_arr[2]);	
			$cart_list = 1;
		}
	}
	
	if($goods_id > 0) {
		$row = $db->selectRow('select * from shop_goods where id=?',$goods_id );
		$img_path = $config ['IMG_DIR'].$config ['SHOP_GOODS_FOTO'];

		$res = array();
		if(count($row)) {
			$res['name'] = $row['name'];
			$res['price'] = $row['price'];
			$res['descr1'] = $row['descr'];
			$res['descr2'] = ($row['descr_ingridients'] ? '<h4>Ингридиенты</h4>'.$row['descr_ingridients'] : '');
			$res['image'] = $img_path.$row['image'];
			$res['weight'] = $row['weight'];
			$res['proteins'] = $row['proteins'];
			$res['fats'] = $row['fats'];
			$res['carbohydrates'] = $row['carbohydrates'];
			$res['kcal'] = $row['kcal'];
			
			$garnish_inf = '';
			if($row['has_garnish']) { // к блюду надо прицепить гарнир
				$garnish_inf = getGarnishInf($garnish_id, $garnish_co, $cart_list);
			}
			$res['garnish'] = $garnish_inf;

			$result = array('type'=>'success', 'goodsinf'=>$res);
		}
	}
} else { // обеды
	$dinner_number = (int) (str_replace ('obed', '', $goods_id));
	if($dinner_number) {
		$current_week_day =  date('N', strtotime(' +1 day')); // текущий день недели... решили, что обед добавляется не текущего, а следующего дня
		$dinner_arr = $db->selectRow('select * from shop_dinner where id=?', $current_week_day);
		$data_dinner = $db->selectRow('select dinner'.$dinner_number.', price'.$dinner_number.' from dinner limit 1');
		if(!empty($data_dinner) && !empty($dinner_arr)) {
			$res = array();							
			$res['week_day'] = $data_dinner['dinner'.$dinner_number].' '.$dinner_arr['week_day'];
			$res['bludo1'] = $dinner_arr['dish_1'];
			$res['bludo2'] = $dinner_arr['dish_2'];
			$res['bludo3'] = $dinner_arr['dish_3'];
			$res['kcal'] = $dinner_arr['calories'];
			$res['price'] = $data_dinner['price'.$dinner_number];
			$res['orderbut'] = $dinner_number;

			$result = array('type'=>'success', 'goodsinf'=>$res);
		}
	}		
}

if(empty($result)) {
	$result = array('type'=>'error');
}

/*
 * Упаковываем данные с помощью JSON
 */
print json_encode($result);

?>