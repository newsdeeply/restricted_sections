<?php
	/*
	Copyight: Deux Huit Huit 2015
	LICENCE: MIT http://deuxhuithuit.mit-license.org;
	*/
	
	if(!defined("__IN_SYMPHONY__")) die("<h2>Error</h2><p>You cannot directly access this file</p>");
	
	/**
	 *
	 * @author Deux Huit Huit
	 * https://deuxhuithuit.com/
	 *
	 */
	class extension_restricted_sections extends Extension
	{

		/**
		 * Name of the extension
		 * @var string
		 */
		const EXT_NAME = 'Restricted Sections';
		
		/**
		 * Symphony utility function that permits to
		 * implement the Observer/Observable pattern.
		 * We register here delegate that will be fired by Symphony
		 */
		public function getSubscribedDelegates()
		{
			return array(
				// assets
				array(
					'page' => '/backend/',
					'delegate' => 'InitaliseAdminPageHead',
					'callback' => 'appendAssets',
				),
				// authors index
				array(
					'page' => '/system/authors/',
					'delegate' => 'AddCustomAuthorColumn',
					'callback' => 'addCustomAuthorColumn',
				),
				array(
					'page' => '/system/authors/',
					'delegate' => 'AddCustomAuthorColumnData',
					'callback' => 'addCustomAuthorColumnData',
				),
				// authors form
				array(
					'page' => '/system/authors/',
					'delegate' => 'AddElementstoAuthorForm',
					'callback' => 'addElementstoAuthorForm',
				),
				// delete
				array(
					'page' => '/system/authors/',
					'delegate' => 'AuthorPreDelete',
					'callback' => 'authorPreDelete',
				),
				array(
					'page' => '/system/authors/',
					'delegate' => 'AuthorPostDelete',
					'callback' => 'authorPostDelete',
				),
				// create
				array(
					'page' => '/system/authors/',
					'delegate' => 'AuthorPreCreate',
					'callback' => 'authorPreCreate',
				),
				array(
					'page' => '/system/authors/',
					'delegate' => 'AuthorPostCreate',
					'callback' => 'authorPostCreate',
				),
				// edit
				array(
					'page' => '/system/authors/',
					'delegate' => 'AuthorPreEdit',
					'callback' => 'authorPreEdit',
				),
				array(
					'page' => '/system/authors/',
					'delegate' => 'AuthorPostEdit',
					'callback' => 'authorPostEdit',
				),
				/*array( //EntryPreEdit, EntryPreCreate
					'EntryPreDelete', '/publish/',
					'page' => '/publish/',
					'delegate' => 'EntryPreDelete',
					'callback' => 'entryPreDelete',
				),*/
				// nav + security
				array(
					'page' => '/backend/',
					'delegate' => 'CanAccessPage',
					'callback' => 'canAccessPage',
				),
			);
		}

		/**
		 *
		 * Appends javascript/css files references into the head, if needed
		 * @param array $context
		 */
		public function appendAssets(array $context)
		{
			// store the callback array locally
			$c = Administration::instance()->getPageCallback();
			
			// publish page
			if($c['driver'] == 'publish') {
				
			}
		}

		public function addCustomAuthorColumn(array $context)
		{

		}

		public function addCustomAuthorColumnData(array $context)
		{

		}


		public function addElementstoAuthorForm(array $context)
		{
			$author = $context['author'];
			$curAuthor = Symphony::Author();
			// Takes privileges to edit this
			if (!$curAuthor->isDeveloper() &&
				!$curAuthor->isManager() &&
				!$curAuthor->isPrimary()) {
				return;
			}
			// Even manager should not edit their own
			if (!$curAuthor->isDeveloper() &&
				!$curAuthor->isPrimary() &&
				$curAuthor->get('id') == $author->get('id')) {
				return;
			}
			/*
			'form' => &$this->Form,
			'author' => $author,
			'fields' => $_POST['fields'],
			*/
			//var_dump($context['form']);die;//getChild

			$sections = SectionManager::fetch();

			$group = static::createAuthorFormElements($sections);

			$context['form']->insertChildAt(2, $group);
		}


		public function authorPreDelete(array $context)
		{

		}

		public function authorPostDelete(array $context)
		{

		}


		public function authorPreCreate(array $context)
		{

		}

		public function authorPostCreate(array $context)
		{

		}


		public function authorPreEdit(array $context)
		{

		}

		public function authorPostEdit(array $context)
		{

		}


		public function canAccessPage(array $context)
		{
			/*
			'allowed' => &$hasAccess,
			'page_limit' => $page_limit,
			'page_url' => $page,
			*/
			$curAuthor = Symphony::Author();
			if ($curAuthor->isDeveloper() ||
				$curAuthor->isPrimary()) {
				return;
			}
			$page = Administration::instance()->Page;
			$page_context = $page->getContext();
			// Content pages only
			if ($page instanceof contentPublish) {
				$section_id = SectionManager::fetchIDFromHandle($page_context['section_handle']);
				$hasAccess = static::canAccessSection($section_id);
				if (!$hasAccess) {
					// update delegate value
					$context['allowed'] = $context['allowed'] && $hasAccess;
					// Log that thing up
					$message = "Access to {$context[page_url]} has been denied for user " .
							$curAuthor->get('username');
					Symphony::Log()->pushToLog($message, E_WARNING, true, true, false);
				}
			}
		}

		/* ********* LIB ******* */

		protected static function createAuthorFormElements(array $sections)
		{
			$group = new XMLElement('fieldset');
			$group->setAttribute('class', 'settings');
			$group->appendChild(new XMLElement('legend', __('Restrcited Sections')));
			$help = new XMLElement('p', __('Insert help message here'), array('class' => 'help'));
			$group->appendChild($help);

			$label = Widget::Label(__('Allowed sections'));

			$attributes = array(
				'multiple' => 'multiple',
				'class' => 'required',
				'required' => 'required',
			);
			$options = array(
				array(0, false, __('All Sections'))
			);

			foreach ($sections as $section) {
				$options[] = array($section->get('id'), false, $section->get('name'));
			}
			$select = Widget::Select('restr_section[sections]', $options, $attributes);
			$label->appendChild($select);
			$group->appendChild($label);

			return $group;
		}

		public static function canAccessSection($section_id)
		{
			$section_id = (int)MySQL::cleanValue($section_id);
			$count = Symphony::Database()->fetchCol('c', "
				SELECT count(`author_id`) as c
					FROM `tbl_authors_restricted_sections`
					WHERE `section_id` = 0 OR `section_id` = $section_id
			");
			return isset($count[0]) && (int)$count[0] > 0;
		}

		protected static function save()
		{
			
		}

		protected static function createTable()
		{
			$ret = Symphony::Database()->query("
				CREATE TABLE `tbl_authors_restricted_sections` (
					`id` int(11) unsigned NOT NULL auto_increment,
					`author_id` int(11) unsigned NOT NULL,
					`section_id` int(11) unsigned NOT NULL,
					PRIMARY KEY  (`id`),
					UNIQUE KEY `author_id` (`author_id`, `section_id`)
				) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
			");
			return $ret;
		}

		protected static function dropTable()
		{
			$ret = Symphony::Database()->query("DROP TABLE `tbl_authors_restricted_sections`");
			return $ret;
		}

		/* ********* INSTALL/UPDATE/UNINSTALL ******* */

		/**
		 * Creates the table needed for the settings of the field
		 */
		public function install()
		{
			return static::createTable();
		}

		/**
		 * This method will update the extension according to the
		 * previous and current version parameters.
		 * @param string $previousVersion
		 */
		public function update($previousVersion = false)
		{
			$ret = true;
			
			if (!$previousVersion) {
				$previousVersion = '0.0.1';
			}
			
			// less than 0.1.0
			if ($ret && version_compare($previousVersion, '0.1.0') == -1) {
				
			}
			
			return $ret;
		}

		/**
		 * Drops the table needed for the settings of the field
		 */
		public function uninstall()
		{
			return static::dropTable();
		}

	}