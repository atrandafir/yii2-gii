<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\gii\controllers;

use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class DefaultController extends Controller
{
    public $layout = 'generator';
    /**
     * @var \yii\gii\Module
     */
    public $module;
    /**
     * @var \yii\gii\Generator
     */
    public $generator;


    /**
     * {@inheritdoc}
     */
    public function beforeAction($action)
    {
        Yii::$app->response->format = Response::FORMAT_HTML;
        return parent::beforeAction($action);
    }


    public function actionIndex()
    {
        $this->layout = 'main';

        return $this->render('index');
    }

    public function actionView($id)
    {
        $generator = $this->loadGenerator($id);
        $params = ['generator' => $generator, 'id' => $id];

        $preview = Yii::$app->request->post('preview');
        $generate = Yii::$app->request->post('generate');
        $answers = Yii::$app->request->post('answers');

        if ($preview !== null || $generate !== null) {
            if ($generator->validate()) {
                $generator->saveStickyAttributes();
                $files = $generator->generate();
                if ($generate !== null && !empty($answers)) {
                    $params['hasError'] = !$generator->save($files, (array) $answers, $results);
                    $params['results'] = $results;
                } else {
                    $params['files'] = $files;
                    $params['answers'] = $answers;
                }
            }
        }

        return $this->render('view', $params);
    }

    public function actionPreview($id, $file)
    {
        $generator = $this->loadGenerator($id);
        if ($generator->validate()) {
            foreach ($generator->generate() as $f) {
                if ($f->id === $file) {
                    $content = $f->preview();
                    if ($content !== false) {
                        return  '<div class="content">' . $content . '</div>';
                    }
                    return '<div class="error">Preview is not available for this file type.</div>';
                }
            }
        }
        throw new NotFoundHttpException("Code file not found: $file");
    }

    public function actionDiff($id, $file)
    {
        $generator = $this->loadGenerator($id);
        if ($generator->validate()) {
            foreach ($generator->generate() as $f) {
                if ($f->id === $file) {
                    return $this->renderPartial('diff', [
                        'diff' => $f->diff(),
                    ]);
                }
            }
        }
        throw new NotFoundHttpException("Code file not found: $file");
    }

    /**
     * Runs an action defined in the generator.
     * Given an action named "xyz", the method "actionXyz()" in the generator will be called.
     * If the method does not exist, a 400 HTTP exception will be thrown.
     * @param string $id the ID of the generator
     * @param string $name the action name
     * @return mixed the result of the action.
     * @throws NotFoundHttpException if the action method does not exist.
     */
    public function actionAction($id, $name)
    {
        $generator = $this->loadGenerator($id);
        $method = 'action' . $name;
        if (method_exists($generator, $method)) {
            return $generator->$method();
        }
        throw new NotFoundHttpException("Unknown generator action: $name");
    }

    /**
     * Loads the generator with the specified ID.
     * @param string $id the ID of the generator to be loaded.
     * @return \yii\gii\Generator the loaded generator
     * @throws NotFoundHttpException
     */
    protected function loadGenerator($id)
    {
        if (isset($this->module->generators[$id])) {
            $this->generator = $this->module->generators[$id];
            $this->generator->loadStickyAttributes();
            $this->generator->load(Yii::$app->request->post());
            $this->generator->frontendFrameworks = $this->module->frontendFrameworks;

            return $this->generator;
        }
        throw new NotFoundHttpException("Code generator not found: $id");
    }
}
