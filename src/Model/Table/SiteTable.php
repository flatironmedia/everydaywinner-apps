<?php
namespace App\Model\Table;

use Cake\ORM\Table;

class SiteTable extends Table
{
    public static function defaultConnectionName() {
        return 'EDWmySQL';
    }

    public function initialize(array $config)
    {
        $this->hasMany('Standings');
    }

}
?>