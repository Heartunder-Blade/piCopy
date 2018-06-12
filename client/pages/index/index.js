//index.js
var qcloud = require('../../vendor/wafer2-client-sdk/index')
var config = require('../../config')
var util = require('../../utils/util.js')

Page( {
    data: {
        identity:null,
        canIUse: wx.canIUse('button.open-type.getUserInfo'),
        userLoading:false,
        keeperLoading:false,
        userDisable:false,
        keeperDisable:false,
        takeSession:true
    },
    getUserinfo:function(){
        util.showBusy('正在登录')
        var that = this
        // 调用登录接口
        var loginUrl=config.service.loginUrl+this.data.identity
        var redirectUrl = "/pages/" + this.data.identity + "/" + this.data.identity

        if(this.data.identity=="keeper")
            redirectUrl="/pages/" + this.data.identity+"/shopManage/shopManage"
        //当写完商家信息页面之后去掉该跳转
        
            qcloud.request({
            url: loginUrl,
            login: true,
            success(result) {
                util.showSuccess('登录成功')
                console.log(result)
                getApp().globalData.userInfo=result.data.data
                if(result.data.isNewKeeper){
                    var navigateUrl="/pages/keeper/keeperRegist/keeperRegist"
                    wx.navigateTo({
                        url:navigateUrl
                    })
                }
                else{
                    wx.redirectTo({
                        url: redirectUrl 
                    })
                }
            },
            fail(error) {
                util.showModel('请求失败', error)
                console.log('request fail', error)
            }
        })
    },


    onclick:function(event){
        this.setData({
            identity:event.currentTarget.dataset.identity,
        }) 
        if (this.data.identity=="user"){
            this.setData({
                userLoading:true,
                keeperDisable:true
            })
        }
        else
        {
            this.setData({
                userDisable:false,
                keeperLoading:false
            })
        }
        this.getUserinfo()
        
        
    },
    onHide:function(){
        this.setData({
            userDisable:false,
            userLoading:false,
            keeperDisable:false,
            keeperLoading:false,
        })

    },
})