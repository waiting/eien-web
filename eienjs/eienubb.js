/*
 * Ultimate Bulletin Board Code Parser
 * 自定义UBB解析
 * 由于正则表达式的局限性,用它来写UBB解析器,导致相同tag不能支持嵌套语法.
 * 因此,设计出一种能把UBB代码解析成树型模型的解析器,就显得很有价值.
 * @author WaiTing
 * @version 0.1.1
 */
// 系列辅助函数
//把对HTML特殊的字符转化为HTML实体
function htmlspecialchars(str)
{
	var chars = ["&", "<", ">", "\"", " ", "\r\n", "\n", "\r"];
	var htmlmap = {"&":"&amp;", "<":"&lt;", ">":"&gt;", "\"":"&quot;", " ":"&nbsp;", "\r\n":"<br />", "\n":"<br />", "\r":"<br />"};
	return str.replace(new RegExp(chars.join("|"), "g"), function(text)
	{
		return htmlmap[text];
	});
}
function texthtmlentry(str)
{
	var htmlentry = ["&amp;", "&lt;", "&gt;", "&quot;", "&nbsp;", "<br />"];
	var charmap = {"&amp;":"&", "&lt;":"<", "&gt;":">", "&quot;":"\"", "&nbsp;":" ", "<br />":"\r\n"};
	return str.replace(new RegExp(htmlentry.join("|"), "g"), function(text)
	{
		return charmap[text];
	});
}
function addcslashes(str)
{
	var chars = ["\b", "\t", "\r", "\n", "\"", "'", "\\\\"];
	var charmap = {"\b":"\\b", "\t":"\\t", "\r":"\\r", "\n":"\\n", "\"":"\\\"", "'":"\\'", "\\":"\\\\"};
	return str.replace(new RegExp(chars.join("|"), "g"), function(text)
	{
		return charmap[text];
	});
}
function stripcslashes(str)
{
	var chars = ["\\\\b", "\\\\t", "\\\\r", "\\\\n", "\\\\\"", "\\\\'", "\\\\\\\\"];
	var charmap = {"\\b":"\b", "\\t":"\t", "\\r":"\r", "\\n":"\n", "\\\"":"\"", "\\'":"'", "\\\\":"\\"};
	return str.replace(new RegExp(chars.join("|"), "g"), function(text)
	{
		return charmap[text];
	});
}
/**
 * UBB 解析器
 */
var UBBParser = function ()
{
}

//public void parse(UBBElement e, string str);
UBBParser.prototype.parse = function (e, str)
{
	// 搜索一个定义的Tag
	var retObj = this.searchDefinedTag(0, str);
	var pos = retObj.pos;
	var tag_length = retObj._length;

	if (pos == -1) // 说明没有搜到tag, 全当作文本来处理
	{
		var textElem = new UBBText();
		textElem.value = str;
		e.appendChild(textElem);
	}
	else // 搜到[...]
	{
		// 前面若有文本,先处理它
		if (pos != 0)
		{
			var textElem = new UBBText();
			textElem.value = str.substr(0, pos);
			e.appendChild(textElem);
		}

		var tagStr = str.substr(pos, tag_length);
		var nextStart = pos + tag_length;   // 下次起始位置
		var isHeader = this.isHeader(tagStr).isHeader;
		// 下次串
		var substr = str.substr(nextStart);
		if (isHeader !== null) // 不是空[]解析到了数据
		{
			// 读数据
			var retObj = this.readData(tagStr);
			var tagName = retObj.tagName;
			var defAttr = retObj.defAttr;
			var attributes = retObj.attributes;

			if (isHeader) // 是头
			{
				var elem = UBBTagList.fromTagName(tagName);
				elem.tagName = tagName;
				elem.defAttr = defAttr;
				elem.attributes = attributes;
				elem.raw1 = tagStr;
				e.appendChild(elem);

				if (substr) this.parse((elem.independent ? e : elem), substr);
			}
			else // 是尾
			{
				if (e.tagName.toUpperCase() != tagName.toUpperCase()) // 如过不能匹配
				{
					// 就把此尾部当作文本处理,连入本元素的文本接点,继续...
					e.appendToLastTextNode(tagStr);
					if (substr) this.parse(e, substr);
				}
				else
				{
					e.raw2 = tagStr;
					if (substr) this.parse(e.parent, substr);
				}
			}
		}
		else
		{
			e.appendToLastTextNode(tagStr);
			if (substr) this.parse(e, substr);
		}

	}
}

// 搜一个支持的tag, type = {0:任意,1:头,2:尾}
// private object{pos, _length} searchDefinedTag(int type, string str);
UBBParser.prototype.searchDefinedTag = function (type, str)
{
	var searchPos = 0;
	var pos = 0;
	var _length = 0;
	var tagStr = '';
	var tagName = '';
	do
	{
		searchPos += pos + _length;
		var retObj = this.searchTag(type, str.substr(searchPos));
		pos = retObj.pos;
		_length = retObj._length;
		tagStr = str.substr(searchPos + pos, _length);
		tagName = this.readData(tagStr).tagName;
	}
	while (!(pos == -1 || UBBTagList.tagExists(tagName)));
	if (pos == -1) return {"pos":-1, "_length":_length};
	else return {"pos":searchPos + pos, "_length":_length};
}
// 搜一个tag, type = {0:任意,1:头,2:尾}
// private object{pos, _length} searchTag(int type, string str);
UBBParser.prototype.searchTag = function (type, str)
{
	var _length = 0;
	var start = 0;
	var len = str.length;
	while (start < len)
	{
		var retObj = this.search_lrdelim(str.substr(start));
		var pos = retObj.pos;
		var tag_length = retObj._length;
		if (pos == -1)
		{
			return {"pos":-1, "_length":_length};
		}
		else
		{
			pos += start;
			var tagStr = str.substr(pos, tag_length);
			var isHead = this.isHeader(tagStr).isHeader;
			if (type == 0 && isHead !== null)
			{
				_length = tag_length;
				return {"pos":pos, "_length":_length};
			}
			else if (type == 1 && isHead === true)
			{
				_length = tag_length;
				return {"pos":pos, "_length":_length};
			}
			else if (type == 2 && isHead === false)
			{
				_length = tag_length;
				return {"pos":pos, "_length":_length};
			}
			start = pos + tag_length;   // 下次起始位置
		}
	}
	return {"pos":-1, "_length":_length};
}
// 搜索 [...] 这个串, 返回其开始位置,否则返回-1, _length表示搜到的长度
// private object{pos, _length} search_lrdelim(string str);
UBBParser.prototype.search_lrdelim = function (str)
{
	var _length = 0;
	var pos = str.indexOf(UBBElement.ldelim);
	if (pos == -1) return {"pos":-1, "_length":_length};
	str = str.substr(pos);
	var pos2 = str.indexOf(UBBElement.rdelim);
	if (pos2 == -1 || pos2 == 0) return {"pos":-1, "_length":_length};
	var newStr = str.substr(0, pos2);
	var pos1 = newStr.lastIndexOf(UBBElement.ldelim);
	if (pos1 == -1) return {"pos":-1, "_length":_length};
	var rdelimLen = UBBElement.rdelim.length;
	_length = pos2 - pos1 + rdelimLen;
	return {"pos":pos1 + pos, "_length":_length};
}

// 判断一个[...] 是头还是尾, data返回去掉界定符后的内容
// private object{isHeader, data} isHeader(string str);
UBBParser.prototype.isHeader = function (str)
{
	var ldelimLen = UBBElement.ldelim.length;
	var rdelimLen = UBBElement.rdelim.length;
	var len = str.length;
	var data = str.substr(ldelimLen, len - 1 - rdelimLen);

	if (data != '')
	{
		if (data.charAt(0) == '/')
		{
			return {"isHeader":false, "data":data};
		}
		else
		{
			return {"isHeader":true, "data":data};
		}
	}
	return {"isHeader":null, "data":data};
}
/*  读取字符到键名,遇到=号键名结束,开始读值
    如果第一个字符不是引号,则读到空格为止值结束
    如果是引号,则继续读到另一个引号为止值结束
    读到\时应对下一字符进行判断,如果是引号,则不结束值,继续读取.
*/
// 读取一个[...]内的数据
//private object{return, tagName, defAttr, attributes} readData(string str);
UBBParser.prototype.readData = function (str)
{
	var retObj = this.isHeader(str, data);
	var ret = retObj.isHeader;
	var data = retObj.data;
	var tagName = '';
	var defAttr = '';
	var attributes = {};
	if(ret !== null)
	{
		if (ret)
		{
			tagName = '';
			var len = data.length;
			for (var i = 0; i < len; i++)
			{
				var ch = data.charAt(i);
				if (ch == '=')
				{
					break;
				}
				else if (ch == ' ')
				{
					i++;
					break;
				}
				else
				{
					tagName += ch;
				}
			}
			tagName = tagName.toUpperCase();
			data = data.substr(i);

			var isKey = true;
			var key = '';
			var value = '';
			len = data.length;
			for (var i = 0; i < len; )
			{
				if (isKey)
				{
					var retObj = this.readKey(data, len, i);
					key = retObj.key;
					i = retObj.pos;
					isKey = false;
					if (i < len)
					{
						i++;  // skip "="
					}
				}
				else
				{
					var retObj = this.readValue(data, len, i);
					value = retObj.value;
					i = retObj.pos;
					isKey = true;
					// 跳过多余空格
					while (i < len && data.charAt(i) == ' ')
					{
						i++;  // skip " "
					}
					if (key == '')
					{
						defAttr = stripcslashes(value);
					}
					else
					{
						attributes[key.toUpperCase()] = stripcslashes(value);
					}
				}
			}
		}
		else
		{
			// skip "/"
			tagName = data.substr(1).toUpperCase();
		}
		return {"return":true, "tagName":tagName, "defAttr":defAttr, "attributes":attributes};
	}
	return {"return":false, "tagName":tagName, "defAttr":defAttr, "attributes":attributes};
}
// 读一个键
UBBParser.prototype.readKey = function (str, len, start)
{
	var key = '';
	var i;
	for (i = start; i < len; i++)
	{
		var ch = str.charAt(i);
		if (ch == '=')
		{
			break;
		}
		else
		{
			key += ch;
		}
	}
	var pos = i;
	return {"key":key, "pos":pos};
}
// 读取一个值
UBBParser.prototype.readValue = function (str, len, start)
{
	var value = '';
	var quote = '';
	var i;
	for (i = start; i < len; i++)
	{
		var ch = str.charAt(i);
		if (i == start && (ch == "'" || ch == '"'))
		{
			quote = ch;
		}
		else
		{
			if ((quote == '' && ch == ' ') || (quote != '' && ch == quote)) // 值结束条件
			{
				if (ch == quote)
					i++;  // skip "'" or '"'
				break;
			}
			else
			{
				value += ch;
				if (ch == "\\" && i < len - 1)
				{
					var c = str.charAt(i + 1);
					if (c == '"' || c == "'")
					{
						value += c;
						i++;
					}
				}
			}
		}
	}
	var pos = i;
	return {"value":value, "pos":pos};
}


// UBB 元素类 *****************************************************
var UBBElement = function ()
{
	UBBElement.init.apply(this, [UBBElement.UE_TEXT]);
}
UBBElement.UE_TEXT = 0; // 文本
UBBElement.UE_ELEM = 1; // 元素
UBBElement.UE_ROOT = 2; // 根

UBBElement.ldelim = '[';
UBBElement.rdelim = ']';

UBBElement.init = function (type, tagName, independent)
{
	this.value = null;    // 标签包含的数据
	this.type = (type != undefined ? type : UBBElement.UE_TEXT);
	this.parent = null;
	this.tagName = (tagName != undefined ? tagName : null);
	this.defAttr = null; // 默认属性
	this.attributes = {};
	this.children = [];

	this.raw1 = null;
	this.raw2 = null;

	this.independent = (independent != undefined ? independent : false);
}

/*子元素到字符串  procType{0:原始串,1:经过处理,2:树}*/
UBBElement.prototype.childAsStr = function (procType)
{
	procType = (procType != undefined ? procType : 0);
	var str = '';
	for (var k in this.children)
	{
		var e = this.children[k];
		str += e.asStr(procType);
	}
	return str;
}
/* 本元素作字符串 procType{0:原始串,1:经过处理,2:树}*/
UBBElement.prototype.asStr = function (procType)
{
	procType = (procType != undefined ? procType : 0);
	return null;
}
UBBElement.prototype.tagStr = function (isHead, procType)
{
	return null;
}
// 向最后一个文本节点加字符串,如果没有文本节点,则马上添加文本节点
UBBElement.prototype.appendToLastTextNode = function (str)
{
	var cnt = this.children.length;
	if (cnt)
	{
		var i;
		for (i = cnt - 1; i >= 0; i--)
		{
			var e = this.children[i];
			if (e.type == UBBElement.UE_TEXT)
			{
				e.value += str;
				break;
			}
		}
		if (i < 0)
		{
			var textElem = new UBBText();
			textElem.value = str;
			e.appendChild(textElem);
		}
	}
	else
	{
		var textElem = new UBBText();
		textElem.value = str;
		this.appendChild(textElem);
	}
}
UBBElement.prototype.appendChild = function (e)
{
	e.parent = this;
	this.children.push(e);
}

UBBElement.prototype.hasChildren = function ()
{
	return this.children.length != 0;
}

UBBElement.prototype.hasAttribute = function (name)
{
	return this.attributes[name] != undefined;
}
UBBElement.prototype.getAttribute = function (name)
{
	return this.hasAttribute(name) ? this.attributes[name] : null;
}
UBBElement.prototype.setAttribute = function (name, value)
{
	if (value == null)
	{
		delete this.attributes[name];
	}
	else
	{
		this.attributes[name] = value;
	}
}
UBBElement.prototype.raw_getElementsByTagName = function (arr, tagName)
{
	if (this.tagName == tagName)
	{
		arr.push(this);
	}
	for (var k in this.children)
	{
		var elem = this.children[k];
		elem.raw_getElementsByTagName(arr, tagName);
	}
}
UBBElement.prototype.getElementsByTagName = function (tagName)
{
	var arr = [];
	tagName = tagName.toUpperCase();
	this.raw_getElementsByTagName(arr, tagName);
	return arr.length != 0 ? arr : null;
}


// Text Element
var UBBText = function ()
{
	UBBElement.init.apply(this, [UBBElement.UE_TEXT]);
}
UBBText.prototype = new UBBElement();
/* 本元素作字符串 procType{0:原始串,1:经过处理,2:树}*/
UBBText.prototype.asStr = function (procType)
{
	procType = (procType != undefined ? procType : 0);
	switch (procType)
	{
	case 2: // 树型处理
		var t = '';
		var e = this;
		while (e.parent)
		{
			t += '&nbsp;&nbsp;&nbsp;&nbsp;';
			e = e.parent;
		}
		return t + '<span style="font-family:Arial;">' + htmlspecialchars(this.value) + '</span>' + "<br />";
	case 3:
		return htmlspecialchars(this.value);
	default:
		return this.value;
	}
	return '';
}


// UBB Tag
var UBBTag = function ()
{
	UBBElement.init.apply(this, [UBBElement.UE_ELEM, null, false]);
}
UBBTag.PROC_RAW = 0;     // 不处理
UBBTag.PROC_SIMPLE = 1;  // 简单修正
UBBTag.PROC_TREE = 2;    // 树型处理
UBBTag.PROC_REPLACE = 3; // 替换处理
UBBTag.PROC_STRIP = 4;   // 去掉tag

UBBTag.prototype = new UBBElement();

UBBTag.prototype.asStr = function (procType)
{
	procType = (procType != undefined ? procType : 0);
	switch (procType)
	{
	case 2:
		return this.treeProc();
	case 3:
		return this.replaceProc();
	default:
		var str = '';
		// 处理首部
		str += this.tagStr(true, procType);
		str += this.childAsStr(procType);
		// 处理尾部
		str += this.tagStr(false, procType);
		return str;
	}
	return '';
}
/* 子类可覆盖此函数以提供特殊的TAG替换处理 */
UBBTag.prototype.replaceProc = function ()
{
	var attrStr = '';
	for (var k in this.attributes)
	{
		var v = this.attributes[k];
		attrStr += ' ' + k.toUpperCase() + '="' + addcslashes(v) + '"';
	}
	var head = '<' + this.tagName.toLowerCase() + attrStr;
	if (this.independent) head += ' />';
	else head += '>';
	var foot;
	if (this.independent) foot = '';
	else foot = '</' + this.tagName.toLowerCase() + '>';

	return head + this.childAsStr(UBBTag.PROC_REPLACE) + foot;
}
/* 树型处理 */
UBBTag.prototype.treeProc = function ()
{
	var t = '';
	var e = this;
	while (e.parent)
	{
		t += '&nbsp;&nbsp;&nbsp;&nbsp;';
		e = e.parent;
	}
	var attrStr = '';
	for (var k in this.attributes)
	{
		var v = this.attributes[k];
		attrStr += " <strong style=\"font-family:Tahoma;\">" + k + "</strong>=\""+"<strong style=\"color:darkgreen;font-family:Tahoma;\">" + addcslashes(v) + "</strong>" + "\"";
	}
	var head = t + "<strong style=\"color:darkblue;font-family:Tahoma;\">" + UBBElement.ldelim + "</strong>" + "<strong style=\"font-family:Tahoma;\">" + this.tagName + "</strong>" + (this.defAttr ? '="' + "<strong style=\"color:green;font-family:Tahoma;\">" + addcslashes(this.defAttr) + "</strong>" + '"' : '') + attrStr + "<strong style=\"color:darkblue;font-family:Tahoma;\">" + UBBElement.rdelim + "</strong>" + "<br />";
	var foot;
	if (this.independent) foot = '';
	else foot = t + "<strong style=\"color:darkblue;font-family:Tahoma;\">" + UBBElement.ldelim + '/' + "</strong>" + "<strong style=\"font-family:Tahoma;\">" + this.tagName + "</strong>" + "<strong style=\"color:darkblue;font-family:Tahoma;\">" + UBBElement.rdelim + "</strong>" + "<br />";

	return head + this.childAsStr(UBBTag.PROC_TREE) + foot;
}

/* procType{0:原始串,1:经过处理,2:树型处理}*/
UBBTag.prototype.tagStr = function (isHead, procType)
{
	if (isHead)
	{
		switch (procType)
		{
		case 0:
			return this.raw1;
			break;
		case 1:
			var attrStr = '';
			for (var k in this.attributes)
			{
				var v = this.attributes[k];
				attrStr += " " + k + "=\"" + addcslashes(v) + "\"";
			}
			return UBBElement.ldelim + this.tagName + (this.defAttr ? '="' + addcslashes(this.defAttr) + '"' : '') + attrStr + UBBElement.rdelim;
			break;
		}
	}
	else
	{
		switch (procType)
		{
		case 0:
			return this.raw2;
			break;
		case 1:
			if (this.independent) return null;
			return UBBElement.ldelim + '/' + this.tagName + UBBElement.rdelim;
			break;
		}
	}
	return null;
}


// UBB Document
var UBBDocument = function (str)
{
	try
	{
		UBBElement.init.apply(this, [UBBElement.UE_ROOT, "#__eien.document", false]);

		if (str !== undefined)
		{
			this.load(str);
		}
	}
	catch (e)
	{
		alert("[" + e.number + "]:" + e.description + "\r\n" + "UBBDocument()")
	}
}
UBBDocument.prototype = new UBBElement();
/* 本元素作字符串 procType{0:原始串,1:经过处理,2:树}*/
UBBDocument.prototype.asStr = function (procType)
{
	procType = (procType != undefined ? procType : 0);
	return this.childAsStr(procType);
}
UBBDocument.prototype.load = function (str, ldelim, rdelim)
{
	try
	{
		ldelim = (ldelim != undefined ? ldelim : '[');
		rdelim = (rdelim != undefined ? rdelim : ']');

		var tmpl = UBBElement.ldelim;
		var tmpr = UBBElement.rdelim;
		UBBElement.ldelim = ldelim;
		UBBElement.rdelim = rdelim;
		var parser = new UBBParser();
		parser.parse(this, str);
		UBBElement.ldelim = tmpl;
		UBBElement.rdelim = tmpr;
	}
	catch (e)
	{
		alert("[" + e.number + "]:" + e.description + "\r\n" + "ubb_proc()")
	}
}


// 提供一个全局函数,方便使用
function ubb_proc(str, procType)
{
	try
	{
		procType = (procType != undefined ? procType : UBBTag.PROC_REPLACE);
		var doc = new UBBDocument();
		doc.load(str);
		return doc.asStr(procType);
	}
	catch (e)
	{
		alert("[" + e.number + "]:" + e.description + "\r\n" + "ubb_proc()")
	}
}

// Tag列表
var UBBTagList = new Object();
UBBTagList.tagList = {
	'U':UBBTag,
	'B':UBBTag,
	'I':UBBTag,
	'TD':UBBTag
};
// 向标签表里增加或修改标签
UBBTagList.setTag = function (tagName, clsName)
{
	UBBTagList.tagList[tagName.toUpperCase()] = clsName;
}
UBBTagList.delTag = function (tagName)
{
	delete UBBTagList.tagList[tagName.toUpperCase()];
}
UBBTagList.fromTagName = function (tagName)
{
	if (UBBTagList.tagExists(tagName))
	{
		classname = UBBTagList.tagList[tagName.toUpperCase()];
		return new classname();
	}
	else
		return new UBBTag();
}
UBBTagList.tagExists = function (tagName)
{
	return UBBTagList.tagList[tagName.toUpperCase()] != undefined;
}



/*定义TAG*/
// table
var UBBTableTag = function ()
{
	UBBElement.init.apply(this, [UBBElement.UE_ELEM, "TABLE", false]);
}
UBBTableTag.prototype = new UBBTag();
UBBTableTag.prototype.replaceProc = function ()
{
	var attrStr = '';
	for (var k in this.attributes)
	{
		var v = this.attributes[k];
		attrStr += ' ' + k.toLowerCase() + '="' + addcslashes(v) + '"';
	}
	var head = '<' + this.tagName.toLowerCase() + attrStr;
	if (this.independent) head += ' />';
	else head += '>';
	var foot;
	if (this.independent) foot = '';
	else foot = '</' + this.tagName.toLowerCase() + '>';

	var childStr = '';
	for (var k in this.children)
	{
		var e = this.children[k];
		if (e.tagName == 'TR')
		{
			childStr += e.asStr(UBBTag.PROC_REPLACE);
		}
	}
	return head + childStr + foot;
}
UBBTagList.setTag("TABLE", UBBTableTag);

// tr
var UBBTrTag = function ()
{
	UBBElement.init.apply(this, [UBBElement.UE_ELEM, "TR", false]);
}
UBBTrTag.prototype = new UBBTag();
UBBTrTag.prototype.replaceProc = function ()
{
	var attrStr = '';
	for (var k in this.attributes)
	{
		var v = this.attributes[k];
		attrStr += ' ' + k.toLowerCase() + '="' + addcslashes(v) + '"';
	}
	var head = '<' + this.tagName.toLowerCase() + attrStr;
	if (this.independent) head += ' />';
	else head += '>';
	var foot;
	if (this.independent) foot = '';
	else foot = '</' + this.tagName.toLowerCase() + '>';

	var childStr = '';
	for (var k in this.children)
	{
		var e = this.children[k];
		if (e.tagName == 'TD')
		{
			childStr += e.asStr(UBBTag.PROC_REPLACE);
		}
	}
	return head + childStr + foot;
}
UBBTagList.setTag("TR", UBBTrTag);


// Color
var UBBColorTag = function ()
{
	UBBElement.init.apply(this, [UBBElement.UE_ELEM, "COLOR", false]);
}
UBBColorTag.prototype = new UBBTag();
UBBColorTag.prototype.replaceProc = function ()
{
	return '<span style="color:' + this.defAttr + ';">' + this.childAsStr(UBBTag.PROC_REPLACE) + '</span>';
}
UBBTagList.setTag("COLOR", UBBColorTag);

// Background Color
var UBBBkColorTag = function ()
{
	UBBElement.init.apply(this, [UBBElement.UE_ELEM, "BKCOLOR", false]);
}
UBBBkColorTag.prototype = new UBBTag();
UBBBkColorTag.prototype.replaceProc = function ()
{
	return '<span style="background:' + this.defAttr + ';">' + this.childAsStr(UBBTag.PROC_REPLACE) + '</span>';
}
UBBTagList.setTag("BKCOLOR", UBBBkColorTag);

// font size
var UBBSizeTag = function ()
{
	UBBElement.init.apply(this, [UBBElement.UE_ELEM, "SIZE", false]);
}
UBBSizeTag.prototype = new UBBTag();
UBBSizeTag.prototype.replaceProc = function ()
{
	return '<span style="font-size:' + this.defAttr + ';">' + this.childAsStr(UBBTag.PROC_REPLACE) + '</span>';
}
UBBTagList.setTag("SIZE", UBBSizeTag);

// FAMILY
var UBBFamilyTag = function ()
{
	UBBElement.init.apply(this, [UBBElement.UE_ELEM, "FAMILY", false]);
}
UBBFamilyTag.prototype = new UBBTag();
UBBFamilyTag.prototype.replaceProc = function ()
{
	return '<span style="font-family:' + this.defAttr + ';">' + this.childAsStr(UBBTag.PROC_REPLACE) + '</span>';
}
UBBTagList.setTag("FAMILY", UBBFamilyTag);

// font 
var UBBFontTag = function ()
{
	UBBElement.init.apply(this, [UBBElement.UE_ELEM, "FONT", false]);
}
UBBFontTag.prototype = new UBBTag();
UBBFontTag.prototype.replaceProc = function ()
{
	var result = '<span style="';
	var attr;
	if (attr = this.getAttribute('FAMILY'))
		result += "font-family:" + attr + ";";
	if (attr = this.getAttribute('SIZE'))
		result += "font-size:" + attr + ";";
	if (attr = this.getAttribute('COLOR'))
		result += "color:" + attr + ";";
	if (attr = this.getAttribute('BKCOLOR'))
		result += "background:" + attr + ";";
	result += '">' + this.childAsStr(UBBTag.PROC_REPLACE) + '</span>';
	return result;
}
UBBTagList.setTag("FONT", UBBFontTag);

// URL link
var UBBURLTag = function ()
{
	UBBElement.init.apply(this, [UBBElement.UE_ELEM, "URL", false]);
}
UBBURLTag.prototype = new UBBTag();
UBBURLTag.prototype.replaceProc = function ()
{
	if (this.defAttr)
	{
		return '<a rel="_blank" href="' + this.defAttr + '">' + this.childAsStr(UBBTag.PROC_REPLACE) + '</a>';
	}
	else
	{
		return '<a rel="_blank" href="' + this.childAsStr(UBBTag.PROC_RAW) + '">' + this.childAsStr(UBBTag.PROC_REPLACE) + '</a>';
	}
}
UBBTagList.setTag("URL", UBBURLTag);
