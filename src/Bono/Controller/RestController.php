<?php

namespace Bono\Controller;

use Norm\Norm;
use Bono\Controller;

class RestController extends Controller {

    public function search() {
        $entries = Norm::factory($this->clazz)->find();

        return array(
            'entries' => $entries,
            'collection' => Norm::factory($this->clazz),
        );
    }

    public function create() {
        $model = Norm::factory($this->clazz)->newInstance();
        $model->set($this->app->request->post());
        $result = $model->save();

        return array(
            'entry' => $model,
        );
    }

    public function read($id) {
        $criteria = array( '$id' => $id );
        $model = Norm::factory($this->clazz)->findOne($criteria);
        if ($model) {
            return array(
                'entry' => $model,
                'collection' => Norm::factory($this->clazz),
            );
        }
    }

    public function update($id) {
        $criteria = array( '$id' => $id );
        $model = Norm::factory($this->clazz)->findOne($criteria);
        if ($model) {
            $model->set($this->app->request->post());
            $result = $model->save();
            return array(
                'entry' => $result,
                'collection' => Norm::factory($this->clazz),
            );
        }
    }

    public function delete($id) {
        $criteria = array( '$id' => $id );
        $model = Norm::factory($this->clazz)->findOne($criteria);
        if ($model) {
            $model->remove();
        }
    }

    public function getViewFor($for) {
        if (is_readable($this->app->config('templates.path').'/'.$this->name.'/'.$for.'.php')) {
            return $this->name.'/'.$for;
        }

        if (is_readable($this->app->config('templates.path').'/shared/'.$for.'.php')) {
            return 'shared/'.$for;
        }


        return $for;
    }

    public function register() {
        $app = $this->app;

        $that = $this;
        $this->app->group('/'.$this->name, function() use ($that,$app) {

            // form entry

            $app->get('/:id/delete', function($id) use ($that,$app) {
                $that->delete($id);
                $that->app->redirect('..');
            });

            // search entries
            $app->get('/', function() use ($that,$app) {
                $app->viewTemplate = $that->getViewFor('search');
                return $app->data = $that->search();
            });

            // add new entry
            $app->post('/', function() use ($that,$app) {
                return $app->data = $that->create();
            });

            // get entry
            $app->get('/:id', function($id) use ($that,$app) {
                $app->viewTemplate = $that->getViewFor('read');
                return $app->data = $that->read($id);
            });

            // update entry
            $app->put('/:id', function($id) use ($that,$app) {
                return $app->data = $that->update($id);
            });

            // delete entry
            $app->delete('/:id', function($id) use ($that,$app) {
                return $app->data = $that->delete($id);
            });


        });
    }

}