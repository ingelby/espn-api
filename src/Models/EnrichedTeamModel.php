<?php


namespace Ingelby\Espn\Models;


class EnrichedTeamModel extends TeamModel
{
    /**
     * @var TeamLogoModel[]
     */
    protected array $logos = [];

    /**
     * @param string $logoSizePreference
     * @return TeamLogoModel|null
     */
    public function getLogo(string $logoSizePreference): ?TeamLogoModel
    {
        if (array_key_exists($logoSizePreference, $this->logos)) {
            return $this->logos[$logoSizePreference];
        }

        $fallbackLogo = current($this->logos);

        if (false === $fallbackLogo) {
            return null;
        }
        return $fallbackLogo;
    }

    /**
     * @param string $logoSizePreference
     * @return string
     */
    public function getLogoUrl(string $logoSizePreference): string
    {
        if (null === $logo = $this->getLogo($logoSizePreference)) {
            //@Todo, add placeholder logo
            return '';
        }
        return $logo->href;
    }

    /**
     * @param string[]        $logoSize
     * @param TeamLogoModel[] $logo
     */
    public function addLogo(string $logoSize, TeamLogoModel $logo): void
    {
        $this->logos[$logoSize] = $logo;
    }

    /**
     * @return TeamLogoModel[]
     */
    public function getLogos(): array
    {
        return $this->logos;
    }

    /**
     * @param TeamLogoModel[] $logos
     */
    public function setLogos(array $logos): void
    {
        $this->logos = $logos;
    }

}
