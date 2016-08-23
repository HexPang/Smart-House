<?php

/**
 * Created by PhpStorm.
 * User: hexpang
 * Date: 16/8/23
 * Time: 21:05
 */
namespace App\Services;
use Illuminate\Support\Facades\Facade;

class RemoteService extends Facade
{
    var $handle;
    var $host;
    var $port;
    var $user;
    var $password;
    public function __construct($host,$port,$user,$password)
    {
        $this->host = $host;
        $this->port = $port;
        $this->user = $user;
        $this->password = $password;
    }

    function Ping($host,$port = 22,$waitTimeoutInSeconds = 10){
        $succ = false;
        if($fp = @fsockopen($host,$port,$errCode,$errStr,$waitTimeoutInSeconds)){
            $succ = true;
            fclose($fp);
        }
        return $succ;
    }
    public function Disconnect(){
        $this->Execute('exit');
        return true;
    }
    public function Connect(){
//        Logger::Info('Ping ' . $this->host . ":" . $this->port,4);

        if(!$this->Ping($this->host,$this->port)){
            Logger::Info('Ping Timeout.',5);
            return false;
        }
//        Logger::Info("Connecting...",5);
        $this->handle = @ssh2_connect($this->host,$this->port);
        if(!$this->handle){
//            Logger::Info("Cannot Connect To Server.",6);
            return false;
        }
        return true;
    }
    public function Authorize(){
//        Logger::Info("Authorizing...({$this->user}:{$this->password})",5);
        $ret = @ssh2_auth_password( $this->handle, $this->user, $this->password );
        return $ret;
    }
    function Execute($command){
        $stream = @ssh2_exec($this->handle, $command);
        if($stream){
            $errorStream = ssh2_fetch_stream($stream, SSH2_STREAM_STDERR);
            stream_set_blocking($stream, true);
            stream_set_blocking($errorStream, true);
            $response = stream_get_contents( $stream );
            $errorInfo = stream_get_contents( $errorStream );
            fclose( $stream );
            fclose( $errorStream );
            return [$response,$errorInfo];
        }else{
            return null;
//            Logger::Info("Cannot fetch stream.",3);
        }
    }

    protected static function getFacadeAccessor() {
        return 'SSH';
    }
}