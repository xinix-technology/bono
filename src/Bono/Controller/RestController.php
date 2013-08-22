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

        $this->app->group('/'.$this->name, function() {

            // form entry

            $this->app->get('/:id/delete', function($id) {
                $this->delete($id);
                $this->app->redirect('..');
            });

            // search entries
            $this->app->get('/', function() {
                $this->app->viewTemplate = $this->getViewFor('search');
                return $this->app->data = $this->search();
            });

            // add new entry
            $this->app->post('/', function() {
                return $this->app->data = $this->create();
            });

            // get entry
            $this->app->get('/:id', function($id) {
                $this->app->viewTemplate = $this->getViewFor('read');
                return $this->app->data = $this->read($id);
            });

            // update entry
            $this->app->put('/:id', function($id) {
                return $this->app->data = $this->update($id);
            });

            // delete entry
            $this->app->delete('/:id', function($id) {
                return $this->app->data = $this->delete($id);
            });


        });
    }

}