<?php
/**
 * @package J2Store
 * @copyright   Copyright (c) 2014-24 Ramesh Elamathi / J2Store.org
 * @copyright   Copyright (c) 2024-26 J2Commerce. All rights reserved.
 * @license GNU GPL v3 or later
 *
 * Default template for J2Store content plugin product display
 * This file can be overridden by copying it to:
 * /templates/YOUR_TEMPLATE/html/plg_content_j2store/default.php
 */
defined('_JEXEC') or die('Restricted access');

/**
 * Available variables:
 * @var object $product        - The J2Store product object
 * @var string $product_html   - The generated product block HTML
 * @var string $image_html     - The generated product image HTML
 * @var object $article        - The Joomla article object
 * @var string $context        - The context (com_content.category, com_content.article, etc.)
 * @var object $params         - The plugin parameters
 * @var string $position       - The position (top, bottom, etc.)
 * @var int    $page           - The page number
 */

// Output the complete product display
?>
<div class="j2store-product-wrapper j2store-product-<?php echo $product->j2store_product_id; ?> j2store-position-<?php echo $position; ?>">
	<?php if ($image_html): ?>
	<div class="j2store-product-images-wrapper">
		<?php echo $image_html; ?>
	</div>
	<?php endif; ?>

	<?php if ($product_html): ?>
	<div class="j2store-product-block-wrapper">
		<?php echo $product_html; ?>
	</div>
	<?php endif; ?>
</div>

