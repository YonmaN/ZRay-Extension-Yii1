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
		print_r($context);
		$storage['YiiConfig'][] = array('Name' => 'Application Name', 'Value' => $context['functionArgs'][0]);
	}
	
	/**
	 * @param array $context 
	 * @param array $storage 
	 */
	public function afterSetPathOfAlias($context, &$storage) {
		$storage['YiiAlias'][] = array('Name' => $context['functionArgs'][0], 'Value' => $context['functionArgs'][1]);
	}
}

$zrayYii1 = new Yii1();

$zre = new ZRayExtension('Yii1');
$zrayYii1->setZray($zre);
//$zre->setMetadata(array('logo' => __DIR__ . DIRECTORY_SEPARATOR . 'logo.png',));
$zre->setEnabled('Yii*::setPathOfAlias');

$zre->traceFunction('Yii*::setPathOfAlias', function(){}, array($zrayYii1, 'afterSetPathOfAlias'));
$zre->traceFunction('Yii*::createApplication', array($zrayYii1, 'beforeCreateApplication'), array($zrayYii1, 'afterCreateApplication'));
$zre->traceFunction('CController::renderPartial', function($context, &$storage){
	$storage['YiiRender'][] = array('Script' => $context['functionArgs'][0]);
}, function(){});
	
$zre->traceFunction('CController::renderFile', function($context, &$storage){
	print_r($context);
	//$storage['YiiRender'][] = array('Name' => $context['functionArgs'][0], 'Value' => $context['functionArgs'][1]);
}, function(){});
	
