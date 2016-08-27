@extends('template')
@section('content')
    <div class="ali center">
        @if($result)
            <div class="by">
                <h4 class="ty">
                    提示信息
                </h4>
                <div class="ph alert-info">
                    @foreach($result['command'] as $index=>$cmd)
                    <label>[{{ $cmd }}]($result['result'][$index][0])($result['result'][$index][1]) </label>
                    @endforeach
                </div>
            </div>
        @endif
        @foreach($menus['groups'] as $title=>$actionGroup)
            <div class="by">
                <h4 class="ty">
                    {{ $title }}
                </h4>
                @foreach($actionGroup as $index=>$action)
                    <div class="ph">
                        <a href="?group={{ $title }}&index={{ $index }}&device={{ $action['config_device'] }}&action={{ $action['action'] }}">{{ $action['title'] }}</a>
                    </div>
                @endforeach
            </div>
        @endforeach

    </div>
@endsection
