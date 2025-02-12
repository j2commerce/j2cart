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

require_once JPATH_ADMINISTRATOR.'/components/com_j2store/controllers/traits/list_view.php';

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\Filesystem\File;
use Joomla\Filesystem\Path;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\Toolbar;

class J2StoreControllerEmailtemplates extends F0FController
{
    use list_view;

    public function execute($task)
    {
        if (in_array($task, array('edit', 'add'))) {
            $task = 'add';
        }
        return parent::execute($task);
    }

	public function add()
	{
		$platform = J2Store::platform();
		$app = $platform->application();
		$vars = $this->getBaseVars();

		if (J2Store::isPro()) {
			$this->editToolBar();
			$bar = Toolbar::getInstance();
			$id = $app->input->getInt('id', 0);
			$bar->appendButton('Link', 'mail', Text::_('J2STORE_EMAILTEMPLATE_SEND_TEST_EMAIL_TO_YOURSELF'), 'index.php?option=com_j2store&view=emailtemplate&task=sendtest&id=' . $id);
		} else {
			$this->noToolbar();
		}

		$vars->primary_key = 'j2store_emailtemplate_id';
		$vars->id = $this->getPageId();
		$emailtemplateTable = J2Store::fof()->loadTable('Emailtemplate', 'J2StoreTable')->getClone();
		$emailtemplateTable->load($vars->id);
		$vars->item = $emailtemplateTable;
		$vars->field_sets = [];

		$orderStatusModel = J2Store::fof()->getModel('Orderstatuses', 'J2StoreModel');
		$defaultOrderStatusList = $orderStatusModel->enabled(1)->getList();
		$orderStatus = ['*' => Text::_('JALL')];
		foreach ($defaultOrderStatusList as $status) {
			$orderStatus[$status->j2store_orderstatus_id] = Text::_(strtoupper($status->orderstatus_name));
		}

		$paymentModel = J2Store::fof()->getModel('Payments', 'J2StoreModel');
		$defaultPaymentList = $paymentModel->enabled(1)->getList();
		$paymentList = ['*' => Text::_('JALL'), 'free' => Text::_('J2STORE_FREE_PAYMENT')];
		foreach ($defaultPaymentList as $payment) {
			$paymentList[$payment->element] = Text::_(strtoupper($payment->element));
		}

		$groupList = HTMLHelper::_('user.groups');
		$groupOptions = ['' => Text::_('JALL')];
		foreach ($groupList as $row) {
			$groupOptions[$row->value] = Text::_($row->text);
		}

		$languages = HTMLHelper::_('contentlanguage.existing');
		$languageList = ['*' => Text::_('JALL_LANGUAGE')];
		foreach ($languages as $lang) {
			$languageList[$lang->value] = Text::_(strtoupper($lang->title_native));
		}

		$vars->field_sets[] = [
			'id' => 'basic_options',
			'label' => 'J2STORE_BASIC_OPTIONS',
			'fields' => [
				'subject' => [
					'label' => 'J2STORE_EMAILTEMPLATE_SUBJECT_LABEL',
					'type' => 'text',
					'name' => 'subject',
					'value' => $emailtemplateTable->subject,
					'options' => ['class' => 'form-control', 'required' => true]
				],
				'body_source' => [
					'label' => 'J2STORE_EMAILTEMPLATE_BODY_SOURCE',
					'type' => 'list',
					'default' => 'html',
					'name' => 'body_source',
					'value' => $emailtemplateTable->body_source,
					'desc' => 'J2STORE_EMAILTEMPLATE_BODY_SOURCE_DESC',
					'options' => [
						'class' => 'form-select',
						'options' => [
							'html' => Text::_('J2STORE_HTML_INLINE_EDITOR'),
							'file' => Text::_('J2STORE_EMAILTEMPLATE_FILE_ADVANCED')
						]
					]
				],
				'receiver_type' => [
					'label' => 'J2STORE_EMAILTEMPLATE_RECEIVER',
					'type' => 'list',
					'default' => 'both',
					'name' => 'receiver_type',
					'desc' => 'J2STORE_EMAILTEMPLATE_RECEIVER_DESC',
					'value' => $emailtemplateTable->receiver_type,
					'options' => [
						'options' => [
							'*' => Text::_('J2STORE_EMAILTEMPLATE_RECEIVER_OPTION_BOTH'),
							'admin' => Text::_('J2STORE_EMAILTEMPLATE_RECEIVER_OPTION_ADMIN'),
							'customer' => Text::_('J2STORE_EMAILTEMPLATE_RECEIVER_OPTION_CUSTOMER')
						]
					]
				],
				'language' => [
					'label' => 'JFIELD_LANGUAGE_LABEL',
					'type' => 'list',
					'default' => 'en-GB',
					'name' => 'language',
					'desc' => 'J2STORE_EMAILTEMPLATE_LANGUAGE_DESC',
					'value' => $vars->item->language ?? '*',
					'options' => ['options' => $languageList]
				],
				'orderstatus_id' => [
					'label' => 'J2STORE_EMAILTEMPLATE_ORDERSTATUS',
					'type' => 'list',
					'name' => 'orderstatus_id',
					'value' => $vars->item->orderstatus_id ?? '*',
					'options' => ['translate' => false, 'options' => $orderStatus],
					'desc' => 'J2STORE_EMAILTEMPLATE_ORDERSTATUS_DESC'
				],
				'group_id' => [
					'label' => 'J2STORE_EMAILTEMPLATE_GROUPS',
					'type' => 'list',
					'name' => 'group_id',
					'default' => '*',
					'value' => $vars->item->group_id ?? '*',
					'options' => ['options' => $groupOptions],
					'desc' => 'J2STORE_EMAILTEMPLATE_GROUPS_DESC'
				],
				'paymentmethod' => [
					'label' => 'J2STORE_EMAILTEMPLATE_PAYMENTMETHODS',
					'type' => 'list',
					'name' => 'paymentmethod',
					'value' => $vars->item->paymentmethod ?? '*',
					'options' => ['options' => $paymentList],
					'desc' => 'J2STORE_EMAILTEMPLATE_PAYMENTMETHODS_DESC'
				],
				'enabled' => [
					'label' => 'J2STORE_ENABLED',
					'type' => 'enabled',
					'name' => 'enabled',
					'value' => $emailtemplateTable->enabled,
					'options' => ['class' => '']
				]
			]
		];

    $body_source = isset($emailtemplateTable->body_source) && !empty($emailtemplateTable->body_source) ? $emailtemplateTable->body_source: 'html';
		$source_hide = '';
		$body_source_file = '';
		$body_hide = '';
		if($body_source === 'html'){
			$source_hide = 'display:none;';
			$body_source_file = 'display:none;';
		}elseif ($body_source === 'file'){
            if(empty($emailtemplateTable->body_source_file)){
				$source_hide = 'display:none;';
			}
			$body_hide = 'display:none;';
		}

		$vars->field_sets[] = [
			'id' => 'advanced_information',
			'label' => 'J2STORE_ADVANCED_SETTINGS',
			'fields' => [
				'body_source_file' => [
					'label' => 'J2STRE_EMAILTEMPLATE_BODY_SOURCE_FILE',
					'type' => 'filelist',
					'name' => 'body_source_file',
					'value' => $emailtemplateTable->body_source_file,
                    'style' => $body_source_file,
					'options' => [
						'directory' => "administrator/components/com_j2store/views/emailtemplate/tpls",
						'filter' => "(.*?)\.(php)"
					]
				],
				'source' => [
					'label' => 'J2STORE_EMAILTEMPLATE_FIELD_SOURCE_LABEL',
					'type' => 'editor',
					'name' => 'source',
					'value' => $emailtemplateTable->body_source_file,
					'desc' => 'J2STORE_EMAILTEMPLATE_FIELD_SOURCE_DESC',
                    'style' => $source_hide,
					'options' => [
						'editor' => 'codemirror',
						'content' => 'from_file',
						'syntax' => 'php',
						'buttons' => false,
						'height' => '500px',
						'rows' => 20,
						'cols' => 80,
						'filter' => 'raw'
					]
				],
				'body' => [
					'label' => 'J2STORE_EMAILTEMPLATE_BODY_LABEL',
					'type' => 'editor',
					'name' => 'body',
					'value' => $emailtemplateTable->body,
                    'style' => $body_hide,
					'hiddenLabel' => 'true',
					'options' => ['class' => 'input-xlarge', 'buttons' => true]
				]
			]
		];

		echo $this->_getLayout('email_tab', $vars, 'edit');
	}
	public function browse()
	{
		$app = Factory::getApplication();
		$model = $this->getThisModel();

		// Define state variables using input sanitization
		$state = [];
		$state['paymentmethod'] = $app->input->getString('paymentmethod', '');
		$state['subject'] = $app->input->getString('subject', '');
		$state['filter_order'] = $app->input->getString('filter_order', 'j2store_emailtemplate_id');
		$state['filter_order_Dir'] = $app->input->getString('filter_order_Dir', 'ASC');

		// Set model state for each item
		foreach ($state as $key => $value) {
			$model->setState($key, $value);
		}

		$items = $model->getList();
		$vars = $this->getBaseVars();
		$vars->edit_view = 'emailtemplates';
		$vars->model = $model;
		$vars->items = $items;
		$vars->state = $model->getState();

		$this->addBrowseToolBar();

		// Define header structure
		$header = [
			'j2store_emailtemplate_id' => [
				'type' => 'rowselect',
				'tdwidth' => '20',
				'label' => 'J2STORE_EMAILTEMPLATE_ID'
			],
			'receiver_type' => [
				'type' => 'receivertypes',
				'sortable' => true,
				'label' => 'J2STORE_EMAILTEMPLATE_RECEIVER'
			],
			'language' => [
				'type' => 'text',
				'sortable' => true,
				'label' => 'JFIELD_LANGUAGE_LABEL'
			],
			'orderstatus_id' => [
				'type' => 'orderstatuslist',
				'sortable' => true,
				'label' => 'J2STORE_ORDERSTATUS_NAME'
			],
			'group_id' => [
				'type' => 'fieldsql',
				'query' => 'SELECT * FROM #__usergroups',
				'key_field' => 'id',
				'value_field' => 'title',
				'sortable' => true,
				'translate' => false,
				'label' => 'J2STORE_EMAILTEMPLATE_GROUPS'
			],
			'paymentmethod' => [
				'type' => 'fieldsearchable',
				'sortable' => true,
				'label' => 'J2STORE_EMAILTEMPLATE_PAYMENTMETHODS'
			],
			'subject' => [
				'type' => 'fieldsearchable',
				'sortable' => true,
				'show_link' => true,
				'url' => "index.php?option=com_j2store&view=emailtemplates&task=edit&id=[ITEM:ID]",
				'url_id' => 'j2store_emailtemplate_id',
				'label' => 'J2STORE_EMAILTEMPLATE_SUBJECT_LABEL'
			],
			'enabled' => [
				'type' => 'published',
				'sortable' => true,
				'label' => 'J2STORE_ENABLED'
			]
		];

		$this->setHeader($header, $vars);
		$vars->pagination = $model->getPagination();

		echo $this->_getLayout('default', $vars);
	}

	/**
	 * ACL check before allowing someone to browse
	 *
	 * @return  boolean  True to allow the method to run
	 */
	protected function onBeforeBrowse()
	{
		if (parent::onBeforeBrowse()) {
			$filename = 'default.php';
			$tplPath = JPATH_ADMINISTRATOR . '/components/com_j2store/views/emailtemplate/tpls';
			$defaultPhp = $tplPath . '/default.php';
			$defaultTpl = $tplPath . '/default.tpl';

			// Check if default.php exists; if not, copy from default.tpl if it exists
			if (!is_file(Path::clean($defaultPhp)) && is_file(Path::clean($defaultTpl))) {
				File::copy($defaultTpl, $defaultPhp);
			}

			return true;
		}

		return false;
	}

	function sendtest()
	{
		$app = Factory::getApplication();
		$platform = J2Store::platform();
		// Retrieve template ID from request
		$template_id = $app->input->getInt('id', 0);
		$msgType = 'warning';
		$msg = '';

		if ($template_id) {
			$model = $this->getModel('Emailtemplates');
			try {
				$email = $model->sendTestEmail($template_id);
				if (!$email) {
					$msg = Text::_('J2STORE_EMAILTEMPLATE_TEST_EMAIL_ERROR');
				} else {
					$msg = Text::sprintf('J2STORE_EMAILTEMPLATE_TEST_EMAIL_SENT', $email);
					$msgType = 'message';
				}
			} catch (Exception $e) {
				$msg = $e->getMessage();
			}

			$url = 'index.php?option=com_j2store&view=emailtemplate&id=' . $template_id;
		} else {
			$msg = Text::_('J2STORE_EMAILTEMPLATE_NO_EMAIL_TEMPLATE_FOUND');
			$url = 'index.php?option=com_j2store&view=emailtemplates';
		}
		$httpStatus = $msgType === 'message' ? 303 : 400;
		$app->setHeader('status', $httpStatus, true);
		$platform->redirect ( $url, $msg, $msgType );
	}
}
