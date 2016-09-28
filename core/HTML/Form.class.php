<?php
/**
 *
 * @author Florian Perdreau (fp@florianperdreau.fr)
 * @copyright Copyright (C) 2016 Florian Perdreau
 * @license <http://www.gnu.org/licenses/agpl-3.0.txt> GNU Affero General Public License v3
 *
 * This file is part of DropCMS.
 *
 * DropCMS is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * DropCMS is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with DropCMS.  If not, see <http://www.gnu.org/licenses/>.
 */
namespace Core\HTML;

/**
 * Class Form
 * Permet de générer un formulaire rapidement et simplement
 */
class Form{

    /**
     * @var array Données utilisées par le formulaire
     */
    private $data;

    /**
     * @var string Tag utilisé pour entourer les champs
     */
    public $surround = 'p';

    /**
     * @param array $data Data used by the form
     * @param null $surround Surround tag (e.g.: 'p')
     */
    public function __construct($data = array(), $surround=null){
        $this->data = $data;
        if (!is_null($surround)) {
            $this->surround = $surround;
        }
    }

    /**
     * @param $html string Code HTML à entourer
     * @return string
     */
    protected function surround($html){
        return "<{$this->surround} class='form-group'>{$html}</{$this->surround}>";
    }

    public function build() {
        $form = '';
        foreach ($this->data as $variable=>$obj) {
            if (!empty($obj->options)) {
                $form .= $this->select($variable, $obj->description, $obj->options);
            } else {
                $form .= $this->input($variable, $obj->description);

            }
        }
        return $form;
    }

    /**
     * @param $index string Index de la valeur à récupérer
     * @return string
     */
    public function getValue($index){
        if(is_object($this->data)){
            if (property_exists($this->data, $index)) {
                return $this->data->$index;
            } else {
                return null;
            }
        }
        return isset($this->data[$index]) ? $this->data[$index] : null;
    }

    /**
     * @param $name string
     * @param $label
     * @param array $options
     * @param null $param
     * @return string
     */
    public function input($name, $label, $options = [], $param=null){
        $type = isset($options['type']) ? $options['type'] : 'text';
        $class = isset($options['class']) ? "class='{$options['class']}'":'';
        $label = '<label>' . $label . '</label>';
        if($type === 'textarea'){
            $input = "<textarea name='{$name}' {$class} {$param}>{$this->getValue($name)}</textarea>";
        } else{
            $input = "<input type='{$type}' name='{$name}' value='{$this->getValue($name)}' {$class} {$param}/>";
        }
        return $this->surround($label . $input);
    }

    public function select($name, $label, $options, $class=null, $param=null){
        $class = !is_null($class) ? "class=$class":"";
        $label = '<label>' . $label . '</label>';
        $input = "<select {$class} name='{$name}' {$param}>";
        foreach($options as $k => $v){
            $attributes = '';
            if($v == $this->getValue($name)){
                $attributes = 'selected';
            }
            $input .= "<option value='$v' $attributes>$k</option>";
        }
        $input .= '</select>';
        return $this->surround($label . $input);
    }

    /**
     * @param null $class
     * @param string $text Button text
     * @param null|string $param extra parameters
     * @return string
     */
    public function submit($class=null, $text='Submit', $param=null){
        return $this->surround("<button type='submit' class='btn btn_red $class' {$param}>{$text}</button>");
    }

}
