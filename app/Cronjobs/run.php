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

namespace App\Cronjobs;

require '../App.class.php';
use App\App;
use App\Controller\TasksController;

// Boot application
App::boot(false);

require_once(PATH_TO_APP . 'i18n'. DS . 'lib'. DS .'gettext'. DS .'gettext.inc');

$Tasks = new TasksController();

// Run scheduled tasks
$logs = $Tasks->execute();

// Send logs to admins
if ($logs !== false) {
    if ($Tasks->getSettings('Tasks', 'notify_admin')) {
        $Tasks->notify_admin($logs);
    }
}
