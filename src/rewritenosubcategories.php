<?php
/**
 * @package       rewritenosubcategories
 * @author        Alexandre ELISÉ <contact@alexandre-elise.fr>
 * @copyright (c) 2021 . Alexandre ELISÉ . Tous droits réservés.
 * @license       GPL-2.0-and-later GNU General Public License v2.0 or later
 * @link          https://coderparlerpartager.fr
 */

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Router\Router;
use Joomla\CMS\Table\Table;
use Joomla\Utilities\ArrayHelper;

defined('_JEXEC') or die;


/**
 * @package     PlgSystemRewriteNoSubCategories
 *
 * @since       version
 */
class PlgSystemRewriteNoSubCategories extends CMSPlugin
{
	/**
	 * Application object.
	 *
	 * @var    \JApplicationCms
	 * @since  3.8.0
	 */
	protected $app;

	/**
	 * @var \JDatabaseDriver $db
	 * @since version
	 */
	protected $db;

	/**
	 *
	 * @var bool
	 * @since version
	 */
	protected $autoloadLanguage = true;

	/**
	 * The routing mode.
	 *
	 * @var    boolean
	 * @since  2.5
	 */
	protected $modeSef;

	/**
	 * @var array $paths
	 * @since version
	 */
	protected $paths;

	/**
	 * Provided parent category id from which to start the subtree lookup
	 *
	 * @var int $parentCategoryId
	 */
	protected $parentCategoryId;

	/**
	 * PlgSystemRewriteNoSubCategories constructor.
	 *
	 * @param   object  &$subject  The object to observe
	 * @param   array   $config    An optional associative array of configuration settings.
	 *                             Recognized key values include 'name', 'group', 'params', 'language'
	 *                             (this list is not meant to be comprehensive).
	 *
	 * @since   1.5
	 */
	public function __construct(&$subject, $config = array())
	{
		parent::__construct($subject, $config);

		if ($this->app->isClient('administrator'))
		{
			return;
		}

		// setup most used params
		$this->modeSef          = (int) $this->app->get('sef', 0);
		$this->parentCategoryId = $this->params->get('parent_category_id') ? (int) $this->params->get('parent_category_id') : -1;

		$this->paths = array();

		Table::addIncludePath(JPATH_ADMINISTRATOR . '/com_categories/tables');

		/**
		 * @var \JTableCategory $categoryTable
		 */
		$categoryTable = Table::getInstance('Category');

		// recuperer info categorie parente en base de donnée
		if (!$categoryTable->load(array('id' => $this->parentCategoryId)))
		{
			return;
		}

		// seulement les categories en dessous de notre categorie parente choisie dans les parametres de ce plugin
		$myParentCategoryIdSubTree = array_reverse($categoryTable->getTree($this->parentCategoryId), true);

		if (empty($myParentCategoryIdSubTree))
		{
			return;
		}

		foreach ($myParentCategoryIdSubTree as $subTree)
		{
			if ((int) $subTree->level < 2)
			{
				continue;
			}
			$this->paths[(string) $subTree->id] = $subTree;
		}
	}
	
	/**
	 * This event is triggered right after Joomla has finished "booting"
	 */
	public function onAfterInitialise()
	{
		// We need to make sure we are always using the site router.
		$router = CMSApplication::getRouter('site');

		// Attach build rules
		$router->attachBuildRule(array($this, 'buildRule'), Router::PROCESS_DURING);

		// Attach parse rules
		$router->attachParseRule(array($this, 'parseRule'), Router::PROCESS_DURING);

	}


	/**
	 * Add build rule to router.
	 *
	 * @param   \JRouter  &$router  JRouter object.
	 * @param   \JUri     &$uri     JUri object.
	 *
	 * @return  void
	 *
	 *
	 * @since   1.6
	 */
	public function buildRule(&$router, &$uri)
	{
		//if provided parent category has no sub category stop here
		if (empty($this->paths))
		{
			return;
		}

		$isContent = ($this->app->input->getCmd('option') === 'com_content');

		$isAnArticle = ($this->app->input->getCmd('view') === 'article');

		$isArticleCategoryInSubTree = $this->isCategoryInSubTree($this->app->input->getUint('catid'), $this->paths);

		$isArticleCategoryGivenParentId = ($this->app->input->getUint('catid') === $this->parentCategoryId);


		// if not in right "context" stop here
		if (!($isContent
				&& $isAnArticle
				&& $isArticleCategoryInSubTree
			)
			|| ($isContent
				&& $isAnArticle
				&& $isArticleCategoryGivenParentId
			)
		)
		{
			$uri->delVar('parent_catid');

			return;
		}

		if (!$this->modeSef)
		{
			$uri->setVar('parent_catid', $this->parentCategoryId);

			return;
		}
		
		$currentUriPath = $uri->getPath();
		$parts          = explode('/', $currentUriPath);

		if (empty($parts))
		{
			return;
		}

		// remove every intermediate sub categories form Uri path
		foreach ($this->paths as $subTree)
		{
			$index = array_search($subTree->alias, $parts, true);
			if ($index === false)
			{
				continue;
			}
			array_unshift($parts, $parts[$index]);
		}

		// uri path without intermediate sub categories (only parent category and article alias) on sef mode
		$uri->setPath(implode('/', $parts));

	}


	/**
	 * Add parse rule to router.
	 *
	 * @param   \JRouter  &$router  JRouter object.
	 * @param   \JUri     &$uri     JUri object.
	 *
	 * @return  array  an array of uri vars
	 *
	 * @since   1.6
	 */
	public function parseRule(&$router, &$uri)
	{
		assert(!empty($this->paths), 'parent category subtree cannot be empty');

		$vars = array();

		if (empty($this->paths))
		{
			return $vars;
		}

		if (!$this->modeSef)
		{
			return $vars;
		}

		$parts = explode('/', $uri->getPath());

		if (empty($parts))
		{
			return $vars;
		}

		if (count($parts) !== 2)
		{
			return $vars;
		}

		try
		{
			//extract article from its alias found in Uri path
			$article = $this->getArticleByAlias($parts[1]);

			if (!($article instanceof \stdClass))
			{
				return $vars;
			}

			// set router vars based on article instance found if any
			$vars['option'] = 'com_content';
			$uri->delVar('option');
			$vars['view'] = 'article';
			$uri->delVar('view');
			$vars['id'] = (((int) $article->id) . ':' . $article->alias);
			$uri->delVar('id');
			$vars['catid'] = (int) $article->catid;
			$uri->delVar('catid');
			$vars['parent_catid'] = $this->parentCategoryId;
			$uri->delVar('parent_catid');
            $uri->setPath('');

			return $vars;
		}
		catch (Exception $exception)
		{
			return $vars;
		}
	}

	/**
	 * Subtree: sub categories of plugin params configured parent_category_id
	 * If given id is in this subtree it means it's one of subcategories "allowed"
	 *
	 * @param   int    $id
	 * @param   array  $subTree
	 *
	 * @return bool
	 */
	private function isCategoryInSubTree($id, array $subTree)
	{
		return (in_array($id, ArrayHelper::toInteger(array_keys($subTree)), true));
	}

	/**
	 * Query the database to return an article object based on provided alias
	 *
	 * @param $alias
	 *
	 * @return mixed|null
	 */
	private function getArticleByAlias($alias)
	{
		$query = $this->db->getQuery(true);
		$query->select('a.*');
		$query->from($this->db->qn('#__content', 'a'));
		$query->where($this->db->qn('a.alias') . '=' . $this->db->q($alias));
		$this->db->setQuery($query, 0, 1);

		return $this->db->loadObject();
	}
}
