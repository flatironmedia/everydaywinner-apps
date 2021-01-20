<?php
namespace App\Controller\Component;

use Cake\Controller\Component;

/* for sites configuration that are not saved on the database */
class SiteComponent extends Component {
    private $site = [
        'EDW' => [
            'name' => 'Everyday Winner',
            'winnerTitle' => "EverydayWinner's winner",
            'prize' => '$500 Visa gift card',
            'release_document_url' => ROOT . DS . 'tmpfiles' . DS .'EverydayWinner_Release_Document.pdf',
            'photo_upload_path' => "files/winnerphoto/",
            'winner_circle_path' => "files\\winnercircle\\"
        ],

        'WG' => [
            'name' => 'Everyday Winner Giveaways',
            'winnerTitle' => 'Everyday Giveaways Winner',
            'prize' => 'Weekly giveaway',
            'release_document_url' => ROOT . DS . 'tmpfiles' . DS . 'EverydayWinnerGiveAways_Release_Document.pdf',
            'photo_upload_path' => "files/wg_winnerphoto/",
            'winner_circle_path' => "files\\wg_winnercircle\\"
        ]

    ];

    public function getSiteConfig($configName, $siteCode)
    {
        return $this->site[$siteCode][$configName];
    }
}
