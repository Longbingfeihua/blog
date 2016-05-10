<html>
<head>
    <meta charset="utf-8">
    <title>recircle</title>
    <style>
        .main{
            margin: 0 auto;
            width:500px;
            height:500px;
            position:relative;
            border:1px solid grey;
        }
        .main div{
            position:absolute;
            border:1px solid grey;
        }
        .leftSide{
            top:20px;
        }
        .middle{
            z-index:2;
            top:20px;
        }
        .rightSide{
            top:20px;
        }
        .rl{
            left:0;
            top:20px;
        }
    </style>
    <script type="text/javascript" src="js/jquery2.1.js"></script>
</head>
<body>
<div class="main">
    <div class="leftSide"></div>
    <div class="middle"></div>
    <div class="rightSide"></div>
    <div class="rl"></div>
    <a>click</a>
</div>
<script>
    //入栈,出栈.
    var main = $(".main");
    var ls = $(".leftSide");
    var md = $(".middle");
    var rs = $(".rightSide");
    var rl = $(".rl");

    function change(ls,rs,md,rl){
        ls.css({width:main.width()/4,height:main.height()/2,left:0,background:'grey'});
        rs.css({width:main.width()/4,height:main.height()/2,right:0,background:'blue'});
        md.css({width:main.width()/2,height:main.height()/2,right:main.width()/4,background:'green'});
        rl.css({height:main.height()/2,background:'blue'});
        ls.animate({width: main.width() / 2, left: main.width() / 4, zIndex: 2});
        rs.animate({width: 0});
        md.animate({width: main.width() / 4, right: 0, zIndex: 0});
        rl.animate({width: main.width() / 4});
    }
    $("a").bind('click',change);
</script>
</body>
</html>
