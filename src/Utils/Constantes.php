<?php

namespace App\Utils;

//define('CREATED', 'Créée');
//define('OPENED' , 'Ouverte');
//define('CLOSED' , 'Clôturée');
//define('ONGOING' , 'Activité en cours');
//define('FINISHED' , 'Passée');
//define('CANCELLED' , 'Annulée');
//define('ARCHIVED' , 'Archivée');
//
//define('EVENT_SHOW',['afficher', 'event_view']);
//define('EVENT_MODIFY',['modifier', 'event_modified']);
//define('EVENT_CANCEL',['annuler', 'event_cancelled']);
//define('EVENT_ABANDON',["se désinscrire", 'event_abandonned']);
//define('EVENT_REGISTER',["s'inscrire", 'event_registration']);
//define('EVENT_PUBLISH',['publier', 'event_published']);


class Constantes
{

    public const CREATED = 'Créée';
    public const OPENED = 'Ouverte';
    public const CLOSED = 'Clôturée';
    public const ONGOING = 'Activité en cours';
    public const FINISHED = 'Passée';
    public const CANCELLED = 'Annulée';
    public const ARCHIVED = 'Archivée';

    public const TIME_ZONE = 'Europe/Paris';

    public const EVENT_SHOW = ['afficher', 'event_view'];
    public const EVENT_MODIFY = ['modifier', 'event_modified'];
    public const EVENT_CANCEL = ['annuler', 'event_cancelled'];
    public const EVENT_ABANDON =["se désinscrire", 'event_abandonned'];
    public const EVENT_REGISTER =["s'inscrire", 'event_registration'];
    public const EVENT_PUBLISH =['publier', 'event_published'];

}