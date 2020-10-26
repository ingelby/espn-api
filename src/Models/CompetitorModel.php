<?php


namespace Ingelby\Espn\Models;


class CompetitorModel extends AbstractEspnModel
{
    public ?string $type = null;
    public ?int $score = null;
    public ?string $homeAway = null;

    protected ?TeamModel $team = null;

    protected ?EnrichedTeamModel $enrichedTeam = null;

    /**
     * @return EnrichedTeamModel|null
     */
    public function getEnrichedTeam(): ?EnrichedTeamModel
    {
        return $this->enrichedTeam;
    }

    /**
     * @param EnrichedTeamModel|null $enrichedTeam
     */
    public function setEnrichedTeam(?EnrichedTeamModel $enrichedTeam): void
    {
        $this->enrichedTeam = $enrichedTeam;
    }

    /**
     * @return TeamModel|null
     */
    public function getTeam(): ?TeamModel
    {
        return $this->team;
    }

    /**
     * @param TeamModel|null $team
     */
    public function setTeam(?TeamModel $team): void
    {
        $this->team = $team;
    }

    public function rules()
    {
        return
            [
                [
                    [
                        'type',
                        'score',
                        'homeAway',
                    ],
                    'safe',
                ],
            ];
    }
}
