<?php

namespace Niikhsan\SatusehatIntegrate\FHIR;

use Niikhsan\SatusehatIntegrate\OAuth2Client;

class Practitioner extends OAuth2Client
{
	public $practitioner = ['resourceType' => 'Practitioner'];

	public function getPractitionerById( $ihs_number ) {

		if ( ! $ihs_number ) {
            throw new FHIRMissingProperty('ihs number is required.');
        }

	}
}
