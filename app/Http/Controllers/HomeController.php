<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use SSH;

class HomeController extends Controller
{
    private function GetSSH($device){
        $result = "未能正常执行.";
        $config = ['IP','PORT','USER','PASSWORD'];
        foreach($config as $conf){
            if(env("{$device}_{$conf}") == null){
                return "请配置 [ {$device}_{$conf} ] 参数.";
            }
        }
        $ssh = new SSH(env("{$device}_IP"),env("{$device}_PORT"),env("{$device}_USER"),env("{$device}_PASSWORD"));
        $succ = $ssh->Ping(env("{$device}_IP"),env("{$device}_PORT"));
        if($succ){
            $succ = $ssh->Connect();
            if($succ){
                $succ = $ssh->Authorize();
                if($succ){
                    return $ssh;
                }else{
                    $result = "路由帐号或密码不正确.";
                }
            }else{
                $result = "无法连接到路由.";
            }
        }else{
            $result = "路由端口未开放.";
        }
        return $result;
    }
    private function NAS($action,$request = null){
        $result = "";
        $ssh = $this->GetSSH('NAS');
        if(is_string($ssh)){
            $result = $ssh;
        }else{
            if($action == 'xunlei'){
                $ssh->Execute(env('NAS_THUNDER'));
                $result = "迅雷服务重启完成.";
            }else if($action == "smb"){
                $ssh->Execute('sudo service smbd restart');
                $result = "共享服务重启完成.";
            }else if($action == "minidlna") {
                $ssh->Execute('sudo service minidlna restart');
                $result = "媒体服务重启完成.";
            }else if($action == 'mount'){
                $device = $request ? $request->get('dev','') : '';
                if($device == ''){
                    $device = 'sdb2';
                }
                $r = $ssh->Execute('sudo mount /dev/' . $device . ' /media/DOWN-DRIVE/');
                $result = $r[0] ? $r[0] : $r[1];
            }else{
                $result = "无效操作.";
            }
            $ssh->Disconnect();
        }
        return $result;
    }
    private function Router($action){
        $result = "";
        $ssh = $this->GetSSH('ROUTER');
        if(is_string($ssh)){
            $result = $ssh;
        }else{
            if($action == 'restart_gfw'){
                $ssh->Execute('/etc/init.d/shadowsocks restart');
                $ssh->Execute('/etc/init.d/pdnsd restart');
                $result = "翻墙服务重启完成.";
            }else if($action == "dnsmasq"){
                $ssh->Execute('/etc/init.d/dnsmasq restart');
                $result = "域名服务重启完成.";
            }
            $ssh->Disconnect();
        }
        return $result;
    }
    public function index(Request $request){
        $result = null;
        $actions = [
            '路由相关' => [
                '重启翻墙服务' => '?device=router&action=restart_gfw',
                '重启域名服务' => '?device=router&action=dnsmasq',
            ],
            'NAS' => [
                '挂载外接硬盘' => '?device=nas&action=mount&dev=sdb2',
                '重启迅雷服务' => '?device=nas&action=xunlei',
                '重启共享服务' => '?device=nas&action=smb',
                '重启媒体服务' => '?device=nas&action=minidlna',
            ]
        ];
        $action = $request->get('action','');
        $device = $request->get('device','');
        if($device == 'router'){
            $result = $this->Router($action);
        }else if($device == 'nas'){
            $result = $this->NAS($action);
        }else if($device != '' && $action != ''){
            $result = "无效设备.";
        }
        return view('index',['actions' => $actions,'result' => $result]);
    }
}
