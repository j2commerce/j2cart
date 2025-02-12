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

use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

$platform = J2Store::platform();
$platform->loadExtra('behavior.modal','a.modal');

?>
<div class="border mb-4 rounded-3 px-4 py-3">
    <div class="d-flex align-items-start">
        <div class="d-lg-flex align-items-center justify-content-between w-100">
            <div class="d-flex">
                <span class="fas fa-solid fa-map-marker-alt"></span>
                <div class="ms-4">
                    <h5 class="mb-0"><?php echo $this->item->first_name.' '.$this->item->last_name;?></h5>
                    <small class="d-block"><?php echo $this->item->company;?></small>
                    <small class="d-block"><?php echo $this->item->address_1;?>
						<?php echo $this->item->city.' '.$this->item->zip;?>
						<?php echo $this->item->zone_name;?>
						<?php echo $this->item->country_name;?>
	                    <?php echo $this->item->phone_1;?></small>
						<?php if(isset( $this->table_fields ) && $this->table_fields):?>
                            <?php $field_present = 0; ?>
							<?php foreach ($this->table_fields as $field):?>

								<?php if($field->field_core == 0):?>
									<?php $name_key = $field->field_namekey; ?>
									<?php if(isset( $this->item->$name_key ) && !empty($this->item->$name_key)):?>
										<?php $field_present += 1; ?>
                                    <small class="d-block">
                                        <b><?php echo $field->field_name.' :'; ?></b>
										<?php echo $this->item->$name_key;?>
                                    </small>
									<?php endif;?>
								<?php endif;?>
							<?php endforeach;?>
						<?php endif;?>
                </div>
            </div>
            <div class="mt-4 mt-lg-0">
	            <?php echo J2StorePopup::popupAdvanced("index.php?option=com_j2store&view=customer&task=editAddress&id=".$this->item->j2store_address_id."&tmpl=component",Text::_('J2STORE_EDIT'),array('class'=>'btn btn-outline-primary btn-sm','refresh'=>true,'id'=>'fancybox','width'=>700,'height'=>600));?>
                <a class="btn btn-danger btn-sm" href="<?php echo Route::_('index.php?option=com_j2store&view=customer&task=delete&id='.$this->item->j2store_address_id);?>">
		            <?php echo Text::_('J2STORE_DELETE');?>
						</a>
            </div>
        </div>
    </div>
</div>
