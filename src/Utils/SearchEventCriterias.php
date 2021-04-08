<?php


namespace App\Utils;


use App\Entity\Campus;
use App\Entity\User;


class SearchEventCriterias
{
    private ?Campus $campus;
    private string $searchBar;
    private $dateStart;
    private $dateEnd;
    private ?bool $isOrganisedByMe = false;
    private ?bool $isAttendedByMe= false;
    private ?bool $isNotAttendedByMe= false;
    private ?bool $isFinished= false;
    private ?User $user;

    public function __construct()
    {
        $dateStart = null;
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
     * @return mixed
     */
    public function getIsOrganisedByMe()
    {
        return $this->isOrganisedByMe;
    }

    /**
     * @param mixed $isOrganisedByMe
     */
    public function setIsOrganisedByMe($isOrganisedByMe): void
    {
        $this->isOrganisedByMe = $isOrganisedByMe;
    }

    /**
     * @return mixed
     */
    public function getIsAttendedByMe()
    {
        return $this->isAttendedByMe;
    }

    /**
     * @param mixed $isAttendedByMe
     */
    public function setIsAttendedByMe($isAttendedByMe): void
    {
        $this->isAttendedByMe = $isAttendedByMe;
    }

    /**
     * @return mixed
     */
    public function getIsNotAttendedByMe()
    {
        return $this->isNotAttendedByMe;
    }

    /**
     * @param mixed $isNotAttendedByMe
     */
    public function setIsNotAttendedByMe($isNotAttendedByMe): void
    {
        $this->isNotAttendedByMe = $isNotAttendedByMe;
    }

    /**
     * @return mixed
     */
    public function getIsFinished()
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


}