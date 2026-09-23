<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Un grupo de ajustes del sitio («promo», «packages», «season», «templates»).
 * Se leen y se escriben desde App\Support\SiteSettings, que es quien los aplica sobre la config.
 */
class SiteSetting extends Model
{
    protected $primaryKey = 'key';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = ['key', 'value'];

    protected $casts = ['value' => 'array'];
}
