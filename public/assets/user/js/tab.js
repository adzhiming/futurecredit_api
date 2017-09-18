/**
 * Created by Administrator on 2017/8/17.
 */
(function () {
    function change(tab){
        var $tab = $(tab);
        $tab.find("li").on("click",function(){
            var index = $(this).index();
            $(this).addClass("curr").siblings().removeClass("curr");
        })
    }
    change(".tab1","");
})()
