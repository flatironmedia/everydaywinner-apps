<?php

namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\ORM\TableRegistry;

class SiteConfigsTable extends Table {

    public $actsAs = array('FlatIronDataBase');
    public function getConfigBySiteCode($configName, $siteCode)
    {
        $sitesModel = TableRegistry::init('Sites');
        $site = $sitesModel->find('first', array(
                'conditions' => array('code' => $siteCode)
            )
        );

        $config = $this->find('first', array(
            'conditions' => array(
                'config_name' => $configName,
                'site_id' => $site['Site']['id']
            )
        ));

        return $config['SiteConfig']['config_value'];
    }
}
