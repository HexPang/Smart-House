<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use hexpang\Client\SSHClient\SSHClient;

class HomeController extends Controller
{
    private function GetSSH($device){
        $result = "未能正常执行.";
        $config = ['IP','PORT','USER','PASSWORD'];
        foreach($config as $conf){
            if(env("{$device}_{$conf}") == null){
              if($conf == 'PASSWORD'){
                if(env("{$device}_PK") == null){
                  return "请配置 [ {$device}_{$conf} ] 参数.";
                }
              }
            }
        }
        $ssh = new SSHClient(env("{$device}_IP"),env("{$device}_PORT"),env("{$device}_USER"),env("{$device}_PASSWORD"));
        $succ = $ssh->ping(env("{$device}_IP"),env("{$device}_PORT"),5);
        if($succ){
            $succ = $ssh->connect();
            if($succ){
                if(env("{$device}_PK")){
                    $succ = $ssh->authorizeWithPK(env("{$device}_PK"));
                }else{
                    $succ = $ssh->authorize();
                }
                if($succ){
                    return $ssh;
                }else{
                    $result = "帐号或密码不正确.";
                }
            }else{
                $result = "无法连接到设备.";
            }
        }else{
            $result = "连接设备超时.";
        }
        return $result;
    }
    private function loadMenus(){
      $file = \File::get(storage_path('config/config.json'));
      $menus = json_decode($file,true);
      return $menus;
    }
    private function execute(Request $request,$menus){
      $ssh = $this->GetSSH($request->device);
      $result = null;
      if(is_string($ssh)){
        $result = $ssh;
      }else{
        $menu = $menus['groups'][$request->group][$request->index];
        foreach($menu['command'] as $cmd){
          $command = null;
          if(is_string($cmd)){
            $command = $cmd;
          }else{
            if(isset($cmd['env'])){
              $command = env($cmd['env']);
            }
          }
          $result = $ssh->cmd($command);
          $ssh->disconnect();
        }
      }
      return $result;
    }
    public function index(Request $request){
        $result = null;
        $menus = $this->loadMenus();
        $action = $request->get('action','');
        if($action){
          $result = $this->execute($request,$menus);
        }
        return view('index',['menus' => $menus,'result' => $result]);
    }
}
