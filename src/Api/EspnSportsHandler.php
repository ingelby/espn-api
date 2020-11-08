<?php

namespace Ingelby\Espn\Api;

use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\TransferException;
use Ingelby\Espn\Constants\SportSlug;
use Ingelby\Espn\Exceptions\EspnClientException;
use Ingelby\Espn\Exceptions\EspnMappedException;
use Ingelby\Espn\Exceptions\EspnServerException;
use Ingelby\Espn\Models\CompetitionModel;
use Ingelby\Espn\Models\CompetitionStatusModel;
use Ingelby\Espn\Models\CompetitorModel;
use Ingelby\Espn\Models\EnrichedTeamModel;
use Ingelby\Espn\Models\EventModel;
use Ingelby\Espn\Models\LeagueModel;
use Ingelby\Espn\Models\SeasonModel;
use Ingelby\Espn\Models\SportModel;
use Ingelby\Espn\Models\TeamLogoModel;
use Ingelby\Espn\Models\TeamModel;
use Ingelby\Espn\Models\WeekModel;
use ingelby\toolbox\helpers\LoggingHelper;
use yii\helpers\Json;
use function GuzzleHttp\Promise\settle;
use function GuzzleHttp\Promise\unwrap;

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
        if (empty($rawResponse['sports'])) {
            throw new EspnClientException(400, 'No sports data in array');
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
        if (empty($rawSportResponse['leagues'])) {
            throw new EspnClientException(400, 'No leagues data in array');
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
     * @param array $rawLeagueResponse
     * @throws EspnClientException
     */
    protected function validateTeamsResponse(array $rawLeagueResponse)
    {
        if (!array_key_exists('teams', $rawLeagueResponse)) {
            throw new EspnClientException(400, 'Error getting teams data, no events key');
        }
        if (!is_array($rawLeagueResponse['teams'])) {
            throw new EspnClientException(400, 'No teams data');
        }
    }

    /**
     * @param array $rawLeagueResponse
     * @throws EspnClientException
     */
    protected function validateCompetitionsResponse(array $rawEventResponse)
    {
        if (!array_key_exists('competitions', $rawEventResponse)) {
            throw new EspnClientException(400, 'Error getting competitions data, no competitions key');
        }
        if (!is_array($rawEventResponse['competitions'])) {
            throw new EspnClientException(400, 'No competitions data');
        }
    }

    /**
     * @param bool     $includeLeageData WARNING: Setting this to true, will likey cause a timeout if you include cricket
     * @param string[] $excludeSportsSlugs
     * @return SportModel[]
     * @throws EspnClientException
     * @throws EspnMappedException
     * @throws EspnServerException
     */
    public function getAll(bool $includeLeageData = false, array $excludeSportsSlugs = []): array
    {
        $rawResponse = $this->get('/v1/sports');

        $this->validateSportsResponse($rawResponse);

        $sports = [];

        foreach ($rawResponse['sports'] as $rawSportData) {
            $sportModel = new SportModel();
            $sportModel->setAttributes($rawSportData);
            if (!$sportModel->validate()) {
                \Yii::warning('Unable to map sports data, reason: ' . implode(', ', $sportModel->getFirstErrors()));
                continue;
            }
            if (in_array($sportModel->slug, $excludeSportsSlugs, true)) {
                \Yii::info('Sportslug: ' . $sportModel->slug . ' in exclude array, skipping');
                continue;
            }
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
    public function getSport(string $sportSlug): SportModel
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

        return $sportModel;
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
     * @return EventModel[]
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

        $events = [];
        foreach ($rawEventsData as $rawEventData) {
            $events[] = $this->mapEventData($rawEventData);
        }

        return $events;
    }

    /**
     * @param string $sportSlug
     * @param string $leagueSlug
     * @return EnrichedTeamModel[]
     * @throws EspnClientException
     * @throws EspnMappedException
     * @throws EspnServerException
     */
    public function getSportsLeagueTeams(string $sportSlug, string $leagueSlug): array
    {
        $rawResponse = $this->get(
            '/v1/sports/' . $sportSlug . '/' . $leagueSlug . '/teams',
            [
                static::LIMIT => static::MAX_RESULTS_PER_PAGE,
            ]
        );

        if ($rawResponse[static::RESULT_COUNT] > static::MAX_RESULTS_PER_PAGE) {
            //@Todo, add pagination
            \Yii::warning(
                'There are more results, than we are returning, limit: ' . static::MAX_RESULTS_PER_PAGE .
                ' resultCount: ' . $rawResponse[static::RESULT_COUNT]
            );
        }

        $this->validateSportsResponse($rawResponse);
        $rawSportResponse = current($rawResponse['sports']);
        $this->validateLeagueResponse($rawSportResponse);
        $rawLeagueData = current($rawSportResponse['leagues']);
        $this->validateTeamsResponse($rawLeagueData);

        $teams = [];
        foreach ($rawLeagueData['teams'] as $rawTeamData) {
            $enrichedTeamModel = $this->mapEnrichedTeamData($rawTeamData);
            $teams[$enrichedTeamModel->id] = $enrichedTeamModel;
        }

        return $teams;
    }

    /**
     * @param string[] $leagueSlugs
     * @return EventModel[][][] ['sportSlug' => ['leagueSlug' => [EventModel[]]]]
     * @throws EspnClientException
     * @throws EspnMappedException
     * @throws EspnServerException
     */
    public function getSportsLeaguesEvents(array $leagueSlugs, array $params = []): array
    {
        $queryParams = array_merge(
            [
                'leagues' => implode(',', $leagueSlugs),
            ],
            $params
        );

        $rawResponse = $this->get('/v1/sports/events', $queryParams);

        $sportsLeaguesEvents = [];
        $this->validateSportsResponse($rawResponse);
        foreach ($rawResponse['sports'] as $rawSportData) {
            try {
                $this->validateLeagueResponse($rawSportData);
                $sportModel = new SportModel();
                $sportModel->setAttributes($rawSportData);
                $rawLeaguesData = $rawSportData['leagues'];
                foreach ($rawSportData['leagues'] as $rawLeagueData) {
                    $league = $this->mapLeagueData($rawLeagueData);
                    $this->validateEventsResponse($rawLeagueData);
                    $rawEventsData = $rawLeagueData['events'];

                    $events = [];
                    foreach ($rawEventsData as $rawEventData) {
                        $events[] = $this->mapEventData($rawEventData);
                    }

                    $sportsLeaguesEvents[$sportModel->slug][$league->slug] = $events;
                }
            } catch (EspnClientException $clientException) {
                \Yii::error($clientException->getMessage());
            }
        }

        return $sportsLeaguesEvents;
    }

    /**
     * WARNING: Do not use with the cacheHandler, need some extra work to clevery autocache results
     *
     * @param string $sportSlug
     * @param string $leagueSlug
     * @param array  $teamIds
     * @return EnrichedTeamModel[]
     */
    public function getEnrichedTeams(string $sportSlug, string $leagueSlug, array $teamIds): array
    {
        $teams = [];
        try {
            $promises = [];
            foreach ($teamIds as $teamId) {
                if ($team = \Yii::$app->cache->get(EnrichedTeamModel::CACHE_KEY . $teamId)) {
                    \Yii::info('Got teamId: ' . $teamId . ' from cache');
                    $teams[$teamId] = $team;
                    continue;
                }
                $uri = '/v1/sports/' . $sportSlug . '/' . $leagueSlug . '/teams/' . $teamId;
                $promises[$teamId] = $this->getAsync($uri);
            }

            try {
                unwrap($promises);
            } catch (TransferException | ConnectException $exception) {
                \Yii::error('Unable to unwrap, message: ' . $exception->getMessage());
            }

            $results = settle($promises)->wait();

            foreach ($results as $teamId => $result) {
                if ('fulfilled' !== $result['state']) {
                    \Yii::warning($teamId . ' request not fulfilled, state: ' . $result['state']);
                    continue;
                }
                $rawResponseAsString = $result['value']->getBody()->getContents();

                \Yii::info('raw result data: ' . $rawResponseAsString);

                try {
                    $rawResponse = Json::decode($rawResponseAsString);
                    if (null === $rawResponse) {
                        \Yii::warning('Unable to decode: ' . $rawResponseAsString);
                        continue;
                    }
                    $this->validateSportsResponse($rawResponse);
                    $rawSportResponse = current($rawResponse['sports']);
                    $this->validateLeagueResponse($rawSportResponse);
                    $rawLeagueData = current($rawSportResponse['leagues']);

                    if (!isset($rawLeagueData['teams']) || empty($rawLeagueData['teams'])) {
                        throw new EspnClientException('No teams data to map');
                    }

                    $rawTeamData = current($rawLeagueData['teams']);
                    $teams[$teamId] = $enrichedTeamModel = $this->mapEnrichedTeamData($rawTeamData);
                    \Yii::$app->cache->set(
                        EnrichedTeamModel::CACHE_KEY . $teamId,
                        $enrichedTeamModel,
                        EnrichedTeamModel::CACHE_TTL
                    );
                } catch (EspnClientException $exception) {
                    \Yii::error('Unable to map team id: ' . $teamId . ', error: ' . $exception->getMessage());
                }
            }
        } catch (\Throwable $exception) {
            LoggingHelper::logException($exception);
        } finally {
            return $teams;
        }
    }

    /**
     * @param array $rawEventData
     * @return EventModel
     */
    protected function mapEventData(array $rawEventData): EventModel
    {
        $eventModel = new EventModel();
        $eventModel->setAttributes($rawEventData);

        if (isset($rawEventData['season'])) {
            $seasonModel = new SeasonModel();
            $seasonModel->setAttributes($rawEventData['season']);
            $eventModel->setSeason($seasonModel);
        }
        if (isset($rawEventData['week'])) {
            $weekModel = new WeekModel();
            $weekModel->setAttributes($rawEventData['week']);
            $eventModel->setWeek($weekModel);
        }

        try {
            $this->validateCompetitionsResponse($rawEventData);
            $eventModel->setCompetitions($this->mapCompetitionsData($rawEventData['competitions']));
        } catch (EspnClientException $exception) {
            \Yii::warning($exception->getMessage());
        }

        return $eventModel;
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
            $leagueModel->setSeason($seasonModel);
        }
        if (isset($rawLeagueData['week'])) {
            $weekModel = new WeekModel();
            $weekModel->setAttributes($rawLeagueData['week']);
            $leagueModel->setWeek($weekModel);
        }
        return $leagueModel;
    }

    /**
     * @param array $rawTeamData
     * @return EnrichedTeamModel
     */
    protected function mapEnrichedTeamData(array $rawTeamData): EnrichedTeamModel
    {
        $enrichedTeamModel = new EnrichedTeamModel();
        $enrichedTeamModel->setAttributes($rawTeamData);
        if (isset($rawTeamData['logos'][TeamLogoModel::SIZE_FULL])) {
            $logoModel = new TeamLogoModel();
            $logoModel->setAttributes($rawTeamData['logos'][TeamLogoModel::SIZE_FULL]);
            $enrichedTeamModel->addLogo(TeamLogoModel::SIZE_FULL, $logoModel);
        }

        return $enrichedTeamModel;
    }

    /**
     * @param array $rawLeagueData
     * @return CompetitionModel[]
     */
    protected function mapCompetitionsData(array $rawComptitionsData): array
    {
        $competitions = [];
        foreach ($rawComptitionsData as $rawComptitionData) {
            $competition = new CompetitionModel();
            $competition->setAttributes($rawComptitionData);
            if (isset($rawComptitionData['status'])) {
                $competitionStatusModel = new CompetitionStatusModel();
                $competitionStatusModel->setAttributes($rawComptitionData['status']);
                $competition->setStatus($competitionStatusModel);
            }

            if (isset($rawComptitionData['competitors']) && is_array($rawComptitionData['competitors'])) {
                $competitors = [];
                foreach ($rawComptitionData['competitors'] as $rawCompetitorData) {
                    $competitor = new CompetitorModel();
                    $competitor->setAttributes($rawCompetitorData);
                    $competitors[] = $competitor;
                    if (isset($rawCompetitorData['team'])) {
                        $teamModel = new TeamModel();
                        $teamModel->setAttributes($rawCompetitorData['team']);
                        $competitor->setTeam($teamModel);
                    }
                }
                $competition->setCompetitors($competitors);
            }

            $competitions[] = $competition;
        }
        return $competitions;

    }
}
