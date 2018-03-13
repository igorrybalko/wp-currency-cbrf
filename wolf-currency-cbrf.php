<?php
/*
Plugin Name: Currency CBRF
Plugin URI: http://wolfweb.com.ua
Description: Shows exchange rates USD and EUR
Version: 1.0.1
Author: Rybalko Igor
Author URI: http://wolfweb.com.ua
*/

/*  Copyright 2017  Rybalko Igor  (email : igorrybalko2009@gmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

class WolfCurrencyCbrf extends WP_Widget
{
    public function __construct() {
        parent::__construct("widget_currency_cbrf", "Currency CBRF",
            array("description" => "Shows exchange rates USD and EUR"));
    }

    public function form($instance) {

        $title = "";
        $cacheTime = 240;
        $tpl = 'default';

        foreach($instance as $k => $v){
            if($v){
                ${$k} = $v;
            }
        }

        $titleFieldId = $this->get_field_id("title");
        $titleFieldName = $this->get_field_name("title");
        echo '<p><label for="' . $titleFieldId . '">Title:</label><br>';
        echo '<input id="' . $titleFieldId . '" type="text" name="'
            . $titleFieldName . '" value="' . $title . '"></p>';


        $cacheTimeFieldId = $this->get_field_id("cacheTime");
        $cacheTimeFieldName = $this->get_field_name("cacheTime");
        echo '<p><label for="' . $cacheTimeFieldId . '">Cache time:</label><br>';
        echo '<input id="' . $cacheTimeFieldId . '" type="number" name="' .
            $cacheTimeFieldName . '" value="' . $cacheTime . '"></p>';

        $listFiles = scandir(__DIR__ . '/tpl');
        $listTpls = array_map(function($el){
            preg_match('/^tpl_(.*)\.php$/', $el, $matches);
            if(isset($matches[1]))
                return $matches[1];
        }, $listFiles);
        $listTpls = array_filter($listTpls, function($el){
            return $el;
        });

        $tplFieldId = $this->get_field_id("tpl");
        $tplFieldName = $this->get_field_name("tpl");
        echo '<p><label for="' . $tplFieldId . '">Template:</label><br>';
        echo '<select name="' . $tplFieldName . '">';
        foreach($listTpls as $tplName){ ?>
            <option value="<?php echo $tplName ?>" <?php selected( $tpl, $tplName )?>><?php echo $tplName ?></option>
        <?php }

        echo '</select></p>';
    }

    public function update($newInstance, $oldInstance) {
        $values = array();
        $values["title"] = htmlentities($newInstance["title"]);
        $values["cacheTime"] = abs(intval($newInstance["cacheTime"]));
        $values["tpl"] = htmlentities($newInstance["tpl"]);
        return $values;
    }

    public function widget($args, $instance) {
        $title = $instance["title"];
        $cacheTime = $instance["cacheTime"];
        $tpl = $instance["tpl"];

        require_once( __DIR__ . '/helper.php');

        $ratesCbrf = CurrencyCbrfHelper::getInstance();
		$data = $ratesCbrf->getRates($cacheTime);

        require_once( __DIR__ . '/tpl/tpl_' . $tpl . '.php');
    }

}
add_action("widgets_init", function () {
    register_widget("WolfCurrencyCbrf");
});