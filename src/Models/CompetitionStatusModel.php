<?php


namespace Ingelby\Espn\Models;


class CompetitionStatusModel extends AbstractEspnModel
{
    public ?int $id = null;
    public ?string $description = null;
    public ?string $detail = null;
    public ?string $shortDetail = null;
    public ?string $state = null;

    public function rules()
    {
        return
            [
                [
                    [
                        'id',
                        'description',
                        'detail',
                        'shortDetail',
                        'state',
                    ],
                    'safe',
                ],
            ];
    }
}
