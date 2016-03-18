<?php
/**
*
* @package DOL Extension 0.0.1
* @copyright (c) 2016 Leodagan
* @license MIT
*
*/
namespace dol\status\controller;

use phpbb\config\config;
use phpbb\controller\helper;
use phpbb\template\template;
use phpbb\user;
use \phpbb\request\request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class images
{
     /* @var config */
    protected $config;

    /* @var helper */
    protected $helper;

    /* @var template */
    protected $template;

    /* @var user */
    protected $user;
    
    /* @var request */
    protected $request;

    /**
    * phpBB root path
    * @var string
    */
    protected $phpbb_root_path;
    
    /**
    * PHP file extension
    * @var string
    */
    protected $php_ext;
    
    /**
    * Extension root path
    * @var string
    */
    protected $root_path;

    
    /** @var \dol\status\controller\helper */
    protected $controller_helper;
    
    /**
    * Constructor
    *
    * @param config $config
    * @param helper $helper
    * @param template $template
    * @param user $user
    * @param string $phpbb_root_path
    * @param string $php_ext
    */
    public function __construct(config $config, helper $helper, template $template, user $user, request $request, $phpbb_root_path, $php_ext, $controller_helper)
    {
        $this->config = $config;
        $this->helper = $helper;
        $this->template = $template;
        $this->user = $user;
        $this->request = $request;
        $this->phpbb_root_path = $phpbb_root_path;
        $this->php_ext = $php_ext;
        $this->root_path = $phpbb_root_path . 'ext/dol/status/';
        $this->controller_helper = $controller_helper;
    }
    
    /** Images Handler **/
    public function handle($cmd, $params)
    {
        $data = false;
        
        if ($params !== null && $params !== '')
        {
            $headers = array(
                'Content-Type'     => 'image/png',
                'Content-Disposition' => 'inline; filename="'.$params.'"');
            
            if ($cmd == 'banner')
            {
                $data = $this->drawBanner($params);
            }
            else if ($cmd == 'sigsmall')
            {
               $data = $this->drawSignatureSmall($params);
            }
            else if ($cmd == 'sigdetailed')
            {
               $data = $this->drawSignatureDetailed($params);
            }
            else if ($cmd == 'siglarge')
            {
               $data = $this->drawSignatureLarge($params);
            }
        }
        
        return new Response($data, 200, $headers);
    }
    /** Region Banners **/
    public function drawBanner($guild)
    {
        // Retrieve Guild Data
        $guild_data = $this->controller_helper->backend_yaml_query('getguild/'.$guild, 15 * 60);
        
        // Check Cache
        $cache_img = $this->cache->get('_BANNERCACHE_banner_'.($guild_data !== null ? md5($guild) : 'null'));
        
        if ($cache_img !== false)
        {
            $cache_img;
        }
        
        $img = imagecreatetruecolor(69, 86);

        if ($guild_data !== null)
        {
            // Decompose Emblem
            $emblemID = $guild_data['Guild']['Emblem'];
            $logo = $emblemID >> 9;
            $pattern = $emblemID >> 7 & 3;
            $primary = $emblemID >> 3 & 15;
            $secondary = ($pattern != 3 ? $emblemID & 7 : 0);
            
            // Get Background Emblem and Symbol
            $emblem = imagecreatefrompng($this->root_path.'styles/all/theme/images/emblems/'.$primary.'-'.$secondary.'-'.$pattern.'-full.png');
            $symbol = imagecreatefromgif($this->root_path.'styles/all/theme/images/emblems/symbols/'.$logo.'.gif');
            
            // Resize Symbol and Center
            $imgx = imagesx($img); $imgy = imagesy($img);
            $emblemx = imagesx($emblem); $emblemy = imagesy($emblem);
            $symbolx = imagesx($symbol); $symboly = imagesy($symbol);

            $ratiox = $imgx / (double)$emblemx * 64.0; $ratioy = $imgy / (double)$emblemy * 64.0;
            $offsetx = ($imgx - $ratiox) / 2.0; $offsety = ($imgy - $ratioy) / 2.0;

            // Draw Emblem then Symbol
            imagecopyresampled($img, $emblem, 0, 0, 0, 0, $imgx, $imgy, $emblemx, $emblemy);
            imagecopyresampled($img, $symbol, $offsetx + 2, $offsety, 0, 0, $ratiox, $ratioy, $symbolx, $symboly);
        }

        // Send Result
        ob_start();
        imagepng($img);
        $data = ob_get_clean();
        $this->cache->put('_BANNERCACHE_banner_'.($guild_data !== null ? md5($guild) : 'null'), $data, 24 * 60 * 60);
        return $data;
    }
    
    public function drawSignatureSmall($player)
    {
        $player_data = $this->controller_helper->backend_yaml_query('getplayer/'.$player, 15 * 60);
                
        // Check Cache
        $cache_img = $this->cache->get('_BANNERCACHE_sigsmall_'.($player_data !== null ? md5($player) : 'null'));
        
        if ($cache_img !== false)
        {
            return $cache_img;
        }
        // Transparent Image
        $img = imagecreatetruecolor(400, 100);
        imagesavealpha($img, true);
        $trans_colour = imagecolorallocatealpha($img, 0, 0, 0, 127);
        imagefill($img, 0, 0, $trans_colour);
        
        if ($player_data !== null)
        {
            $emblem = false;
            // Get Background and Emblem
            if ($player_data['Player']['GuildName'] !== null && $player_data['Player']['GuildName'] !== '')
                $emblem = imagecreatefromstring($this->drawBanner(rawurlencode($player_data['Player']['GuildName'])));
            
            $logo = false;
            switch($player_data['Player']['Realm'])
            {
                case 'albion':
                    $logo = 'alb';
                break;
                case 'midgard':
                    $logo = 'mid';
                break;
                case 'hibernia':
                    $logo = 'hib';
                break;
            }
            
            $background = imagecreatefrompng($this->root_path.'styles/all/theme/images/signatures/bc_'.$logo.'.png');
            
            $imgx = imagesx($img); $imgy = imagesy($img);
            $backgroundx = imagesx($background); $backgroundy = imagesy($background);
            // Draw Background then Emblem
            imagecopyresampled($img, $background, 0, 0, 0, 0, $imgx, $imgy, $backgroundx, $backgroundy);
            
            if ($emblem !== false)
            {
                $emblemx = imagesx($emblem); $emblemy = imagesy($emblem);
                imagecopyresampled($img, $emblem, 324, 7, 0, 0, $emblemx, $emblemy, $emblemx, $emblemy);
            }
            
            // Draw Text
            $font = $this->root_path.'styles/all/theme/images/fonts/verdana.ttf';
            $textcolor = imagecolorallocate($img, 180, 180, 180);
            
            $namestring = $player_data['Player']['Name'].' '.$player_data['Player']['LastName'];
            $guildstring = $player_data['Player']['GuildName'] !== null && $player_data['Player']['GuildName'] !== '' ? '<'.$player_data['Player']['GuildName'].'>' : '';
            $raceclassstring = $player_data['Player']['Race'].' '.$player_data['Player']['Class'];
            $realmstring = $player_data['Player']['RealmTitle'].' - '.$player_data['Player']['RealmRank'];
            $rpstring = number_format($player_data['Player']['RealmPoints'], 0, ',', ' ').' RP';

            $namebox = ImageTTFText($img, 9, 0, 22, 25, $textcolor, $font, $namestring);
            $guildbox = ImageTTFText($img, 8, 0, 22, 40, $textcolor, $font, $guildstring);
            $raceclassbox = ImageTTFText($img, 8, 0, 22, 55, $textcolor, $font, $raceclassstring);
            $realmbox = ImageTTFText($img, 8, 0, 22, 70, $textcolor, $font, $realmstring);
            $rpbox = ImageTTFText($img, 8, 0, 22, 85, $textcolor, $font, $rpstring);

            //if ($mlLevel > 0)
            //ImageTTFText($background, 8, 0, $length, 40, $textcolor, $font4, $ml);

            //Align Right
            $killstring = number_format($player_data['Player']['KillsAlbionPlayers'] + $player_data['Player']['KillsMidgardPlayers'] + $player_data['Player']['KillsHiberniaPlayers'], 0, ',', ' ').' Kills';
            $deathblowstring = number_format($player_data['Player']['KillsAlbionDeathBlows'] + $player_data['Player']['KillsMidgardDeathBlows'] + $player_data['Player']['KillsHiberniaDeathBlows'], 0, ',', ' ').' Deathblows';
            $solostring = number_format($player_data['Player']['KillsHiberniaDeathBlows'] + $player_data['Player']['KillsMidgardSolo'] + $player_data['Player']['KillsHiberniaSolo'], 0, ',', ' ').' Solo Kills';
            
            $killsbox = imagettfbbox(8, 0, $font, $killstring);
            $deathblowbox = imagettfbbox(8, 0, $font, $deathblowstring);
            $solobox = imagettfbbox(8, 0, $font, $solostring);
            
            $longest = abs($killsbox[4] - $killsbox[0]);
            $longest = abs($deathblowbox[4] - $deathblowbox[0]) > $longest ? abs($deathblowbox[4] - $deathblowbox[0]) : $longest;
            $longest = abs($solobox[4] - $solobox[0]) > $longest ? abs($solobox[4] - $solobox[0]) : $longest;
            $length = $imgx - $longest - 90;
            
            ImageTTFText($img, 8, 0, $length, 55, $textcolor, $font, $killstring);
            ImageTTFText($img, 8, 0, $length, 70, $textcolor, $font, $deathblowstring);
            ImageTTFText($img, 8, 0, $length, 85, $textcolor, $font, $solostring);
        }
        
        // Send Result
        ob_start();
        imagepng($img);
        $data = ob_get_clean();
        $this->cache->put('_BANNERCACHE_sigsmall_'.($player_data !== null ? md5($player) : 'null'), $data, 24 * 60 * 60);
        return $data;
    }
    
    public function drawSignatureDetailed($player)
    {
        $player_data = $this->controller_helper->backend_yaml_query('getplayer/'.$player, 15 * 60);
                
        // Check Cache
        $cache_img = $this->cache->get('_BANNERCACHE_sigdetailed_'.($player_data !== null ? md5($player) : 'null'));
        
        if ($cache_img !== false)
        {
            return $cache_img;
        }
        // Transparent Image
        $img = imagecreatetruecolor(460, 100);
        imagesavealpha($img, true);
        $trans_colour = imagecolorallocatealpha($img, 0, 0, 0, 127);
        imagefill($img, 0, 0, $trans_colour);
        
        if ($player_data !== null)
        {
            $logo = false;
            switch($player_data['Player']['Realm'])
            {
                case 'albion':
                    $logo = 'alb';
                break;
                case 'midgard':
                    $logo = 'mid';
                break;
                case 'hibernia':
                    $logo = 'hib';
                break;
            }
            
            $background = imagecreatefrompng($this->root_path.'styles/all/theme/images/signatures/detailed_'.$logo.'.png');
            $imgx = imagesx($img); $imgy = imagesy($img);
            $backgroundx = imagesx($background); $backgroundy = imagesy($background);
            // Draw Background
            imagecopyresampled($img, $background, 0, 0, 0, 0, $imgx, $imgy, $backgroundx, $backgroundy);
            $font = $this->root_path.'styles/all/theme/images/fonts/celtic.ttf';
            $font2 = $this->root_path.'styles/all/theme/images/fonts/univers.ttf';
            $textcolor = imagecolorallocate($img, 220, 220, 220);

            // Draw Details
            ImageTTFText($img, 9, 0, 12, 30, $textcolor, $font2, $player_data['Player']['RealmTitle'].' - '.$player_data['Player']['RealmRank']);
            ImageTTFText($img, 9, 0, 12, 46, $textcolor, $font2, 'Rank on Server: '.$player_data['Player']['Ranking']);
            ImageTTFText($img, 9, 0, 12, 62, $textcolor, $font2, 'Rank in Realm: '.$player_data['Player']['RankingRealm']);
            ImageTTFText($img, 9, 0, 12, 78, $textcolor, $font2, 'Rank in Class: '.$player_data['Player']['RankingClass']);
            
            // Draw Title and Guild
            $namestring = $player_data['Player']['Name'].($player_data['Player']['LastName'] !== null && $player_data['Player']['LastName'] !== '' ? ' '.$player_data['Player']['LastName'] : '');
            $namebox = imagettfbbox(11, 0, $font, $namestring);
            ImageTTFText($img, 11, 0, ($imgx - abs($namebox[4] - $namebox[0])) / 2, 44, $textcolor, $font, $namestring);
            if ($player_data['Player']['GuildName'] !== null && $player_data['Player']['GuildName'] !== '')
            {
                $guildbox = imagettfbbox(10, 0, $font2, '< '.$player_data['Player']['GuildName'].' >');
                ImageTTFText($img, 10, 0, ($imgx - abs($guildbox[4] - $guildbox[0])) / 2, 60, $textcolor, $font2, '< '.$player_data['Player']['GuildName'].' >');
            }
            
            // Draw Right Aligned Detail
            $raceclassstring = $player_data['Player']['Race'].' '.$player_data['Player']['Class'];
            $killstring = number_format($player_data['Player']['KillsAlbionPlayers'] + $player_data['Player']['KillsMidgardPlayers'] + $player_data['Player']['KillsHiberniaPlayers'], 0, ',', ' ').' Kills';
            $deathblowstring = number_format($player_data['Player']['KillsAlbionDeathBlows'] + $player_data['Player']['KillsMidgardDeathBlows'] + $player_data['Player']['KillsHiberniaDeathBlows'], 0, ',', ' ').' Deathblows';
            $solostring = number_format($player_data['Player']['KillsHiberniaDeathBlows'] + $player_data['Player']['KillsMidgardSolo'] + $player_data['Player']['KillsHiberniaSolo'], 0, ',', ' ').' Solo Kills';
            
            $raceclassbox = imagettfbbox(9, 0, $font2, $raceclassstring);
            $killsbox = imagettfbbox(9, 0, $font2, $killstring);
            $deathblowbox = imagettfbbox(9, 0, $font2, $deathblowstring);
            $solobox = imagettfbbox(9, 0, $font2, $solostring);
            
            $longest = abs($killsbox[4] - $killsbox[0]);
            $longest = abs($deathblowbox[4] - $deathblowbox[0]) > $longest ? abs($deathblowbox[4] - $deathblowbox[0]) : $longest;
            $longest = abs($solobox[4] - $solobox[0]) > $longest ? abs($solobox[4] - $solobox[0]) : $longest;
            $longest = abs($raceclassbox[4] - $raceclassbox[0]) > $longest ? abs($raceclassbox[4] - $raceclassbox[0]) : $longest;
            $length = $imgx - $longest - 12;
            
            ImageTTFText($img, 9, 0, $length, 30, $textcolor, $font2, $raceclassstring);
            ImageTTFText($img, 9, 0, $length, 46, $textcolor, $font2, $killstring);
            ImageTTFText($img, 9, 0, $length, 62, $textcolor, $font2, $deathblowstring);
            ImageTTFText($img, 9, 0, $length, 78, $textcolor, $font2, $solostring);
            
        }
        // Send Result
        ob_start();
        imagepng($img);
        $data = ob_get_clean();
        $this->cache->put('_BANNERCACHE_sigdetailed_'.($player_data !== null ? md5($player) : 'null'), $data, 24 * 60 * 60);
        return $data;
    }
    
    public function drawSignatureLarge($player)
    {
        $player_data = $this->controller_helper->backend_yaml_query('getplayer/'.$player, 15 * 60);
                
        // Check Cache
        $cache_img = $this->cache->get('_BANNERCACHE_siglarge_'.($player_data !== null ? md5($player) : 'null'));
        
        if ($cache_img !== false)
        {
            return $cache_img;
        }
        // Transparent Image
        $img = imagecreatetruecolor(550, 100);
        imagesavealpha($img, true);
        $trans_colour = imagecolorallocatealpha($img, 0, 0, 0, 127);
        imagefill($img, 0, 0, $trans_colour);
        
        if ($player_data !== null)
        {
            $logo = false;
            switch($player_data['Player']['Realm'])
            {
                case 'albion':
                    $logo = 'alb';
                break;
                case 'midgard':
                    $logo = 'mid';
                break;
                case 'hibernia':
                    $logo = 'hib';
                break;
            }
            
            $background = imagecreatefrompng($this->root_path.'styles/all/theme/images/signatures/large_'.$logo.'.png');
            $imgx = imagesx($img); $imgy = imagesy($img);
            $backgroundx = imagesx($background); $backgroundy = imagesy($background);
            // Draw Background
            imagecopyresampled($img, $background, 0, 0, 0, 0, $imgx, $imgy, $backgroundx, $backgroundy);
            
            $emblem = false;
            // Get Emblem
            if ($player_data['Player']['GuildName'] !== null && $player_data['Player']['GuildName'] !== '')
            {
                $emblem = imagecreatefromstring($this->drawBanner(rawurlencode($player_data['Player']['GuildName'])));
                $emblemx = imagesx($emblem); $emblemy = imagesy($emblem);
                imagecopyresampled($img, $emblem, 7, 7, 0, 0, $emblemx, $emblemy, $emblemx, $emblemy);
            }

            // Offset
            $offset = 12;
            if ($emblem !== false)
                $offset += 7 + imagesx($emblem);

            // Draw Text
            $font = $this->root_path.'styles/all/theme/images/fonts/univers.ttf';
            $font2 = $this->root_path.'styles/all/theme/images/fonts/celtic.ttf';
            $textcolor = imagecolorallocate($img, 220, 220, 220);
            
            $namestring = $player_data['Player']['Name'].' '.$player_data['Player']['LastName'];
            $guildstring = $player_data['Player']['GuildName'] !== null && $player_data['Player']['GuildName'] !== '' ? '<'.$player_data['Player']['GuildName'].'>' : '';
            $raceclassstring = $player_data['Player']['Race'].' '.$player_data['Player']['Class'];
            $realmstring = $player_data['Player']['RealmTitle'].' - '.$player_data['Player']['RealmRank'];
            $rpstring = number_format($player_data['Player']['RealmPoints'], 0, ',', ' ').' RP';

            $namebox = ImageTTFText($img, 11, 0, $offset, 25, $textcolor, $font2, $namestring);
            $guildbox = ImageTTFText($img, 10, 0, $offset, 42, $textcolor, $font, $guildstring);
            $raceclassbox = ImageTTFText($img, 10, 0, $offset, 57, $textcolor, $font, $raceclassstring);
            $realmbox = ImageTTFText($img, 10, 0, $offset, 72, $textcolor, $font, $realmstring);
            $rpbox = ImageTTFText($img, 10, 0, $offset, 87, $textcolor, $font, $rpstring);
            
            // Right Box
            $killstring = 'Kills: '.number_format($player_data['Player']['KillsAlbionPlayers'] + $player_data['Player']['KillsMidgardPlayers'] + $player_data['Player']['KillsHiberniaPlayers'], 0, ',', ' ');
            $rankstring = 'Rank on Server: '.$player_data['Player']['Ranking'];
            $rankrealmstring = 'Rank in Realm: '.$player_data['Player']['RankingRealm'];
            $rankclassstring = 'Rank in Class: '.$player_data['Player']['RankingClass'];
            
            $killsbox = imagettfbbox(10, 0, $font, $killstring);
            $length = abs($killsbox[4] - $killsbox[0]);
            $rankbox = imagettfbbox(10, 0, $font, $rankstring);
            $length = abs($rankbox[4] - $rankbox[0]) > $length ? abs($rankbox[4] - $rankbox[0]) : $length;
            $rankrealmbox = imagettfbbox(10, 0, $font, $rankrealmstring);
            $length = abs($rankrealmbox[4] - $rankrealmbox[0]) > $length ? abs($rankrealmbox[4] - $rankrealmbox[0]) : $length;
            $rankclassbox = imagettfbbox(10, 0, $font, $rankclassstring);
            $length = abs($rankclassbox[4] - $rankclassbox[0]) > $length ? abs($rankclassbox[4] - $rankclassbox[0]) : $length;
            
            $offset = $imgx - $length - 30;
            
            ImageTTFText($img, 10, 0, $offset, 42, $textcolor, $font, $killstring);
            ImageTTFText($img, 10, 0, $offset, 57, $textcolor, $font, $rankstring);
            ImageTTFText($img, 10, 0, $offset, 72, $textcolor, $font, $rankrealmstring);
            ImageTTFText($img, 10, 0, $offset, 87, $textcolor, $font, $rankclassstring);
       }
        // Send Result
        ob_start();
        imagepng($img);
        $data = ob_get_clean();
        $this->cache->put('_BANNERCACHE_siglarge_'.($player_data !== null ? md5($player) : 'null'), $data, 24 * 60 * 60);
        return $data;
    }
    /** EndRegion Banners **/
    
}
