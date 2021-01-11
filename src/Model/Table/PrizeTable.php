<?php


namespace App\Model\Table;

use Cake\ORM\Table;

class PrizeTable extends Table {
    public $hasOne = array('Winner', 'PrizeSchedule');
}