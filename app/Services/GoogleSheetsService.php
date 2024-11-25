<?php
namespace App\Services;

use Google_Client;
use Google_Service_Sheets;
use Google_Service_Sheets_ClearValuesRequest;

class GoogleSheetsService
{
    protected $client;
    protected $service;

    public function __construct()
    {
        $this->client = new Google_Client();
        $this->client->setAuthConfig(storage_path('app/google-sheet/google-api-credentials.json'));
        $this->client->addScope(Google_Service_Sheets::SPREADSHEETS_READONLY);
        $this->client->addScope(Google_Service_Sheets::SPREADSHEETS);
        $this->client->setAccessType('offline');

        $this->service = new Google_Service_Sheets($this->client);
    }

    public function getSheetData($spreadsheetId, $range)
    {
        $response = $this->service->spreadsheets_values->get($spreadsheetId, $range);
        return $response->getValues();
    }

    public function clearSheetData($spreadsheetId, $range)
    {
        $requestBody = new Google_Service_Sheets_ClearValuesRequest(); // Correct ClearValuesRequest instantiation
        $response = $this->service->spreadsheets_values->clear($spreadsheetId, $range, $requestBody);
        return $response;
    }
}
