<?php
class BlocksContentControllerExtension extends Extension {

	/**
	 * @var array
	 */
	private static $allowed_actions = array(
		'handleBlock'
	);

	public function onAfterInit(){
		if($this->owner->getRequest()->getVar('block_preview') == 1){
			Requirements::javascript(THIRDPARTY_DIR . '/jquery/jquery.js');
			Requirements::javascript(BLOCKS_DIR . '/javascript/block-preview.js');
			Requirements::css(BLOCKS_DIR . '/css/block-preview.css');
		}
	}
	
	/**
	 * 
	 * Allows us to set and populate config areas without having to manually edit
	 * any themes' template files.
	 * 
	 * @return HTMLText
	 * @param SS_HTTPRequest $req
	 * @todo only return something if in user-config mode
	 * @todo add validation to SiteConfig to disallow deletion of SS' default $Config placeholder
	 */
	public function index(SS_HTTPRequest $req) {
		$siteConfig = SiteConfig::current_site_config();
		$blockAreas = $siteConfig->getUserDefinedConfigAreas();
		
		// Populate incoming vcontent placeholder vars, if configured in CMS' SiteConfig
		$_blockAreas = [];
		$content = '';
		foreach($blockAreas as $areaName) {
			$content .= (
				($areaName === 'Content') ? 
				$this->owner->Content : 
				$this->owner->BlockArea($areaName)
			);
		}
		
		// Escape for templates and prevent XSS to boot...
		$_blockAreas['Content'] = DBField::create_field('HTMLText', $content);
		
		/*
		 * Becuase every theme at least comes with a 'Page.ss' template.
		 * CWP sites will however need detection so they leverage 'BasePage.ss' instead.
		 */
		return $siteConfig->owner->renderWith('Page', array(
			'Content' => SSViewer::execute_template(
				'Includes/CustomBlockAreas',
				null,
				$_blockAreas
			)
		));
	}

	/**
	 * Handles blocks attached to a page
	 * Assumes URLs in the following format: <URLSegment>/block/<block-ID>.
	 * 
	 * @return RequestHandler
	 */
	public function handleBlock() {
		if($id = $this->owner->getRequest()->param('ID')){
			$blocks = $this->owner->data()->getBlockList(null, true, true, true);
			if($block = $blocks->find('ID', $id)){
				return $block->getController();
			}
		}
		return $block->getController();
	}

}