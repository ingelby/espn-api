<?php


namespace Ingelby\Espn\Models;


class EventModel extends AbstractEspnModel
{
    public ?int $id = null;
    public ?string $uid = null;
    public ?string $date = null;
    public ?bool $timeValid = null;
    public ?string $type = null;
    public ?int $eventSourceId = null;
    public ?string $eventSource = null;
    public ?int $statSourceId = null;
    public ?string $statSource = null;

    protected ?SeasonModel $season = null;
    protected ?WeekModel $week = null;
    /**
     * @var CompetitionModel[]
     */
    protected array $competitions = [];

    public function rules()
    {
        return
            [
                [
                    [
                        'id',
                        'uid',
                        'date',
                        'timeValid',
                        'type',
                        'eventSourceId',
                        'eventSource',
                        'statSourceId',
                        'statSource',
                    ],
                    'safe',
                ],
                [
                    [
                        'id',
                        'uid',
                        'date',
                        'timeValid',
                    ],
                    'required',
                ],
            ];
    }

    /**
     * @param int[] $teamIds
     * @return bool
     */
    public function hasTeamIdsCompeting(array $teamIds)
    {
        \Yii::info('Searching for teamIds: ' . implode(', ', $teamIds) . ' in eventId: ' . $this->id);
        if (empty($this->getCompetitions())) {
            \Yii::info('No competitions for event: ' . $eventModel->id);
            return false;
        }
        $competitions = $this->getCompetitions();
        foreach ($competitions as $competition) {
            if (empty($competition->getCompetitors())) {
                \Yii::info('No competitors for event: ' . $eventModel->id . ' competition: ' . $competition->id);
                continue;
            }

            foreach ($competition->getCompetitors() as $competitor) {
                if (null === $team = $competitor->getTeam()) {
                    continue;
                }
                if (in_array($team->id, $teamIds, false)) {
                    \Yii::info('EventId: ' . $this->id . ' contains team: ' . $team->id);
                    return true;
                }
            }
        }
        \Yii::info('EventId: ' . $this->id . ' does not contain searched team');
        return false;
    }

    /**
     * @param EventModel[] $eventModels
     * @return TeamModel[]
     */
    public static function getTeamsFromEvents(array $eventModels): array
    {
        $teams = [];
        \Yii::info('Getting teams from events');
        foreach ($eventModels as $eventModel) {
            if (empty($eventModel->getCompetitions())) {
                \Yii::warning('No competitions for event: ' . $eventModel->id);
                continue;
            }
            $competitions = $eventModel->getCompetitions();
            foreach ($competitions as $competition) {
                if (empty($competition->getCompetitors())) {
                    \Yii::warning('No competitors for event: ' . $eventModel->id . ' competition: ' . $competition->id);
                    continue;
                }

                foreach ($competition->getCompetitors() as $competitor) {
                    $teams[$competitor->getTeam()->id] = $competitor->getTeam();
                }
            }
        }

        return $teams;
    }

    /**
     * @param EventModel[]        $eventModels
     * @param EnrichedTeamModel[] $enrichedTeamModels
     */
    public static function setEnrichedTeamsForEvents(array &$eventModels, array $enrichedTeamModels): void
    {
        \Yii::info('Getting teams from events, enriched team ids: ' . implode(', ', array_keys($enrichedTeamModels)));
        foreach ($eventModels as $eventModel) {
            if (empty($eventModel->getCompetitions())) {
                \Yii::warning('No competitions for event: ' . $eventModel->id);
                continue;
            }
            $competitions = $eventModel->getCompetitions();
            foreach ($competitions as $competition) {
                if (empty($competition->getCompetitors())) {
                    \Yii::warning('No competitors for event: ' . $eventModel->id . ' competition: ' . $competition->id);
                    continue;
                }

                foreach ($competition->getCompetitors() as $competitor) {
                    $teamId = $competitor->getTeam()->id;
                    if (!array_key_exists($teamId, $enrichedTeamModels)) {
                        \Yii::warning('No enriched team model for team id: ' . $teamId);
                        continue;
                    }

                    $enrichedTeamModel = $enrichedTeamModels[$teamId];

                    $competitor->setEnrichedTeam($enrichedTeamModels[$teamId]);
                    \Yii::info('Set enriched team id: ' . $enrichedTeamModel->id . ' successfully');

                }
            }
        }
    }

    /**
     * @return CompetitionModel[]
     */
    public function getCompetitions(): array
    {
        return $this->competitions;
    }

    /**
     * @param CompetitionModel[] $competitions
     */
    public function setCompetitions(array $competitions): void
    {
        $this->competitions = $competitions;
    }

    /**
     * @param CompetitionModel $competition
     */
    public function addCompetition(CompetitionModel $competition): void
    {
        $this->competitions[] = $competition;
    }


    /**
     * @return WeekModel|null
     */
    public function getWeek(): ?WeekModel
    {
        return $this->week;
    }

    /**
     * @param WeekModel|null $week
     */
    public function setWeek(?WeekModel $week): void
    {
        $this->week = $week;
    }

    /**
     * @return SeasonModel|null
     */
    public function getSeason(): ?SeasonModel
    {
        return $this->season;
    }

    /**
     * @param SeasonModel|null $season
     */
    public function setSeason(?SeasonModel $season): void
    {
        $this->season = $season;
    }
}
