<?php


namespace Ingelby\Espn\Models;


class LeagueModel extends AbstractEspnModel
{
    public ?string $name = null;
    public ?string $slug = null;
    public ?string $abbreviation = null;
    public ?int $id = null;
    public ?string $uid = null;
    public ?int $groupId = null;
    public ?string $shortName = null;
    protected ?SeasonModel $season;
    protected ?WeekModel $week;

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
     * @param SeasonModel $seasonModel
     */
    public function setSeason(SeasonModel $seasonModel)
    {
        $this->season = $seasonModel;
    }

    /**
     * @return SeasonModel|null
     */
    public function getSeason(): ?SeasonModel
    {
        return $this->season;
    }

    /**
     * @param WeekModel $weekModel
     */
    public function setWeek(WeekModel $weekModel)
    {
        $this->week = $weekModel;
    }

    /**
     * @return WeekModel
     */
    public function getWeek(): ?WeekModel
    {
        return $this->week;
    }
}
