<?php

require_once( dirname(__FILE__).'/../../php/cache.php');
require_once( dirname(__FILE__).'/../../php/util.php');
require_once( dirname(__FILE__).'/../../php/settings.php');
eval(getPluginConf('discordpopopush'));

class Discord {

    public $hash = "discordpopo.dat";
    public $log = array
    (
        "discordpopo_enabled"=>0,
        "discordpopo_addition"=>0,
        "discordpopo_finish"=>0,
        "discordpopo_deletion"=>0,
        "discordpopo_webhook"=>'',
        "discordpopo_avatar"=>'',
        "discordpopo_pushuser"=>'',
    );

    public function store()
    {
        $cache = new rCache();
        return($cache->set($this));
    }
    public function set()
    {
        if(!isset($HTTP_RAW_POST_DATA))
            $HTTP_RAW_POST_DATA = file_get_contents("php://input");
        if(isset($HTTP_RAW_POST_DATA))
        {
            $vars = explode('&', $HTTP_RAW_POST_DATA);
            foreach($vars as $var)
            {
                $parts = explode("=",$var);
                $this->log[$parts[0]] = in_array($parts[0], array('discordpopo_webhook', 'discordpopo_avatar', 'discordpopo_pushuser')) ? $parts[1] : intval($parts[1]);
            }
            $this->store();
            $this->setHandlers();
        }
    }
    public function get()
    {
        if (function_exists("safe_json_encode")) {
            return("theWebUI.discordpopo = ".safe_json_encode($this->log).";");
        } else {
            // We dont really need safe_json_encode here since we dont store any values other than numeric and hash values, but sometimes this throws an error
            return("theWebUI.discordpopo = ".json_encode($this->log).";");
        }
    }

    public function setHandlers()
    {
        global $rootPath;
        if($this->log["discordpopo_enabled"] && $this->log["discordpopo_addition"])
        {
            $addCmd = getCmd('execute').'={'.getPHP().','.$rootPath.'/plugins/discordpopopush/push.php'.',1,$'.
                getCmd('d.get_name').'=,$'.getCmd('d.get_size_bytes').'=,$'.getCmd('d.get_bytes_done').'=,$'.
                getCmd('d.get_up_total').'=,$'.getCmd('d.get_ratio').'=,$'.getCmd('d.get_creation_date').'=,$'.
                getCmd('d.get_custom').'=addtime,$'.getCmd('d.get_custom').'=seedingtime'.
                ',"$'.getCmd('t.multicall').'=$'.getCmd('d.get_hash').'=,'.getCmd('t.get_url').'=,'.getCmd('cat').'=#",$'.
                getCmd('d.get_custom1')."=,$".getCmd('d.get_custom')."=x-pushbullet,".
                getUser().'}';
        }
        else
            $addCmd = getCmd('cat=');
        if($this->log["discordpopo_enabled"] && $this->log["discordpopo_finish"])
            $finCmd = getCmd('execute').'={'.getPHP().','.$rootPath.'/plugins/discordpopopush/push.php'.',2,$'.
                getCmd('d.get_name').'=,$'.getCmd('d.get_size_bytes').'=,$'.getCmd('d.get_bytes_done').'=,$'.
                getCmd('d.get_up_total').'=,$'.getCmd('d.get_ratio').'=,$'.getCmd('d.get_creation_date').'=,$'.
                getCmd('d.get_custom').'=addtime,$'.getCmd('d.get_custom').'=seedingtime'.
                ',"$'.getCmd('t.multicall').'=$'.getCmd('d.get_hash').'=,'.getCmd('t.get_url').'=,'.getCmd('cat').'=#",$'.
                getCmd('d.get_custom1')."=,$".getCmd('d.get_custom')."=x-pushbullet,".
                getUser().'}';
        else
            $finCmd = getCmd('cat=');
        if($this->log["discordpopo_enabled"] && $this->log["discordpopo_deletion"])
            $delCmd = getCmd('execute').'={'.getPHP().','.$rootPath.'/plugins/discordpopopush/push.php'.',3,$'.
                getCmd('d.get_name').'=,$'.getCmd('d.get_size_bytes').'=,$'.getCmd('d.get_bytes_done').'=,$'.
                getCmd('d.get_up_total').'=,$'.getCmd('d.get_ratio').'=,$'.getCmd('d.get_creation_date').'=,$'.
                getCmd('d.get_custom').'=addtime,$'.getCmd('d.get_custom').'=seedingtime'.
                ',"$'.getCmd('t.multicall').'=$'.getCmd('d.get_hash').'=,'.getCmd('t.get_url').'=,'.getCmd('cat').'=#",$'.
                getCmd('d.get_custom1')."=,$".getCmd('d.get_custom')."=x-pushbullet,".
                getUser().'}';
        else
            $delCmd = getCmd('cat=');
        $req = new rXMLRPCRequest( array(
            rTorrentSettings::get()->getOnInsertCommand( array('tdiscordpopo'.getUser(), $addCmd ) ),
            rTorrentSettings::get()->getOnFinishedCommand( array('tdiscordpopo'.getUser(), $finCmd ) ),
            rTorrentSettings::get()->getOnEraseCommand( array('tdiscordpopo'.getUser(), $delCmd ) ),
        ));
        $res = $req->success();
        return($res);
    }

    static public function load()
    {
        $cache = new rCache();
        $ar = new Discord();
        if($cache->get($ar))
        {
            if(!array_key_exists("discordpopo_enabled",$ar->log))
            {
                $ar->log["discordpopo_enabled"] = 0;
                $ar->log["discordpopo_addition"] = 0;
                $ar->log["discordpopo_finish"] = 0;
                $ar->log["discordpopo_deletion"] = 0;
                $ar->log["discordpopo_webhook"] = '';
                $ar->log["discordpopo_avatar"] = '';
                $ar->log["discordpopo_pushuser"] = '';
            }
        }
        return($ar);
    }

    public function pushNotify($data)
    {
        global $discordNotifications;
        $actions = array
        (
            1 => 'Added',
            2 => 'Finished',
            3 => 'Deleted',
        );
        //$section = $discordNotifications[$actions[$data['action']]];
        $fields = array();

        switch ($data['action']) {
            case 1:
                $fields[] = array("name" => "Name", "value" => $data['name']);
                if (!empty($data['label'])) $fields[] = array("name" => "Label", "value" => $data['label']);
                $fields[] = array("name" => "Size", "value" => self::bytes(round($data['size'],2)));
                //$fields[] = array("name" => "Added", "value" => strftime('%c',$data['added']));
                $fields[] = array("name" => "Tracker", "value" => parse_url($data['tracker'], PHP_URL_HOST));
                $color = 4886754;
                break;
            case 2:
                $fields[] = array("filename" => "Filename", "value" => $data['filename']);
                $fields[] = array("path" => "Path", "value" => $data['path']);
                $fields[] = array("name" => "Name", "value" => $data['name']);
                if (!empty($data['label'])) $fields[] = array("name" => "Label", "value" => $data['label']);
                $fields[] = array("name" => "Size", "value" => self::bytes(round($data['size'],2)));
                //$fields[] = array("name" => "Downloaded", "value" => self::bytes(round($data['downloaded'],2)));
                //$fields[] = array("name" => "Uploaded", "value" => self::bytes(round($data['uploaded'],2)));
                //$fields[] = array("name" => "Ratio", "value" => $data['ratio']);
                //$fields[] = array("name" => "Added", "value" => strftime('%c',$data['added']));
                //$fields[] = array("name" => "Finished", "value" => strftime('%c',$data['finished']));
                $fields[] = array("name" => "Tracker", "value" => parse_url($data['tracker'], PHP_URL_HOST));
                $color = 8311585;
                break;
            case 3:
                $fields[] = array("name" => "Name", "value" => $data['name']);
                if (!empty($data['label'])) $fields[] = array("name" => "Label", "value" => $data['label']);
                $fields[] = array("name" => "Size", "value" => self::bytes(round($data['size'],2)));
                //$fields[] = array("name" => "Downloaded", "value" => self::bytes(round($data['downloaded'],2)));
                //$fields[] = array("name" => "Uploaded", "value" => self::bytes(round($data['uploaded'],2)));
                //$fields[] = array("name" => "Ratio", "value" => $data['ratio']);
                //$fields[] = array("name" => "Creation", "value" => strftime('%c',$data['creation']));
                //$fields[] = array("name" => "Added", "value" => strftime('%c',$data['added']));
                //$fields[] = array("name" => "Finished", "value" => strftime('%c',$data['finished']));
                $fields[] = array("name" => "Tracker", "value" => parse_url($data['tracker'], PHP_URL_HOST));
                $color = 10562619;
                break;
        }

        $avatarUrl = !empty($this->log['discordpopo_avatar']) ? $this->log['discordpopo_avatar'] : null;
        $botUsername = !empty($this->log['discordpopo_pushuser']) ? $this->log['discordpopo_pushuser'] : null;

        $payload = json_encode(array(
            "content" => "",
            'avatar_url' => $avatarUrl,
            "username" => $botUsername,
            "embeds" => array(
                array(
                    "title" => "Torrent ".$actions[$data['action']].": ".$data['name'],
                    "action" => $actions[$data['action']],
                    "color" => $color,
                    "timestamp" => date('Y-m-d\TH:i:s.u'),
                    "thumbnail" => array(
                        "url" => $avatarUrl
                    ),
                    "author" => array(
                        "name" => "rutorrent-discordpopo"
                    ),
                    "fields" => $fields
                )
            )
        ), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);


        $ch = curl_init($this->log['discord_webhook']);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Length: '.strlen($payload)));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $res = curl_exec($ch);
        curl_close($ch);
    }

    static protected function bytes( $bt )
    {
        $a = array('B', 'KiB', 'MiB', 'GiB', 'TiB', 'PiB');
        $ndx = 0;
        if($bt == 0)
            $ndx = 1;
        else
        {
            if($bt < 1024)
            {
                $bt = $bt / 1024;
                $ndx = 1;
            }
            else
            {
                while($bt >= 1024)
                {
                    $bt = $bt / 1024;
                    $ndx++;
                }
            }
        }
        return((floor($bt*10)/10)." ".$a[$ndx]);
    }

}
