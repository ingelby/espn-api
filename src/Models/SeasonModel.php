<?php


namespace Ingelby\Espn\Models;


class SeasonModel extends AbstractEspnModel
{
    public ?string $year = null;
    public ?int $type = null;
    public ?string $description = null;
    public ?string $startDate = null;
    public ?string $endDate = null;

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
