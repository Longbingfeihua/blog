<html>
<head>
    <title>标题</title>
<style>
    h2{
        text-align:center;
        cursor:pointer;
    }
    .main,.step2{
        margin:0 auto;
        width:1000px;
        height:500px;
        border:1px solid #ddd;
    }
    .aside{
        width:0px;
        height:500px;
        float:left;
        background-color: #2e6da4;
        position:relative;
    }
    img{
        width:0px;
        height:150px;
        cursor:pointer;
    }
    .aside a{
        position:absolute;
        right:0;
        bottom:0;
    }
    .build{
        width:698px;
        height:500px;
        float:right;
        position:relative;

    }
    .buildDiv{
        border:1px solid #ddd;
        position:absolute;
        bottom:0px;
    }
    .circle{
        position:absolute;
        left:198px;
        top:198px;
        width:4px;
        height:4px;
        background:#F00;
    }
</style>
<script src="js/jquery2.1.js"></script>
</head>
<body>
<h2>测试侧边</h2>
<div class="main">
    <div class="aside"><img src="image/20160425155656_97773.gif"></div>
    <div class="build"></div>
</div>
<script>
    var data = {
        i:0,
        j:0,
        arr:[100,200,300,400,250,300,200,195]
    };
    function tip(){
        switch (data.i) {
            case 0:
                    $(".aside,img").stop(0).animate({width:"300px"},'500',function(){
                        data.i = 1;
                });
                break;
            case 1:
                    $(".aside,img").stop(0).animate({width:"0px"},'500',function(){
                        data.i = 0;
                    });
                break;
        }
        if(data.j != 0){
            sharp();
        }
    }
    function sharp(){
        var build = $(".build");
        if(data.j == 0) {
            var width = build.width() / (data.arr.length * 2 + 1);
            $.each(data.arr, function (n, value) {
                var div = $('<div></div>');
                div.addClass('buildDiv');
                div.css({
                    'width': width,
                    'left': width * (2 * n + 1),
                    'background-color': '#2e6da4'
                });
                div.appendTo(build);
                div.animate({height: value, innerHTML: value}, 2000);
            });
            data.j=1;
        }else{
            $(".build div").animate({height: 0, innerHTML: 0}, 2000,function(){
                this.remove();
            });
            data.j=0;
        }
    }
    $("h2").bind('click',tip);
    $("img").bind('click',sharp);
//    setInterval(function(){},ms)
</script>
</body>
</html>