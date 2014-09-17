/*
 * eien web js framework core code
 * @author WaiTing
 * @version 0.0.44
 */
// 判断浏览器类型
var userAgent = navigator.userAgent.toLowerCase();
var is_opera = userAgent.indexOf('opera') != -1 && opera.version();
var is_moz = (navigator.product == 'Gecko') && userAgent.substr(userAgent.indexOf('firefox') + 8, 3);
var is_ie = (userAgent.indexOf('msie') != -1 && !is_opera) && userAgent.substr(userAgent.indexOf('msie') + 5, 3);
var is_mac = userAgent.indexOf('mac') != -1;

// 修正Gecko DOM原型
if(is_moz && window.HTMLElement) {
	HTMLElement.prototype.__defineSetter__('outerHTML', function(sHTML) {
		var r = this.ownerDocument.createRange();
		r.setStartBefore(this);
		var df = r.createContextualFragment(sHTML);
		this.parentNode.replaceChild(df,this);
		return sHTML;
	});

	HTMLElement.prototype.__defineGetter__('outerHTML', function() {
		var attr;
		var attrs = this.attributes;
		var str = '<' + this.tagName.toLowerCase();
		for(var i = 0;i < attrs.length;i++){
			attr = attrs[i];
			if(attr.specified)
			str += ' ' + attr.name + '="' + attr.value + '"';
		}
		if(!this.canHaveChildren) {
			return str + ' />';
		}
		return str + '>' + this.innerHTML + '</' + this.tagName.toLowerCase() + '>';
	});

	HTMLElement.prototype.__defineGetter__('canHaveChildren', function() {
		switch(this.tagName.toLowerCase()) {
		case 'area':case 'base':case 'basefont':case 'col':case 'frame':
		case 'hr':case 'img':case 'br':case 'input':case 'isindex':
		case 'link':case 'meta':case 'param':
		return false;
		}
		return true;
	});
	HTMLElement.prototype.click = function(){
		var evt = this.ownerDocument.createEvent('MouseEvents');
		evt.initMouseEvent('click', true, true, this.ownerDocument.defaultView, 1, 0, 0, 0, 0, false, false, false, false, 0, null);
		this.dispatchEvent(evt);
	}
}
// 显示时间串
function showTimeStr(t)
{
	var res = 0;
	var str = '';
	if (t)
	{
		res = Math.floor(t / 86400);
		t %= 86400;
		res && (str += res + '天');
	}
	if (t)
	{
		res = Math.floor(t / 3600);
		t %= 3600;
		res && (str += res + '小时');
	}
	if (t)
	{
		res = Math.floor(t / 60);
		t %= 60;
		res && (str += res + '分');
	}
	if (t)
	{
		res = t;
		res && (str += res + '秒');
	}
	
	str || (str = '0秒');
	return str;
}
// {key:value}转到uri串
function uri(obj)
{
	var flag = false;
	var str = '';
	for (var k in obj)
	{
		if (flag)
		{
			str += '&';
		}
		str += encodeURIComponent(k) + '=' + encodeURIComponent(obj[k]);
		flag = true;
	}
	return str;
}
/*字节大小*/
function byteSize(size)
{
	if (size < 1024)
	{
		return String(size) + 'B';
	}
	else if (size < 1024 * 1024)
	{
		return String(Math.ceil(size * 100 / 1024) / 100) + 'KB';
	}
	else
	{
		return String(Math.ceil(size * 100 / (1024 * 1024)) / 100) + 'MB';
	}
}
// COOKIE操作函数
// 添加
// void setCookie(string sName ,string sValue [,int expires]);
function setCookie(sName, sValue)
{
	var cookieStr = sName + "=" + escape(sValue) + "; ";
	if(arguments[2]){
		var expiresSec = arguments[2];
		var date = new Date();
		var newDate = new Date(date.valueOf() + expiresSec*1000);
		cookieStr += "expires=" + newDate.toGMTString() + "; ";
	}
	//alert(cookieStr);
	document.cookie = cookieStr;
}
// 获取
// string getCookie(string sName);
function getCookie(sName)
{
	// cookies are separated by semicolons
	var aCookie = document.cookie.split("; ");
	for (var i=0; i < aCookie.length; i++)
	{
		// a name/value pair (a crumb) is separated by an equal sign
		var aCrumb = aCookie[i].split("=");
		if (sName == aCrumb[0])
		return unescape(aCrumb[1]);
	}
	// a cookie with the requested name does not exist
	return null;
}
// Delete the cookie with the specified name.
// void delCookie(string sName [,string sValue]);
function delCookie(sName)
{
	var sValue = arguments[1] ? arguments[1] : '';
	document.cookie = sName + "=" + escape(sValue) + "; expires=Fri, 31 Dec 1999 23:59:59 GMT; ";
}
// 设置首页
function setIndexPage(obj, url)
{
	if (is_ie)
	{
		obj.style.behavior='url(#default#homepage)';
		obj.setHomePage(url);
	}
	else if (is_moz)
	{
		if(window.netscape)
		{
			try
			{
				netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
			}
			catch(e)
			{
				alert("用户取消了此操作，或者浏览器拒绝此操作！\r\n请在浏览器地址栏输入“about:config”并回车。\r\n然后将[signed.applets.codebase_principal_support]设置为true。");
				return;
			}
		}
		else
		{
			alert('NetScape对象无效！');
			return;
		}
		var prefs = Components.classes['@mozilla.org/preferences-service;1'].getService(Components.interfaces.nsIPrefBranch);
		if (window.confirm('是否将“' + url + '”设为主页?'))
		{
			prefs.setCharPref('browser.startup.homepage', url);
		}
	}
	else
	{
		alert('对不起，本站暂不支持您所用浏览器的这个操作！');
	}
}
// 加入收藏
function addFavorite(url, title)
{
	title = title != undefined ? title : '';
	if (is_ie)
	{
		window.external.AddFavorite(url, title);
	}
	else if (is_moz)
	{
		window.sidebar.addPanel(title, url, "");
	}
	else
	{
		alert('对不起，本站暂不支持您所用浏览器的这个操作！');
	}
}
// 全选
// void selectAll(CheckBox opr, String id);
// opr : 控制器
// id : id前缀
function selectAll(opr, id)
{
	var oChk;
	var checked = opr.checked != undefined ? opr.checked : false;
	for (var i = 0; oChk = $(id + i); i++)
	{
		oChk.checked = checked;
		if (oChk.onclick) oChk.onclick();
	}
}
// 控制一个HTML对象的可见性
// boolean elementVisible(HTMLElement obj[, String value = 'auto'[, String property = 'display']]);
function elementVisible(obj, value, property)
{
	value = value != undefined ? value : 'auto';
	property = property != undefined ? property : 'display';
	var visible;
	try
	{
		switch(property)
		{
		case 'display':
			if (value == 'auto')
			{
				visible = obj.style.display == 'none';
				value = visible ? '' : 'none';
			}
			obj.style.display = value;
			break;
		case 'visibility':
			if (value == 'auto')
			{
				visible = obj.style.visibility == 'hidden';
				value = visible ? '' : 'hidden';
			}
			obj.style.visibility = value;
			break;
		}
	}
	catch(e)
	{
		alert(e.description);
	}
	return visible;
}

// 通过ID获取元素对象
function $(id)
{
	return document.getElementById(id);
}
// 通过TAG名称获取元素对象
function _(tag)
{
	return document.getElementsByTagName(tag);
}
// 通过类名查找元素
function c(clsname)
{
	var elems = [];
	raw_cls_elem(elems, document.body, clsname);
	return elems;
}
function raw_cls_elem(arr, elem, name)
{
	var clsname = elem.className ? elem.className : '';
	if (clsname.match(new RegExp('\\b' + name + '\\b'))) arr.push(elem);
	for (var i = 0; i < elem.childNodes.length; i++)
		raw_cls_elem(arr, elem.childNodes[i], name);
}
// 核心函数
var eien = function(elem)
{
	var e = null;
	// 超简易选择器
	if (typeof elem == 'string')
	{
		if (elem.length > 1 && elem.charAt(0) == '#')
		{
			e = $(elem.substr(1));
		}
		else if (elem.length > 1 && elem.charAt(0) == '.')
		{
			e = c(elem.substr(1));
		}
		else if (elem.length > 0)
		{
			e = _(elem);
		}
	}
	else if (typeof elem == 'object')
	{
		e = elem;
	}
	return new eienElement(e);
}

// 浏览器类型
eien.browser = null;
eien.version = null;
if (is_opera)
{
	eien.browser = 'opera';
	eien.version = is_opera;
}
else if (is_moz)
{
	eien.browser = 'moz';
	eien.version = is_moz;
}
else if (is_ie)
{
	eien.browser = 'ie';
	eien.version = is_ie;
}
else if (is_mac)
{
	eien.browser = 'mac';
	eien.version = is_mac;
}
else
{
	eien.browser = 'unknown';
	eien.version = 'unknown';
}

// page load complete.
// 加载事件回调函数数组
eien.ready_callbacks = new Array();
// 加载完后的准备事件
eien.ready = function(callback)
{
	eien.ready_callbacks.push(callback);
}
// 打开新窗口的连接
eien.ready(function()
{
	var anchors = [];
	if (document.getElementsByTagName)
	{
		anchors = document.getElementsByTagName("a");
	}
	for (var i = 0; i < anchors.length; i++)
	{
		var anchor = anchors[i];
		if (anchor.getAttribute("href") && anchor.getAttribute("rel") == "_blank")
		{
			anchor.target = "_blank";
		}
	}
});

// 绑定页面加载事件
window.onload = function()
{
	for (var k in eien.ready_callbacks)
	{
		eien.ready_callbacks[k].apply(window);
	}
}

// 异步循环
/*[{
id:TIMER ID,代表循环
i:次数,从0开始
callback: boolean Context.callback(object obj);循环回调函数,this指向Context对象,返回false停止
obj:传递到callback的对象参数
stop:是否已经停止
}]*/
eien.loopContext = []; // Context数组
//分配一个循环索引
eien.getLoopIndex = function()
{
	var k = 0;
	for (k = 0; k < eien.loopContext.length; k++)
	{
		if (eien.loopContext[k].stop)
		{
			return k;
		}
	}
	return eien.loopContext.length;
}
// 异步循环回调
function eien_loop_callback(index)
{
	try
	{
		var cx = eien.loopContext[index];
		var ret = cx.callback(cx.obj);
		cx.i++;
		ret == undefined && (ret = false);
		if (!ret)
		{
			cx.stop = true;
			window.clearInterval(cx.id);
		}
	}
	catch (e)
	{
		alert(e.description);
	}
}
// 启动循环
eien.loop = function(interval, callback, obj)
{
	try
	{
		obj = obj == undefined ? null : obj;
		var i = eien.getLoopIndex(); // 获得索引
		eien.loopContext[i] = {"id":0, "i":0, "callback":callback, "obj":obj, "stop":false};
		eien.loopContext[i].id = window.setInterval("eien_loop_callback(" + i + ");", interval);
	}
	catch (e)
	{
		alert(e.description);
	}
}

//********************************************************************************
// eien元素类构造函数
function eienElement(e)
{
	this.e = null;
	this.ecoll = null;
	if (e && e.length === undefined)
	{
		this.e = e;
		this.length = 1;
	}
	else if (e && e.length == 1)
	{
		this.e = e[0];
		this.length = 1;
	}
	else if (e)
	{
		this.ecoll = e;
		this.length = e.length;
	}
}
eienElement.prototype.toString = function()
{
	return '[eienElement Object]\r\n{\r\n' +
	'    length:' + this.length + '\r\n' +
	'    element:[' + this.foreach(function(){return this.e.tagName;}) + ']\r\n' +
	'}';
}
// 遍历
// 回调函数的THIS环境为每个HTML元素的eien元素引用
eienElement.prototype.foreach = function(callback)
{
	if (this.e)
	{
		return callback.apply(this);
	}
	else if (this.ecoll)
	{
		var rets = [];
		for (var i = 0; i < this.ecoll.length; i++)
			rets.push(callback.apply(new eienElement(this.ecoll[i])));
		return rets;
	}
}
// eienElement对象的操作 ------------------------------------------
// 设置透明度
/*.setOpacity {
	opacity: .75; 标准: Firefox 1.5 以上, Opera, Safari 
	filter: alpha(opacity=75);  IE 8 以下 
	-ms-filter: "alpha(opacity=75)";  IE 8 
	-khtml-opacity: .75;  Safari 1.x 
	-moz-opacity: .75;  FF lt 1.5, Netscape
}*/
eienElement.prototype.opacity = function(value)
{
	this.foreach(function()
	{
		if (is_ie)
		{
			this.e.style.filter = 'Alpha(Opacity=' + value + ')';
		}
		else
		{
			var v = value / 100;
			this.e.style.opacity = v;
		}
		this.width(this.width());
	});
	return this;
}
/*// 淡入
eienElement.prototype.fadeIn(startOpacity, time)
{
	var interval = 100;
	var n = Math.ceil(time / interval);
	var step = (100 - startOpacity) / n;

	var tid = window.setInterval('fadeIn_step();', interval);
	function fadeIn_step()
	{
		//this.
	}
}
// 淡出
eienElement.prototype.fadeOut(startOpacity, time)
{
}*/
function getUnitNumber(val)
{
	var n = parseInt(val);
	return isNaN(n) ? 0 : n;
}
// width
eienElement.prototype.width = function(w)
{
	try
	{
		var ret = this.foreach(function()
		{
			if (w == undefined)
			{
				return this.e.offsetWidth;
			}
			else
			{
				// padding border
				var paddingWidth = getUnitNumber(this.style('paddingLeft')) + getUnitNumber(this.style('paddingRight'));
				var borderWidth = getUnitNumber(this.style('borderLeftWidth')) + getUnitNumber(this.style('borderRightWidth'));
				// 宽度包含padding,border,计算剩余宽度
				w = w - paddingWidth - borderWidth;
				w < 0 && (w = 0);
				this.style('width', w + 'px');
			}
		});
		return w == undefined ? ret : this;
	}
	catch(e)
	{
		window.alert(e.description);
	}
}
// height
eienElement.prototype.height = function(h)
{
	try
	{
		var ret = this.foreach(function()
		{
			if (h == undefined)
			{
				return this.e.offsetHeight;
			}
			else
			{
				// padding border
				var paddingHeight = getUnitNumber(this.style('paddingTop')) + getUnitNumber(this.style('paddingBottom'));
				var borderHeight = getUnitNumber(this.style('borderTopWidth')) + getUnitNumber(this.style('borderBottomWidth'));
				// 包含padding,border
				h = h - paddingHeight - borderHeight;
				h < 0 && (h = 0);
				this.style('height', h + 'px');
			}
		});
		return h == undefined ? ret : this;
	}
	catch(e)
	{
		window.alert(e.description);
	}
}
// left
eienElement.prototype.left = function(l)
{
	try
	{
		var ret = this.foreach(function()
		{
			var pleft = 0;
			var p = this.e.offsetParent;
			while (p)
			{
				pleft += p.offsetLeft;
				p = p.offsetParent;
			}
			if (l == undefined)
			{
				return pleft + this.e.offsetLeft;
			}
			else
			{
				l = l - pleft;
				this.style('left', l + 'px');
			}
		});
		return l == undefined ? ret : this;
	}
	catch(e)
	{
		window.alert(e.description);
	}
}
// top
eienElement.prototype.top = function(t)
{
	try
	{
		var ret = this.foreach(function()
		{
			var ptop = 0;
			var p = this.e.offsetParent;
			while (p)
			{
				ptop += p.offsetTop;
				p = p.offsetParent;
			}
			if (t == undefined)
			{
				return ptop + this.e.offsetTop;
			}
			else
			{
				t = t - ptop;
				this.style('top', t + 'px');
			}
		});
		return t == undefined ? ret : this;
	}
	catch(e)
	{
		alert(e.description);
	}
}
// style
eienElement.prototype.style = function(attr, val)
{
	try
	{
		var ret = this.foreach(function()
		{
			if (val == undefined)
			{
				if (is_ie)
				{
					if (attr == undefined)
						return this.e.currentStyle;
					else
						return this.e.currentStyle[attr];
				}
				else
				{
					if (attr == undefined)
						return getComputedStyle(this.e, null);
					else
						return getComputedStyle(this.e, null)[attr];
				}
			}
			else
			{
				if (attr == 'float')
				{
					if (is_ie)
					{
						attr = 'styleFloat';
					}
					else
					{
						attr = 'cssFloat';
					}
				}
				this.e.style[attr] = val;
			}
		});
		return val == undefined ? ret : this;
	}
	catch(e)
	{
		alert(e.description);
	}
	return null;
}
// 内部html内容
eienElement.prototype.inner = function(html)
{
	var ret = this.foreach(function()
	{
		if (html == undefined)
		{
			return this.e.innerHTML;
		}
		else
		{
			this.e.innerHTML = html;
		}
	});
	return html == undefined ? ret : this;
}