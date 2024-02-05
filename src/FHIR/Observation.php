<?php

namespace Niikhsan\SatusehatIntegrate\FHIR;

use Niikhsan\SatusehatIntegrate\FHIR\Enum\ObservationCategory;
use Niikhsan\SatusehatIntegrate\FHIR\Enum\ObservationCode;
use Niikhsan\SatusehatIntegrate\FHIR\Exception\FHIRMissingProperty;
use Niikhsan\SatusehatIntegrate\OAuth2Client;

class Observation extends OAuth2Client
{
    public $observation = ['resourceType' => 'Observation'];

    /**
     * Sets a status to the observation.
     *
     * @param  string  $status The status to add. Defaults to "final".
     * @return Observation Returns the current instance of the Observation class.
     */
    public function setStatus($status = 'final'): Observation
    {
        switch ($status) {
            case 'registered':
                $code = 'registered';
                break;
            case 'preliminary':
                $code = 'preliminary';
                break;
            case 'final':
                $code = 'final';
                break;
            case 'amended':
                $code = 'amended';
                break;
            case 'corrected':
                $code = 'corrected';
                break;
            case 'cancelled':
                $code = 'cancelled';
                break;
            case 'entered-in-error':
                $code = 'entered-in-error';
                break;
            case 'unknown':
                $code = 'unknown';
                break;
            default:
                $code = 'final';
        }

        $this->observation['status'] = $code;

        return $this;
    }

    /**
     * Adds a category to the observation.
     *
     * @param  string  $code The code of the category.
     * @param  string  $display The display name of the category.
     * @return Observation The updated observation object.
     */
    public function addCategory($category)
    {
        switch ($category) {
            case 'vital-signs':
                $category_code = 'vital-signs';
                $category_display = 'Vital Signs';
                break;
            case 'social-history':
                $category_code = 'social-history';
                $category_display = 'Social History';
                break;
            case 'imaging':
                $category_code = 'imaging';
                $category_display = 'Imaging';
                break;
            case 'laboratory':
                $category_code = 'laboratory';
                $category_display = 'Laboratory';
                break;
            case 'procedure':
                $category_code = 'procedure';
                $category_display = 'Procedure';
                break;
            case 'survey':
                $category_code = 'survey';
                $category_display = 'Survey';
                break;
            case 'exam':
                $category_code = 'exam';
                $category_display = 'Exam';
                break;
            case 'therapy':
                $category_code = 'therapy';
                $category_display = 'Therapy';
                break;
            case 'activity':
                $category_code = 'activity';
                $category_display = 'Activity';
                break;
            default:
                $category_code = 'vital-signs';
                $category_display = 'Vital Signs';
        }

        // NOTE: we currently only support 'vital-signs'
        $this->observation['category'][] = [
            'coding' => [
                [
                    'system' => 'http://terminology.hl7.org/CodeSystem/observation-category',
                    'code' => $category_code,
                    'display' => $category_display,
                ],
            ],
        ];

        return $this;
    }

    /**
     * Adds an observation code to the observation.
     * If more than one code is added, the last one will be used.
     *
     * @param  ObservationCode  $code The valid observation code to add.
     * @return Observation Returns the updated observation object.
     */
    public function addCode($code)
    {
        switch ($code) {
            case '8480-6':
                $loinc_code = '8480-6';
                $code_display = 'Systolic blood pressure';
                break;
            case '8462-4':
                $loinc_code = '8462-4';
                $code_display = 'Diastolic blood pressure';
                break;
            case '8867-4':
                $loinc_code = '8867-4';
                $code_display = 'Heart rate';
                break;
            case '9279-1':
                $loinc_code = '9279-1';
                $code_display = 'Respiratory rate';
                break;
            case '8310-5':
                $loinc_code = '8310-5';
                $code_display = 'Body temperature';
                break;
            default:
                $loinc_code = '8310-5';
                $code_display = 'Body temperature';
        }

        $this->observation['code']['coding'][] = [
            'system' => 'http://loinc.org',
            'code' => $loinc_code,
            'display' => $code_display
        ];

        return $this;
    }

    /**
     * Sets the subject of the observation.
     *
     * @param  string  $subjectId The Satu Sehat ID of the subject.
     * @param  string  $name The name of the subject.
     * @return Observation The current observation instance.
     */
    public function setSubject(string $subjectId, string $name): Observation
    {
        $this->observation['subject'] = [
            'reference' => "Patient/{$subjectId}",
            'display' => $name,
        ];

        return $this;
    }

    public function performer(string $practitionerId): Observation
    {
        $this->observation['performer'][] = [
            'reference' => "Practitioner/{$practitionerId}"
        ];

        return $this;
    }

    public function observationDate($effectiveDateTime) {

        $this->observation['effectiveDateTime'] = date("Y-m-d\TH:i:sP", strtotime($effectiveDateTime['effectiveDateTime']));
        $this->observation['issued'] = date("Y-m-d\TH:i:sP", strtotime($effectiveDateTime['issued']));

        return $this;

    }

    public function bodySite($bodySite) {

        $this->observation['bodySite'][] = [
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

    public function valueQuantity($valueQuantity)
    {
        $this->observation['valueQuantity'] = [
            'value' => $valueQuantity['value'],
            'unit' => $valueQuantity['unit'],
            'system' => 'http://unitsofmeasure.org',
            'code' => $valueQuantity['code']
        ];

        return $this;
    }

    public function interpretation($interpretation) {

        $this->observation['interpretation'][] = [
            'coding' => [
                [
                    'system' => 'http://terminology.hl7.org/CodeSystem/v3-ObservationInterpretation',
                    'code' => $interpretation['code'],
                    'display' => $interpretation['display']
                ],
            ],
            'text' => 'test test'
        ];

        return $this;

    }

    /**
     * Visit data where observation results are obtained
     *
     * @param  string  $encounterId The Satu Sehat Encounter ID of the encounter.
     * @param  string  $display The display name of the encounter.
     */
    public function setEncounter( string $resourceType, string $encounterId, string $display = null): Observation
    {
        if( $resourceType == 'Bundle' ) {
            $this->observation['encounter'] = [
                'reference' => "urn:uuid:{$encounterId}",
                'display' => ! empty($display) ? $display : "Kunjungan {$encounterId}",
            ];
        }else{
            $this->observation['encounter'] = [
                'reference' => "Encounter/{$encounterId}",
                'display' => ! empty($display) ? $display : "Kunjungan {$encounterId}",
            ];
        }

        return $this;
    }

    /**
     * Returns the JSON representation of the observation.
     *
     * @return string The JSON representation of the observation.
     */
    public function json(): string
    {
        if (! array_key_exists('status', $this->observation)) {
            throw new FHIRMissingProperty('Status is required.');
        }

        if (! array_key_exists('category', $this->observation)) {
            throw new FHIRMissingProperty('Category is required.');
        }

        if (! array_key_exists('code', $this->observation)) {
            throw new FHIRMissingProperty('Code is required.');
        }

        if (! array_key_exists('subject', $this->observation)) {
            throw new FHIRMissingProperty('Subject is required.');
        }

        if (! array_key_exists('encounter', $this->observation)) {
            throw new FHIRMissingProperty('Encounter is required.');
        }

        return json_encode($this->observation, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    }

    public function post()
    {
        $payload = json_decode($this->json());
        [$statusCode, $res] = $this->ss_post('Observation', $payload);

        return [$statusCode, $res];
    }

    public function put($id)
    {
        $payload = json_decode($this->json());
        [$statusCode, $res] = $this->ss_put('Observation', $id, $payload);

        return [$statusCode, $res];
    }
}
