<?php

namespace Niikhsan\SatusehatIntegrate\FHIR;

use Niikhsan\SatusehatIntegrate\Models\Icd10;
use Niikhsan\SatusehatIntegrate\OAuth2Client;
use Niikhsan\SatusehatIntegrate\FHIR\Exception\FHIRMissingProperty;

class Procedure extends OAuth2Client
{
	public $procedure = ['resourceType' => 'Procedure'];

	public function setStatus($status = 'completed')
    {

    	$this->procedure['status'] = $status;

        return $this;

    }

    public function setCategory($category_code, $text = null) {

    	$this->procedure['category'] = [
            'coding' => [
                [
                    'system' => 'http://snomed.info/sct',
                    'code' => $category_code,
                    'display' => $text,
                ],
            ],
            'text' => $text
        ];

        return $this;
    }

    public function addCode($code)
    {
        
        $this->procedure['code']['coding'][] = [
            'system' => 'http://hl7.org/fhir/sid/icd-9-cm',
            'code' => $code['code'],
            'display' => $code['display']
        ];

        return $this;
    }

    public function setSubject(string $subjectId, string $name)
    {
        $this->procedure['subject'] = [
            'reference' => "Patient/{$subjectId}",
            'display' => $name,
        ];

        return $this;
    }

    public function setEncounter( string $resourceType, string $encounterId, string $display = null)
    {
        if( $resourceType == 'Bundle' ) {
            $this->procedure['encounter'] = [
                'reference' => "urn:uuid:{$encounterId}",
                'display' => ! empty($display) ? $display : "Tindakan {$encounterId}",
            ];
        }else{
            $this->procedure['encounter'] = [
                'reference' => "Encounter/{$encounterId}",
                'display' => ! empty($display) ? $display : "Tindakan {$encounterId}",
            ];
        }

        return $this;
    }

    public function performer(string $practitionerId, string $display)
    {
        $this->procedure['performer'][]['actor'] = [
            'reference' => "Practitioner/{$practitionerId}",
            'display' => $display
        ];

        return $this;
    }

    public function reasonCode($code = null, $display = null) {

    	// Look in database if display is null
        $code_check = Icd10::where('icd10_code', $code)->first();

        // Handling if incomplete code / display
        if (! $code_check) {
            return 'Kode ICD-10 invalid';
        }

        $display = $display ? $display : $code_check->icd10_en;

        $this->procedure['reasonCode'][]['coding'][] = [
            'system' => 'http://hl7.org/fhir/sid/icd-10',
            'code' => $code,
            'display' => $display,
        ];

        return $this;
    }

    public function bodySite($bodySite) {

        $this->procedure['bodySite'][] = [
            'coding' => [
                [
                    'system' => 'http://snomed.info/sct',
                    'code' => $bodySite['snomed_code'],
                    'display' => $bodySite['snomed_display']
                ],
            ],
        ];

        return $this;

    }

    public function setNote($note) {

        $this->procedure['note'][] = [
            'text' => $note
        ];

        return $this;

    }

    public function json(): string
    {
        if (! array_key_exists('status', $this->procedure)) {
            throw new FHIRMissingProperty('Status is required.');
        }

        if (! array_key_exists('category', $this->procedure)) {
            throw new FHIRMissingProperty('Category is required.');
        }

        if (! array_key_exists('code', $this->procedure)) {
            throw new FHIRMissingProperty('Code is required.');
        }

        if (! array_key_exists('subject', $this->procedure)) {
            throw new FHIRMissingProperty('Subject is required.');
        }

        if (! array_key_exists('encounter', $this->procedure)) {
            throw new FHIRMissingProperty('Encounter is required.');
        }

        if (! array_key_exists('performer', $this->procedure)) {
            throw new FHIRMissingProperty('Performer is required.');
        }

        if (! array_key_exists('reasonCode', $this->procedure)) {
            throw new FHIRMissingProperty('Reason Code is required.');
        }

        if (! array_key_exists('bodySite', $this->procedure)) {
            throw new FHIRMissingProperty('Body site is required.');
        }

        if (! array_key_exists('note', $this->procedure)) {
            throw new FHIRMissingProperty('Note is required.');
        }

        return json_encode($this->procedure, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    }

    public function post()
    {
        $payload = json_decode($this->json());
        [$statusCode, $res] = $this->ss_post('Procedure', $payload);

        return [$statusCode, $res];
    }

    public function put($id)
    {
        $payload = json_decode($this->json());
        [$statusCode, $res] = $this->ss_put('Procedure', $id, $payload);

        return [$statusCode, $res];
    }
}
