<?php /* Presto.md - Copyright (C) 2013 Bruce Alderson */

namespace napkinware\presto;

/* Bootstrap HTML helpers

	Generate Bootstrappy HTML for common things.


*/
class bootstrap extends html {
	
	
	static function listGroupItem($name, $url, $description = null, $title = null, $badge = null) { 
		
		$badge = self::span($badge, 'badge');
	?>

	<a href="<?= $url ?>" class="list-group-item"<?= $title ?>>
		<?= $badge ?>
		<h4 class="list-group-item-heading"><?= $name ?></h4>
		<p class="list-group-item-text"><?= $description ?></p>
	</a>	
		
<?php } 

}

// Tests


