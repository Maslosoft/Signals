<h1>Signals Generator</h1>

<?php $form = $this->beginWidget('CCodeForm', array('model' => $model)); ?>

<div class="row">
	<?= $form->labelEx($model, 'configAlias'); ?>
	<?= $form->textField($model, 'configAlias', array('size' => 65, 'readonly' => true)); ?>
	<div class="hint">
		Path alias "<?= $model->configAlias; ?>" must be defined and writable.<br />
		Can be changed in application config like this:<br />
		<pre>
	'components' => [
		...
		'signal' => [
		...
		'configAlias' => '<?= $model->configAlias; ?>'
		]
	]</pre>
	</div>
	<?= $form->error($model, 'configAlias'); ?>
</div>

<?php $this->endWidget(); ?>