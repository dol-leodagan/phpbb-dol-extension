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
    'DOL_STATUS_BOOK'           => 'Grimoire',
    'DOL_STATUS_STATUS'         => 'Server Status',
    'DOL_STATUS_STATUS_TITLE'   => 'Server Status & RvR Feed',
    
    'DOL_STATUS_ALBION'         => 'Albion',
    'DOL_STATUS_MIDGARD'        => 'Midgard',
    'DOL_STATUS_HIBERNIA'       => 'Hibernia',
    
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
));
?>
