<?php
/*
 * @package   pkg_radicalmicro
 * @version   1.0.0
 * @author    Dmitriy Vasyukov - https://fictionlabs.ru
 * @copyright Copyright (c) 2022 Fictionlabs. All rights reserved.
 * @license   GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link      https://fictionlabs.ru/
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Uri\Uri;
use RadicalMicro\Helpers\SchemaHelper;
use RadicalMicro\Helpers\DateHelper;
use RadicalMicro\Helpers\TypesHelper;

/**
 * Radicalmicro
 *
 * @package   plgRadicalmicroContent
 * @since     1.0.0
 */
class plgRadicalmicroContent extends CMSPlugin
{
	/**
	 * Application object
	 *
	 * @var    CMSApplication
	 * @since  1.0.0
	 */
	protected $app;

	/**
	 * Affects constructor behavior. If true, language files will be loaded automatically.
	 *
	 * @var    boolean
	 * @since  1.0.0
	 */
	protected $autoloadLanguage = true;

    /**
	 * OnRadicalmicroProvider event
     *
     * @return array.
	 *
	 * @since  1.0.0
	 */
	public function onRadicalmicroProvider()
	{
		// Check is article view
		if (!$this->isArticleView())
		{
			return;
		}

		$article_id = (int) $this->app->input->get('id', 0);

		// Get Article
		$article = Table::getInstance('Content', 'JTable');
		$article->load($article_id);

		// Get schema type
		$type = $this->params->get('type', 'article');

		// Get image
		$image = $this->getImage($article);

		// Data object
		$object = new stdClass();
		$object->id = $article->id;
		$object->type = $this->params->get('type', 'article');
		$object->title = $article->title;
		$object->description = $article->introtext.$article->fulltext;
		$object->url = Uri::current();
		$object->published = DateHelper::format($article->publish_up);
        $object->created = DateHelper::format($article->created);
		$object->modified = DateHelper::format($article->modified);
		$object->author = Factory::getUser($article->created_by)->name;

		if ($image)
		{
			$object->image = $image;
		}

		// Get schema data
		$schemaData = TypesHelper::execute($type, $object);

		// Set data
		$micro      = SchemaHelper::getInstance();
		$micro->addChild('root', $schemaData);
	}

	/**
	 * Check article page view
	 *
	 * @since  1.0.0
	 */
	public function isArticleView()
	{
		$input = Factory::getApplication()->input;

		return $input->getCmd('option') === 'com_content' && $input->getCmd('view') === 'article' && is_null($input->getCmd('task'));
	}

	/**
	 * Get image from Article object
     *
     * @return void|string.
	 *
	 * @since  1.1.0
	 */
	public function getImage(object $article)
	{
		$jsonImgObj = json_decode($article->images);

		if (!empty($jsonImgObj->image_fulltext))
		{
			return URI::root() . $jsonImgObj->image_fulltext;
		}

		if (!empty($json->image_intro))
		{
			return URI::root() . $jsonImgObj->image_intro;
		}

		return;
	}
}
