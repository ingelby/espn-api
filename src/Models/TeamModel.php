<?php


namespace Ingelby\Espn\Models;


class TeamModel extends AbstractEspnModel
{
    protected const LOGO_CDN_URL = 'https://a.espncdn.com';
    public ?int $id = null;
    public ?int $alternateId = null;
    public ?string $uid = null;
    public ?string $location = null;
    public ?string $name = null;
    public ?string $nickname = null;
    public ?string $abbreviation = null;
    public ?string $color = null;
    public ?string $secondaryColor = null;

    public function init()
    {
        parent::init();
    }

    public function rules()
    {
        return
            [
                [
                    [
                        'id',
                        'alternateId',
                        'uid',
                        'location',
                        'name',
                        'nickname',
                        'abbreviation',
                        'color',
                        'secondaryColor',
                    ],
                    'safe',
                ],
            ];
    }

    /**
     * @param string $leagueSlug
     * @param int    $size
     * @return string
     */
    public function getLogoQuick(string $leagueSlug, int $size = 500)
    {
        $logoUri = '/i/teamlogos/' . $leagueSlug . '/' . $size . '/' . strtolower($this->abbreviation) . '.png';
        return static::LOGO_CDN_URL . $logoUri;
    }
}
