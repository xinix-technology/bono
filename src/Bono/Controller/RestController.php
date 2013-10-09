<?php

namespace Bono\Controller;

use Norm\Norm;
use Bono\Controller;
use Bono\Exception\RestException;

class RestController extends Controller {

    private $helper = array();
    private $published = array();

    public function search() {
        $entries = Norm::factory($this->clazz)->find();

        $this->publish('entries', $entries);
    }

    public function create() {
        $model = Norm::factory($this->clazz)->newInstance();
        $model->set($this->app->request->post());
        $result = $model->save();

        $this->publish('entry', $model);
    }

    public function read($id) {
        $criteria = array( '$id' => $id );
        $model = Norm::factory($this->clazz)->findOne($criteria);

        $this->publish('entry', $model);
    }

    public function update($id) {

        $criteria = array( '$id' => $id );
        $model = Norm::factory($this->clazz)->findOne($criteria);
        if ($model) {
            $model->set($this->app->request->post());
            $result = $model->save();

            $this->publish('entry', $result);
        } else {
            throw new RestException('No resource available', 404);
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

    public function helper($key, $value) {
        $this->helper[$key] = $value;
    }

    public function publish($key, $value) {
        $this->published[$key] = $value;
    }

    public function register() {
        $app = $this->app;

        $this->helper('collection', Norm::factory($this->clazz));

        $this->app->group('/'.$this->name, function() {

            // delete form entry
            $this->app->get('/:id/delete', function($id) {
                $this->delete($id);
                $this->app->redirect('..');
            });

            // update form entry
            $this->app->get('/:id/update', function($id) {
                $this->app->viewTemplate = $this->getViewFor('update');
                $this->read($id);
                $this->app->helper = $this->helper;
                $this->app->published = $this->published;
            });

            $this->app->post('/:id/update', function($id) {
                $this->update($id);
                $this->app->helper = $this->helper;
                $this->app->published = $this->published;
                $this->app->redirect('../'.$id);

            });

            // search entries
            $this->app->get('/', function() {
                $this->app->viewTemplate = $this->getViewFor('search');
                $this->search();
                $this->app->helper = $this->helper;
                $this->app->published = $this->published;
            });

            // add new entry
            $this->app->post('/', function() {
                $this->create();
                $this->app->helper = $this->helper;
                $this->app->published = $this->published;
            });

            // get entry
            $this->app->get('/:id', function($id) {
                $this->app->viewTemplate = $this->getViewFor('read');
                $this->read($id);
                $this->app->helper = $this->helper;
                $this->app->published = $this->published;
            });

            // update entry
            $this->app->put('/:id', function($id) {
                $this->update($id);
                $this->app->helper = $this->helper;
                $this->app->published = $this->published;
            });

            // delete entry
            $this->app->delete('/:id', function($id) {
                $this->delete($id);
                $this->app->helper = $this->helper;
                $this->app->published = $this->published;
            });


        });
    }

}