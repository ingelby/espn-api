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
     * @param array $rawResponse
     * @throws EspnClientException
     */
    protected function validateSportsResponse(array $rawResponse)
    {
        if (!array_key_exists('sports', $rawResponse)) {
            throw new EspnClientException(400, 'Error getting sports data, no sports key');
        }
        if (!is_array($rawResponse['sports'])) {
            throw new EspnClientException(400, 'No sports data');
        }
    }

    /**
     * @param array $rawSportResponse
     * @throws EspnClientException
     */
    protected function validateLeagueResponse(array $rawSportResponse)
    {
        if (!array_key_exists('leagues', $rawSportResponse)) {
            throw new EspnClientException(400, 'Error getting league data, no league key');
        }
        if (!is_array($rawSportResponse['leagues']) && count($rawSportResponse['leagues']) > 0) {
            throw new EspnClientException(400, 'No leagues data');
        }
    }

    /**
     * @param array $rawLeagueResponse
     * @throws EspnClientException
     */
    protected function validateEventsResponse(array $rawLeagueResponse)
    {
        if (!array_key_exists('events', $rawLeagueResponse)) {
            throw new EspnClientException(400, 'Error getting events data, no events key');
        }
        if (!is_array($rawLeagueResponse['events'])) {
            throw new EspnClientException(400, 'No events data');
        }
    }

    /**
     * @return SportModel[]
     * @param bool $includeLeageData WARNING: Setting this to true, will likey cause a timeout
     * @throws EspnClientException
     * @throws EspnMappedException
     * @throws EspnServerException
     */
    public function getAll(bool $includeLeageData = false): array
    {
        $rawResponse = $this->get('/v1/sports');

        $this->validateSportsResponse($rawResponse);

        $sports = [];

        foreach ($rawResponse['sports'] as $rawSportData) {
            $sportModel = new SportModel();
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
        $rawResponse = $this->get('/v1/sports/' . $sportSlug);

        $this->validateSportsResponse($rawResponse);

        $rawSportData = current($rawResponse['sports']);

        $sportModel = new SportModel();
        $sportModel->setAttributes($rawSportData);

        if (array_key_exists('leagues', $rawSportData) && is_array($rawSportData['leagues'])) {
            foreach ($rawSportData['leagues'] as $rawLeagueData) {
                $sportModel->addLeague($this->mapLeagueData($rawLeagueData));
            }
        }

        return $sport;
    }

    /**
     * @param string $sportSlug
     * @param string $leagueSlug
     * @return LeagueModel
     * @throws EspnClientException
     * @throws EspnMappedException
     * @throws EspnServerException
     */
    public function getSportsLeague(string $sportSlug, string $leagueSlug): LeagueModel
    {
        $rawResponse = $this->get('/v1/sports/' . $sportSlug . '/' . $leagueSlug);

        $this->validateSportsResponse($rawResponse);
        $rawSportResponse = current($rawResponse['sports']);
        $this->validateLeagueResponse($rawSportResponse);
        $rawLeagueData = current($rawSportResponse['leagues']);

        return $this->mapLeagueData($rawLeagueData);
    }

    /**
     * @param string $sportSlug
     * @param string $leagueSlug
     * @return SportModel
     * @throws EspnClientException
     * @throws EspnMappedException
     * @throws EspnServerException
     */
    public function getSportsLeagueEvents(string $sportSlug, string $leagueSlug): array
    {
        $rawResponse = $this->get('/v1/sports/' . $sportSlug . '/' . $leagueSlug . '/events');

        $this->validateSportsResponse($rawResponse);
        $rawSportResponse = current($rawResponse['sports']);
        $this->validateLeagueResponse($rawSportResponse);
        $rawLeagueData = current($rawSportResponse['leagues']);
        $this->validateEventsResponse($rawLeagueData);

        $rawEventsData = $rawLeagueData['events'];

        return $sport;
    }

    /**
     * @param array $rawLeagueData
     * @return LeagueModel
     */
    protected function mapLeagueData(array $rawLeagueData): LeagueModel
    {
        $leagueModel = new LeagueModel();
        $leagueModel->setAttributes($rawLeagueData);
        if (isset($rawLeagueData['season'])) {
            $seasonModel = new SeasonModel();
            $seasonModel->setAttributes($rawLeagueData['season']);
        }
        if (isset($rawLeagueData['week'])) {
            $weekModel = new WeekModel();
            $weekModel->setAttributes($rawLeagueData['week']);
        }
        return $leagueModel;
    }
}
