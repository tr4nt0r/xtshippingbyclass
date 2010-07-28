<?php
/* -----------------------------------------------------------------------------------------
   $Id:$   

   XT-Commerce - community made shopping
   http://www.xt-commerce.com

   Copyright (c) 2003 XT-Commerce
   -----------------------------------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(zones.php,v 1.3 2002/04/17); www.oscommerce.com 
   (c) 2003	 nextcommerce (zones.php,v 1.4 2003/08/13); www.nextcommerce.org

   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/
   
// CUSTOMIZE THIS SETTING
define('NUMBER_OF_CLASSES',10);

define('MODULE_SHIPPING_SHIPPINGCLASS_TEXT_TITLE', 'Shipping');
define('MODULE_SHIPPING_SHIPPINGCLASS_TEXT_DESCRIPTION', 'Versandkosten Versandklassenbasierend');
define('MODULE_SHIPPING_SHIPPINGCLASS_TEXT_WAY', 'shipping to');
define('MODULE_SHIPPING_SHIPPINGCLASS_TEXT_UNITS', '');
define('MODULE_SHIPPING_SHIPPINGCLASS_INVALID_ZONE', 'No shipping available to the selected country!');
define('MODULE_SHIPPING_SHIPPINGCLASS_UNDEFINED_RATE', 'The shipping rate cannot be determined at this time. After submitting your order one of our employees will contanct you quickest possible and inform you about the shipping costs. You can then confirm or cancel your order.');
define('MODULE_SHIPPING_SHIPPINGCLASS_FREESHIPPING', 'Free Shipping');

define('MODULE_SHIPPING_SHIPPINGCLASS_STATUS_TITLE' , 'Versandkosten nach Zonen Methode aktivieren');
define('MODULE_SHIPPING_SHIPPINGCLASS_STATUS_DESC' , 'M&ouml;chten Sie Versandkosten nach Zonen anbieten?');
define('MODULE_SHIPPING_SHIPPINGCLASS_ALLOWED_TITLE' , 'Erlaubte Versandzonen');
define('MODULE_SHIPPING_SHIPPINGCLASS_ALLOWED_DESC' , 'Geben Sie <b>einzeln</b> die Zonen an, in welche ein Versand m&ouml;glich sein soll. (z.B. AT,DE (lassen Sie dieses Feld leer, wenn Sie alle Zonen erlauben wollen))');
define('MODULE_SHIPPING_SHIPPINGCLASS_TAX_CLASS_TITLE' , 'Steuerklasse');
define('MODULE_SHIPPING_SHIPPINGCLASS_TAX_CLASS_DESC' , 'Folgende Steuerklasse an Versandkosten anwenden');
define('MODULE_SHIPPING_SHIPPINGCLASS_SORT_ORDER_TITLE' , 'Sortierreihenfolge');
define('MODULE_SHIPPING_SHIPPINGCLASS_SORT_ORDER_DESC' , 'Reihenfolge der Anzeige');
define('MODULE_SHIPPING_SHIPPINGCLASS_SUMMATE_CLASSES_TITLE' , 'Alle Versandklassen addieren');
define('MODULE_SHIPPING_SHIPPINGCLASS_SUMMATE_CLASSES_DESC' , 'Die errechneten Versandkosten aller Versandklassen werden miteinander addiert.');

for ($ii=1;$ii<=NUMBER_OF_CLASSES;$ii++) {
define('MODULE_SHIPPING_SHIPPINGCLASS_ZONENAME_'.$ii.'_TITLE' , 'Versandklasse ' . @constant('MODULE_SHIPPING_SHIPPINGCLASS_ZONENAME_'.$ii) . ' Bezeichnung');
define('MODULE_SHIPPING_SHIPPINGCLASS_ZONENAME_'.$ii.'_DESC' , 'Bezeichnung der Versandklasse ' . @constant('MODULE_SHIPPING_SHIPPINGCLASS_ZONENAME_'.$ii) . ' &auml;ndern.');
define('MODULE_SHIPPING_SHIPPINGCLASS_COST_'.$ii.'_TITLE' , 'Versandklasse ' . @constant('MODULE_SHIPPING_SHIPPINGCLASS_ZONENAME_'.$ii) . ' Versandkosten');
define('MODULE_SHIPPING_SHIPPINGCLASS_COST_'.$ii.'_DESC' , 'Versandkosten nach Versandklasse ' . @constant('MODULE_SHIPPING_SHIPPINGCLASS_ZONENAME_'.$ii) . ' Bestimmungsorte. Beispiel: DE:2.95,AT:4.95,CH:14.95,00:19.95 . F&uuml;r alle &uuml;brigen L&auml;nder 00 als L&auml;ndercode verwenden.');
define('MODULE_SHIPPING_SHIPPINGCLASS_SHIPPINGFREEAMOUNT_'.$ii.'_TITLE' , 'Versandklasse ' . @constant('MODULE_SHIPPING_SHIPPINGCLASS_ZONENAME_'.$ii) . ' Versandkostenfrei');
define('MODULE_SHIPPING_SHIPPINGCLASS_SHIPPINGFREEAMOUNT_'.$ii.'_DESC' , 'Bestellwertgrenze f&uuml;r Versandkostenfreie Lieferung');
define('MODULE_SHIPPING_SHIPPINGCLASS_PRIORITY_'.$ii.'_TITLE' , 'Versandklasse ' . @constant('MODULE_SHIPPING_SHIPPINGCLASS_ZONENAME_'.$ii) . ' Priorit&auml;t');
define('MODULE_SHIPPING_SHIPPINGCLASS_PRIORITY_'.$ii.'_DESC' , 'Priorit&auml;t der Versandklasse vor anderen Versandklassen. (1 = h&ouml;chste Priorit&auml;t)');
define('MODULE_SHIPPING_SHIPPINGCLASS_SUMMATE_'.$ii.'_TITLE' , 'Versandklasse ' . @constant('MODULE_SHIPPING_SHIPPINGCLASS_ZONENAME_'.$ii) . ' Versandkosten addieren');
define('MODULE_SHIPPING_SHIPPINGCLASS_SUMMATE_'.$ii.'_DESC' , 'Die Versandkosten werden f&auml;r jeden Artikel in Versandklasse ' . @constant('MODULE_SHIPPING_SHIPPINGCLASS_ZONENAME_'.$ii) . ' berechnet, ansonsten nur einmal.');
}

?>
