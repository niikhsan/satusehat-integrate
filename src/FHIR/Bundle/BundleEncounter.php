<?php

namespace Niikhsan\SatusehatIntegrate\FHIR\Bundle;

use Niikhsan\SatusehatIntegrate\Models\Icd10;
use Niikhsan\SatusehatIntegrate\OAuth2Client;

class BundleEncounter extends OAuth2Client
{
    public $encounter = [
        'resourceType' => 'Bundle',
        'type' => 'transaction',
        'entry' => []
    ];

    public function addEncounter($uuid, $class, $subject, $participant, $timestamp, $location, $statusHistory, $identifier, $diagnosis)
    {
        $encounter['fullUrl'] = 'urn:uuid:'.$uuid;
        $encounter['resource']['resourceType'] = 'Encounter';
        $encounter['resource']['status'] = 'finished';
        
        $encounter['resource']['period']['start'] = date("Y-m-d\TH:i:sP", strtotime($timestamp['start']));
        $encounter['resource']['period']['end'] = date("Y-m-d\TH:i:sP", strtotime($timestamp['finished']));

        $encounter['resource']['class']['system'] = 'http://terminology.hl7.org/CodeSystem/v3-ActCode';

        // Class
        switch ( $class['code'] ) {
            case 'RAJAL':
                $class_code = 'AMB';
                $class_display = 'ambulatory';
                break;
            case 'IGD':
                $class_code = 'EMER';
                $class_display = 'emergency';
                break;
            case 'RANAP':
                $class_code = 'IMP';
                $class_display = 'inpatient encounter';
                break;
            case 'HOMECARE':
                $class_code = 'HH';
                $class_display = 'home health';
                break;
            case 'TELEKONSULTASI':
                $class_code = 'TELE';
                $class_display = 'teleconsultation';
                break;
            default:
                return 'consultation_method is invalid (Choose RAJAL / IGD / RANAP/ HOMECARE / TELEKONSULTASI)';
        }

        $encounter['resource']['class']['code'] = $class_code;
        $encounter['resource']['class']['display'] = $class_display;

        // Subject
        if (array_key_exists('reference', $subject)) {
            $encounter['resource']['subject']['reference'] = 'Patient/'.$subject['reference'];
            $encounter['resource']['subject']['display'] = $subject['name'];
        } else {
            return 'reference is required';
        }

        // participant
        if (array_key_exists('code', $participant)) {

            $encounter_coding['coding'][] = [
                'system' => 'http://terminology.hl7.org/CodeSystem/v3-ParticipationType',
                'code' => $participant['code'],
                'display' => $participant['display']
            ];

            $encounter_participant['type'][] = $encounter_coding;

        } else {
            return 'code is required';
        }

        // individual
        if (array_key_exists('reference_id', $participant)) {

            $encounter_participant['individual'] = [
                'reference' => 'Practitioner/'.$participant['reference_id'],
                'display' => $participant['reference_name']
            ];

        } else {
            return 'reference id is required';
        }

        $encounter['resource']['participant'][] = $encounter_participant;

        // location
        $encounter_location['location']['reference'] = 'Location/'.$location['location_id'];
        $encounter_location['location']['display'] = $location['location_name'];
        $encounter['resource']['location'][] = $encounter_location;

        // Diagnosis
        if( count($diagnosis) > 0 ) {

            $encounter_diagnosis = [];
            foreach ($diagnosis as $key => $value) {
            
                switch ( $value['diagnosis_code'] ) {
                    case 'AD':
                        $diagnosis_code = 'AD';
                        $diagnosis_display = 'Admission diagnosis';
                        break;
                    case 'DD':
                        $diagnosis_code = 'DD';
                        $diagnosis_display = 'Discharge diagnosis';
                        break;
                    case 'CC':
                        $diagnosis_code = 'CC';
                        $diagnosis_display = 'Chief complaint';
                        break;
                    case 'CM':
                        $diagnosis_code = 'CM';
                        $diagnosis_display = 'Comorbidity diagnosis';
                        break;
                    case 'pre-op':
                        $diagnosis_code = 'pre-op';
                        $diagnosis_display = 'pre-op diagnosis';
                        break;
                    case 'post-op':
                        $diagnosis_code = 'post-op';
                        $diagnosis_display = 'post-op diagnosis';
                        break;
                    case 'billing':
                        $diagnosis_code = 'billing';
                        $diagnosis_display = 'Billing';
                        break;
                    default:
                        return 'consultation_method is invalid (Choose RAJAL / IGD / RANAP/ HOMECARE / TELEKONSULTASI)';
                }

                // condition
                $encounter_diagnosis[$key]['condition']['reference'] = 'urn:uuid:'.$value['condition_code'];
                $encounter_diagnosis[$key]['condition']['display'] = $value['description'];

                // use
                $encounter_diagnosis_coding['system'] = 'http://terminology.hl7.org/CodeSystem/diagnosis-role';
                $encounter_diagnosis_coding['code'] = $value['diagnosis_code'];
                $encounter_diagnosis_coding['display'] = $value['diagnosis_name'];

                $encounter_diagnosis[$key]['use']['coding'][] = $encounter_diagnosis_coding;
                
                // rank
                $encounter_diagnosis[$key]['rank'] = $value['rank'];

            }

        }else{

            // condition
            $encounter_diagnosis[0]['condition']['reference'] = '';
            $encounter_diagnosis[0]['condition']['display'] = '';

            // use
            $encounter_diagnosis_coding['system'] = 'http://terminology.hl7.org/CodeSystem/diagnosis-role';
            $encounter_diagnosis_coding['code'] = '';
            $encounter_diagnosis_coding['display'] = '';

            $encounter_diagnosis[0]['use']['coding'][] = $encounter_diagnosis_coding;

            // rank
            $encounter_diagnosis[0]['rank'] = '';

        }

        $encounter['resource']['diagnosis'] = $encounter_diagnosis;

        // status history
        // Unset if previously set
        if ( array_key_exists('statusHistory', $this->encounter) ) {
            unset( $this->encounter['statusHistory'] );
        }

        // Arrived
        if (array_key_exists('arrived', $statusHistory)) {

            $statusHistory_arrived['status'] = 'arrived';
            $statusHistory_arrived['period']['start'] = date("Y-m-d\TH:i:sP", strtotime($statusHistory['arrived']));
        } else {
            return 'arrived is required';
        }

        // In-progress
        if (array_key_exists('inprogress', $statusHistory)) {

            $statusHistory_inprogress['status'] = 'in-progress';
            $statusHistory_inprogress['period']['start'] = date("Y-m-d\TH:i:sP", strtotime($statusHistory['inprogress']));

            $statusHistory_arrived['period']['end'] = date("Y-m-d\TH:i:sP", strtotime($statusHistory['inprogress']));
        }

        // Finished
        if (array_key_exists('finished', $statusHistory)) {

            $statusHistory_finished['status'] = 'finished';
            $statusHistory_finished['period']['start'] = date("Y-m-d\TH:i:sP", strtotime($timestamp['finished']));
            $statusHistory_finished['period']['end'] = date("Y-m-d\TH:i:sP", strtotime($timestamp['finished']));

            $statusHistory_inprogress['period']['end'] = date("Y-m-d\TH:i:sP", strtotime($timestamp['finished']));
        }

        // Add all timestamp statusHistory
        $encounter['resource']['statusHistory'][] = $statusHistory_arrived;
        $encounter['resource']['statusHistory'][] = $statusHistory_inprogress;
        $encounter['resource']['statusHistory'][] = $statusHistory_finished;
        
        // service provider
        $encounter['resource']['serviceProvider']['reference'] = 'Organization/'.$this->organization_id;
        
        // identifier
        $encounter_identifier['system'] = 'http://sys-ids.kemkes.go.id/encounter/'.$this->organization_id;
        $encounter_identifier['value'] = $identifier['identifier'];
        $encounter['resource']['identifier'][] = $encounter_identifier;
        
        $encounter['request'] = [
            'method' => 'POST',
            'url' => 'Encounter'
        ];

        $this->encounter['entry'][] = $encounter;
    }

    public function addCondition($uuid, $clinical_status, $category = 'diagnosis', $icd_code, $subject, $encounterCode) {

        $encounter['fullUrl'] = 'urn:uuid:'.$uuid;
        $encounter['resource']['resourceType'] = 'Condition';

        // clinical_status
        switch ( strtolower($clinical_status) ) {
            case 'active':
                $code_status = 'active';
                $display_status = 'Active';
                break;
            case 'recurrence':
                $code_status = 'recurrence';
                $display_status = 'Recurrence';
                break;
            case 'inactive':
                $code_status = 'inactive';
                $display_status = 'Inactive';
                break;
            case 'remission':
                $code_status = 'remission';
                $display_status = 'Remission';
                break;
            case 'resolved':
                $code_status = 'resolved';
                $display_status = 'Resolved';
                break;
            default:
                $code_status = 'active';
                $display_status = 'Active';
        }

        // clinicalStatus
        $encounter['resource']['clinicalStatus']['coding'][] = [
            'system' => 'http://terminology.hl7.org/CodeSystem/condition-clinical',
            'code' => $code_status,
            'display' => $display_status,
        ];

        // category
        $category = strtolower($category);
        switch ($category) {
            case 'diagnosis':
                $code_category = 'encounter-diagnosis';
                $display_category = 'Encounter Diagnosis';
                break;
            case 'keluhan':
                $code_category = 'problem-list-item';
                $display_category = 'Problem List Item';
                break;
            default:
                $code_category = 'encounter-diagnosis';
                $display_category = 'Encounter Diagnosis';
        }

        $encounter['resource']['category'][] = [
            'coding' => [
                [
                    'system' => 'http://terminology.hl7.org/CodeSystem/condition-category',
                    'code' => $code_category,
                    'display' => $display_category,
                ],
            ],
        ];

        // code
        // Look in database if display is null
        $code_check = Icd10::where('icd10_code', $icd_code['icd_code'])->first();

        // Handling if incomplete code / display
        if (! $code_check) {
            return 'Kode ICD-10 invalid';
        }

        $display_icd = $icd_code['display'] ? $icd_code['display'] : $code_check->icd10_en;

        $encounter['resource']['code']['coding'][] = [
            'system' => 'http://hl7.org/fhir/sid/icd-10',
            'code' => $icd_code['icd_code'],
            'display' => $display_icd
        ];

        // Subject / data pasien
        $encounter['resource']['subject']['reference'] = 'Patient/'.$subject['reference'];
        $encounter['resource']['subject']['display'] = $subject['name'];

        // Hubungkan dengan Encounter
        $encounter['resource']['encounter']['reference'] = 'urn:uuid:' . $encounterCode['encounter_code'];
        $encounter['resource']['encounter']['display'] = $encounterCode['description'] ? $encounterCode['description'] : 'Kunjungan ' . $encounterCode['encounter_code'];

        $encounter['request'] = [
            'method' => 'POST',
            'url' => 'Condition'
        ];

        $this->encounter['entry'][] = $encounter;
    }

    public function json()
    {
        // Status is required
        if (! array_key_exists('status', $this->encounter)) {
            return 'Please use encounter->statusHistory([timestamp array]) to add the status';
        }

        // Class is required
        if (! array_key_exists('class', $this->encounter)) {
            return 'Please use encounter->setConsultationMethod($method) to pass the data';
        }

        // Subject is required
        if (! array_key_exists('subject', $this->encounter)) {
            return 'Please use encounter->setSubject($subjectId, $name) to pass the data';
        }

        // Participant is required
        if (! array_key_exists('participant', $this->encounter)) {
            return 'Please use encounter->addParticipant($participantId, $name) to pass the data';
        }

        // Location is required
        if (! array_key_exists('location', $this->encounter)) {
            return 'Please use encounter->addLocation($locationId, $name) to pass the data';
        }

        // Add default ServiceProvider
        if (! array_key_exists('serviceProvider', $this->encounter)) {
            $this->setServiceProvider();
        }

        return json_encode($this->encounter, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    }

    public function post()
    {
        $payload = json_decode($this->json());
        [$statusCode, $res] = $this->ss_post('Bundle', $payload);

        return [$statusCode, $res];
    }

    public function put($id)
    {
        $payload = json_decode($this->json());
        [$statusCode, $res] = $this->ss_put('Bundle', $id, $payload);

        return [$statusCode, $res];
    }
}
