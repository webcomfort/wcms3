<?php header("Content-type: text/xml"); ?>
<?php echo '<?xml version="1.0" encoding="UTF-8"?>'; ?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <?php foreach ($xml_urls as $value) { ?>
	<url><loc><?php echo $value; ?></loc></url>
	<?php } ?>
</urlset>