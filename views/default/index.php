<?php

use system\modules\filemanager\widget\MediaViewWidget;
use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $searchModel system\modules\parameters\models\SearchParameters */
/* @var $dataProvider yii\data\ActiveDataProvider */

//\system\widgets\topMenu\TopMenuWidget::addBtb(
//    'add',
//    Yii::t('parameters', 'ثبت '),
//    'success',
//    null,
//    Yii::$app->Options->BackendUrl . '/parameters/default/create'
//);
\system\widgets\backLang\backLangWidget::languages();

$this->title = Yii::t('parameters', 'پارامتر ها') . ' ';
$this->params['breadcrumbs'][] = $this->title;
$DeleteText = \Yii::t('parameters', 'حذف این ردیف');
$KeyText = \Yii::t('parameters', 'نام کلید را به صورت پیوسته و فقط به صورت لاتین وارد کنید(در غیر اینصورت قابل استفاده نخواهد بود)');
$ValText = \Yii::t('parameters', 'مقدار کلید را ثبت کنید');
$DescriptionLabel = \Yii::t('parameters', 'توضیحات');
$ValLabel = \Yii::t('parameters', 'مقدار');
$KeyLabel = \Yii::t('parameters', 'نام کلید');
$RemoveUrl = Yii::$app->urlManager->createUrl(['/parameters/default/remove']);
$js = <<<JS
var DeleteText='{$DeleteText}';
var KeyText='{$KeyText}';
var ValText='{$ValText}';
var RemoveUrl='{$RemoveUrl}';
var DescriptionLabel='{$DescriptionLabel}';
var ValLabel='{$ValLabel}';
var KeyLabel='{$KeyLabel}';
JS;
$this->registerJs($js, $this::POS_END);
$this->registerJs($this->render('script/app.js'), $this::POS_END);
$form = \yii\bootstrap\ActiveForm::begin();
?>
<style>

    .card {
        margin-bottom: 80px;
    }

    #add-row span {
        color: white;
    }

    #add-row {
        position: fixed;
        top: calc(100vh - 50%);
        width: 80px;
        z-index: 99999;
        right: calc(268px - 4px);
        border-radius: 5px;
        left: auto;
        rotate: 90deg;
    }

    .rm-btn span {
        color: white !important;
    }

    .rm-btn {
        margin-top: 0;
    }

</style>
<div class="parameters-index">
    <div class="row">
        <div class="col-md-12">
            <div class="card card-nav-tabs">
                <div class="card-body ">
                    <h4 class="text-center">مقادیر</h4>
                    <div class="row">
                        <div class="col-md-12 pull-right">
                            <div class="header-box">
                                <div class="row">
                                    <div class="col-md-4">
                                        <button class="btn btn-round btn-success"
                                                id="add-row" <?= \system\widgets\TippyTooltip\TippyWidget::attribute(\Yii::t('parameters', 'کلید جدید')) ?>>
                                                    <span class="material-icons">
                                                        add
                                                    </span>
                                        </button>
                                    </div>
                                    <div class="col-md-8">
                                        <button class="btn btn-round btn-success " style="float: left"
                                            <?= \system\widgets\TippyTooltip\TippyWidget::attribute(\Yii::t('parameters', 'ذخیره ی موارد')) ?>>
                                            <?= \Yii::t('parameters', 'ذخیره') ?>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <p><?= \Yii::t('parameters', 'در این بخش میتوانید کلید هایی تعریف کنید که در ویجت ها قابل استفاده باشند') ?></p>
                            <p><?= \Yii::t('parameters', 'مقادیر این بخش در مموری ذخیره میشوند و با سرعت بالا قابل فراخوانی هستند') ?></p>
                            <ul class="p-errors"></ul>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="table-responsive">
                                        <table class="table  table-bordered">
                                            <thead>
                                            <tr>
                                                <th><?= \Yii::t('parameters', 'کلید') ?></th>
                                                <th><?= \Yii::t('parameters', 'محتوا') ?></th>
                                                <th><?= \Yii::t('parameters', 'اقدامات') ?></th>
                                            </tr>
                                            </thead>
                                            <tbody id="data-table">
                                            <?php
                                            if (!empty($dataProvider->models)) {
                                                foreach ($dataProvider->models as $model) {
                                                    ?>
                                                    <tr style="margin-top: 5px" id="db-<?= $model->id ?>">
                                                        <td>
                                                            <span <?= !empty($model->description) ? \system\widgets\TippyTooltip\TippyWidget::attribute($model->description) : '' ?>>
                                                                {{<?= $model->key ?>}}
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <label for=""><?= \Yii::t('parameters', 'مقدار') ?></label>
                                                            <?php
                                                            if (empty($model->editor)) {

                                                                echo Html::input('text', 'keys[' . $model->key . '][val]', $model->val, ['class' => 'form-control']);
                                                            } else {
                                                                echo Html::textarea('keys[' . $model->key . '][val]', $model->val, ['class' => 'form-control']);
                                                            }

                                                            ?>
                                                        </td>
                                                        <td <?= empty($model->protected) ? 'rowspan="2"' : '' ?>>
                                                            <?php
                                                            if (empty($model->protected)) {
                                                                ?>
                                                                <div class="rm-btn">
                                                                    <button class="rm-btn btn-round btn btn-danger"
                                                                            data-mode="db"
                                                                        <?= \system\widgets\TippyTooltip\TippyWidget::attribute(\Yii::t('parameters', 'حذف این ردیف')) ?>
                                                                            data-id="<?= $model->id ?>">
                                                                    <span class="material-icons">
                                                                         clear
                                                                    </span>
                                                                    </button>
                                                                </div>
                                                                <?php
                                                            } else {
                                                                ?>
                                                                <p style="color: darkred">قابل حذف نیست</p>
                                                                <p style="color: green">قابل ویرایش هست</p>
                                                                <?php
                                                            }
                                                            ?>

                                                        </td>


                                                    </tr>
                                                    <?php
                                                    if (empty($model->protected)) {
                                                        ?>
                                                        <tr id="db2-<?= $model->id ?>">
                                                            <td colspan="2">
                                                                <label for=""><?= \Yii::t('parameters', 'توضیحات') ?></label>
                                                                <?= Html::input('text', 'keys[' . $model->key . '][description]', $model->description, ['class' => 'form-control', 'label' => \Yii::t('parameters', 'توضیحات')]) ?>
                                                            </td>
                                                        </tr>
                                                        <?php
                                                    }
                                                    ?>
                                                    <?php
                                                }
                                            }
                                            ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
<?php
\yii\bootstrap\ActiveForm::end();
?>
