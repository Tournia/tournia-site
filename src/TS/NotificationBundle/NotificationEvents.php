<?php
namespace TS\NotificationBundle;

final class NotificationEvents
{
    /**
     * The tournament.new event is triggered when a Tournament is created
     *
     * The event listener receives an
     * TS\NotificationBundle\Event\TournamentEvent instance.
     *
     * @var string
     */
    const TOURNAMENT_NEW = 'ts.tournament.new';

    /**
     * The person.new event is triggered when a new Person is created
     * This Person has possibly also authorizations
     *
     * The event listener receives an
     * TS\NotificationBundle\Event\PersonEvent instance.
     *
     * @var string
     */
    const PERSON_NEW = 'ts.person.new';

    /**
     * The person.authorized event is triggered when a Person has received new authorizations
     *
     * The event listener receives an
     * TS\NotificationBundle\Event\PersonEvent instance.
     *
     * @var string
     */
    const PERSON_AUTHORIZED = 'ts.person.authorized';

    /**
     * The match.new event is triggered when a new Match is created
     *
     * The event listener receives an
     * TS\NotificationBundle\Event\MatchEvent instance.
     *
     * @var string
     */
    const MATCH_NEW = 'ts.match.new';

    /**
     * The match.score event is triggered when a score of a match is changed
     *
     * The event listener receives an
     * TS\NotificationBundle\Event\MatchEvent instance.
     *
     * @var string
     */
    const MATCH_SCORE = 'ts.match.score';

    /**
     * The match.status event is triggered when the status of a match is changed
     *
     * The event listener receives an
     * TS\NotificationBundle\Event\MatchEvent instance.
     *
     * @var string
     */
    const MATCH_STATUS = 'ts.match.status';

    /**
     * The player.new event is triggered when a new Player is created
     *
     * The event listener receives an
     * TS\NotificationBundle\Event\PlayerEvent instance.
     *
     * @var string
     */
    const PLAYER_NEW = 'ts.player.new';

    /**
     * The player.change event is triggered when a Player is changed
     *
     * The event listener receives an
     * TS\NotificationBundle\Event\PlayerEvent instance.
     *
     * @var string
     */
    const PLAYER_CHANGE = 'ts.player.change';

    /**
     * The player.delete_before event is triggered before a Player is deleted
     *
     * The event listener receives an
     * TS\NotificationBundle\Event\PlayerEvent instance.
     *
     * @var string
     */
    const PLAYER_DELETE_BEFORE = 'ts.player.delete_before';

    /**
     * The registrationGroup.change event is triggered when a RegistrationGroup is changed
     *
     * The event listener receives an
     * TS\NotificationBundle\Event\PlayerEvent instance.
     *
     * @var string
     */
    const REGISTRATIONGROUP_CHANGE = 'ts.registrationGroup.change';

    /**
     * The invoice.new event is triggered when a new Invoice is created
     *
     * The event listener receives an
     * TS\NotificationBundle\Event\InvoiceEvent instance.
     *
     * @var string
     */
    const INVOICE_NEW = 'ts.invoice.new';
}