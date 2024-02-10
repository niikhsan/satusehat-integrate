<?php

namespace Niikhsan\SatusehatIntegrate;

use Dotenv\Dotenv;
use GuzzleHttp\Client;
// Guzzle HTTP Package
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Psr7\Request;
// SATUSEHAT Model & Log
use Niikhsan\SatusehatIntegrate\Models\SatusehatLog;
use Niikhsan\SatusehatIntegrate\Models\SatusehatCondition;
use Niikhsan\SatusehatIntegrate\Models\SatusehatEncounter;
use Niikhsan\SatusehatIntegrate\Models\SatusehatToken;

class OAuth2Client
{
    public $patient_dev = ['P02478375538', 'P02428473601', 'P03647103112', 'P01058967035', 'P01836748436', 'P01654557057', 'P00805884304', 'P00883356749', 'P00912894463'];

    public $practitioner_dev = ['10009880728', '10006926841', '10001354453', '10010910332', '10018180913', '10002074224', '10012572188', '10018452434', '10014058550', '10001915884'];

    public $auth_url = "";

    public $base_url = "";

    public $client_id = "";

    public $client_secret = "";

    public $organization_id = "";

    public function __construct()
    {
        // $dotenv = Dotenv::createUnsafeImmutable(getcwd());
        // $dotenv->safeLoad();

        if (getenv('SATUSEHAT_ENV') == 'PROD') {
            $this->auth_url = getenv('SATUSEHAT_AUTH_PROD', 'https://api-satusehat.kemkes.go.id/oauth2/v1');
            $this->base_url = getenv('SATUSEHAT_FHIR_PROD', 'https://api-satusehat.kemkes.go.id/fhir-r4/v1');
            $this->client_id = getenv('CLIENTID_PROD');
            $this->client_secret = getenv('CLIENTSECRET_PROD');
            $this->organization_id = getenv('ORGID_PROD');
        } elseif (getenv('SATUSEHAT_ENV') == 'STG') {
            $this->auth_url = getenv('SATUSEHAT_AUTH_STG', 'https://api-satusehat-stg.dto.kemkes.go.id/oauth2/v1');
            $this->base_url = getenv('SATUSEHAT_FHIR_STG', 'https://api-satusehat-stg.dto.kemkes.go.id/fhir-r4/v1');
            $this->client_id = getenv('CLIENTID_STG');
            $this->client_secret = getenv('CLIENTSECRET_STG');
            $this->organization_id = getenv('ORGID_STG');
        } elseif (getenv('SATUSEHAT_ENV') == 'DEV') {
            $this->auth_url = getenv('SATUSEHAT_AUTH_DEV', 'https://api-satusehat-dev.dto.kemkes.go.id/oauth2/v1');
            $this->base_url = getenv('SATUSEHAT_FHIR_DEV', 'https://api-satusehat-dev.dto.kemkes.go.id/fhir-r4/v1');
            $this->client_id = getenv('CLIENTID_DEV');
            $this->client_secret = getenv('CLIENTSECRET_DEV');
            $this->organization_id = getenv('ORGID_DEV');
        }

        if ($this->organization_id == null) {
            return 'Add your organization_id at environment first';
        }
    }

    public function token()
    {
        $token = SatusehatToken::where('environment', getenv('SATUSEHAT_ENV'))->orderBy('created_at', 'desc')
            ->where('created_at', '>', now()->subMinutes(50))->first();

        if ($token) {
            return $token->token;
        }

        $client = new Client();

        $headers = [
            'Content-Type' => 'application/x-www-form-urlencoded',
        ];

        $options = [
            'form_params' => [
                'client_id' => $this->client_id,
                'client_secret' => $this->client_secret,
            ],
        ];

        // Create session
        $url = $this->auth_url.'/accesstoken?grant_type=client_credentials';
        $request = new Request('POST', $url, $headers);

        try {
            $res = $client->sendAsync($request, $options)->wait();
            $contents = json_decode($res->getBody()->getContents());

            if (isset($contents->access_token)) {
                SatusehatToken::create([
                    'environment' => getenv('SATUSEHAT_ENV'),
                    'token' => $contents->access_token,
                ]);

                return $contents->access_token;
            } else {
                // return $this->respondError($oauth2);
                return null;
            }
        } catch (ClientException $e) {
            // error.
            $res = json_decode($e->getResponse()->getBody()->getContents());
            $issue_information = $res->issue[0]->details->text;

            return $issue_information;
        }
    }

    // https://www.uuidgenerator.net/dev-corner/php
    public function guidv4($data = null) {
        
        // Generate 16 bytes (128 bits) of random data or use the data passed into the function.
        $data = $data ?? random_bytes(16);
        assert(strlen($data) == 16);

        // Set version to 0100
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        // Set bits 6-7 to 10
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);

        // Output the 36 character UUID.
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

    public function log($id, $statusCode, $action, $url, $payload, $response)
    {
        $status = new SatusehatLog();
        $status->response_id = $id;
        $status->action = $action;
        $status->url = $url;
        $status->payload = $payload;
        $status->response = $response;
        $status->user_id = auth()->user() ? auth()->user()->id : 'Cron Job';
        $status->save();

        $dataset = json_decode($payload[0]);
        if( $statusCode == 200 ) {

            if( $dataset->resourceType == 'Bundle' ) {

                foreach ($dataset->entry as $key => $value) {

                    if( $value['resource']['resourceType'] == 'Encounter' ) {

                        $encounter = new SatusehatEncounter();
                        $encounter->encounter_uuid = $value['fullUrl'];
                        $encounter->subject_reference = $value['resource']['subject']['reference'];
                        $encounter->participant_type = $value['resource']['participant'][0]['type'][0]['coding'][0]['code'];
                        $encounter->participant_individual = $value['resource']['participant'][0]['individual']['reference'];
                        $encounter->location_reference = $value['resource']['location'][0]['location']['reference'];
                        $encounter->identifier = $value['resource']['identifier'][0]['value'];
                        $encounter->status = $value['resource']['status'];
                        $encounter->name_patient = $value['resource']['subject']['display'];
                        $encounter->location_name = $value['resource']['location'][0]['location']['reference'];
                        $encounter->practitioner_name = $value['resource']['participant'][0]['individual']['display'];
                        $encounter->ihs_number_organization = $value['resource']['serviceProvider']['reference'];
                        $encounter->organization_name = isset( $value['resource']['serviceProvider']['display'] ) ? $value['resource']['serviceProvider']['display'] : '';
                        $encounter->periode_start = $value['resource']['period']['start'];
                        $encounter->periode_end = $value['resource']['period']['end'];
                        $encounter->created_at = date('Y-m-d H:i:s');
                        $encounter->updated_at = date('Y-m-d H:i:s');
                        $encounter->save();

                    }else if( $value['resource']['resourceType'] == 'Condition' ) {

                        DB::table('satusehat_condition')
                        ->insert([
                            'encounter' => $value['resource']['encounter']['reference'],
                            'condition_uuid' => $value['fullUrl'],
                            'rank' => $key,
                            'icd10_code' => $value['resource']['code']['coding'][0]['code'],
                            'subject_reference' => $value['resource']['subject']['reference'],
                            'created_at' => date('Y-m-d H:i:s'),
                            'updated_at' => date('Y-m-d H:i:s')
                        ]);

                        $condition = new SatusehatCondition();
                        $condition->encounter = $value['resource']['encounter']['reference'];
                        $condition->condition_uuid = $value['fullUrl'];
                        $condition->rank = $key;
                        $condition->icd10_code = $value['resource']['code']['coding'][0]['code'];
                        $condition->subject_reference = $value['resource']['subject']['reference'];
                        $condition->encounter_name = $value['resource']['encounter']['display'];
                        $condition->icd10_name = $value['resource']['code']['coding'][0]['display'];
                        $condition->name_patient = $value['resource']['subject']['display'];
                        $condition->created_at = date('Y-m-d H:i:s');
                        $condition->updated_at = date('Y-m-d H:i:s');
                        $condition->save();

                    }

                }

            }else{

            }

        }

    }

    public function get_by_id($resource, $id)
    {
        $access_token = $this->token();

        if (! isset($access_token)) {
            return $this->respondError($oauth2);
        }

        $client = new Client();
        $headers = [
            'Authorization' => 'Bearer '.$access_token,
        ];

        $url = $this->base_url.'/'.$resource.'/'.$id;
        $request = new Request('GET', $url, $headers);

        try {
            $res = $client->sendAsync($request)->wait();
            $statusCode = $res->getStatusCode();
            $response = json_decode($res->getBody()->getContents());

            if ($response->resourceType == 'OperationOutcome' | $response->total == 0) {
                $id = 'Error '.$statusCode;
            }
            $this->log($id, $statusCode, 'GET', $url, null, json_encode($response));

            return [$statusCode, $response];
        } catch (ClientException $e) {
            $statusCode = $e->getResponse()->getStatusCode();
            $res = json_decode($e->getResponse()->getBody()->getContents());

            $this->log('Error '.$statusCode, $statusCode, 'GET', $url, null, (array) $res);

            return [$statusCode, $res];
        }
    }

    public function get_by_nik($resource, $nik)
    {
        $access_token = $this->token();
        if (! isset($access_token)) {

            return $this->respondError($oauth2);
        }

        $client = new Client();
        $headers = [
            'Authorization' => 'Bearer '.$access_token,
        ];

        $url = $this->base_url.'/'.$resource.'?identifier=https://fhir.kemkes.go.id/id/nik|'.$nik;
        $request = new Request('GET', $url, $headers);

        try {
            $res = $client->sendAsync($request)->wait();
            $statusCode = $res->getStatusCode();
            $response = json_decode($res->getBody()->getContents());

            if ($response->resourceType == 'OperationOutcome' | $response->total == 0) {
                $id = 'Not Found';
            } else {
                $id = $response->entry['0']->resource->id;
            }
            $this->log($id, $statusCode, 'GET', $url, null, (array) $response);

            return [$statusCode, $response];
        } catch (ClientException $e) {
            $statusCode = $e->getResponse()->getStatusCode();
            $res = json_decode($e->getResponse()->getBody()->getContents());

            $this->log('Error '.$statusCode, $statusCode, 'GET', $url, null, (array) $res);

            return [$statusCode, $res];
        }
    }

    public function ss_post($resource, $body)
    {
        $access_token = $this->token();

        if (! isset($access_token)) {
            return $this->respondError($oauth2);
        }

        $client = new Client();
        $headers = [
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer '.$access_token,
        ];

        $url = $this->base_url.($resource == 'Bundle' ? '' : '/'.$resource);
        $request = new Request('POST', $url, $headers, $body);

        try {
            $res = $client->sendAsync($request)->wait();
            $statusCode = $res->getStatusCode();
            $response = json_decode($res->getBody()->getContents());

            if ($response->resourceType == 'OperationOutcome' || $statusCode >= 400) {
                $id = 'Error '.$statusCode;
            } else {
                if ($resource == 'Bundle') {
                    $id = 'Success '.$statusCode;
                } else {
                    $id = $response->id;
                }
            }
            $this->log($id, $statusCode, 'POST', $url, (array) $body, (array) $response);

            return [$statusCode, $response];
        } catch (ClientException $e) {
            $statusCode = $e->getResponse()->getStatusCode();
            $res = json_decode($e->getResponse()->getBody()->getContents());

            $this->log('Error '.$statusCode, $statusCode, 'POST', $url, (array) $body, (array) $res);

            return [$statusCode, $res];
        }

        $res = $client->sendAsync($request)->wait();
        echo $res->getBody();
    }

    public function ss_put($resource, $id, $body)
    {
        $access_token = $this->token();

        if (! isset($access_token)) {
            return $this->respondError($oauth2);
        }

        $client = new Client();
        $headers = [
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer '.$access_token,
        ];

        $url = $this->base_url.'/'.$resource.'/'.$id;
        $request = new Request('PUT', $url, $headers, $body);

        try {
            $res = $client->sendAsync($request)->wait();
            $statusCode = $res->getStatusCode();
            $response = json_decode($res->getBody()->getContents());

            if ($response->resourceType == 'OperationOutcome' || $statusCode >= 400) {
                $id = 'Error '.$statusCode;
            } else {
                $id = $response->id;
            }
            $this->log($id, $statusCode, 'PUT', $url, (array) $body, (array) $response);

            return [$statusCode, $response];
        } catch (ClientException $e) {
            $statusCode = $e->getResponse()->getStatusCode();
            $res = json_decode($e->getResponse()->getBody()->getContents());

            $this->log('Error '.$statusCode, $statusCode, 'PUT', $url, null, (array) $res);

            return [$statusCode, $res];
        }
    }
}
