<?php
namespace App\Model\Table;

use Cake\ORM\Table;

class StandingTable extends Table {
    var $actsAs = array('Containable');

    public $belongsTo = array('Site', 'Winner');
}