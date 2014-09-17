/*
 * eien web js framework core code
 * @author WaiTing
 * @version 0.1.0 */

// 核心函数
var eien = function( elem )
{
	var e = null;
	// 超简易选择器
	if ( typeof elem == 'string' )
	{
		if ( elem.length > 1 && elem.charAt(0) == '#' )
		{
			e = _byId( elem.substr(1) );
		}
		else if ( elem.length > 1 && elem.charAt(0) == '.' )
		{
			e = _byClass(elem.substr(1));
		}
		else if ( elem.length > 0 )
		{
			e = _byTag(elem);
		}
	}
	else if ( typeof elem == 'object' )
	{
		e = elem;
	}
	return new eienElement(e);
}

// 判断浏览器类型
eien.browser = {
	ver: '',
	name: 'Unknown',
	toString: function()
	{
		/*var r;
		if ( this.ie )
			r = this.ie;
		else if ( this.firefox )
			r = this.firefox;
		else if ( this.opera )
			r = this.opera;
		else if ( this.chrome )
			r = this.chrome;
		else if ( this.safari )
			r = this.safari;
		else
			r = 'Unknown';
		return r + ( this.ver ? ' ' + this.ver : '' );*/
		return this.name + ( this.ver ? ' ' + this.ver : '' );
	}
};
( function () {
	var ua = navigator.userAgent.toLowerCase();
	var s;

	if ( s = ua.match(/msie ([\d\.]+)/) ) // ie5.5 ~ ie10
	{
		eien.browser.ie = 'IE'
		eien.browser.name = 'Internet Explorer';
		eien.browser.ver = s[1];
	}
	else if ( ua.match(/trident/) && ( s = ua.match(/rv.?([\d\.]+)/) ) ) // ie11
	{
		eien.browser.ie = 'IE';
		eien.browser.name = 'Internet Explorer';
		eien.browser.ver = s[1];
	}
	else if ( s = ua.match(/firefox\/([\d\.]+)/) ) // firefox
	{
		eien.browser.firefox = 'Firefox';
		eien.browser.name = 'Firefox';
		eien.browser.ver = s[1];
	}
	else if ( s = ua.match(/opera.([\d\.]+)/) )
	{
		var s2;
		if ( s2 = ua.match(/version\/([\d\.]+)/) )
			s = s2;
		eien.browser.opera = 'Opera';
		eien.browser.name = 'Opera';
		eien.browser.ver = s[1];
	}
	else if ( s = ua.match(/opr.([\d\.]+)/) )
	{
		eien.browser.opera = 'Opera';
		eien.browser.name = 'Opera';
		eien.browser.ver = s[1];
	}
	else if ( s = ua.match(/chrome\/([\d\.]+)/) )
	{
		eien.browser.chrome = 'Chrome';
		eien.browser.name = 'Chrome';
		eien.browser.ver = s[1];
	}
	else if ( s = ua.match(/version\/([\d\.]+).*safari/) )
	{
		eien.browser.safari = 'Safari';
		eien.browser.name = 'Safari';
		eien.browser.ver = s[1];
	}
})();

// 修正Gecko DOM原型
if ( eien.browser.firefox && window.HTMLElement )
{
	HTMLElement.prototype.__defineSetter__(
		'outerHTML',
		function( sHTML )
		{
			var r = this.ownerDocument.createRange();
			r.setStartBefore(this);
			var df = r.createContextualFragment(sHTML);
			this.parentNode.replaceChild(df,this);
			return sHTML;
		}
	);

	HTMLElement.prototype.__defineGetter__(
		'outerHTML',
		function()
		{
			var attr;
			var attrs = this.attributes;
			var str = '<' + this.tagName.toLowerCase();
			for ( var i = 0; i < attrs.length; i++ )
			{
				attr = attrs[i];
				if ( attr.specified )
				str += ' ' + attr.name + '="' + attr.value + '"';
			}
			if ( !this.canHaveChildren )
				return str + ' />';

			return str + '>' + this.innerHTML + '</' + this.tagName.toLowerCase() + '>';
		}
	);

	HTMLElement.prototype.__defineGetter__(
		'canHaveChildren',
		function()
		{
			switch ( this.tagName.toLowerCase() )
			{
			case 'area':case 'base':case 'basefont':case 'col':case 'frame':
			case 'hr':case 'img':case 'br':case 'input':case 'isindex':
			case 'link':case 'meta':case 'param':
				return false;
			}
			return true;
		}
	);

	HTMLElement.prototype.click = function()
	{
		var evt = this.ownerDocument.createEvent('MouseEvents');
		evt.initMouseEvent( 'click', true, true, this.ownerDocument.defaultView, 1, 0, 0, 0, 0, false, false, false, false, 0, null );
		this.dispatchEvent(evt);
	}
}

// 通过ID获取元素对象
function _byId( id )
{
	return document.getElementById(id);
}
// 通过TAG名称获取元素对象
function _byTag( tag, parent )
{
	parent = parent != undefined ? parent : document;
	return parent.getElementsByTagName(tag);
}
// 通过类名查找元素
function _byClass( clsname, parent )
{
	parent = parent != undefined ? parent : document.body;
	var elems = [];
	__rawByClass( elems, parent, clsname );
	return elems;
}

function __rawByClass( arr, elem, name )
{
	var clsname = elem.className ? elem.className : '';
	if ( clsname.match( new RegExp( '\\b' + name + '\\b' ) ) )
		arr.push(elem);
	for ( var i = 0; i < elem.childNodes.length; i++ )
		__rawByClass( arr, elem.childNodes[i], name );
}

// 加载完成事件处理器数组
eien._readyHandlers = new Array();
// 加载完后的准备事件
eien.ready = function( callback )
{
	eien._readyHandlers.push(callback);
}

// 打开新窗口的连接
eien.ready(
	function()
	{
		var anchors = [];
		anchors = document.getElementsByTagName("a");

		for ( var i = 0; i < anchors.length; i++ )
		{
			var anchor = anchors[i];
			if ( anchor.getAttribute("href") && anchor.getAttribute("rel") == "_blank" )
			{
				anchor.target = "_blank";
			}
		}
	}
);

// 绑定页面加载事件
window.onload = function()
{
	for ( var k in eien._readyHandlers )
	{
		eien._readyHandlers[k].apply(window);
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
	for ( k = 0; k < eien.loopContext.length; k++ )
	{
		if ( eien.loopContext[k].stop )
		{
			return k;
		}
	}
	return eien.loopContext.length;
}

// 异步循环回调
function eien_loop_callback( index )
{
	try
	{
		var cx = eien.loopContext[index];
		var ret = cx.callback(cx.obj);
		cx.i++;
		ret == undefined && ( ret = false );
		if ( !ret )
		{
			cx.stop = true;
			window.clearInterval(cx.id);
		}
	}
	catch ( e )
	{
		alert(e.description);
	}
}

// 启动循环
eien.loop = function( interval, callback, obj )
{
	try
	{
		obj = obj == undefined ? null : obj;
		var i = eien.getLoopIndex(); // 获得索引
		eien.loopContext[i] = { "id":0, "i":0, "callback":callback, "obj":obj, "stop":false };
		eien.loopContext[i].id = window.setInterval( "eien_loop_callback(" + i + ");", interval );
	}
	catch ( e )
	{
		alert(e.description);
	}
}

//********************************************************************************
// eien元素类构造函数
// e:html元素引用或html元素引用数组
function eienElement( e )
{
	this.e = null; // html元素
	this.ecoll = null; // html元素集合
	if ( e && e.length == undefined ) // 传入的是一个html元素引用
	{
		this.e = e;
		this.length = 1;
	}
	else if ( e && e.length == 1 ) // 传入的是只有一个元素的html元素引用数组
	{
		this.e = e[0];
		this.length = 1;
	}
	else if ( e ) // 传入一个html元素引用数组
	{
		this.ecoll = e;
		this.length = e.length;
	}
}

// 转成字符串显示
eienElement.prototype.toString = function()
{
	return '[eienElement Object]\n{\n' +
		'    length:' + this.length + '\n' +
		'    element:[' + this.foreach(function(){return this.e.tagName.toLowerCase();}) + ']\n' +
		'}';
}

// 遍历eienElement选取的每个html元素，并通过callback执行给定操作，返回callback的返回值。若是多个元素则返回返回值的数组。
// callback中的this引用为每个HTML元素的eienElement引用
eienElement.prototype.foreach = function( callback )
{
	if ( this.e )
	{
		return callback.apply(this);
	}
	else if ( this.ecoll )
	{
		var rets = [];
		for ( var i = 0; i < this.ecoll.length; i++ )
			rets.push( callback.apply( new eienElement( this.ecoll[i] ) ) );
		return rets;
	}
}

// eienElement对象的操作 ------------------------------------------
// 设置透明度，返回eienElement的引用以便后续操作
/*.setOpacity {
	opacity: .75; 标准: Firefox 1.5 以上, Opera, Safari 
	filter: alpha(opacity=75);  IE 8 以下 
	-ms-filter: "alpha(opacity=75)";  IE 8 
	-khtml-opacity: .75;  Safari 1.x 
	-moz-opacity: .75;  FF lt 1.5, Netscape
}*/
eienElement.prototype.opacity = function( value )
{
	this.foreach( function()
	{
		if ( eien.browser.ie )
		{
			this.e.style.filter = 'Alpha(Opacity=' + value + ')';
		}
		else
		{
			var v = value / 100;
			this.e.style.opacity = v;
		}
		this.width( this.width() );
	} );
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

// 设置或获取宽度。
// w不指定则为获取宽度，返回宽度；w指定则为设置宽度，返回eienElement。
eienElement.prototype.width = function( w )
{
	try
	{
		var ret = this.foreach( function()
		{
			if ( w == undefined )
			{
				return this.e.offsetWidth;
			}
			else
			{
				// padding border
				var paddingWidth = eien.utils.getUnitNumber( this.style('paddingLeft') ) + eien.utils.getUnitNumber( this.style('paddingRight') );
				var borderWidth = eien.utils.getUnitNumber( this.style('borderLeftWidth') ) + eien.utils.getUnitNumber( this.style('borderRightWidth') );
				// 宽度包含padding,border,计算剩余宽度
				w = w - paddingWidth - borderWidth;
				w < 0 && ( w = 0 );
				this.style( 'width', w + 'px' );
			}
		} );
		return w == undefined ? ret : this;
	}
	catch ( e )
	{
		alert(e.description);
	}
}

// 设置或获取高度
// h不指定则为获取高度，返回高度；h指定则为设置高度，返回eienElement。
eienElement.prototype.height = function( h )
{
	try
	{
		var ret = this.foreach( function()
		{
			if ( h == undefined )
			{
				return this.e.offsetHeight;
			}
			else
			{
				// padding border
				var paddingHeight = eien.utils.getUnitNumber( this.style('paddingTop') ) + eien.utils.getUnitNumber( this.style('paddingBottom') );
				var borderHeight = eien.utils.getUnitNumber( this.style('borderTopWidth') ) + eien.utils.getUnitNumber( this.style('borderBottomWidth') );
				// 包含padding,border
				h = h - paddingHeight - borderHeight;
				h < 0 && ( h = 0 );
				this.style( 'height', h + 'px' );
			}
		} );
		return h == undefined ? ret : this;
	}
	catch(e)
	{
		alert(e.description);
	}
}

// left
eienElement.prototype.left = function( l )
{
	try
	{
		var ret = this.foreach( function()
		{
			var pleft = 0;
			var p = this.e.offsetParent;
			while ( p )
			{
				pleft += p.offsetLeft;
				p = p.offsetParent;
			}

			if ( l == undefined )
			{
				return pleft + this.e.offsetLeft;
			}
			else
			{
				l = l - pleft;
				this.style( 'left', l + 'px' );
			}
		} );
		return l == undefined ? ret : this;
	}
	catch ( e )
	{
		alert(e.description);
	}
}

// top
eienElement.prototype.top = function( t )
{
	try
	{
		var ret = this.foreach( function()
		{
			var ptop = 0;
			var p = this.e.offsetParent;
			while ( p )
			{
				ptop += p.offsetTop;
				p = p.offsetParent;
			}

			if ( t == undefined )
			{
				return ptop + this.e.offsetTop;
			}
			else
			{
				t = t - ptop;
				this.style( 'top', t + 'px' );
			}
		} );
		return t == undefined ? ret : this;
	}
	catch ( e )
	{
		alert(e.description);
	}
}

// style
eienElement.prototype.style = function( attr, val )
{
	try
	{
		var ret = this.foreach( function()
		{
			if ( val == undefined ) // 获取样式
			{
				if ( eien.browser.ie )
				{
					if ( attr == undefined )
						return this.e.currentStyle;
					else
						return this.e.currentStyle[attr];
				}
				else
				{
					if ( attr == undefined )
						return getComputedStyle( this.e, null );
					else
						return getComputedStyle( this.e, null )[attr];
				}
			}
			else // 设置样式
			{
				if ( attr == 'float' )
				{
					if ( eien.browser.ie )
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
		} );
		return val == undefined ? ret : this;
	}
	catch ( e )
	{
		alert(e.description);
	}
	return null;
}

// 内部html内容
eienElement.prototype.inner = function( html )
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

////////////////////////////////////////////////////////////////////////////////////////////////////
// eien.utils 小工具函数
eien.utils = {

// 秒数显示时间串
showTimeStr: function( t )
{
	var res = 0;
	var str = '';
	if ( t )
	{
		res = Math.floor(t / 86400);
		t %= 86400;
		res && (str += res + '天');
	}
	if ( t )
	{
		res = Math.floor(t / 3600);
		t %= 3600;
		res && (str += res + '小时');
	}
	if ( t )
	{
		res = Math.floor(t / 60);
		t %= 60;
		res && (str += res + '分');
	}
	if ( t )
	{
		res = t;
		res && (str += res + '秒');
	}

	str || (str = '0秒');
	return str;
},
// {key:value}转到uri串
uri: function ( obj )
{
	var flag = false;
	var str = '';
	for ( var k in obj )
	{
		if ( flag )
		{
			str += '&';
		}
		str += encodeURIComponent(k) + '=' + encodeURIComponent(obj[k]);
		flag = true;
	}
	return str;
},
/*字节数显示成串*/
bytesSize: function ( size )
{
	if ( size < 1024 )
	{
		return String(size) + 'B';
	}
	else if ( size < 1024 * 1024 )
	{
		return String( Math.ceil( size * 100 / 1024 ) / 100 ) + 'KB';
	}
	else
	{
		return String( Math.ceil( size * 100 / ( 1024 * 1024 ) ) / 100 ) + 'MB';
	}
},
// COOKIE操作函数
// 添加
// void setCookie(string sName ,string sValue [,int expires]);
setCookie: function( sName, sValue, expires )
{
	var cookieStr = encodeURIComponent(sName) + "=" + encodeURIComponent(sValue) + "; ";
	if ( expires )
	{
		var date = new Date();
		var newDate = new Date( date.valueOf() + expires * 1000 );
		cookieStr += "expires=" + newDate.toGMTString() + "; ";
	}
	//alert(cookieStr);
	document.cookie = cookieStr;
},
// 获取
// string getCookie(string sName);
getCookie: function( sName )
{
	// cookies are separated by semicolons
	var aCookie = document.cookie.split("; ");
	for ( var i = 0; i < aCookie.length; i++ )
	{
		// a name/value pair (a crumb) is separated by an equal sign
		var aCrumb = aCookie[i].split("=");
		if ( sName == decodeURIComponent(aCrumb[0]) )
			return decodeURIComponent(aCrumb[1]);
	}
	// a cookie with the requested name does not exist
	return "";
},
// Delete the cookie with the specified name.
// void delCookie(string sName [,string sValue]);
delCookie: function( sName, sValue )
{
	sValue = sValue != undefined ? sValue : '';
	document.cookie = encodeURIComponent(sName) + "=" + encodeURIComponent(sValue) + "; expires=Fri, 31 Dec 1999 23:59:59 GMT; ";
},
// 设置首页
setIndexPage: function( obj, url )
{
	if ( eien.browser.ie )
	{
		obj.style.behavior = 'url(#default#homepage)';
		obj.setHomePage(url);
	}
	else if ( eien.browser.firefox )
	{
		if ( window.netscape )
		{
			try
			{
				netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
			}
			catch ( e )
			{
				alert('用户取消了此操作，或者浏览器拒绝此操作！\n请在浏览器地址栏输入"about:config"并回车。\n然后将[signed.applets.codebase_principal_support]设置为true。');
				return;
			}
		}
		else
		{
			alert('NetScape对象无效！');
			return;
		}

		var prefs = Components.classes['@mozilla.org/preferences-service;1'].getService(Components.interfaces.nsIPrefBranch);
		if ( window.confirm('是否将"' + url + '"设为主页？') )
		{
			prefs.setCharPref( 'browser.startup.homepage', url );
		}
	}
	else
	{
		alert('对不起，本站暂不支持您所用浏览器的这个操作！');
	}
},
// 加入收藏
addFavorite: function( url, title )
{
	title = title != undefined ? title : '';
	if ( eien.browser.ie )
	{
		window.external.AddFavorite( url, title );
	}
	else if ( eien.browser.firefox )
	{
		window.sidebar.addPanel( title, url, "" );
	}
	else
	{
		alert('对不起，本站暂不支持您所用浏览器的这个操作！');
	}
},
// 使相关checkboxes全选。依据ctl指定的checkbox作为是否标记，id为前缀，指示控制哪些checkboxes。
// 经常用在帖子全部操作时的UI控制。
// void selectAll( CheckBox ctl, String id );
// ctl : 控制器
// id : id前缀
checkboxSelectAll: function( ctl, id )
{
	var oChk;
	var checked = ctl.checked != undefined ? ctl.checked : false;
	for ( var i = 0; oChk = _byId(id + i); i++ )
	{
		oChk.checked = checked;
		if ( oChk.onclick ) oChk.onclick();
	}
},
// 控制一个HTML对象的可见性
// boolean elementVisible(HTMLElement obj[, String value = 'auto'[, String property = 'display']]);
elementVisible: function( obj, value, property )
{
	value = value != undefined ? value : 'auto';
	property = property != undefined ? property : 'display';
	var visible;
	try
	{
		switch ( property )
		{
		case 'display':
			if ( value == 'auto' )
			{
				visible = obj.style.display == 'none';
				value = visible ? '' : 'none';
			}
			obj.style.display = value;
			break;
		case 'visibility':
			if ( value == 'auto' )
			{
				visible = obj.style.visibility == 'hidden';
				value = visible ? '' : 'hidden';
			}
			obj.style.visibility = value;
			break;
		}
	}
	catch ( e )
	{
		alert(e.description);
	}
	return visible;
},
// 含有单位的数字串获取数字部分的数值
getUnitNumber: function( val )
{
	var n = parseInt(val);
	return isNaN(n) ? 0 : n;
}

}