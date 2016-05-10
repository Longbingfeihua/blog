<html>
<head>
    <meta charset="utf-8">
    <title>cal</title>
    <style>
        .container{
            margin:0 auto;
            background-image: url('image/cal_bg.jpg');
        }
        .main{
            margin:0 auto;
            width:100%;
            height:100%;
            border:1px solid grey;
            position:relative;
        }
        .date{
            border:1px solid grey;
            position:absolute;
        }
        .show{
            display: inline-block;
            height:30px;
            width:50px;
            text-align: center;
            line-height: 30px;
            top:0;
            right:0;
            position:absolute;
            border:1px solid darkslategrey;
        }
        .day{
            margin:0 auto;
            width:100%;
            border:1px solid grey;
            position:relative;
        }
    </style>
    <script type="text/javascript" src="js/jquery2.1.js"></script>
</head>
<body>
    <div class="container">
        <h2></h2>
        <div class="day"></div>
        <div class="main">
        </div>
    </div>
<script>
    var cm = 0;
    var width = $(".main").width()/7;
    var height = $(".main").height()/5;
    var week = ['一','二','三','四','五','六','日'];

    //遍历星期
    $.each(week,function(n,value){
        var weekDiv = $('<div></div>').css({width:width,height:'30px',textAlign:'center',lineHeight:'30px',
                    position:'absolute',bottom:0,left:n*width})
                .html
                ('星期'+value);
        $(".day").append(weekDiv);
    });

    function setDate(m)
    {
        var date = new Date();
        date.setMonth(date.getMonth()+1+m);
        date.setDate(0);
        date.getDate();

        return date;
    }
    function initDate(m){
        /*设置日期*/
        var date = setDate(m);
        $('h2').html('<span onclick="changecm(-1)"> << </span>'+(date.getFullYear())+'年'+(date.getMonth()+1)+'月<span ' +
                'onclick="changecm' +
                '(1)' +
                '"> >> </span>').css('text-align','center');
        $('h2 span').css('cursor','pointer');
        for(var i=1;i<=date.getDate();i++){
            var left = i%7-1 == -1 ? 6 : i%7-1;
            var top = Math.ceil(i/7)-1;
            var newDate = $('<div></div>').addClass('date').css({width:width,height:height,left:left*width,
                top:top*height,cursor:'pointer'});
            $('<span></span>').html(i==new Date().getDate() && cm==0 ? i+'今天' : i).addClass('show').appendTo(newDate);
            $('.main').append(newDate);
        }
        var shiftx = $('.main div span').width();
        var shifty = $('.main div span').height();
        $('.main div').hover(function(){
            $(this).children().stop(0,true).animate({right:(width-shiftx)/2});
        },function(){
            $(this).children().stop(0,true).animate({right:0});
        });
    }
    function changecm(n){
        cm = cm+n;
        $(".main").empty();
        initDate(cm);
    }
    $(document).ready(initDate(cm));
</script>
</body>
</html>