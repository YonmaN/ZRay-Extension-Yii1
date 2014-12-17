<?php


class Yii1 {
	
	/**
	 * @var ZRayExtension
	 */
	private $zray;
	/**
	 * @var string
	 */
	private $configFile;
	/**
	 * @param \ZRayExtension $zray
	 */
	public function setZray($zray) {
		$this->zray = $zray;
	}
	
	/**
	 * @return \ZRayExtension
	 */
	public function getZray() {
		return $this->zray;
	}
	
	/**
	 * @param array $context 
	 * @param array $storage 
	 */
	public function beforeCreateApplication($context, &$storage) {
		$this->configFile = $context['functionArgs'][1];
		$this->getZray()->traceFile($this->configFile, function($context, &$storage) {}, array($this, 'afterConfigMerge'));
	}
	
	/**
	 * @param array $context 
	 * @param array $storage 
	 */
	public function afterCreateApplication($context, &$storage) {
		$storage['Yii'][] = array('Name' => 'Application Name', 'Value' => $context['functionArgs'][0]);
		$storage['Yii'][] = array('Name' => 'Configuration File', 'Value' => $context['functionArgs'][1]);
	}
	
	/**
	 * @param array $context 
	 * @param array $storage 
	 */
	public function afterConfigMerge($context, &$storage) {
		$storage['YiiConfig'][] = array('Name' => 'Application Name', 'Value' => $context['functionArgs'][0]);
	}
	
	/**
	 * @param array $context 
	 * @param array $storage 
	 */
	public function afterSetPathOfAlias($context, &$storage) {
		$storage['YiiAlias'][] = array('Name' => $context['functionArgs'][0], 'Value' => $context['functionArgs'][1]);
	}
	
	public function afterRenderPartial($context, &$storage){
		$storage['YiiRender'][] = array('Script' => $context['functionArgs'][0], 'Type' => 'partial');
	}
	
	public function afterRenderFile($context, &$storage){
		$storage['YiiRender'][] = array('Script' => $context['functionArgs'][0], 'Render' => 'file');
	}
	
	public function harvestApplication($context, &$storage) {
		foreach (YiiBase::app()->getModules() as $module => $properties) {
			$storage['YiiModules'][] = array('module' => $module, 'class' => $properties['class']);
		}
		
		$components = YiiBase::app()->getComponents(false);
		$usedComponents = array_keys(YiiBase::app()->getComponents(true));
		
		foreach ($components as $name => $component) {
			$class = 'unclear';
			if (is_object($component)) {
				$class = get_class($component);
			} elseif (is_array($component) && isset($component['class'])) {
				$class = $component['class'];
			}
			$storage['YiiComponents'][] = array('component' => $name, 'class' => $class, 'used' => in_array($name, $usedComponents) ? 'Loaded' : 'Nope');
		}
		
	}
	
	public function logEntries($context, &$storage) {
		$storage['YiiLog'][] = array('entry' => $context['functionArgs'][0]);
	}
}

$zrayYii1 = new Yii1();

$zre = new ZRayExtension('Yii1');
$zrayYii1->setZray($zre);
//$zre->setMetadata(array('logo' => __DIR__ . DIRECTORY_SEPARATOR . 'logo.png',));
$zre->setEnabled('Yii*::setPathOfAlias');

$zre->traceFunction('Yii*::setPathOfAlias', function(){}, array($zrayYii1, 'afterSetPathOfAlias'));
$zre->traceFunction('Yii*::createApplication', array($zrayYii1, 'beforeCreateApplication'), array($zrayYii1, 'afterCreateApplication'));
$zre->traceFunction('CController::renderPartial', function(){}, array($zrayYii1, 'afterRenderPartial'));
$zre->traceFunction('CController::renderFile', function(){}, array($zrayYii1, 'afterRenderFile'));
	
$zre->traceFunction('Yii*::createApplication', function(){}, array($zrayYii1, 'harvestApplication'));
$zre->traceFunction('YiiBase::log', function(){}, array($zrayYii1, 'logEntries'));
