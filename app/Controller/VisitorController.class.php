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

namespace App\Controller;


use App\Model\VisitorModel;

/**
 * Class VisitorController
 * @package App\Controller
 */
class VisitorController extends AppController {

    /**
     * @var VisitorModel
     */
    protected $Visitor;

    /**
     * VisitorController constructor.
     */
    function __construct() {
        parent::__construct();
        $this->loadModel('Visitor');
    }

    /**
     * Get number of clicks
     * @param $id
     * @param $type
     * @return bool
     */
    public function get($id, $type) {
        return $this->Visitor->all(array('name'=>$id,'type'=>$type));
    }

    /**
     * Register clicks
     * @param array $data
     * @return bool
     */
    public function add(array $data=null) {
        if (is_null($data)) $data = $_POST;
        $data['ip'] = self::getIp();
        self::getGeoIp($data['ip']);
        $data['city'] = (isset($_SESSION['geoip']['city'])) ? $_SESSION['geoip']['city'] : 'NA';
        $data['country'] = (isset($_SESSION['geoip']['country'])) ? $_SESSION['geoip']['country'] : 'NA';
        $data['date'] = date('Y-m-d H:i:s');
        return $this->Visitor->add($data);
    }

    /**
     * Get IP
     * @return string
     */
    public static function getIp() {
        return strval($_SERVER['REMOTE_ADDR']);
    }

    /**
     * Get Location from IP
     * @param $ip
     * @return mixed
     */
    public static function getGeoIp($ip) {
        if (!isset($_SESSION['geoip'])) {
            try {
                $geo = @json_decode(file_get_contents("http://ipinfo.io/{$ip}"));
                $_SESSION['geoip']['city'] = $geo->city;
                $_SESSION['geoip']['country'] = $geo->country;
            } catch (\Exception $e) {
                $_SESSION['geoip']['city'] = 'NA';
                $_SESSION['geoip']['country'] = 'NA';
            }
        }
        return $_SESSION['geoip'];
    }

    /**
     * Get number of visit and download
     * @param $name
     * @return mixed
     */
    public static function getClicks($name) {
        $visit = new self();
        $count['view'] = count($visit->get($name,'view'));
        $count['dl'] = count($visit->get($name,'dl'));
        return $count;
    }

    /**
     * Registers visit
     * @param $id
     * @param string $type
     */
    public static function addClick($id, $type='view') {
        $Visitor = new self();
        $Visitor->add(array('name'=>$id, 'type'=>$type));
    }
}