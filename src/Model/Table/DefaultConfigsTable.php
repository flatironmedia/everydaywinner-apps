<?php

namespace App\Model\Table;

use Cake\ORM\Table;

class DefaultConfigsTable extends Table
{
    var $primaryKey = 'name';
        
    public function getDefaultConfig($name){
        $query = $this->find('list', array(
            'fields' => 'DefaultConfigs.value',
            'conditions' => array(
                'DefaultConfigs.name' => $name
            )
        ));

        $result = $query->toArray();

        return is_array($name)? $result:(array_key_exists($name,$result)?$result[$name]:null);
    }

    public function saveDefaultConfig($key, $value){
        $currentConfig = trim($this->getDefaultConfig($key));
        if(!is_null($currentConfig)){
            $this->id = $key;
        }
        $data = array(
            'name' => $key,
            'value' => $value
        );

        $defaultConfig = $this->newEntity($data);

        return $this->save($defaultConfig);
    }

    public function saveConfig($data){
        foreach($data as $field => $value){
            $this->saveDefaultConfig($field, $value);
        }
    }
}


?>