var indexapp = angular.module('indexapp',['ui.router','angular-loading-bar']);
var user = store.get('userInfo');
var topassword = store.get('topassword');

if(!user){ jump(); }

//首页
indexapp.controller('subIndex',['$scope','$state','$rootScope','$http',function($scope,$state,$rootScope,$http){
    $scope.lev = user.lev; 
    $http.post( rout+'getAgentInfoById',{id:user.id,token:topassword,tokenaid:user.id}).then(function(data){
        var resData = data.data;
        //console.log(resData)
        if(resData.code == 1){
            $scope.subIndexInfo = resData.data;
            store.set('userOtherInfo',resData.data);
        }
        else if(resData.code == -7){
        	zdyAlert.init(resData.msg,function(){
        		store.clearAll();
                window.location.href="login";
            })   
        }
        else{
            zdyAlert.init(resData.msg,function(){
                //console.log(resData.msg)
            })            
        }
    },function(error){
        //console.log(error)
    });
    $scope.loginout = function(){
        $http.post( rout+'logout',{id:user.id,token:topassword,tokenaid:user.id}).then(function(data){
            var resData = data.data;
            //console.log(resData)
            if(resData.code == 1){                
                zdyAlert.init(resData.msg,function(){
                    store.clearAll();
                    window.location.href="login";
                })     
            }else{
                zdyAlert.init(resData.msg,function(){
                })                       
            }
        },function(error){
            //console.log(error)
        }); 
    }

    $scope.withdraw = function(types,lsrc){
        if(types == 1){
            $state.go("withdraw");
        }else if(types == 0){
            zdycomfig.init('是否授权',function(){
                window.location.href=lsrc;
            })
        }
    }
}])
//分享链接
indexapp.controller('sharePage',['$scope','$http',function($scope,$http){
    $scope.did = user.id;
    $http.post( rout+'getAgentShareUrl',{id:user.id,token:topassword,tokenaid:user.id}).then(function(data){
        var resData = data.data;
        //console.log(resData)
        if(resData.code == 1){
            $scope.card = resData.data;
            $scope.share_card_url = resData.data.share_card_url;
            $scope.share_loan_url = resData.data.share_loan_url;
            $scope.share_juhe_url = resData.data.share_juhe_url;
            var cardurl = new QRCode(document.getElementById("cardurl"),$scope.share_card_url);
            var loanurl = new QRCode(document.getElementById("loanurl"),$scope.share_loan_url);
            var zhgxurl = new QRCode(document.getElementById("zhgxurl"),$scope.share_juhe_url);
        }else{
            zdyAlert.init(resData.msg,function(){
                //console.log(resData.msg)
            }) 
        }
    },function(error){
        //console.log(error)
    }); 
    $scope.close = false;
    $scope.cen = false;
    $scope.xybox = false;
    $scope.dkbox = false;
    $scope.zhbox = false;

    $scope.shower = function(types){
        $scope.close = true;
        $scope.cen = true;
        if(types == 'xy'){
            $scope.xybox = true;
            $('#xiyongka,#xiyongkanew').show()
            $scope.createQRcodeXyk();
        }else if(types == 'dk'){
            $scope.dkbox = true;
            $('#xindai,#xindainew').show()
            $scope.createQRcodeXd();
        }else if(types == 'zh'){
            $scope.zhbox = true;
            $('#zhgx,#zhgxnew').show()
            $scope.createQRcodeJuhe();
        }

    }
    $scope.createQRcodeXyk = function() {
        html2canvas(document.getElementById("xiyongka"), {
            allowTaint: true,
            taintTest: false,
            onrendered: function(canvas) {
                canvas.id = "xiyongka";
                //生成base64图片数据
                var dataUrl = canvas.toDataURL();
                var newImg = document.createElement("img");
                newImg.src =  dataUrl;
                $('#xiyongkanew').html(newImg);
            }
        });
    }
    $scope.createQRcodeXd = function() {
        html2canvas(document.getElementById("xindai"), {
            allowTaint: true,
            taintTest: false,
            onrendered: function(canvas) {
                canvas.id = "xindai";
                //生成base64图片数据
                var dataUrl = canvas.toDataURL();
                var newImg = document.createElement("img");
                newImg.src =  dataUrl;
                $('#xindainew').html(newImg);
            }
        });
    }
    $scope.createQRcodeJuhe = function() {
        html2canvas(document.getElementById("zhgx"), {
            allowTaint: true,
            taintTest: false,
            onrendered: function(canvas) {
                canvas.id = "zhgx";
                //生成base64图片数据
                var dataUrl = canvas.toDataURL();
                var newImg = document.createElement("img");
                newImg.src =  dataUrl;
                $('#zhgxnew').html(newImg);
            }
        });
    }
    $scope.closeFun = function(){
        $scope.close = false;
        $scope.cen = false;
        $scope.xybox = false;
        $scope.dkbox = false;
        $scope.zhbox = false;   
        $('#xiyongka,#xindai,#zhgx,#xiyongkanew,#xindainew,#zhgxnew').hide();     
    }
    $scope.tiaozhuan = function(loSrc){
        console.log(loSrc)
        if(loSrc == 'xy'){
            window.location.href = $scope.share_card_url;
        }else if(loSrc == 'xd'){
            window.location.href = $scope.share_loan_url;
        }else if(loSrc == 'zh'){
            window.location.href = $scope.share_juhe_url;
        }
    }
}])
//提现
indexapp.controller('withdraw',['$scope','$interval','$http',function($scope,$interval,$http){
    $scope.tip =  true;
    $scope.tips = true;
    $scope.otherUser = store.get('userOtherInfo');
    $scope.getMone = function(){
        $http.post( rout+'getAgentInfoById',{id:user.id,token:topassword,tokenaid:user.id}).then(function(data){
            var resData = data.data;
            //console.log(resData)
            if(resData.code == 1){
                $scope.subIndexInfo = resData.data;
            }else{
                zdyAlert.init(resData.msg,function(){
                    //console.log(resData.msg)
                })
            }
        },function(error){
            //console.log(error)
        }); 
    }
    $scope.getMone();

    $scope.timeS = 60;
    var timer = true;
    $scope.getyzm = function(iphone,type_id){

        if($scope.money == '' || isNaN(Number($scope.money))){
            $scope.tips = false;
            $scope.tipsText = '金额格式错误'
        }else if(Number($scope.money) <1 || Number($scope.money) >= 2000){
            $scope.tips = false;
            $scope.tipsText = '金额请在1-2000元之间'
        }else{
            if(timer){
                timer = false;
                //console.log(timer)
                $http.post( routUser+'crode',{phone:iphone,type_id:type_id,token:topassword,tokenaid:user.id}).then(function(data){
                    var resData = data.data;
                    if(resData.code == 1){
                        $scope.tip = false;
                        var timerinter = $interval(function(){
                            $scope.timeS -- ;
                            if($scope.timeS == 0){
                                $interval.cancel(timerinter); 
                                $scope.tip = true;
                                $scope.timeS = 60;
                                timer = true;
                            }
                        },1000)
                    }else{
                        zdyAlert.init(resData.msg,function(){
                            //console.log(resData.msg)
                        })
                    }
                },function(error){
                    //console.log(error)
                });
            }            
        }
    }
    $scope.money = '';
    $scope.code = '';
    $scope.tixian = function(){
        if($scope.money == ''){
            $scope.tips = false;
            $scope.tipsText = '请输入金额';
            return
        }else if($scope.code == ''){
            $scope.tipsText = '请输入验证码';
            $scope.tips = false;
            return
        }else{
            $scope.tips = true;
        }
        $http.post( rout+'withdraw',{
            id:user.id,
            money:$scope.money,
            code:$scope.code,
            token:topassword,
            tokenaid:user.id
        }).then(function(data){
            var resData = data.data;
            //console.log(resData)
            if(resData.code == 1){
                zdyAlert.init('提现成功',function(){
                    $scope.getMone();
                    $scope.money = '';
                    $scope.code = '';
                })  
            }else{
                zdyAlert.init(resData.msg,function(){
                    //console.log(resData.msg)
                })
            }
        },function(error){
            //console.log(error)
        }); 
    }
}])
//报表
indexapp.controller('report',['$scope','$http',function($scope,$http){
    $scope.tip = {
        text:'',
        isHide:true
    };
    $http.post( rout+'agentReport',{id:user.id,page:$scope.page,stime:$scope.stime,etime:$scope.etime,token:topassword,tokenaid:user.id}).then(function(data){
        var resData = data.data;
        $scope.conf = resData;
        if(resData.code == 1){
            if(resData.data.data.length <=0){
                $scope.tip = {
                    text:'暂时没有数据',
                    isHide:false
                };
            }else{
                $scope.pageset = {
                    currentPage:resData.data.page,
                    count:resData.data.page_count,
                    pageList:[],
                    selectArr:[],
                    onChange:function(){
                        $http.post( rout+'agentReport',{id:user.id,page:$scope.pageset.currentPage,stime:$scope.stime,etime:$scope.etime,token:topassword,tokenaid:user.id}).then(function(data){
                            var resData = data.data;
                            if(resData.code == 1){
                                $scope.baobiaolist = resData.data.data;
                            }else{
                                zdyAlert.init(resData.msg,function(){})
                            }
                        })
                    }
                }
                $scope.tip = {
                    text:'',
                    isHide:true
                };
                $scope.baobiaolist = resData.data.data;
            }
        }else{
            zdyAlert.init(resData.msg,function(){})
        }
    },function(error){
        //console.log(error)
    });
    $scope.search  = function(){
        if(!$scope.stime){
            zdyAlert.init('请选择开始时间',function(){})
            return
        }else if(!$scope.etime){
            zdyAlert.init('请选择结束时间',function(){})
            return
        }else if(Date.parse(new Date($scope.stime)) > Date.parse(new Date($scope.etime))){
            zdyAlert.init('开始时间不能大于结束时间',function(){})
            return
        }

        $http.post( rout+'agentReport',{id:user.id,page:$scope.page,stime:$scope.stime,etime:$scope.etime,token:topassword,tokenaid:user.id}).then(function(data){
            var resData = data.data;
            $scope.conf = resData;
            if(resData.code == 1){
                if(resData.data.data.length <=0){
                    $scope.tip = {
                        text:'暂时没有数据',
                        isHide:false
                    };
                    $scope.baobiaolist = [];
                    $scope.pageset = {
                        currentPage:0,
                        count:0,
                        pageList:[],
                        selectArr:[],
                        onChange:function(){}
                    }
                }else{
                    $scope.pageset = {
                        currentPage:resData.data.page,
                        count:resData.data.page_count,
                        pageList:[],
                        selectArr:[],
                        onChange:function(){
                            $http.post( rout+'agentReport',{id:user.id,page:$scope.pageset.currentPage,stime:$scope.stime,etime:$scope.etime,token:topassword,tokenaid:user.id}).then(function(data){
                                var resData = data.data;
                                if(resData.code == 1){
                                    $scope.baobiaolist = resData.data.data;
                                }else{
                                    zdyAlert.init(resData.msg,function(){})
                                }
                            })
                        }
                    }
                    $scope.tip = {
                        text:'',
                        isHide:true
                    };
                    $scope.baobiaolist = resData.data.data;
                }
            }else{
                zdyAlert.init(resData.msg,function(){})
            }
        },function(error){
            //console.log(error)
        });
    }
}])
//我的用户
indexapp.controller('myuser',['$scope','$http','$location',function($scope,$http,$location){
    var href = $location.absUrl();
    var index = href.indexOf("#/");
    var str = href.substring(index,href.length);
    var navName;
    if(str.indexOf('yonghu') != -1){
        navName = "yonghu";
    }else if(str.indexOf('access') != -1){
        navName = "access";
    }else if(str.indexOf('crdApplyRecord') != -1){
        navName = "crdApplyRecord";
    }else if(str.indexOf('loanApplyRecord') != -1){
        navName = "loanApplyRecord";
    }
    $scope.tap = function(eVale){
        $scope.navName = eVale;
        // //console.log($scope.navName)
    }
    $scope.navName = navName;
    $scope.page = 1;
    $scope.user = store.get('userInfo');
}])
//我的用户 - 用户
indexapp.controller('user',['$scope','$http',function($scope,$http){
    $scope.tip = {
        text:'',
        isHide:true
    };
    $http.post( rout+'myUserList',{id:user.id,page:$scope.page,token:topassword,tokenaid:user.id}).then(function(data){
        var resData = data.data;
        $scope.conf = resData;
        if(resData.code == 1){
            if(resData.data.data.length <=0){
                $scope.tip = {
                    text:'暂时没有数据',
                    isHide:false
                };
            }else{
                $scope.pageset = {
                    currentPage:resData.data.page,
                    count:resData.data.page_count,
                    pageList:[],
                    selectArr:[],
                    onChange:function(){
                        $http.post( rout+'myUserList',{id:user.id,page:$scope.pageset.currentPage,token:topassword,tokenaid:user.id}).then(function(data){
                            var resData = data.data;
                            if(resData.code == 1){
                                $scope.userList = resData.data.data;
                            }else{
                                zdyAlert.init(resData.msg,function(){})
                            }
                        })
                    }
                }
                $scope.tip = {
                    text:'',
                    isHide:true
                };
                $scope.userList = resData.data.data;
            }
        }else{
            zdyAlert.init(resData.msg,function(){})
        }
    },function(error){
        //console.log(error)
    });

    $scope.search = function(){
        $http.post( rout+'myUserList',{id:user.id,page:$scope.page,keyword:$scope.keyup,token:topassword,tokenaid:user.id}).then(function(data){
            var resData = data.data;
            console.log(resData)
            $scope.conf = resData;
            if(resData.code == 1){
                if(resData.data.data.length <=0){
                    $scope.tip = {
                        text:'没查到该用户',
                        isHide:false
                    };
                    $scope.pageset = {
                       currentPage:0,
                       count:0,
                       pageList:[],
                       selectArr:[],
                       onChange:function(){}
                    }
                    $scope.userList = [];
                    // zdyAlert.init('没有查到数据',function(){})
                }else{
                    $scope.pageset = {
                        currentPage:resData.data.page,
                        count:resData.data.page_count,
                        pageList:[],
                        selectArr:[],
                        onChange:function(){
                            $http.post( rout+'myUserList',{id:user.id,page:$scope.pageset.currentPage,keyword:$scope.keyup,token:topassword,tokenaid:user.id}).then(function(data){
                                var resData = data.data;
                                if(resData.code == 1){
                                    $scope.userList = resData.data.data;
                                }else{
                                    zdyAlert.init(resData.msg,function(){})
                                }
                            })
                        }
                    }
                    $scope.userList = resData.data.data;
                }
            }else{
                zdyAlert.init(resData.msg,function(){})
            }
        },function(error){
            //console.log(error)
        });
    }
}])
//我的用户 - 访问
indexapp.controller('access',['$scope','$http',function($scope,$http){
    $scope.tip = {
        text:'',
        isHide:true
    };
    $http.post( rout+'myUserAccessLog',{id:user.id,page:$scope.page,token:topassword,tokenaid:user.id}).then(function(data){
        var resData = data.data;
        console.log(resData)
        if(resData.code == 1){
            if(resData.data.data.length <= 0){
                $scope.tip = {
                    text:'暂时没有数据',
                    isHide:false
                };
                $scope.pageset = {
                    currentPage:'',
                    count:'',
                    pageList:[],
                    selectArr:[],
                    onChange:function(){}
                }
            }else{
                $scope.pageset = {
                    currentPage:resData.data.page,
                    count:resData.data.page_count,
                    pageList:[],
                    selectArr:[],
                    onChange:function(){
                        $http.post( rout+'myUserAccessLog',{id:user.id,page:$scope.pageset.currentPage,token:topassword,tokenaid:user.id}).then(function(data){
                            var resData = data.data;
                            //console.log(resData)
                            if(resData.code == 1){
                                $scope.recordList = resData.data.data;
                            }else{
                                zdyAlert.init(resData.msg,function(){})
                            }
                        })
                    }
                }
                $scope.tip = {
                    text:'',
                    isHide:true
                };
                $scope.recordList = resData.data.data;
            }
        }else{
            zdyAlert.init(resData.msg,function(){
                //console.log(resData.msg)
            })
        }
    },function(error){
        //console.log(error)
    });
    $scope.search = function(){
        if(!$scope.stime){
            zdyAlert.init('请选择开始时间',function(){}); return
        }else if(!$scope.etime){
            zdyAlert.init('请选择结束时间',function(){}); return
        }else if(Date.parse(new Date($scope.stime)) > Date.parse(new Date($scope.etime))){
            zdyAlert.init('开始时间不能大于结束时间',function(){})
            return
        }
        $http.post( rout+'myUserAccessLog',{id:$scope.user.id,page:1,stime:$scope.stime,etime:$scope.etime,token:topassword,tokenaid:user.id}).then(function(data){
            var resData = data.data;
            //console.log(resData)
            if(resData.code == 1){
                if(resData.data.data.length <= 0){
                    $scope.recordList = [];
                    $scope.tip = {
                        text:'暂时没有数据',
                        isHide:false
                    };
                    $scope.pageset = {
                        currentPage:'',
                        count:'',
                        pageList:[],
                        selectArr:[],
                        onChange:function(){}
                    }
                    $scope.recordList = [];
                }else{
                    $scope.pageset = {
                        currentPage:resData.data.page,
                        count:resData.data.page_count,
                        pageList:[],
                        selectArr:[],
                        onChange:function(){
                            $http.post( rout+'myUserAccessLog',{id:user.id,page:$scope.pageset.currentPage,stime:$scope.stime,etime:$scope.etime,token:topassword,tokenaid:user.id}).then(function(data){
                                var resData = data.data;
                                //console.log(resData)
                                if(resData.code == 1){
                                    $scope.recordList = resData.data.data;
                                }else{
                                    zdyAlert.init(resData.msg,function(){})
                                }
                            })
                        }
                    }
                    $scope.tip = {
                        text:'',
                        isHide:true
                    };
                    $scope.recordList = resData.data.data;
                }
            }else{
                zdyAlert.init(resData.msg,function(){
                    //console.log(resData.msg)
                })
            }            
        })
    }
}])
//我的用户 - 申卡记录
indexapp.controller('crdApplyRecord',['$scope','$http',function($scope,$http){
    $scope.tip = {
        text:'',
        isHide:true
    };
    $http.post( rout+'myUserApplyCardLog',{id:user.id,page:$scope.page,token:topassword,tokenaid:user.id}).then(function(data){
        var resData = data.data;
        //console.log(resData)
        if(resData.code == 1){
            if(resData.data.data.length <= 0){
                $scope.tip = {
                    text:'暂时没有数据',
                    isHide:false
                };
                $scope.pageset = {
                    currentPage:'',
                    count:'',
                    pageList:[],
                    selectArr:[],
                    onChange:function(){}
                }
            }else{
                $scope.pageset = {
                    currentPage:resData.data.page,
                    count:resData.data.page_count,
                    pageList:[],
                    selectArr:[],
                    onChange:function(){
                        $http.post( rout+'myUserApplyCardLog',{id:user.id,page:$scope.pageset.currentPage,token:topassword,tokenaid:user.id}).then(function(data){
                            var resData = data.data;
                            //console.log(resData)
                            if(resData.code == 1){
                                $scope.recordList = resData.data.data;
                            }else{
                                zdyAlert.init(resData.msg,function(){})
                            }
                        })
                    }
                }
                $scope.tip = {
                    text:'',
                    isHide:true
                };
                $scope.recordList = resData.data.data;
            }
        }else{
            zdyAlert.init(resData.msg,function(){
                //console.log(resData.msg)
            })
        }
    },function(error){
        //console.log(error)
    });

    $scope.search = function(){
        if(!$scope.stime){
            zdyAlert.init('请选择开始时间',function(){}); return
        }else if(!$scope.etime){
            zdyAlert.init('请选择结束时间',function(){}); return
        }else if(Date.parse(new Date($scope.stime)) > Date.parse(new Date($scope.etime))){
            zdyAlert.init('开始时间不能大于结束时间',function(){})
            return
        }
        $http.post( rout+'myUserApplyCardLog',{id:$scope.user.id,page:1,stime:$scope.stime,etime:$scope.etime,token:topassword,tokenaid:user.id}).then(function(data){
            var resData = data.data;
            if(resData.code == 1){
                if(resData.data.data.length <= 0){
                    $scope.recordList = [];
                    $scope.tip = {
                        text:'暂时没有数据',
                        isHide:false
                    };
                    $scope.pageset = {
                        currentPage:'',
                        count:'',
                        pageList:[],
                        selectArr:[],
                        onChange:function(){}
                    }   
                    $scope.recordList = [];                 
                }else{
                    $scope.pageset = {
                        currentPage:resData.data.page,
                        count:resData.data.page_count,
                        pageList:[],
                        selectArr:[],
                        onChange:function(){
                            $http.post( rout+'myUserApplyCardLog',{id:user.id,page:$scope.pageset.currentPage,stime:$scope.stime,etime:$scope.etime,token:topassword,tokenaid:user.id}).then(function(data){
                                var resData = data.data;
                                //console.log(resData)
                                if(resData.code == 1){
                                    $scope.recordList = resData.data.data;
                                }else{
                                    zdyAlert.init(resData.msg,function(){})
                                }
                            })
                        }
                    }
                    $scope.tip = {
                        text:'',
                        isHide:true
                    };
                    $scope.recordList = resData.data.data;
                }
            }else{
                zdyAlert.init(resData.msg,function(){
                    //console.log(resData.msg)
                })
            }
        })
    }
}])
//我的用户 - 申贷记录
indexapp.controller('loanApplyRecord',['$scope','$http',function($scope,$http){
    $scope.tip = {
        text:'',
        isHide:true
    };
    $http.post( rout+'myUserApplyLoanLog',{id:$scope.user.id,page:1,token:topassword,tokenaid:user.id}).then(function(data){
        var resData = data.data;
        //console.log(resData)
        if(resData.code == 1){
            if(resData.data.data.length <= 0){
                $scope.recordList = [];
                $scope.tip = {
                    text:'暂时没有数据',
                    isHide:false
                };
                $scope.pageset = {
                    currentPage:'',
                    count:'',
                    pageList:[],
                    selectArr:[],
                    onChange:function(){}
                }                
            }else{
                $scope.pageset = {
                    currentPage:resData.data.page,
                    count:resData.data.page_count,
                    pageList:[],
                    selectArr:[],
                    onChange:function(){
                        $http.post( rout+'myUserApplyLoanLog',{id:user.id,page:$scope.pageset.currentPage,token:topassword,tokenaid:user.id}).then(function(data){
                            var resData = data.data;
                            //console.log(resData)
                            if(resData.code == 1){
                                $scope.recordList = resData.data.data;
                            }else{
                                zdyAlert.init(resData.msg,function(){})
                            }
                        })
                    }
                }
                $scope.tip = {
                    text:'',
                    isHide:true
                };
                $scope.recordList = resData.data.data;
            }
        }else{
            zdyAlert.init(resData.msg,function(){
                //console.log(resData.msg)
            })
        }
    },function(error){
        //console.log(error)
    }); 

    $scope.search = function(){
        if(!$scope.stime){
            zdyAlert.init('请选择开始时间',function(){}); return
        }else if(!$scope.etime){
            zdyAlert.init('请选择结束时间',function(){}); return
        }else if(Date.parse(new Date($scope.stime)) > Date.parse(new Date($scope.etime))){
            zdyAlert.init('开始时间不能大于结束时间',function(){})
            return
        }
        $http.post( rout+'myUserApplyLoanLog',{id:$scope.user.id,page:1,stime:$scope.stime,etime:$scope.etime,token:topassword,tokenaid:user.id}).then(function(data){
            //console.log(data)
            var resData = data.data;
            if(resData.code == 1){
                if(resData.data.data.length <= 0){
                    $scope.tip = {
                        text:'暂时没有数据',
                        isHide:false
                    };
                    $scope.pageset = {
                        currentPage:'',
                        count:'',
                        pageList:[],
                        selectArr:[],
                        onChange:function(){}
                    }
                    $scope.recordList = [];
                }else{
                    $scope.pageset = {
                        currentPage:resData.data.page,
                        count:resData.data.page_count,
                        pageList:[],
                        selectArr:[],
                        onChange:function(){
                            $http.post( rout+'myUserApplyLoanLog',{id:user.id,page:$scope.pageset.currentPage,stime:$scope.stime,etime:$scope.etime,token:topassword,tokenaid:user.id}).then(function(data){
                                var resData = data.data;
                                //console.log(resData)
                                if(resData.code == 1){
                                    $scope.recordList = resData.data.data;
                                }else{
                                    zdyAlert.init(resData.msg,function(){})
                                }
                            })
                        }
                    }
                    $scope.tip = {
                        text:'',
                        isHide:true
                    };
                    $scope.recordList = resData.data.data;
                }
            }else{
                zdyAlert.init(resData.msg,function(){
                    //console.log(resData.msg)
                })
            }            
        })
    }
}])
//收款账户
indexapp.controller('collectionAccount',['$scope','$http',function($scope,$http){
    var otherUser = store.get('userOtherInfo');
    $scope.istip = true;
    $scope.tip = '';
    //console.log(otherUser)
    $scope.sk = {
        name:otherUser.name,
        id_card:otherUser.id_card,
        card_no:otherUser.card_no,
        card_name:otherUser.card_name,
        card_sub_name:otherUser.card_sub_name
    }
    $scope.submit = function(){
        var reg = /(^\d{15}$)|(^\d{18}$)|(^\d{17}(\d|X|x)$)/; 
        if($scope.sk.name == ''){
            $scope.tip = '开户名不能为空';
            $scope.istip = false;
            return
        }else if($scope.sk.id_card == ''){
            $scope.tip = '身份证不能为空';
            $scope.istip = false;
            return
        }else if( !(reg.test($scope.sk.id_card)) ){
            $scope.tip = '身份证格式错误';
            $scope.istip = false;
            return
        }else if($scope.sk.card_no == ''){
            $scope.tip = '卡号不能为空';
            $scope.istip = false;
            return
        }else if($scope.sk.card_name == ''){
            $scope.tip = '银行卡不能为空';
            $scope.istip = false;
            return
        }else if($scope.sk.card_sub_name == ''){
            $scope.tip = '支行不能为空';
            $scope.istip = false;
            return
        }else{
            $scope.istip = true;
            $scope.tip = '';
        }

        var data = {
            id:user.id,
            name:$scope.sk.name,
            id_card:$scope.sk.id_card,
            card_no:$scope.sk.card_no,
            card_name:$scope.sk.card_name,
            card_sub_name:$scope.sk.card_sub_name
        }
        //console.log(data)
        $http.post( rout+'editUserCommit',data).then(function(data){
            var resData = data.data;
            //console.log(resData)
            if(resData.code == 1){
                zdyAlert.init(resData.msg,function(){
                    //console.log(resData.msg)
                })
            }else{
                zdyAlert.init(resData.msg,function(){
                    //console.log(resData.msg)
                })
            }
        },function(error){
            //console.log(error)
        }); 
    }
}])
//代理
indexapp.controller('daili',['$scope','$location',function($scope,$location){
    $scope.lev = user.lev;

    if($scope.lev == 3){
        $('.header-three-nav a').css('width','50%');
    }
    var href = $location.absUrl();
    var index = href.indexOf("#/");
    var str = href.substring(index,href.length);
    var navName;
    if(str.indexOf('myAgent') != -1){
        navName = "myAgent";
    }else if(str.indexOf('agentAdd') != -1){
        navName = "agentAdd";
    }else if(str.indexOf('quotedPrice') != -1){
        navName = "quotedPrice";
    }
    $scope.tap = function(eVale){
        $scope.navName = eVale;
    }
    $scope.navName = navName;
}])
//代理 - 我的代理
indexapp.controller('myAgent',['$scope','$state','$stateParams','$http',function($scope,$state,$stateParams,$http){
    $('.header-three-nav a').removeClass('active');
    $('.header-three-nav a').eq(0).addClass('active')
    $scope.tip = {
        text:'',
        isHide:true
    };
    $scope.lev = user.lev; 
    $scope.page = $stateParams.page || 1;
    $scope.dlList = [];
    $scope.myAgent = function(keyup){
        $http.post( rout+'myAgenList',{id:user.id,page:$scope.page,token:topassword,tokenaid:user.id,keyword:keyup}).then(function(data){
            var resData = data.data;
            console.log(resData)
            if(resData.code == 1){
                if(resData.data.data.length <=0){
                    $scope.tip = {
                        text:'暂时没有数据',
                        isHide:false
                    };
                    $scope.dlList = [];
                    $scope.pageset = {
                        currentPage:0,
                        count:0,
                        pageList:[],
                        selectArr:[],
                        onChange:function(){}
                    }
                }else{
                    $scope.pageset = {
                        currentPage:resData.data.current_page,
                        count:resData.data.page_count,
                        pageList:[],
                        selectArr:[],
                        onChange:function(){
                            $scope.dlList = [];
                            $http.post( rout+'myAgenList',{id:user.id,page:$scope.pageset.currentPage,token:topassword,tokenaid:user.id}).then(function(data){
                                var resData = data.data;
                                if(resData.code == 1){
                                    $scope.dlList = resData.data.data;
                                    $scope.page = resData.data.current_page
                                }else{
                                    zdyAlert.init(resData.msg,function(){})
                                }
                            })
                        }
                    }
                    $scope.tip = {
                        text:'',
                        isHide:true
                    };
                    $scope.dlList = resData.data.data;
                }
            }else{
                zdyAlert.init(resData.msg,function(){//console.log(resData.msg)
                })
            }
        },function(error){
            //console.log(error)
        });        
    }
    $scope.myAgent();

    $scope.gothree = function(did,types,name,lev){
        if(types == 1){
            $state.go("myAgentThree",{id:did,name:name,lev:lev,page:$scope.page});
        }
    }
    $scope.search = function(){
        console.log($scope.keyup)
        $scope.myAgent($scope.keyup);
    }
}])
//代理 - 我的代理 - 下级
indexapp.controller('myAgentThree',['$scope','$state','$stateParams','$http',function($scope,$state,$stateParams,$http){
    $('.header-three-nav a').removeClass('active');
    $('.header-three-nav a').eq(0).addClass('active');

    // $scope.pageTwo = $stateParams.page;
    
    if($stateParams.parentpage){
        $scope.pageTwo = $stateParams.parentpage;
    }else if($stateParams.page){
        $scope.pageTwo = $stateParams.page;
    }


    $scope.did = $stateParams.id;
    $scope.levTop = {
        name:$stateParams.name,
        lev:$stateParams.lev
    }
    $scope.tip = {
        text:'',
        isHide:true
    };
    $scope.lev = user.lev; 
    $scope.page = $stateParams.page || 1;
    $scope.dlList = [];
    $http.post( rout+'myAgenList',{id:$scope.did,page:$scope.page,token:topassword,tokenaid:user.id}).then(function(data){
        var resData = data.data;
        console.log(resData)
        if(resData.code == 1){
            if(resData.data.data.length <=0){
                $scope.tip = {
                    text:'暂时没有数据',
                    isHide:false
                };
                $scope.dlList = [];
                $scope.pageset = {
                    currentPage:0,
                    count:0,
                    pageList:[],
                    selectArr:[],
                    onChange:function(){}
                }
            }else{
                $scope.pageset = {
                    currentPage:resData.data.current_page,
                    count:resData.data.page_count,
                    pageList:[],
                    selectArr:[],
                    onChange:function(){
                        $scope.dlList = [];
                        $http.post( rout+'myAgenList',{id:$scope.did,page:$scope.pageset.currentPage,token:topassword,tokenaid:user.id}).then(function(data){
                            var resData = data.data;
                            if(resData.code == 1){
                                $scope.dlList = resData.data.data;
                                $scope.page = resData.data.current_page
                            }else{
                                zdyAlert.init(resData.msg,function(){})
                            }
                        })
                    }
                }
                $scope.tip = {
                    text:'',
                    isHide:true
                };
                $scope.dlList = resData.data.data;
            }
        }else{
            zdyAlert.init(resData.msg,function(){//console.log(resData.msg)
            })
        }
    },function(error){

    });
    $scope.gothree = function(did,types,name,lev){
        if(types == 1){
            $state.go("myAgentThreeTwo",{id:$stateParams.id,name:$stateParams.name,lev:$stateParams.lev,twoId:did,twoName:name,twoLev:lev,page:$scope.page,parentpage:$scope.pageTwo});
        }
    }
}])
//代理 - 我的代理 - 下级 - 三级
indexapp.controller('myAgentThreeTwo',['$scope','$stateParams','$http',function($scope,$stateParams,$http){
    $('.header-three-nav a').removeClass('active');
    $('.header-three-nav a').eq(0).addClass('active')
    $scope.did = $stateParams.id;
    $scope.twoDid = $stateParams.twoId;

    $scope.pageThree = $stateParams.page;
    $scope.parentpage = $stateParams.parentpage;

    $scope.levTop = {
        name:$stateParams.name,
        lev:$stateParams.lev
    }
    $scope.levTopTwo = {
        name:$stateParams.twoName,
        lev:$stateParams.twoLev
    }
    $scope.tip = {
        text:'',
        isHide:true
    };
    $scope.lev = user.lev; 
    $scope.page = 1;
    $scope.dlList = [];
    $http.post( rout+'myAgenList',{id:$scope.twoDid,page:$scope.page,token:topassword,tokenaid:user.id}).then(function(data){
        var resData = data.data;
        console.log(resData)
        if(resData.code == 1){
            if(resData.data.data.length <=0){
                $scope.tip = {
                    text:'暂时没有数据',
                    isHide:false
                };
                $scope.dlList = [];
                $scope.pageset = {
                    currentPage:0,
                    count:0,
                    pageList:[],
                    selectArr:[],
                    onChange:function(){}
                }
            }else{
                $scope.pageset = {
                    currentPage:resData.data.current_page,
                    count:resData.data.page_count,
                    pageList:[],
                    selectArr:[],
                    onChange:function(){
                        $scope.dlList = [];
                        $http.post( rout+'myAgenList',{id:$scope.twoDid,page:$scope.pageset.currentPage,token:topassword,tokenaid:user.id}).then(function(data){
                            var resData = data.data;
                            if(resData.code == 1){
                                $scope.dlList = resData.data.data;
                                $scope.page = resData.data.current_page
                            }else{
                                zdyAlert.init(resData.msg,function(){})
                            }
                        })
                    }
                }
                $scope.tip = {
                    text:'',
                    isHide:true
                };
                $scope.dlList = resData.data.data;
            }
        }else{
            zdyAlert.init(resData.msg,function(){//console.log(resData.msg)
            })
        }
    },function(error){

    });
}])
//代理 - 我的代理 - 修改
indexapp.controller('myAgent_edit',['$scope','$stateParams','$http',function($scope,$stateParams,$http){
    var did = $stateParams.did;
    $scope.page = $stateParams.page;
    $http.post( rout+'getAgentInfoById',{id:did,token:topassword,tokenaid:user.id}).then(function(data){
        var resData = data.data;
        if(resData.code == 1){
            $scope.dl = {
                name:resData.data.name,
                phone:resData.data.phone
            }
        }else if(resData.msg == '非法操作'){
            zdyAlert.init(resData.msg,function(){
                window.history.go(-2);
            })            
        }else{
            zdyAlert.init(resData.msg,function(){
                //console.log(resData.msg)
            })
        }
    },function(error){
        //console.log(error)
    }); 

    $scope.submit = function(){
        if($scope.dl.name == ''){
            $scope.tip = '请输入姓名';
            return 
        }else if( !$scope.dl.password || !$scope.dl.repassword ){
            $scope.tip = '密码不能为空';
            return
        }else if($scope.dl.password !== $scope.dl.repassword){
            $scope.tip = '密码不一致';
            return
        }else{
            $scope.tip = '';
        }
        $scope.dl.id =  did;
        $scope.dl.token = topassword;
        $scope.dl.tokenaid = user.id;

        $http.post( rout+'editUserCommit',$scope.dl).then(function(data){
            var resData = data.data;
            //console.log(resData)
            if(resData.code == 1){
                zdyAlert.init(resData.msg,function(){
                    window.history.back();
                })
            }else{
                zdyAlert.init(resData.msg,function(){
                    //console.log(resData.msg)
                })
            }
        },function(error){
            //console.log(error)
        });         
    }
}])
//代理 - 新增代理
indexapp.controller('agentAdd',['$scope','$state','$http',function($scope,$state,$http){
    $scope.tip = '';
    $scope.dl = {
        id:user.id,
        name:'',
        phone:'',
        password:'',
        repassword:''
    }    
    $scope.submit = function(){
        if($scope.dl.name == ''){
            $scope.tip = '请输入姓名';
            return 
        }else if($scope.dl.phone == ''){
            $scope.tip = '请输入手机号';
            return
        }else if( !(/^1[34578]\d{9}$/.test($scope.dl.phone)) ){
            $scope.tip = '手机号有误';
            return
        }else if($scope.dl.password !== $scope.dl.repassword){
            $scope.tip = '密码不一致';
            return
        }else{
            $scope.tip = '';
        }
        $scope.dl.token = topassword;
        $scope.dl.tokenaid = user.id;
        $http.post( rout+'addMyAgentCommit',$scope.dl).then(function(data){
            var resData = data.data;
            //console.log(resData)
            if(resData.code == 1){
                zdyAlert.init(resData.msg,function(){
                    $scope.dl = {
                        id:user.id,
                        name:'',
                        phone:'',
                        password:'',
                        repassword:''
                    }  

                    $state.go('daili.myAgent')
                })
                
            }else{
                zdyAlert.init(resData.msg,function(){
                    //console.log(resData.msg)
                })

            }
        },function(error){
            //console.log(error)
        }); 
    }
}])
//代理 - 报价单
indexapp.controller('quotedPrice',['$location','$stateParams','$scope','$http',function($location,$stateParams,$scope,$http){
    $scope.user = store.get('userOtherInfo');
    if($stateParams.nav == 'xy'){
        $('.xinyongka-nav').addClass('active');
        $('.xinyongka').show();
        $scope.page = $stateParams.page || 1;
        $scope.pages = 0
    }else if($stateParams.nav == 'dk'){
        $('.dakuan-nav').addClass('active');
        $('.dakuan').show();
        $scope.pages = $stateParams.page || 1;
        $scope.page = 0
    }
    $scope.navtap = function(data){
        $('.baojia-box').hide();
        $('.'+data+'-nav').addClass('active').siblings().removeClass('active');
        $('.'+data).show();
        // 后期开回去
        $('.more').removeClass('dh');
        $('.canpin-list-box').hide();
    }
    $('.close-bianji').bind('click',function(){
        $('.cen,.bianji-box').hide();
    })
    $scope.tip = {
        text:'',
        isHide:true
    };
    $scope.tips = {
        text:'',
        isHide:true
    };
    // 请求数据 信用和贷款数据
    $scope.initxy = function(){
        $http.post( rout+'quotationList',{id:user.id,page:$scope.page,type:1,token:topassword,tokenaid:user.id}).then(function(data){
            var resData = data.data;
            //console.log(resData)
            if(resData.code == 1){
                if(resData.data.given_user_pricelist.length <=0){
                    $scope.tip = {
                        text:'暂时没有数据',
                        isHide:false
                    };
                    $scope.xygiven = [];
                }else{
                    $scope.pageset = {
                        currentPage:resData.data.page,
                        count:resData.data.page_count,
                        pageList:[],
                        selectArr:[],
                        onChange:function(){
                            $http.post( rout+'quotationList',{id:user.id,page:$scope.pageset.currentPage,type:1,token:topassword,tokenaid:user.id}).then(function(data){
                                var resData = data.data;
                                //console.log(resData)
                                if(resData.code == 1){
                                    $scope.xygiven = resData.data.given_user_pricelist;
                                }else{
                                    zdyAlert.init(resData.msg,function(){})
                                }
                            })
                        }
                    }
                    $scope.tip = {
                        text:'',
                        isHide:true
                    };

                    $scope.xygiven = resData.data.given_user_pricelist;
                }
                $scope.xydefault = resData.data.default_pricelist;
            }else{
                zdyAlert.init(resData.msg,function(){
                    //console.log(resData.msg)
                })
            }
        },function(error){
            //console.log(error)
        });
    }

    $scope.initdk = function(){
        $http.post( rout+'quotationList',{id:user.id,page:$scope.pages,type:2,token:topassword,tokenaid:user.id}).then(function(data){
            var resData = data.data;
            //console.log(resData)
            if(resData.code == 1){
                if(resData.data.given_user_pricelist.length <=0){
                    $scope.tips = {
                        text:'暂时没有数据',
                        isHide:false
                    };
                    $scope.dkgiven = [];
                }else{
                    $scope.pagesets = {
                        currentPage:resData.data.page,
                        count:resData.data.page_count,
                        pageList:[],
                        selectArr:[],
                        onChange:function(){
                            $http.post( rout+'quotationList',{id:user.id,page:$scope.pagesets.currentPage,type:2,token:topassword,tokenaid:user.id}).then(function(data){
                                var resData = data.data;
                                if(resData.code == 1){
                                    $scope.dkgiven = resData.data.given_user_pricelist;
                                }else{
                                    zdyAlert.init(resData.msg,function(){})
                                }
                            })
                        }
                    }
                    $scope.tips = {
                        text:'',
                        isHide:true
                    };
                    console.log(resData.data.given_user_pricelist)
                    $scope.dkgiven = resData.data.given_user_pricelist;
                }
                $scope.dkdefault = resData.data.default_pricelist;
            }else{
                zdyAlert.init(resData.msg,function(){
                    //console.log(resData.msg)
                })
            }
        },function(error){
            //console.log(error)
        });
    }
    $scope.initxy();
    $scope.initdk();

    // 普通用户的编辑
    $scope.bianji = function(did,types,index){
        $('.cen,.bianji-box').show();
        if(types == 'xy'){
            $scope.thisInfo = $scope.xydefault[index];
        }else if(types == 'dk'){
            $scope.thisInfo = $scope.dkdefault[index];
        }
        $scope.init_lv0 = $scope.thisInfo.level_price;
        $scope.init_lv1 = $scope.thisInfo.level1_price;
        $scope.init_lv2 = $scope.thisInfo.level2_price;
        $scope.init_lv3 = $scope.thisInfo.level3_price;
    }
    $scope.bianjiXd = function(did,types,index,parentIndex){
        $('.cen,.bianji-box').show();
        
        $scope.thisInfo = $scope.dkdefault[parentIndex][index];
        console.log($scope.thisInfo)
        $scope.init_lv0 = $scope.thisInfo.level_price;
        $scope.init_lv1 = $scope.thisInfo.level1_price;
        $scope.init_lv2 = $scope.thisInfo.level2_price;
        $scope.init_lv3 = $scope.thisInfo.level3_price;
    }


    $scope.bianjisubmit = function(did){
        var lev  = Number($scope.thisInfo.level_price);
        var lev1 = Number($scope.thisInfo.level1_price);
        var lev2 = Number($scope.thisInfo.level2_price);
        var lev3 = Number($scope.thisInfo.level3_price);
        var userlev = user.lev;

        if(userlev == 0 && (lev3 >= lev2 || lev3 >= lev1 || lev3 >= lev || lev2 >= lev1 || lev2 >= lev || lev1 >= lev) ){
                zdyAlert.init('不能大于上级价格',function(){})
                $scope.thisInfo.level_price = $scope.init_lv0;
                $scope.thisInfo.level1_price = $scope.init_lv1;
                $scope.thisInfo.level2_price = $scope.init_lv2;
                $scope.thisInfo.level3_price = $scope.init_lv3;
                return;
        }
        if(userlev == 1 && (lev3 >= lev2 || lev3 >= lev1 || lev2 >= lev1) ){
                zdyAlert.init('不能大于上级价格',function(){})
                $scope.thisInfo.level_price = $scope.init_lv0;
                $scope.thisInfo.level1_price = $scope.init_lv1;
                $scope.thisInfo.level2_price = $scope.init_lv2;
                $scope.thisInfo.level3_price = $scope.init_lv3;
                return;
        }
        if(userlev == 2 && (lev3 >= lev2 ) ){
                zdyAlert.init('不能大于上级价格',function(){})
                $scope.thisInfo.level_price = $scope.init_lv0;
                $scope.thisInfo.level1_price = $scope.init_lv1;
                $scope.thisInfo.level2_price = $scope.init_lv2;
                $scope.thisInfo.level3_price = $scope.init_lv3;
                return;
        }

        $http.post( rout+'editSysPrice',{
            id:did,
            agent_id:user.id,
            level_price:lev,
            level1_price:$scope.thisInfo.level1_price,
            level2_price:$scope.thisInfo.level2_price,
            level3_price:$scope.thisInfo.level3_price,
            token:topassword,
            tokenaid:user.id
        }).then(function(data){
            var resData = data.data;
            //console.log(resData)
            if(resData.code == 1){
                zdyAlert.init(resData.msg,function(){
                    $('.cen,.bianji-box').hide();
                    $scope.initxy();
                    $scope.initdk();
                })
            }else{
                zdyAlert.init(resData.msg,function(){
                    //console.log(resData.msg)
                })
            }
        },function(error){
            //console.log(error)
        }); 
    }
    //特定用户删除 
    $scope.delete = function(did,types,cardId,typeNumber){
        zdycomfig.init('确认要删除吗？',function(){
            $('.alertcen-ff,.alertnei-new').remove();
            $http.post( rout+'delAgentPrice',{
                id:did,
                card_id:cardId,
                type:typeNumber,
                token:topassword,
                tokenaid:user.id
            }).then(function(data){
                var resData = data.data;
                //console.log(resData)
                if(resData.code == 1){
                    zdyAlert.init(resData.msg,function(){
                        if(types == 'xy'){
                            $scope.initxy();
                        }else if(types == 'dk'){
                            $scope.initdk();
                        }
                    })
                }else{
                    zdyAlert.init(resData.msg,function(){
                        //console.log(resData.msg)
                    })
                }
            },function(error){
                //console.log(error)
            }); 
        })
    }
    $scope.isShowGz = false;
    // 更多规则
    $scope.moreGz = function(text){
        $scope.morexq = text;
        $scope.isShowGz = true;
        // $scope.$apply();
        // $('.guice-box,.guice-cen').show();
    }
    $scope.closegz = function(){
        $scope.isShowGz = false;
        // $scope.$apply();
        // $('.guice-box,.guice-cen').hide();
    }
}])
//代理 - 报价单 - 编辑 - 信用卡
indexapp.controller('teding_edit',['$scope','$stateParams','$state','$http',function($scope,$stateParams,$state,$http){
    $scope.name = $stateParams.name;
    $scope.lev0_p = $stateParams.lv0_p;
    $scope.lev1_p = $stateParams.lv1_p;
    $scope.lev2_p = $stateParams.lv2_p;
    $scope.lev3_p = $stateParams.lv3_p;
    $scope.agentid = $stateParams.agentid;
    $scope.page = $stateParams.page
    $scope.userlev = user.lev;
    $scope.tip = '';
    $scope.activeUser = $stateParams.name;
    $scope.activeUserId = $stateParams.agentid;
    $scope.activeBank = {
        bank_name : $stateParams.bank_name
    }
    $scope.activeBankId = $stateParams.bank_id;
    // $scope.activeXykBank = $stateParams.cardname;
    // $scope.activeXykBankId = $stateParams.cardid;
    $scope.pricetype = $stateParams.pricetype;
    // 读取银行数据
    $scope.bankInit = function(keyword){
        $http.post( rout+'getBankLoanList',{
            // id:$stateParams.did,
            aid:user.id,
            keyword:keyword,
            type:1,
            token:topassword,
            tokenaid:user.id
        }).then(function(data){
            var resData = data.data;
            //console.log(resData)
            if(resData.code == 1){
                if(resData.data){
                    $scope.banklist = resData.data;
                    var arr = $scope.banklist;
                    var ar = [];
                    var s = [];
                    for(var b=0,i=1;b<(arr.length)/6;b++,i++){
                       s = [];
                       s = $scope.banklist.slice(b*6, i*6);
                       ar[b] = s
                    }
                    $scope.banklist = ar;
                }else{
                    zdyAlert.init('没有查到数据',function(){})
                }
            }else{
                zdyAlert.init(resData.msg,function(){})
            }
        },function(error){
            //console.log(error)
        });
    }
    // 银行卡搜索
    $scope.bankSearch = function(){
        $scope.bankInit($scope.bankKeup)
    }
    // 用户搜索
    $scope.userSearch = function(){
        $scope.userInit($scope.userKeup)
    }
    // 读取用户数据
    $scope.userInit = function(keyword){
        $http.post( rout+'mySelfAgenList',{
            id:user.id,
            keyword:keyword,
            token:topassword,
            tokenaid:user.id
        }).then(function(data){
            var resData = data.data;
            //console.log(resData)
            if(resData.code == 1){
                if(resData.data.data.length > 0){
                    $scope.userlist = resData.data.data;
                    var arr = $scope.userlist;
                    var ar = [];
                    var s = [];
                    for(var b=0,i=1;b<(arr.length)/6;b++,i++){
                       s = [];
                       s = $scope.userlist.slice(b*6, i*6);
                       ar[b] = s
                    }
                    $scope.userlist = ar;
                }else{
                    zdyAlert.init('没有查到数据',function(){})
                }

            }else{
                zdyAlert.init(resData.msg,function(){})
            }
        },function(error){
            //console.log(error)
        });        
    }
    $scope.bankInit('');
    $scope.userInit('');
    // 信用卡选择
    // $scope.bankXykSelect = function(did,index,parentIndex){
    //     $scope.activeXykBankParentIndex = parentIndex;
    //     $scope.activeXykBankId = did;
    //     $scope.activeXykBankIndex = index;
    // }
    // 银行选择
    $scope.bankSelect = function(did,index,parentIndex){
        $scope.activeBankParentIndex = parentIndex;
        $scope.activeBankId = did;
        $scope.activeBankIndex = index;
    }
    $scope.bankSure = function(){
        if($scope.activeBankParentIndex !== undefined || $scope.activeBankIndex !== undefined){
            $scope.activeBank.bank_name = $scope.banklist[$scope.activeBankParentIndex][$scope.activeBankIndex].name;
        }
        $('.cen,.teding_fixed_box').hide();
        $scope.lev0_p = '';
        $scope.lev1_p = '';
        $scope.lev2_p = '';
        $scope.lev3_p = '';
        $scope.pricetype = '';

        // 2017-09-05修改
        $http.post( rout+'getAgentPrice',{
            agent_id:user.id,
            type:1,
            bank_id:$scope.activeBankId,
            token:topassword,
            tokenaid:user.id
        }).then(function(data){
            var resData = data.data;
            //console.log(resData)
            if(resData.code == 1){
                $scope.lev0_p = resData.data.level_price;
                $scope.lev1_p = resData.data.level1_price;
                $scope.lev2_p = resData.data.level2_price;
                $scope.lev3_p = resData.data.level3_price;
                $scope.pricetype = resData.data.price_type
            }else{
                zdyAlert.init(resData.msg,function(){})
            }
        },function(error){
            //console.log(error)
        });
    }
    // 用户选择
    $scope.userSelect = function(did,index,parentIndex){
        $scope.activeUserkParentIndex = parentIndex;
        $scope.activeUserId = did;
        $scope.activeUserIndex = index;
    }
    $scope.userSure = function(){
        if($scope.activeUserkParentIndex !== undefined || $scope.activeUserIndex !== undefined){
            $scope.activeUser = $scope.userlist[$scope.activeUserkParentIndex][$scope.activeUserIndex].name;
        }
        $('.cen,.teding_fixed_box').hide();
    }
    $scope.xyInitSelect = function(){
        $http.post( rout+'getCreditList',{
            bank_id:$scope.activeBankId,
            keyword:'',
            token:topassword,
            tokenaid:user.id
        }).then(function(data){
            var resData = data.data;
            //console.log(resData)
            if(resData.code == 1){
                if(resData.data.length > 0){
                    $scope.bankXyklist = resData.data;
                    var arr = $scope.bankXyklist;
                    var ar = [];
                    var s = [];
                    for(var b=0,i=1;b<(arr.length)/6;b++,i++){
                       s = [];
                       s = $scope.bankXyklist.slice(b*6, i*6);
                       ar[b] = s
                    }
                    $scope.bankXyklist = ar;
                }else{
                    zdyAlert.init('没有查到数据',function(){})
                }
            }else{
                zdyAlert.init(resData.msg,function(){})
            }
        },function(error){
            //console.log(error)
        });   
    }
    $scope.xyInitSelect();

    //修改用户
    $scope.formSubmit = function(){
        //console.log($scope.activeXykBankId)
        var data = {
            level1_price:$scope.lev1_p,
            level2_price:$scope.lev2_p,
            level3_price:$scope.lev3_p,
            bank_id:$scope.activeBankId,
            id:$stateParams.did,
            new_agent_id:$scope.activeUserId,
            // card_id:$scope.activeXykBankId,
            agent_id:$scope.agentid,
            parent_agent_id:user.id,
            token:topassword,
            tokenaid:user.id,
            type:1
        }
        var lev0 = Number($scope.lev0_p);
        var lev1 = Number($scope.lev1_p);
        var lev2 = Number($scope.lev2_p);
        var lev3 = Number($scope.lev3_p);
        var userlev = user.lev;

        if( userlev == 0 && (lev3 >= lev2 || lev3 >= lev1 || lev3 >= lev0 || lev2 >= lev1 || lev2 >= lev0 || lev1 >= lev0) ){
            $scope.tip = '价格不能高于上级价格';
        }else if(userlev == 1 && (lev3 >= lev2 || lev3 >= lev1 || lev2 >= lev1) ){
            $scope.tip = '价格不能高于上级价格';
        }else if(userlev == 2 && (lev3 >= lev2 ) ){
            $scope.tip = '价格不能高于上级价格';
        }else{
            $scope.tip = '';
            $http.post( rout+'editAgentPrice',data).then(function(data){
                var resData = data.data;
                //console.log(resData)
                if(resData.code == 1){
                    zdyAlert.init(resData.msg,function(){
                        $state.go("daili.quotedPrice",{nav:'xy',page:$scope.page});
                    })
                }else{
                    zdyAlert.init(resData.msg,function(){
                        //console.log(resData.msg)
                    })
                }
            },function(error){
                //console.log(error)
            });
        }
    }
    $('.close-teding-box').bind('click',function(){
        $('.cen,.teding_fixed_box').hide();
    })
    var mySwiper = new Swiper('.swiper-container', {
        observer: true,
        observeParents: true,
        prevButton:'.button-prev',
        nextButton:'.button-next'
    })
    var mySwiper = new Swiper('.swiper-name-container', {
        observer: true,
        observeParents: true,
        prevButton:'.name-button-prev',
        nextButton:'.name-button-next'
    })
}])
//代理 - 报价单 - 新增 - 信用卡
indexapp.controller('teding_add',['$scope','$stateParams','$state','$http',function($scope,$stateParams,$state,$http){
    $scope.type = $stateParams.type;
    $scope.userlev = user.lev;
    $scope.page = $stateParams.page;
    $scope.bankActive = '请选择银行';
    // $scope.bankXykActive = '请选择信用卡';
    $scope.userActive = '请选择用户';

    // 读取银行数据
    $scope.bankInit = function(keyword){
        $http.post( rout+'getBankLoanList',{
            aid:user.id,
            type:1,
            keyword:keyword,
            token:topassword,
            tokenaid:user.id
        }).then(function(data){
            var resData = data.data;
            //console.log(resData)
            if(resData.code == 1){
                if(resData.data){
                    $scope.banklist = resData.data;
                    var arr = $scope.banklist;
                    var ar = [];
                    var s = [];
                    for(var b=0,i=1;b<(arr.length)/6;b++,i++){
                       s = [];
                       s = $scope.banklist.slice(b*6, i*6);
                       ar[b] = s
                    }
                    $scope.banklist = ar;                    
                }else{
                    zdyAlert.init('未配置银行信息',function(){})
                }
            }else{
                zdyAlert.init(resData.msg,function(){})
            }
        },function(error){
            //console.log(error)
        });
    }

    // 读取用户数据
    $scope.userInit = function(keyword){
        $http.post( rout+'mySelfAgenList',{
            id:user.id,
            keyword:keyword,
            token:topassword,
            tokenaid:user.id
        }).then(function(data){
            var resData = data.data;
            if(resData.code == 1){
                if(resData.data.data.length > 0){
                    $scope.userlist = resData.data.data;
                    var arr = $scope.userlist;
                    var ar = [];
                    var s = [];
                    for(var b=0,i=1;b<(arr.length)/6;b++,i++){
                       s = [];
                       s = $scope.userlist.slice(b*6, i*6);
                       ar[b] = s
                    }
                    $scope.userlist = ar;
                }else{
                    zdyAlert.init('没有查到数据',function(){})
                }
            }else{
                zdyAlert.init(resData.msg,function(){
                    //console.log(resData.msg)
                })
            }
        },function(error){
            //console.log(error)
        });        
    }
    // 银行卡搜索
    $scope.bankSearch = function(){
        $scope.bankInit($scope.bankKeup)
    }
    
    
    // 用户搜索
    $scope.userSearch = function(){
        $scope.userInit($scope.userKeup)
    }
    $scope.bankInit('');
    $scope.userInit('');
    $scope.bankXykInit = function(key){
        $http.post( rout+'getCreditList',{
            bank_id:$scope.activeBankId,
            keyword:key,
            token:topassword,
            tokenaid:user.id
        }).then(function(data){
            var resData = data.data;
            //console.log(resData)
            if(resData.code == 1){
                if(resData.data){
                    $scope.bankXyklist = resData.data;
                    var arr = $scope.bankXyklist;
                    var ar = [];
                    var s = [];
                    for(var b=0,i=1;b<(arr.length)/6;b++,i++){
                       s = [];
                       s = $scope.bankXyklist.slice(b*6, i*6);
                       ar[b] = s
                    }
                    $scope.bankXyklist = ar;                    
                }else{
                    zdyAlert.init('没有查到数据',function(){})
                }                
            }else{
                zdyAlert.init(resData.msg,function(){
                    //console.log(resData.msg)
                })
            }
        },function(error){
            //console.log(error)
        });        
    }

    $scope.bankSelect = function(did,index,parentIndex){
        $scope.activeBankParentIndex = parentIndex;
        $scope.bankIndex = index
        $scope.activeBankId = did;
    }
    $scope.bankSubmit = function(){
        $scope.bankActive = $scope.banklist[$scope.activeBankParentIndex][$scope.bankIndex].name;
        $('.cen,.teding_fixed_box').hide();
        // $scope.bankXykInit('');
        // $scope.bankXykActive = '请选择信用卡';
        // $scope.activeBankXykParentIndex = '';
        // $scope.bankXykIndex = ''
        // $scope.activeXykBankId = '';   
        $scope.pricetype = '';

        $('.cen,.teding_fixed_box').hide();
        $http.post( rout+'getAgentPrice',{
            agent_id:user.id,
            type:1,
            bank_id:$scope.activeBankId,
            token:topassword,
            tokenaid:user.id
        }).then(function(data){
            var resData = data.data;
            //console.log(resData)
            if(resData.code == 1){
                if(resData.data.data !== ''){
                    $scope.level_price = resData.data.level_price;
                    $scope.level1_price = resData.data.level1_price;
                    $scope.level2_price = resData.data.level2_price;
                    $scope.level3_price = resData.data.level3_price;
                    $scope.pricetype = resData.data.price_type;
                }
            }else{
                zdyAlert.init(resData.msg,function(){
                    //console.log(resData.msg)
                })
            }
        },function(error){
            //console.log(error)
        });
    }
    $scope.userSelect = function(did,index,parentIndex){
        $scope.activeUserParentIndex = parentIndex;
        $scope.userIndex = index;
        $scope.activeUserId = did;
    }
    $scope.userSubmit = function(){
        $scope.userActive = $scope.userlist[$scope.activeUserParentIndex][$scope.userIndex].name;
        $('.cen,.teding_fixed_box').hide();
    }

    $scope.xyAdd = function(){
        var data = {
            bank_id:$scope.activeBankId,
            card_id:$scope.activeXykBankId,
            agent_id:$scope.activeUserId,
            parent_agent_id:user.id,
            level_price:$scope.level_price,
            level1_price:$scope.level1_price,
            level2_price:$scope.level2_price,
            level3_price:$scope.level3_price,
            type:1,
            token:topassword,
            tokenaid:user.id
        }
        var lev0 = Number($scope.level0_price);
        var lev1 = Number($scope.level1_price);
        var lev2 = Number($scope.level2_price);
        var lev3 = Number($scope.level3_price);
        var userlev = user.lev;


        if(!$scope.activeBankId){
            $scope.tip = '请选择银行';
        }else if(!$scope.activeUserId){
            $scope.tip = '请选择用户';
        }else if(userlev == 0 && (lev3 >= lev2 || lev3 >= lev1 || lev3 >= lev0 || lev2 >= lev1 || lev2 >= lev0 || lev1 >= lev0) ){
            $scope.tip = '价格不能高于上级价格';
        }else if(userlev == 1 && (lev3 >= lev2 || lev3 >= lev1 || lev2 >= lev1) ){
            $scope.tip = '价格不能高于上级价格';
        }else if(userlev == 2 && (lev3 >= lev2 ) ){
            $scope.tip = '价格不能高于上级价格';
        }else{
            $http.post( rout+'addAgentPrice',data).then(function(data){
                var resData = data.data;
                //console.log(resData)
                if(resData.code == 1){
                    if(resData.data.data !== ''){
                        zdyAlert.init(resData.msg,function(){
                            $state.go("daili.quotedPrice",{nav:'xy',page:$scope.page});
                        })
                    }
                }else{
                    zdyAlert.init(resData.msg,function(){
                        //console.log(resData.msg)
                    })
                }
            },function(error){
                //console.log(error)
            });
        }
    }
    $('.close-teding-box').bind('click',function(){
        $('.cen,.teding_fixed_box').hide();
        $scope.activeBankId = -1;
        $scope.activeUserId = -1;
    })
    var mySwiper = new Swiper('.swiper-container', {
        observer: true,
        observeParents: true,
        prevButton:'.button-prev',
        nextButton:'.button-next'
    })
    var mySwiper = new Swiper('.swiper-name-container', {
        observer: true,
        observeParents: true,
        prevButton:'.name-button-prev',
        nextButton:'.name-button-next'
    })
}])
//代理 - 报价单 - 编辑 - 贷款
indexapp.controller('teding_edit_dk',['$scope','$stateParams','$state','$http',function($scope,$stateParams,$state,$http){
    $scope.agentid = $stateParams.agentid;
    $scope.bankId = $stateParams.bank_id;
    $scope.page = $stateParams.page;
    $scope.userlev = user.lev;
    $scope.activeBank = {
        name:''
    }
    $http.post( rout+'getMyselfAgentPrice',{
        agent_id:$scope.agentid,
        bank_id:$scope.bankId,
        type:2,
        token:topassword,
        tokenaid:user.id
    }).then(function(data){
        var resData = data.data;
        $scope.activeBank.name = resData.data[0].loan_name
        $scope.activeBankId = resData.data[0].bank_id
        $scope.activeXdBank = resData.data[0].product_name
        $scope.activeXdBankId = resData.data[0].card_id
        $scope.activeUser = resData.data[0].agent_name
        $scope.activeUserId = resData.data[0].agent_id

        if(resData.data.length == 2){
            $scope.fyType = 3;
            if(resData.data[0].fy_type == 1){
                $scope.cpa_level_price  = resData.data[0].level_price;
                $scope.cpa_level1_price = resData.data[0].level1_price;
                $scope.cpa_level2_price = resData.data[0].level2_price;
                $scope.cpa_level3_price = resData.data[0].level3_price;
                $scope.cpa_pricetype = resData.data[0].price_type;
            }else{
                $scope.cps_level_price  = resData.data[0].level_price;
                $scope.cps_level1_price = resData.data[0].level1_price;
                $scope.cps_level2_price = resData.data[0].level2_price;
                $scope.cps_level3_price = resData.data[0].level3_price;
                $scope.cps_pricetype = resData.data[0].price_type;
            }
            if(resData.data[1].fy_type == 2){
                $scope.cps_level_price  = resData.data[1].level_price;
                $scope.cps_level1_price = resData.data[1].level1_price;
                $scope.cps_level2_price = resData.data[1].level2_price;
                $scope.cps_level3_price = resData.data[1].level3_price;
                $scope.cps_pricetype = resData.data[1].price_type;
            }else{
                $scope.cpa_level_price  = resData.data[1].level_price;
                $scope.cpa_level1_price = resData.data[1].level1_price;
                $scope.cpa_level2_price = resData.data[1].level2_price;
                $scope.cpa_level3_price = resData.data[1].level3_price;
                $scope.cpa_pricetype = resData.data[1].price_type;
            }
        }

        if(resData.data.length == 1){
            if(resData.data[0].fy_type == 1){
                $scope.fyType = 1;
                $scope.cpa_level_price  = resData.data[0].level_price;
                $scope.cpa_level1_price = resData.data[0].level1_price;
                $scope.cpa_level2_price = resData.data[0].level2_price;
                $scope.cpa_level3_price = resData.data[0].level3_price;
                $scope.cpa_pricetype = resData.data[0].price_type;

                // 重置
                $scope.cps_level_price  = '';
                $scope.cps_level1_price = '';
                $scope.cps_level2_price = '';
                $scope.cps_level3_price = '';
                $scope.cps_pricetype = '';                 
            }else if(resData.data[0].fy_type == 2){
                $scope.fyType = 2;
                $scope.cps_level_price  = resData.data[0].level_price;
                $scope.cps_level1_price = resData.data[0].level1_price;
                $scope.cps_level2_price = resData.data[0].level2_price;
                $scope.cps_level3_price = resData.data[0].level3_price;
                $scope.cps_pricetype = resData.data[0].price_type;

                // 重置
                $scope.cpa_level_price  = '';
                $scope.cpa_level1_price = '';
                $scope.cpa_level2_price = '';
                $scope.cpa_level3_price = '';
                $scope.cpa_pricetype = '';                 
            }
        }
    },function(error){
    });
    // 读取用户数据
    $scope.userInit = function(keyword){
        $http.post( rout+'mySelfAgenList',{
            id:user.id,
            keyword:keyword,
            token:topassword,
            tokenaid:user.id
        }).then(function(data){
            var resData = data.data;
            //console.log(resData)
            if(resData.code == 1){
                if(resData.data.data.length > 0){
                    $scope.userlist = resData.data.data;
                    var arr = $scope.userlist;
                    var ar = [];
                    var s = [];
                    for(var b=0,i=1;b<(arr.length)/6;b++,i++){
                       s = [];
                       s = $scope.userlist.slice(b*6, i*6);
                       ar[b] = s
                    }
                    $scope.userlist = ar;                    
                }else{
                    zdyAlert.init('没有查到数据',function(){})
                }
            }else{
                zdyAlert.init(resData.msg,function(){})
            }
        },function(error){
            //console.log(error)
        });        
    }
    // 读取贷款数据
    $scope.xdInit = function(keyword){
        $http.post( rout+'getBankLoanList',{
            aid:user.id,
            type:2,
            keyword:keyword,
            token:topassword,
            tokenaid:user.id
        }).then(function(data){
            var resData = data.data;
            //console.log(resData)
            if(resData.code == 1){
                if(resData.data){
                    $scope.banklist = resData.data;
                    var arr = $scope.banklist;
                    var ar = [];
                    var s = [];
                    for(var b=0,i=1;b<(arr.length)/6;b++,i++){
                       s = [];
                       s = $scope.banklist.slice(b*6, i*6);
                       ar[b] = s
                    }
                    $scope.banklist = ar;
                }else{
                    zdyAlert.init('没有查到数据',function(){})
                }
            }else{
                zdyAlert.init(resData.msg,function(){})
            }
        },function(error){
            //console.log(error)
        });        
    }
    // 贷款公司选择
    $scope.bankSelect = function(did,index,parentIndex){
        $scope.activeBankParentIndex = parentIndex;
        $scope.activeBankId = did;
        $scope.activeBankIndex = index;
    }
    // 确定选择贷款公司
    $scope.bankSure = function(){
        if($scope.activeBankParentIndex !== undefined|| $scope.activeBankIndex !== undefined){
            $scope.activeBank = $scope.banklist[$scope.activeBankParentIndex][$scope.activeBankIndex];
        }
        $('.cen,.teding_fixed_box').hide();
        // 重置数据
        $scope.cps_level_price  = '';
        $scope.cps_level1_price = '';
        $scope.cps_level2_price = '';
        $scope.cps_level3_price = '';
        $scope.cps_pricetype = '';

        $scope.cpa_level_price  = '';
        $scope.cpa_level1_price = '';
        $scope.cpa_level2_price = '';
        $scope.cpa_level3_price = '';
        $scope.cpa_pricetype = '';
        $scope.fyType = 4

        $http.post( rout+'getLoanList',{
            loan_id:$scope.activeBankId,
            keyword:'',
            token:topassword,
            tokenaid:user.id
        }).then(function(data){
            var resData = data.data;
            console.log(resData)
            if(resData.code == 1){
                if(resData.data.length > 0){
                    $scope.bankXdlist = resData.data;
                    var arr = $scope.bankXdlist;
                    var ar = [];
                    var s = [];
                    for(var b=0,i=1;b<(arr.length)/6;b++,i++){
                       s = [];
                       s = $scope.bankXdlist.slice(b*6, i*6);
                       ar[b] = s
                    }
                    $scope.bankXdlist = ar;                    
                }else{
                    zdyAlert.init('没有查到数据',function(){})
                }
                $scope.activeXdBank = '请选择贷款产品';
                $scope.activeXdBankParentIndex = '';
                $scope.activeXdBankId = '';
                $scope.activeXdBankIndex = '';
            }else{
                zdyAlert.init(resData.msg,function(){})
            }
        },function(error){
            //console.log(error)
        });         
    }

    // 贷款产品选择
    $scope.bankXdSelect = function(did,index,parentIndex){
        $scope.activeXdBankParentIndex = parentIndex;
        $scope.activeXdBankId = did;
        $scope.activeXdBankIndex = index;
    }
    // 确认贷款产品
    $scope.bankXdSure = function(){
        if($scope.activeXdBankParentIndex !== undefined || $scope.activeXdBankIndex !== undefined){
            $scope.activeXdBank = $scope.bankXdlist[$scope.activeXdBankParentIndex][$scope.activeXdBankIndex].name;
        }
        $('.cen,.teding_fixed_box').hide();

        $http.post( rout+'getAgentPrice',{
            agent_id:user.id,
            type:2,
            bank_id:$scope.activeXdBankId,
            token:topassword,
            tokenaid:user.id
        }).then(function(data){
            var resData = data.data;
            console.log(resData)
            if(resData.code == 1){
                if(resData.data.length == 2){
                    $scope.fyType = 3;
                    if(resData.data[0].fy_type == 2){
                        $scope.cps_level_price  = resData.data[0].level_price;
                        $scope.cps_level1_price = resData.data[0].level1_price;
                        $scope.cps_level2_price = resData.data[0].level2_price;
                        $scope.cps_level3_price = resData.data[0].level3_price;
                        $scope.cps_pricetype = resData.data[0].price_type;
                    }else{
                        $scope.cpa_level_price = resData.data[0].level_price;
                        $scope.cpa_level1_price = resData.data[0].level1_price;
                        $scope.cpa_level2_price = resData.data[0].level2_price;
                        $scope.cpa_level3_price = resData.data[0].level3_price;
                        $scope.cpa_pricetype = resData.data[0].price_type;
                    }
                    if(resData.data[1].fy_type == 1){
                        $scope.cpa_level_price = resData.data[1].level_price;
                        $scope.cpa_level1_price = resData.data[1].level1_price;
                        $scope.cpa_level2_price = resData.data[1].level2_price;
                        $scope.cpa_level3_price = resData.data[1].level3_price;
                        $scope.cpa_pricetype = resData.data[1].price_type;
                    }else{
                        $scope.cps_level_price = resData.data[1].level_price;
                        $scope.cps_level1_price = resData.data[1].level1_price;
                        $scope.cps_level2_price = resData.data[1].level2_price;
                        $scope.cps_level3_price = resData.data[1].level3_price;
                        $scope.cps_pricetype = resData.data[1].price_type;
                    }
                }else if(resData.data.length == 1){
                    $scope.fyType = resData.data[0].fy_type
                    if($scope.fyType == 1){
                        $scope.cpa_level_price = resData.data[0].level_price;
                        $scope.cpa_level1_price = resData.data[0].level1_price;
                        $scope.cpa_level2_price = resData.data[0].level2_price;
                        $scope.cpa_level3_price = resData.data[0].level3_price;
                        $scope.cpa_pricetype = resData.data[0].price_type;
                        // 重置
                        $scope.cps_level_price  = '';
                        $scope.cps_level1_price = '';
                        $scope.cps_level2_price = '';
                        $scope.cps_level3_price = '';
                        $scope.cps_pricetype = ''; 
                    }else if($scope.fyType == 2){
                        $scope.cps_level_price  = resData.data[0].level_price;
                        $scope.cps_level1_price = resData.data[0].level1_price;
                        $scope.cps_level2_price = resData.data[0].level2_price;
                        $scope.cps_level3_price = resData.data[0].level3_price;
                        $scope.cps_pricetype = resData.data[0].price_type;       
                        // 重置
                        $scope.cpa_level_price = '';
                        $scope.cpa_level1_price = '';
                        $scope.cpa_level2_price = '';
                        $scope.cpa_level3_price = '';
                        $scope.cpa_pricetype = '';                                                 
                    }
                }
            }else{
                zdyAlert.init(resData.msg,function(){
                    //console.log(resData.msg)
                })
            }
        },function(error){
            //console.log(error)
        });        
    }
    // 用户选择
    $scope.userSelect = function(did,index,parentIndex){
        $scope.activeUserParentIndex = parentIndex;
        $scope.activeUserId = did;
        $scope.activeUserIndex = index;
    }
    // 确认用户选择
    $scope.userSure = function(){
        if($scope.activeUserParentIndex !== undefined || $scope.activeUserIndex !== undefined){
            $scope.activeUser = $scope.userlist[$scope.activeUserParentIndex][$scope.activeUserIndex].name;
        }
        $('.cen,.teding_fixed_box').hide();
    }
    // 银行卡搜索
    $scope.bankSearch = function(){
        $scope.xdInit($scope.bankKeup)
    }
    // 用户搜索
    $scope.userSearch = function(){
        $scope.userInit($scope.userKeup)
    }
    // 信贷产品搜索
    $scope.bankXdSearch = function(){
        $http.post( rout+'getLoanList',{
            loan_id:$scope.activeBankId,
            keyword:$scope.bankXdKeup,
            token:topassword,
            tokenaid:user.id
        }).then(function(data){
            var resData = data.data;
            if(resData.code == 1){
                if(resData.data && resData.data.length > 0){
                    $scope.bankXdlist = resData.data;
                    var arr = $scope.bankXdlist;
                    var ar = [];
                    var s = [];
                    for(var b=0,i=1;b<(arr.length)/6;b++,i++){
                       s = [];
                       s = $scope.bankXdlist.slice(b*6, i*6);
                       ar[b] = s
                    }
                    $scope.bankXdlist = ar;                    
                }else{
                    zdyAlert.init('没有查到数据',function(){})
                }
            }else{
                zdyAlert.init(resData.msg,function(){})
            }
        },function(error){
            //console.log(error)
        });          
    }
    $scope.xdInit('');
    $scope.userInit('');

    // 表单提交编辑
    $scope.formSubmit = function(){
        var data = {
            bank_id:$scope.activeBankId,
            card_id:$scope.activeXdBankId,
            id:$stateParams.did,  
            new_agent_id:$scope.activeUserId,
            agent_id:$scope.agentid,
            parent_agent_id:user.id,
            token:topassword,
            tokenaid:user.id,
            type:2
        }

        var cpa_lev0 = Number($scope.cpa_level_price);
        var cpa_lev1 = Number($scope.cpa_level1_price);
        var cpa_lev2 = Number($scope.cpa_level2_price);
        var cpa_lev3 = Number($scope.cpa_level3_price);

        var cps_lev0 = Number($scope.cps_level_price);
        var cps_lev1 = Number($scope.cps_level1_price);
        var cps_lev2 = Number($scope.cps_level2_price);
        var cps_lev3 = Number($scope.cps_level3_price);

        var userlev = user.lev;

        if($scope.activeXdBankId == ''){
            $scope.tip = '请选择贷款产品';
            return
        }else if($scope.activeUserId == ''){
            $scope.tip = '请选择用户';
            return
        }

        if($scope.fyType == 3){
            if(userlev == 0 && (cpa_lev3 > cpa_lev2 || cpa_lev3 >= cpa_lev1 || cpa_lev3 >= cpa_lev0 || cpa_lev2 >= cpa_lev1 || cpa_lev2 >= cpa_lev0 || cpa_lev1 >= cpa_lev0)){
                $scope.tip = '价格不能高于上级价格';
                return
            }else if(userlev == 1 && (cpa_lev3 >= cpa_lev2 || cpa_lev3 >= cpa_lev1 || cpa_lev2 >= cpa_lev1) ){
                $scope.tip = '价格不能高于上级价格';
                return
            }else if(userlev == 2 && (cpa_lev3 >= cpa_lev2 ) ){
                $scope.tip = '价格不能高于上级价格';
                return
            }
            if(userlev == 0 && (cps_lev3 > cps_lev2 || cps_lev3 >= cps_lev1 || cps_lev3 >= cps_lev0 || cps_lev2 >= cps_lev1 || cps_lev2 >= cps_lev0 || cps_lev1 >= cps_lev0)){
                $scope.tip = '价格不能高于上级价格';
                return
            }else if(userlev == 1 && (cps_lev3 >= cps_lev2 || cps_lev3 >= cps_lev1 || cps_lev2 >= cps_lev1) ){
                $scope.tip = '价格不能高于上级价格';
                return
            }else if(userlev == 2 && (cps_lev3 >= cps_lev2 ) ){
                $scope.tip = '价格不能高于上级价格';
                return
            }
            data.cpa_level_price  = cpa_lev0;
            data.cpa_level1_price = cpa_lev1;
            data.cpa_level2_price = cpa_lev2;
            data.cpa_level3_price = cpa_lev3;
            
            data.cps_level_price  = cps_lev0;
            data.cps_level1_price = cps_lev1;
            data.cps_level2_price = cps_lev2;
            data.cps_level3_price = cps_lev3;
        }

        if($scope.fyType == 1){
            if(userlev == 0 && (cpa_lev3 > cpa_lev2 || cpa_lev3 >= cpa_lev1 || cpa_lev3 >= cpa_lev0 || cpa_lev2 >= cpa_lev1 || cpa_lev2 >= cpa_lev0 || cpa_lev1 >= cpa_lev0)){
                $scope.tip = '价格不能高于上级价格';
                return
            }else if(userlev == 1 && (cpa_lev3 >= cpa_lev2 || cpa_lev3 >= cpa_lev1 || cpa_lev2 >= cpa_lev1) ){
                $scope.tip = '价格不能高于上级价格';
                return
            }else if(userlev == 2 && (cpa_lev3 >= cpa_lev2 ) ){
                $scope.tip = '价格不能高于上级价格';
                return
            }
            data.cpa_level_price  = cpa_lev0;
            data.cpa_level1_price = cpa_lev1;
            data.cpa_level2_price = cpa_lev2;
            data.cpa_level3_price = cpa_lev3;
        }else if($scope.fyType == 2){
            if(userlev == 0 && (cps_lev3 > cps_lev2 || cps_lev3 >= cps_lev1 || cps_lev3 >= cps_lev0 || cps_lev2 >= cps_lev1 || cps_lev2 >= cps_lev0 || cps_lev1 >= cps_lev0)){
                $scope.tip = '价格不能高于上级价格';
                return
            }else if(userlev == 1 && (cps_lev3 >= cps_lev2 || cps_lev3 >= cps_lev1 || cps_lev2 >= cps_lev1) ){
                $scope.tip = '价格不能高于上级价格';
                return
            }else if(userlev == 2 && (cps_lev3 >= cps_lev2 ) ){
                $scope.tip = '价格不能高于上级价格';
                return
            }  
            data.cps_level_price  = cps_lev0;
            data.cps_level1_price = cps_lev1;
            data.cps_level2_price = cps_lev2;
            data.cps_level3_price = cps_lev3;
        }
        
        $http.post( rout+'editAgentPrice',data).then(function(data){
            var resData = data.data;
            //console.log(resData)
            if(resData.code == 1){
                zdyAlert.init(resData.msg,function(){
                    $state.go("daili.quotedPrice",{nav:'dk',page:$scope.page});
                })
            }else{
                zdyAlert.init(resData.msg,function(){
                    //console.log(resData.msg)
                })
            }
        },function(error){
            //console.log(error)
        });        
    }





    $('.close-teding-box').bind('click',function(){
        $('.cen,.teding_fixed_box').hide();
    })

    var mySwiper = new Swiper('.swiper-container', {
        observer: true,
        observeParents: true,
        prevButton:'.button-prev',
        nextButton:'.button-next'
    })
    var mySwiper = new Swiper('.swiper-name-container', {
        observer: true,
        observeParents: true,
        prevButton:'.name-button-prev',
        nextButton:'.name-button-next'
    })
}])
//代理 - 报价单 - 新增 - 贷款
indexapp.controller('teding_add_dk',['$scope','$stateParams','$state','$http',function($scope,$stateParams,$state,$http){
    $scope.type = $stateParams.type;
    $scope.page = $stateParams.page;
    $scope.userlev = user.lev;
    $scope.bankActive = '请选择贷款产品';
    $scope.userActive = '请选择用户';
    $scope.bankYhActive = '请选择贷款公司';
    $scope.activeBankId = '';
    $scope.activeUserId = '';

    // 读取银行数据
    $scope.xdInit = function(keyword){
        $http.post( rout+'getBankLoanList',{
            aid:user.id,
            keyword:keyword,
            type:2,
            token:topassword,
            tokenaid:user.id
        }).then(function(data){
            var resData = data.data;
            //console.log(resData)
            if(resData.code == 1){
                if(resData.data){
                    $scope.bankYhlist = resData.data;
                    var arr = $scope.bankYhlist;
                    var ar = [];
                    var s = [];
                    for(var b=0,i=1;b<(arr.length)/6;b++,i++){
                       s = [];
                       s = $scope.bankYhlist.slice(b*6, i*6);
                       ar[b] = s
                    }
                    $scope.bankYhlist = ar;
                }else{
                    zdyAlert.init('未配置银行信息',function(){})                    
                }
            }else{
                zdyAlert.init(resData.msg,function(){
                    //console.log(resData.msg)
                })
            }
        },function(error){
            //console.log(error)
        });
    }
    // 读取用户数据
    $scope.userInit = function(keyword){
        $http.post( rout+'mySelfAgenList',{
            id:user.id,
            keyword:keyword,
            token:topassword,
            tokenaid:user.id
        }).then(function(data){
            var resData = data.data;
            //console.log(resData)
            if(resData.code == 1){
                if(resData.data.data.length > 0){
                    $scope.userlist = resData.data.data;
                    var arr = $scope.userlist;
                    var ar = [];
                    var s = [];
                    for(var b=0,i=1;b<(arr.length)/6;b++,i++){
                       s = [];
                       s = $scope.userlist.slice(b*6, i*6);
                       ar[b] = s
                    }
                    $scope.userlist = ar;                    
                }else{
                    zdyAlert.init('没有查到数据',function(){})
                }
            }else{
                zdyAlert.init(resData.msg,function(){})
            }
        },function(error){
            //console.log(error)
        });        
    }
    $scope.xdInit('');
    $scope.userInit('');
    // 银行卡搜索
    $scope.bankYhSearch = function(){
        $scope.xdInit($scope.bankYhKeup)
    }
    // 银行卡搜索
    $scope.bankSearch = function(){
        $scope.xdInit($scope.bankKeup)
    }
    // 用户搜索
    $scope.userSearch = function(){
        $scope.userInit($scope.userKeup)
    }

    $scope.bankSelect = function(did,index,parentIndex){
        $scope.activeBankParentIndex = parentIndex;
        $scope.bankIndex = index
        $scope.activeBankId = did;
    }

    $scope.bankYhSelect = function(did,index,parentIndex){
        $scope.activeYhBankParentIndex = parentIndex;
        $scope.bankYhIndex = index
        $scope.activeYhBankId = did;
    }
    $scope.xdYhInit = function(key){
        $http.post( rout+'getLoanList',{
            loan_id:$scope.activeYhBankId,
            keyword:key,
            token:topassword,
            tokenaid:user.id
        }).then(function(data){
            var resData = data.data;
            //console.log(resData)
            if(resData.code == 1){
                if(resData.data.length > 0){
                    $scope.banklist = resData.data;
                    var arr = $scope.banklist;
                    var ar = [];
                    var s = [];
                    for(var b=0,i=1;b<(arr.length)/6;b++,i++){
                       s = [];
                       s = $scope.banklist.slice(b*6, i*6);
                       ar[b] = s
                    }
                    $scope.banklist = ar;                    
                }else{
                    zdyAlert.init('没有查到数据',function(){})
                }
            }else{
                zdyAlert.init(resData.msg,function(){})
            }
        },function(error){
            //console.log(error)
        });            
    }
    $scope.bankYhSubmit = function(){
        $scope.bankYhActive = $scope.bankYhlist[$scope.activeYhBankParentIndex][$scope.bankYhIndex].name;
        $('.cen,.teding_fixed_box').hide();
        $scope.xdYhInit('');
        $scope.bankActive = '请选择贷款产品';
        $scope.activeBankParentIndex = '';
        $scope.bankIndex = ''
        $scope.activeBankId = '';
        $scope.pricetype = '';
        $scope.fyType = 4;
    }

    $scope.cps_level_price = '';
    $scope.cps_level1_price = '';
    $scope.cps_level2_price = '';
    $scope.cps_level3_price = '';
    $scope.cpa_level_price = '';
    $scope.cpa_level1_price = '';
    $scope.cpa_level2_price = '';
    $scope.cpa_level3_price = '';

    $scope.bankSubmit = function(){
        $scope.bankActive = $scope.banklist[$scope.activeBankParentIndex][$scope.bankIndex].name;
        $('.cen,.teding_fixed_box').hide();
        $http.post( rout+'getAgentPrice',{
            agent_id:user.id,
            type:2,
            bank_id:$scope.activeBankId,
            token:topassword,
            tokenaid:user.id
        }).then(function(data){
            var resData = data.data;
            console.log(resData)

            if(resData.code == 1){
                if(resData.data.length == 2){
                    $scope.fyType = 3;
                    if(resData.data[0].fy_type == 2){
                        $scope.cps_level_price  = resData.data[0].level_price;
                        $scope.cps_level1_price = resData.data[0].level1_price;
                        $scope.cps_level2_price = resData.data[0].level2_price;
                        $scope.cps_level3_price = resData.data[0].level3_price;
                        $scope.cps_pricetype = resData.data[0].price_type;
                    }else{
                        $scope.cpa_level_price = resData.data[0].level_price;
                        $scope.cpa_level1_price = resData.data[0].level1_price;
                        $scope.cpa_level2_price = resData.data[0].level2_price;
                        $scope.cpa_level3_price = resData.data[0].level3_price;
                        $scope.cpa_pricetype = resData.data[0].price_type;
                    }
                    if(resData.data[1].fy_type == 1){
                        $scope.cpa_level_price = resData.data[1].level_price;
                        $scope.cpa_level1_price = resData.data[1].level1_price;
                        $scope.cpa_level2_price = resData.data[1].level2_price;
                        $scope.cpa_level3_price = resData.data[1].level3_price;
                        $scope.cpa_pricetype = resData.data[1].price_type;
                    }else{
                        $scope.cps_level_price = resData.data[1].level_price;
                        $scope.cps_level1_price = resData.data[1].level1_price;
                        $scope.cps_level2_price = resData.data[1].level2_price;
                        $scope.cps_level3_price = resData.data[1].level3_price;
                        $scope.cps_pricetype = resData.data[1].price_type;
                    }
                }else if(resData.data.length == 1){
                    $scope.fyType = resData.data[0].fy_type
                    if($scope.fyType == 1){
                        $scope.cpa_level_price = resData.data[0].level_price;
                        $scope.cpa_level1_price = resData.data[0].level1_price;
                        $scope.cpa_level2_price = resData.data[0].level2_price;
                        $scope.cpa_level3_price = resData.data[0].level3_price;
                        $scope.cpa_pricetype = resData.data[0].price_type;
                        // 重置
                        $scope.cps_level_price  = '';
                        $scope.cps_level1_price = '';
                        $scope.cps_level2_price = '';
                        $scope.cps_level3_price = '';
                        $scope.cps_pricetype = ''; 
                    }else if($scope.fyType == 2){
                        $scope.cps_level_price  = resData.data[0].level_price;
                        $scope.cps_level1_price = resData.data[0].level1_price;
                        $scope.cps_level2_price = resData.data[0].level2_price;
                        $scope.cps_level3_price = resData.data[0].level3_price;
                        $scope.cps_pricetype = resData.data[0].price_type;       
                        // 重置
                        $scope.cpa_level_price = '';
                        $scope.cpa_level1_price = '';
                        $scope.cpa_level2_price = '';
                        $scope.cpa_level3_price = '';
                        $scope.cpa_pricetype = '';                                                
                    }
                }


            }else{
                zdyAlert.init(resData.msg,function(){
                    //console.log(resData.msg)
                })
            }
        },function(error){
            //console.log(error)
        });
    }
    $scope.userSelect = function(did,index,parentIndex){
        $scope.activeUserParentIndex = parentIndex;
        $scope.userIndex = index;
        $scope.activeUserId = did;
    }
    $scope.userSubmit = function(){
        $scope.userActive = $scope.userlist[$scope.activeUserParentIndex][$scope.userIndex].name;
        $('.cen,.teding_fixed_box').hide();
    }

    $scope.xyAdd = function(){

        var cpa_lev0 = Number($scope.cpa_level_price);
        var cpa_lev1 = Number($scope.cpa_level1_price);
        var cpa_lev2 = Number($scope.cpa_level2_price);
        var cpa_lev3 = Number($scope.cpa_level3_price);

        var cps_lev0 = Number($scope.cps_level_price);
        var cps_lev1 = Number($scope.cps_level1_price);
        var cps_lev2 = Number($scope.cps_level2_price);
        var cps_lev3 = Number($scope.cps_level3_price);

        var data = {
            bank_id:$scope.activeYhBankId,
            agent_id:$scope.activeUserId,
            card_id:$scope.activeBankId,
            parent_agent_id:user.id,
            type:2,
            token:topassword,
            tokenaid:user.id
        }

        var userlev = user.lev;
        if(!$scope.activeYhBankId){
            $scope.tip = '请选择贷款公司';
            return;
        }else if(!$scope.activeBankId){
            $scope.tip = '请选择贷款产品';
            return;
        }else if(!$scope.activeUserId){
            $scope.tip = '请选择用户';
            return;
        }

        if($scope.fyType == 3){
            if(userlev == 0 && (cpa_lev3 > cpa_lev2 || cpa_lev3 >= cpa_lev1 || cpa_lev3 >= cpa_lev0 || cpa_lev2 >= cpa_lev1 || cpa_lev2 >= cpa_lev0 || cpa_lev1 >= cpa_lev0)){
                $scope.tip = '价格不能高于上级价格';
                return
            }else if(userlev == 1 && (cpa_lev3 >= cpa_lev2 || cpa_lev3 >= cpa_lev1 || cpa_lev2 >= cpa_lev1) ){
                $scope.tip = '价格不能高于上级价格';
                return
            }else if(userlev == 2 && (cpa_lev3 >= cpa_lev2 ) ){
                $scope.tip = '价格不能高于上级价格';
                return
            }
            if(userlev == 0 && (cps_lev3 > cps_lev2 || cps_lev3 >= cps_lev1 || cps_lev3 >= cps_lev0 || cps_lev2 >= cps_lev1 || cps_lev2 >= cps_lev0 || cps_lev1 >= cps_lev0)){
                $scope.tip = '价格不能高于上级价格';
                return
            }else if(userlev == 1 && (cps_lev3 >= cps_lev2 || cps_lev3 >= cps_lev1 || cps_lev2 >= cps_lev1) ){
                $scope.tip = '价格不能高于上级价格';
                return
            }else if(userlev == 2 && (cps_lev3 >= cps_lev2 ) ){
                $scope.tip = '价格不能高于上级价格';
                return
            }
            data.cpa_level_price  = cpa_lev0;
            data.cpa_level1_price = cpa_lev1;
            data.cpa_level2_price = cpa_lev2;
            data.cpa_level3_price = cpa_lev3;

            data.cps_level_price  = cps_lev0;
            data.cps_level1_price = cps_lev1;
            data.cps_level2_price = cps_lev2;
            data.cps_level3_price = cps_lev3;
        }

        if($scope.fyType == 1){
            if(userlev == 0 && (cpa_lev3 > cpa_lev2 || cpa_lev3 >= cpa_lev1 || cpa_lev3 >= cpa_lev0 || cpa_lev2 >= cpa_lev1 || cpa_lev2 >= cpa_lev0 || cpa_lev1 >= cpa_lev0)){
                $scope.tip = '价格不能高于上级价格';
                return
            }else if(userlev == 1 && (cpa_lev3 >= cpa_lev2 || cpa_lev3 >= cpa_lev1 || cpa_lev2 >= cpa_lev1) ){
                $scope.tip = '价格不能高于上级价格';
                return
            }else if(userlev == 2 && (cpa_lev3 >= cpa_lev2 ) ){
                $scope.tip = '价格不能高于上级价格';
                return
            }
            data.cpa_level_price  = cpa_lev0;
            data.cpa_level1_price = cpa_lev1;
            data.cpa_level2_price = cpa_lev2;
            data.cpa_level3_price = cpa_lev3;
        }else if($scope.fyType == 2){
            if(userlev == 0 && (cps_lev3 > cps_lev2 || cps_lev3 >= cps_lev1 || cps_lev3 >= cps_lev0 || cps_lev2 >= cps_lev1 || cps_lev2 >= cps_lev0 || cps_lev1 >= cps_lev0)){
                $scope.tip = '价格不能高于上级价格';
                return
            }else if(userlev == 1 && (cps_lev3 >= cps_lev2 || cps_lev3 >= cps_lev1 || cps_lev2 >= cps_lev1) ){
                $scope.tip = '价格不能高于上级价格';
                return
            }else if(userlev == 2 && (cps_lev3 >= cps_lev2 ) ){
                $scope.tip = '价格不能高于上级价格';
                return
            }  
            data.cps_level_price  = cps_lev0;
            data.cps_level1_price = cps_lev1;
            data.cps_level2_price = cps_lev2;
            data.cps_level3_price = cps_lev3;
        }

        $http.post( rout+'addAgentPrice',data).then(function(data){
            var resData = data.data;
            //console.log(resData)
            if(resData.code == 1){
                if(resData.data.data !== ''){
                    zdyAlert.init(resData.msg,function(){
                        // window.history.back();
                        $state.go("daili.quotedPrice",{nav:'dk',page:$scope.page});
                    })
                }
            }else{
                zdyAlert.init(resData.msg,function(){
                    //console.log(resData.msg)
                })
            }
        },function(error){
            //console.log(error)
        });
    }
    $('.close-teding-box').bind('click',function(){
        $('.cen,.teding_fixed_box').hide();
        $scope.activeBankId = -1;
        $scope.activeUserId = -1;
    })
    var mySwiper = new Swiper('.swiper-container', {
        observer: true,
        observeParents: true,
        prevButton:'.button-prev',
        nextButton:'.button-next'
    })
    var mySwiper = new Swiper('.swiper-name-container', {
        observer: true,
        observeParents: true,
        prevButton:'.name-button-prev',
        nextButton:'.name-button-next'
    })
}])
//我的
indexapp.controller('personal',['$scope','$http',function($scope,$http){
    var userOtherInfo = store.get('userOtherInfo');
    $scope.userInfo = userOtherInfo;
    $scope.username = userOtherInfo.name;
    $scope.tip = '';

    $scope.personalSub = function(){
        if($scope.userInfo.name == ''){
            $scope.tip = '请输入姓名';
            return 
        }else if($scope.userInfo.phone == ''){
            $scope.tip = '请输入手机号';
            return
        }else if( !(/^1[34578]\d{9}$/.test($scope.userInfo.phone)) ){
            $scope.tip = '手机号有误';
            return
        }else{
            $scope.tip = '';
        }
        $http.post( rout+'editUserCommit',{
            id:user.id,
            name:$scope.userInfo.name,
            phone:$scope.userInfo.phone,
            email:$scope.userInfo.email,
            remark:$scope.userInfo.remark,
            token:topassword,
            tokenaid:user.id
        }).then(function(data){
            var resData = data.data;
            //console.log(resData)
            if(resData.code == 1){
                $http.post( rout+'getAgentInfoById',{id:user.id,token:topassword,tokenaid:user.id}).then(function(data){
                    var resData = data.data;
                    //console.log(resData)
                    if(resData.code == 1){
                        store.set('userOtherInfo',resData.data);
                        $scope.username = userOtherInfo.name;
                    }else{
                        zdyAlert.init(resData.msg,function(){
                            //console.log(resData.msg)
                        })            
                    }
                },function(error){
                    //console.log(error)
                }); 
                zdyAlert.init(resData.msg,function(){})
            }else{
                zdyAlert.init(resData.msg,function(){})
            }
        },function(error){
            //console.log(error)
        });  
    }
}])
//我的分类
indexapp.controller('personalthree',['$scope','$http',function($scope,$http){
    var userOtherInfo = store.get('userOtherInfo');
    $scope.userInfo = userOtherInfo;
    $scope.username = userOtherInfo.name;
}])
//重置密码
indexapp.controller('resetPass',['$scope','$http',function($scope,$http){
    var userOtherInfo = store.get('userOtherInfo');
    $scope.userInfo = userOtherInfo;
    $scope.username = userOtherInfo.name;
    $scope.tip = '';
    $scope.oldpassword  = '';
    $scope.newpassword  = '';
    $scope.repeatpassword  = '';
    $scope.personalSub = function(){
        if($scope.oldpassword == '' || $scope.newpassword == '' || $scope.repeatpassword == ''){
            $scope.tip = '请输入密码';
            return 
        }else if($scope.newpassword.length < 6 != $scope.repeatpassword < 6){
            $scope.tip = '密码最少6个字符';
            return             
        }else if($scope.newpassword != $scope.repeatpassword){
            $scope.tip = '新密码不一致';
            return 
        }
        $http.post( rout+'resetPass',{
            aid:user.id,
            oldpassword:$scope.oldpassword,
            newpassword:$scope.newpassword,
            repeatpassword:$scope.repeatpassword,
            token:topassword,
            tokenaid:user.id
        }).then(function(data){
            var resData = data.data;
            //console.log(data)
             if(resData.code == 1){
                 zdyAlert.init(resData.msg,function(){
                     window.location.href="index";
                 })
                 
             }else{
                zdyAlert.init(resData.msg,function(){})
             }
        },function(error){
            //console.log(error)
        });         
    }
}])
// 推广
indexapp.controller('spread',['$scope','$stateParams','$http',function($scope,$stateParams,$http){
    if($stateParams.types == 'xy'){
        $scope.tap = 'xy';
    }else if($stateParams.types == 'xd'){
        $scope.tap = 'xd';
    }else{
        $scope.tap = 'xy';
    }
    $scope.tapFun = function(types){
        if(types == 'xy'){
            $scope.tap = 'xy';
        }else{
            $scope.tap = 'xd';
        }
    }
    $http.post( rout+'getBankLoanList',{
        aid:user.id,
        type:1,
        token:topassword,
        tokenaid:user.id
    }).then(function(data){
        var resData = data.data;
        // console.log(resData)
        if(resData.code == 1){
            if(resData.data){
                $scope.bankLisk = resData.data;
            }
        }else{
            zdyAlert.init(resData.msg,function(){})
        }
    },function(error){
        //console.log(error)
    });
    $http.post( rout+'getBankLoanList',{
        aid:user.id,
        type:3,
        token:topassword,
        tokenaid:user.id
    }).then(function(data){
        var resData = data.data;
        // console.log(resData)
        if(resData.code == 1){
            if(resData.data){
                $scope.xdLisk = resData.data;
            }
        }else{
            zdyAlert.init(resData.msg,function(){})
        }
    },function(error){
        //console.log(error)
    });
}])
// 推广 - 详情
indexapp.controller('spreadDetails',['$scope','$stateParams','$http',function($scope,$stateParams,$http){
    $scope.did = $stateParams.did;
    $scope.types = $stateParams.types;
    var typsNum = $scope.types == 'xy'?1:2;

    $http.post( rout+'getProductInfoByid',{
        aid:user.id,
        type:typsNum,
        productid:$scope.did,
        token:topassword,
        tokenaid:user.id
    }).then(function(data){
        var resData = data.data;
        console.log(resData)
        $scope.titleName = resData.bank_name;
        $scope.logo = resData.logo;
        if($scope.types == 'xy'){
            $scope.cardList = resData.card_list;
        }else if($scope.types == 'xd'){
            $scope.card_name = resData.card_name;
            $scope.card_details = resData.card_details;
            $scope.money_range = resData.money_range;
        }
        $('#info-box').html(resData.xykhtml)
        var cardurl = new QRCode(document.getElementById("erweima"),resData.share_url);
        cardurl.callback = function(){
            html2canvas(document.getElementById("info-box"), {
                allowTaint: true,
                taintTest: false,
                onrendered: function(canvas) {
                    canvas.id = "box";
                    //生成base64图片数据
                    var dataUrl = canvas.toDataURL();
                    var newImg = document.createElement("img");
                    newImg.src =  dataUrl;
                    $('#boxs').html(newImg);
                }
            });
        };
        cardurl.callback();
    },function(error){
        //console.log(error)
    });
}])
indexapp.config(function($stateProvider, $urlRouterProvider,cfpLoadingBarProvider){
    cfpLoadingBarProvider.includeSpinner = false;
    $urlRouterProvider.otherwise('/subIndex');
    $stateProvider
        .state('subIndex',{
            url: '/subIndex',
            templateUrl: 'subIndex.html',
            controller: 'subIndex'
        })
        .state('sharePage',{
            url: '/sharePage',
            templateUrl: 'sharePage.html',
            controller: 'sharePage'
        })      
        .state('withdraw',{
            url: '/withdraw',
            templateUrl: 'withdraw.html',
            controller: 'withdraw'
        })
        .state('report',{
            url: '/report',
            templateUrl: 'report.html',
            controller: 'report'
        })
        .state('myuser',{
            url: '/myuser',
            templateUrl: 'myuser.html',
            controller: 'myuser'
        })  
        .state('collectionAccount',{
            url: '/collectionAccount',
            templateUrl: 'collectionAccount.html',
            controller: 'collectionAccount'
        })
        .state('myuser.yonghu',{
            url: '/yonghu',
            templateUrl: 'user.html',
            controller: 'user'
        })  
        .state('myuser.access',{
            url: '/access',
            templateUrl: 'access.html',
            controller: 'access'
        })  
        .state('myuser.crdApplyRecord',{
            url: '/crdApplyRecord',
            templateUrl: 'crdApplyRecord.html',
            controller: 'crdApplyRecord'
        })  
        .state('myuser.loanApplyRecord',{
            url: '/loanApplyRecord',
            templateUrl: 'loanApplyRecord.html',
            controller: 'loanApplyRecord'
        })
        .state('daili',{
            url: '/daili',
            templateUrl: 'daili.html',
            controller: 'daili'
        })
        .state('daili.myAgent',{
            url: '/myAgent?page',
            templateUrl: 'myAgent.html',
            controller: 'myAgent'
        })
        .state('myAgentThree',{
            url: '/myAgentThree?id&name&lev&page&parentpage',
            templateUrl: 'myAgentThree.html',
            controller: 'myAgentThree'
        })
        .state('myAgentThreeTwo',{
            url: '/myAgentThreeTwo?id&name&lev&twoId&twoName&twoLev&page&parentpage',
            templateUrl: 'myAgentThreeTwo.html',
            controller: 'myAgentThreeTwo'
        })
        .state('myAgent_edit',{
            url: '/myAgent_edit?did?page',
            templateUrl: 'myAgent_edit.html',
            controller: 'myAgent_edit'
        })
        .state('daili.agentAdd',{
            url: '/agentAdd',
            templateUrl: 'agentAdd.html',
            controller: 'agentAdd'
        })
        .state('daili.quotedPrice',{
            url: '/quotedPrice?nav&page',
            templateUrl: 'quotedPrice.html',
            controller: 'quotedPrice'
        })
        .state('teding_edit',{ //特定用户编辑 - 信用卡
            url: '/teding_edit?agentid&did&name&lv0_p&lv1_p&lv2_p&lv3_p&bank_name&bank_id&page$cardid$cardname$pricetype',
            templateUrl: 'teding_edit.html',
            controller: 'teding_edit'            
        })
        .state('teding_edit_dk',{ //特定用户编辑 - 贷款
            url: '/teding_edit_dk?agentid&did&bank_id&page',
            templateUrl: 'teding_edit_dk.html',
            controller: 'teding_edit_dk'            
        })
        .state('teding_add',{ //特定用户增加 - 信用卡
            url: '/teding_add?type&page',
            templateUrl: 'teding_add.html',
            controller: 'teding_add'            
        })
        .state('teding_add_dk',{ //特定用户增加 - 贷款
            url: '/teding_add_dk?type&page',
            templateUrl: 'teding_add_dk.html',
            controller: 'teding_add_dk'            
        })
        .state('personalthree',{
            url: '/personalthree',
            templateUrl: 'personalthree.html',
            controller: 'personalthree'
        })
        .state('personal',{
            url: '/personal',
            templateUrl: 'personal.html',
            controller: 'personal'
        })
        .state('resetPass',{
            url: '/resetPass',
            templateUrl: 'resetPass.html',
            controller: 'resetPass'
        })
        .state('spread',{
            url: '/spread?types',
            templateUrl: 'spread.html',
            controller: 'spread'
        })
        .state('spreadDetails',{
            url: '/spreadDetails?did&types',
            templateUrl: 'spreadDetails.html',
            controller: 'spreadDetails'
        })
})

// 编辑+增加+信用卡
indexapp.directive('yinhang',[function(){
    return{
        restrict:'A',
        link:function(scope,element,attrs){
            element.bind('click',function(){

                $('.cen,.teding_fixed_box.yinhang').show();
            })
        }
    }
}])
// 编辑+增加+信用卡
indexapp.directive('xinyongka',[function(){
    return{
        restrict:'A',
        link:function(scope,element,attrs){
            element.bind('click',function(){
                $('.cen,.teding_fixed_box.xinyong').show();
            })
        }
    }
}])
// 编辑+增加+用户
indexapp.directive('user',[function(){
    return{
        restrict:'A',
        link:function(scope,element,attrs){
            element.bind('click',function(){
                $('.cen,.teding_fixed_box.yonghu').show();
            })
        }
    }
}])
// 更多
indexapp.directive('more',[function(){
    return{
        restrict:'A',
        link:function(scope,element,attrs){
            element.bind('click',function(){
                var $canpinListBox = $(element).parent().find('.canpin-list-box');
                if($canpinListBox.css('display') == 'block'){
                    $(element).find('.more').removeClass('dh');
                    $canpinListBox.stop().slideUp(500);
                }else{
                    $(element).find('.more').addClass('dh');
                    $canpinListBox.stop().slideDown(500);
                }
            })
        }
    }
}])
// 日期控件
indexapp.directive('riqi',[function(){
    return{
        restrict:'A',
        link:function(scope,element,attrs){
            element.bind('click',function(){
                if($('.dateBox').css('display') == 'none'){
                    $('.dateBox').stop().slideDown();
                    $('.jt').addClass('jtdh');
                }else{
                    $('.dateBox').stop().slideUp();
                    $('.jt').removeClass('jtdh');
                }
            })

        }
    }
}])
// 日期控件 - 开始
indexapp.directive('stime',[function(){
    return{
        restrict:'A',
        link:function(scope,element,attrs){
            element.text('请选择开始时间')
            var calendar = new datePicker();
            calendar.init({
                'trigger': '#stime', 
                'type': 'date',
                'minDate':'1900-1-1',
                'maxDate':'2100-12-31',
                'onSubmit':function(){
                    element.text(calendar.value)
                    scope.stime = calendar.value
                }
            });
        }
    }
}])
// 日期控件 - 结束
indexapp.directive('etime',[function(){
    return{
        restrict:'A',
        link:function(scope,element,attrs){
            element.text('请选择结束时间')
            var calendar = new datePicker();
            calendar.init({
                'trigger': '#etime', 
                'type': 'date',
                'minDate':'1900-1-1',
                'maxDate':'2100-12-31',
                'onSubmit':function(){
                    element.text(calendar.value)
                    scope.etime = calendar.value
                }
            });

        }
    }
}])

// 脚步切换高亮
indexapp.directive('footertap',[function(){
    return{
        restrict:'A',
        link:function(scope,element,attrs){
            var href=window.location.href;
            var index = href.indexOf("#/");
            var str = href.substring(index,href.length);
            var src,
                eqIndex;
            if(str.indexOf('subIndex') != -1 || str.indexOf('sharePage') != -1){
                src="/assets/agent/img/footer1_active.png";
                eqIndex = 0;
            }else if(str.indexOf('myuser') != -1){
                src="/assets/agent/img/footer2_active.png";
                eqIndex = 1;
            }else if(str.indexOf('daili') != -1 || str.indexOf('teding') != -1 || str.indexOf('myAgentThree') != -1){
                src="/assets/agent/img/footer3_active.png";
                eqIndex = 2;
            }else if(str.indexOf('personal') != -1 || str.indexOf('withdraw') != -1 || str.indexOf('report') != -1 || str.indexOf('collectionAccount') != -1 || str.indexOf('spread') != -1){
                src="/assets/agent/img/footer4_active.png";
                eqIndex = 3;
            }
            $('#footer a').eq(eqIndex).find('img').attr('src',src)
        }
    }
}])
indexapp.filter('lastcode', function() { //可以注入依赖
    return function(text) {
        return text.slice(text.length - 3)
    }
});
indexapp.filter('phonexin', function() { //可以注入依赖
    return function(text) {
        text = String(text)
        var first = text.slice(0,4);
        var last = text.slice(text.length - 3);
        return first +'*****'+ last
    }
});
indexapp.filter('startname', function() { 
    return function(text) {
        text = String(text);
        var first = text.slice(0,1);
        var last = '';
        for(var i=0;i<text.length-1;i++){
            last += '*';
        }
        if(text == '匿名用户'){
            return '匿名用户';
        }else{
            return first + last;
        }
    }
});
// 更多规则
indexapp.filter('moreRule', function() { //可以注入依赖
    return function(text) {
        text = String(text)
        if(text.length > 30){
            var first = text.slice(0,30);
            text = first + '.....'
        }else if(text == 'null'){
            text = ''
        }
        return text
    }
});


indexapp.directive('paginations', function() {
    return {
      restrict : 'EA',
      templateUrl : 'pagination.html',
      replace: true,
      scope:{
        conf:'='
      },
      link:function(scope, ele , attrs){
        var conf = scope.conf;
        if(!scope.conf){
            scope.conf = {
                currentPage:0,
                count:0
            }
        }
        scope.$watch('conf',function(nVal,oVal){
            conf = nVal;
            scope.pageListFn();
        })

        scope.next = function(){
            if(conf.currentPage >= conf.count) return ;
            conf.currentPage += 1;
            scope.pageListFn();
            conf.onChange();
        }
        scope.prev = function(){
            if(conf.currentPage <= 1) return ;
            conf.currentPage -= 1;
            scope.pageListFn();
            conf.onChange();
        }
        scope.sPage = function(e){
            conf.currentPage = e;
            scope.pageListFn();
            conf.onChange();
        }

        scope.pageListFn = function(){
            conf.count = Number(conf.count)
            conf.currentPage = Number(conf.currentPage)
            conf.pageList = [];
            conf.selectArr = [];
            if(conf.count > 6){
                if(conf.currentPage < 5){
                    for(var i=1;i<=5;i++){
                        conf.pageList.push(i)
                    }
                    conf.pageList.push('...')
                }else if(conf.currentPage >= conf.count - 3){
                    for(var i=conf.count - 3;i<=conf.count;i++){
                        conf.pageList.push(i)
                    }
                    conf.pageList.unshift('...')
                }else{
                    for(var i= conf.currentPage -2 ; i<= conf.currentPage + 2 ; i++){
                        conf.pageList.push(i);
                    }
                    conf.pageList.push('...');
                    conf.pageList.unshift('...');
                }
            }else{
                for(var i=1;i<= conf.count;i++){
                    conf.pageList.push(i)
                }
            }
            for(var i=1;i<=conf.count;i++){
                conf.selectArr.push(i)
            }
        }
      }
    }
})


var zdyAlert = {
    init:function(e,callback){
        var html = '<div class="alertcen"></div>';
        html += '<div class="alertnei box-show"><p class="title">提示</p><div class="alert-con">'+ e +'</div><div class="alert-btn">确定</div></div>';

        $('body').append(html);
        $('.alert-btn').click(function(){
            $('.alertcen,.alertnei').remove();
            callback();
        })
    }
}



var zdycomfig = {
        init:function(e,callback){
        var html = '<div class="alertcen-ff"></div>';
        html += '<div class="alertnei-new box-show"><p class="title">提示</p><div class="alert-con">'+ e +'</div><div class="two-btn"><div class="queren">确定</div><div class="close">取消</div></div></div>';
        $('body').append(html);
        $('.close').click(function(){
            $('.alertcen-ff,.alertnei-new').remove();
        })
        $('.queren').click(function(){
            callback();
        })
    }
}
function jump(){
    zdyAlert.init('请登录',function(){
        window.location.href="login";
    })
}


