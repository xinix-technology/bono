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
        $that = $this;

        $this->helper('collection', Norm::factory($this->clazz));

        $app->group('/'.$this->name, function() use ($app, $that) {

            // delete form entry
            $app->get('/:id/delete', function($id) use ($app, $that) {
                $that->delete($id);
                $app->redirect('..');
            });

            // update form entry
            $app->get('/:id/update', function($id) use ($app, $that) {
                $app->viewTemplate = $that->getViewFor('update');
                $that->read($id);
                $app->helper = $that->helper;
                $app->published = $that->published;
            });

            $app->post('/:id/update', function($id) use ($app, $that) {
                $that->update($id);
                $app->helper = $that->helper;
                $app->published = $that->published;
                $app->redirect('../'.$id);

            });

            // search entries
            $app->get('/', function() use ($app, $that) {
                $app->viewTemplate = $that->getViewFor('search');
                $that->search();
                $app->helper = $that->helper;
                $app->published = $that->published;
            });

            // add new entry
            $app->post('/', function() use ($app, $that) {
                $that->create();
                $app->helper = $that->helper;
                $app->published = $that->published;
            });

            // get entry
            $app->get('/:id', function($id) use ($app, $that) {
                $app->viewTemplate = $that->getViewFor('read');
                $that->read($id);
                $app->helper = $that->helper;
                $app->published = $that->published;
            });

            // update entry
            $app->put('/:id', function($id) use ($app, $that) {
                $that->update($id);
                $app->helper = $that->helper;
                $app->published = $that->published;
            });

            // delete entry
            $app->delete('/:id', function($id) use ($app, $that)  {
                $that->delete($id);
                $app->helper = $that->helper;
                $app->published = $that->published;
            });


        });
    }

}