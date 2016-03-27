<?php
/**
*
* @package DOL Extension 0.0.1
* @copyright (c) 2016 Leodagan
* @license MIT
*
*/
/**
* DO NOT CHANGE
*/
if (!defined('IN_PHPBB'))
{
    exit;
}
if (empty($lang) || !is_array($lang))
{
    $lang = array();
}
// DEVELOPERS PLEASE NOTE
//
// All language files should use UTF-8 as their encoding and the files must not contain a BOM.
//
// Placeholders can now contain order information, e.g. instead of
// 'Page %s of %s' you can (and should) write 'Page %1$s of %2$s', this allows
// translators to re-order the output of data while ensuring it remains correct
//
// You do not need this where single placeholders are used, e.g. 'Message %d' is fine
// equally where a string contains only two placeholders which are used to wrap text
// in a url you again do not need to specify an order e.g., 'Click %sHERE%s' is fine
// Common
$lang = array_merge($lang, array(
    'DOL_STATUS_HERALD'         => 'Herald',
    'DOL_STATUS_GAME'           => 'Game Account',
    'DOL_STATUS_GAME_TITLE'     => 'Game Account Center',
    'DOL_STATUS_BOOK'           => 'Grimoire',
    'DOL_STATUS_STATUS'         => 'Server Status',
    'DOL_STATUS_STATUS_TITLE'   => 'Server Status & RvR Feed',
    
    'DOL_STATUS_ALBION'         => 'Albion',
    'DOL_STATUS_MIDGARD'        => 'Midgard',
    'DOL_STATUS_HIBERNIA'       => 'Hibernia',
    
    'DOL_STATUS_YEAR'           => 'year',
    'DOL_STATUS_YEARS'          => 'years',
    'DOL_STATUS_MONTH'          => 'month',
    'DOL_STATUS_MONTHS'         => 'months',
    'DOL_STATUS_WEEK'           => 'week',
    'DOL_STATUS_WEEKS'          => 'weeks',
    'DOL_STATUS_DAY'            => 'day',
    'DOL_STATUS_DAYS'           => 'days',
    'DOL_STATUS_HOUR'           => 'hour',
    'DOL_STATUS_HOURS'          => 'hours',
    'DOL_STATUS_MINUTE'         => 'minute',
    'DOL_STATUS_MINUTES'        => 'minutes',
    'DOL_STATUS_SECOND'         => 'second',
    'DOL_STATUS_SECONDS'        => 'seconds',
    'DOL_STATUS_AGO'            => 'ago',

    'DOL_HERALD_WARMAP'         => 'Warmap',
    'DOL_HERALD_SEARCH'         => 'Search',
    'DOL_HERALD_BADSEARCH'      => 'Invalid Search Terms ! Search Query must consist of at least 3 characters and no special characters...',
    'DOL_HERALD_PLACEHOLDER'    => 'Players and Guilds...',
    'DOL_HERALD_LADDERACTIVE'   => 'Top Active Players',
    'DOL_HERALD_LADDERTOP'      => 'Top 50 Players',
    'DOL_HERALD_LADDERGUILDS'   => 'Top 50 Guilds',
    'DOL_HERALD_LADDERDEATHBLOW'        => 'Top 50 Deathblows',
    'DOL_HERALD_LADDERSOLO'     => 'Top 50 Solo Kills',
    'DOL_HERALD_LADDERKILLS'    => 'Top 50 Total Kills',
    'DOL_HERALD_RESUME'         => 'Population Statistic',
    
    'DOL_HERALD_LADDERNAME'     => 'Name',
    'DOL_HERALD_LADDERRACECLASS'        => 'Race / Class',
    'DOL_HERALD_LADDERREALMPOINTS'      => 'Realm Points',
    'DOL_HERALD_LADDERRR'       => 'RR',
    'DOL_HERALD_LADDERGUILD'    => 'Guild',
    'DOL_HERALD_LADDERLASTPLAYED'       => 'Last Played',
    'DOL_HERALD_LADDERGUILDNAME'        => 'Guild Name',
    'DOL_HERALD_LADDERALLIANCENAME'     => 'Alliance Name',
    'DOL_HERALD_LADDERMEMBERS'  => 'Members',
    
    'DOL_HERALD_PLAYERGUILD'    => 'Guild',
    'DOL_HERALD_PLAYERREALMRANK'        => 'Realm Rank',
    'DOL_HERALD_PLAYERREALMPOINTS'      => 'Realm Points',
    'DOL_HERALD_PLAYERRANKINGSERVER'    => 'Rank on Server',
    'DOL_HERALD_PLAYERRANKINGREALM'     => 'Rank in Realm',
    'DOL_HERALD_PLAYERRANKINGCLASS'     => 'Rank in Class',
    'DOL_HERALD_PLAYERTOTALKILLS'       => 'Total Player Kills',
    'DOL_HERALD_PLAYERMIDGARDKILLS'     => 'Midgard Player Kills',
    'DOL_HERALD_PLAYERHIBERNIAKILLS'    => 'Hibernia Player Kills',
    'DOL_HERALD_PLAYERALBIONKILLS'      => 'Albion Player Kills',
    'DOL_HERALD_PLAYEROFTOTALKILLS'     => 'of total Kills',
    'DOL_HERALD_PLAYERTOTALDEATHBLOW'   => 'Total Deathblow',
    'DOL_HERALD_PLAYERMIDGARDDEATHBLOW' => 'Deathblow Midgard',
    'DOL_HERALD_PLAYERHIBERNIADEATHBLOW'=> 'Deathblow Hibernia',
    'DOL_HERALD_PLAYERALBIONDEATHBLOW'  => 'Deathblow Albion',
    'DOL_HERALD_PLAYEROFTOTALDEATHBLOW' => 'of total Deathblows',
    'DOL_HERALD_PLAYERTOTALSOLO'        => 'Total Solo Kills',
    'DOL_HERALD_PLAYERMIDGARDSOLO'      => 'Midgard Player Solo Kills',
    'DOL_HERALD_PLAYERHIBERNIASOLO'     => 'Hibernia Player Solo Kills',
    'DOL_HERALD_PLAYERALBIONSOLO'       => 'Albion Player Solo Kills',
    'DOL_HERALD_PLAYEROFTOTALSOLO'      => 'of total Solo Kills',
    'DOL_HERALD_PLAYERRVRDEATHS'        => 'RvR Deaths',
    'DOL_HERALD_PLAYERKILLDEATHRATIO'   => 'Kill / Death Ratio',
    'DOL_HERALD_PLAYERRPPERDEATH'       => 'Realm Points per Death',
    'DOL_HERALD_PLAYERLASTPLAYED'       => 'Last Played',
    'DOL_HERALD_PLAYERSIGNATURES'       => 'Signatures',
    'DOL_HERALD_PLAYERMEMBERS'          => 'Members',
    'DOL_HERALD_PLAYERALLIANCE'         => 'Alliance',
    'DOL_HERALD_PLAYERWEBSITE'         => 'Website',

    'DOL_HERALD_KEEPLEVEL'      => 'Keep Level',
    'DOL_HERALD_CAPTUREDBY'     => 'Captured by',
    'DOL_HERALD_CLAIMEDBY'      => 'Claimed by',
    
    'DOL_STATUS_SHARD'          => 'Shard',
    'DOL_STATUS_ONLINE'         => 'Online',
    'DOL_STATUS_STARTING'       => 'Starting',
    'DOL_STATUS_OFFLINE'        => 'Offline',
    'DOL_STATUS_SERVERLOAD'     => 'Server Load',
    'DOL_STATUS_LIGHT'          => 'Light',
    'DOL_STATUS_NORMAL'         => 'Normal',
    'DOL_STATUS_HEAVY'          => 'Heavy',
    'DOL_STATUS_REPLICATION'    => 'Replication',
    'DOL_STATUS_WORKING'        => 'Working',
    'DOL_STATUS_DELAYED'        => 'Delayed',
    'DOL_STATUS_BROKEN'         => 'Broken',
    'DOL_STATUS_ONLINEPLAYERS'  => 'Online Players',
    'DOL_STATUS_ACCOUNTS'       => 'Accounts',
    'DOL_STATUS_VALIDATED'      => 'Validated',
    'DOL_STATUS_MOBSSPAWNED'    => 'Mobs spawned',
    'DOL_STATUS_RESTARTED'      => 'Server Restarted',
    'DOL_STATUS_TIME'           => 'Server Time',

    'DOL_GAME_HELP'             => 'How to Register',
    'DOL_GAME_HELP_DESC1'       => '<p>Registering your Game Account on Freyad Portal brings new features for your Shard experience:</p><ul><li>Account summary with characters statistics.</li><li>Self-service password reset.</li><li>Improved support from Staff.</li><li>Account protection when archiving non-returning players.</li></ul>',
    'DOL_GAME_HELP_DESC2'       => '<p>Log In Game with a non-registered account or a newly created account and follow these simple steps:</p><ul><li>Type in-Game command <i>/register &quot;phpBB Profile Name&quot;</i> and confirm.</li><li>Refresh this page some minutes later, your validation token should appear.</li><li>Complete registration with in-Game command <i>/register "#Token"</i>.</li><li>Refresh this page again some minutes later for your personal summary.</li></ul>',
    'DOL_GAME_NOPENDING'        => 'You have no account Pending for Validation...',
    'DOL_GAME_PENDING'          => 'Pending accounts',
    'DOL_GAME_PENDING_DESC'     => 'Following game accounts are pending for Registration (Token Validation).',
    'DOL_GAME_PENDING_DESC1'    => 'Complete your registration by using in-game command <i>/register "#Token"</i>, make sure you replace the <i>"#Token"</i> string with the appropriate Game account Token...',
    'DOL_GAME_VALIDATED'        => 'Registered accounts',
    'DOL_GAME_VALIDATED_DESC'   => 'Following game accounts are fully registered with your phpBB profile.',
    'DOL_GAME_HEADER_ACCOUNTNAME'   => 'Account Name',
    'DOL_GAME_HEADER_TOKEN'         => 'Validation Token',
    'DOL_GAME_HEADER_REALM'         => 'Realm',
    'DOL_GAME_HEADER_CREATED'       => 'Created',
    'DOL_GAME_HEADER_LASTLOGIN'     => 'Last Login',
    'DOL_GAME_HEADER_PLAYERNAME'    => 'Character',
    'DOL_GAME_HEADER_RACECLASS'     => 'Race / Class',
    'DOL_GAME_HEADER_GUILD'         => 'Guild',
    'DOL_GAME_HEADER_LEVEL'         => 'Level',
    'DOL_GAME_HEADER_MASTERLEVEL'   => 'Master Level',
    'DOL_GAME_HEADER_CHAMPIONLEVEL' => 'Champion Level',
    'DOL_GAME_HEADER_REALMRANK'     => 'Realm Rank',
    'DOL_GAME_HEADER_LASTPLAYED'    => 'Last Played',
    'DOL_GAME_HEADER_PLAYED'        => 'Played Time',
    'DOL_GAME_HEADER_ACTIONS'       => 'Actions',
    'DOL_GAME_ACTION_RESET'         => 'Reset Password',
    'DOL_GAME_ACTION_RESET_CONFIRM' => 'This will generate a new Password for your Game Account and send it to your registered e-mail address, continue ?',
    'DOL_GAME_ACTION_KICK'          => 'Disconnect',
    'DOL_GAME_ACTION_KICK_CONFIRM'  => 'This will disconnect all currently playing characters of this account, continue ?',
    'DOL_GAME_ACTION_NOTME'         => 'Not my Account !',
    'DOL_GAME_ACTION_NOTME_CONFIRM' => 'This will remove the pending validation for this Account, continue ?',
    'DOL_GAME_ACTION_PASSWD'        => 'Password Validation',
    'DOL_GAME_ACTION_PASSWD_CONFIRM' => 'Enter Game Account Password to confirm registration offline...',
    'DOL_GAME_ACTION_CONFIRM'       => 'Confirm ?',
    'DOL_GAME_ACTION_CANCEL'        => 'Cancel',
    'DOL_GAME_ACTION_CLOSE'         => 'Close',
    'DOL_GAME_ACTION_REFRESH'       => 'Refresh',
    'DOL_GAME_CREATEACC'            => 'Create a Game Account',
    'DOL_GAME_CREATEACC_SUBMIT'     => 'Create',
    'DOL_GAME_CREATEACC_DESC'       => 'Create an in-Game account that will be automatically registered with your phpBB profile. Maximum Account Name and Password Length is 20 characters...',
    'DOL_GAME_CREATEACC_NAME'       => 'Account Name',
    'DOL_GAME_CREATEACC_NAMEPH'     => 'At least 3 chars...',
    'DOL_GAME_CREATEACC_PASSWD'     => 'Account Password',
    'DOL_GAME_CREATEACC_PASSWDPH'   => 'At least 6 chars...',
    'DOL_GAME_CREATEACC_CONFIRM'    => 'Retype Password',
    'DOL_GAME_CREATEACC_CONFIRMPH'  => 'Password must match...',
    'DOL_GAME_CREATEACC_DEFAULT'    => 'Default Realm',
    'DOL_GAME_CREATEACC_ALERT'      => 'This will create a new in-Game Account with provided credentials, continue ?',
    'DOL_GAME_MESSAGE_ENTERPASSWD'  => 'Please enter a Password string !',
    'DOL_GAME_MESSAGE_WRONGPASSWD'  => 'Password is Invalid !',
    
));
?>
