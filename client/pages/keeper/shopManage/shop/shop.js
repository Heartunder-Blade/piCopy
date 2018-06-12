// pages/keeper/shopManage/shop/shop.js
var config = require("../../../../config")
Page({

  /**
   * 页面的初始数据
   */
  data: {
    shop: null,
    isEdit: false,
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    var that = this
    wx.request({
      url: config.service.shopUrl + "getShop/" + options.shopId,
      success: function (res) {
        that.setData({
          'shop': res.data.row,
          'isEdit': false
        })
      }
    })

  },

  /**
   * 生命周期函数--监听页面初次渲染完成
   */
  onReady: function () {

  },

  /**
   * 生命周期函数--监听页面显示
   */
  onShow: function () {

  },

  /**
   * 生命周期函数--监听页面隐藏
   */
  onHide: function () {

  },

  /**
   * 生命周期函数--监听页面卸载
   */
  onUnload: function () {

  },

  /**
   * 页面相关事件处理函数--监听用户下拉动作
   */
  onPullDownRefresh: function () {

  },

  /**
   * 页面上拉触底事件的处理函数
   */
  onReachBottom: function () {

  },

  /**
   * 用户点击右上角分享
   */
  onShareAppMessage: function () {

  },
  priceInput: function (event) {
    var reg = /^(([1-9]+)|([0-9]+\.[0-9]{1,2}))$/;
    var result
    var price = event.detail.value
    if (reg.test(price)) {
      result = "inCorrect"
    }
    else {
      result = "inMismatch"
      price = 0
    }
    this.setData({
      "priceInput": result,
      "price": price
    })
    console.log(event.detail.value)

  },

  submit: function (event) {
    
    var url = config.service.shopUrl + "shopUpdata"
    var that = this
    var form = event.detail.value
    console.log(form)
    wx.showModal({
      title: '确认',
      content: '是否更新信息',
      success: function (res) {
        if (res.confirm) {
          wx.request({
            url: url,
            data: form,
            method: "POST",
            header: {
              "Content-Type": "application/x-www-form-urlencoded"
            },
            success: function (res) {
              console.log(res)
              if (res.data.code == 0) {
                //提示修改成功
                that.setData({
                  'shop.page_price':form.price,
                  'shop.name':form.shopName,
                  'isEdit': false
                })
              }
              else {
                //提示更新失败
              }
            }
          })
        } else if (res.cancel) {
          console.log('用户点击取消')
        }
      }
    })


  },

  editInfo: function () {
    this.setData({
      "isEdit": true
    })
  },

  shopDelete: function (event) {
    var shopId = event.currentTarget.dataset.shopid
    var url = config.service.shopUrl + "shopDelete/" + shopId
    wx.showModal({
      title: "删除确认",
      content: "是否删除商店",
      success: function (res) {
        if (res.confirm) {
          wx.request({
            url: url,
            success(result) {
              if (result.data.code == 0) {
                wx.navigateBack()
              }
              else {
                //提示删除失败
              }
            }
          })
        } else if (res.cancel) {
          console.log('用户点击取消')
        }
      }
    })

    console.log(shopId)

  },
  addPrinter: function (event) {
    var shopId = event.currentTarget.dataset.shopid
    console.log(shopId)
    var url = "printerManage/printerManage?shopId=" + shopId
    wx.navigateTo({
      url: url
    })
  },
  back: function () {
    wx.navigateBack()
  }
})