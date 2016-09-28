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
use App\Entity\PolicyEntity;
use Core\HTML\BootstrapForm;
use Core\Language\Translation;

/**
 * Class PolicyView
 * @package App\Views
 */
class PolicyView {

    /**
     * @param BootstrapForm $form
     * @param null|PolicyEntity $policy
     * @return mixed
     */
    public function form(BootstrapForm $form, $policy=null) {
        $url = (!is_null($policy)) ? URL_TO_APP . 'admin/policy/update/'.$policy->policyid:URL_TO_APP . 'admin/policy/update/';
        $formURL = (is_null($policy)) ? URL_TO_APP . 'policy/edit/' : URL_TO_APP . 'policy/edit/'.$policy->policyid;
        $del_btn = (!is_null($policy))?"<button type='submit'><a href='".URL_TO_APP.'policy/delete/'.$policy->policyid."' class='delete'>Delete</a></button>":"";
        $name = (is_null($policy)) ? null : $policy->name;
        $languages = array();
        foreach (Translation::getLanguages() as $langCode=>$langName) {
            $languages[$langName] = $langCode;
        }

        $formContent = $form->select('lang','Language', $languages, "required data-url='{$formURL}'", "select_lang");
        $formContent .= $form->input('name','Name', array('type'=>'text'), 'required');
        $textArea = $form->input('content','Message', array('type'=>'textarea','class'=>'tinymce','id'=>'content'), 'required');
        $submit = $form->submit('processform');
        $result = "
            <form method='post' action='$url' id='$name'>
                {$formContent}
                <div class='tinymce_container'>
                    {$textArea}
                </div>
                <div class='submit_btns'>
                    {$del_btn}
                    {$submit}
                </div>
            </form>
            ";

        return $result;
    }

    /**
     * Renders cookie policy consent popup
     * @return string
     */
    public static function consent_bar() {
        $url_to_policy = URL_TO_APP."policy/show/cookies";
        $url_ok = URL_TO_APP . 'policy/consent';
        return "
                <div id='cookie_consent_container'>
                    <div id='cookie_consent_text'>
                        "._('This site uses cookies. By continuing to browse the site, you are agreeing to our use of cookies.')."
                         <a href='{$url_to_policy}'>"._('More info')."</a>
                    </div>
                    <div>
                        <a href='{$url_ok}' id='cookie_consent_btn'>"._('OK')."</a>
                    </div>
                </div>";
    }

    /**
     * Renders list of policies (to be displayed in the footer)
     * @param array $policies
     * @return string
     */
    public static function policy_menu($policies) {
        $content = "";
        foreach ($policies as $key=>$policy) {
            $url = URL_TO_APP . 'policy/show/'.$policy->clean_url;
            $content .= "<li><a href='{$url}'>".ucfirst($policy->name)."</a></li>";
        }
        return "
        <div class='policies_container'>
            <div class='policies_title'>". _("terms and conditions")."</div>
            <div class='policies_list'>
            <ul>{$content}</ul>
            </div>
        </div>
        ";
    }

    /**
     * Renders policies main page
     * @param array $policies
     * @return string
     */
    public static function policies(array $policies) {
        $content = "";
        $summary = '';
        foreach ($policies as $key=>$policy) {
            $summary .= "<li><a href='#{$policy->name}'>".ucfirst($policy->name)."</a></li>";
            $content .= "
            <div class='policies_section' id='{$policy->clean_url}'>
                <div class='policies_section_name'><h2>{$policy->name}</h2><div class='policies_toggle_content toggle_on' id='on'></div></div>
                <div class='policies_content' id='{$policy->clean_url}'>".htmlspecialchars_decode($policy->content)."</div>
            </div>
            ";
        }
        return "
        <h1>". _("terms and conditions")."</h1>
        $summary
        $content
        ";
    }

}
