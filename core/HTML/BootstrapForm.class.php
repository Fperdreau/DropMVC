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
 * Class BootstrapForm
 * @package Core\HTML
 */
class BootstrapForm extends Form{

    /**
     * @param $html string Code HTML à entourer
     * @return string
     */
    protected function surround($html){
        return "<fieldset class=\"form-group\">{$html}</fieldset>";
    }

    /**
     * @param $name string
     * @param $label
     * @param array $options
     * @param string|null $param
     * @return string
     */
    public function input($name, $label, $options = [], $param=null){
        $type = isset($options['type']) ? $options['type'] : 'text';
        $placeholder = isset($options['placeholder']) ? $options['placeholder'] : '';
        $class = isset($options['class']) ? $options['class'] : '';
        $id = isset($options['id']) ? "id='".$options['id']."'" : '';
        $label = "<label>{$label}</label>";
        if($type === 'textarea'){
            $input = "<textarea name='{$name}' class='form-control $class' {$id}
            {$placeholder} {$param}>{$this->getValue($name)}</textarea>";
        } else{
            $value = ($type !== 'password') ? $this->getValue($name):''; // Do not show passwords
            $input = '<input type="' . $type . '" name="' . $name . '"
            value="' . $value . '" class="form-control ' . $class . '" ' . $id. '
                        placeholder="' . $placeholder . '" ' . $param . '>';
        }
        return $this->surround($label . $input);
    }

    public function select($name, $label, $options, $param=null, $class=null){
        $label = '<label for="' . $name .'">' . $label . '</label>';
        $input = '<select class="form-control ' . $class .'" name="' . $name . '" ' . $param . '>';
        foreach($options as $k => $v){
            $attributes = '';
            if($v == $this->getValue($name)){
                $attributes = 'selected';
            }
            $input .= '<option value="' . $v . '" ' . $attributes . '>' . $k . '</option>';
        }
        $input .= '</select>';
        return $this->surround($label . $input);
    }

    public function checkbox($name, $label, $options, $param=null, $class=null) {
        $label = "<label>$label</label>";
        $input = "";
        foreach ($options as $l => $v) {
            $input .= "<input type='checkbox' name='{$name}' value='{$v}'
            class='form-control $class' {$param}> {$l}";
        }
        return $this->surround($label.$input);
    }

    /**
     * Display submit button
     * @param null $class
     * @param string $text : submit button content
     * @param null $param: extra parameters
     * @return string
     */
    public function submit($class=null, $text='Submit', $param=null){
        return $this->surround("<button type='submit' class='btn btn-primary $class'
         {$param}>{$text}</button>");
    }
}
