<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 *
 * Advanced example template for J2Store content plugin
 * This demonstrates various customization options
 *
 * To use this template:
 * 1. Copy to: /templates/YOUR_TEMPLATE/html/plg_content_j2store/advanced.php
 * 2. Set plugin parameter 'product_template_layout' to 'advanced'
 */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;

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

// Get additional information
$isCategoryView = ($context == 'com_content.category' || $context == 'com_content.featured');
$productId = isset($product->j2store_product_id) ? $product->j2store_product_id : 0;
$productName = isset($product->product_name) ? $product->product_name : '';

// Add custom CSS for this template
$wa = Factory::getApplication()->getDocument()->getWebAssetManager();
$customCss = "
.j2store-advanced-layout {
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    padding: 20px;
    margin: 20px 0;
    background: #f9f9f9;
}
.j2store-advanced-layout .j2store-images-section {
    float: left;
    width: 30%;
    margin-right: 20px;
}
.j2store-advanced-layout .j2store-details-section {
    overflow: hidden;
}
.j2store-advanced-layout .j2store-product-badge {
    display: inline-block;
    padding: 5px 10px;
    background: #007bff;
    color: white;
    border-radius: 3px;
    font-size: 12px;
    margin-bottom: 10px;
}
@media (max-width: 768px) {
    .j2store-advanced-layout .j2store-images-section {
        float: none;
        width: 100%;
        margin-right: 0;
        margin-bottom: 15px;
    }
}
";
$wa->addInlineStyle($customCss);
?>

<div class="j2store-product-wrapper j2store-advanced-layout j2store-product-<?php echo $productId; ?> j2store-position-<?php echo $position; ?> <?php echo $isCategoryView ? 'j2store-category-layout' : 'j2store-item-layout'; ?>">

	<?php if (!$isCategoryView): ?>
	<span class="j2store-product-badge">J2Store Product</span>
	<?php endif; ?>

	<?php if ($image_html): ?>
	<div class="j2store-images-section">
		<?php echo $image_html; ?>
	</div>
	<?php endif; ?>

	<div class="j2store-details-section">
		<?php if ($productName && !$isCategoryView): ?>
		<h3 class="j2store-product-title"><?php echo htmlspecialchars($productName); ?></h3>
		<?php endif; ?>

		<?php if ($product_html): ?>
		<div class="j2store-product-block">
			<?php echo $product_html; ?>
		</div>
		<?php endif; ?>
	</div>

	<div style="clear: both;"></div>
</div>

