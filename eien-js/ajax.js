/**
 * Ajax相关
 * @author WaiTing
 * @version 1.1.0 */

// _Ajax类
function _Ajax( method, url, async, username, password )
{
	this.method = method != undefined ? method : 'GET';
	this.url = url != undefined ? url : '.';
	this.async = async != undefined ? async : true;
	this.username = username != undefined ? username : '';
	this.password = password != undefined ? password : '';
	/* -------- 事件 ---------
    //event handle             // readyState
	this.onnoinit = null;      // 未初始化 0
	this.onreadyok = null;     // 准备OK   1
	this.onsent = null;        // 已经发送 2
	this.onrecv = null;        // 正在接收 3
	this.onfin = null;         // 完毕     4 */

	this.create();
	this.open();
}

_Ajax.createXMLHttpRequest = function()
{
	var request = false;
	if ( window.XMLHttpRequest )
	{
		request = new XMLHttpRequest();
		if ( request.overrideMimeType )
		{
			request.overrideMimeType('text/xml');
		}
	}
	else if ( window.ActiveXObject )
	{
		var versions = ['Microsoft.XMLHTTP', 'MSXML.XMLHTTP', 'Microsoft.XMLHTTP', 'Msxml2.XMLHTTP.7.0', 'Msxml2.XMLHTTP.6.0', 'Msxml2.XMLHTTP.5.0', 'Msxml2.XMLHTTP.4.0', 'MSXML2.XMLHTTP.3.0', 'MSXML2.XMLHTTP'];
		for ( var i = 0; i < versions.length; i++ )
		{
			try
			{
				request = new ActiveXObject(versions[i]);
				if ( request )
				{
					return request;
				}
			}
			catch ( e )
			{ }
		}
	}
	return request;
}

// 创建
_Ajax.prototype.create = function()
{
	this.xmlHttp = _Ajax.createXMLHttpRequest();
	var thisAjaxObj = this;
	// 绑定事件
	this.xmlHttp.onreadystatechange = function()
	{
		switch ( thisAjaxObj.xmlHttp.readyState )
		{
		case 0://描述一种"未初始化"状态；此时，已经创建一个XMLHttpRequest对象，但是还没有初始化。
			if ( thisAjaxObj.onnoinit ) thisAjaxObj.onnoinit(thisAjaxObj.xmlHttp);
			break;
		case 1://描述一种"发送"状态；此时，代码已经调用了XMLHttpRequest.open()方法并且XMLHttpRequest已经准备好把一个请求发送到服务器。
			if ( thisAjaxObj.onreadyok ) thisAjaxObj.onreadyok(thisAjaxObj.xmlHttp);
			break;
		case 2://描述一种"发送"状态；此时，已经通过send()方法把一个请求发送到服务器端，但是还没有收到一个响应。
			if ( thisAjaxObj.onsent ) thisAjaxObj.onsent(thisAjaxObj.xmlHttp);
			break;
		case 3://描述一种"正在接收"状态；此时，已经接收到HTTP响应头部信息，但是消息体部分还没有完全接收结束。
			if ( thisAjaxObj.onrecv ) thisAjaxObj.onrecv(thisAjaxObj.xmlHttp);
			break;
		case 4://描述一种"已加载"状态；此时，响应已经被完全接收。
			if ( thisAjaxObj.onfin ) thisAjaxObj.onfin(thisAjaxObj.xmlHttp);
			break;
		}
	}
	return this.xmlHttp;
}
// 打开方法
_Ajax.prototype.open = function()
{
	// 防止缓存,对URL进行加工
	var url = this.url;
	if ( url.indexOf('?') != -1 )
	{
		url += '&_reqt=' + encodeURIComponent( new Date() );
	}
	else
	{
		url += '?_reqt=' + encodeURIComponent( new Date() );
	}

	this.xmlHttp.open( this.method, url, this.async, this.username, this.password );
	// POST方法时
	if ( this.method.toUpperCase() == 'POST' )
	{
		this.xmlHttp.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
	}
	// 打开后的初始化工作
	if ( this.init ) this.init();
}
// 发送
_Ajax.prototype.send = function( data )
{
	this.xmlHttp.send( data != undefined ? data : null );
}
// 终止
_Ajax.prototype.abort = function()
{
	this.xmlHttp.abort();
}
// 设置请求头信息
_Ajax.prototype.setRequestHeader = function( header, value )
{
	this.xmlHttp.setRequestHeader( header, value );
}
// 检索响应的头部值
_Ajax.prototype.getResponseHeader = function( header )
{
	if ( arguments[1] != undefined ) return this.xmlHttp.getResponseHeader( header, arguments[1] );
	return this.xmlHttp.getResponseHeader(header);
}
// 以一个字符串形式返回所有的响应头部（每一个头部占单独的一行）。
_Ajax.prototype.getAllResponseHeaders = function()
{
	return this.xmlHttp.getAllResponseHeaders();
}

/** AJAX高级封装 */
// HttpGet(string url, function onfin[, string username][, string password])
_Ajax.get = function( url, onfin, username, password )
{
	username = username != undefined ? username : '';
	password = password != undefined ? password : '';
	var aj = new _Ajax( 'GET', url, true, username, password );
	aj.onfin = onfin;
	aj.send();
}

// HttpPost(string url, URI data, function onfin[, string username][, string password])
_Ajax.post = function( url, data, onfin, username, password )
{
	username = username != undefined ? username : '';
	password = password != undefined ? password : '';
	var aj = new _Ajax( 'POST', url, true, username, password );
	aj.onfin = onfin;
	aj.send(data);
}
