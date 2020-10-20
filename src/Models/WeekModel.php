<?php


namespace Ingelby\Espn\Models;


class WeekModel extends AbstractEspnModel
{
    public ?int $number;
    public ?string $startDate;
    public ?string $endDate;

    public function rules()
    {
        return
            [
                [
                    [
                        'number',
                        'startDate',
                        'endDate',
                    ],
                    'safe',
                ],
            ];
    }
}
