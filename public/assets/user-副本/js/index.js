(function(angular){
    "use strict";
    /*创建模块 注入路由*/
    var app =  angular.module('app',['ui.router']);
    app.controller('appController',['$scope','$window',function ($scope,$window) {

        /*记录当前点击的类型*/
        $scope.type = "card";

        /*监听tabbar通知*/
        $scope.tabbarClick = function (type) {
            $scope.type = type;
            /*发通知，改标题*/
            switch (type){
                case 'card':
                    break;
                case 'loan':
                    break;
                case 'my':
                    break;
            }
        };

    //    切换字体颜色
        $(function(){
            $('.foot li a').click(function(){
                $(this).addClass('active');
                $('.foot li a').not(this).removeClass('active');
            });
        })

    }]);

})(angular)