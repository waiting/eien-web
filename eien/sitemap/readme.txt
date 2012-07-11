# Sitemap支持
**sitemap.class.php**提供生成*sitemap*的功能，供搜索引擎使用。

* 接口**ISiteContent**用于回调网站帖子，文章内容。

不同的**SiteCategoryItem**对应一个**ISiteContent**接口。

**Sitemap**类会根据提供的**SiteCategoryItem**, **ISiteContent**接口生成*sitemap*。如果有多个*Category*，则生成多个*sitemap*，并生成一个*SitemapIndex*。