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

defined ('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Associations;
use Joomla\CMS\Router\Route;
use Joomla\Filter\OutputFilter;
use \J2Commerce\Helper\J2StoreRouterHelper;

class J2Article
{
	public static $instance = null;
	protected $state;

	public function __construct($properties=null)
  {

	}

	public static function getInstance(array $config = array())
	{
		if (!self::$instance)
		{
			self::$instance = new self($config);
		}

		return self::$instance;
	}

	public function display( $articleid )
	{
		$html = '';
		if(empty($articleid)) {
			return;
		}
		//try loading language associations
			$id = $this->getAssociatedArticle($articleid);
			if($id && is_int($id)) {
				$articleid = $id;
			}
		$item = $this->getArticle($articleid);
		// Return html if the load fails
		if (!$item->id)
		{
			return $html;
		}

		$item->title = OutputFilter::ampReplace($item->title);

		$item->text = '';

		$item->text = $item->introtext . chr(13).chr(13) . $item->fulltext;

		$prepare_content = J2Store::config()->get('prepare_content', 0);
		if($prepare_content) {
			$html .= HTMLHelper::_('content.prepare', $item->text);
		}else {
			$html .= $item->text;
		}

		return $html;
	}

	public function getArticle($id)
  {
		static $sets;

		if ( !is_array( $sets ) )
		{
			$sets = array( );
		}
		if ( !isset( $sets[$id] ) )
		{
            $db = Factory::getContainer()->get('DatabaseDriver');
			$query = $db->getQuery(true);
			$query->select('*')->from('#__content')->where('id='.$db->q($id));
			$db->setQuery($query);
			$sets[$id] = $db->loadObject();
		}
		return $sets[$id];
	}

	public function getArticleLink($id = 0)
  {
	    $link = '';
	    if(empty($id)){
	        return $link;
        }
        $article = $this->getArticle($id);
        $com_path = JPATH_SITE.'/components/com_content/';

        if (!class_exists('ContentHelperRoute')) {
            require_once $com_path.'helpers/route.php';
        }
        return Route::_(ContentHelperRoute::getArticleRoute($article->id, $article->catid, $article->language));
    }

	public function loadFalangAliasById($id,$lang_id)
  {
		if (empty($id) || empty($lang_id)){
			return '';
		}

		//get default language
        $params = ComponentHelper::getParams('com_languages');
        $lang = $params->get('site');
		$default_lang_id = J2StoreRouterHelper::getLanguageId($lang);
        $db = Factory::getContainer()->get('DatabaseDriver');
		$query = $db->getQuery(true);
		if($default_lang_id == $lang_id){
			$query->select('original_text');
		}else{
			$query->select('value');
		}
		$query->from('#__falang_content')
			->where($db->quoteName('reference_table') . ' = ' . $db->quote('content'))
			->where($db->quoteName('reference_field') . ' = ' . $db->quote('alias'))
			->where($db->quoteName('published') . ' = 1');
		if($default_lang_id != $lang_id){
			$query->where($db->quoteName('language_id') . ' = '.$db->q($lang_id));
		}
		$query->where($db->quoteName('reference_id') . ' = ' . $db->quote($id));
		$db->setQuery($query);
		$alias = $db->loadResult();
		return $alias;
	}

	public function getArticleByAlias($alias, $categories = array())
	{
		static $sets;

		if ( ! is_array( $sets ) )
		{
			$sets = array();
		}
		if ( ! isset( $sets[ $alias ] ) )
		{
			$content_id = 0;
			if ( $this->isFalangInstalled() )
			{
				$config = J2Store::config();
				// get alternate content from falang tables
				if ( $config->get( 'enable_falang_support', 0 ) )
				{
					$content_id = $this->loadFalangContentID( $alias );
				}
			}

      $db = Factory::getContainer()->get('DatabaseDriver');
			$query = $db->getQuery( true );
			$query->select( '*' )->from( '#__content' );
			if ( $content_id > 0 )
			{
				$query->where( $db->quoteName( 'id' ) . ' = ' . $db->quote( $content_id ) );
			} else
			{
				$query->where( $db->quoteName( 'alias' ) . ' = ' . $db->quote( $alias ) );
				$tag = Factory::getApplication()->getLanguage()->getTag();
				if ( $tag != '*' && ! empty( $tag ) )
				{
					$query->where( $db->quoteName( 'language' ) . ' IN (' . $db->quote( $tag ) . ',' . $db->quote( '*' ) . ' )' );
				}
			}

			if ( $categories )
			{
				if ( ! is_array( $categories ) )
				{
					$categories = (array) $categories;
				}
				//$categories = J2Store::platform()->toInteger( $categories );
				//Too early to introduce this as this would affect if store owners use multiple categories
				//$query->where( $db->quoteName( 'catid' ) . ' IN (' . implode( ',', $categories ) . ')' );
			}

			$db->setQuery( $query );
			try
			{
				$sets[ $alias ] = $db->loadObject();
			} catch ( Exception $e )
			{
				$sets[ $alias ] = new stdClass();
			}

		}

		return $sets[ $alias ];
	}

	/**
	 * Check if Falang is installed
	 * @return bool true if falng is installed
	 * */
	public function isFalangInstalled()
  {
		if(ComponentHelper::isInstalled('com_falang')) {
			if(ComponentHelper::isEnabled('com_falang')) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Check the falang table aliases and get corresponding content id
	 * @param string $alias article or product alias
	 * @return int    		content_id of the corresponding article or 0 if none
	 * */
	public function loadFalangContentID($alias='')
  {
		if (empty($alias)){
			return 0;
		}
        $db = Factory::getContainer()->get('DatabaseDriver');
		$query = $db->getQuery(true);
		$query->select('reference_id')->from('#__falang_content')
			->where($db->quoteName('reference_table') . ' = ' . $db->quote('content'))
			->where($db->quoteName('reference_field') . ' = ' . $db->quote('alias'))
			->where($db->quoteName('published') . ' = 1')
			->where($db->quoteName('value') . ' = ' . $db->quote($alias));
		$db->setQuery($query);
		$content_id = $db->loadResult();
		if (empty($content_id)){
			$content_id = 0;
		}
		return $content_id;
	}

    public function getAssociatedArticle($id,$tag='')
    {
        $associated_id =0;

        if(empty($tag)){
            $tag = Factory::getApplication()->getLanguage()->getTag();
        }
        $result = $this->getAssociations($id, 'article',$tag);
        if(isset($result[$tag])) {
            $associated_id = (int) $result[$tag];
        }
        if(isset($associated_id) && $associated_id) {
            $id = $associated_id;
        }
        return $id;
    }

    /**
     * Method to get the associations for a given item
     *
     * @param   integer  $id    Id of the item
     * @param   string   $view  Name of the view
     *
     * @return  array   Array of associations for the item
     */
    public function getAssociations($id = 0, $view = null, $current_tag = '')
    {
        $user   = Factory::getApplication()->getIdentity();
        $groups = implode(',', $user->getAuthorisedViewLevels());

        if ( $view === 'article' && !empty($id) )
        {
            if ($id)
            {
                $associations = Associations::getAssociations('com_content', '#__content', 'com_content.item', $id);

                $return = array();
                if(empty($current_tag)){
                    $tag = Factory::getApplication()->getLanguage()->getTag();
                }
                foreach ($associations as $tag => $item)
                {
                    // no need to run other language
                    if($current_tag === $tag){
                        $arrId   = explode(':', $item->id);
                        $assocId = $arrId[0];

                        $db = Factory::getContainer()->get('DatabaseDriver');
                        $query = $db->getQuery(true)
                            ->select($db->qn('state'))
                            ->from($db->qn('#__content'))
                            ->where($db->qn('id') . ' = ' . $db->q((int) ($assocId)))
                            ->where('access IN (' . $groups . ')');
                        $db->setQuery($query);

                        $result = (int) $db->loadResult();

                        if ($result > 0)
                        {
                            $return[$tag] = $item->id;
                        }
                    }

                }

                return $return;
            }
        }

        return array();
    }

	public function getCategoryById($id)
  {
		if (! is_numeric ( $id ) || empty ( $id ))
			return new stdClass();

		static $csets;

		if (! is_array ( $csets )) {
			$csets = array ();
		}
		if (! isset ( $csets [$id] )) {
			$db = Factory::getContainer()->get('DatabaseDriver');
			$query = $db->getQuery ( true );
			$query->select ( '*' )->from ( '#__categories' )->where ( $db->quoteName ( 'id' ) . ' = ' . $db->quote ( $id ) );
			$db->setQuery ( $query );
			$csets [$id] = $db->loadObject ();
		}

		return $csets [$id];
	}
}
