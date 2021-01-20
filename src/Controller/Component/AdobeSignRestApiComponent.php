<?php

namespace App\Controller\Component;

use Cake\Controller\Component;
use Cake\Core\Configure;
use Cake\Http\Client;
use Cake\Utility\Hash;
use Cake\Log\Log;

class AdobeSignRestApiComponent extends Component
{
    public function initialize(array $config)
    {
        parent::initialize($config);
        $this->tokenData = $this->refreshToken();
        $this->ApiURL = $this->getBaseUris();
        // $this->ApiKey = Configure::read('ClickMeterApiKey');
        // $this->pushCampaignGroupId = Configure::read('pushCampaignGroupId');
        // $this->clickMeterDomainId = Configure::read('clickMeterDomainId');
        // $this->httpClient = new Client();
    }

    public function makeTransientDocuments($document)
    {
        $res = (new Client())->post("{$this->ApiURL}transientDocuments", [
            'File' => fopen($document, 'r'),
        ], [
            'headers' => [
                'Authorization' => "{$this->tokenData['token_type']} {$this->tokenData['access_token']}",
            ],
        ]);
        return $res->getJson;
    }
    public function createWidgetId($document){
        $transientDocumentId = $this->makeTransientDocuments($document);
        $widgetInfo = [
            'fileInfos' => [
                ['transientDocumentId' =>  $transientDocumentId['transientDocumentId']]
            ],
            'state' => "AUTHORING",
        ];
        $widgetInfo['name'] = 'EverydayWinner Widget';
        $widgetInfo['widgetParticipantSetInfo'] = [
            'memberInfos' => [
                [
                    'email' => '',
                ],
            ],
            'role' => 'SIGNER',
        ];

        //This part is done so far, response maybe in a working state.
        $res = (new Client())->post("{$this->ApiURL}widgets", json_encode($widgetInfo), [
            'headers' => [
                'Authorization' => "{$this->tokenData['token_type']} {$this->tokenData['access_token']}",
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ],
            'type' => 'json'
            ]);
        return $res->getJson;
    }

    public function createWidgetInfo($document, $options)
    {
        debug($this->tokenData);
        debug($this->ApiURL);
        die();
        $optionProperties = $options;
        $widgetId = $this->createWidgetId($document);
        $widgetInfo = [
            'name' => 'AUTHORING',
        ];
        $res = (new Client())->post("{$this->ApiURL}widgets/{$widgetId['id']}/views", json_encode($widgetInfo), [
            'headers' => [
                'Authorization' => "{$this->tokenData['token_type']} {$this->tokenData['access_token']}",
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ],
            'type' => 'json'
            ]);
        return $res->getJson;
    }

    public function getBaseUris()
    {
        $res = (new Client())->get('https://api.echosign.com/api/rest/v6/baseUris', [], [
            'headers' => [
                'Authorization' => "{$this->tokenData['token_type']} {$this->tokenData['access_token']}",
            ]
        ]);
        return "{$res->getJson['apiAccessPoint']}api/rest/v6/";
    }
    public function refreshToken($tokenOverride = null, $clientIdOverride = null, $clientSecretOverride = null)
    {
        $refreshToken = $tokenOverride ? $tokenOverride : Configure::read('Adobe.refresh_token');
        $clientId = $clientIdOverride ? $clientIdOverride : Configure::read('Adobe.client_id');
        $clientSecret = $clientSecretOverride ? $clientSecretOverride : Configure::read('Adobe.client_secret');

        $res = (new Client())->post('https://api.na1.echosign.com/oauth/refresh', [
            'grant_type' => 'refresh_token',
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'refresh_token' => $refreshToken,
        ], [
            'headers' => [
                'Content-Type' => 'application/x-www-form-urlencoded'
            ]
        ]);

        return $res->getJson;
    }

    public function generateTokenRequest()
    {
        $client = Configure::read('Adobe.client_id');
        $adobeRedirectUrl = Configure::read('Adobe.redirect_uri');

        return "https://secure.na1.echosign.com/public/oauth?redirect_uri=$adobeRedirectUrl/confirm/receiveAccessToken&response_type=code&client_id=$client&scope=user_read:account+user_write:account+user_login:account+agreement_read:account+agreement_write:account+agreement_send:account+widget_read:account+widget_write:account+library_read:account+library_write:account+workflow_read:account+workflow_write:account+webhook_read:account+webhook_write:account+webhook_retention:account";
    }
}
