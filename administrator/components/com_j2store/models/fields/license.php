<?php
/**
 * @package     Joomla.Component
 * @subpackage  J2Store
 *
 * @copyright Copyright (C) 2014-24 Ramesh Elamathi / J2Store.org
 * @copyright Copyright (C) 2025 J2Commerce, LLC. All rights reserved.
 * @license https://www.gnu.org/licenses/gpl-3.0.html GNU/GPLv3 or later
 * @website https://www.j2commerce.com
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

HTMLHelper::_('jquery.framework');

class JFormFieldLicense extends FormField
{
    protected $type = 'License';

    protected function getInput()
    {
        $app = J2Store::platform()->application();
        $license_value = is_array($this->value) && isset($this->value['license']) && !empty($this->value['license']) ? $this->value['license'] : '';
        $status = is_array($this->value) && isset($this->value['status']) && !empty($this->value['status']) ? $this->value['status'] : 'in_active';
        $expire = is_array($this->value) && isset($this->value['expire']) && !empty($this->value['expire']) ? $this->value['expire'] : '';
        $html = '<input id="plugin_license_key" style="width:60%;appearance: none;background-clip: padding-box;
        border: 1px solid var(--template-bg-dark-20);border-radius: .25rem;color: #212529;font-size: 1rem;font-weight: 400;
        line-height: 1.5;padding: .5rem 1rem;" name="' . $this->name . '[license]" value="' . $license_value . '">';
        $html .= '<input id="plugin_license_status" type="hidden" name="' . $this->name . '[status]" value="' . $status . '">';
        $html .= '<input id="plugin_license_expire" type="hidden" name="' . $this->name . '[expire]" value="' . $expire . '">';
        $extension_id = (int)$app->input->get('extension_id', 0);
        $view = (string)$app->input->get('view', '');
        $custom_task = (string)$app->input->get('customTask', '');
        $app_task = !empty($custom_task) ? $custom_task : $app->input->get('appTask', 'apply');

        $is_app_view = false ;
        if( $view === 'apps' && empty($extension_id) ){
            $extension_id = (int)$app->input->get('id', 0);
            $is_app_view =  true;
        }
        $is_report_view = false;
        if ($view === 'report' && empty($extension_id)) {
            $extension_id = (int)$app->input->get('id', 0);
            $is_report_view = true;
        }
        $is_module_view = false;
        if ($view === 'module' && empty($extension_id)) {
            $extension_ids = (int)$app->input->get('id', 0);
            $extension_name = $this->getModulename($extension_ids);
            if ($extension_name) {
                $extension_id = $this->getModuletId($extension_name);
            }
            $is_module_view = true;
        }

        $is_component_view = false ;
        if( $view === 'component' && empty($extension_id) ) {
            $extension_name = $app->input->get('component', '');
            $extension_id = $this->getComponentId($extension_name);
            $is_component_view = true;
        }

        $force = false;
        if ($status === 'active' && !empty($license_value)) {
            $now = time();
            $expire_time = strtotime($expire);
            if ($now > $expire_time) {
                $force = true;
            }
        }
        if (($status !== 'active' && !empty($license_value)) || $force) {
            $html .= "<a id='activate_license' onclick='activateLicense()' class='btn btn-success' >" . Text::_('J2STORE_ACTIVATE') . "</a>";
            $html .= '<script>
        function activateLicense(){
            let license = document.getElementById("plugin_license_key").value;
            let status = document.getElementById("plugin_license_status").value;
            let expire = document.getElementById("plugin_license_expire").value;
            let extension_id = "' . $extension_id . '";
            let is_app_view = "' . $is_app_view . '";
            let is_component_view = "' . $is_component_view . '";
            let is_module_view = "' . $is_module_view . '";
            let is_report_view = "' . $is_report_view . '";
             let app_task = "'.$app_task.'";

                    // Create XMLHttpRequest object
                    let xhr = new XMLHttpRequest();
                    xhr.open("POST", j2storeURL + "index.php?option=com_ajax&format=json&group=j2store&plugin=activateLicence", true);
                    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

                    // Handle the response
                    xhr.onload = function () {
                        if (xhr.status === 200) {
                            let data = JSON.parse(xhr.responseText);
                            if (data.success === false) {
                                let errorSpan = document.createElement("span");
                                errorSpan.className = "j2error";
                                errorSpan.innerHTML = data.message;
                                document.getElementById("plugin_license_key").after(errorSpan);
    			    }else {
                                document.getElementById("plugin_license_status").value = "active";
                                document.getElementById("plugin_license_expire").value = data.response.expires;

                                let successSpan = document.createElement("span");
                                successSpan.className = "j2success";
                                successSpan.innerHTML = data.message;
                                document.getElementById("plugin_license_key").after(successSpan);

                        if(is_app_view){
                             document.adminForm.task ="view";
                             document.getElementById("appTask").value = app_task;
                             Joomla.submitform("view");
                        }else if(is_component_view ){
                                    document.querySelector(\'input[name="task"]\').value = "component.apply";
                                    setTimeout(() => {
                                        document.getElementById("plugin_license_key").closest("form").submit();
                                    }, 1000);
                        }else if(is_module_view ){
                                    document.querySelector(\'input[name="task"]\').value = "module.apply";
                                    setTimeout(() => {
                                        document.getElementById("plugin_license_key").closest("form").submit();
                                    }, 1000);
                        }else if(is_report_view ){
                             document.adminForm.task ="view";
                             document.getElementById("reportTask").value = app_task;
                             Joomla.submitform("view");
                                } else {
                                    document.querySelector(\'input[name="task"]\').value = "plugin.apply";
                                    setTimeout(() => {
                                        document.getElementById("plugin_license_key").closest("form").submit();
                                    }, 1000);
                        }
                        }
    			    }
                    };
                    // Send the request
                    xhr.send("license=" + encodeURIComponent(license) + "&status=" + encodeURIComponent(status) + "&expire=" + encodeURIComponent(expire) + "&id=" + encodeURIComponent(extension_id));}
            </script>';
        } elseif ($status === 'active') {
            $html .= "<a id='de_activate_license' class='btn btn-danger' onclick='deActivateLicense()' >" . Text::_('J2STORE_DEACTIVATE') . "</a>";
            $html .= '<script>
        function deActivateLicense(){
                    let license = document.getElementById("plugin_license_key").value;
            let extension_id = "' . $extension_id . '";
            let is_app_view = "' . $is_app_view . '";
            let is_component_view = "' . $is_component_view . '";
            let is_module_view = "' . $is_module_view . '";
            let is_report_view = "' . $is_report_view . '";
            let app_task = "'.$app_task.'";

                    // Create an XMLHttpRequest object
                    let xhr = new XMLHttpRequest();
                    xhr.open("POST", j2storeURL + "index.php?option=com_ajax&format=json&group=j2store&plugin=deActivateLicence", true);
                    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

                    // Define what happens on successful response
                    xhr.onload = function () {
                        if (xhr.status === 200) {
                            let data = JSON.parse(xhr.responseText);
                            if (data.success === false) {
                                let errorSpan = document.createElement("span");
                                errorSpan.className = "j2error";
                                errorSpan.innerHTML = data.message;
                                document.getElementById("plugin_license_key").after(errorSpan);
    			    }else {
                                document.getElementById("plugin_license_status").value = "in_active";
                                document.getElementById("plugin_license_expire").value = "";

                                let successSpan = document.createElement("span");
                                successSpan.className = "j2success";
                                successSpan.innerHTML = data.message;
                                document.getElementById("plugin_license_key").after(successSpan);

                        if(is_app_view){
                             document.adminForm.task ="view";
			                 document.getElementById("appTask").value = app_task;
                             Joomla.submitform("view");
                        }else if(is_component_view ){
                                    document.querySelector(\'input[name="task"]\').value = "component.apply";
                            setTimeout(function (){
                                        document.getElementById("plugin_license_key").closest("form").submit();
                                    }, 1000);
                        }else if(is_module_view ){
                                    document.querySelector(\'input[name="task"]\').value = "module.apply";
                            setTimeout(function (){
                                        document.getElementById("plugin_license_key").closest("form").submit();
                                    }, 1000);
                        }else if(is_report_view ){
                             document.adminForm.task ="view";
                             document.getElementById("reportTask").value = app_task;
                             Joomla.submitform("view");
                        }
                        else{
                                    document.querySelector(\'input[name="task"]\').value = "plugin.apply";
                            setTimeout(function (){
                                        document.getElementById("plugin_license_key").closest("form").submit();
                                    }, 1000);
                        }
    			    }
                }
                    };

                    // Send the request
                    xhr.send("license=" + encodeURIComponent(license) + "&id=" + encodeURIComponent(extension_id));
        }
            </script>';
                }
        return $html;
    }

    function getComponentId($extension_name)
    {
        if (empty($extension_name)) {
            return;
        }
        $db = Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true);
        $query->select("extension_id")->from('#__extensions')
            ->where($db->qn('element') . ' = ' . $db->q($extension_name));
        $db->setQuery($query);
        $result = $db->loadObject();
        return $result->extension_id;
    }

    function getModulename($extension_ids)
    {
        if (empty($extension_ids)) {
            return;
        }
        $db = Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true);
        $query->select("module")->from('#__modules')
            ->where($db->qn('id') . ' = ' . $db->q($extension_ids));
        $db->setQuery($query);
        $result = $db->loadObject();
        return $result->module;
    }

    function getModuletId($extension_name)
    {
        if (empty($extension_name)) {
            return;
        }
        $db = Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true);
        $query->select("extension_id")->from('#__extensions')
            ->where($db->qn('element') . ' = ' . $db->q($extension_name));
        $db->setQuery($query);
        $result = $db->loadObject();
        return $result->extension_id;
    }
}
