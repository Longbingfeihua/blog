<head>
    <title>标题</title>
<style>
    h2{
        text-align:center;
        cursor:pointer;
    }
    .main{
        margin:0 auto;
        width:1000px;
        height:1000px;
        border:1px solid #ddd;
    }
    .aside{
        width:0px;
        height:1000px;
        background-color: #2e6da4;
    }
    img{
        width:0px;
        height:150px;
    }
</style>
<script src="js/jquery2.1.js"></script>
</head>
<body>
<h2>测试侧边</h2>
<div class="main">
    <div class="aside"><img src="image/20160425155656_97773.gif"></div>
</div>
<script>
    var i = 0;
    function tip(){
        switch (i) {
            case 0:
                    $(".aside,img").stop(0).animate({width:"300px"},'500',function(){
                        i = 1;
                });
                break;
            case 1:
                    $(".aside,img").stop(0).animate({width:"0px"},'500',function(){
                        i = 0;
                    })
                break;
        }
    }
    $("h2").bind('click',tip);
</script>
</body>