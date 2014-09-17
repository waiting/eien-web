/**
 * Ajax相关
 * @author WaiTing
 * @version 1.1.0
 */
 
/**
 * 创建XMLHttpRequest对象
 */
function createXMLHttpRequest()
{
	var request = false;
	if (window.XMLHttpRequest)
	{
		request = new XMLHttpRequest();
		if (request.overrideMimeType)
		{
			request.overrideMimeType('text/xml');
		}
	}
	else if (window.ActiveXObject)
	{
		var versions = ['Microsoft.XMLHTTP', 'MSXML.XMLHTTP', 'Microsoft.XMLHTTP', 'Msxml2.XMLHTTP.7.0', 'Msxml2.XMLHTTP.6.0', 'Msxml2.XMLHTTP.5.0', 'Msxml2.XMLHTTP.4.0', 'MSXML2.XMLHTTP.3.0', 'MSXML2.XMLHTTP'];
		for (var i = 0; i < versions.length; i++)
		{
			try
			{
				request = new ActiveXObject(versions[i]);
				if (request)
				{
					return request;
				}
			}
			catch (e)
			{}
		}
	}
	return request;
}
// _Ajax类
function _Ajax()
{
	this.method = arguments[0] ? arguments[0] : 'GET';
	this.url = arguments[1] ? arguments[1] : '.';
	this.async = arguments[2] != undefined ? arguments[2] : true;
	this.username = arguments[3] ? arguments[3] : '';
	this.password = arguments[4] ? arguments[4] : '';
/* -------- 事件 ---------
    //event handle             // readyState
	this.onnoinit = null;      // 未初始化 0
	this.onreadyok = null;     // 准备OK   1
	this.onsent = null;        // 已经发送 2
	this.onrecv = null;        // 正在接收 3
	this.onfin = null;         // 完毕     4
*/
	// 创建XMLHttpRequest对象
	this.create();
}

// 打开方法
_Ajax.prototype.open = function()
{
	if(arguments[0] != undefined) this.method = arguments[0];
	if(arguments[1] != undefined) this.url = arguments[1];
	if(arguments[2] != undefined) this.async = arguments[2];
	if(arguments[3] != undefined) this.username = arguments[3];
	if(arguments[4] != undefined) this.password = arguments[4];
	// 防止缓存,对URL进行加工
	var url = this.url;
	if (url.indexOf('?') != -1)
	{
		url += '&_reqt=' + new Date();
	}
	else
	{
		url += '?_reqt=' + new Date();
	}
	this.xmlHttp.open(this.method, url, this.async, this.username, this.password);
	// POST方法时
	if(this.method.toUpperCase() == 'POST')
	{
		this.xmlHttp.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
	}
	// 打开后的初始化工作
	if (this.init) this.init();
}
// 创建
_Ajax.prototype.create = function()
{
	this.xmlHttp = createXMLHttpRequest();
	var this_obj = this;
	// 绑定事件
	this.xmlHttp.onreadystatechange = function()
	{
		switch (this_obj.xmlHttp.readyState)
		{
		case 0://描述一种"未初始化"状态；此时，已经创建一个XMLHttpRequest对象，但是还没有初始化。
			if (this_obj.onnoinit) this_obj.onnoinit(this_obj.xmlHttp);
			break;
		case 1://描述一种"发送"状态；此时，代码已经调用了XMLHttpRequest.open()方法并且XMLHttpRequest已经准备好把一个请求发送到服务器。
			if (this_obj.onreadyok) this_obj.onreadyok(this_obj.xmlHttp);
			break;
		case 2://描述一种"发送"状态；此时，已经通过send()方法把一个请求发送到服务器端，但是还没有收到一个响应。
			if (this_obj.onsent) this_obj.onsent(this_obj.xmlHttp);
			break;
		case 3://描述一种"正在接收"状态；此时，已经接收到HTTP响应头部信息，但是消息体部分还没有完全接收结束。
			if (this_obj.onrecv) this_obj.onrecv(this_obj.xmlHttp);
			break;
		case 4://描述一种"已加载"状态；此时，响应已经被完全接收。
			if (this_obj.onfin) this_obj.onfin(this_obj.xmlHttp);
			break;
		}
	}
	return this.xmlHttp;
}
// 发送
_Ajax.prototype.send = function()
{
	var data = arguments[0] != undefined ? arguments[0] : null;
	this.xmlHttp.send(data);
}
// 终止
_Ajax.prototype.abort = function()
{
	this.xmlHttp.abort();
}
// 设置请求头信息
_Ajax.prototype.setRequestHeader = function(header,value)
{
	this.xmlHttp.setRequestHeader(header,value);
}
// 检索响应的头部值
_Ajax.prototype.getResponseHeader = function(header)
{
	if (arguments[1] != undefined) return this.xmlHttp.getResponseHeader(header,arguments[1]);
	return this.xmlHttp.getResponseHeader(header);
}
// 以一个字符串形式返回所有的响应头部（每一个头部占单独的一行）。
_Ajax.prototype.getAllResponseHeaders = function()
{
	return this.xmlHttp.getAllResponseHeaders();
}

/**
 * AJAX高级封装
 */
// HttpGet(string url, function onfin[, string username][, string password])
function HttpGet(url, onfin, username, password)
{
	username = username != undefined ? username : '';
	password = password != undefined ? password : '';
	var request = createXMLHttpRequest();
	request.onreadystatechange = function()
	{
		switch (request.readyState)
		{
		case 4:
			if (onfin) onfin(request);
		}
	}
	var dtime = new Date();
	if (url.indexOf('?') != -1)
	{
		url += '&_reqt=' + dtime.valueOf();
	}
	else
	{
		url += '?_reqt=' + dtime.valueOf();
	}
	request.open('GET', url, true, username, password);
	request.send(null);
}

// HttpPost(string url, URI data, function onfin[, string username][, string password])
function HttpPost(url, data, onfin, username, password)
{
	username = username != undefined ? username : '';
	password = password != undefined ? password : '';
	var request = createXMLHttpRequest();
	request.onreadystatechange = function()
	{
		switch (request.readyState)
		{
		case 4:
			if (onfin) onfin(request);
		}
	}
	var dtime = new Date();
	if (url.indexOf('?') != -1)
	{
		url += '&_reqt=' + dtime.valueOf();
	}
	else
	{
		url += '?_reqt=' + dtime.valueOf();
	}
	request.open('POST', url, true, username, password);

	request.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
	request.send(data);
}
