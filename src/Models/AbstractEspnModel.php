<?php

namespace Ingelby\Espn\Models;

use yii\base\Model;

abstract class AbstractEspnModel extends Model
{
    public const SCENARIO_GET = 'SCENARIO_GET';
    public const SCENARIO_POST = 'SCENARIO_POST';
    public const SCENARIO_PUT = 'SCENARIO_PUT';
}
