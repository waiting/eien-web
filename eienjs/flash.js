/**
 * Flash类,用于向网页中嵌入FLASH插件,并提供一些常用操作
 * 输出遵循XHTML1.0

 * @author WaiTing
 * @version 1.0.0
 */

// 构造函数
// Flash(string flash, int cx, int cy[, string id = '']);
// flash:URL,cx:宽度,cy:高度[,id:标识]
function Flash(flash, cx, cy, id, otherAttr)
{
	this.flash = flash;
	this.cx = cx;
	this.cy = cy;
	this.id = id != undefined ? id : '';
	this.otherAttr = otherAttr != undefined ? otherAttr : '';
	this.params = {};
	this.flaVars = {};
	this.setParam('movie', this.flash);
}
// 添加一个param元素
// void setParam(string name, string value);
Flash.prototype.setParam = function(name, value)
{
	this.params[name] = value;
}
// 移除param
// void unsetParam(string name);
Flash.prototype.unsetParam = function(name)
{
	delete this.params[name];
}
// 添加传往Flash中ActionScript的变量,将成为AS的全局变量
// void setFlashVar(string name, string value);
Flash.prototype.setFlashVar = function(name, value)
{
	this.flaVars[name] = value;
}
// 移除一个FlashVar
// void unsetFlashVar(string name);
Flash.prototype.unsetFlashVar = function(name)
{
	delete this.flaVars[name];
}
// 输出
// string output([boolean ret = false]);
// ret:为假则直接输出到浏览器,为真则作为字符串返回
Flash.prototype.output = function()
{
	var embed = '<embed' + (this.id != '' ? ' name="' + this.id + '"' : '') + ' src="' + this.flash + '" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer" width="' + this.cx + '" height="' + this.cy + '" ';
	var s = '';
	s += '<object ' + this.otherAttr + (this.id != '' ? ' id="' + this.id + '"' : '') + ' classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="http://fpdownload.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=8,0,0,0" width="' + this.cx + '" height="' + this.cy + '">';
	var sFlaVars = '';
	var i = 0;
	for (varname in this.flaVars)
	{
		if (i != 0)
		{
			sFlaVars += '&';
		}
		sFlaVars += varname + '=' + this.flaVars[varname];
		i++;
	}

	for (name in this.params)
	{
		s += '<param name="' + name + '" value="' + this.params[name] + '" />';
		embed += name + '="' + this.params[name] + '" ';
	}

	if (sFlaVars != '')
	{
		s += '<param name="FlashVars" value="' + sFlaVars + '" />';
		embed += 'FlashVars="' + sFlaVars + '" ';
	}

	embed += '/>';
	s += embed;
	s += '</object>';
	//alert(s);
	ret = arguments[0] ? arguments[0] : false;
	if (!ret)
	{
		document.write(s);
		return '';
	}
	else
	{
		return s;
	}
}
/* 常用方法 */
// 透明背景
// void enabledTransparent([boolean enabled = true]);
Flash.prototype.enabledTransparent = function(enabled)
{
	if (enabled == undefined) enabled = true;
	if (enabled === null)
	{
		this.unsetParam('wmode');
	}
	else if(enabled)
	{
		this.setParam('wmode', 'transparent');
	}
	else
	{
		this.setParam('wmode', 'opaque');
	}
}
// allowScriptAccess
// void allowScriptAccess([string v = 'sameDomain']);
Flash.prototype.allowScriptAccess = function(v)
{
	if (v == undefined) v = 'sameDomain';
	if (v === null)
	{
		this.unsetParam('allowScriptAccess');
	}
	else
	{
		this.setParam('allowScriptAccess', v);
	}
}
// 背景色
// void setBgColor([string bgcolor = '#FFFFFF']);
Flash.prototype.setBgColor = function(bgcolor)
{
	if (bgcolor == undefined) bgcolor = '#FFFFFF';
	if (bgcolor === null)
	{
		this.unsetParam('bgcolor');
	}
	else
	{
		this.setParam('bgcolor', bgcolor);
	}
}
