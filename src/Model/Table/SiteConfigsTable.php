<?php

namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\ORM\TableRegistry;

class SiteConfigsTable extends Table {

    public function initialize(array $config)
    {
        $this->addBehavior('FlatIronDataBase');
    }

    public function getConfigBySiteCode($configName, $siteCode)
    {
        $sitesModel = TableRegistry::getTableLocator()->get('Sites');
        $site = $sitesModel->find('all', [
                'conditions' => ['code' => $siteCode]
            ]
        )->first();

        $config = $this->find('all', [
            'conditions' => [
                'config_name' => $configName,
                'site_id' => $site['id']
            ]
        ])->first();

        return $config['config_value'];
    }
}
