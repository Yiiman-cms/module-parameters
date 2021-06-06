<?php

namespace system\modules\parameters\models;

use system\lib\Application;
use system\lib\hquery\hQuery;
use system\lib\i18n\PhpMessageSource;
use system\lib\View;
use system\modules\form\widgets\FormRenderWidget;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%module_parameters}}".
 *
 * @property int $id
 * @property int $private مقادیر مخفی از دید ویرایشگر های متن
 * @property int $protected محافظت شده(توسط کاربران قابل حذف نیست و از پارامتر های سیستم به حساب می آید)
 * @property string $key نام کلید
 * @property string $val مقدار داخل کلید
 * @property string $editor آیا از ادیتور برای ویرایش این مقدار استفاده شود؟
 * @property string $description توضیحات کوتاه در مورد این کلید
 */
class Parameters extends \system\lib\ActiveRecord
{
    const STATUS_ACTIVE = 1;
    const STATUS_DE_ACTIVE = 0;


    private static $Parameters = [];
    private static $statics = [];
    private static $functions = [];

    private static $regexFind;
    private static $regexReplace;

    private static $regexFunctionFind = [];
    private static $regexFunctionVal = [];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%module_parameters}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['key', 'val'], 'required'],
            [['key', 'val', 'description'], 'string', 'max' => 255],
            [['protected', 'private','editor'], 'integer']
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('parameters', 'ID'),
            'key' => Yii::t('parameters', 'نام کلید'),
            'val' => Yii::t('parameters', 'مقدار داخل کلید'),
            'description' => Yii::t('parameters', 'توضیحات کوتاه در مورد این کلید'),
        ];
    }

    public static function filter($content)
    {

        $model = Parameters::getAllParameters(false, true);

        // < Get Tag Parameters >
        {

            preg_match_all('/<systemparameter name="(.+?)" data-component-.+? (.+?)>.+?<\/systemparameter>/s', $content, $matches, PREG_SET_ORDER);
//            echo '<pre>';
//            var_dump($matches);
//            var_dump($content);
//            die();
            foreach ($matches as $match) {
                $fullTag = $match[0];
                $paramName = $match[1];
                $tags = $match[2];
                $Functionparams = '';
                if (!empty($model[$paramName])) {
                    $content = str_replace($fullTag, $model[$paramName]['val'], $content);
                }
                if (!empty(self::$functions[$paramName])) {
                    if (!empty(self::$functions[$paramName]['fields'])) {
                        $countNumber = 1;
                        $count=count(self::$functions[$paramName]['fields']);
                        foreach (self::$functions[$paramName]['fields'] as $pName => $pdetails) {
                            preg_match('/'.$pName.'="(.+?)"/', $tags, $pMatch);
                            if (!empty($pMatch)) {
                                $Functionparams .= '"'.$pMatch[1].'"';
                            } else {
                                $Functionparams .= '';
                            }
                            if ($countNumber<$count){
                                $Functionparams .=',';
                            }
                            $countNumber ++;
                        }
                    }

                    // < Execute Parameter function >
                    {
                        $callback=" \$replace= self::\$functions[\$paramName]['val']( " . $Functionparams . " ); ";
                        eval($callback);

                        $content = str_replace($fullTag, $replace, $content);
                    }
                    // </ Execute Parameter function >
                }

                if ($paramName=='form'){

                    preg_match('/idmodel="(.+?)"/', $tags, $pMatch);
                    $id=$pMatch[1];
                    $form=FormRenderWidget::widget(['id'=>$id]);
                    $content = str_replace($fullTag, $form, $content);
                }


            }


        }
        // </ Get Tag Parameters >


        if (empty(self::$regexFind)) {

            if (!empty($model)) {
                foreach ($model as $item) {
                    self::$regexFind[] = '{{' . $item['key'] . '}}';
                    self::$regexReplace[] = $item['val'];
                }
            }


        }


        if (!empty(self::$regexFind)) {
            $content = str_replace(self::$regexFind, self::$regexReplace, $content);
        }

        // < function Regexes >
        {
            if (!empty(self::$functions)) {
                if (empty(self::$regexFunctionFind)) {
                    foreach (self::$functions as $item) {
                        self::$regexFunctionFind[$item['key']] = '{{{' . $item['key'] . '}}}';
                        self::$regexFunctionVal[$item['key']] = $item['val'];
                    }
                }

                $matches = [];
                preg_match_all('/{{{.*}}}/', $content, $matches);
                $variables = [];
                if (isset($matches[0])) {
                    foreach ($matches[0] as $match) {
                        $paramName = [];
                        preg_match('/[{]\w+ /', $match, $paramName);
                        $paramName = trim(str_replace('{', '', $paramName[0]));

                        $variables = explode(']', $match);
                        unset($variables[(count($variables) - 1)]);
                        foreach ($variables as $k => $v) {
                            $variables[$k] = trim(str_replace(['{{{' . $paramName . ' [', '[', ']'], '', $variables[$k]));

                        }
                        $vi = '';
                        foreach ($variables as $kk => $v) {
                            $vi .= '"' . $v . '"';
                            if (!empty($variables[($kk + 1)])) {
                                $vi .= ',';
                            }
                        }

                        eval(" \$replace= self::\$regexFunctionVal[\$paramName]( " . $vi . " ); ");

                        $content = str_replace($match, $replace, $content);
                    }
                }


            }
        }
        // </ function Regexes >



        return $content;

    }


    /**
     * اطلاعات یک پارامتر را به شما بازگردانی میکند
     * @param $key
     * @return string
     */
    public static function getParameter($key)
    {
        self::loadParameters();

        if (!empty(self::$Parameters[$key])) {
            return self::$Parameters[$key]['val'];
        } else {
            if (!empty(self::$statics[$key])) {
                $model = new Parameters();
                $model->key = $key;
                $model->val = (string)self::$statics[$key]['val'];
                $model->description = !empty(self::$statics[$key]['description']) ? self::$statics[$key]['description'] : '';
                $model->protected = !empty(self::$statics[$key]['protected']) ? 1 : 0;
                $model->private = !empty(self::$statics[$key]['private']) ? 1 : 0;
                if ($model->save()) {

                    self::$Parameters[$key] = self::$statics[$key];
                }
                return self::$statics[$key]['val'];
            }


        }


        return '';
    }

    /**
     * آرایه ای شامل همه ی پارامتر های ثبت شده بر میگرداند
     *
     *
     * این آرایه مشابه آرایه ی ذیل است:
     *
     *
     * [
     *      [
     *          'description'=>'',
     *          'key'=>'',
     *          'val'=>''
     *      ],
     *      [
     *          'description'=>'',
     *          'key'=>'',
     *          'val'=>'',
     *      ],
     * ]
     *
     *
     * @param bool $withFunctions
     * @return array
     */
    public static function getAllParameters($withFunctions = false, $force = false)
    {
        self::loadParameters($withFunctions, $force);

        return ArrayHelper::merge(self::$statics, self::$Parameters);
    }


    /**
     * آرایه ای شامل پارامتر های مدنظر که به صورت یکجا ثبت میشود
     * این آرایه ها در هر جای سیستم قابل بازخوانی هستند، همچنین به طور خودکار بر روی ویجت های ورود محتوی برای استفاده درج میشوند
     *
     *
     * Sample Array:
     *
     *
     * [
     *      [
     *          'description'=>'',
     *          'key'=>'',
     *          'val'=>''
     *      ],
     *      [
     *          'description'=>'',
     *          'key'=>'',
     *          'val'=>'',
     *      ],
     * ]
     *
     *
     * @param $array
     */
    public static function addParameters($array)
    {
        foreach ($array as $item) {
            if (empty($item['function'])) {
                self::$statics[$item['key']] =
                    [
                        'val' => $item['val'],
                        'description' => !empty($item['description']) ? $item['description'] : '',
                        'protected' => 1,
                        'key' => $item['key'],
                        'editor' => !empty($item['editor'])?$item['editor']:0,
                        'private' => !empty($item['private']) ? $item['private'] : '',
                    ];
            } else {
                self::$functions[$item['key']] = $item;
            }
        }
        unset($app);

    }

    private static function loadParameters($withFunction = false, $force = false)
    {
        if (empty(self::$Parameters) || $force) {
            $model = Parameters::find()
                ->asArray()
                ->all();


            if (!empty($model)) {
                self::$Parameters = ArrayHelper::index($model, 'key');
            }
            if ($withFunction) {
                // < Add Functions >
                {
                    if (!empty(self::$functions)) {
                        foreach (self::$functions as $key => $func) {
                            self::$Parameters[$key] = $func;
                        }
                    }
                }
                // </ Add Functions >
            }


        }
    }
}
