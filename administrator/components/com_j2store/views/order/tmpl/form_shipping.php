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
use Joomla\CMS\Uri\Uri;

$currency = J2Store::currency();
?>
<?php if(isset($this->shipping->ordershipping_type) && !empty($this->shipping->ordershipping_name)): ?>
<?php
    $image_exists = false;
    $imageExtensions = ['jpg', 'png', 'webp'];
    $imagePath = '';

    foreach ($imageExtensions as $extension) {
        $path = JPATH_SITE . '/media/plg_j2store_'.$this->shipping->ordershipping_type.'/images/' . $this->shipping->ordershipping_type . '.' . $extension;
        if (file_exists($path)) {
            $image_exists = true;
            $imagePath = Uri::root(true) . '/media/plg_j2store_' . $this->shipping->ordershipping_type . '/images/' . $this->shipping->ordershipping_type . '.' . $extension;
            break;
        }
    }
?>
    <div class="border rounded-3 px-4 py-3 shipping-information">
        <div class="d-flex align-items-start min-ht-50">
            <div class="d-lg-flex align-items-center justify-content-between w-100">
                <div class="d-flex align-items-center">
	                <?php if($image_exists):?>
                        <img src="<?php echo $imagePath; ?>" class="img-fluid me-2 order-thumb-image" alt="<?php echo Text::_($this->shipping->ordershipping_code); ?>"/>
                    <?php endif;?>
                    <div>
                        <h6 class="mb-0"><?php echo $this->shipping->ordershipping_name; ?><span class="d-inline-block fw-medium text-success fs-6 ms-2"><?php echo $currency->format($this->shipping->ordershipping_price); ?></h6>
                        <small class="d-block"><?php echo Text::_('J2STORE_SHIPPING_TRACKING_ID'); ?><span class="d-inline-block fw-medium fs-6 ms-2"><?php echo $this->shipping->ordershipping_tracking_id; ?></span></small>
                    </div>
                </div>
                <div class="mt-4 mt-lg-0">
                    <a href="#" class="btn btn-outline-primary btn-sm" data-bs-toggle="collapse" data-bs-target="#collapseShippingMethod" aria-expanded="false" aria-controls="collapseShippingMethod">
						<?php echo Text::_('J2STORE_SHIPPING_TRACKING_UPDATE');?>
                    </a>
                </div>
            </div>
        </div>
        <div id="collapseShippingMethod" class="collapse">
            <div class="pt-4">
                <textarea class="form-control" aria-invalid="false" name="ordershipping_tracking_id"><?php echo $this->shipping->ordershipping_tracking_id; ?></textarea>
                <div class="text-end mt-2">
                    <button class="btn btn-sm btn-primary" type="submit" onclick="jQuery('#task').attr('value','saveTrackingId');"><?php echo Text::_('J2STORE_ORDER_STATUS_SAVE'); ?></button>
                </div>
            </div>
        </div>
    </div>
<?php endif;?>
