<?php
namespace Bono\Session;

use Bono\Http\Context;

class Memory
{
    protected $data = [];

    public function getId(Context $context, array $options)
    {
        $id = $context->getCookie($options['name']);
        if (!isset($this->data[$id])) {
            do {
                $id = uniqid('');
            } while(isset($this->data[$id]));
            $this->data[$id] = [];
        }
        return $id;
    }

    public function read(Context $context)
    {
        return $this->data[$context['@session.id']];
    }

    public function write(Context $context, $data)
    {
        $this->data[$context['@session.id']] = iterator_to_array($data);
    }

    public function destroy(Context $context)
    {
        unset($this->data[$context['@session.id']]);
    }
}