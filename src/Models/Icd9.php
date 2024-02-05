<?php

namespace Niikhsan\SatusehatIntegrate\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Niikhsan\SatusehatIntegrate\Models\Icd9.
 *
 * @property string $icd9_code
 * @property string $icd9_en
 * @property string $icd9_id
 * @property bool $active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class Icd9 extends Model
{
    public $table;

    public $guarded = [];

    public function __construct(array $attributes = [])
    {
        if (! isset($this->connection)) {
            $this->setConnection(config('satusehatintegration.database_connection'));
        }

        if (! isset($this->table)) {
            $this->setTable(config('satusehatintegration.icd9_table_name'));
        }

        parent::__construct($attributes);
    }

    protected $primaryKey = 'icd9_code';

    public $incrementing = false;

    protected $casts = ['icd9_code' => 'string', 'icd9_en' => 'string', 'icd9_id' => 'string'];
}
