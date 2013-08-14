<?php

namespace Bono\Controller;

use Bono\Controller;

abstract class ScrudController extends Controller {

    public function getViewFor($for) {
        if (is_readable($this->app->config('templates.path').'/'.$this->name.'/'.$for.'.php')) {
            return $this->name.'/'.$for;
        }

        return $for;
    }

    public function register() {
        $app = $this->app;

        $this->app->group('/'.$this->name, function() {

            // search entries
            $this->app->get('/', function() {
                $this->app->viewTemplate = $this->getViewFor('search');
                return $this->app->data = $this->search();
            });

            // add new entry
            $this->app->post('/', function() {
                $this->app->viewTemplate = $this->getViewFor('create');
                return $this->app->data = $this->create();
            });

            // get entry
            $this->app->get('/:id', function($id) {
                $this->app->viewTemplate = $this->getViewFor('read');
                return $this->app->data = $this->read($id);
            });

            // update entry
            $this->app->put('/:id', function($id) {
                $this->app->viewTemplate = $this->getViewFor('update');
                return $this->app->data = $this->update($id);
            });

            // delete entry
            $this->app->delete('/:id', function($id) {
                $this->app->viewTemplate = $this->getViewFor('delete');
                return $this->app->data = $this->delete($id);
            });

        });
    }

    abstract public function search();

    abstract public function create();

    abstract public function read($id);

    abstract public function update($id);

    abstract public function delete($id);
}