<?php

use yii\helpers\Inflector;
use yii\helpers\StringHelper;

/** @var yii\web\View $this */
/** @var yii\gii\generators\crud\Generator $generator */

echo "<?php\n";
?>

use <?= $generator->frontendPhpClass('yii\helpers\Html') ?>;
use <?= $generator->frontendPhpClass('yii\widgets\ActiveForm') ?>;

/** @var yii\web\View $this */
/** @var <?= ltrim($generator->searchModelClass, '\\') ?> $model */
/** @var <?= $generator->frontendPhpClass('yii\widgets\ActiveForm') ?> $form */
?>

<div class="<?= Inflector::camel2id(StringHelper::basename($generator->modelClass)) ?>-search">

    <?= "<?php " ?>$form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
<?php if ($generator->enablePjax): ?>
        'options' => [
            'data-pjax' => 1
        ],
<?php endif; ?>
    ]); ?>

<?php
$count = 0;
foreach ($generator->getColumnNames() as $attribute) {
    if (++$count < 6) {
        echo "    <?= " . $generator->generateActiveSearchField($attribute) . " ?>\n\n";
    } else {
        echo "    <?php // echo " . $generator->generateActiveSearchField($attribute) . " ?>\n\n";
    }
}
?>
    <div class="<?= $generator->frontendHtmlClass('formGroup') ?>">
        <?= "<?= " ?>Html::submitButton(<?= $generator->generateString('Search') ?>, ['class' => '<?= $generator->frontendHtmlClass('buttonPrimary') ?>']) ?>
        <?= "<?= " ?>Html::resetButton(<?= $generator->generateString('Reset') ?>, ['class' => '<?= $generator->frontendHtmlClass('buttonOutlineSecondary') ?>']) ?>
    </div>

    <?= "<?php " ?>ActiveForm::end(); ?>

</div>
