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

namespace App\Entity;


use Core\Entities\BaseEntity;

/**
 * Class MailEntity
 * @package App\Entity
 */
class MailEntity extends BaseEntity {

    /**
     * @var int $id: row id
     */
    public $id;

    /**
     * @var string $date: Email creation date
     */
    public $date;

    /**
     * @var string $mail_id: unique id
     */
    public $mail_id;

    /**
     * @var int $status: email's status (1: sent, 0: not sent)
     */
    public $status;

    /**
     * @var string $recipients: recipients list (comma-separated)
     */
    public $recipients;

    /**
     * @var string $attachments: list of files id (comma-separated)
     */
    public $attachments;

    /**
     * @var string $content: email's content
     */
    public $content;

    /**
     * @var string $subject: email's subject
     */
    public $subject;

}