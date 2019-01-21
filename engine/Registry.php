<?php

namespace payla\engine;

use Payla;
use payla\helper\ArrayHelper;

final class Registry extends Component{
		
	private $_components=[];
	private $_componentConfig=[];

	public function __construct($config = [])
    {
        Payla::setApplication($this);
        $this->registerCoreComponents();
        $this->configure($config);
    }

   	public function configure($configs)
	{
		if(is_array($configs))
		{
			$config = Payla::app()->config;
			foreach($configs as $key=>$value){
				if($key == 'components')
					$this->setComponents($value);
				else
					$config->set($key, $value);
			}

			$config->loadSettings();
		}
	}

	public function registerModuleComponents(){
		$modules = Payla::app()->config->get('modules');
		if(isset($modules[Payla::app()->request->module]['components'])){
			if(!empty($components = $modules[Payla::app()->request->module]['components']))
				$this->setComponents($components);
		}
	}

    public function run(){
        $response = Payla::app()->response;

        $request = Payla::app()->request;
        
        $request->resolveUri();

        $this->registerModuleComponents();

        $request->handler()->dispatch();

        // Output
		$response->output();
    }

	/**
	 * Registers the core application components.
	 * @see setComponents
	 */
	protected function registerCoreComponents()
	{
		$components = [
			'db'=>[
				'class'=>'payla\library\Db'
			],
			'load'=>[
				'class'=>'payla\engine\Loader'
			],
			'config'=>[
				'class'=>'payla\library\Config'
			],
			'language' => [
				'class' => 'payla\library\Language'
			],
			'url'=>[
				'class' => 'payla\library\Url'
			],
			'request'=>[
				'class'=>'payla\library\Request'
			],
			'response'=>[
				'class' => 'payla\library\Response'
			],
			'document' => [
				'class' => 'payla\library\Document'
			],
			'session' => [
				'class' => 'payla\library\Session'
			],
			'log'=>[
				'class'=>'payla\library\Log'
			]
		];

		$this->setComponents($components);
	}

	/**
	 * Checks whether the named component exists.
	 * @param string $id application component ID
	 * @return boolean whether the named application component exists (including both loaded and disabled.)
	 */
	public function hasComponent($id)
	{
		return isset($this->_components[$id]) || isset($this->_componentConfig[$id]);
	}

	/**
	 * Retrieves the named application component.
	 * @param string $id application component ID (case-sensitive)
	 * @param boolean $createIfNull whether to create the component if it doesn't exist yet.
	 * @see hasComponent
	 */
	public function getComponent($id,$createIfNull=true)
	{
		if(isset($this->_components[$id]))
			return $this->_components[$id];
		elseif(isset($this->_componentConfig[$id]) && $createIfNull)
		{
			$config=$this->_componentConfig[$id];
			if(!isset($config['enabled']) || $config['enabled'])
			{
				unset($config['enabled']);
				$component = Payla::createComponent($config);
				$component->init();
				return $this->_components[$id]=$component;
			}
		}
	}

	/**
	 * Puts a component under the management of the module.
	 * The component will be initialized by calling its init()
	 * method if it has not done so.
	 * @param string $id component ID
	 * @param array $component application component
	 * @param boolean $merge whether to merge the new component configuration
	 * with the existing one. Defaults to true, meaning the previously registered
	 * component configuration with the same ID will be merged with the new configuration.
	 * If set to false, the existing configuration will be replaced completely.
	 */
	public function setComponent($id,$component,$merge=true)
	{
		if($component===null)
		{
			unset($this->_components[$id]);
			return;
		}
		elseif(isset($this->_components[$id]))
		{
			if(isset($component['class']) && get_class($this->_components[$id])!==$component['class'])
			{
				unset($this->_components[$id]);
				$this->_componentConfig[$id]=$component; //we should ignore merge here
				return;
			}
			foreach($component as $key=>$value)
			{
				if($key!=='class')
					$this->_components[$id]->$key=$value;
			}
		}
		elseif(isset($this->_componentConfig[$id]['class'],$component['class'])
			&& $this->_componentConfig[$id]['class']!==$component['class'])
		{
			$this->_componentConfig[$id]=$component; //we should ignore merge here
			return;
		}
		if(isset($this->_componentConfig[$id]) && $merge)
			$this->_componentConfig[$id]=ArrayHelper::merge($this->_componentConfig[$id],$component);
		else
			$this->_componentConfig[$id]=$component;
	}

	/**
	 * Returns the application components.
	 * @param boolean $loadedOnly whether to return the loaded components only. If this is set false,
	 * then all components specified in the configuration will be returned, whether they are loaded or not.
	 * Loaded components will be returned as objects, while unloaded components as configuration arrays.
	 * @return array the application components (indexed by their IDs)
	 */
	public function getComponents($loadedOnly=true)
	{
		if($loadedOnly)
			return $this->_components;
		else
			return array_merge($this->_componentConfig, $this->_components);
	}

	/**
	 * Sets the application components.
	 *
	 */
	public function setComponents($components,$merge=true)
	{
		foreach($components as $id=>$component)
			$this->setComponent($id,$component,$merge);
	}
}