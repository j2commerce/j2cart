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
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

$wa = Factory::getApplication()->getDocument()->getWebAssetManager();
$wa->useScript('table.columns');

$cols_count = 0;
?>
<div class="table-responsive-md">
    <table class="table itemList" id="j2storeList">
        <?php if (isset($vars->header) && !empty($vars->header)): ?>
            <thead>
            <tr>
                <?php foreach ($vars->header as $name => $field):?>
                    <?php if(isset($field['class']) && $field['class']):
                        $class = ' class="'.$field['class'].'"';
                    else:
                        $class = '';
                    endif;?>
                    <?php if (isset($field['type']) && $field['type'] == 'rowselect'): ?>
                        <td class="w-1"><input type="checkbox" name="checkall-toggle" value="" title="<?php echo Text::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)" /></td>
                        <?php $cols_count += 1; ?>
                    <?php elseif (isset($field['sortable']) && $field['sortable'] == 'true'): ?>
                        <th scope="col"<?php echo $class;?>>
                            <?php echo HTMLHelper::_('grid.sort', $field['label'], $name, $vars->state->filter_order_Dir, $vars->state->filter_order); ?>
                        </th>
                        <?php $cols_count += 1; ?>
                    <?php elseif (isset($field['label']) && $field['label']): ?>
                        <th scope="col"<?php echo $class;?>><?php echo Text::_($field['label']); ?></th>
                        <?php $cols_count += 1; ?>
                    <?php else: ?>
                        <th scope="col"<?php echo $class;?>><?php echo Text::_($name); ?></th>
                        <?php $cols_count += 1; ?>
                    <?php endif; ?>
                <?php endforeach; ?>
            </tr>
            </thead>

            <tbody>
            <?php if (isset($vars->items) && !empty($vars->items)): ?>
                <?php foreach ($vars->items as $i => $item): ?>
                    <tr>
                    <?php foreach ($vars->header as $name => $field): ?>
                        <?php if(isset($field['class']) && $field['class']):
                            $class = ' class="'.$field['class'].'"';
                        else:
                            $class = '';
                        endif;?>
                        <?php if (isset($field['type']) && $field['type'] == 'rowselect'): ?>
                            <td class="text-center">
                                <?php if(isset($item->orderstatus_core) && $item->orderstatus_core == 1):?>
                                <?php else:?>
                                    <?php echo HTMLHelper::_('grid.id', $i, $item->$name) ?>
                                <?php endif; ?>
                            </td>
                        <?php elseif (isset($field['type']) && in_array($field['type'], array('couponexpiretext', 'fieldsql','corefieldtypes','receivertypes','orderstatuslist','shipping_link'))): ?>
                            <td<?php echo $class;?>><?php echo J2Html::list_custom($field['type'], $name, $field, $item); ?></td>
                        <?php elseif (isset($field['show_link']) && $field['show_link'] == 'true' && isset($field['url_id']) && isset($field['url'])): ?>
                            <?php $url_id = $field['url_id']; ?>
                            <td<?php echo $class;?>>
                                <a href="<?php echo str_replace('[ITEM:ID]', $item->$url_id, $field['url']); ?>">
                                    <?php echo isset($field['translate']) && $field['translate'] ? Text::_($item->$name):$item->$name; ?>
                                </a>
                            </td>
                        <?php elseif (isset($field['type']) && $field['type'] == 'published'): ?>
                            <td<?php echo $class;?>>
                                <?php if (version_compare(JVERSION, '3.99.99', 'ge')) : ?>
                                    <?php
                                    $options = [
                                        'id' => 'publish-' . $i
                                    ];
                                    echo (new \Joomla\CMS\Button\PublishedButton)->render((int)$item->$name, $i, $options);
                                    ?>
                                <?php else: ?>
                                    <?php echo HTMLHelper::_('grid.published', $item->$name, $i); ?>
                                <?php endif; ?>
                            </td>
                        <?php else: ?>
                            <td<?php echo $class;?>><?php echo $item->$name; ?></td>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php endforeach; ?>
                </tr>
            <?php else: ?>
                <tr>
                    <td colspan="<?php echo $cols_count; ?>"><?php echo Text::_('J2STORE_NO_ITEMS_FOUND'); ?></td>
                </tr>
            <?php endif; ?>
            </tbody>
        <?php endif; ?>
    </table>
</div>
<?php echo $vars->extra_content ?? '';?>
<?php echo $vars->pagination->getListFooter(); ?>
