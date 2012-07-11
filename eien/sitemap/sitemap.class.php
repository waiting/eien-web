<?php
/**	站点地图支持
	@author WaiTing
	@version 0.1.0 */
define('EIEN_SITEMAP_CLASS', 'sitemap/sitemap.class.php');

/** 枚举的种类项对象类 */
class SiteCategoryItem
{
	public $content; # 内容接口
	public $name; # 种类名称

	public $loc; # Sitemap档的URL位置
	public $lastmod; # optional 最后修改时间，W3C日期时间格式
	public function __construct( ISiteContent $content, $name, $loc, $lastmod = null )
	{
		$this->content = $content;
		$this->name = $name;
		$this->loc = $loc;
		$this->lastmod = $lastmod;
	}
}

/** 枚举的内容项对象类 */
class SiteContentItem
{
	public $title; # 标题

	public $loc; # 内容页的URL位置
	public $lastmod; # optional 最后修改时间，W3C日期时间格式
	public $changefreq; # optional 更新频率 always,hourly,daily,weekly,monthly,yearly,never
	public $priority; # optional 优先级 0.0~1.0
	public function __construct( $title, $loc, $lastmod = null, $changefreq = null, $priority = null )
	{
		$this->title = $title;
		$this->loc = $loc;
		$this->lastmod = $lastmod;
		$this->changefreq = $changefreq;
		$this->priority = $priority;
	}
}

/** 站点内容接口 */
interface ISiteContent
{
	/** 获取数量 */
	public function getCount();
	/** 获取第一个
	 * @return object */
	public function first();
	/** 获取下一个
	 * @return object */
	public function next();
}


/** 站点内容适配器类 */
class SiteContent implements ISiteContent
{
	private $rs;
	private $callback;
	/**
	 * @param $sql string SQL SELECT查询语句
	 * @param $callback 原型'object callback( array fields );' */
	public function __construct( $sql, $callback )
	{
		$this->rs = db::rs($sql);
		//echo $this->rs->recordCount()."\n";
		$this->callback = $callback;
	}
	/** 获取数量 */
	public function getCount()
	{
		return $this->rs->recordCount();
	}
	/** 获取第一个
	 * @return object */
	public function first()
	{
		if ( $this->rs->recordCount() == 0 )
		{
			return null;
		}
		return call_user_func( $this->callback, $this->rs->fields() );
	}
	/** 获取下一个
	 * @return object */
	public function next()
	{
		$this->rs->moveNext();
		if ( $this->rs->eof() )
		{
			return null;
		}
		return call_user_func( $this->callback, $this->rs->fields() );
	}
}


/** Sitemap 支持类 */
class Sitemap
{
	private $categoryItems; # 种类
	/**
	 * @param $categoryItems array */
	public function __construct( $categoryItems = null )
	{
		$this->categoryItems = $categoryItems;
	}
	/** 生成SitemapIndex
	 * @param $sitemapIndexFile string 生成的sitemap文件 */
	public function buildSitemapIndex( $sitemapIndexFile )
	{
		/*foreach ( $this->categoryItems as $cateItem )
		{
			// 引入File类 in filesys/file.class.php
			$this->sitemapGenerate( $cateItem->content, new File( $sitemapIndexFile, 'w' ) );
		}*/
	}
	/** 生成一个Sitemap
	 * @param $content ISiteContent
	 * @param $sitemapFile IFile */
	public function buildSitemap( ISiteContent $content, IFile $sitemapFile )
	{
		$sitemapFile->puts("<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n");
		$sitemapFile->puts("<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n");
		if ( $content && ( $obj = $content->first() ) ) do
		{
			$sitemapFile->puts("<url>\n");
			$sitemapFile->puts("<loc>".htmlspecialchars($obj->loc)."</loc>\n");
			if ( $obj->lastmod )
				$sitemapFile->puts("<lastmod>".$obj->lastmod."</lastmod>\n");
			if ( $obj->changefreq )
				$sitemapFile->puts("<changefreq>".$obj->changefreq."</changefreq>\n");
			if ( $obj->priority !== '' && $obj->priority !== null )
				$sitemapFile->puts("<priority>".$obj->priority."</priority>\n");
			$sitemapFile->puts("</url>\n");
		}
		while ( $obj = $content->next() );

		$sitemapFile->puts("</urlset>\n");
	}
}
