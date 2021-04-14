<?php


namespace App\Utils;


use DateInterval;
use DateTime;

class DateTimeHandler
{

    /**
     * Fonction qui nous permet d'ajouter des minutes à une date
     * @param DateTime $dateTimeStart
     * @param int $minutes
     * @return DateTime
     */
    public static function dateAddMinutes(DateTime $dateTimeStart, int $minutes) : DateTime
    {

        $dateTimeEnd = clone $dateTimeStart;
        $intervalDuration = abs($minutes);
        $dateTimeEnd->add(new DateInterval('PT' . $intervalDuration . 'M'));

        return $dateTimeEnd;
    }

    /**
     * Fonction qui permet d'enlever des minutes à une date
     * @param DateTime $dateTimeStart
     * @param int $minutes
     * @return DateTime
     */
    public static function dateSubMinutes(DateTime $dateTimeStart, int $minutes) : DateTime
    {

        $dateTimeEnd = clone $dateTimeStart;
        $intervalDuration = abs($minutes);
        $dateTimeEnd->sub(new DateInterval('PT' . $intervalDuration . 'M'));

        return $dateTimeEnd;
    }


}