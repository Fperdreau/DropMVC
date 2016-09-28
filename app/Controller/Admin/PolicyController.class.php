<?php
/**
 * Created by PhpStorm.
 * User: Florian
 * Date: 19/03/2016
 * Time: 14:18
 */

namespace App\Controller\Admin;


use App\Views\PolicyView;
use Core\HTML\BootstrapForm;

/**
 * Class PolicyController
 * @package App\Controller\Admin
 */
class PolicyController extends \App\Controller\PolicyController {

    /**
     * @var PolicyView
     */
    protected $view;

    public function index() {
        $policies = '';
        foreach ($this->Policy->all() as $key=>$policy) {
            $policies .= "<option value='{$policy->policyid}'>".ucfirst($policy->name)."</option>";
        }
        $options = "
            <option value='' disabled selected>Add/Edit a policy</option>
            <option value='add'>Add a new policy</option>
            {$policies}
        ";
        $this->render('admin.policy.index', compact('options'));
    }

}