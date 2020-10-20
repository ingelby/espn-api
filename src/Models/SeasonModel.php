<?php


namespace Ingelby\Espn\Models;


class SeasonModel extends AbstractEspnModel
{
    public ?string $year;
    public ?int $type;
    public ?string $description;
    public ?string $startDate;
    public ?string $endDate;

    public function rules()
    {
        return
            [
                [
                    [
                        'year',
                        'type',
                        'description',
                        'startDate',
                        'endDate',
                    ],
                    'safe',
                ],
            ];
    }
}
