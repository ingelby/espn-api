<?php


namespace Ingelby\Espn\Models;


class CompetitionModel extends AbstractEspnModel
{
    public ?int $id = null;
    public ?string $uid = null;
    public ?string $date = null;
    public ?bool $timeValid = null;
    public ?int $period = null;
    public ?string $clock = null;

    /**
     * @var CompetitorModel[]
     */
    protected array $competitors = [];

    protected ?CompetitionStatusModel $status = null;

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
                        'period',
                        'clock',
                    ],
                    'safe',
                ],
            ];
    }

    /**
     * @return CompetitorModel[]
     */
    public function getCompetitors(): array
    {
        return $this->competitors;
    }

    /**
     * @param CompetitorModel[] $competitors
     */
    public function setCompetitors(array $competitors): void
    {
        $this->competitors = $competitors;
    }
    
    /**
     * @param CompetitorModel $competitor
     */
    public function addCompetitor(CompetitorModel $competitor): void
    {
        $this->competitors[] = $competitor;
    }

    /**
     * @return CompetitionStatusModel|null
     */
    public function getStatus(): ?CompetitionStatusModel
    {
        return $this->status;
    }

    /**
     * @param CompetitionStatusModel|null $status
     */
    public function setStatus(?CompetitionStatusModel $status): void
    {
        $this->status = $status;
    }
}
