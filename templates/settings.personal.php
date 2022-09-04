<?php script('calibre_opds', 'settings'); ?>
<div id="calibre_opds" class="section">
	<h2><?php p($l->t('Calibre OPDS library')); ?></h2>
	<form action="personal.php">
		<p class="settings-hint"><?php p($l->t('Publish your Calibre library in OPDS')); ?></p>
		<p>
			<label>
				<?php p($l->t('Library root folder:')); ?>
				<input type="text" name="calibre_opds_library" value="<?php p($_['library']); ?>" placeholder="<?php p($_['library']); ?>"/>
			</label>
		</p>
	</form>
	<div name="calibre_opds_saved" style="display: none;">
		<span class="msg success"><?php p($l->t('Saved')); ?></span>
	</div>
</div>
