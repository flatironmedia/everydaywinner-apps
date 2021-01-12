<?php
namespace App\Model\Table;

use Cake\ORM\Table;

class SiteTable extends Table {
    var $actsAs = array('Containable');

    public $hasMany = 'Standing';
}
?>