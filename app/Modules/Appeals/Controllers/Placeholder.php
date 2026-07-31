<?php

namespace Modules\Appeals\Controllers;

class Placeholder extends \App\Controllers\BaseController
{
    public function index()
    {
        return $this->response->setJSON([
            'module' => 'Appeals',
            'status' => 'scaffold',
        ]);
    }
}
