<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>文档</title>
    <link rel="stylesheet" href="{{asset('static/nav/css/style.css')}}">
</head>
<body>

<header class="header">
    <h1>文档</h1>
    <p>内部文档</p>
</header>
<div class="wrapper">
    <div class="cols">
        @foreach($config as $key => $value)
            <div class="col" ontouchstart="this.classList.toggle('hover');">
                <a href="{{route('doc.index',[$value->id])}}">
                    <div class="container">
                        <div class="front" style="background-image: url({{asset('static/nav/img/'.rand(1,8).'.png')}})">
                            <div class="inner">
                                <p>{{$value['name']}}</p>
                            </div>
                        </div>
                        <div class="back">
                            <div class="inner">
                                <p>项目名称: {{$value['app_name']}}</p>
                                <p>域名: {{$value['path']}}</p>
                                <p>版本号: {{$value['v']}}</p>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        @endforeach
    </div>
</div>

</body>
</html>
