<!DOCTYPE HTML>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>API在线文档</title>
    <link href="{{asset('static/source/api.css')}}" rel="stylesheet" type="text/css" />
    <script language="javascript" src="{{asset('static/source/jquery.min.js')}}"></script>
    <script language="javascript" src="{{asset('static/source/jquery.dimensions.js')}}"></script>
</head>
<style>
    td {
        text-align:center;
    }
    tr {
        text-align:center;
    }
</style>
<body>
<div class="tit">
    <div id="titcont">
        项目名称:{{$project->name}}<span class="sma"></span>
    </div>
</div>
<div id="cont">
    <div class='fun'>
        <div class='lineface'>一、接口总述 </div>
        <span class="le">#.<em style="font-size: 20px">全局参数说明</em></span>
        <span class='ri'></span>
        <div class='says'>
            请求地址：{{$project->path}}<br>
            url参数:　api/{id} api/1<br>
            query参数:　?pageSize=10<br>
            body参数:　body体参数<br>
        </div>
    </div>
    <div class='fun'>
        <div class='lineface'>二、相关接口</div>
        @foreach($apis as $value)
        <a name="{{$value->name}}"></a>
        <span class='le'>#.<em style="font-size: 20px">{{$value->name}}</em> <b>描述:{{$value->desc}}</b></span>
        <span class='ri'>方式:<em> {{$value->method}}</em></span>
        <span class='ri'>路由:<em> <a href='{{$value->path}}' target='_blank'>{{$value->path}}</a> </em></span>
        @if(!$value->params->isEmpty())
            <div class='says'>传参说明:</div>
            <div class="says">
                <table>
                    <tr>
                        <th width="100">参数</th>
                        <th width="100">说明</th>
                        <th width="100">必填</th>
                        <th width="100">事例</th>
                        <th width="100">类型</th>
                    </tr>

                        @foreach($value->params as $vv)
                            <tr>
                                <td>{{$vv->name}}</td>
                                <td>{{$vv->desc}}</td>
                                <td>{{$vv->is_must}}</td>
                                <td>{{$vv->example}}</td>
                                <td>
                                    @if($vv->type == 1 )
                                        url
                                    @elseif($vv->type == 2)
                                        query
                                    @elseif($vv->type == 3)
                                        body
                                    @endif
                                </td>
                            </tr>
                        @endforeach

                </table>
            </div>
        @endif
        <div class='says'>返回结构示例：
        <pre class="intersays">

暂无
        </pre>
        </div>
        <br>
        <br>
        <br>
        @endforeach

    </div>

    <div class="info">
        <b>*</b> 本接口文档最后更新时间：<span class="red"><?php echo date("Y-m-d H:i:s",filemtime(__FILE__));?></span><br>
    </div>
</div>
<div id="foot">
    faed提供技术支持
</div>


<!--浮动接口导航栏-->
<div id="floatMenu">
    <ul class="menu"></ul>
</div>
<script language="javascript">
    var name = "#floatMenu";
    var menuYloc = null;
    $(document).ready(function(){
        $(".le > em").each(function(index, element){
            $(".menu").append(" <li><a href='#"+ $(this).text() +"'>"+ $(this).text()+"</a></li>");
        });
        menuYloc = parseInt($(name).css("top").substring(0,$(name).css("top").indexOf("px")))
        $(window).scroll(function () {
            offset = menuYloc+$(document).scrollTop()+"px";
            $(name).animate({top:offset},{duration:500,queue:false});
        });
    });
</script>
</body>
</html>
