<?php
namespace App\Controller\Component;

use Cake\Controller\Component;
// use Cake\Core\Configure;

class EDWThingsComponent extends Component {

    private function arraysValues(){
        $USStates = array('AL' => 'Alabama','AK' => 'Alaska','AZ' => 'Arizona','AR' => 'Arkansas','CA' => 'California',
            'CO' => 'Colorado','CT' => 'Connecticut','DE' => 'Delaware','DC' => 'Washington DC','FL' => 'Florida','GA' => 'Georgia',
            'HI' => 'Hawaii','ID' => 'Idaho','IL' => 'Illinois','IN' => 'Indiana','IA' => 'Iowa','KS' => 'Kansas','KY' => 'Kentucky',
            'LA' => 'Louisiana','ME' => 'Maine','MD' => 'Maryland','MA' => 'Massachusetts','MI' => 'Michigan','MN' => 'Minnesota',
            'MS' => 'Mississippi','MO' => 'Missouri','MT' => 'Montana','NE' => 'Nebraska','NV' => 'Nevada','NH' => 'New Hampshire',
            'NJ' => 'New Jersey','NM' => 'New Mexico','NY' => 'New York','NC' => 'North Carolina','ND' => 'North Dakota','OH' => 'Ohio',
            'OK' => 'Oklahoma','OR' => 'Oregon','PA' => 'Pennsylvania','PR' => 'Puerto Rico','RI' => 'Rhode Island','SC' => 'South Carolina',
            'SD' => 'South Dakota','TN' => 'Tennessee','TX' => 'Texas','UT' => 'Utah','VT' => 'Vermont','VI' => 'Virgin Islands','VA' => 'Virginia',
            'WA' => 'Washington','WV' => 'West Virginia','WI' => 'Wisconsin',  'WY' => 'Wyoming');
        

        $result = array(
            'US_States'=>$USStates,
        );

        return $result;
    }

    public function getArrayValue($key){
        if(array_key_exists($key, $this->arraysValues())){
            return $this->arraysValues()[$key];
        }
        return array();
    }
    
}
