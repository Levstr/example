<?php
// создание - редактирование товара каталога
class ShopGoods extends adminMain {
	private $mode;
	private $breadcrumb;
	public function __construct($mode) {
		global $db;
		global $config;
		parent::__construct();
		 
		$this->mode = $mode;
		$this->setTableName('shop_goods', 'id', 'id');
		$this->setPageListAdmin('shopgoods');
		$this->setFieldList(array('section_name'=>array('sql_field'=>'(select name from shop_section where id='.$this->table.'.section_id ) as section_name', 'th_head'=>'Раздел'),
								'subsection_name'=>array('sql_field'=>'(select name from shop_subsection where id='.$this->table.'.subsection_id ) as subsection_name', 'th_head'=>'Подраздел'), 
								'name'=>'Название', 'price'=>'Цена'));
		$this->setFieldOrder('section_name asc, subsection_name asc , name asc');
		$this->delFoto($config ['IMG_DIR_ROOT'].$config ['SHOP_GOODS_FOTO'], 'image','image_list');
		$is_form = $this->getMode($mode);
		
		if($is_form) {			
			$section_list = '';
			if($mode=='edit') {
				$section_id = (int)$this->getFieldValue('section_id');
			} elseif($mode=='create' && (int)getPostField('section')) {
				$section_id = (int)getPostField('section');
			}
			if($section_id) {
				$section_list = "select id as ARRAY_KEY, name from shop_subsection where section_id=".$section_id." order by name asc";
			}
			
			$this->addSelectField('section', 'section_id','Раздел', 'Раздел', null, "select id as ARRAY_KEY,  name from shop_section where hidden=0 order by name asc", 'select_section');
			$this->addSelectField('subsect', 'subsection_id','Подраздел', 'Подраздел',null,$section_list,'select_subsect'); // подгрузку сделаем на аяксе - при выборе в первом select-e услуги
			
			$this->addTextField('name', 'name','Название', 'Название');
			$this->addTextField('price', 'price','Цена', 'Цена');	
			$this->addTextField('weight', 'weight','Вес', 'Вес');	
			$this->addTextArea('descr', 'descr', 'Описание', true);			
			$this->addFotoUploadField('image', 'image', 'Изображение', 'Изображение',$config ['SHOP_GOODS_FOTO'], 'goodsimage', $config['SHOP_FOTO_WIDTH'], 0, $config['SHOP_FOTO_WIDTH_SM'], 0, 'image_list');
			$this->addSelectField('dopcond', 'dop_condition','Дополнительно', '', null, "select id as ARRAY_KEY, name from shop_goods_dop_condition order by name asc");
			
			$this->addTextField('proteins', 'proteins','Белки', 'Белки');	
			$this->addTextField('fats', 'fats','Жиры', 'Жиры');	
			$this->addTextField('carbohydrates', 'carbohydrates','Углеводы', 'Углеводы');	
			$this->addTextField('kcal', 'kcal','Ккал', 'Ккал');	
			$this->addTextArea('descr2', 'descr_ingridients', 'Ингридиенты', true);		
			$this->addCheckField('garnish', 'has_garnish','Блюдо с гарниром');
		}		
	}	
	
	// вывод на сайте(на юзерской стороне)
	public function getUserPage($templ='') {
		global $db;
		global $config;
		
		$text = ''; 	

		return $text;
	}	

	
	public function  makeTitle($menu_section,$menu_item) {
		global $config;
	
		$title = '';
		return $title;
	}

}

$page = new ShopGoods($mode);
?>