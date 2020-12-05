<?php

namespace Ingelby\Espn\Constants;

class LeagueSlug
{
    public const NFL = 'nfl';
    public const NBA = 'nba';
    public const NHL = 'nhl';
    public const MLB = 'mbl';
    public const COLLEGE_FOOTBALL = 'college-football';
    public const UEFA_CHAMPIONS_LEAGUE = 'uefa.champions';
    public const ENGLISH_PREMIER_LEAGUE = 'eng.1';

    /**
     * @param string $slug
     * @return string
     */
    public static function mapFriendlyName(string $slug): string
    {
        switch ($slug) {
            case static::UEFA_CHAMPIONS_LEAGUE:
                return 'UEFA Champions League';
            case static::ENGLISH_PREMIER_LEAGUE:
                return 'English Premier League';
            default:
                return $slug;
        }
    }
}
