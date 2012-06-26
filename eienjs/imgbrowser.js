/**
 * ImgBrowser 图片浏览器类
 * @author: WaiTing
 * @version: 1.0.0
 */

// 定义静态属性
ImgBrowser.browserSWF = 'imgbrowser.swf';

// 构造函数
// ImgBrowser(int width, int height, int textHeight[, Array pics, Array texts, Array links]);
function ImgBrowser(width, height, textHeight, pics, texts, links)
{
	// 属性
	this.width = width;           // SWF width
	this.height = height;         // SWF height
	this.textHeight = textHeight; // Text Height
	this.borderWidth = this.width;
	this.borderHeight = this.height - this.textHeight;
	// 背景色
	this.bgcolor = '#FFFFFF';
	this.wmode = 'transparent';//'opaque';
	// 图片URL数组
	this.imgURL = pics != undefined ? pics : new Array();
	// 文本数组
	this.imgText = texts != undefined ? texts : new Array();
	// 连接数组
	this.imgLink = links != undefined ? links : new Array();
}
// 往图片浏览器中添加一个图片
// addImage(String pic, String text, String link);
ImgBrowser.prototype.addImage = function(pic, text, link)
{
	this.imgURL.push(pic);
	this.imgText.push(encodeURIComponent(text));
	this.imgLink.push(encodeURIComponent(link));
}
// 获得UI字符串数据
ImgBrowser.prototype.getUIStr = function()
{
	var pics = this.imgURL.join('|');
	var texts = this.imgText.join('|');
	var links = this.imgLink.join('|');

	var str = '';
	str += '<object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=7,0,19,0" width="' + this.width + '" height="' + this.height + '">';
	str += '<param name="movie" value="' + ImgBrowser.browserSWF + '" />';
	str += '<param name="allowScriptAccess" value="sameDomain" />';
	str += '<param name="quality" value="high" />';
	str += '<param name="bgcolor" value="' + this.bgcolor + '" />';
	str += '<param name="menu" value="false" />';
	str += '<param name="wmode" value="' + this.wmode + '" />';
	str += '<param name="FlashVars" value="pics=' + pics + '&links=' + links + '&texts=' + texts + '&borderwidth=' + this.borderWidth + '&borderheight=' + this.borderHeight + '&textheight=' + this.textHeight + '" />';
	str += '<embed src="' + ImgBrowser.browserSWF + '" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer" width ="'+ this.width +'" height="'+ this.height +'" wmode="' + this.wmode + '" menu="false" bgcolor="' + this.bgcolor + '" quality="high" allowScriptAccess="sameDomain" FlashVars="pics=' + pics + '&links=' + links + '&texts=' + texts + '&borderwidth=' + this.borderWidth + '&borderheight= ' + this.borderHeight + '&textheight=' + this.textHeight + '" />';
	str += '</object>';

	return str;
}
// 向HTML中输出浏览器,即显示.
// show();
ImgBrowser.prototype.show = function()
{
	document.write(this.getUIStr());
}
