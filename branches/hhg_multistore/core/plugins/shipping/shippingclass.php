<?php
/* -----------------------------------------------------------------------------------------
   $Id:$   

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2003 XT-Commerce
   -----------------------------------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(zones.php,v 1.19 2003/02/05); www.oscommerce.com 
   (c) 2003	 nextcommerce (zones.php,v 1.7 2003/08/24); www.nextcommerce.org

   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/

class shippingclass {
	var $code, $title, $description, $icon, $enabled, $num_classes, $store_id;	
	/**
	 * class constructor
	 */
	function shippingclass() {
		global $order;
		
		$this->store_id = hhg_get_store_id();
		$this->code = 'shippingclass';
		$this->title = MODULE_SHIPPING_SHIPPINGCLASS_TEXT_TITLE;
		$this->description = MODULE_SHIPPING_SHIPPINGCLASS_TEXT_DESCRIPTION;
		$this->sort_order = MODULE_SHIPPING_SHIPPINGCLASS_SORT_ORDER;
		$this->icon = '';
		$this->tax_class = MODULE_SHIPPING_SHIPPINGCLASS_TAX_CLASS;
		$this->enabled = ((MODULE_SHIPPING_SHIPPINGCLASS_STATUS == 'True') ? true : false);
		
		/**
		 * CUSTOMIZE THIS SETTING FOR THE NUMBER OF SHIPPINGCLASSES NEEDED
		 * 
		 */
		$this->num_classes = 4;
		
			if (($this->enabled == true) && ((int)MODULE_SHIPPING_FLAT_ZONE > 0)) {
			$check_flag = false;
			$check = hhg_db_query("select zone_id from " . TABLE_ZONES_TO_GEO_ZONES . " where geo_zone_id = '" . MODULE_SHIPPING_SHIPPINGCLASS_ZONE . "' and zone_country_id = '" . $order->delivery['country']['id'] . "' order by zone_id");
			while (!$check->EOF) {
				if ($check->fields['zone_id'] < 1) {
					$check_flag = true;
					break;
				} elseif ($check->fields['zone_id'] == $order->delivery['zone_id']) {
					$check_flag = true;
					break;
				}
				$check->MoveNext();
			}
			if ($check_flag == false) {
				$this->enabled = false;
			}
		}
				
		$this->types = array();
		$products_shippingclass = hhg_db_query ($sql = 'SELECT * FROM configuration_' . $this->store_id . '_modules WHERE configuration_key like "MODULE_SHIPPING_SHIPPINGCLASS_ZONENAME_%" ORDER BY configuration_value');
		while( !$products_shippingclass->EOF) {
			$this->types[str_replace('MODULE_SHIPPING_SHIPPINGCLASS_ZONENAME_', '', $products_shippingclass->fields['configuration_key'])] = $products_shippingclass->fields['configuration_value'];
			$products_shippingclass->MoveNext();
		}

	}
	
	/**
	 * class methods
	 */
	function quote($method = '') {
		global $order, $shipping_weight, $shipping_num_boxes;
		
		$dest_country = $order->delivery ['country'] ['iso_code_2'];
		$dest_country_name = $order->delivery['country']['title'];		
		$pass = array();
		
		//Ermitteln welche Versandklassen die Artikel im Warenkorb haben
		$products_available_classes = array ();
		$highestpriority = null; //ID der Versandklasse mit der höchsten Priorität

		

		foreach ( $order->products as $product ) {
			$products_shippingclass= hhg_db_query ( $sql = 'SELECT products_shippingclass, products_weight, products_shippingcosts FROM ' . TABLE_PRODUCTS . ' WHERE products_id = ' . (int) ( $product ['id'] ) );

			if(!$products_shippingclass->fields['products_shippingclass']) {
				$products_shippingclass->fields['products_shippingclass'] = 2;
			}
			
			if($products_shippingclass->fields['shipping_costs']) {
				$products_shippingclass->fields['products_shippingclass'] = 3;
			}

			if (defined ( 'MODULE_SHIPPING_SHIPPINGCLASS_ZONENAME_' . $products_shippingclass->fields ['products_shippingclass'] )) {
				$products_available_classes [$products_shippingclass->fields ['products_shippingclass']] ['qty'] += $product ['qty'];
				//TODO: Alternative Staffelung nach Gewicht oder Preis, statt Versandkostenfrei.
				$products_available_classes [$products_shippingclass->fields ['products_shippingclass']] ['weight'] += $products_shippingclass->fields ['products_weight'];
				$products_available_classes [$products_shippingclass->fields ['products_shippingclass']] ['order_amount'] += $product ['price'];
				

				if ($highestpriority == null) {
					//initialisieren
					$highestpriority = $products_shippingclass->fields ['products_shippingclass'];
				} else {
					if (constant ( 'MODULE_SHIPPING_SHIPPINGCLASS_PRIORITY_' . $products_shippingclass->fields ['products_shippingclass'] ) < constant ( 'MODULE_SHIPPING_SHIPPINGCLASS_PRIORITY_' . $highestpriority )) {
						$highestpriority = $products_shippingclass->fields ['products_shippingclass'];
					}
				}
				if($products_shippingclass->fields['shipping_costs']) {
					$dest_table = split("[:,]" , $products_shippingclass->fields['shipping_costs']);
				} else {
					$dest_table = split ( "[:;]", constant ( 'MODULE_SHIPPING_SHIPPINGCLASS_COST_' . $products_shippingclass->fields ['products_shippingclass'] ) );
				}
				for($i = 0; $i < sizeof ( $dest_table ); $i += 2) {
					if (strpos ( $dest_table [$i + 1], ',' ) !== false) {
						$dest_table_scale = split ( "[,|]", $dest_table [$i + 1] );
						for($ii = 0; $ii < sizeof ( $dest_table_scale ); $ii += 2) {
							if ($products_available_classes [$products_shippingclass->fields ['products_shippingclass']] ['qty'] >= $dest_table_scale [$ii]) {
								$current_shipping_cost = floatval ( $dest_table_scale [$ii + 1] );
							}
						}
					} else {
						$current_shipping_cost = $dest_table [$i + 1];
					}
					if ($dest_country == $dest_table [$i] || $dest_table [$i] == '00') {
						if (constant ( 'MODULE_SHIPPING_SHIPPINGCLASS_SUMMATE_' . $products_shippingclass->fields ['products_shippingclass']  ) == 'True') {
							$products_available_classes [$products_shippingclass->fields ['products_shippingclass']] ['shipping_cost'] = floatval ( $current_shipping_cost ) * $products_available_classes [$products_shippingclass->fields ['products_shippingclass']] ['qty'];
						} else {
							$products_available_classes [$products_shippingclass->fields ['products_shippingclass']] ['shipping_cost'] = floatval ( $current_shipping_cost );
						}
						$pass[$products_shippingclass['products_shippingclass']] = true;
						break;
					}
				}
			}
		}
		
		if (MODULE_SHIPPING_SHIPPINGCLASS_SUMMATE_CLASSES == 'True') {
			$shipping_method_parts = array ();
			$shipping_cost = 0;
			$shipping_title = MODULE_SHIPPING_SHIPPINGCLASS_TEXT_TITLE;
			foreach ( $products_available_classes as $k => $values ) {
				if ($this->get_free_amount( constant ( 'MODULE_SHIPPING_SHIPPINGCLASS_SHIPPINGFREEAMOUNT_' . $k ), $dest_country ) == 0 || $this->get_free_amount ( constant ( 'MODULE_SHIPPING_SHIPPINGCLASS_SHIPPINGFREEAMOUNT_' . $k ), $dest_country ) > $values ['order_amount']) {
					$shipping_cost += $values ['shipping_cost'];
					if (constant ( 'MODULE_SHIPPING_SHIPPINGCLASS_SUMMATE_' . $k ) == 'True') {
						$shipping_method_parts [] = $values ['qty'] . ' x ' . constant ( 'MODULE_SHIPPING_SHIPPINGCLASS_ZONENAME_' . $k );
					} else {
						$shipping_method_parts [] = '1 x ' . constant ( 'MODULE_SHIPPING_SHIPPINGCLASS_ZONENAME_' . $k );
					}
				}
			
			}
			
			$shipping_method = MODULE_SHIPPING_SHIPPINGCLASS_TEXT_WAY . ' ' . $dest_country_name . ' : ' . (($shipping_cost > 0) ? implode ( ', ', $shipping_method_parts ) : MODULE_SHIPPING_SHIPPINGCLASS_FREESHIPPING);
		
		} else {
			
			if ($this->get_free_amount ( constant ( 'MODULE_SHIPPING_SHIPPINGCLASS_SHIPPINGFREEAMOUNT_' . $highestpriority ), $dest_country ) > 0 && $this->get_free_amount ( constant ( 'MODULE_SHIPPING_SHIPPINGCLASS_SHIPPINGFREEAMOUNT_' . $highestpriority ), $dest_country ) <= $products_available_classes [$highestpriority] ['order_amount']) {
				$shipping_cost = 0;
				$shipping_title = MODULE_SHIPPING_SHIPPINGCLASS_FREESHIPPING;
				$shipping_method = MODULE_SHIPPING_SHIPPINGCLASS_TEXT_WAY . ' ' . $dest_country_name;
			} else {
				$shipping_title = constant ( 'MODULE_SHIPPING_SHIPPINGCLASS_ZONENAME_' . $highestpriority );
				if (constant ( 'MODULE_SHIPPING_SHIPPINGCLASS_SUMMATE_' . $highestpriority ) == 'True') {					
					$shipping_method = $products_available_classes [$highestpriority] ['qty'] . ' x ' . ' ' . MODULE_SHIPPING_SHIPPINGCLASS_TEXT_WAY . ' ' . $dest_country_name;
				} else {
					$shipping_method = MODULE_SHIPPING_SHIPPINGCLASS_TEXT_WAY . ' ' . $dest_country_name;
				}
				$shipping_cost = $products_available_classes [$highestpriority] ['shipping_cost'];
			}
		}
		
		if (!isset($pass[$highestpriority]) || $pass[$highestpriority] != true) {
			$shipping_method = MODULE_SHIPPING_SHIPPINGCLASS_UNDEFINED_RATE;
			$shipping_cost = 0;
		}
		$this->quotes = array ('id' => $this->code, 'module' => $shipping_title, 'methods' => array (array ('id' => $highestpriority, 'title' => $shipping_method, 'cost' => $shipping_cost ) ) );
		
		if ($this->tax_class > 0) {
			$this->quotes ['tax'] = hhg_get_tax_rate ( $this->tax_class, $order->delivery ['country'] ['id'], $order->delivery ['zone_id'] );
		}
		
		if ($this->icon )
			$this->quotes ['icon'] = xtc_image ( $this->icon, $this->title );
		

		
		return $this->quotes;
	}
	
	function get_shipping_cost ($product_id, $dest_country = STORE_COUNTRY, $products_price = 0) {
		global $xtPrice;
		
		$products_shippingclass = hhg_db_query ( $sql = 'SELECT products_shippingclass, products_weight, products_shippingcosts, products_tax_class_id FROM ' . TABLE_PRODUCTS . ' WHERE products_id = ' . (int) $product_id . '\'' );

		if($products_shippingclass->fields['shipping_costs']) {
			$products_shippingclass->fields ['products_shippingclass'] = 3;
		}
		
		if($products_shippingclass->fields['shipping_costs']) {
			$dest_table = split("[:,]" , $products_shippingclass->fields['shipping_costs']);
		} else {
			$dest_table = split ( "[:;]", constant ( 'MODULE_SHIPPING_SHIPPINGCLASS_COST_' . $products_shippingclass->fields ['products_shippingclass'] ) );
		}
		
		for($i = 0; $i < sizeof ( $dest_table ); $i += 2) {
			if (strpos ( $dest_table [$i + 1], ',' ) !== false) {
				$dest_table_scale = split ( "[,|]", $dest_table [$i + 1] );
				for($ii = 0; $ii < sizeof ( $dest_table_scale ); $ii += 2) {
					if ($dest_table_scale [$ii] <= 1) {
						$shipping_cost = floatval ( $dest_table_scale [$ii + 1] );
					}
				}
			} else {
				$shipping_cost = $dest_table [$i + 1];
			}
			if ($dest_country == $dest_table [$i] || $dest_table [$i] == '00') {
				break;
			}
		}

		if ($this->get_free_amount ( constant ( 'MODULE_SHIPPING_SHIPPINGCLASS_SHIPPINGFREEAMOUNT_' . $products_shippingclass->fields ['products_shippingclass'] ), $dest_country ) > 0 && $this->get_free_amount ( constant ( 'MODULE_SHIPPING_SHIPPINGCLASS_SHIPPINGFREEAMOUNT_' . $products_shippingclass->fields ['products_shippingclass'] ), $dest_country ) <= $products_price) {
				$shipping_cost = 0;
		}
		
		return $shipping_cost;
	}
	
	function check() {
		if (!isset($this->_check)) {
			$check_query = hhg_db_query("select configuration_value from configuration_" . $this->store_id . "_modules where configuration_key = 'MODULE_SHIPPING_SHIPPINGCLASS_STATUS'");
			$this->_check = $check_query->RecordCount();
		}
		return $this->_check;
	}
	
	function install() {
		hhg_db_query ( "insert into configuration_" . $this->store_id . "_modules (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_SHIPPING_SHIPPINGCLASS_STATUS', 'True', '6', '0', 'xtc_cfg_select_option(array(\'True\', \'False\'), ', now())" );
		hhg_db_query ( "insert into configuration_" . $this->store_id . "_modules (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('MODULE_SHIPPING_SHIPPINGCLASS_ALLOWED', '', '6', '0', now())" );
		hhg_db_query ( "insert into configuration_" . $this->store_id . "_modules (configuration_key, configuration_value, configuration_group_id, sort_order, use_function, set_function, date_added) values ('MODULE_SHIPPING_SHIPPINGCLASS_TAX_CLASS', '1', '6', '0', 'xtc_get_tax_class_title', 'xtc_cfg_pull_down_tax_classes(', now())" );
		hhg_db_query ( "insert into configuration_" . $this->store_id . "_modules (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('MODULE_SHIPPING_SHIPPINGCLASS_SORT_ORDER', '1', '6', '0', now())" );
		hhg_db_query ( "insert into configuration_" . $this->store_id . "_modules (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_SHIPPING_SHIPPINGCLASS_SUMMATE_CLASSES', 'False', '6', '0', 'xtc_cfg_select_option(array(\'True\', \'False\'), ', now())" );
		hhg_db_query ( "ALTER TABLE configuration_" . $this->store_id . "_modules CHANGE `configuration_value` `configuration_value` TEXT");
		for($i = 1; $i <= $this->num_classes; $i ++) {
			$default_name = $i;
			$default_costs = '';
			$default_priority = 0;
			$default_summate = 'False';
			if ($i == 1) {
				$default_name = 'Buchversand';
				$default_costs = 'DE:1,2.1008|2,5.8403;BE:9.2436;NL:9.2436;LU:9.2436;BG:92.4369;DK:10.9243;EE:21.0084;LV:21.0084;LT:21.0084;FI:32.7731;FR:15.1260;GR:79.8319;GB:19.3277;IE:31.9327;IT:16.8067;MT:142.8571;NO:40.3361;AT:10.0840;PL:15.9663;PT:24.3697;RO:24.3697;SE:18.4873;CH:36.0000;SK:23.5294;SI:15.1260;ES:23.5294;CZ:13.4453;TR:91.5966;HU:17.6470;CY:91,5966';
				$default_priority = 3;
			}
			if ($i == 2) {
				$default_name = 'General Logistics Systems';
				$default_costs = 'DE:5.8403;BE:9.2436;NL:9.2436;LU:9.2436;BG:92.4369;DK:10.9243;EE:21.0084;LV:21.0084;LT:21.0084;FI:32.7731;FR:15.1260;GR:79.8319;GB:19.3277;IE:31.9327;IT:16.8067;MT:142.8571;NO:40.3361;AT:10.0840;PL:15.9663;PT:24.3697;RO:24.3697;SE:18.4873;CH:36.0000;SK:23.5294;SI:15.1260;ES:23.5294;CZ:13.4453;TR:91.5966;HU:17.6470;CY:91,5966';
				$default_priority = 2;
			}
			if ($i == 3) {
				$default_name = 'Spedition';
				$default_costs = 'DE:42.0168';
				$default_priority = 1;
				$default_summate = 'True';
			}
				if ($i == 4) {
				$default_name = 'Versandkostenfrei';
				$default_costs = '00:0';
				$default_priority = 999;
			}
			hhg_db_query ( "insert into configuration_" . $this->store_id . "_modules (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('MODULE_SHIPPING_SHIPPINGCLASS_ZONENAME_" . $i . "', '" . $default_name . "', '6', '0', now())" );
			hhg_db_query ( "insert into configuration_" . $this->store_id . "_modules (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('MODULE_SHIPPING_SHIPPINGCLASS_COST_" . $i . "', '" . $default_costs . "', '6', '0', now())" );
			hhg_db_query ( "insert into configuration_" . $this->store_id . "_modules (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('MODULE_SHIPPING_SHIPPINGCLASS_SHIPPINGFREEAMOUNT_" . $i . "', '0', '6', '0', now())" );
			hhg_db_query ( "insert into configuration_" . $this->store_id . "_modules (configuration_key, configuration_value, configuration_group_id, sort_order, date_added) values ('MODULE_SHIPPING_SHIPPINGCLASS_PRIORITY_" . $i . "', $default_priority, '6', '0', now())" );
			hhg_db_query ( "insert into configuration_" . $this->store_id . "_modules (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_SHIPPING_SHIPPINGCLASS_SUMMATE_" . $i . "', '".$default_summate."', '6', '0', 'xtc_cfg_select_option(array(\'True\', \'False\'), ', now())" );
		
		}
		hhg_db_query ( "insert into " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added) VALUES ('MODULE_SHIPPING_SHIPPINGCLASS_SUMMATE_CLASSES', 'False', '6', '0', 'xtc_cfg_select_option(array(\'True\', \'False\'), ', now())" );
		hhg_db_query ( 'ALTER TABLE ' . TABLE_PRODUCTS . ' ADD products_shippingclass INT (11), products_shippingcosts TEXT NOT NULL' );

	}
	
	function remove() {
		hhg_db_query ( "delete from configuration_" . $this->store_id . "_modules where configuration_key in ('" . implode ( "', '", $this->keys () ) . "')" );
		hhg_db_query ( 'ALTER TABLE ' . TABLE_PRODUCTS . ' DROP products_shippingclass, products_shippingcosts' );
	}
	
	function keys() {
		$keys = array ('MODULE_SHIPPING_SHIPPINGCLASS_STATUS', 'MODULE_SHIPPING_SHIPPINGCLASS_ALLOWED', 'MODULE_SHIPPING_SHIPPINGCLASS_TAX_CLASS', 'MODULE_SHIPPING_SHIPPINGCLASS_SORT_ORDER', 'MODULE_SHIPPING_SHIPPINGCLASS_SUMMATE_CLASSES' );
		
		for($i = 1; $i <= $this->num_classes; $i ++) {
			$keys [] = 'MODULE_SHIPPING_SHIPPINGCLASS_ZONENAME_' . $i;
			$keys [] = 'MODULE_SHIPPING_SHIPPINGCLASS_COST_' . $i;
			$keys [] = 'MODULE_SHIPPING_SHIPPINGCLASS_SHIPPINGFREEAMOUNT_' . $i;
			$keys [] = 'MODULE_SHIPPING_SHIPPINGCLASS_PRIORITY_' . $i;
			$keys [] = 'MODULE_SHIPPING_SHIPPINGCLASS_SUMMATE_' . $i;
		}
		
		return $keys;
	}
	
	function get_free_amount($freeamount, $dest_country) {
		
		if(is_numeric($freeamount)) {
			return floatval($freeamount);
		}
		
		if($freeamount == '') {
			return 0;
		}
		$dest_table = split ( "[:;]", $freeamount );		
		for($i = 0; $i < sizeof ( $dest_table ); $i += 2) {
			if ($dest_country == $dest_table [$i] || $dest_table [$i] == '00') {
						return floatval($dest_table [$i + 1]);
			}
		}
	}
}
?>