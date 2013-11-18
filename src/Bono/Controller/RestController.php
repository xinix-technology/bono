<?php

namespace Bono\Controller;


abstract class RestController extends Controller {

    public function mapRoute() {
        $this->map('/null/create', 'create')->via('GET', 'POST');
        $this->map('/:id/read', 'read')->via('GET');
        $this->map('/:id/update', 'update')->via('GET', 'POST');
        $this->map('/:id/delete', 'delete')->via('GET', 'POST');

        $this->map('/', 'search')->via('GET');
        $this->map('/', 'create')->via('POST');
        $this->map('/:id', 'read')->via('GET');
        $this->map('/', 'update')->via('PUT');
        $this->map('/', 'delete')->via('DELETE');

}
    abstract function search();
    abstract function create();
    abstract function read($id);
    abstract function update($id);
    abstract function delete($id);

}