<?php


namespace App\Utils;

class SearchEventCriterias
{
    private $campus;
    private $searchBar;
    private $dateStart;
    private $dateEnd;
    private bool $isOrganisedByMe = false;
    private bool $isAttendedByMe= false;
    private bool $isNotAttendedByMe= false;
    private bool $isFinished= false;
    private $user;

    public function __construct()
    {
    }

    /**
     * @return mixed
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param mixed $user
     */
    public function setUser($user): void
    {
        $this->user = $user;
    }

    /**
     * @return mixed
     */
    public function getCampus()
    {
        return $this->campus;
    }

    /**
     * @param mixed $campus
     */
    public function setCampus($campus): void
    {
        $this->campus = $campus;
    }

    /**
     * @return mixed
     */
    public function getSearchBar()
    {
        return $this->searchBar;
    }

    /**
     * @param mixed $searchBar
     */
    public function setSearchBar($searchBar): void
    {
        $this->searchBar = $searchBar;
    }

    /**
     * @return mixed
     */
    public function getDateStart()
    {
        return $this->dateStart;
    }

    /**
     * @param mixed $dateStart
     */
    public function setDateStart($dateStart): void
    {
        $this->dateStart = $dateStart;
    }

    /**
     * @return mixed
     */
    public function getDateEnd()
    {
        return $this->dateEnd;
    }

    /**
     * @param mixed $dateEnd
     */
    public function setDateEnd($dateEnd): void
    {
        $this->dateEnd = $dateEnd;
    }

    /**
     * @return bool|null
     */
    public function getIsOrganisedByMe(): ?bool
    {
        return $this->isOrganisedByMe;
    }

    /**
     * @return bool|null
     */
    public function getIsAttendedByMe(): ?bool
    {
        return $this->isAttendedByMe;
    }

    /**
     * @return bool|null
     */
    public function getIsNotAttendedByMe(): ?bool
    {
        return $this->isNotAttendedByMe;
    }

    /**
     * @return bool|null
     */
    public function getIsFinished(): ?bool
    {
        return $this->isFinished;
    }

    /**
     * @param mixed $isFinished
     */
    public function setIsFinished($isFinished): void
    {
        $this->isFinished = $isFinished;
    }

    /**
     * @param bool $isOrganisedByMe
     */
    public function setIsOrganisedByMe(bool $isOrganisedByMe): void
    {
        $this->isOrganisedByMe = $isOrganisedByMe;
    }

    /**
     * @param bool $isAttendedByMe
     */
    public function setIsAttendedByMe(bool $isAttendedByMe): void
    {
        $this->isAttendedByMe = $isAttendedByMe;
    }

    /**
     * @param bool $isNotAttendedByMe
     */
    public function setIsNotAttendedByMe(bool $isNotAttendedByMe): void
    {
        $this->isNotAttendedByMe = $isNotAttendedByMe;
    }


}