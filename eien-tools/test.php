<?php
header('Content-type: text/xml; charset=utf-8');

require_once dirname(__FILE__).'/../eien/filesys/folder.func.php';
require_once dirname(__FILE__).'/../eien/filesys/file.class.php';
require_once dirname(__FILE__).'/../eien/sitemap/sitemap.class.php';
require_once dirname(__FILE__).'/../eien/db/db.class.php';

DbConfig::$db_name = 'wp_test';
DbConfig::$db_user = 'wt';
DbConfig::$db_pwd = 'wt';

function myrecord2contentitem( $fields )
{
	// public function __construct( $title, $loc, $lastmod = null, $changefreq = null, $priority = null );
	return new SiteContentItem(
		$fields['post_title'],
		$fields['post_type'] == 'post' ? "http://www.x86pro.com/article/$fields[post_name]" :  "http://www.x86pro.com/$fields[post_name]"
	);
}

$sitemap = new Sitemap();
$sitemap->sitemapGenerate(
	new SiteContent(
		"SELECT * FROM wp_posts where post_status='publish' order by post_type, post_name;",
		'myrecord2contentitem'
	),
	new StdoutFile( 'sitemap.xml', 'w', false )
);

