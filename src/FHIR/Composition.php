<?php

namespace Niikhsan\SatusehatIntegrate\FHIR;

use Niikhsan\SatusehatIntegrate\Models\Icd10;
use Niikhsan\SatusehatIntegrate\OAuth2Client;

class Composition extends OAuth2Client
{
	public $composition = ['resourceType' => 'Composition'];

	public function setStatus($status = 'final')
    {
    	$this->composition['status'] = $status;

        return $this;

    }

	public function setIdentifier( $registration_id )
    {
        $this->composition['identifier'][] = [
        	'system' => 'http://sys-ids.kemkes.go.id/encounter/'.$this->organization_id,
        	'value' => $registration_id
        ];

        return $this;

    }

    public function setType($loinc_code) {

    	$this->composition['type'] = [
            'coding' => [
                [
                    'system' => 'http://loinc.org',
                    'code' => $loinc_code['code'],
                    'display' => $loinc_code['display']
                ],
            ]
        ];

        return $this;
    }

    public function setCategory($loinc_code) {

    	$this->composition['category'][] = [
            'coding' => [
                [
                    'system' => 'http://loinc.org',
                    'code' => $loinc_code['code'],
                    'display' => $loinc_code['display'],
                ],
            ]
        ];

        return $this;
    }

    public function setSubject(string $subjectId, string $name)
    {
        $this->composition['subject'] = [
            'reference' => "Patient/{$subjectId}",
            'display' => $name,
        ];

        return $this;
    }

    public function setEncounter( string $resourceType, string $encounterId, string $display = null)
    {
        if( $resourceType == 'Bundle' ) {
            $this->composition['encounter'] = [
                'reference' => "urn:uuid:{$encounterId}",
                'display' => ! empty($display) ? $display : "Kunjungan {$encounterId}",
            ];
        }else{
            $this->composition['encounter'] = [
                'reference' => "Encounter/{$encounterId}",
                'display' => ! empty($display) ? $display : "Kunjungan {$encounterId}",
            ];
        }

        return $this;
    }

    public function setDateComp($onset_date_time = null)
    {
        $this->composition['date'] = $onset_date_time ?
                                                date("Y-m-d\TH:i:sP", strtotime($onset_date_time)) :
                                                date("Y-m-d\TH:i:sP");
    }

    public function setAuthor(string $practitionerId, string $display)
    {
        $this->composition['author'][] = [
            'reference' => "Practitioner/{$practitionerId}",
            'display' => $display
        ];

        return $this;
    }

    public function setTitle(string $title)
    {
        $this->composition['title'] = $title;

        return $this;
    }

    public function setCustodian()
    {
        $this->composition['custodian'] = [
        	'reference' => "Organization/".$this->organization_id
        ];

        return $this;
    }

    public function setSection($code)
    {
        
        $section['code']['coding'][] = [
            'system' => 'http://loinc.org',
            'code' => $code['code'],
            'display' => $code['display']
        ];

        $section['text'] = [
            'status' => $code['status'],
            'div' => $code['div'],
        ];

        return $this->composition['section'][] = $section;
    }

    public function json(): string
    {
        if (! array_key_exists('identifier', $this->composition)) {
            throw new FHIRMissingProperty('identifier is required.');
        }

        if (! array_key_exists('status', $this->composition)) {
            throw new FHIRMissingProperty('status is required.');
        }

        if (! array_key_exists('type', $this->composition)) {
            throw new FHIRMissingProperty('type is required.');
        }

        if (! array_key_exists('category', $this->composition)) {
            throw new FHIRMissingProperty('category is required.');
        }

        if (! array_key_exists('subject', $this->composition)) {
            throw new FHIRMissingProperty('subject is required.');
        }

        if (! array_key_exists('encounter', $this->composition)) {
            throw new FHIRMissingProperty('encounter is required.');
        }

        if (! array_key_exists('date', $this->composition)) {
            throw new FHIRMissingProperty('date Code is required.');
        }

        if (! array_key_exists('author', $this->composition)) {
            throw new FHIRMissingProperty('author site is required.');
        }

        if (! array_key_exists('title', $this->composition)) {
            throw new FHIRMissingProperty('title is required.');
        }

        if (! array_key_exists('custodian', $this->composition)) {
            throw new FHIRMissingProperty('custodian is required.');
        }

        if (! array_key_exists('section', $this->composition)) {
            throw new FHIRMissingProperty('section is required.');
        }

        return json_encode($this->composition, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    }

    public function post()
    {
        $payload = json_decode($this->json());
        [$statusCode, $res] = $this->ss_post('Composition', $payload);

        return [$statusCode, $res];
    }

    public function put($id)
    {
        $payload = json_decode($this->json());
        [$statusCode, $res] = $this->ss_put('Composition', $id, $payload);

        return [$statusCode, $res];
    }

}
