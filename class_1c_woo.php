<?php
class woo_1c{
	

	function createProduct_1c(){

			$url = 'http://'.$_SERVER['HTTP_HOST'];

		    $languages = simplexml_load_file($url."/import.xml");
			$json = json_encode($languages);
			$array = json_decode($json,TRUE);
			echo '<pre>';

			$products = $array['Каталог']['Товары']['Товар'];

	
	foreach($products as $product){
		$name_product = $product['Наименование'];
		$prod_array = array(
                            'author' => '', // optional
                            'title' => (string)$product['Наименование'],
                            'content' => '',
                            //'excerpt'       => 'The product short description…',
                            'regular_price' => $prices, // product regular price
                            'sale_price' => $prices, // product sale price (optional)
                            'stock' => '1', // Set a minimal stock quantity
                            'image_id' => '', // optional
                            'gallery_ids' => array(), // optional
                            'sku' => $product['Артикул'], // optional
                            'tax_class'     => '', // optional
                            'weight'        => '', // optional
							'id_1c' => $product['Ид']
                        );
		if($product['Характеристики']['Характеристика']){
			$mods = $product['Характеристики']['Характеристика'];

			foreach($mods as $key_a => $value_a){
				$value= reset($value_a['@attributes']);
				$key = key($value_a['@attributes']);
				$prod_array['attributes'][$key][]= $value;
			}
			//print_r($m);
		}
						
                        $prod_v_id = $this->create_product_variation_1c($prod_array);
						
						if($product['Характеристики']['Характеристика']){
							$mods = $product['Характеристики']['Характеристика'];

								foreach($mods as $key_a => $value_a){
									$value= reset($value_a['@attributes']);
									$key = key($value_a['@attributes']);
									 $post_v = array(
										'post_author' => 1,
										'post_content' => '',
										'post_status' => 'publish',
										'post_title' => $name_product,
										'post_type' => 'product_variation',
										'post_parent' => $prod_v_id
										);
										
										$mod_id = wp_insert_post($post_v, $wp_error);
										
										$name_tax = wc_attribute_taxonomy_name($key);

											wp_set_object_terms($prod_v_id, $value, $name_tax, true);

											$atr = 'attribute_' . $name_tax;

                                //

											$parents1 = get_term_by('name', $value, $name_tax);

											add_post_meta($mod_id, $atr, $parents1->slug);
										
								}
						}
						
						
						
		}
	}

	public function create_product_variation_1c($data){
		   if( ! function_exists ('save_product_attribute_from_name') ) return;

    $postname = sanitize_title( $data['title'] );
    $author = empty( $data['author'] ) ? '1' : $data['author'];
	
    $post_data111 = array(
        'post_author'   => $author,
        'post_name'     => $postname,
        'post_title'    => $data['title'],
        'post_content'  => $data['content'],
        //'post_excerpt'  => $data['excerpt'],
        'post_status'   => 'publish',
        'ping_status'   => 'closed',
        'post_type'     => 'product',
        'guid'          => home_url( '/product/'.$postname.'/' ),
    );
    // Creating the product (post data)
    $product_id = wp_insert_post( $post_data111, true );
    //add_post_meta( $product_id, '_regular_price',$data['regular_price'] );
    add_post_meta( $product_id, 'id_1c',$data['id_1c'] );
    //wp_mail('drol825@gmail.com', 'VARIANT', 'Ответ:' . $data['regular_price'] );
    // Get an instance of the WC_Product_Variable object and save it
    $product = new WC_Product_Variable( $product_id );
    $product->save();

    


    //Создаем вариацию продукта

    ## ---------------------- Other optional data  ---------------------- ##
    ##     (see WC_Product and WC_Product_Variable setters methods)

    // THE PRICES (No prices yet as we need to create product variations)

    // IMAGES GALLERY
    if( ! empty( $data['gallery_ids'] ) && count( $data['gallery_ids'] ) > 0 )
        $product->set_gallery_image_ids( $data['gallery_ids'] );

    // SKU
    if( ! empty( $data['sku'] ) )
        $product->set_sku( $data['sku'] );

    // STOCK (stock will be managed in variations)
    $product->set_stock_quantity( $data['stock'] ); // Set a minimal stock quantity
    $product->set_manage_stock(true);
    $product->set_stock_status('');

    // Tax class
    if( empty( $data['tax_class'] ) )
        $product->set_tax_class( $data['tax_class'] );

    // WEIGHT
    if( ! empty($data['weight']) )
        $product->set_weight(''); // weight (reseting)
    else
        $product->set_weight($data['weight']);

    $product->validate_props(); // Check validation

    ## ---------------------- VARIATION ATTRIBUTES ---------------------- ##

    $product_attributes = array();

    foreach( $data['attributes'] as $key => $terms ){
        $taxonomy = wc_attribute_taxonomy_name($key); // The taxonomy slug
        $attr_label = ucfirst($key); // attribute label name
        $attr_name = ( wc_sanitize_taxonomy_name($key)); // attribute slug

        // NEW Attributes: Register and save them
        if( ! taxonomy_exists( $taxonomy ) )
            save_product_attribute_from_name( $attr_name, $attr_label );

        $product_attributes[$taxonomy] = array (
            'name'         => $taxonomy,
            'value'        => '',
            'position'     => '',
            'is_visible'   => 0,
            'is_variation' => 1,
            'is_taxonomy'  => 1
        );

        foreach( $terms as $value ){
            $term_name = ucfirst($value);
            $term_slug = sanitize_title($value);

            // Check if the Term name exist and if not we create it.
            if( ! term_exists( $value, $taxonomy ) )
                wp_insert_term( $term_name, $taxonomy, array('slug' => $term_slug ) ); // Create the term

            // Set attribute values
            wp_set_post_terms( $product_id, $term_name, $taxonomy, true );
        }
    }
    update_post_meta( $product_id, '_product_attributes', $product_attributes );
    $product->save(); // Save the data
    return $product_id;

	}
	
		public function data_product(){
		global $wpdb;
		$languages = simplexml_load_file("http://detidom.ru/offers.xml");
			$json = json_encode($languages);
			$array = json_decode($json,TRUE);
			//echo '<pre>';
			$products = $array['ПакетПредложений']['Предложения']['Предложение'];
				foreach($products as $product){
					//print_r($product);
					//echo 'Айди: '. $product['Ид'].'<br />';
					
					$pieces = explode("#", $product['Ид']);
					
					
					$id_1c = $product['Ид'];
					$posts = $wpdb->get_results("SELECT * FROM $wpdb->postmeta WHERE meta_key='id_1c' AND meta_value='$id_1c'");
					//print_r($posts);
					if($posts){
						
					$post_id = $posts[0]->post_id;
					$price = $product['Цены']['Цена']['ЦенаЗаЕдиницу'];
					//echo '11';
					update_post_meta($post_id,'_regular_price',$price);
					
					///echo 'Цена: '.$product['Цены']['Цена']['ЦенаЗаЕдиницу'].'<br />';
						$count_variant =0;
					}elseif(count($pieces) == 2){
						//print_r($product['Наименование']);
						//echo $post_id.'VARIANT<br />';
							$stock_v = $product['Количество'];
							//update_post_meta($post_id,'_stock',$stock);
							$price_v = $product['Цены']['Цена']['ЦенаЗаЕдиницу'];
							//echo (int)$stock. ' - ' .$price.'<br />';
							$variants = $wpdb->get_results($wpdb->prepare(
								"SELECT * FROM {$wpdb->posts} WHERE post_parent = %s  AND post_type='product_variation' ORDER BY ID DESC LIMIT 1 OFFSET $count_variant",
								$post_id
							));
							//print_r($variants[0]->ID);
							$id_variant = $variants[0]->ID;
							//echo 'ID '.$id_variant.'<br />';
							update_post_meta($id_variant,'_regular_price',$price_v);
							update_post_meta($id_variant,'_stock',$stock_v);
							$count_variant +=1;
						
					}
					
				}
			
		
	}
	public function Set_orders(){
		//генерируем xml фаил заказа
		global $wpdb;
		$dateCreateDoc =  date("Y-m-d");  
		
		$xml = '<?xml version="1.0" encoding="windows-1251"?>
			<КоммерческаяИнформация ВерсияСхемы="2.03" ДатаФормирования="'.$dateCreateDoc.'">';
			
			
			$orders = $wpdb->get_results("SELECT * FROM $wpdb->posts WHERE post_type='shop_order'");
					
				
			//print_r($orders);
			foreach($orders as $order){

				//echo $order->post_status;
				
				if((string)$order->post_status != 'draft' AND (string)$order->post_status !='trash' AND (string)$order->post_status !='wc-auto-draft' ){
				//echo $order->ID.'<br />';
					$order_data = wc_get_order( $order->ID );
					$order_total = $order_data->get_total();
					$order_data_t = $order_data->get_data();
					$order_billing_first_name = $order_data_t['billing']['first_name'];
					$order_billing_last_name = $order_data_t['billing']['last_name'];
					$id_user = $order_data->get_user_id(); 
					//print_r($order);
					$xml .= '<Документ><Ид>'.$order->ID.'</Ид><Номер>'.$order->ID.'</Номер><Дата>'.$dateCreateDoc.'</Дата><ХозОперация>Заказ товара</ХозОперация><Роль>Продавец</Роль><Валюта>руб</Валюта><Курс>1</Курс><Сумма>'.$order_total.'</Сумма>';
					
					$xml .='<Контрагенты><Контрагент><Ид>'.$id_user.'</Ид><Наименование>'.$order_billing_first_name.' '.$order_billing_last_name.'</Наименование><Роль>Покупатель</Роль><ПолноеНаименование>'.$order_billing_first_name.' '.$order_billing_last_name.'</ПолноеНаименование><Фамилия>'.$order_billing_last_name.'</Фамилия><Имя>'.$order_billing_last_name.'</Имя><АдресРегистрации><Представление>ггг</Представление><АдресноеПоле><Тип>Почтовый индекс</Тип><Значение>1111</Значение><АдресноеПоле><Тип>Улица</Тип><Значение>ггг</Значение></АдресноеПоле></Контрагент></Контрагенты><Время>15:19:27</Время>';
					$xml .='<Товары>';
					        $order = wc_get_order( $post_id );
        $order_items = $order_data->get_items();
        $arr = array();
        foreach( $order_items as $item_id => $item ){
            $sku = get_post_meta( $item['product_id'], '_sku', true );
			//print_r($item);
			$id_1c_product = get_post_meta($item['product_id'],'id_1c',true);
			$xml .= '<Товар>
				<Ид>'.$id_1c_product.'</Ид>
				<Наименование>'.$item['name'].'</Наименование>
				<БазоваяЕдиница Код="796" НаименованиеПолное="Штука" МеждународноеСокращение="PCE">шт</БазоваяЕдиница>
				<ЦенаЗаЕдиницу>'.$item['subtotal'].'</ЦенаЗаЕдиницу>
				<Количество>'.$item['quantity'].'</Количество>
				<Сумма>'.$item['total'].'</Сумма>
				<ЗначенияРеквизитов>
					<ЗначениеРеквизита>
						<Наименование>ВидНоменклатуры</Наименование>
						<Значение>Товар</Значение>
					</ЗначениеРеквизита>
					<ЗначениеРеквизита>
						<Наименование>ТипНоменклатуры</Наименование>
						<Значение>Товар</Значение>
					</ЗначениеРеквизита>
				</ЗначенияРеквизитов>
			</Товар>';
      
            $arr[] = $var;

        }
					$xml .= '</Товары>';// Товары

				}
			}
		
		//END
		$xml .='</КоммерческаяИнформация>';
			$new_file_name = 'orders.xml';
			$upload = wp_upload_bits( $new_file_name, null, $xml );

				if( $upload['error'] )
					echo 'Запись вызвала ошибку: '. $upload['error'];
				else
					echo 'Запись удалась! Путь файла: '. $upload['file'] .'; УРЛ файла: '. $upload['url'];
	}


}
