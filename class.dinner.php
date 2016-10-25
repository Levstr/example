<?php
// страница обедов
class Dinner extends adminMain {
	public function __construct($mode) {
		global $db;
		global $config;
		parent::__construct();
		$this->setTableName('dinner', 'id', 'id');
		$this->setPageList('dinneredit1.htm','dinneredit','dinnerdel');
		$this->setFieldList(array('title'=>'Заголовок'));
		$is_form = $this->getMode($mode);
		
		if($is_form) {
			$this->addTextArea('text_dinner', 'text_dinner', 'Текст  "наши обеды"', true);
			$this->addTextField('price1', 'price1', 'Цена на легкий обед', 'Цена на легкий обед');
			$this->addTextArea('text1', 'text1', 'Текст для легкого обеда', true);
			$this->addTextField('price2', 'price2', 'Цена на полный обед', 'Цена на полный обед');
			$this->addTextArea('text2', 'text2', 'Текст для полного обеда', true);
		}
	}
	
	// вывод на сайте(на юзерской стороне)
	public function getUserPage($templ='') {
		global $db;
		global $config;
		
		$text = '';
		$row = $db->selectRow("select * from ".$this->table ." limit 1");
		$dinner_text = $price1 = $text1 = $price2 = $text2 = '';
		if(count($row)) {
			$dinner_text = $row['text_dinner'];
			$price1 = $row['price1'];
			$text1 = $row['text1'];
			$price2 = $row['price2'];
			$text2 = $row['text2'];
			$dinner1 = $row['dinner1'];
			$dinner2 = $row['dinner2'];			
		}
		
		$dinner_list = '';
		$list = $db->select('select * from shop_dinner order by id');
		if(count($list)) {
			$row_number = 1;
			foreach ($list as $k=>$v) {
				$dop_class = 'dinner-bl-'.$row_number;
				$clear_class = '';
				if(($k+1)%3==0) {
					$dop_class .= ' last'; // последний элемент в строке
					$clear_class = '<div class="clear"></div>';
					$row_number++;
				}
						
				$templ_fields = array ('week_day'=>$v['week_day'],'bludo1'=>$v['dish_1'],'bludo2'=>$v['dish_2'],'bludo3'=>$v['dish_3'],'kcal'=>$v['calories'], 'dop_class'=>$dop_class);
				$dinner_list .= parseSmallTempl ($config ['DIR_USER_TEMPLATES'].'templ.dinner_block.php', $templ_fields);
				$dinner_list .= $clear_class;
			}
		}
		
		
		$text .= parseSmallTempl ($config ['DIR_USER_TEMPLATES'].'templ.dinner.php', array('dinner_text'=>$dinner_text, 'price1'=>$price1,'dinner1'=>$dinner1, 'text1'=>$text1,'dinner2'=>$dinner2,'price2'=>$price2, 'text2'=>$text2, 'dinner_list'=>$dinner_list));
		
		return $text;
	}
}

$page = new Dinner($mode);
?>