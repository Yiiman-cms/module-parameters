<?php


namespace system\modules\parameters\widgets;


use kartik\base\Widget;
use system\lib\View;
use system\modules\parameters\models\Parameters;
use system\widgets\TippyTooltip\TippyWidget;

class ParameterWidget extends Widget
{
    public function run()
    {
        $out = '';
        $id = uniqid();
        if (!empty(Parameters::getAllParameters(true))) {
            $out .= '
<style>
#pOut {
	background: #0000001f;
	padding: 10px;
	border-radius: 5px;
	margin: auto;
	width: 92%;
	display: block;
	text-align: center;
	user-select: all;
	direction: ltr;
}
</style>
<div class="row" style="
border: solid 1px rgba(0, 0, 0, 0.11);
border-radius: 5px;
margin: 10px 0;
padding: 10px;
"><div class="col-md-12">';
            $out .= '<h3>' . \Yii::t('parameters', 'پارامتر های قابل استفاده برای ادیتور') . '</h3>';

            // < Translates >
            {
                $translates =
                    json_encode(
                        [
                            'save' => \Yii::t('site', 'استفاده'),
                            'help' => \Yii::t('site', 'پس از ورود اطلاعات، کلید "استفاده" را فشار دهید، تا پارامتر ساخته شده کپی شود، سپس آن را به متن خود الصاق کنید'),
                            'close' => \Yii::t('site', 'انصراف و بستن'),
                            'header' => \Yii::t('site', 'فرم ایجاد پارامتر داینامیک')
                        ]
                    );
                $js = <<<JS
var ptranslates= $translates;
JS;
                $this->view->registerJs($js, View::POS_HEAD);
            }
            // </ Translates >
            foreach (Parameters::getAllParameters(true) as $item) {
                if ($item['private']) {
                    continue;
                }
                // < Description >
                {
                    if (!empty($item['description'])) {
                        $tippy = TippyWidget::attribute($item['description']);
                    } else {
                        $tippy = '';
                    }
                }
                // </ Description >



                // < set Color Class >
                {
                    switch (true) {
                        case !empty($item['function']):
                            $classColor = 'badge-rose';
                            $isFunction = "true";
                            $fields = $item["key"] . "" . uniqid();
                            $jsFields = json_encode($item["fields"]);

                            $js = <<<JS
var $fields = $jsFields;
JS;

                            $this->view->registerJs($js, View::POS_HEAD);
                            break;
                        default:
                            $classColor = 'badge-success';
                            $isFunction = 'false';
                            $fields = '';
                            break;
                    }
                }
                // </ set Color Class >


                $out .= '<span style="cursor:pointer;margin:5px;padding:5px 10px;" fields="' . $fields . '" function="' . $isFunction . '" class="badge ' . $classColor . ' p-parameter" ' . $tippy . '>' . $item['key'] . '</span>';
            }

            $out .= '</div></div>';
            $this->view->registerJs($this->render('script/app.js'), View::POS_END);

        }
        return $out;
    }


}
