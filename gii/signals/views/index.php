<h1>Signals Generator</h1>

<? $form = $this->beginWidget('CCodeForm', array('model' => $model)); ?>

<div class="row">
	<?= $form->labelEx($model, 'autogenAlias'); ?>
	<?= $form->textField($model, 'autogenAlias', array('size' => 65, 'readonly' => true)); ?>
	<div class="tooltip">
		Path alias "autogen" must be defined and writable.
	</div>
	<?= $form->error($model, 'autogenAlias'); ?>
</div>

<? $this->endWidget(); ?>