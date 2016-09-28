<?php

/**
 *
 * @author Florian Perdreau (fp@florianperdreau.fr)
 * @copyright Copyright (C) 2016 Florian Perdreau
 * @license <http://www.gnu.org/licenses/agpl-3.0.txt> GNU Affero General Public License v3
 *
 * This file is part of JR-Haarsieraad.
 *
 * JR-Haarsieraad is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * JR-Haarsieraad is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with JR-Haarsieraad.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace App\Views;
use App\Controller\AppController;
use Core;
use Core\Language\Translation;

class PagesView {

    /**
     * Show page settings
     * @param \Core\HTML\Form $form
     * @param \App\Entity\PagesEntity $page
     * @param int $total
     * @return string
     */
    public function show($form, $page, $total) {
        $formURL = URL_TO_APP . 'pages/edit/'.$page->name;
        $languages = array();
        foreach (Translation::getLanguages() as $langCode=>$langName) {
            $languages[$langName] = $langCode;
        }

        $selectLang = $form->select('lang','Language', $languages, "required data-url='{$formURL}'", "select_lang");

        $status = $form->select('status','Status', AppController::$accessLevels);
        $rank = $form->select('rank','Rank', range(0, $total, 1));
        $show = $form->select('show_menu','Show', array("no"=>0,"yes"=>1));
        $title  = $form->input('meta_title', 'Title', array('type'=>'text'));
        $description  = $form->input('meta_description', 'Description', array('type'=>'text'));
        $keywords  = $form->input('meta_keywords', 'Keywords', array('type'=>'text'));
        $submit = $form->submit('processform');
        $action = URL_TO_APP . 'pages/update/' .$page->name;
        $pageSettings = "
        <div class='trad_form_container'>
            <div class='plugDiv' id='page_$page->name'>
                <div class='plugLeft' style='width: 200px;'>
                    <div class='plugName'>$page->name</div>
                </div>

                <div class='plugSettings'>
                    <form action='$action' method='post'>
                        <input type='hidden' value='$page->name' name='name' />
                        <div class='page_settings'>
                            $status
                            $rank
                            $show
                        </div>
                        <div>
                            $selectLang
                            $title
                            $description
                            $keywords
                        </div>
                        <div class='submit_btns'>
                            $submit
                        </div>
                    </form>
                </div>
            </div>
        </div>

        ";
        return $pageSettings;
    }
}