<?php
/**
 * Legacy extension to aid with migrating from Blocks 0.x to 1.x
 * @package silverstipe blocks
 * @author Shea Dawson <shea@silverstripe.com.au>
 */
class BlockSiteConfigExtension extends DataExtension {
	
	/**
	 *
	 * @var array
	 */
	private static $db = array(
		'ConfigAreaSource'	=> "Enum('YML, User', 'YML')",
		'ConfigAreas'		=> 'Text'
	);
	
	/**
	 *
	 * @var array
	 */	
	private static $many_many = array(
		'Blocks' => 'Block'
	);

    /**
	 * 
	 * @todo Add JS toggle for ConfigAreas field dependent on selection made in ConfigSource field.
	 */
	public function updateCMSFields(FieldList $fields) {
		$fields->removeByName('Blocks');		
		$sources = Config::inst()->get('BlockManager', 'config_sources');
		$configSource = DropdownField::create('ConfigAreaSource', 'Block config source', $sources);
		$configSource->setDescription('Sets the source of truth for content-area cofiguration.');
		$configAreas = TextareaField::create('ConfigAreas', 'User defined content-areas', '$Content');
		$configAreas->setDescription('Add custom placeholders defined in the "Blocks" admin.'
				. ' (Only relevant if "User" is selected above).');
		$configAreas->setRows(15);
		
		$defaultContentAreas = Config::inst()->get('SiteConfig', 'default_content_areas');
		$defaultContentAreas = $this->owner->ConfigAreas ?: $defaultContentAreas;
		$configAreas->setValue($defaultContentAreas);
		$fields->addFieldsToTab('Root.Blocks', [$configSource, $configAreas]);
	}
	
	/**
	 * 
	 * Generates an array of user-inputted block areas for inclusion above or below SS'
	 * standard '$Content' variable.
	 * 
	 * @return array Associative array suited to passing to SSViewer->renderWith().
	 */
	public function getUserDefinedConfigAreas() {
		$areas = preg_split("#\R#", $this->owner->ConfigAreas);
		$_areas = [];
		foreach($areas as $area) {
			$a = preg_replace("#[^\w]+#", '', $area);
			$_areas[$a] = $a;
		}	
		
		return $_areas;
	}
}