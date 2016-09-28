<?php
/**
 *
 * @author Florian Perdreau (fp@florianperdreau.fr)
 * @copyright Copyright (C) 2016 Florian Perdreau
 * @license <http://www.gnu.org/licenses/agpl-3.0.txt> GNU Affero General Public License v3
 *
 * This file is part of DropMVC.
 *
 * DropMVC is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * DropMVC is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with DropMVC.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace App\Views;


/**
 * Class MenuView
 * @package App\Views
 */
class MenuView {

    /**
     * Render menu
     * @param $sections
     * @param null $main
     * @return string
     */
    public function menu($sections, $main=null) {
        $menu = '';
        foreach ($sections as $section) {
            $split = explode('/', $section->name);
            $pageName = (!empty($section->meta_title)) ? ucfirst($section->meta_title) : ucfirst(end($split));
            $url = (property_exists($section, 'clean_url')) ? $section->clean_url:$section->name;
            $url = $_SESSION['BASE_URL'] . $url;
            $subMenu_trigger = (!empty($section->submenu)) ? "submenu_trigger":"";
            $menu .= "<li id='$section->name'><a href='$url' class='menu_section {$subMenu_trigger}'>$pageName</a></li>";
        }
        $result = "
        <nav>
            <ul>
                $menu
            </ul>
        </nav>";
        return $result;
    }

    /**
     * @param array $sections
     * @param string $current
     * @return array
     */
    public function subMenu($sections, $current) {
        $menu = array();
        foreach ($sections as $main) {
            if (!empty($main->submenu)) {
                $hide = ($main->name !== $current) ? "style='display: none'":'';
                $submenu = $this->menu($main->submenu, $main->name.'/');
                $menu[$main->name] = "
                    <div class='submenu' id='{$main->name}' {$hide}>{$submenu}</div>
                ";
            }
        }
        return $menu;
    }

    /**
     * Renders language menu in the Header
     * @param array $languages
     * @return string
     */
    public static function languagesMenu(array $languages) {
        $content = "";
        $selected = '';
        foreach ($languages as $language=>$langName) {
            $langURL = $_SESSION['BASE_URL'] . 'language/set/'.$language;
            if ($language == $_SESSION['lang']) {
                $selected = "<li id='selected'>{$langName}</li>";
            }
            $content .= "<li><a href='{$langURL}' class='lang_trigger' rel='nofollow'>{$langName}</a></li>";
        }
        return "
        <div id='language_label'>"._('Language')."</div>
        <div class='language_container'>
            {$selected}
            <div id='option_list' style='display: none'>
                {$content}
            </div>
        </div>
        ";

    }

}