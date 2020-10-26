<?php


namespace Ingelby\Espn\Models;


class SportModel extends AbstractEspnModel
{
    public ?string $name = null;
    public ?string $slug = null;
    public ?int $id = null;
    public ?string $uid = null;
    public array $links = [];

    /**
     * @var LeagueModel[]
     */
    protected array $leagues = [];

    public function rules()
    {
        return
            [
                [
                    [
                        'name',
                        'slug',
                        'id',
                        'uid',
                    ],
                    'safe',
                ],
                [
                    [
                        'name',
                        'slug',
                        'id',
                        'uid',
                    ],
                    'required',
                ],
            ];
    }

    /**
     * @return LeagueModel[]
     */
    public function getLeagues(): array
    {
        return $this->leagues;
    }

    /**
     * @param LeagueModel[] $leagues
     */
    public function setLeagues(array $leagues): void
    {
        $this->leagues = $leagues;
    }

    /**
     * @param LeagueModel $league
     */
    public function addLeague(LeagueModel $league): void
    {
        $this->leagues[] = $league;
    }
}
