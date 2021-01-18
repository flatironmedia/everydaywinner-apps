<?php

namespace App\Model\Table;

use Cake\ORM\Table;

class PrizeTable extends Table {
    public function initialize(array $config)
    {
        $this->hasOne('Winners');
        $this->hasOne('PrizeSchedules');
    }
}