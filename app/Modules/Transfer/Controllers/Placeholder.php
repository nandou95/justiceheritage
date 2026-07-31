<?php

namespace Modules\Transfer\Controllers;

class Placeholder extends \App\Controllers\BaseController
{
    public function index()
    {
        return $this->response->setJSON([
            'module' => 'Transfer',
            'status' => 'scaffold',
        ]);
    }
}
