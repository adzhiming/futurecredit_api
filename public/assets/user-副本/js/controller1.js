

(function (angular) {

        "use strict";
        // var IP= " http://192.168.1.134";
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

     //免年费年卡
     app.controller('freeController',['$scope','$http',function ($scope,$http) {
      $http({
          method:'GET',
          url:api_url+'user/index'
      }).then(function (data) {
          //银行中心
          $scope.bank_list = data.data.data.bank_list;

          //主题精选
          $scope.index_theme = data.data.data.index_theme;
      //    热门精选
          $scope.bank_card = data.data.data.bank_card;
          //弹出登录框或者直接登录
          $scope.cardUrl = function (id) {

              $scope.id = id;

              $http({
                  method:'GET',
                  url:api_url+'user/check_all_login'
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
                          url:api_url+'user/bank_url',
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
                      url:api_url+"user/crode",
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
                      url: api_url+"user/check_login",
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
                                  url:api_url+'user/loan_url',
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
                                  console.log(data.code);
                                  $scope.bankurl =data.data.url;
                                  console.log($scope.bankurl);
                                  window.location.href = $scope.bankurl

                              })
                          }

                      }
                  });
              }


          }

      })

  }]);
//    白金信用卡
    app.controller("platinumController",['$scope','$http',function ($scope,$http) {

        $http({
            method:'post',
            url:api_url+'user/card_list',
            data:{check:2},
            headers:{'Content-Type': 'application/x-www-form-urlencoded'},
            transformRequest: function(obj) {
                var str = [];
                for(var p in obj){
                    str.push(encodeURIComponent(p) + "=" + encodeURIComponent(obj[p]));
                }
                return str.join("&");
            }
        }).then(function(data){

            $scope.card_list =data.data.data;
        });

    }])



//    银行中心
    app.controller('bankCenterController',['$scope','$stateParams','$http',function ($scope,$stateParams,$http) {
       $scope.id =  $stateParams.bankid;

        $http({
            method:'post',
            url:api_url+'user/bank_to_card',
            data:{id:$scope.id},
            headers:{'Content-Type': 'application/x-www-form-urlencoded'},
            transformRequest: function(obj) {
                var str = [];
                for(var p in obj){
                    str.push(encodeURIComponent(p) + "=" + encodeURIComponent(obj[p]));
                }
                return str.join("&");
            }
        }).then(function (data) {
            $scope.bank = data.data.data.bank;
            $scope.bankcenter = data.data.data.bank_card;
        })
        //弹出登录框或者登陆
        $scope.cardUrl = function (id) {

            $scope.id = id;

            $http({
                method:'GET',
                url:api_url+'user/check_all_login'
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
                        url:api_url+'user/bank_url',
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
                    url:api_url+"user/crode",
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
                    url: api_url+"user/check_login",
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
                                url:api_url+'user/loan_url',
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

    }])



//    贷款的极速到账和低息贷款
    app.controller('loanController',["$scope","$http",function ($scope,$http) {

    //    极速到账
        $http({
            method:'post',
            url:api_url+'user/loan_list',
            data:{check:1},
            headers:{'Content-Type': 'application/x-www-form-urlencoded'},
            transformRequest: function(obj) {
                var str = [];
                for(var p in obj){
                    str.push(encodeURIComponent(p) + "=" + encodeURIComponent(obj[p]));
                }
                return str.join("&");
            }
        }).then(function(data){

            $scope.daoz =data.data.data
        });

        // 低息贷款请求数据
        $http({
            method:'post',
            url:api_url+'user/loan_list',
            data:{check:2},
            headers:{'Content-Type': 'application/x-www-form-urlencoded'},
            transformRequest: function(obj) {
                var str = [];
                for(var p in obj){
                    str.push(encodeURIComponent(p) + "=" + encodeURIComponent(obj[p]));
                }
                return str.join("&");
            }
        }).then(function(data){

            $scope.dix =data
        });

    }])


//    贷款登录
    app.controller('loanLoginController',['$scope','$stateParams','$http',function ($scope,$stateParams,$http) {

        $scope.id = $stateParams.loanid
        //    请求数据
        $http({
            method:'post',
            url:api_url+'user/loan_product_url',
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
            $scope.loanLogin = data.data.data;
        })

        //    点击提交申请
        $scope.cardUrl = function (id) {

            $scope.id = id;

            $http({
                method:'GET',
                url:api_url+'user/check_all_login'
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
                        url:api_url+'user/loan_url',
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
                    url:api_url+"user/crode",
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
                    url: api_url+"user/check_login",
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
                                url:api_url+'user/loan_url',
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



    }]);

//     我的贷款
    app.controller('myLoanController',['$scope','$http',function ($scope,$http) {


        //    贷款请求数据
        $http({
            method:'GET',
            url:api_url+'user/loan_log'
        }).then(function(data) {
            $scope.loan_log = data.data.data
        })


        //    信用卡请求数据
        $http({
            method:'GET',
            url:api_url+'user/my_card'
        }).then(function(data) {
            $scope.my_card = data.data.data
        })

        //    申请请求数据
        $http({
            method:'GET',
            url:api_url+'user/applying_list'
        }).then(function(data) {
            $scope.applying_list = data.data.data
        })

        //    否决请求数据
        $http({
            method:'GET',
            url:api_url+'user/reject_list'
        }).then(function(data) {
            $scope.reject_list = data.data.data
        })

    }]);


//    我的个人中心
    app.controller('myController',['$scope','$http',function ($scope,$http) {


        $scope.name = "登 录"
        //    请求数据
        $http({
            method:'GET',
            url:api_url+'user/user_list'
        }).then(function(data) {
            alert(0)
            console.log(data);
            //银行中心
            $scope.code = data.data;
            $scope.user_list = data;
            console.log($scope.user_list);

        })

        // //    退出账号

        // $scope.pop = function () {
        //     $('.popup').css({"display":"block"})
        // }
        // $scope.no = function () {
        //     $('.popup').css({"display":"none"})
        // }
        // //    退出账号
        // $scope.yes = function () {
        //     $http({
        //         method:'GET',
        //         url:api_url+'user/out_login'
        //     }).then(function(data) {
        //         if(data.code == 1){
        //             window.location.href=web_url+'login/userLogin';
        //         }
        //     })
        // }

        // 登录
        $scope.cardUrl = function () {
            $http({
                method:'GET',
                url:api_url+'user/check_all_login'
            }).then(function (data) {
                //没在登录状态
                if(data.data.code == -5){

                    $('.all').css({"display":"block"})
                }else {
                    $('.loginss').html("iphoneVal")
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
                    url:api_url+"user/crode",
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
                    url:api_url+'user/check_login',
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
                        if(data.code == 2005){
                            //登录成功跳转
                            $(".all").css({
                                "display":"none"
                            })
                            window.location.reload();
                        }

                    }

                });
            }


        }
    }]);




})(angular)