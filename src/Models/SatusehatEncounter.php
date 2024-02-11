<?php

namespace Niikhsan\SatusehatIntegrate\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Niikhsan\SatusehatIntegrate\Models\SatusehatEncounter.
 *
 * @property string|null $response_id
 * @property string $action
 * @property string $url
 * @property array|null $payload
 * @property array $response
 * @property string $user_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class SatusehatEncounter extends Model
{
    public $table;

    public $guarded = [];

    public function __construct(array $attributes = [])
    {
        if (! isset($this->connection)) {
            $this->setConnection(config('satusehatintegration.database_connection'));
        }

        if (! isset($this->table)) {
            $this->setTable( config('satusehatintegration.encounter_table_name') );
        }

        parent::__construct($attributes);
    }

    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $casts = [
        'encounter_uuid' => 'string',
        'subject_reference' => 'string',
        'participant_type' => 'string',
        'participant_individual' => 'string',
        'location_reference' => 'string',
        'identifier' => 'string',
        'status' => 'string',
        'name_patient' => 'string',
        'location_name' => 'string',
        'practitioner_name' => 'string',
        'ihs_number_organization' => 'string',
        'organization_name' => 'string'
    ];

}
