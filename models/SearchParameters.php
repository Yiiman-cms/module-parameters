<?php

namespace system\modules\parameters\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use system\modules\parameters\models\Parameters;

/**
 * SearchParameters represents the model behind the search form of `system\modules\parameters\models\Parameters`.
 */
class SearchParameters extends Parameters
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['key', 'val', 'description'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = Parameters::find();
        if (!empty( $_GET['lng'])){
            $query=$query->where(['language'=> $_GET['lng']]);
        }else{
            $query=$query->where(['language_parent'=>null]);
        }
        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
        ]);

        $query->andFilterWhere(['like', 'key', $this->key])
            ->andFilterWhere(['like', 'val', $this->val])
            ->andFilterWhere(['like', 'description', $this->description]);

        return $dataProvider;
    }
}
