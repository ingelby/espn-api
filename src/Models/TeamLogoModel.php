<?php


namespace Ingelby\Espn\Models;


class TeamLogoModel extends AbstractEspnModel
{
    public const SIZE_FULL = 'full';
    
    public ?string $href = null;
    public ?int $width = null;
    public ?int $height = null;

    public function rules()
    {
        return
            [
                [
                    [
                        'href',
                        'width',
                        'height',
                    ],
                    'safe',
                ],
            ];
    }


}
