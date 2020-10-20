<?php


namespace Ingelby\Espn\Models;


class SportModel extends AbstractEspnModel
{
    public ?string $name;
    public ?string $slug;
    public ?int $id;
    public ?string $uid;
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
