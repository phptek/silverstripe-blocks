<?php
/**
 * BlockManager
 * @package silverstipe blocks
 * @author Shea Dawson <shea@livesource.co.nz>
 */
class BlockManager extends Object{

	/**
	 * Define areas and config on a per theme basis
	 * @var array
	 **/
	private static $themes = array();

	/**
	 * Use default ContentBlock class
	 * @var Boolean
	 **/
	private static $use_default_blocks = true;


	public function __construct(){
		parent::__construct();
	}

	
	/**
	 * Gets an array of all areas defined for the current theme
	 * @param string $theme
	 * @param bool $keyAsValue
	 * @return array $areas
	 **/
	public function getAreasForTheme($theme = null, $keyAsValue = true){
		$theme = $theme ? $theme : $this->getTheme();
		if(!$theme){
			return false;
		}
		$config = $this->getAreaSource($theme);
		if(!isset($config[$theme]['areas'])){
			return false;
		}
		$areas = $config[$theme]['areas'];
		$areas = $keyAsValue ? ArrayLib::valuekey(array_keys($areas)) : $areas;
		if(count($areas)){
			foreach ($areas as $k => $v) {
				$areas[$k] = $keyAsValue ? FormField::name_to_label($k) : $v;
			}	
		}
		return $areas;
	}


	/**
	 * Gets an array of all areas defined for the current theme that are compatible
	 * with pages of type $class
	 * @param string $class
	 * @return array $areas
	 **/
	public function getAreasForPageType($class){
		$areas = $this->getAreasForTheme(null, false);

		if(!$areas){
			return false;
		}

		foreach($areas as $area => $config) {
			if(!is_array($config)) {
				continue;
			}

			if(isset($config['except'])) {
				$except = $config['except'];
				if (is_array($except)
					? in_array($class, $except)
					: $except == $class
				) {
					unset($areas[$area]);
					continue;
				}
			}

			if(isset($config['only'])) {
				$only = $config['only'];
				if (is_array($only)
					? !in_array($class, $only)
					: $only != $class
				) {
					unset($areas[$area]);
					continue;
				}
			}
		}
		
		if(count($areas)){
			foreach ($areas as $k => $v) {
				$areas[$k] = FormField::name_to_label($k);
			}
			return $areas;
		}else{
			return $areas;
		}
	}


	/*
	 * Get the current/active theme
	 */
	private function getTheme(){
		return Config::inst()->get('SSViewer', 'theme');
	}

	/*
	 * Get the block config for the current theme
	 */
	private function getThemeConfig(){
		$theme = $this->getTheme();
		$config = $this->config()->get('themes');
		return $theme && isset($config[$theme]) ? $config[$theme] : null;
	}
	
	/*
	 * Usage of BlockSets configurable from yaml
	 */
	public function getUseBlockSets(){
		$config = $this->getThemeConfig();
		return isset($config['use_blocksets']) ? $config['use_blocksets'] : true;
	}

	/*
	 * Exclusion of blocks from page types defined in yaml
	 */
	public function getExcludeFromPageTypes(){
		$config = $this->getThemeConfig();
		return isset($config['exclude_from_page_types']) ? $config['exclude_from_page_types'] : array();
	}

	/*
	 * Usage of extra css classes configurable from yaml
	 */
	public function getUseExtraCSSClasses(){
		$config = $this->getThemeConfig();
		return isset($config['use_extra_css_classes']) ? $config['use_extra_css_classes'] : false;
	}
	
	/**
	 * 
	 * Dictates the definitive source for content-block information.
	 * 
	 * Defaults to use of YML config for configuration of content-block areas if
	 * no CMS user-input. If conflicts arise between names/keys of user-inputted 
	 * content blocks and YML config, the default priority is to take config from 
	 * user-input, but this can be overridden using the $priority parameter.
	 * 
	 * @param string $theme
	 * @param string $priority
	 * @return array
	 * @todo deal with 'except' and 'only' YML config declarations
	 * @todo deal with $priority
	 */
	public function getAreaSource($theme, $priority = 'User') {
		// Default to YML config
		$defaultConfig = $this->config()->get('themes');
		$sources = Config::inst()->get(__CLASS__, 'config_sources');

		$useDefault = (
			!$theme || 
			!in_array($priority, $sources) ||
			$priority !== 'User'
		);
		
		if($useDefault) {
			return $defaultConfig;
		} else {
			// Use CMS user-defined config
			$siteConfig = SiteConfig::current_site_config();
			return array(
				$theme => array('areas' => $siteConfig->getUserDefinedConfigAreas())
			);
		}
	}

}