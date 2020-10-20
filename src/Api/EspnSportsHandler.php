<?php

namespace Ingelby\Espn\Api;

use Ingelby\Espn\Exceptions\EspnClientException;
use Ingelby\Espn\Exceptions\EspnMappedException;
use Ingelby\Espn\Exceptions\EspnServerException;
use Ingelby\Espn\Models\LeagueModel;
use Ingelby\Espn\Models\SeasonModel;
use Ingelby\Espn\Models\SportModel;
use Ingelby\Espn\Models\WeekModel;

class EspnSportsHandler extends AbstractHandler
{
    /**
     * @return SportModel[]
     * @param bool $includeLeageData WARNING: Setting this to true, will likey cause a timeout
     * @throws EspnClientException
     * @throws EspnMappedException
     * @throws EspnServerException
     */
    public function getAll(bool $includeLeageData = false): array
    {
        $response = $this->get('/v1/sports');

        if (!array_key_exists('sports', $response)) {
            throw new EspnClientException(400, 'Error getting sports data, no sports key');
        }
        if (!is_array($response['sports'])) {
            throw new EspnClientException(400, 'No sports data');
        }

        $sports = [];

        foreach ($response['sports'] as $rawSportData) {
            $sportModel = new SportModel();
            $sportModel->setScenario(SportModel::SCENARIO_GET);
            $sportModel->setAttributes($rawSportData);
            if (array_key_exists('leagues', $rawSportData) && is_array($rawSportData['leagues']) && $includeLeageData) {
                foreach ($rawSportData['leagues'] as $rawLeagueData) {
                    $sportModel->addLeague($this->mapLeagueData($rawLeagueData));
                }
            }
            $sports[] = $sportModel;
        }


        return $sports;
    }

    /**
     * @param string $sportSlug
     * @return SportModel
     * @throws EspnClientException
     * @throws EspnMappedException
     * @throws EspnServerException
     */
    public function getSport(string $sportSlug): array
    {
        $response = $this->get('/v1/sports/' . $sportSlug);

        if (!array_key_exists('sports', $response)) {
            throw new EspnClientException(400, 'Error getting sports data, no sports key');
        }
        if (!is_array($response['sports']) && count($response['sports']) > 0) {
            throw new EspnClientException(400, 'No sports data');
        }
        $rawSportData = current($response['sports']);

        $sportModel = new SportModel();
        $sportModel->setScenario(SportModel::SCENARIO_GET);
        $sportModel->setAttributes($rawSportData);

        if (array_key_exists('leagues', $rawSportData) && is_array($rawSportData['leagues'])) {
            foreach ($rawSportData['leagues'] as $rawLeagueData) {
                $sportModel->addLeague($this->mapLeagueData($rawLeagueData));
            }
        }

        return $sport;
    }

    /**
     * @param array $rawLeagueData
     * @return LeagueModel
     */
    protected function mapLeagueData(array $rawLeagueData): LeagueModel
    {
        $leagueModel = new LeagueModel();
        $leagueModel->setScenario(LeagueModel::SCENARIO_GET);
        $leagueModel->setAttributes($rawLeagueData);
        if (isset($rawLeagueData['season'])) {
            $seasonModel = new SeasonModel();
            $seasonModel->setScenario(SeasonModel::SCENARIO_GET);
            $seasonModel->setScenario($rawLeagueData['season']);
        }
        if (isset($rawLeagueData['week'])) {
            $weekModel = new WeekModel();
            $weekModel->setScenario(SeasonModel::SCENARIO_GET);
            $weekModel->setScenario($rawLeagueData['week']);
        }
        return $leagueModel;
    }
}
