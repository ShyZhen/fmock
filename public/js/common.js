/**
 * Toast 组件
 */
var $Toast = {

    // 隐藏的 setTimeOut 引用
    hideTimeOut: null,
    toastNode: null,

    /**
     * 初始化
     */
    init: function () {
        if (!this.toastNode) {
            this.toastNode = document.createElement('section');
            this.toastNode.innerHTML =
                '<i class="iconfont iconcheck" hidden></i><i class="iconfont iconerror" hidden></i>&nbsp;<span class="toast-text"></span>';
            this.toastNode.id = 'toastWaka';               // 设置id，一个页面有且仅有一个Toast
            this.toastNode.setAttribute('class', 'toast'); // 设置类名
            this.toastNode.style.display = 'none';         // 设置隐藏
            document.body.appendChild(this.toastNode);
        }
    },

    /**
     * show
     * @param text
     * @param type
     * @param duration
     */
    show: function (text, type, duration) {
        if (!this.toastNode) {
            this.init()
            this.process(text, type, duration)
        }
        this.process(text, type, duration)
    },

    /**
     * 显示Toast
     * @param text 文本内容
     * @param type 类型 success error
     * @param duration 持续时间
     */
    process: function (text, type, duration) {
        // 防止重复点击
        if (this.hideTimeOut) {
            return;
        }
        if (!text) {
            console.error('text 不能为空!');
            return;
        }

        var domIconSuccess = this.toastNode.querySelector(".iconcheck"); // 成功图标
        var domIconError = this.toastNode.querySelector(".iconerror"); // 错误图标
        var domToastText = this.toastNode.querySelector(".toast-text"); // 文字
        domToastText.innerHTML = text || '';
        switch (type) {
            case 'success':
                this.fadeIn(domIconSuccess, 'inline')
                this.fadeOut(domIconError)
                break;
            case 'error':
                this.fadeOut(domIconSuccess)
                this.fadeIn(domIconError, 'inline')
                break;
            default:
                this.fadeOut(domIconSuccess)
                this.fadeOut(domIconError)
                break;
        }
        this.fadeIn(this.toastNode, 'block')

        var that = this;
        this.hideTimeOut = setTimeout(function () {
            that.fadeOut(that.toastNode)
            that.hideTimeOut = null;
        }, duration || 2500);
    },

    /**
     * 强制隐藏 Toast （不等失效时间结束）
     */
    hide: function () {
        // 如果 TimeOut 存在
        if (this.hideTimeOut) {
            // 清空 TimeOut 引用
            clearTimeout(this.hideTimeOut);
            this.hideTimeOut = null;
        }
        if (this.toastNode) {
            this.fadeOut(this.toastNode)
        }
    },

    /**
     * 淡入
     * @param el
     * @param display
     */
    fadeIn: function (el, display) {
        el.style.opacity = 0;
        el.style.display = display || 'block';

        (function fade() {
            var val = parseFloat(el.style.opacity);
            if (!((val += .1) > 1)) {
                el.style.opacity = val;
                requestAnimationFrame(fade);
            }
        })();
    },

    /**
     * 淡出
     * @param el
     */
    fadeOut: function (el) {
        el.style.opacity = 1;
        (function fade() {
            if ((el.style.opacity -= .1) < 0) {
                el.style.display = 'none';
            } else {
                requestAnimationFrame(fade);
            }
        })();
    }
};

/**
 * 全局辅助函数
 * @type {{getCsrfToken: {getCsrfToken: (function(): string)}}}
 */
var $Global = {

    /**
     * 获取设置在meta上的csrfToken
     * @type {{getCsrfToken: (function(): string)}}
     */
    getCsrfToken: function() {
        return document.querySelector('#csrfToken').getAttribute('content')
    }
}

/**
 * 所有需要提前加载的、不需要触发的函数
 * @type {{init: $FMock.init, headerInit: $FMock.headerInit}}
 */
var $FMock = {
    // 所有项目初始化时需要默认加载的方法
    init: function() {
        this.headerInit()
    },

    // 处理header的active状态
    headerInit: function() {
        $("#app-navbar-collapse #app-left-nav").find("li").each(function () {
            var a = $(this).find("a:first")[0];
            if ($(a).attr('href') === location.pathname) {
                $(this).addClass('active');
            } else {
                $(this).removeClass('active');
            }
        });
    }
}