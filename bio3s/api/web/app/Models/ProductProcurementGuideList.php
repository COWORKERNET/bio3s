<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class productProcurementGuideList extends Model
{
    use HasFactory;

    protected $connection = 'mysql';

    protected $table = 'productProcurementGuideList';

    protected function serializeDate(\DateTimeInterface $date) {
        return $date->format('Y-m-d H:i:s');
    }
}
