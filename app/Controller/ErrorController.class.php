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

/**
 * Class ErrorController
 * @package App\Controller
 */
class ErrorController extends AppController {

    /**
     * ErrorController constructor.
     * @param string $error
     */
    function __construct($error)
    {
        parent::__construct();
        switch ($error) {
            case "404":
                header('HTTP/1.0 404 Not Found');
                break;
            case "403":
                header('HTTP/1.0 403 Forbidden');
                break;
        }
        $this->render('error.'.$error);
    }

}