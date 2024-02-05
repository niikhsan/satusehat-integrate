<?php

namespace Niikhsan\SatusehatIntegrate\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Niikhsan\SatusehatIntegrate\Models\SatusehatComposition.
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
class SatusehatComposition extends Model
{
    public $table;

    public $guarded = [];

    public function __construct(array $attributes = [])
    {
        if (! isset($this->connection)) {
            $this->setConnection(config('satusehatintegration.database_connection'));
        }

        if (! isset($this->table)) {
            $this->setTable('satusehat_composition');
        }

        parent::__construct($attributes);
    }

    protected $primaryKey = 'id';

    public $incrementing = false;

}
