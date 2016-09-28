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

namespace Core\Language;


use App\App;
use Core\Database\Database;
use Core\Models\BaseModel;


/**
 * Class Translation
 * @package Core\Language
 */
class Translation {

    public static $default_lang = 'en_US';
    private $lang;
    private $encoding = "UTF-8";
    private $domain = 'trad';

    /**
     * Translation constructor.
     * @param null $encoding: Encoding format (e.g. UTF-8)
     * @param null $domain
     */
    function __construct($encoding=null, $domain=null) {
        if (!is_null($encoding)) {
            $this->encoding = $encoding;
        }
        if (!is_null($domain)) {
            $this->domain = $domain;
        }

        $this->lang = isset($_SESSION['lang']) ? $_SESSION['lang'] : self::$default_lang;
        if (!isset($_SESSION['lang'])) {
            $_SESSION['lang'] = $this->lang;
        }
    }

    /**
     * Gets locale languages
     * @return array
     */
    public static function getLanguages() {
        $languages = array();
        foreach (scandir(PATH_TO_APP.'i18n/locale')  as $dir) {
            if (is_dir(PATH_TO_APP.'i18n/locale/'.$dir) && !in_array($dir, array('.', '..'))) {
                $lang = explode('_', $dir);
                $languages[$dir] = strtoupper($lang[0]);
            }
        }
        return $languages;
    }

    /**
     * Returns hreflang tags
     * @return string
     */
    public static function getHrefLang($cur_lang, $cur_uri) {
        $cur_uri = str_replace($_SESSION['BASE_URL'], '', $cur_uri);
        $content = "";
        foreach (self::getLanguages() as $code=>$name) {
            if ($code === $cur_lang) continue;
            $href = explode('_', $code);
            $url = URL_TO_APP . $code . '/' . $cur_uri;
            $content .= "<link rel='alternate' hreflang='{$href[0]}' href='{$url}' />";
        }
        return $content;
    }

    /**
     * Set language
     * @param $lang
     */
    public function set($lang) {
        $this->lang = $lang;
        $_SESSION['lang'] = $lang;
    }

    /**
     * Load gettext translation
     */
    public function load() {

        // define constants
        define('LOCALE_DIR', PATH_TO_APP .'i18n'.DS.'locale');
        define('DEFAULT_LOCALE', self::$default_lang);

        $supported_locales = array_keys(self::getLanguages());
        $locale = $this->lang;

        // gettext setup
        T_setlocale(LC_MESSAGES, $locale);

        // Set the text domain as 'messages'
        bindtextdomain($this->domain, LOCALE_DIR);

        // bind_textdomain_codeset is supported only in PHP 4.2.0+
        if (function_exists('bind_textdomain_codeset'))
            bind_textdomain_codeset($this->domain, $this->encoding);
        textdomain($this->domain);

        header("Content-type: text/html; charset=$this->encoding");
    }
}