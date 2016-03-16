<?php
/**
*
* @package DOL Extension 0.0.1
* @copyright (c) 2016 Leodagan
* @license MIT
*
*/
namespace dol\status\controller;

use phpbb\template\template;
use Symfony\Component\Yaml\Parser;
use phpbb\cache\driver;
use phpbb\config\config;
use Symfony\Component\HttpFoundation\Response;

class helper
{
    /* @var template */
    protected $template;
    
    /** @var \phpbb\cache\driver\driver_interface */
    protected $cache;

     /* @var config */
    protected $config;

    /**
    * phpBB root path
    * @var string
    */
    protected $phpbb_root_path;
    
    /**
    * Extension root path
    * @var string
    */
    protected $root_path;

    /** @var Symfony\Component\Yaml\Parser */
    protected $parser;
    
    /**
    * Constructor
    *
    * @param template $template
    */
    public function __construct(template $template, $cache, config $config, $phpbb_root_path)
    {
        $this->template = $template;
        $this->cache = $cache;
        $this->config = $config;
        $this->phpbb_root_path = $phpbb_root_path;
        $this->root_path = $phpbb_root_path . 'ext/dol/status/';
        $this->parser = new Parser();
    }
    
    /** Region BackendQuery **/
    public function backend_yaml_query($service, $cachettl)
    {
        $cache_get = $this->cache->get('_YMLBACKEND_'.$service);
        
        if ($cache_get === FALSE)
        {
            $content = $this->backend_raw_query($service, $cachettl);
            try
            {
                $value = $this->parser->parse($content);
                $this->cache->put('_YMLBACKEND_'.$service, $value, $cachettl);
                return $value;
            }
            catch (ParseException $e)
            {
                return array('Y_Exception' => $e->getMessage());
            }
        }
        
        return $cache_get;
    }
    
    protected function backend_raw_query($service, $cachettl)
    {
        $backend_url = 'https://karadok.freyad.net/';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $backend_url.$service);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // The --cacert option
        curl_setopt($ch, CURLOPT_CAINFO, '/var/lib/openshift/56c899540c1e66e27c000049/app-root/data/ssl/server.cert');
        // The --cert option
        curl_setopt($ch, CURLOPT_SSLCERT, '/var/lib/openshift/56c899540c1e66e27c000049/app-root/data/ssl/client.pem');
        $raw_get = curl_exec($ch);
        curl_close($ch);
        return $raw_get;
    }
    /** EndRegion BackendQuery **/


    /** Region - YAML to Template Parser **/
    public function assign_yaml_vars($yaml)
    {
        foreach($yaml as $key => $value)
        {
            if (is_array($value))
            {
                if ($this->is_assoc($value))
                    $this->append_yaml_dictroot($value, 'Y_'.$key);
                else
                    $this->append_yaml_list($value, 'Y_'.$key);
            }
            else
            {
                $this->template->assign_var('Y_'.$key, $value);
            }
        }
    }
    
    protected function append_yaml_dictroot($dict, $prefix)
    {
         foreach ($dict as $key => $value)
        {
            if (is_array($value))
            {
                if ($this->is_assoc($value))
                    $this->append_yaml_dictroot($value, $prefix.'_'.$key);
                else
                    $this->append_yaml_list($value, $prefix.'_'.$key);
            }
            else
            {
                $this->template->assign_var($prefix.'_'.$key, $value);
            }
        }
    }

    protected function append_yaml_list($list, $prefix)
    {
        foreach ($list as $key => $value)
        {
            if (is_array($value))
            {
                $this->template->assign_block_vars($prefix, array('KEY' => $key));

                if ($this->is_assoc($value))
                    $this->append_yaml_dict($value, $prefix);
                else
                    $this->append_yaml_list($value, $prefix.'.VALUE');
            }
            else
            {
                $this->template->assign_block_vars($prefix, array(
                    'KEY' => $key,
                    'VALUE' => $value
                ));
            }
        }
    }
    
    protected function append_yaml_dict($dict, $prefix, $next = false)
    {
        foreach ($dict as $key => $value)
        {
            $realkey = $next === FALSE ? $key : $next.'_'.$key;
            if (is_array($value))
            {
                if ($this->is_assoc($value))
                    $this->append_yaml_dict($value, $prefix, $realkey);
                else
                    $this->append_yaml_list($value, $prefix.'.'.$realkey);
            }
            else
            {
                $this->template->alter_block_array($prefix, array($realkey => $value), true, 'change');
            }
        }
    }
    
    protected function is_assoc($array)
    {
        return array_values($array)!==$array;
    }
    /** EndRegion - YAML to Template Parser **/

    /** Region Banners **/
    public function drawBanner($guild)
    {
        // Retrieve Guild Data
        $guild_data = $this->backend_yaml_query('getguild/'.$guild, 15 * 60);
        
        // Defaults
        $headers = array(
            'Content-Type'     => 'image/png',
            'Content-Disposition' => 'inline; filename="'.$guild.'"');
        
        // Check Cache
        $cache_img = $this->cache->get('_BANNERCACHE_banner_'.($guild_data !== null ? md5($guild) : 'null'));
        
        if ($cache_img !== false)
        {
            return new Response($cache_img, 200, $headers);
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
        return new Response($data, 200, $headers);
    }
    
    public function drawSignatureSmall($player)
    {
        $player_data = $this->backend_yaml_query('getplayer/'.$player, 15 * 60);
        
        // Defaults
        $headers = array(
            'Content-Type'     => 'image/png',
            'Content-Disposition' => 'inline; filename="'.$player.'"');
        
        // Check Cache
        $cache_img = $this->cache->get('_BANNERCACHE_sigsmall_'.($player_data !== null ? md5($player) : 'null'));
        
        if ($cache_img !== false)
        {
            return new Response($cache_img, 200, $headers);
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
            if ($player_data['GuildName'] !== '')
                $emblem = imagecreatefromstring($this->drawBanner($player_data['GuildName'])->getContent());
            
            $logo = false;
            switch($player_data['Realm'])
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
        }
        
        // Send Result
        ob_start();
        imagepng($img);
        $data = ob_get_clean();
        $this->cache->put('_BANNERCACHE_banner_'.($player_data !== null ? md5($player) : 'null'), $data, 24 * 60 * 60);
        return new Response($data, 200, $headers);
    }
    /** EndRegion Banners **/
}