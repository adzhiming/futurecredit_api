

(function (angular) {
    "use strict";

    angular.module('app').service('loginHttp',['$http',function ($http) {

        this.login = function () {
            /*请求数据*/
            $scope.cardUrl = function (id) {

                $scope.id = id;

                $http({
                    method:'GET',
                    url:IP+'/index.php/api/user/check_all_login'
                }).then(function (data) {
                    //没在登录状态
                    if(data.data.code == -5){

                        $('.all').css({"display":"block"})
                    }else {


                        //登录成功跳转
                        sessionStorage.setItem("id", id);
                        $scope.id = sessionStorage.getItem("id")
                        $http({
                            method:'post',
                            url:IP+'/index.php/api/user/loan_url',
                            data:{id:$scope.id},
                            headers:{'Content-Type': 'application/x-www-form-urlencoded'},
                            transformRequest: function(obj) {
                                var str = [];
                                for(var p in obj){
                                    str.push(encodeURIComponent(p) + "=" + encodeURIComponent(obj[p]));
                                }
                                return str.join("&");
                            }
                        }).then(function(data) {
                            $scope.bankurl =data.data.data.url;
                            window.location.href = $scope.bankurl;

                        })
                    }
                });


                //倒计时
                var clock = '';
                var nums = 60;
                var btn;


                //点击获取验证码
                $scope.sendCode = function(thisBtn){
                    var iphoneVal =$("#telphone").val();
                    btn = thisBtn;
                    btn.disabled = true; //将按钮置为不可点击
                    btn.value = nums+' s后重发';
                    clock = setInterval(doLoop, 1000); //一秒执行一次

                    $("#code").html("验证码已经发送到手机"+$('input[name="name"]').val().substring(0,3)+"****"+$('input[name="name"]').val().substring(7,11));
                    //    验证码倒计时
                    function doLoop()
                    {
                        nums--;
                        if(nums > 0){
                            btn.value = nums+' s后重发';
                        }else{
                            clearInterval(clock); //清除js定时器
                            btn.disabled = false;
                            btn.value = '获取验证码';
                            nums = 60; //重置时间
                        }
                    }

                    //2.获取验证码
                    $.ajax({
                        url:IP+"/index.php/api/user/crode",
                        type:'POST',
                        data:{
                            phone:iphoneVal
                        },
                        success: function(data){
                            console.log(data);

                            if(data.code == 2001){
                                $('input[name="name"]').val("")
                                alert("短信发送失败");
                                clearInterval(clock); //清除js定时器
                                btn.disabled = false;
                                btn.value = '获取验证码';
                                nums = 60; //重置时间
                            }else if(data.code == 2002){
                                $('input[name="name"]').val("")
                                alert("手机号码不能为空");
                                clearInterval(clock); //清除js定时器
                                btn.disabled = false;
                                btn.value = '获取验证码';
                                nums = 60; //重置时间
                            }else if(data.code == 2003){
                                $('input[name="name"]').val("")
                                alert("手机号码格式错误");
                                clearInterval(clock); //清除js定时器
                                btn.disabled = false;
                                btn.value = '获取验证码';
                                nums = 60; //重置时间
                            }

                        }
                    });

                };


                //获取url后面的参数
                function GetQueryString(name) {
                    var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)", "i");
                    var r = window.location.search.substr(1).match(reg);  //获取url中"?"符后的字符串并正则匹配
                    var context = "";
                    if (r != null)
                        context = r[2];
                    reg = null;
                    r = null;
                    return context == null || context == "" || context == "undefined" ? "" : context;
                }

                // 3.点击立即申请
                $scope.sq = function(){
                    var iphoneVal = $('input[name="name"]').val();
                    console.log(iphoneVal);

                    var yzmVal = $('input[name="yzm"]').val();
                    console.log(yzmVal);


                    $.ajax({
                        type: 'POST',
                        url: IP+"/index.php/api/user/check_login",
                        data:{
                            phone:iphoneVal,
                            code:yzmVal,
                        },
                        // headers: {
                        //     "Access-Control-Allow-Origin":"http://example.edu",
                        //     "Access-Control-Allow-Headers":"X-Requested-With"
                        // },
//                        4、如果成功跳转页面
                        success: function(data) {
                            console.log(data);
                            $scope.code = data.code;
                            console.log($scope.code);
                            if(data.code == 2005){


                                //登录成功跳转
                                sessionStorage.setItem("id", id);
                                $scope.id = sessionStorage.getItem("id")
                                console.log($scope.id);
                                $http({
                                    method:'post',
                                    url:IP+'/index.php/api/user/loan_url',
                                    data:{id:$scope.id},
                                    headers:{'Content-Type': 'application/x-www-form-urlencoded'},
                                    transformRequest: function(obj) {
                                        var str = [];
                                        for(var p in obj){
                                            str.push(encodeURIComponent(p) + "=" + encodeURIComponent(obj[p]));
                                        }
                                        return str.join("&");
                                    }
                                }).then(function(data) {
                                    // console.log(data.code);
                                    $scope.bankurl =data.data.url;
                                    console.log($scope.bankurl);
                                    window.location.href = $scope.bankurl

                                })
                            }

                        }
                    });
                }


            }
        };

    }]);


})(angular)