<?php

namespace Bono\Renderer;

use Bono\Http\Context;

interface RendererInterface
{
    public function resolve($template);
    public function write(Context $context);
    public function render($template, array $data = []);
}
